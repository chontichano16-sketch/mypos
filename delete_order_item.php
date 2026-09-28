<?php
// ปิดการพ่น Warning/Notice ที่อาจแทรกเข้ามาใน JSON
error_reporting(0);
ini_set('display_errors', 0);

require 'db.php';

// ล้างข้อความหรือช่องว่างค้างกระดานออกให้หมด
if (ob_get_length()) ob_clean();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['detail_id'], $_POST['order_id'])) {
    $detail_id = intval($_POST['detail_id']);
    $order_id = intval($_POST['order_id']);

    mysqli_begin_transaction($conn);
    try {
        
        $stmt_delete = $conn->prepare("DELETE FROM `order_detail` WHERE `id` = ? AND `order_id` = ?");
        $stmt_delete->bind_param("ii", $detail_id, $order_id);
        $stmt_delete->execute();

        $stmt_sum = $conn->prepare("SELECT SUM(price * quantity) AS new_total FROM `order_detail` WHERE `order_id` = ?");
        $stmt_sum->bind_param("i", $order_id);
        $stmt_sum->execute();
        $res_sum = $stmt_sum->get_result();
        $row = $res_sum->fetch_assoc();
        $new_total = $row['new_total'] ? floatval($row['new_total']) : 0;

        $stmt_update = $conn->prepare("UPDATE `order` SET `total_amount` = ? WHERE `order_id` = ?");
        $stmt_update->bind_param("di", $new_total, $order_id);
        $stmt_update->execute();

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


// ลบรายการในบิลที่บันทึกไปแล้ว