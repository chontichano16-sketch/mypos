<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'db.php';

$orderId = (int)($_GET['order_id'] ?? $_GET['id'] ?? 0);
if ($orderId <= 0) {
    die("ไม่มีรายการให้พิมพ์ (order_id ไม่ถูกต้อง)");
}

// รองรับ pids: ถ้าส่งมา = พิมพ์เฉพาะรายการนั้น, ถ้าไม่ส่ง = ใช้ printed flag
$pids = isset($_GET['pids']) && $_GET['pids'] !== ''
    ? array_filter(array_map('intval', explode(',', $_GET['pids'])))
    : [];

$where = "od.order_id = $orderId";
if (!empty($pids)) {
    $where .= " AND od.product_id IN (" . implode(',', $pids) . ")";
} else {
    $where .= " AND od.printed = 0";
}

$sql = "SELECT od.*, p.p_name
        FROM order_detail od
        JOIN products p ON od.product_id = p.p_id
        WHERE $where";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("SQL Error: " . mysqli_error($conn));
}

$rows = [];
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}
if (count($rows) === 0) {
    die("ไม่มีรายการใหม่ให้พิมพ์ (order_id = $orderId)");
}

// ดึงเลขโต๊ะ
$o = mysqli_fetch_assoc(mysqli_query($conn, "SELECT table_id FROM `order` WHERE order_id = $orderId"));
$tableId = $o['table_id'] ?? '-';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <title>ใบสั่งครัว #<?= $orderId ?></title>
    <style>
        body {
            font-family: 'Tahoma', sans-serif;
            width: 280px;
            margin: 0;
            padding: 8px;
        }

        h2 {
            text-align: center;
            margin: 4px 0;
            font-size: 18px;
        }

        .info {
            font-size: 13px;
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        td {
            font-size: 14px;
            padding: 4px 0;
            vertical-align: top;
        }

        .qty {
            text-align: right;
            width: 40px;
            font-weight: bold;
        }

        .remark {
            font-size: 12px;
            color: #444;
            padding-left: 8px;
        }
    </style>
</head>

<body>
    <h2>*** ใบสั่งอาหาร ***</h2>
    <div class="info">
        โต๊ะ: <b style="font-size:16px"><?= htmlspecialchars($tableId) ?></b><br>
        บิล: #<?= $orderId ?><br>
        เวลา: <?= date('d/m/Y H:i') ?>
    </div>
    <table>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['p_name']) ?></td>
                <td class="qty">x<?= (int)$row['quantity'] ?></td>
            </tr>
            <?php if (!empty($row['remark'])): ?>
                <tr>
                    <td colspan="2" class="remark">* <?= htmlspecialchars($row['remark']) ?></td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>

    <script>
        window.onload = function() {
            window.print();
            setTimeout(function() {
                window.close();
            }, 500);
        };
    </script>
</body>

</html>
<?php
// มาร์คว่าพิมพ์แล้ว — ทำท้ายสุดหลังแสดงผลสำเร็จ กันพิมพ์ซ้ำตอนกด F5
mysqli_query($conn, "UPDATE order_detail SET printed = 1 WHERE order_id = $orderId AND printed = 0");
?>