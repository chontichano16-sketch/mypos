<?php
header('Content-Type: application/json; charset=utf-8');
require_once "db.php"; 

// ดึงข้อมูลจากตาราง order เรียงจากบิลล่าสุดไปเก่าสุด
$sql = "SELECT order_id, table_id, created_at, note FROM `order` ORDER BY order_id DESC";
$result = mysqli_query($conn, $sql);

$bills = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // แปลง created_at (int) เป็นรูปแบบ วัน/เดือน/ปี ชั่วโมง:นาที
        $row['formatted_date'] = date('d/m/Y H:i', $row['created_at']);
        $bills[] = $row;
    }
}

echo json_encode($bills);
?>