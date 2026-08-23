
<?php
require_once 'db.php';

$table_id = $_POST['table_id'];
$payment_method = $_POST['payment_method'];

// อัปเดตข้อมูลบิลของโต๊ะนี้ ที่สถานะยังเป็น pending ให้กลายเป็น paid
$sql = "UPDATE `order` 
        SET status = 'paid', 
            payment_method = '$payment_method' 
        WHERE table_id = '$table_id' AND status = 'pending'";

if (mysqli_query($conn, $sql)) {
    if (mysqli_affected_rows($conn) > 0) {
        echo "Success";
    } else {
        echo "No pending bill found";
    }
} else {
    echo "Error: " . mysqli_error($conn);
}
?>