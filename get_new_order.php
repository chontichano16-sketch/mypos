<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style2.css">
</head>

<body>
    <script src="script.js"></script>
</body>

</html>


<?php
require 'db.php';
$res = mysqli_query($conn, "SELECT * FROM `order` WHERE source='qr' AND status IN ('pending', 'cooking', 'new_item') ORDER BY created_at DESC");

if (mysqli_num_rows($res) == 0) {
    echo "ไม่มีออเดอร์ใหม่";
    exit;
}

echo "<table class='table-order'>
        <thead>
            <tr>
            <th>โต๊ะ</th>
            <th>เวลา</th>
            <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>";
while ($row = mysqli_fetch_assoc($res)) {
    echo "<tr>
            <td>{$row['table_id']}</td>
            <td>{$row['created_at']}</td>
            <td><button class='btn-print' onclick='processOrder({$row['order_id']})'>รับออเดอร์/ปริ้น</button></td>
          </tr>";
}
echo "</tbody></table>";
