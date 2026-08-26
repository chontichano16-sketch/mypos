<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์มใน Modal
    $p_id = mysqli_real_escape_string($conn, $_POST['p_id']);
    $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
    $p_price = mysqli_real_escape_string($conn, $_POST['p_price']);
    $type_id = mysqli_real_escape_string($conn, $_POST['type_id']);
    $old_img = $_POST['old_img'];

    $p_img = $old_img; // ตั้งต้นให้ใช้รูปเดิม

    // ตรวจสอบว่ามีการอัปโหลดรูปภาพใหม่มาด้วยหรือไม่
    if (isset($_FILES['p_img']['name']) && $_FILES['p_img']['name'] != "") {
        $file_name = $_FILES['p_img']['name'];
        $file_tmp = $_FILES['p_img']['tmp_name'];
        
        // แยกนามสกุลไฟล์และตั้งชื่อใหม่เพื่อป้องกันไฟล์ซ้ำ
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = "product_" . uniqid() . "." . $file_ext;
        $upload_dir = "upload/";

        // อัปโหลดไฟล์ใหม่ไปยังโฟลเดอร์ upload
        if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            $p_img = $new_file_name;

            // ลบรูปภาพเก่าทิ้ง (ถ้ามีรูปเก่าและไฟล์มีอยู่จริง)
            if (!empty($old_img) && file_exists($upload_dir . $old_img)) {
                @unlink($upload_dir . $old_img);
            }
        }
    }

    // คำสั่ง SQL สำหรับอัปเดตข้อมูลสินค้า
    $sql = "UPDATE products SET 
            p_name = '$p_name', 
            p_price = '$p_price', 
            type_id = '$type_id', 
            p_img = '$p_img' 
            WHERE p_id = '$p_id'";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        echo "<script>
            alert('อัปเดตข้อมูลสินค้าเรียบร้อยแล้ว');
            window.location.href = 'show_pro.php';
        </script>";
    } else {
        echo "<script>
            alert('เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . mysqli_error($conn) . "');
            window.history.back();
        </script>";
    }
} else {
    header("Location: show_pro.php");
}
?>