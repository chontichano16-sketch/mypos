<?php
require 'db.php';
$res = mysqli_query($conn, "SELECT * FROM `order` WHERE source='qr' AND status IN ('new_item') ORDER BY created_at DESC");
if (!$res || mysqli_num_rows($res) == 0) {
    echo "<div style='text-align:center; padding: 20px; color: #666;'>ไม่มีออเดอร์ใหม่</div>";
    exit;
}
?>
<style>
    .action-dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        margin-top: 6px;
        background-color: #ffffff;
        min-width: 160px;
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        z-index: 1000;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        text-align: left;
    }

    .action-dropdown-menu.show {
        display: block !important;
    }

    .action-dropdown-menu a {
        color: #374151;
        padding: 10px 14px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        transition: background-color 0.15s ease-in-out;
    }

    .action-dropdown-menu a:hover {
        background-color: #f3f4f6;
    }

    .action-dropdown-menu a.text-danger {
        color: #dc2626;
    }

    .action-dropdown-menu a.text-danger:hover {
        background-color: #fef2f2;
    }
</style>

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

                        <div style="position: relative; display: inline-block;">
                            <button style="background: transparent; border: none; font-size: 16px; cursor: pointer;color: #616161;" type="button" class="btn-list" onclick="toggleActionMenu(event, '<?= $row['order_id'] ?>')">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div id="action-menu-<?= $row['order_id'] ?>" class="action-dropdown-menu">
                                <a href="javascript:void(0)" onclick="viewOrderDetails('<?= $row['order_id'] ?>')">
                                    <!-- <i class="fa-solid fa-eye"></i>-->ดูรายการ
                                </a>
                                <a href="javascript:void(0)" onclick="processOrder('<?= $row['order_id'] ?>')">
                                    <!-- <i class="fa-solid fa-print"></i>  --> รับออเดอร์/ปริ้น
                                </a>
                                <a href="javascript:void(0)" onclick="cancelOrder('<?= $row['order_id'] ?>')" class="text-danger">
                                    <!-- <i class="fa-solid fa-circle-xmark"></i>  --> ยกเลิกออเดอร์
                                </a>
                            </div>
                        </div>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>