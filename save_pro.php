<?php
include "db.php";
//เก็บค่าไว้ในตัวแปร


$p_name  = $_POST['p_name'];
$p_price = $_POST['p_price'];
$p_img = "";
$type_id = $_POST['type_id'];



//เช็คว่ามีการอัปโหลดรูป
if (isset($_FILES['p_img']['name']) && $_FILES['p_img']['name'] != "") {
    $p_img = "img_" . date("His") . ".jpg";
    move_uploaded_file($_FILES['p_img']["tmp_name"], "upload/" . $p_img);
}

$add_product = "INSERT INTO products (p_name, p_price, p_img, type_id)
        VALUES ('$p_name','$p_price','$p_img', '$type_id')";

$check = mysqli_query($conn, $add_product) or die(mysqli_error($conn));

if ($check) {
    echo "success";
} else {
    echo "error: " . mysqli_error($conn);
}
?>
