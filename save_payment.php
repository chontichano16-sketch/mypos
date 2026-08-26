
<?php
require_once 'db.php';
// PHP ส่งค่ากลับเป็น JSON
header('Content-Type: application/json; charset=utf-8');

$table_id = $_POST['table_id'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';
$total_amount = $_POST['total_amount'] ?? 0;
$is_takeaway = $_POST['is_takeaway'] ?? 'no';

if ($is_takeaway === 'yes') {
    $items = json_decode($_POST['items'], true);

    mysqli_begin_transaction($conn);
    try {
        $stmt = $conn->prepare("INSERT INTO `order` (table_id, total_amount, status, payment_method, created_at) VALUES (?, ?, 'paid', ?, NOW())");
        $stmt->bind_param("sds", $table_id, $total_amount, $payment_method);
        $stmt->execute();

        $order_id = $stmt->insert_id; // ดึงรหัสบิลที่เพิ่งสร้างใหม่

        $stmt_detail = $conn->prepare("INSERT INTO order_detail (order_id, product_id, quantity, price, remark) VALUES (?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $pid = $item['id'];
            $qty = $item['quantity'];
            $price = $item['price'];
            $remark = $item['remark'] ?? '';
            $stmt_detail->bind_param("iiids", $order_id, $pid, $qty, $price, $remark);
            $stmt_detail->execute();
        }
        mysqli_commit($conn);

        // ส่งรหัสบิลกลับไปให้ JavaScript
        echo json_encode(["status" => "success", "order_id" => $order_id]);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    $stmt_find = $conn->prepare("SELECT order_id FROM `order` WHERE table_id = ? AND status = 'pending' LIMIT 1");
    $stmt_find->bind_param("s", $table_id);
    $stmt_find->execute();
    $result = $stmt_find->get_result();

    if ($row = $result->fetch_assoc()) {
        $order_id = $row['order_id'];

        $stmt_update = $conn->prepare("UPDATE `order` SET status = 'paid', payment_method = ?, total_amount = ? WHERE order_id = ?");
        $stmt_update->bind_param("sdi", $payment_method, $total_amount, $order_id);

        if ($stmt_update->execute()) {
            // ส่งรหัสบิลกลับไปให้ JavaScript
            echo json_encode(["status" => "success", "order_id" => $order_id]);
        } else {
            echo json_encode(["status" => "error", "message" => "อัปเดตบิลไม่สำเร็จ"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No pending bill found (ไม่พบบิลค้างชำระ)"]);
    }
}
?>