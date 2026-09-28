<?php
require 'db.php';

// กำหนดการส่งคืนค่าเป็น JSON สำหรับ AJAX 
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tableName = trim($_POST['table_name'] ?? '');

    if (!empty($tableName)) {
        // ป้องกัน SQL Injection
        $tableNameEscaped = mysqli_real_escape_string($conn, $tableName);

        // ตรวจสอบชื่อโต๊ะซ้ำ
        $checkQuery = "SELECT * FROM `tables` WHERE `tables_number` = '$tableNameEscaped' AND `is_active` = 1";
        $checkResult = mysqli_query($conn, $checkQuery);

        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'มีโต๊ะนี้อยู่ในระบบแล้ว กรุณาใช้ชื่ออื่น']);
            exit;
        }

        // ลิงก์สำหรับ QR Code
        $host = $_SERVER['HTTP_HOST']; //  127.0.0.1 หรือ localhost อัตโนมัติ
        $baseUrl = "http://" . $host . "/mypos/customer_menu.php?tables_id=";
        $qrLink = $baseUrl . urlencode($tableNameEscaped);

        // บันทึกข้อมูล
        $sql = "INSERT INTO `tables` (`tables_number`, `qr_link`, `table_status`, `is_active`) 
                VALUES ('$tableNameEscaped', '$qrLink', 'available', 1)";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มโต๊ะเรียบร้อยแล้ว']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกชื่อหรือหมายเลขโต๊ะ']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
