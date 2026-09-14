<?php
require_once '../db.php';

header('Content-Type: application/json; charset=utf-8');
$order_id = (int)($_POST['order_id'] ?? 0);
if ($order_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'รหัสออเดอร์ไม่ถูกต้อง']);
    exit;
}
try {
    $stmt = $conn->prepare("UPDATE `order` SET status = 'cooking' WHERE order_id = ? AND source = 'qr' AND status = ('pending','new_item')");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    if ($stmt->affected_rows !== 1) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบออเดอร์ใหม่ หรือออเดอร์นี้ถูกรับแล้ว']);
        exit;
    }
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถรับออเดอร์ได้']);
}
