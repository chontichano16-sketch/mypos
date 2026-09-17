<?php
require '../db.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$tableId = trim((string)($data['table_id'] ?? $data['tables_id'] ?? ''));
$items = $data['items'] ?? [];
$source = trim((string)($data['source'] ?? 'qr')); // รับค่า source (ถ้ามาจาก POS จะส่ง 'pos' มา)

if ($tableId === '' || empty($items)) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบ']);
    exit;
}

mysqli_begin_transaction($conn);
try {
    // 1. หาบิลเดิมที่เปิดค้างอยู่ (เพื่อเอาไว้ทำ Parent ID หรือรวมบิล)
    $st = mysqli_prepare($conn, "SELECT `order_id` FROM `order` WHERE `table_id` = ? AND `status` IN ('pending', 'cooking' , 'new_item' ) ORDER BY `order_id` DESC LIMIT 1");
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
    while ($r = mysqli_fetch_assoc($res)) {
        $priceMap[(int)$r['p_id']] = (float)$r['p_price'];
    }

    $total = 0;
    $clean = [];
    //  สร้าง Array เก็บราคา Option ไว้ฝั่งหลังบ้าน (อ้างอิงให้ตรงกับใน customer.js)
    $validOptions = [
        "ร้อน" => 0,
        "เย็น" => 10,
        "ปั่น" => 15,
        "สุกปานกลาง (Medium)" => 0,
        "สุกมาก (Well Done)" => 0
    ];

    $total = 0;
    $clean = [];
    foreach ($items as $it) {
        $pid = (int)($it['id'] ?? 0);
        $qty = max(1, (int)($it['qty'] ?? 1));

        if (!isset($priceMap[$pid])) throw new Exception("เมนู ID: $pid ไม่อยู่ในระบบ");

        //  ดึงราคาฐานข้อมูลเป็นหลัก (ปลอดภัยที่สุด)
        $basePrice = (float)$priceMap[$pid];

        //  ตรวจสอบ Option ที่ลูกค้าส่งมาว่ามีในระบบไหม และต้องบวกเพิ่มเท่าไหร่
        $optionLabel = trim($it['option_label'] ?? '');
        $adjustmentPrice = 0;

        if ($optionLabel !== '' && array_key_exists($optionLabel, $validOptions)) {
            $adjustmentPrice = (float)$validOptions[$optionLabel];
        }

        // ให้ PHP คำนวณราคาสุทธิเองเลย (ลูกค้าหลอกไม่ได้แน่นอน)
        $finalPrice = $basePrice + $adjustmentPrice;

        $total += $finalPrice * $qty;

        $clean[] = [
            'pid' => $pid,
            'qty' => $qty,
            'price' => $finalPrice,
            'remark' => mb_substr(trim($it['remark'] ?? ''), 0, 255),
            'option_label' => mb_substr($optionLabel, 0, 255)
        ];
    }

    // 3. จัดการบิล (แยกเคส POS รวมบิล กับ QR สั่งเพิ่ม)
    if ($parentOrderId > 0 && $source === 'pos') {
        // --- กรณีสั่งจาก POS และโต๊ะนี้มีบิลเปิดอยู่แล้ว ---
        // ให้ยึด order_id เดิม แล้วบวกยอดเงินเพิ่มเข้าไป (ไม่สร้างแถวใหม่ในตาราง order)
        $orderId = $parentOrderId;
        $st = mysqli_prepare($conn, "UPDATE `order` SET `total_amount` = `total_amount` + ? WHERE `order_id` = ?");
        mysqli_stmt_bind_param($st, 'di', $total, $orderId);
        mysqli_stmt_execute($st);
    } else {
        // --- กรณีเปิดโต๊ะใหม่ หรือเป็นออเดอร์จาก QR ---
        $status = ($source === 'pos') ? 'pending' : 'new_item';
        $st = mysqli_prepare($conn, "INSERT INTO `order` (`table_id`, `source`, `status`, `total_amount`, `parent_order_id`) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, 'sssdi', $tableId, $source, $status, $total, $parentOrderId);
        mysqli_stmt_execute($st);
        $orderId = mysqli_insert_id($conn);
    }

    // 4. บันทึกรายการอาหารลง order_detail
    $st = mysqli_prepare($conn, "INSERT INTO `order_detail` (`order_id`, `product_id`, `quantity`, `price`, `remark`, `option_label`) 
    VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($clean as $c) {
        $optLabel = isset($c['option_label']) ? $c['option_label'] : '';

        mysqli_stmt_bind_param($st, 'iiidss', $orderId, $c['pid'], $c['qty'], $c['price'], $c['remark'], $optLabel);
        mysqli_stmt_execute($st);
    }

    mysqli_commit($conn);
    echo json_encode(['status' => 'success', 'order_id' => $orderId, 'is_new' => ($parentOrderId == 0)]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
