<?php
header('Content-Type: application/json');
require_once 'db.php';

// รับค่าที่ส่งมาจาก JavaScript (JSON)
$input = json_decode(file_get_contents('php://input'), true);
$orderId = $input['order_id'] ?? null;
$cancelReason = $input['cancel_reason'] ?? 'ลูกค้ายกเลิกเองผ่านระบบ';

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบรหัสออเดอร์']);
    exit;
}

$sql = "UPDATE `order` SET status = 'cancelled', cancel_reason = ? WHERE order_id = ?";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $cancelReason, $orderId);

    if (mysqli_stmt_execute($stmt)) {

        $find_table_sql = "SELECT table_id FROM `order` WHERE order_id = '$orderId'";
        $find_table_query = mysqli_query($conn, $find_table_sql);

        if ($table_row = mysqli_fetch_assoc($find_table_query)) {
            $table_id = $table_row['table_id'];

            if (!empty($table_id)) {
                $update_table_sql = "UPDATE `tables` SET table_status = 'available' WHERE tables_id = '$table_id'";
                mysqli_query($conn, $update_table_sql);
            }
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัปเดตข้อมูลได้']);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'คำสั่ง SQL ผิดพลาด']);
}
