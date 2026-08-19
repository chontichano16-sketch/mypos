<?php
// db.php - เชื่อมต่อฐานข้อมูล
 $servername = "localhost";
 $username = "root";
 $password = "";
 $db_name = "mypos_db";

// สร้างการเชื่อมต่อ
 $conn = new mysqli($servername, $username, $password, $db_name,);

// ตรวจสอบการเชื่อมต่อ
 if ($conn->connect_error) {
    die("connection failed: " . $conn->connect_error);
}

//ตั้งค่าเป็นภาษาไทย
 $conn->set_charset("utf8");

//ถ้าเชื่อมได้ จะขึ้นข้อความนี้
// echo "เชื่อมต่อได้จ้า";
?>

