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

// เริ่ม Transaction
mysqli_begin_transaction($conn);
$last_sql = ""; //เก็บคำสั่ง SQL ล่าสุดไว้ดูตอนพัง

try {
    $total_amount = 0;
    $processed_items = [];

    // คำนวณยอดรวม
    foreach ($items as $item) {
        $prod_id = (int)$item['id'];
        $qty = (int)$item['quantity'];
        $remark = mysqli_real_escape_string($conn, $item['remark'] ?? '');

        $last_sql = "SELECT p_price FROM products WHERE p_id = $prod_id";
        $res_price = mysqli_query($conn, $last_sql);
        
        if ($row = mysqli_fetch_assoc($res_price)) {
            $price = (float)$row['p_price'];
            $total_amount += ($price * $qty); 
            
            $processed_items[] = [
                'id' => $prod_id,
                'qty' => $qty,
                'price' => $price,
                'remark' => $remark
            ];
        } else {
            throw new Exception("ไม่พบสินค้า ID: $prod_id");
        }
    }

    // บันทึกลงตาราง order
    $last_sql = "INSERT INTO `order` (table_id, created_at, total_amount) 
                 VALUES ('$table_id', UNIX_TIMESTAMP(), $total_amount)";
    
    if (!mysqli_query($conn, $last_sql)) {
        throw new Exception('บันทึกตาราง order พลาด');
    }
    
    $order_id = mysqli_insert_id($conn);

    // บันทึกลงตาราง order_detail
    foreach ($processed_items as $p_item) {
        $pid = $p_item['id'];
        $qty = $p_item['qty'];
        $item_price = $p_item['price'];
        $item_remark = $p_item['remark']; 

        $last_sql = "INSERT INTO order_detail (order_id, product_id, quantity, price, remark) 
                     VALUES ($order_id, $pid, $qty, $item_price, '$item_remark')";

        if (!mysqli_query($conn, $last_sql)) {
            throw new Exception('บันทึกตาราง order_detail พลาด');
        }
    }

    // ยืนยันข้อมูล
    mysqli_commit($conn);
    echo json_encode(['success' => true, 'order_id' => $order_id]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    // ถ้าพังจะส่งประโยค SQL ออกไปโชว์ที่หน้าจอด้วยเลย จะได้รู้ว่าผิดตรงไหน
    echo json_encode(['success' => false, 'message' => $e->getMessage() . " | คำสั่งที่พังคือ: " . $last_sql]);
}
?>