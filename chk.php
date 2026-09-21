<?php
session_start();
require_once "db.php";

// ดักจับชื่อตัวแปรเชื่อมต่อ DB
if (!isset($conn)) {
    if (isset($connect)) $conn = $connect;
    elseif (isset($con)) $conn = $con;
    elseif (isset($db))  $conn = $db;
}

if (isset($_POST['pin'])) {

    $raw_pin = $_POST['pin'];
    $md5_pin = md5($raw_pin);

    // ค้นหาเฉพาะ PIN ที่ตรงกัน และสถานะเป็น active (ตัดการเช็ค username ออก)
    $sql = "SELECT * FROM users 
            WHERE (pin = '$raw_pin' OR pin = '$md5_pin')
            AND status = 'active'";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) >= 1) {
        $row = mysqli_fetch_assoc($result);

        // ระบบจะดึงข้อมูลของคนที่ตรงกับ PIN นั้นมาเก็บเข้า Session
        $_SESSION["user_id"]  = $row["user_id"];
        $_SESSION["fullname"] = $row["fullname"] ?? $row["username"];
        $_SESSION["role"]     = $row["role"];

        header("Location: index.php");
        exit();
    } else {
        // เมื่อรหัสผิด ให้เด้งกลับหน้า login.php พร้อมแนบ error ไปด้วย
        header("Location: login.php?error=invalid_pin");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>