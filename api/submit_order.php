<?php
require '../db.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$tableId = trim((string)($data['table_id'] ?? $data['tables_id'] ?? ''));
$items = $data['items'] ?? [];

if ($tableId === '' || empty($items)) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบ']);
    exit;
}

mysqli_begin_transaction($conn);
try {
    // 1. หาบิลเดิมที่เปิดค้างอยู่ (เพื่อเอาไว้ทำ Parent ID)
    $st = mysqli_prepare($conn, "SELECT `order_id` FROM `order` WHERE `table_id` = ? AND `status` IN ('pending', 'cooking') ORDER BY `order_id` DESC LIMIT 1");
    mysqli_stmt_bind_param($st, 's', $tableId);
    mysqli_stmt_execute($st);
    $open = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    $parentOrderId = ($open && isset($open['order_id'])) ? (int)$open['order_id'] : 0;

    // 2. คำนวณราคารวม
    $ids = array_values(array_unique(array_map(fn($i) => (int)($i['id'] ?? 0), $items)));
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $st = mysqli_prepare($conn, "SELECT p_id, p_price FROM products WHERE p_id IN ($ph)");
    mysqli_stmt_bind_param($st, str_repeat('i', count($ids)), ...$ids);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    $priceMap = [];
    while ($r = mysqli_fetch_assoc($res)) $priceMap[(int)$r['p_id']] = (float)$r['p_price'];

    $total = 0;
    $clean = [];
    foreach ($items as $it) {
        $pid = (int)($it['id'] ?? 0);
        $qty = max(1, (int)($it['qty'] ?? 1));
        if (!isset($priceMap[$pid])) throw new Exception("เมนู ID: $pid ไม่อยู่ในระบบ");
        $total += $priceMap[$pid] * $qty;
        $clean[] = ['pid' => $pid, 'qty' => $qty, 'price' => $priceMap[$pid], 'remark' => mb_substr(trim($it['remark'] ?? ''), 0, 255)];
    }

    // 3. บันทึกออเดอร์ใหม่เป็นสถานะ new_item
    $st = mysqli_prepare($conn, "INSERT INTO `order` (`table_id`, `source`, `status`, `total_amount`, `parent_order_id`) VALUES (?, 'qr', 'new_item', ?, ?)");
    mysqli_stmt_bind_param($st, 'sdi', $tableId, $total, $parentOrderId);
    mysqli_stmt_execute($st);
    $orderId = mysqli_insert_id($conn);

    // 4. บันทึกรายการอาหาร
    $st = mysqli_prepare($conn, "INSERT INTO `order_detail` (`order_id`, `product_id`, `quantity`, `price`, `remark`) VALUES (?, ?, ?, ?, ?)");
    foreach ($clean as $c) {
        mysqli_stmt_bind_param($st, 'iiids', $orderId, $c['pid'], $c['qty'], $c['price'], $c['remark']);
        mysqli_stmt_execute($st);
    }

    mysqli_commit($conn);
    echo json_encode(['status' => 'success', 'order_id' => $orderId, 'is_new' => true]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
