<?php
require 'db.php';

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo "<div style='text-align:center; padding:10px;'>ไม่พบข้อมูลออเดอร์</div>";
    exit;
}

$orderId = intval($_GET['order_id']);

$sql = "SELECT od.*, p.p_name 
        FROM `order_detail` od 
        LEFT JOIN `products` p ON od.product_id = p.p_id 
        WHERE od.order_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $orderId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) === 0) {
    echo "<div style='text-align:center; padding:10px;'>ไม่มีรายการอาหารในออเดอร์นี้</div>";
    exit;
}

$total = 0;
?>

<table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
    <thead>
        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
            <th style="padding: 10px;">รายการเมนู</th>
            <th style="padding: 10px; text-align: center;">จำนวน</th>
            <th style="padding: 10px; text-align: right;">ราคา</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($item = mysqli_fetch_assoc($res)): ?>
            <?php
            $name = htmlspecialchars($item['p_name'] ?? 'ไม่พบชื่อสินค้า');
            $qty = intval($item['quantity']);
            $price = floatval($item['price']);
            $subtotal = $qty * $price;
            $total += $subtotal;
            $opt = htmlspecialchars($item['option_label'] ?? '');
            $remark = htmlspecialchars($item['remark'] ?? '');
            ?>
            <tr style="border-bottom: 1px solid #e9ecef;">
                <td style="padding: 10px;">
                    <strong><?php echo $name; ?></strong>
                    <?php if (!empty($opt)): ?>
                        <br><small style="color: oklch(55.6% 0 none);">• <?php echo $opt; ?></small>
                    <?php endif; ?>
                    <?php if (!empty($remark)): ?>
                        <br><small style="color: oklch(55.6% 0 none);">• หมายเหตุ: <?php echo $remark; ?></small>
                    <?php endif; ?>
                </td>
                <td style="padding: 10px; text-align: center; font-weight: bold;"><?php echo $qty; ?></td>
                <td style="padding: 10px; text-align: right;"><?php echo number_format($subtotal, 2); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
    <tfoot>
        <tr style="background-color: #f8f9fa; font-weight: bold;">
            <td colspan="2" style="padding: 10px; text-align: right;">ราคารวม:</td>
            <td style="padding: 10px; text-align: right; color: #28a745; font-size: 16px;"><?php echo number_format($total, 2); ?> บาท</td>
        </tr>
    </tfoot>
</table>