<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

require __DIR__ . '/../db.php';

// 1. รับค่า order_id
$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
if ($orderId <= 0) {
    $input = json_decode(file_get_contents('php://input'), true);
    $orderId = isset($input['order_id']) ? (int)$input['order_id'] : 0;
}

if ($orderId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบค่า order_id']);
    exit;
}

// 2. ดึงข้อมูลออเดอร์
$result = mysqli_query($conn, "SELECT * FROM `order` WHERE `order_id` = $orderId");
$res = mysqli_fetch_assoc($result);

if (!$res) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบรายการออเดอร์']);
    exit;
}

// 3. กรณีบิลใหม่ (สถานะ pending)
if ($res['status'] === 'pending') {
    // ไม่ต้อง UPDATE เปลี่ยนสถานะ เพื่อให้คงเป็น 'pending' สำหรับดึงมาโชว์ในหน้า POS
    echo json_encode([
        'status'   => 'success',
        'type'     => 'pending',
        'print_id' => $orderId,
        'table_id' => $res['table_id']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 4. กรณีสั่งอาหารเพิ่ม (สถานะ new_item)
if ($res['status'] === 'new_item' && (int)$res['parent_order_id'] > 0) {
    echo json_encode([
        'status'   => 'success',
        'type'     => 'new_item',
        'print_id' => $orderId,
        'table_id' => $res['table_id']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'status'   => 'success',
    'type'     => 'already_accepted',
    'print_id' => $orderId,
    'table_id' => $res['table_id']
], JSON_UNESCAPED_UNICODE);