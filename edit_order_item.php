<?php
// ปิดการพ่น Warning/Notice ที่อาจแทรกเข้าไปใน JSON
error_reporting(0);
ini_set('display_errors', 0);

require 'db.php';

// ล้างข้อความหรือช่องว่างที่อาจค้างอยู่ออกให้หมด
if (ob_get_length()) ob_clean();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['detail_id'], $_POST['order_id'], $_POST['qty'])) {
    $detail_id = intval($_POST['detail_id']);
    $order_id = intval($_POST['order_id']);
    $qty = max(1, intval($_POST['qty']));
    $remark = isset($_POST['remark']) ? mysqli_real_escape_string($conn, $_POST['remark']) : '';

    mysqli_begin_transaction($conn);
    try {
        // 1. อัปเดตรายการอาหาร
        $stmt_update_detail = $conn->prepare("UPDATE `order_detail` SET `quantity` = ?, `remark` = ? WHERE `id` = ? AND `order_id` = ?");
        $stmt_update_detail->bind_param("isii", $qty, $remark, $detail_id, $order_id);
        $stmt_update_detail->execute();

        // 2. คำนวณยอดรวมใหม่
        $stmt_sum = $conn->prepare("SELECT SUM(price * quantity) AS new_total FROM `order_detail` WHERE `order_id` = ?");
        $stmt_sum->bind_param("i", $order_id);
        $stmt_sum->execute();
        $res_sum = $stmt_sum->get_result();
        $row = $res_sum->fetch_assoc();
        $new_total = $row['new_total'] ? floatval($row['new_total']) : 0;

        // 3. อัปเดตยอดรวมบิล
        $stmt_update_order = $conn->prepare("UPDATE `order` SET `total_amount` = ? WHERE `order_id` = ?");
        $stmt_update_order->bind_param("di", $new_total, $order_id);
        $stmt_update_order->execute();

        mysqli_commit($conn);
        echo json_encode(['status' => 'success', 'new_total' => $new_total]);
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}
?>



<!-- สำหรับบันทึกข้อมูลที่แก้ไข แล้วไปอัปเดทที่ฐานข้อมูลใหม่ -->