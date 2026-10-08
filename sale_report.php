<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
include "all_popup.php";

//รับค่าพารามิเตอร์การกรองข้อมูล
$requestedDate = $_GET['report_date'] ?? date('Y-m-d');
$reportType = $_GET['type'] ?? 'daily'; // 'daily', 'monthly', 'quarterly', 'yearly'

$selectedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $requestedDate);
$dateErrors = DateTimeImmutable::getLastErrors();
if ($selectedDate === false || ($dateErrors !== false && ($dateErrors['warning_count'] || $dateErrors['error_count']))) {
    $selectedDate = new DateTimeImmutable('today');
}

$filterDate = $selectedDate->format('Y-m-d');
$currentYear = (int)$selectedDate->format('Y');

// คำนวณช่วงเวลาสำหรับการสรุปยอดและการดึงตารางข้อมูล
$dayStart = $selectedDate->format('Y-m-d 00:00:00');
$dayEnd = $selectedDate->modify('+1 day')->format('Y-m-d 00:00:00');

$monthStart = $selectedDate->modify('first day of this month')->format('Y-m-d 00:00:00');
$monthEnd = $selectedDate->modify('first day of next month')->format('Y-m-d 00:00:00');

// คำนวณขอบเขตไตรมาส
$currentMonth = (int)$selectedDate->format('m');
$quarter = (int)ceil($currentMonth / 3);
$startMonthOfQuarter = ($quarter - 1) * 3 + 1;
$quarterStart = sprintf('%04d-%02d-01 00:00:00', $currentYear, $startMonthOfQuarter);
$quarterEnd = (new DateTimeImmutable($quarterStart))->modify('+3 months')->format('Y-m-d 00:00:00');

// คำนวณขอบเขตรายปี
$yearStart = sprintf('%04d-01-01 00:00:00', $currentYear);
$yearEnd = sprintf('%04d-01-01 00:00:00', $currentYear + 1);

$thaiMonths = [
    1 => 'มกราคม',
    2 => 'กุมภาพันธ์',
    3 => 'มีนาคม',
    4 => 'เมษายน',
    5 => 'พฤษภาคม',
    6 => 'มิถุนายน',
    7 => 'กรกฎาคม',
    8 => 'สิงหาคม',
    9 => 'กันยายน',
    10 => 'ตุลาคม',
    11 => 'พฤศจิกายน',
    12 => 'ธันวาคม'
];

$selectedM   = (int)$selectedDate->format('m');
$selectedYBE = (int)$selectedDate->format('Y') + 543; // แปลง ค.ศ. เป็น พ.ศ.

// กำหนดช่วงเวลาสำหรับดึงข้อมูลตาราง และข้อความหัวข้อตามที่เลือก
if ($reportType === 'monthly') {
    $tableStart     = $monthStart;
    $tableEnd       = $monthEnd;
    $tableTitle     = 'รายละเอียดการขายประจำเดือน';
    $headerSubTitle = "ภาพรวมรายได้และจำนวนออเดอร์ของร้าน ประจำเดือน " . $thaiMonths[$selectedM] . " " . $selectedYBE;
} elseif ($reportType === 'quarterly') {
    $tableStart     = $quarterStart;
    $tableEnd       = $quarterEnd;
    $tableTitle     = 'รายละเอียดการขายประจำไตรมาส ' . $quarter;
    $headerSubTitle = "ภาพรวมรายได้และจำนวนออเดอร์ของร้าน ประจำไตรมาสที่ " . $quarter . " ปี " . $selectedYBE;
} elseif ($reportType === 'yearly') {
    $tableStart     = $yearStart;
    $tableEnd       = $yearEnd;
    $tableTitle     = 'รายละเอียดการขายประจำปี ' . $selectedYBE;
    $headerSubTitle = "ภาพรวมรายได้และจำนวนออเดอร์ของร้าน ประจำปี " . $selectedYBE;
} else {
    $reportType     = 'daily';
    $tableStart     = $dayStart;
    $tableEnd       = $dayEnd;
    $tableTitle     = 'รายละเอียดการขายประจำวัน';
    $headerSubTitle = "ภาพรวมรายได้และจำนวนออเดอร์ของร้าน ณ วันที่ " . $selectedDate->format('d/m/') . $selectedYBE;
}

