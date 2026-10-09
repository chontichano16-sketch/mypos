<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require 'db.php';
include "all_popup.php";

$search = $_GET['search'] ?? '';
$where = "WHERE status IN ('paid', 'cancelled')";

if (!empty($search)) {
    $searchEscaped = mysqli_real_escape_string($conn, $search);
    $where .= " AND (`order`.order_id = '$searchEscaped' OR `order`.table_id = '$searchEscaped')";
}

$sql = "SELECT `order`.*, table.table_number FROM `order` LEFT JOIN `table` ON `order`.table_id = table.table_id
$where ORDER BY `order`.order_id DESC LIMIT 100";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ประวัติการขาย - POS</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" crossorigin="anonymous"
        referrerpolicy="no-referrer">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: sans-serif;
            overflow-y: auto;
        }

        .card {
            padding: 20px;
            border-radius: 8px;
            width: min(1200px, calc(100% - 32px));
            margin: 32px auto;
        }

        .detail-history {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }

        .head-history {
            background-color: #fff;
            padding: 10px;
            border-radius: 12px;
            margin-bottom: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }

        .search-box-history {
            display: flex;
            gap: 10px;
            margin-left: 10px;
        }

        .search-box-history input {
            padding: 8px 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 250px;
        }

        .search-box-history button {
            padding: 8px 16px;
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            color: #3f342d;
        }

        th {
            background: #f8f9fa;
        }

        .btn-print-h {
            color: #414141;
            border: 1px solid oklch(0.84 0 0);
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-detail-h1 {
            color: #414141;
            border: 1px solid oklch(0.84 0 0);
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-detail-h2 {
            color: #414141;
            border: 1px solid oklch(0.84 0 0);
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-print-h:hover {
            background: oklch(93% 0.034 272.788);
        }

        .user-menu {
            position: relative;
            display: inline-block;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #333;
            padding: 6px 10px;
            border-radius: 8px;
            transition: background .2s;
        }

        .user-info:hover {
            background: #f0f0f0;
        }

        .user-info i {
            font-size: 22px;
        }

        .user-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 180px;
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .12);
            padding: 6px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: all .18s ease;
        }

        .user-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: #333;
            text-decoration: none;
            font-size: 15px;
        }

        .user-dropdown a:hover {
            background: #f5f5f5;
        }

        .user-dropdown a.logout {
            color: #d9534f;
        }

        .user-dropdown a.logout:hover {
            background: #fdecea;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="dropdown">
            <button onclick="toggleMenu(event)" class="dropbtn"><i class="fa-solid fa-bars"></i></button>

            <div id="myDropdown" class="dropdown-content" style="border: none;">
                <button class="menu-btn"> <i class="bi bi-chevron-down" style="float: right;"></i></i><i class="fa-solid fa-chair"></i> จัดการข้อมูลโต๊ะ</button>
                <ul class="submenu">
                    <li><a href="show_table.php"> รายการโต๊ะทั้งหมด</a></li>
                    <li><button onclick="openAddTableModal()">เพิ่มโต๊ะ</button></li>
                    <li><a href="print_qr.php"><!--<i class="bi bi-qr-code">--></i> พิมพ์ QR Code โต๊ะ</a></li>
                </ul>
                <button class="menu-btn"><i class="bi bi-chevron-down" style="float: right;"></i><i class="fa-solid fa-utensils"></i> จัดการข้อมูลเมนูอาหาร</button>

                <ul class="submenu">
                    <li><button onclick="openModal('product')">เพิ่มสินค้า</button></li>
                    <li><button onclick="openModal('type')">เพิ่มประเภทสินค้า</button></li>
                    <li><a href="show_pro.php" style="border-bottom: 1px solid #63554c1f;">รายการสินค้าทั้งหมด</a></li>
                    <li><a href="show_type.php" style="border-bottom: 1px solid #63554c1f;">ประเภทสินค้าทั้งหมด</a></li>
                </ul>

                <a href="sale_report.php"><i class="fa-solid fa-chart-line"></i> รายงานยอดขาย</a>
            </div>
        </div>


        <div class="nav-report">

            <div class="nav-report-header">
                <a href="index.php">หน้าร้าน</a>
                <span>/</span>
                <span>ประวัติการขาย</span>
            </div>

            <div class="btn-navreport">
                <button type="button" onclick="window.location.reload();">
                    <span><i class="fa-solid fa-rotate"></i></span> รีเฟรชข้อมูล
                </button>
                <button type="button" id="history-report">
                    <a href="sale_report.php"><i class="fa-solid fa-file"></i> รายงานยอดขาย</a>
                </button>
            </div>
        </div>

        <div class="user-menu">
            <button type="button" class="user-info" id="userInfoBtn" onclick="toggleUserMenu(event)">
                <?php echo $_SESSION["fullname"]; ?>
                <i class="fa-solid fa-circle-user"></i>
            </button>

            <div class="user-dropdown" id="userDropdown">
                <!-- <a href="profile.php"><i class="fa-solid fa-user"></i> โปรไฟล์</a> -->
                <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
            </div>
        </div>

    </nav>

    <div class="card">
        <section class="head-history">
            <h2 style="color: #3f342d;"><i class="fa-solid fa-scroll"></i> ประวัติการขาย (สำหรับสั่งพิมพ์ย้อนหลัง)</h2>
            <!-- ฟอร์มค้นหาเลขโต๊ะ -->
            <form class="search-box-history" method="GET">
                <input id="searchInput" type="text" name="search" placeholder="ค้นหาเลขบิล หรือ โต๊ะ..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">ค้นหา</button>
                <a href="sales_history.php" style="align-self:center; text-decoration:none; color:#666;">รีเซ็ต</a>
            </form>
        </section>

        <section class="detail-history">
            <table>
                <thead>
                    <tr>
                        <th style="text-align: center;">วัน/เวลา</th>
                        <th style="text-align: center;">เลขที่ออเดอร์</th>
                        <th style="text-align: center;">โต๊ะ</th>
                        <th style="text-align: center;">ราคารวม</th>
                        <th style="text-align: center;">สถานะ
                            <select id="statusFilter" onchange="filterTable()"
                                style="padding: 5px; border: 1px solid #ccc; border-radius: 10px; outline: none; background-color: white;">
                                <option value="">ทั้งหมด</option>
                                <option value="ชำระแล้ว">ชำระแล้ว</option>
                                <option value="ยกเลิก">ยกเลิก</option>
                            </select>
                        </th>
                        <th style="text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                            $rawDate = $row['created_at'] ?? $row['order_date'] ?? $row['date_time'] ?? null;
                            $formattedDate = $rawDate ? date('d/m/Y H:i', strtotime($rawDate)) . ' น.' : '-';
                            ?>
                            <tr>
                                <td style="color: #555; text-align: center;">
                                    <?= $formattedDate ?></td>

                                <td style="text-align: center;">#ORD-
                                    <?= str_pad((string) $row['order_id'], 4, '0', STR_PAD_LEFT) ?>
                                </td>

                                <td style="text-align: center;">
                                    <?= !empty($row['table_number']) ? $row['table_number'] : ($row['table_id'] ?? '-') ?>
                                </td>

                                <td style="text-align: center;">
                                    <?= number_format($row['total_amount'] ?? 0, 2) ?> ฿</td>

                                <td style="text-align: center;">
                                    <?php
                                    $statusMap = [
                                        'paid' => 'ชำระแล้ว',
                                        'pending'   => 'รอชำระเงิน',
                                        'cancelled' => 'ยกเลิก'
                                    ];
                                    $colorMap = [
                                        'paid' => 'green',
                                        'pending' => 'orange',
                                        'cancelled' => 'red'
                                    ];

                                    $currentStatus = $row['status'];
                                    $displayStatus = $statusMap[$currentStatus] ?? $currentStatus;
                                    $displayColor = $colorMap[$currentStatus] ?? 'black';
                                    ?>
                                    <span style="color: <?= $displayColor ?>; font-weight: bold;">
                                        <?= $displayStatus ?>
                                    </span>
                                </td>

                                <td style="text-align: center;">
                                    <?php if ($currentStatus === 'cancelled'): ?>
                                        <!-- สถานะยกเลิก -->
                                        <button class="btn-detail-h1" onclick="viewDetails(<?= $row['order_id'] ?>, 'cancelled')">
                                            <i class="fa-solid fa-eye"></i> ดูรายละเอียด
                                        </button>
                                    <?php else: ?>
                                        <!-- สถานะชำระแล้ว -->
                                        <button class="btn-detail-h2" onclick="viewDetails(<?= $row['order_id'] ?>, 'paid')">
                                            <i class="fa-solid fa-file-invoice"></i> ดูรายละเอียด
                                        </button>

                                        <button class="btn-print-h" onclick="printReceipt(<?= $row['order_id'] ?>)">
                                            <i class="fa-solid fa-print"></i> พิมพ์
                                        </button>

                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; color:#888;">ไม่พบประวัติการขาย</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>

    <script src="script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script>
        function toggleUserMenu(e) {
            e.stopPropagation();
            document.getElementById('userDropdown').classList.toggle('show');
        }

        // คลิกที่อื่นแล้วปิดเมนู
        document.addEventListener('click', function() {
            document.getElementById('userDropdown').classList.remove('show');
        });

        // กด ESC ปิดเมนู
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('userDropdown').classList.remove('show');
            }
        });

        function printReceipt(orderId) {
            // โยน order_id ไปยังไฟล์พิมพ์ใบเสร็จสำหรับลูกค้า
            window.open('print_receipt.php?id=' + orderId, '_blank', 'width=350,height=600');
        }
    </script>

</body>

</html>