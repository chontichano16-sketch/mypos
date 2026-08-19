<?php
// ซ่อน Error HTML เพื่อไม่ให้ JSON พังเวลาเกิดปัญหา
ini_set('display_errors', 0); 
header('Content-Type: application/json; charset=utf-8');

require_once "db.php";

$data = json_decode(file_get_contents('php://input'), true);
$items = $data['items'] ?? [];
$table_id = $data['table_id'] ?? '';

if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'ไม่มีรายการออเดอร์']);
    exit;
}

// เริ่ม Transaction เพื่อป้องกันข้อมูลบันทึกไม่ครบ
mysqli_begin_transaction($conn);

try {
    $total_amount = 0;
    $processed_items = [];

    // 1. วนลูปดึงราคาอาหารจากตาราง products มาคำนวณยอดรวม
    foreach ($items as $item) {
        $prod_id = (int)$item['id'];
        $qty = (int)$item['quantity'];

        $sql_price = "SELECT p_price FROM products WHERE p_id = $prod_id";
        $res_price = mysqli_query($conn, $sql_price);
        
        if ($row = mysqli_fetch_assoc($res_price)) {
            $price = (float)$row['p_price'];
            $total_amount += ($price * $qty); // บวกยอดรวมทั้งบิล
            
            // เก็บข้อมูลเตรียมไว้บันทึกลง order_detail
            $processed_items[] = [
                'id' => $prod_id,
                'qty' => $qty,
                'price' => $price
            ];
        } else {
            throw new Exception("ไม่พบสินค้า ID: $prod_id");
        }
    }

    // 2. บันทึกลงตาราง order (พร้อมยอด total_amount)
    $note = mysqli_real_escape_string($conn, $table_id);
    $sql_order = "INSERT INTO `order` (table_id, note, created_at, total_amount) 
                  VALUES ('$table_id', '$note', UNIX_TIMESTAMP(), $total_amount)";
    
    if (!mysqli_query($conn, $sql_order)) {
        throw new Exception('บันทึกตาราง order พลาด: ' . mysqli_error($conn));
    }
    
    $order_id = mysqli_insert_id($conn);

    // 3. บันทึกลงตาราง order_detail (ใช้ชื่อคอลัมน์ product_id และ price ตามฐานข้อมูลจริง)
    foreach ($processed_items as $p_item) {
        $pid = $p_item['id'];
        $qty = $p_item['qty'];
        $item_price = $p_item['price'];

        $sql_detail = "INSERT INTO order_detail (order_id, product_id, quantity, price) 
                       VALUES ($order_id, $pid, $qty, $item_price)";

        if (!mysqli_query($conn, $sql_detail)) {
            throw new Exception('บันทึกตาราง order_detail พลาด: ' . mysqli_error($conn));
        }
    }

    // ทำงานครบทุกขั้นตอน กดยืนยันบันทึกข้อมูล
    mysqli_commit($conn);
    echo json_encode(['success' => true, 'order_id' => $order_id]);

} catch (Exception $e) {
    // ถ้ามีอะไรพัง ให้ยกเลิกการบันทึกทั้งหมด แล้วส่งข้อความแจ้งเตือน
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>