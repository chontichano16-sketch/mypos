<?php
require_once 'db.php';

$requestedDate = $_GET['report_date'] ?? date('Y-m-d');
$selectedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $requestedDate);
$dateErrors = DateTimeImmutable::getLastErrors();
if ($selectedDate === false || ($dateErrors !== false && ($dateErrors['warning_count'] || $dateErrors['error_count']))) {
    $selectedDate = new DateTimeImmutable('today');
}

$filterDate = $selectedDate->format('Y-m-d');
$dayStart = $selectedDate->format('Y-m-d 00:00:00');
$dayEnd = $selectedDate->modify('+1 day')->format('Y-m-d 00:00:00');
$monthStart = $selectedDate->modify('first day of this month')->format('Y-m-d 00:00:00');
$monthEnd = $selectedDate->modify('first day of next month')->format('Y-m-d 00:00:00');
$yearStart = $selectedDate->setDate((int) $selectedDate->format('Y'), 1, 1)->format('Y-m-d 00:00:00');
$yearEnd = $selectedDate->setDate((int) $selectedDate->format('Y') + 1, 1, 1)->format('Y-m-d 00:00:00');

function salesSummary(mysqli $conn, string $start, string $end): array
{
    $statement = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) AS total_amount, COUNT(order_id) AS total_orders FROM `order` WHERE status = 'paid' AND created_at >= ? AND created_at < ?");
    $statement->bind_param('ss', $start, $end);
    $statement->execute();
    return $statement->get_result()->fetch_assoc();
}

$ordersStatement = $conn->prepare("SELECT order_id, table_id, created_at, total_amount, payment_method FROM `order` WHERE status = 'paid' AND created_at >= ? AND created_at < ? ORDER BY created_at DESC");
$ordersStatement->bind_param('ss', $dayStart, $dayEnd);
$ordersStatement->execute();
$salesData = $ordersStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$dailySummary = salesSummary($conn, $dayStart, $dayEnd);
$monthlySummary = salesSummary($conn, $monthStart, $monthEnd);
$yearlySummary = salesSummary($conn, $yearStart, $yearEnd);
?>

<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานยอดขาย</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" crossorigin="anonymous" 
    referrerpolicy="no-referrer">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">
    
    <style>
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

<body class="report-page">
    <nav class="navbar">
        <div class="dropdown">
            <button type="button" onclick="toggleMenu(event)" class="dropbtn" aria-label="เปิดเมนู">&#9776;</button>
            <div id="myDropdown" class="dropdown-content">
                <button type="button" class="menu-btn"><i class="bi bi-chevron-down" style="float: right;"></i>จัดการข้อมูลโต๊ะ</button>
                <ul class="submenu">
                    <li><a href="print_qr.php"><i class="bi bi-qr-code"></i> พิมพ์ QR Code โต๊ะ</a></li>
                </ul>
                <button type="button" class="menu-btn"><i class="bi bi-chevron-down" style="float: right;"></i>จัดการข้อมูลเมนูอาหาร</button>
                <ul class="submenu">
                    <li><button type="button" onclick="openModal('product')">เพิ่มสินค้า</button></li>
                    <li><button type="button" onclick="openModal('type')">เพิ่มประเภทสินค้า</button></li>
                </ul>
                <a href="show_pro.php">รายการสินค้าทั้งหมด</a>
                <a href="sale_report.php">รายงานยอดขาย</a>
            </div>
        </div>

        <div class="nav-report">

            <div class="nav-report-header">
                <a href="index.php">หน้าร้าน</a>
                <span>/</span>
                <span>รายงานยอดขาย</span>
            </div>

            <div class="btn-navreport">
                <button type="button" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> พิมพ์รายงาน
                </button>
                <button type="button" id="export-excel">
                    <i class="fa-solid fa-file-excel"></i> ส่งออก Excel
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

    <main class="report-container">
        <section class="report-header">
            <div>
                <h1>สรุปรายงานยอดขาย</h1>
                <p>ภาพรวมรายได้และจำนวนออเดอร์ของร้าน ณ วันที่
                    <span id="header-date-text">
                        <?= htmlspecialchars($selectedDate->format('d/m/Y'), ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>



            <form method="get" class="report-filter">

                <div class="report-buttons">
                    <button type="button" class="active" onclick="loadReport('daily',this)">รายวัน</button>
                    <button type="button" onclick="loadReport('monthly',this)">รายเดือน</button>
                    <button type="button" onclick="loadReport('yearly',this)">รายปี</button>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <label for="date-report">เลือกวันที่</label>
                    <input type="date" name="report_date" id="date-report" value="<?= htmlspecialchars($filterDate, ENT_QUOTES, 'UTF-8') ?>" onchange="loadReport(currentType)">
                </div>

            </form>
        </section>

        <section class="report-cards" aria-label="สรุปยอดขาย">
            <article class="report-card">
                <span>ยอดขายรายวัน <i class="fa-solid fa-calendar-day"></i></span>
                <strong id="val-daily">฿<?= number_format((float) $dailySummary['total_amount'], 2) ?></strong>
            </article>

            <article class="report-card">
                <span>ยอดขายรายเดือน <i class="fa-solid fa-calendar-days"></i></span>
                <strong id="val-monthly">฿<?= number_format((float) $monthlySummary['total_amount'], 2) ?></strong>
            </article>

            <article class="report-card">
                <span>ยอดขายรายปี <i class="fa-solid fa-chart-line"></i></span>
                <strong id="val-yearly">฿<?= number_format((float) $yearlySummary['total_amount'], 2) ?></strong>
            </article>

            <article class="report-card">
                <span>ออเดอร์รายวัน <i class="fa-solid fa-receipt"></i></span>
                <strong id="val-count"><?= number_format((int) $dailySummary['total_orders']) ?> บิล</strong>
            </article>

        </section>

        <section class="detail-report">
            <h2 id="table-title">รายละเอียดการขายประจำวัน</h2>
            <div class="report-table-wrap">
                <table id="report-table">
                    <thead>
                        <tr>
                            <th>วัน/เวลา</th>
                            <th>เลขที่ออเดอร์</th>
                            <th>โต๊ะ</th>
                            <th>ชำระด้วย</th>
                            <th class="amount">ยอดรวม (บาท)</th>
                        </tr>
                    </thead>
                    <tbody id="report-tbody">
                        <?php if ($salesData): foreach ($salesData as $row): ?>
                                <tr>
                                    <td><?= is_numeric($row['created_at'])
                                            ? date('H:i น.', (int)$row['created_at'])
                                            : date('H:i น.', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td>#ORD-<?= str_pad((string) $row['order_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td> <?= htmlspecialchars((string) $row['table_id'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['payment_method'] ?: 'เงินสด', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="amount"><strong><?= number_format((float) $row['total_amount'], 2) ?></strong></td>
                                </tr>
                            <?php endforeach;
                        else: ?><tr>
                                <td colspan="5" class="no-data">ไม่มีข้อมูลการขายในวันที่เลือก</td>
                            </tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

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
    </script>

</body>

</html>