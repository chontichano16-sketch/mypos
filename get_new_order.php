<?php
require 'db.php';
$res = mysqli_query($conn, "SELECT * FROM `order` WHERE source='qr' AND status IN ('new_item') ORDER BY created_at DESC");
if (!$res || mysqli_num_rows($res) == 0) {
    echo "<div style='text-align:center; padding: 20px; color: #666;'>ไม่มีออเดอร์ใหม่</div>";
    exit;
}
?>

<table class="table-order">
    <thead>
        <tr>
            <th style="width: 20%;">โต๊ะ</th>
            <th style="width: 40%;">เวลา</th>
            <th style="width: 40%;">จัดการ</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['table_id']); ?></td>
                <td><?php echo date('H:i', strtotime($row['created_at'])); ?></td>
                <td>
                    <div class="btn-ordernew">
                        <button type="button" class="btn-detail" onclick="viewOrderDetails(<?php echo $row['order_id']; ?>)">
                            ดูรายการ
                        </button>
                        <button class="btn-print" onclick="processOrder(<?php echo $row['order_id']; ?>)">
                            รับออเดอร์/ปริ้น
                        </button>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>