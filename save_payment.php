
<?php
require_once 'db.php';
require_once 'product_options_lib.php';
// PHP ส่งค่ากลับเป็น JSON
header('Content-Type: application/json; charset=utf-8');

$table_id = $_POST['table_id'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';
$total_amount = $_POST['total_amount'] ?? 0;
$is_takeaway = $_POST['is_takeaway'] ?? 'no';

if ($is_takeaway === 'yes') {
    $items = json_decode($_POST['items'], true);

    if ($table_id !== 'Takeaway') {
        echo json_encode(["status" => "error", "message" => "Invalid takeaway order"]);
        exit;
    }

    if (!is_array($items) || count($items) === 0) {
        echo json_encode(["status" => "error", "message" => "No order items provided"]);
        exit;
    }

    ensureProductOptionsTable($conn);
    mysqli_begin_transaction($conn);
    try {
        // ใช้ราคาจากฐานข้อมูล เพื่อไม่ให้ยอดชำระถูกแก้ไขจากหน้าเว็บ
        $total_amount = 0;
        $processed_items = [];
        $stmt_product = $conn->prepare("SELECT p_price FROM products WHERE p_id = ?");

        foreach ($items as $item) {
            $pid = (int)($item['id'] ?? 0);
            $qty = (int)($item['quantity'] ?? 0);
            $option_id = (int)($item['optionId'] ?? 0);
            $option = getProductOption($conn, $pid, $option_id);
            $option_label = $option['option_name'] ?? '';
            $option_adjustment = (float)($option['price_adjustment'] ?? 0);

            if ($pid <= 0 || $qty <= 0 || ($option_id > 0 && !$option)) {
                throw new Exception("Invalid order item");
            }

            $stmt_product->bind_param("i", $pid);
            $stmt_product->execute();
            $product = $stmt_product->get_result()->fetch_assoc();
            if (!$product) {
                throw new Exception("Product not found");
            }

            $price = (float)$product['p_price'] + $option_adjustment;
            $total_amount += $price * $qty;
            $remark_parts = [];
            if ($option_label !== '') {
                $remark_parts[] = $option_label;
            }
            if (!empty($item['remark'])) {
                $remark_parts[] = trim((string)$item['remark']);
            }
            $processed_items[] = [
                'id' => $pid,
                'quantity' => $qty,
                'price' => $price,
                'remark' => implode(' | ', $remark_parts)
            ];
        }

        $stmt = $conn->prepare("INSERT INTO `order` (table_id, total_amount, status, payment_method, created_at) VALUES (?, ?, 'paid', ?, NOW())");
        $stmt->bind_param("sds", $table_id, $total_amount, $payment_method);
        $stmt->execute();

        $order_id = $stmt->insert_id; // ดึงรหัสบิลที่เพิ่งสร้างใหม่

        $stmt_detail = $conn->prepare("INSERT INTO order_detail (order_id, product_id, quantity, price, remark) VALUES (?, ?, ?, ?, ?)");
        foreach ($processed_items as $item) {
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
