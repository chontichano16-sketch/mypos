<?php
require_once "db.php";

// เช็คว่ามีการส่งข้อมูลแบบ POST มาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าและป้องกัน SQL Injection
    $type_id = mysqli_real_escape_string($conn, $_POST['type_id']);
    $type_name = mysqli_real_escape_string($conn, $_POST['type_name']);

    // ตรวจสอบว่าข้อมูลไม่เป็นค่าว่าง
    if (!empty($type_id) && !empty($type_name)) {
        
        // คำสั่ง SQL สำหรับอัปเดตข้อมูล
        $sql = "UPDATE type SET type_name = '$type_name' WHERE type_id = '$type_id'";
        
        if (mysqli_query($conn, $sql)) {
            echo "<script>
                    alert('อัปเดตประเภทสินค้าเรียบร้อยแล้ว');
                    window.location.href = 'show_type.php'; // <--- เปลี่ยนชื่อไฟล์ให้ตรงกับหน้าประเภทสินค้าของคุณ
                  </script>";
        } else {
            echo "<script>
                    alert('เกิดข้อผิดพลาด: " . mysqli_error($conn) . "');
                    window.history.back();
                  </script>";
        }
    } else {
        echo "<script>
                alert('กรุณากรอกชื่อประเภทสินค้าให้ครบถ้วน');
                window.history.back();
              </script>";
    }
}
?>