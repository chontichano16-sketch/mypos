<?php
require 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['action'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    // เช็คว่าต้องการทำอะไร
    if ($action === 'hide') {
        // ซ่อนโต๊ะ
        $sql = "UPDATE `table` SET `is_active` = 0 WHERE `table_id` = $id";
    } else if ($action === 'force_delete') {
        // ลบถาวร
        $sql = "DELETE FROM `table` WHERE `table_id` = $id";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'คำสั่งไม่ถูกต้อง']);
        exit;
    }

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'การส่งข้อมูลไม่ถูกต้อง']);
}