$selectedSummary = salesSummary($conn, $tableStart, $tableEnd);
$cardTotalSales  = $selectedSummary['total_amount'] ?? 0;  // ยอดขายรวม
$cardTotalOrders = $selectedSummary['total_orders'] ?? 0; // จำนวนบิลรวม
$cardAvgPerOrder = ($cardTotalOrders > 0) ? ($cardTotalSales / $cardTotalOrders) : 0; // คำนวณยอดขายเฉลี่ยต่อบิล
$targetYear = $selectedDate->format('Y');
$targetYearStart = $targetYear . "-01-01 00:00:00";
$targetYearEnd   = ($targetYear + 1) . "-01-01 00:00:00";
$selectedYearlySummary = salesSummary($conn, $targetYearStart, $targetYearEnd);
$cardTotalYearly = $selectedYearlySummary['total_amount'] ?? 0;
//กำหนดชื่อหัวข้อบนการ์ดสรุปตามโหมดที่เลือก
if ($reportType === 'monthly') {
    $card1Title = "ยอดขายประจำเดือน";
    $card2Title = "จำนวนบิลประจำเดือน";
    $card3Title = "ยอดขายเฉลี่ยต่อบิล";
    $card4Title = "ยอดขายปี " . $selectedYBE;
} elseif ($reportType === 'quarterly') {
    $card1Title = "ยอดขายไตรมาส " . $quarter;
    $card2Title = "จำนวนบิลไตรมาส " . $quarter;
    $card3Title = "ยอดขายเฉลี่ยต่อบิล";
    $card4Title = "ยอดขายปี " . $selectedYBE;
} elseif ($reportType === 'yearly') {
    $card1Title = "ยอดขายประจำปี " . $selectedYBE;
    $card2Title = "จำนวนบิลประจำปี " . $selectedYBE;
    $card3Title = "ยอดขายเฉลี่ยต่อบิล";
    $card4Title = "ยอดขายรวมปี " . $selectedYBE;
} else { // daily
    $card1Title = "ยอดขายประจำวัน";
    $card2Title = "จำนวนบิลประจำวัน";
    $card3Title = "ยอดขายเดือนนี้";
    $card4Title = "ยอดขายปีนี้";
}


$reportYears = [$currentYear];
$yearsResult = $conn->query("SELECT DISTINCT YEAR(created_at) AS report_year FROM `order` WHERE status = 'paid' AND created_at IS NOT NULL ORDER BY report_year DESC");
if ($yearsResult) {
    while ($yearRow = $yearsResult->fetch_assoc()) {
        if ($yearRow['report_year'] !== null) {
            $reportYears[] = (int)$yearRow['report_year'];
        }
    }
}
$reportYears = array_values(array_unique($reportYears));
rsort($reportYears);


function salesSummary(mysqli $conn, string $start, string $end): array
{
    $statement = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) AS total_amount, COUNT(order_id) AS total_orders FROM `order` WHERE status = 'paid' AND created_at >= ? AND created_at < ?");
    $statement->bind_param('ss', $start, $end);
    $statement->execute();
    return $statement->get_result()->fetch_assoc();
}

// --- คำนวณช่วงเวลาสำหรับ Card สรุปยอดด้านบน (ใช้วันปัจจุบันจริงๆ เสมอ) ---
$realToday = new DateTimeImmutable('today');
$todayStart     = $realToday->format('Y-m-d 00:00:00');
$todayEnd       = $realToday->modify('+1 day')->format('Y-m-d 00:00:00');
$thisMonthStart = $realToday->modify('first day of this month')->format('Y-m-d 00:00:00');
$thisMonthEnd   = $realToday->modify('first day of next month')->format('Y-m-d 00:00:00');
$thisYearStart  = $realToday->format('Y-01-01 00:00:00');
$thisYearEnd    = sprintf('%04d-01-01 00:00:00', (int)$realToday->format('Y') + 1);

// ดึงข้อมูลใส่ Card สรุปยอด
$dailySummary   = salesSummary($conn, $todayStart, $todayEnd);
$monthlySummary = salesSummary($conn, $thisMonthStart, $thisMonthEnd);
$yearlySummary  = salesSummary($conn, $thisYearStart, $thisYearEnd);

