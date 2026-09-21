<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

$requestedDate = $_GET['date'] ?? date('Y-m-d');
$type = $_GET['type'] ?? 'daily'; // daily, monthly, yearly

$selectedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $requestedDate);
if ($selectedDate === false) {
    $selectedDate = new DateTimeImmutable('today');
}

// Use the dedicated selector for non-daily reports. Invalid values safely
// retain the date-based default.
if ($type === 'monthly') {
    $requestedMonth = $_GET['month'] ?? '';
    if (preg_match('/^(0[1-9]|1[0-2])$/', $requestedMonth)) {
        $selectedDate = $selectedDate->setDate((int) $selectedDate->format('Y'), (int) $requestedMonth, 1);
    }
} elseif ($type === 'yearly') {
    $requestedYear = $_GET['year'] ?? '';
    if (preg_match('/^\d{4}$/', $requestedYear) && (int) $requestedYear >= 2000 && (int) $requestedYear <= 2100) {
        $selectedDate = $selectedDate->setDate((int) $requestedYear, 1, 1);
    }
}

// กำหนดขอบเขตวันที่ (อิงจากโค้ดเดิมของคุณ)
$dayStart = $selectedDate->format('Y-m-d 00:00:00');
$dayEnd = $selectedDate->modify('+1 day')->format('Y-m-d 00:00:00');
$monthStart = $selectedDate->modify('first day of this month')->format('Y-m-d 00:00:00');
$monthEnd = $selectedDate->modify('first day of next month')->format('Y-m-d 00:00:00');
$yearStart = $selectedDate->setDate((int) $selectedDate->format('Y'), 1, 1)->format('Y-m-d 00:00:00');
$yearEnd = $selectedDate->setDate((int) $selectedDate->format('Y') + 1, 1, 1)->format('Y-m-d 00:00:00');

function salesSummary(mysqli $conn, string $start, string $end): array {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) AS total_amount, COUNT(order_id) AS total_orders FROM `order` WHERE status = 'paid' AND created_at >= ? AND created_at < ?");
    $stmt->bind_param('ss', $start, $end);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$dailySummary = salesSummary($conn, $dayStart, $dayEnd);
$monthlySummary = salesSummary($conn, $monthStart, $monthEnd);
$yearlySummary = salesSummary($conn, $yearStart, $yearEnd);

// กำหนดเงื่อนไขดึงตารางตามประเภทที่กด
if ($type === 'monthly') {
    $start = $monthStart; $end = $monthEnd;
    $title = "รายละเอียดการขายประจำเดือน";
} elseif ($type === 'yearly') {
    $start = $yearStart; $end = $yearEnd;
    $title = "รายละเอียดการขายประจำปี";
} else {
    $start = $dayStart; $end = $dayEnd;
    $title = "รายละเอียดการขายประจำวัน";
}

$ordersStmt = $conn->prepare("SELECT order_id, table_id, created_at, total_amount, payment_method FROM `order` WHERE status = 'paid' AND created_at >= ? AND created_at < ? ORDER BY created_at DESC");
$ordersStmt->bind_param('ss', $start, $end);
$ordersStmt->execute();
$salesData = $ordersStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// สร้างตาราง HTML 
$table_html = "";
if ($salesData) {
    foreach ($salesData as $row) {
        $timestamp = is_numeric($row['created_at']) ? (int)$row['created_at'] : strtotime($row['created_at']);
        if ($type === 'daily') {
            $time_display = date('H:i น.', $timestamp);
        } else {
            $time_display = date('d/m/Y H:i น.', $timestamp);
        }

        $order_id = '#ORD-' . str_pad((string) $row['order_id'], 4, '0', STR_PAD_LEFT);
        $table_val = htmlspecialchars((string) $row['table_id'], ENT_QUOTES, 'UTF-8');
        $table = ($table_val == '0' || strtolower($table_val) == 'takeaway') ? 'กลับบ้าน' : 'โต๊ะ ' . $table_val;
        $payment = htmlspecialchars($row['payment_method'] ?: 'เงินสด', ENT_QUOTES, 'UTF-8');
        $total = number_format((float) $row['total_amount'], 2);

        $table_html .= "<tr>
                            <td>{$time_display}</td>
                            <td>{$order_id}</td>
                            <td>{$table}</td>
                            <td>{$payment}</td>
                            <td class='amount'><strong>{$total}</strong></td>
                        </tr>";
    }
} else {
    $table_html = '<tr><td colspan="5" class="no-data">ไม่มีข้อมูลการขายในช่วงเวลาที่เลือก</td></tr>';
}

// ส่งกลับเป็น JSON
echo json_encode([
    'daily_total' => number_format((float) $dailySummary['total_amount'], 2),
    'monthly_total' => number_format((float) $monthlySummary['total_amount'], 2),
    'yearly_total' => number_format((float) $yearlySummary['total_amount'], 2),
    'daily_orders' => number_format((int) $dailySummary['total_orders']),
    'table_title' => $title,
    'table_html' => $table_html
]);
?>
