<?php
session_start();
include "db.php";

$p_id = $_GET['p_id'];

if (isset($p_id) && $p_id != "") {

    $sql_img = "SELECT p_img FROM products where p_id = '$p_id'";
    $res_id = mysqli_query($conn, $sql_img);
    $row_img = mysqli_fetch_array($res_id);
    $old_img = $row_img['p_img'];

    $sql_delete = "DELETE FROM products where p_id='$p_id'";
    $query_delete = mysqli_query($conn, $sql_delete);

    if ($query_delete) {
        if ($old_img != "" && file_exists("upload/" . $old_img)) {
            unlink("upload/" . $old_img);
        }
        echo "<script>
            alert('ลบสินค้าเรียนร้อย');
            window.location = 'show_pro.php';
            </script>";
    } else {
        echo "<script>
            alert('เกิดข้อผิดพลาด: ไม่สามารถลบสินค้าได้');
            window.history.back();
            </script>";
    }
} else {
    header("location: show_pro.php");
}
mysqli_close($conn);
?>