// ดึงข้อมูลรายการขายสำหรับตาราง
$ordersStatement = $conn->prepare("SELECT `order`.*, tables.tables_number FROM `order` LEFT JOIN tables ON `order`.table_id = tables.tables_id WHERE `order`.status = 'paid' AND `order`.created_at >= ? AND `order`.created_at < ? ORDER BY `order`.created_at DESC");
$ordersStatement->bind_param('ss', $tableStart, $tableEnd);
$ordersStatement->execute();
$salesData = $ordersStatement->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานยอดขาย</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

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

        @media print {

            @media print {

                /* ซ่อนปุ่มเมนู ที่ไม่เกี่ยวข้อง */
                .btn-navreport,
                header,
                nav,
                .sidebar,
                .user-profile,
                form,
                select,
                input,
                .filter-container,
                .report-type-selector,
                .modal,
                .modal-dialog,
                .offcanvas,
                .overlay,
                .popup-container,
                [id*="modal"] {
                    display: none !important;
                }

                body,
                main,
                .container,
                .report-table-wrap {
                    display: block !important;
                    background: #fff !important;
                    color: #000 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                    height: auto !important;
                    max-height: none !important;
                    overflow: visible !important;
                    box-shadow: none !important;
                }

                table {
                    width: 100% !important;
                    border-collapse: collapse !important;
                    page-break-inside: auto !important;
                }

                tr {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                thead {
                    display: table-header-group !important;
                }

                tfoot {
                    display: table-footer-group !important;
                }
            }

            .btn-navreport,
            header,
            nav,

            .report-cards,
            .sidebar,
            .user-profile,

            form,
            select,
            input,
            .filter-container,
            .report-type-selector {
                display: none !important;
            }

            body,
            main,
            .container {
                background: #fff !important;
                color: #000 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                box-shadow: none !important;
            }

            .detail-report {
                box-shadow: none !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
        }
    </style>
</head>

<body class="report-page">
    <nav class="navbar">
        <div class="dropdown">
            <button onclick="toggleMenu(event)" class="dropbtn"><i class="fa-solid fa-bars"></i></button>

            <div id="myDropdown" class="dropdown-content" style="border: none;">
                <button class="menu-btn"> <i class="bi bi-chevron-down" style="float: right;"></i></i><i class="fa-solid fa-chair"></i> จัดการข้อมูลโต๊ะ</button>
                <ul class="submenu">
                    <li><a href="show_tables.php"> รายการโต๊ะทั้งหมด</a></li>
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
                <span>รายงานยอดขาย</span>
            </div>

            <div class="btn-navreport">
                <button type="button" id="history-report">
                    <a href="sales_history.php"><i class="fa-solid fa-clock-rotate-left"></i> ประวัติการขาย</a>
                </button>
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
                <?php echo htmlspecialchars($_SESSION["fullname"] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                <i class="fa-solid fa-circle-user"></i>
            </button>

            <div class="user-dropdown" id="userDropdown">
                <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <main class="report-container">
        <section class="report-header">
            <div>
                <h1>สรุปรายงานยอดขาย</h1>
                <span style="color: #3e3e3e;" id="header-date-text">
                    <?= $headerSubTitle ?>
                </span>
            </div>

            <form method="get" class="report-filter" onsubmit="return false;">
                <div class="report-buttons" id="tab-buttons">
                    <button type="button" onclick="switchTab('daily', this)">รายวัน</button>
                    <button type="button" onclick="switchTab('monthly', this)">รายเดือน</button>
                    <button type="button" onclick="switchTab('quarterly', this)">รายไตรมาส</button>
                    <button type="button" onclick="switchTab('yearly', this)">รายปี</button>
                </div>

                <div id="date-input-wrap" style="display: flex; align-items: center; gap: 10px;">
                    <label for="date-report">เลือกวันที่</label>
                    <input type="date" name="report_date" id="date-report"
                        value="<?= $filterDate ?>"
                        lang="th-TH-u-ca-buddhist"
                        onchange="loadReport('daily', true)">
                </div>
            </form>
        </section>

        <!-- การ์ด -->
        <section class="report-cards" aria-label="สรุปยอดขาย">
            <article class="report-card">
                <span><?= $card1Title ?> <i class="fa-solid fa-calendar-day"></i></span>
                <strong id="val-daily">฿<?= number_format((float) ($cardTotalSales ?? 0), 2) ?></strong>
            </article>

            <article class="report-card">
                <span><?= $card2Title ?> <i class="fa-solid fa-receipt"></i></span>
                <strong id="val-count"><?= number_format((int) ($cardTotalOrders ?? 0)) ?> บิล</strong>
            </article>

            <article class="report-card">
                <span><?= $card3Title ?> <i class="fa-solid fa-calendar-days"></i></span>
                <strong id="val-monthly">
                    <?php if ($reportType === 'daily'): ?>
                        ฿<?= number_format((float) ($monthlySummary['total_amount'] ?? 0), 2) ?>
                    <?php else: ?>
                        ฿<?= number_format((float) ($cardAvgPerOrder ?? 0), 2) ?>
                    <?php endif; ?>
                </strong>
            </article>

            <article class="report-card">
                <span><?= $card4Title ?> <i class="fa-solid fa-chart-line"></i></span>
                <strong id="val-yearly">฿<?= number_format((float) ($cardTotalYearly ?? 0), 2) ?></strong>
            </article>
        </section>
        <!-- จบ -->

        <section class="detail-report">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 id="table-title" style="margin: 0;"><?= htmlspecialchars($tableTitle, ENT_QUOTES, 'UTF-8') ?></h2>

                <div id="date-filter-wrap" style="display: none; gap: 10px; align-items: center;">
                    <!-- Dropdown เลือกเดือน -->
                    <select id="filterMonth" aria-label="เลือกเดือน" onchange="loadReport('monthly')" style="display: none; padding: 6px 12px; border-radius: 10px; border: 1px solid #ccc; outline: none; cursor: pointer;">
                        <option value="01">มกราคม</option>
                        <option value="02">กุมภาพันธ์</option>
                        <option value="03">มีนาคม</option>
                        <option value="04">เมษายน</option>
                        <option value="05">พฤษภาคม</option>
                        <option value="06">มิถุนายน</option>
                        <option value="07">กรกฎาคม</option>
                        <option value="08">สิงหาคม</option>
                        <option value="09">กันยายน</option>
                        <option value="10">ตุลาคม</option>
                        <option value="11">พฤศจิกายน</option>
                        <option value="12">ธันวาคม</option>
                    </select>

                    <!-- Dropdown เลือกไตรมาส -->
                    <select id="filterQuarter" aria-label="เลือกไตรมาส" onchange="loadReport('quarterly')" style="display: none; padding: 6px 12px; border-radius: 10px; border: 1px solid #ccc; outline: none; cursor: pointer;">
                        <option value="1">ไตรมาส 1 (ม.ค. - มี.ค.)</option>
                        <option value="2">ไตรมาส 2 (เม.ย. - มิ.ย.)</option>
                        <option value="3">ไตรมาส 3 (ก.ค. - ก.ย.)</option>
                        <option value="4">ไตรมาส 4 (ต.ค. - ธ.ค.)</option>
                    </select>

                    <!-- Dropdown เลือกปี -->
                    <select id="filterYear" aria-label="เลือกปี" onchange="loadReport(currentReportType)" style="display: none; padding: 6px 12px; border-radius: 10px; border: 1px solid #ccc; outline: none; cursor: pointer;">
                    </select>
                </div>
            </div>

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
                        <?php
                        $sumTableTotal = 0;
                        if ($salesData):
                            foreach ($salesData as $row):
                                $sumTableTotal += (float)$row['total_amount'];
                        ?>
                                <tr>
                                    <td>
                                        <?php
                                        $timestamp = is_numeric($row['created_at']) ? (int)$row['created_at'] : strtotime($row['created_at']);
                                        // ดูแบบรายวันให้แสดงเฉพาะเวลา ดูรายอื่นให้แสดง วัน/เดือน/ปี เวลา
                                        if ($reportType === 'daily') {
                                            echo date('H:i น.', $timestamp);
                                        } else {
                                            $thaiYear = (int)date('Y', $timestamp) + 543; // แปลงเป็น พ.ศ.
                                            echo date('d/m/', $timestamp) . $thaiYear . date(' H:i น.', $timestamp);
                                        }
                                        ?>
                                    </td>
                                    <td>#ORD-<?= str_pad((string) $row['order_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= htmlspecialchars((string) (!empty($row['tables_number']) ? $row['tables_number'] : ($row['table_id'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['payment_method'] ?: 'เงินสด', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="amount"><strong><?= number_format((float) $row['total_amount'], 2) ?></strong></td>

                                </tr>
                            <?php endforeach; ?>
                            <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #cbd5e1;">
                                <!-- colspan="4" รวบคอลัมน์เข้าด้วยกัน -->
                                <td colspan="4" style="text-align: right; padding-right: 20px;">ยอดรวมทั้งหมด</td>
                                <td class="amount" style="color: #1e3a8a;">
                                    <strong><?= number_format($sumTableTotal, 2) ?></strong>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="no-data" style="text-align: center; padding: 25px; color: #888;">ไม่มีข้อมูลการขายในช่วงเวลาที่เลือก</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script>
        // ดึงค่า เดือน/ปี จาก PHP เพื่อมากำหนดให้ Dropdown ล็อกค่าที่ถูกต้องเสมอ
        const selectedM = "<?= $selectedDate->format('m') ?>";
        const selectedY = "<?= $selectedDate->format('Y') ?>";
        const selectedQ = "<?= (int)ceil((int)$selectedDate->format('m') / 3) ?>";

        const monthSelector = document.getElementById('filterMonth');
        const quarterSelector = document.getElementById('filterQuarter');
        const yearSelector = document.getElementById('filterYear');

        // ให้แสดงหน้าเว็บเป็น พ.ศ. แต่ส่งค่าค้นหาเป็น ค.ศ.
        yearSelector.innerHTML = '';
        <?= json_encode($reportYears, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>.forEach(function(year) {
            const thaiYear = parseInt(year) + 543; // แปลง ค.ศ. เป็น พ.ศ. 

            const option = new Option(thaiYear, year, false, String(year) === selectedY);
            yearSelector.add(option);
        });

        // กำหนดค่าเดือน และ ไตรมาส ให้ตรงกับวันที่เลือกอยู่จริง (ป้องกันไม่ให้เด้งไปเดือน 01)
        if (monthSelector) monthSelector.value = selectedM;
        if (quarterSelector) quarterSelector.value = selectedQ;
        if (yearSelector) yearSelector.value = selectedY;

        // ดึงสถานะแท็บปัจจุบันจาก URL Parameter
        let currentReportType = new URLSearchParams(window.location.search).get('type') || 'daily';

        document.addEventListener('DOMContentLoaded', () => {
            const activeBtn = document.querySelector(`#tab-buttons button[onclick*="'${currentReportType}'"]`) || document.querySelectorAll('#tab-buttons button')[0];
            if (activeBtn) {
                switchTab(currentReportType, activeBtn, false);
            }
        });

        // ฟังก์ชันสลับแท็บ
        function switchTab(reportType, btnElement, fetchReport = true) {
            currentReportType = reportType;

            if (btnElement) {
                document.querySelectorAll('#tab-buttons button').forEach(btn => btn.classList.remove('active'));
                btnElement.classList.add('active');
            }

            const dateInputWrap = document.getElementById('date-input-wrap');
            const dateFilterWrap = document.getElementById('date-filter-wrap');

            filterMonth.style.display = 'none';
            filterQuarter.style.display = 'none';
            filterYear.style.display = 'none';

            if (reportType === 'daily') {
                dateInputWrap.style.display = 'flex';
                dateFilterWrap.style.display = 'none';
            } else {
                dateInputWrap.style.display = 'none';
                dateFilterWrap.style.display = 'flex';

                if (reportType === 'monthly') {
                    filterMonth.style.display = 'inline-block';
                    filterYear.style.display = 'inline-block';
                } else if (reportType === 'quarterly') {
                    filterQuarter.style.display = 'inline-block';
                    filterYear.style.display = 'inline-block';
                } else if (reportType === 'yearly') {
                    filterYear.style.display = 'inline-block';
                }
            }

            if (fetchReport) {
                loadReport(reportType);
            }
        }

        // เพิ่มพารามิเตอร์ isDatePicker (ค่าเริ่มต้นคือ false)
        function loadReport(type, isDatePicker = false) {
            let url = new URL(window.location.href);
            url.searchParams.set('type', type);

            const yearSelect = document.getElementById('filterYear');
            const selectedYear = (yearSelect && yearSelect.value) ? yearSelect.value : new Date().getFullYear();

            if (type === 'daily') {
                let targetDate;

                if (isDatePicker) {
                    // คลิกเลือกวันที่ในช่อง Datepicker เอง
                    targetDate = document.getElementById('date-report').value;
                } else {
                    // กดปุ่มแท็บ "รายวัน" ให้รีเซ็ตกลับเป็น "วันนี้จริง" เสมอ
                    const now = new Date();
                    const y = now.getFullYear();
                    const m = String(now.getMonth() + 1).padStart(2, '0');
                    const d = String(now.getDate()).padStart(2, '0');
                    targetDate = `${y}-${m}-${d}`;
                }

                url.searchParams.set('report_date', targetDate);
            } else if (type === 'monthly') {
                const monthVal = document.getElementById('filterMonth').value || '01';
                url.searchParams.set('report_date', `${selectedYear}-${monthVal}-01`);
            } else if (type === 'quarterly') {
                const quarterVal = document.getElementById('filterQuarter').value || '1';
                const monthOfQuarter = (quarterVal == 1) ? '01' : (quarterVal == 2) ? '04' : (quarterVal == 3) ? '07' : '10';
                url.searchParams.set('report_date', `${selectedYear}-${monthOfQuarter}-01`);
            } else if (type === 'yearly') {
                url.searchParams.set('report_date', `${selectedYear}-01-01`);
            }

            window.location.href = url.toString();
        }

        // จัดการ User Menu
        function toggleUserMenu(e) {
            e.stopPropagation();
            document.getElementById('userDropdown').classList.toggle('show');
        }

        document.addEventListener('click', function() {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        });
    </script>

</body>

</html>