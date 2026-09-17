<?php

date_default_timezone_set('Asia/Bangkok');
// db.php - เชื่อมต่อฐานข้อมูล
 $servername = "127.0.0.1"; //localhost
 $username = "root";
 $password = "";
 $db_name = "mypos_db";

// สร้างการเชื่อมต่อ
 $conn = new mysqli($servername, $username, $password, $db_name);

// ตรวจสอบการเชื่อมต่อ
 if ($conn->connect_error) {
    die("connection failed: " . $conn->connect_error);
}

//ตั้งค่าเป็นภาษาไทย
 $conn->set_charset("utf8");
 
?>

