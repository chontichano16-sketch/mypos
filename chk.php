<?php
session_start();
require_once "db.php";

// ดักจับชื่อตัวแปรเชื่อมต่อ DB
if (!isset($conn)) {
    if (isset($connect)) $conn = $connect;
    elseif (isset($con)) $conn = $con;
    elseif (isset($db))  $conn = $db;
}

if (isset($_POST['username']) && isset($_POST['pin'])) {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $raw_pin  = $_POST['pin'];
    $md5_pin  = md5($raw_pin);

    // ค้นหาทั้งรหัสธรรมดา และ MD5
    $sql = "SELECT * FROM users 
            WHERE username = '$username' 
            AND (pin = '$raw_pin' OR pin = '$md5_pin')
            AND status = 'active'";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        $_SESSION["user_id"]  = $row["user_id"];
        $_SESSION["fullname"] = $row["fullname"] ?? $row["username"];
        $_SESSION["role"]     = $row["role"];

        header("Location: index.php");
        exit();
    } else {
        echo "<script>";
        echo "alert('รหัส PIN ไม่ถูกต้อง หรือบัญชีไม่ได้อยู่ในสถานะ active');";
        echo "window.history.back();";
        echo "</script>";
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>