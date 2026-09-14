<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';
require_once 'product_options_lib.php';

try {
    ensureProductOptionsTable($conn);
    $productId = (int)($_GET['product_id'] ?? 0);
    if ($productId <= 0) {
        throw new Exception('Invalid product');
    }

    $stmt = $conn->prepare(
        'SELECT option_id, option_name, price_adjustment FROM product_options WHERE product_id = ? ORDER BY sort_order, option_id'
    );
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] = [
            'id' => (int)$row['option_id'],
            'label' => $row['option_name'],
            'adjustment' => (float)$row['price_adjustment']
        ];
    }

    echo json_encode(['success' => true, 'options' => $options]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
