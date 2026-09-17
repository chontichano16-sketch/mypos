<?php
require '../db.php';

// ตรวจสอบว่ามีการส่งค่า id มาและเป็นตัวเลข
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: Invalid Order ID");
}

$orderId = (int)$_GET['id'];

// ดึงข้อมูลบิลหลัก
$query = mysqli_query($conn, "SELECT * FROM `order` WHERE `order_id` = $orderId");
$res = mysqli_fetch_assoc($query);

// ตรวจสอบว่าพบบิล และบิลนั้นมี parent_order_id ให้ย้ายไปรวมหรือไม่
if (!$res || empty($res['parent_order_id'])) {
    die("Error: Order not found or missing parent order.");
}

$parent = (int)$res['parent_order_id'];
$amount = (float)$res['total_amount'];

// เริ่ม Transaction (All-or-Nothing)
mysqli_begin_transaction($conn);

try {
    // ย้ายรายการสินค้าไปยังบิลหลัก
    mysqli_query($conn, "UPDATE `order_detail` SET `order_id` = $parent WHERE `order_id` = $orderId");
    
    // บวกรวมยอดเงินในบิลหลัก
    mysqli_query($conn, "UPDATE `order` SET `total_amount` = `total_amount` + $amount WHERE `order_id` = $parent");
    
    // ลบบิลย่อยทิ้ง
    mysqli_query($conn, "DELETE FROM `order` WHERE `order_id` = $orderId");

    // ยืนยันการเปลี่ยนแปลง (Commit) เมื่อคำสั่งทั้งหมดทำงานสำเร็จ
    mysqli_commit($conn);
    
    // สำเร็จ อาจจะ redirect หรือ echo กลับไป
    echo "Success";

} catch (Exception $e) {
    // หากมี Error ให้ย้อนกลับข้อมูลทั้งหมด (Rollback) เพื่อไม่ให้ข้อมูลพัง
    mysqli_rollback($conn);
    echo "Error processing order: " . $e->getMessage();
}
?>