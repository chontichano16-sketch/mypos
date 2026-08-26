<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบก่อนทำรายการ']);
    exit();
}

require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);

    // เริ่ม Transaction เพื่อความปลอดภัย
    mysqli_begin_transaction($conn);

    try {
        // 1. ลบรายการเมนูย่อยในบิลก่อน (ถ้ามีตาราง order_detail)
        $sql_detail = "DELETE FROM order_detail WHERE order_id = ?";
        $stmt_detail = mysqli_prepare($conn, $sql_detail);
        if ($stmt_detail) {
            mysqli_stmt_bind_param($stmt_detail, "i", $order_id);
            mysqli_stmt_execute($stmt_detail);
            mysqli_stmt_close($stmt_detail);
        }

        // 2. ลบบิลหลักจากตาราง order
        $sql_order = "DELETE FROM `order` WHERE order_id = ?";
        $stmt_order = mysqli_prepare($conn, $sql_order);
        mysqli_stmt_bind_param($stmt_order, "i", $order_id);
        mysqli_stmt_execute($stmt_order);
        mysqli_stmt_close($stmt_order);

        // ยืนยันการทำรายการลบ
        mysqli_commit($conn);
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        // หากเกิดข้อผิดพลาด ให้ยกเลิกการลบทั้งหมด
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'คำขอไม่ถูกต้อง']);
}
?>