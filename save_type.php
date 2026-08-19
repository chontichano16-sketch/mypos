<?php
include "db.php";
//เก็บค่าไว้ในตัวแปร
$type_name = $_POST['type_name'];

$add_type = "INSERT INTO type (type_name)
        VALUES ('$type_name')";
$check = mysqli_query($conn, $add_type) or die(mysqli_error($conn));
echo "บันทึกข้อมูลเรียบร้อย";
