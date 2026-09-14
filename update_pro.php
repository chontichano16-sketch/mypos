<?php
include "db.php";
require_once "product_options_lib.php";

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
        try {
            ensureProductOptionsTable($conn);

            $delete_options = $conn->prepare('DELETE FROM product_options WHERE product_id = ?');
            $product_id_int = (int)$p_id;
            $delete_options->bind_param('i', $product_id_int);
            $delete_options->execute();

            $option_names = $_POST['option_name'] ?? [];
            $option_adjustments = $_POST['option_adjustment'] ?? [];
            $insert_option = $conn->prepare(
                'INSERT INTO product_options (product_id, option_name, price_adjustment, sort_order) VALUES (?, ?, ?, ?)'
            );

            foreach ($option_names as $index => $option_name) {
                $option_name = trim((string)$option_name);
                $adjustment = $option_adjustments[$index] ?? null;

                if ($option_name === '' || !is_numeric($adjustment)) {
                    continue;
                }

                $adjustment = (float)$adjustment;
                $sort_order = (int)$index;
                $insert_option->bind_param('isdi', $product_id_int, $option_name, $adjustment, $sort_order);
                $insert_option->execute();
            }
        } catch (Exception $e) {
            echo "<script>
                alert('บันทึกสินค้าแล้ว แต่บันทึกตัวเลือกไม่สำเร็จ: " . addslashes($e->getMessage()) . "');
                window.location.href = 'show_pro.php';
            </script>";
            exit;
        }

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
