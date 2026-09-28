<?php
require 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['new_name'])) {
    $id = intval($_POST['id']);
    $newName = mysqli_real_escape_string($conn, trim($_POST['new_name']));

    // เช็คชื่อซ้ำ (ยกเว้นโต๊ะตัวเอง)
    $checkQuery = "SELECT * FROM `tables` WHERE `tables_number` = '$newName' AND `is_active` = 1 AND `tables_id` != $id";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'ชื่อโต๊ะนี้มีอยู่แล้ว']);
        exit;
    }

    // อัปเดตข้อมูล
    $host = $_SERVER['HTTP_HOST']; //  127.0.0.1 หรือ localhost อัตโนมัติ
    $baseUrl = "http://" . $host . "/mypos/customer_menu.php?tables_id=";
    
    $newQrLink = $baseUrl . urlencode($newName);
    $sql = "UPDATE `tables` SET `tables_number` = '$newName', `qr_link` = '$newQrLink' WHERE `tables_id` = $id";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
}
