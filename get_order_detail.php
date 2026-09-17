<?php
require_once "db.php";
header('Content-Type: application/json; charset=utf-8');

$order_id = (int)($_GET['id'] ?? 0);

if ($order_id > 0) {
    $sql_order = "SELECT table_id, parent_order_id FROM `order` WHERE order_id = $order_id";
    $order_data = mysqli_fetch_assoc(mysqli_query($conn, $sql_order));

    // ถ้าเป็นบิลลูก ให้ย้ายไปอ่านบิลแม่แทน
    if (!empty($order_data['parent_order_id']) && (int)$order_data['parent_order_id'] > 0) {
        $order_id = (int)$order_data['parent_order_id'];
    }

    // เพิ่ม od.option_label ในคำสั่ง SELECT
    $sql_details = "SELECT od.quantity, od.price, p.p_name AS name, od.remark, od.option_label
                    FROM order_detail od
                    JOIN products p ON od.product_id = p.p_id
                    WHERE od.order_id IN (
                        SELECT order_id FROM `order`
                        WHERE order_id = $order_id OR parent_order_id = $order_id
                    )";
    $query_details = mysqli_query($conn, $sql_details);
    if (!$query_details) {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        exit;
    }

    $items = [];
    $total_amount = 0;
    
    while ($row = mysqli_fetch_assoc($query_details)) {
        $items[] = $row;
        $total_amount += ($row['price'] * $row['quantity']);
    }

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'table_id' => $order_data['table_id'] ?? '',
        'items' => $items,
        'total' => $total_amount
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'message' => 'No Order ID'], JSON_UNESCAPED_UNICODE);
}
