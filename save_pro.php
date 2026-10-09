<?php
include "db.php";
//เก็บค่าไว้ในตัวแปร


$product_name  = $_POST['product_name'];
$product_price = $_POST['product_price'];
$product_img = "";
$type_id = $_POST['type_id'];



//เช็คว่ามีการอัปโหลดรูป
if (isset($_FILES['product_img']['name']) && $_FILES['product_img']['name'] != "") {
    $product_img = "img_" . date("His") . ".jpg";
    move_uploaded_file($_FILES['product_img']["tmproduct_name"], "upload/" . $product_img);
}

$add_product = "INSERT INTO product (product_name, product_price, product_img, type_id)
        VALUES ('$product_name','$product_price','$product_img', '$type_id')";

$check = mysqli_query($conn, $add_product) or die(mysqli_error($conn));

if ($check) {
    echo "success";
} else {
    echo "error: " . mysqli_error($conn);
}
?>
