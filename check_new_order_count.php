<?php
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT COUNT(*) as total FROM `order` WHERE source='qr' AND status IN ('new_item')";
$result = mysqli_query($conn, $sql);
$total = 0;

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total = (int)$row['total'];
}

echo json_encode(['count' => $total]);
?>