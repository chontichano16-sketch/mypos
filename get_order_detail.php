<?php
require_once "db.php";
header('Content-Type: application/json; charset=utf-8');

$order_id = (int)($_GET['id'] ?? 0);

if ($order_id > 0) {
    $sql_order = "SELECT table_id FROM `order` WHERE order_id = $order_id";
    $query_order = mysqli_query($conn, $sql_order);
    $order_data = mysqli_fetch_assoc($query_order);

    $sql_details = "SELECT od.quantity, od.price, p.p_name AS name , od.remark
                    FROM order_detail od
                    JOIN products p ON od.product_id = p.p_id 
                    WHERE od.order_id = $order_id";

    $query_details = mysqli_query($conn, $sql_details);

    $items = array();
    $total_amount = 0;

    while ($row = mysqli_fetch_assoc($query_details)) {
        $items[] = $row;
        $total_amount += ($row['price'] * $row['quantity']);
    }

    echo json_encode([
        'success' => true,
        'table_id' => $order_data['table_id'] ?? '',
        'items' => $items,
        'total' => $total_amount
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'No Order ID']);
}
