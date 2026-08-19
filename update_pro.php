<?php
session_start();
include "db.php"; 


$p_id = $_POST['p_id'];
$type_id = $_POST['type_id'];
$p_name = $_POST['p_name'];
$p_price = $_POST['p_price'];
$old_img = $_POST['old_img']; // ชื่อรูปเดิมที่ส่งแบบ hidden มา

// 2. ตรวจสอบการอัปโหลดรูปภาพใหม่
if (isset($_FILES['p_img']['name']) && $_FILES['p_img']['name'] != "") {
    
    // ตั้งชื่อโฟลเดอร์ที่เก็บรูปภาพ
    $folder_upload = "upload/"; 
    // ดึงนามสกุลไฟล์เดิมออกมา
    $ext = pathinfo(basename($_FILES['p_img']['name']), PATHINFO_EXTENSION);
    // สร้างชื่อไฟล์ใหม่ไม่ให้ชื่อซ้ำกันในโฟลเดอร์ 
    $new_img_name = "product_" . $p_id . "_" . time() . "." . $ext;
    // ที่อยู่เต็มในการบันทึกไฟล์ใหม่
    $path_upload = $folder_upload . $new_img_name;
    
    if (move_uploaded_file($_FILES['p_img']['tmp_name'], $path_upload)) {
        $p_img = $new_img_name;

        if ($old_img != "" && file_exists($folder_upload . $old_img)) {
            unlink($folder_upload . $old_img);
        }
    } else {
        $p_img = $old_img;
    }
    
} else {
    $p_img = $old_img;
}

$sql_update = "UPDATE products SET 
                type_id = '$type_id', 
                p_name = '$p_name', 
                p_price = '$p_price', 
                p_img = '$p_img' 
               WHERE p_id = '$p_id'";

$query_update = mysqli_query($conn, $sql_update);

if ($query_update) {
    echo "<script>
            alert('แก้ไขข้อมูลสินค้าสำเร็จเรียบร้อยแล้ว!');
            window.location = 'index.php'; 
          </script>";
} else {
    echo "<script>
            alert('เกิดข้อผิดพลาด: ไม่สามารถแก้ไขข้อมูลได้');
            window.history.back(); 
          </script>";
}

mysqli_close($conn);
?>