<?php
require 'db.php';

$order_id = $_GET['id'] ?? 0;
$order_id = intval($order_id);

if ($order_id <= 0) {
    echo "<p style='text-align:center;'>ไม่พบข้อมูลออเดอร์</p>";
    exit;
}

// ดึงข้อมูลออเดอร์หลัก
$order_sql = "SELECT * FROM `order` WHERE order_id = $order_id";
$order_query = mysqli_query($conn, $order_sql);
$order = mysqli_fetch_assoc($order_query);

if (!$order) {
    echo "<p style='text-align:center;'>ไม่พบข้อมูลออเดอร์</p>";
    exit;
}

// ดึงรายการสินค้า
$items_sql = "SELECT od.*, p.p_name 
              FROM `order_detail` od 
              LEFT JOIN `products` p ON od.product_id = p.p_id 
              WHERE od.order_id = $order_id";
$items_query = mysqli_query($conn, $items_sql);

$isCancelled = ($order['status'] === 'cancelled');
?>

<div style="font-size: 14px; color: #333;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
        <span><strong>เลขที่ออเดอร์ : </strong> #ORD-<?= str_pad((string)$order['order_id'], 4, '0', STR_PAD_LEFT) ?></span>
        <span><strong>โต๊ะ : </strong> <?= htmlspecialchars($order['table_id'] ?? '-') ?></span>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; color: #666; font-size: 13px;">
        <!-- ฝั่งซ้าย: วันที่-เวลา และ เหตุผลต่อข้างล่าง -->
        <div>
            <div><strong>วันที่-เวลา : </strong> <?= date('d/m/Y H:i', strtotime($order['created_at'] ?? 'now')) ?> น.</div>
            <?php if ($isCancelled): ?>
                <div style="margin-top: 6px; color: #6d6d6d;">
                    <strong>เหตุผลที่ยกเลิก : </strong> <?= htmlspecialchars($order['cancel_reason'] ?? '-') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ฝั่งขวา: สถานะออเดอร์ -->
        <div>
            <strong>สถานะ : </strong>
            <?php if ($isCancelled): ?>
                <span style="color: #dc3545; font-weight: bold;">ยกเลิก</span>
            <?php else: ?>
                <span style="color: #28a745; font-weight: bold;">ชำระแล้ว</span>
            <?php endif; ?>
        </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #ddd;">
                <th style="padding: 8px; text-align: left;">รายการ</th>
                <th style="padding: 8px; text-align: center;">จำนวน</th>
                <th style="padding: 8px; text-align: right;">ราคา</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items_query && mysqli_num_rows($items_query) > 0): ?>
                <?php while ($item = mysqli_fetch_assoc($items_query)): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 8px;">
                            <?= htmlspecialchars($item['product_name'] ?? 'สินค้า') ?>
                            <?php if (!empty($item['option_label'])): ?>
                                <br><small style="color:#777;">(<?= htmlspecialchars($item['option_label']) ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 8px; text-align: center;"><?= $item['quantity'] ?></td>
                        <td style="padding: 8px; text-align: right;"><?= number_format($item['price'] * $item['quantity'], 2) ?> ฿</td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center; padding: 12px; color: #888;">ไม่พบรายการสินค้า</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 16px; text-align: right; font-size: 16px; font-weight: bold; color: <?= $isCancelled ? '#dc3545' : '#28a745' ?>;">
        มูลค่าออเดอร์: <?= number_format($order['total_amount'] ?? 0, 2) ?> ฿
    </div>

    <!-- ปุ่มพิมพ์ใบเสร็จย้อนหลัง (แสดงเฉพาะบิลที่ชำระแล้ว) -->
    <?php if (!$isCancelled): ?>
        <div style="margin-top: 20px; text-align: center; border-top: 1px solid #eee; padding-top: 15px;">
            <button onclick="printReceipt(<?= $order['order_id'] ?>)" style="color: #1b1b1b; border: 1px solid oklch(0.84 0 0); padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 14px;">
                <i class="fa-solid fa-print"></i> พิมพ์ใบเสร็จย้อนหลัง
            </button>
        </div>
    <?php endif; ?>
</div>