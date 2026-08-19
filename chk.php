<?php
session_start();

include("db.php");

if(isset($_POST['username']) && isset($_POST['pin'])){

    $username = $_POST['username'];
    $pin = md5($_POST['pin']);

    $sql = "SELECT * FROM users
            WHERE username='$username'
            AND pin='$pin'
            AND status='active'";

    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 1){

        $row = mysqli_fetch_assoc($result);

        $_SESSION["user_id"] = $row["user_id"];
        $_SESSION["fullname"] = $row["fullname"];
        $_SESSION["role"] = $row["role"];

        header("Location: index.php");
        exit();

    }else{

        echo "<script>";
        echo "alert('รหัส PIN ไม่ถูกต้อง');";
        echo "window.history.back();";
        echo "</script>";

    }

}else{

    header("Location: login.php");
    exit();

}
?>