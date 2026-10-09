<?php
session_start();
require_once "db.php";

// ดักจับชื่อตัวแปรเชื่อมต่อ DB
if (!isset($conn)) {
    if (isset($connect)) $conn = $connect;
    elseif (isset($con)) $conn = $con;
    elseif (isset($db)) $conn = $db;
}

// เคลียร์คิวคำสั่งค้างใน MySQL เพื่อป้องกันปัญหา Commands out of sync
while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
    if ($res = mysqli_store_result($conn)) {
        mysqli_free_result($res);
    }
}

if (isset($_POST['pin']) && $_POST['pin'] !== '') {

    $raw_pin = $_POST['pin'];
    $md5_pin = md5($raw_pin);

    // ป้องกัน SQL Injection
    $pin_safe = mysqli_real_escape_string($conn, $md5_pin);

    // ค้นหาข้อมูลผู้ใช้งาน (ดึงเฉพาะคอลัมน์ที่มีอยู่ในฐานข้อมูล)
    $sql = "SELECT user_id, username, role 
            FROM `users` 
            WHERE `pin` = '$pin_safe' AND `status` = 'active' 
            LIMIT 1";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // บันทึกข้อมูลเข้า Session
        $_SESSION["user_id"]  = $row["user_id"];
        $_SESSION["username"] = $row["username"];
        $_SESSION["fullname"] = $row["username"]; 
        $_SESSION["role"]     = $row["role"];

        header("Location: index.php");
        exit();
    } else {
        // เมื่อรหัสผิด ให้เด้งกลับหน้า login.php พร้อมแนบ error
        header("Location: login.php?error=invalid_pin");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
