<?php
require_once "db.php";

// เช็คว่ามีการส่งค่า id มาทาง URL หรือไม่
if (isset($_GET['id'])) {
    // รับค่าและป้องกัน SQL Injection
    $type_id = mysqli_real_escape_string($conn, $_GET['id']);

    // คำสั่ง SQL สำหรับลบข้อมูล
    $sql = "DELETE FROM type WHERE type_id = '$type_id'";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('ลบประเภทสินค้าเรียบร้อยแล้ว');
                window.location.href = 'show_type.php'; // <--- เปลี่ยนชื่อไฟล์ให้ตรงกับหน้าประเภทสินค้าของคุณ
              </script>";
    } else {
        echo "<script>
                alert('เกิดข้อผิดพลาดในการลบ: อาจมีสินค้าที่กำลังใช้งานหมวดหมู่นี้อยู่');
                window.history.back();
              </script>";
    }
} else {
    echo "<script>window.location.href = 'show_type.php';</script>";
}
?>