<?php
require '../db.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$orderId = (int)($data['order_id'] ?? $data['id'] ?? $_POST['order_id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);

if ($orderId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่มีรหัสออเดอร์']);
    exit;
}

mysqli_begin_transaction($conn);
try {
    // 1. ดึงข้อมูลบิลที่กำลังกดรับ (เพื่อเอาเบอร์โต๊ะ และ ยอดเงินไปประมวลผลต่อ)
    $st = mysqli_prepare($conn, "SELECT `table_id`, `total_amount` FROM `order` WHERE `order_id` = ?");
    mysqli_stmt_bind_param($st, 'i',$orderId);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    $orderData = mysqli_fetch_assoc($res);

    if (!$orderData) {
        throw new Exception("ไม่พบข้อมูลออเดอร์นี้");
    }

    $tableId = (int)$orderData['table_id'];
    $amount = (float)$orderData['total_amount'];

    // 2. เช็คว่าโต๊ะนี้ มีบิลหลักที่ "เปิดอยู่ (cooking)" แล้วหรือไม่
    // (ค้นหาบิลของโต๊ะนี้ ที่สถานะเป็น cooking และไม่ใช่บิลที่กำลังกดรับอยู่)
    $stCheck = mysqli_prepare($conn, "SELECT `order_id` FROM `order` WHERE `table_id` = ? AND `status` = 'cooking' AND `order_id` != ? ORDER BY `order_id` ASC LIMIT 1");
    mysqli_stmt_bind_param($stCheck, 'ii', $tableId,$orderId);
    mysqli_stmt_execute($stCheck);
    $resCheck = mysqli_stmt_get_result($stCheck);
    $activeMainOrder = mysqli_fetch_assoc($resCheck);

    if ($activeMainOrder) {
        // ==========================================
        // กรณีมีบิลหลักเปิดอยู่แล้ว -> บังคับ "รวมบิล" เข้าไปเลย
        // ==========================================
        $mainOrderId = (int)$activeMainOrder['order_id'];

        // 2.1 ย้ายรายการอาหารไปใส่ในบิลหลัก
        $stUpdateDetail = mysqli_prepare($conn, "UPDATE `order_detail` SET `order_id` = ? WHERE `order_id` = ?");
        mysqli_stmt_bind_param($stUpdateDetail, 'ii', $mainOrderId,$orderId);
        mysqli_stmt_execute($stUpdateDetail);

        // 2.2 บวกยอดเงินเข้าไปในบิลหลัก
        $stUpdateParent = mysqli_prepare($conn, "UPDATE `order` SET `total_amount` = `total_amount` + ? WHERE `order_id` = ?");
        mysqli_stmt_bind_param($stUpdateParent, 'di', $amount,$mainOrderId);
        mysqli_stmt_execute($stUpdateParent);

        // 2.3 ลบบิลที่กำลังกดรับทิ้ง (เพราะย้ายของไปหมดแล้ว)
        $stDelete = mysqli_prepare($conn, "DELETE FROM `order` WHERE `order_id` = ?");
        mysqli_stmt_bind_param($stDelete, 'i',$orderId);
        mysqli_stmt_execute($stDelete);

        $finalOrderId =$mainOrderId;

    } else {
        // ==========================================
        // กรณีไม่มีบิลหลักเลย -> บิลนี้จะเป็นบิลตั้งต้น
        // ==========================================
        $stUpdate = mysqli_prepare($conn, "UPDATE `order` SET `status` = 'cooking' WHERE `order_id` = ?");
        mysqli_stmt_bind_param($stUpdate, 'i',$orderId);
        mysqli_stmt_execute($stUpdate);

        $finalOrderId =$orderId;
    }

    mysqli_commit($conn);
    echo json_encode(['status' => 'success', 'message' => 'รับออเดอร์และจัดการบิลเรียบร้อย', 'order_id' => $finalOrderId]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>