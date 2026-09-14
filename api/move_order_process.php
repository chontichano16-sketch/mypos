<?php
require '../db.php';
$orderId = (int)$_GET['id'];

// ดึงข้อมูลบิลหลักมาเตรียมไว้
$res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `order` WHERE `order_id` = $orderId"));
$parent = $res['parent_order_id'];

// ย้ายข้อมูล
mysqli_query($conn, "UPDATE `order_detail` SET `order_id` = $parent WHERE `order_id` = $orderId");
mysqli_query($conn, "UPDATE `order` SET `total_amount` = `total_amount` + {$res['total_amount']} WHERE `order_id` = $parent");
mysqli_query($conn, "DELETE FROM `order` WHERE `order_id` = $orderId");
?>