<?php
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$tableId = trim((string)($data['table_id'] ?? $data['tables_id'] ?? ''));
$items = $data['items'] ?? [];

if ($tableId === '' || empty($items)) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบ']);
    exit;
}

mysqli_begin_transaction($conn);
try {
    // 1. เช็คว่าโต๊ะนี้มีบิลเดิมที่เปิดค้างอยู่หรือไม่
    $st = mysqli_prepare($conn, "SELECT `order_id` FROM `order` WHERE `table_id` = ? AND `status` IN ('pending', 'cooking') ORDER BY `order_id` DESC LIMIT 1");
    mysqli_stmt_bind_param($st, 's', $tableId);
    mysqli_stmt_execute($st);
    $open = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    $existingOrderId = ($open && isset($open['order_id'])) ? (int)$open['order_id'] : 0;

    // 2. ค้นหาราคาตั้งต้นของเมนู
    $ids = array_values(array_unique(array_map(fn($i) => (int)($i['id'] ?? 0), $items)));
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $st = mysqli_prepare($conn, "SELECT p_id, p_price FROM products WHERE p_id IN ($ph)");
    mysqli_stmt_bind_param($st, str_repeat('i', count($ids)), ...$ids);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    $priceMap = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $priceMap[(int)$r['p_id']] = (float)$r['p_price'];
    }

    // 3. คำนวณยอดรวมและเตรียมข้อมูลให้สะอาด
    $total = 0;
    $clean = [];
    foreach ($items as $it) {
        $pid = (int)($it['id'] ?? 0);
        $qty = max(1, (int)($it['qty'] ?? $it['quantity'] ?? 1)); // ดักรับ qty หรือ quantity

        if (!isset($priceMap[$pid])) throw new Exception("เมนู ID: $pid ไม่อยู่ในระบบ");

        // ถ้าระบบหน้าบ้านมีการส่งราคาที่บวก option มาแล้ว ให้ใช้ราคานั้น หากไม่มีให้ใช้ราคาพื้นฐาน
        $unitPrice = isset($it['price']) ? (float)$it['price'] : $priceMap[$pid];

        $total += $unitPrice * $qty;

        $clean[] = [
            'pid' => $pid,
            'qty' => $qty,
            'price' => $unitPrice,
            'remark' => mb_substr(trim($it['remark'] ?? ''), 0, 255),
            'option_label' => mb_substr(trim($it['optionLabel'] ?? ''), 0, 255) // รับค่า optionLabel เข้ามา
        ];
    }

    // 4. จัดการบิลหลัก (order)
    if ($existingOrderId > 0) {
        // มีบิลเปิดอยู่แล้ว -> ใช้บิลเดิม + บวกยอดเงินเพิ่ม
        $orderId = $existingOrderId;
        $st = mysqli_prepare($conn, "UPDATE `order` SET `total_amount` = `total_amount` + ? WHERE `order_id` = ?");
        mysqli_stmt_bind_param($st, 'di', $total, $orderId);
        mysqli_stmt_execute($st);
    } else {
        // ไม่มีบิลเดิม -> สร้างบิลใหม่
        $st = mysqli_prepare($conn, "INSERT INTO `order` (`table_id`, `source`, `status`, `total_amount`, `parent_order_id`) VALUES (?, 'pos', 'pending', ?, 0)");
        mysqli_stmt_bind_param($st, 'sd', $tableId, $total);
        mysqli_stmt_execute($st);
        $orderId = mysqli_insert_id($conn);
    }

    // 5. บันทึกรายการอาหารลง order_detail และ อัปเดตยอดขายสินค้า
    $st = mysqli_prepare($conn, "INSERT INTO `order_detail` (`order_id`, `product_id`, `quantity`, `price`, `remark`, `option_label`) VALUES (?, ?, ?, ?, ?, ?)");

    // เพิ่ม: เตรียมคำสั่งอัปเดตยอดขายในตาราง products
    $st_sales = mysqli_prepare($conn, "UPDATE `products` SET `sales_count` = `sales_count` + ? WHERE `p_id` = ?");

    foreach ($clean as $c) {
        // 5.1 บันทึกรายการลง order_detail
        mysqli_stmt_bind_param($st, 'iiidss', $orderId, $c['pid'], $c['qty'], $c['price'], $c['remark'], $c['option_label']);
        mysqli_stmt_execute($st);

        // 5.2 เพิ่มบรรทัดนี้: บวกเพิ่ม sales_count ตามจำนวน (qty) ที่สั่งซื้อ
        mysqli_stmt_bind_param($st_sales, 'ii', $c['qty'], $c['pid']);
        mysqli_stmt_execute($st_sales);
    }

    mysqli_commit($conn);
    echo json_encode(['status' => 'success', 'success' => true, 'order_id' => $orderId]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['status' => 'error', 'success' => false, 'message' => $e->getMessage()]);
}
