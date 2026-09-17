<?php
require '../db.php';
header('Content-Type: application/json; charset=utf-8');

// ... (รับค่า order_id ที่พนักงานกดรับออเดอร์มา สมมติเก็บในตัวแปร $orderId)
$data = json_decode(file_get_contents('php://input'), true);
$orderId = (int)($data['order_id'] ?? $data['id'] ?? $_POST['order_id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);

if ($orderId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่มีรหัสออเดอร์']);
    exit;
}

mysqli_begin_transaction($conn);
try {
    // 1. ดึงข้อมูลบิลที่กำลังจะกดรับ เพื่อดูว่าเป็นบิลสั่งเพิ่มหรือไม่
    $st = mysqli_prepare($conn, "SELECT `parent_order_id`, `total_amount` FROM `order` WHERE `order_id` = ?");
    mysqli_stmt_bind_param($st, 'i', $orderId);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    $orderData = mysqli_fetch_assoc($res);

    if ($orderData) {
        $parentId = (int)$orderData['parent_order_id'];
        $amount = (float)$orderData['total_amount'];

        if ($parentId > 0) {
            // 1. ย้ายรายการอาหารจากบิลย่อย ไปใส่บิลหลัก
            $stUpdateDetail = mysqli_prepare($conn, "UPDATE `order_detail` SET `order_id` = ? WHERE `order_id` = ?");
            mysqli_stmt_bind_param($stUpdateDetail, 'ii', $parentId, $orderId);
            mysqli_stmt_execute($stUpdateDetail);

            // 2. บวกยอดเงินเข้าไปในบิลหลัก และให้บิลหลักมีสถานะเป็น cooking (หรือ pending ตามต้องการ)
            $stUpdateParent = mysqli_prepare($conn, "UPDATE `order` SET `total_amount` = `total_amount` + ?, `status` = 'cooking' WHERE `order_id` = ?");
            mysqli_stmt_bind_param($stUpdateParent, 'di', $amount, $parentId);
            mysqli_stmt_execute($stUpdateParent);

            // 3. ลบบิลย่อยทิ้งไปเลย (เพื่อให้หายไปจากหน้า "บิลทั้งหมด")
            $stDelete = mysqli_prepare($conn, "DELETE FROM `order` WHERE `order_id` = ?");
            mysqli_stmt_bind_param($stDelete, 'i', $orderId);
            mysqli_stmt_execute($stDelete);

            $finalOrderId = $parentId; // ส่ง ID บิลหลักกลับไปให้หน้าจอ

        } else {
            // ==========================================
            // กรณีเป็น "บิลหลัก" ปกติ (ออเดอร์แรกสุดของโต๊ะ)
            // ==========================================
            $stUpdate = mysqli_prepare($conn, "UPDATE `order` SET `status` = 'cooking' WHERE `order_id` = ?");
            mysqli_stmt_bind_param($stUpdate, 'i', $orderId);
            mysqli_stmt_execute($stUpdate);

            $finalOrderId = $orderId;
        }

        mysqli_commit($conn);
        echo json_encode(['status' => 'success', 'message' => 'รับออเดอร์และรวมบิลเรียบร้อย', 'order_id' => $finalOrderId]);
    } else {
        throw new Exception("ไม่พบข้อมูลออเดอร์นี้");
    }

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}