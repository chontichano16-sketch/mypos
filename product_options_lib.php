<?php

function ensureProductOptionsTable(mysqli $conn): void
{
    $sql = "CREATE TABLE IF NOT EXISTS product_options (
        option_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        option_name VARCHAR(100) NOT NULL,
        price_adjustment DECIMAL(10,2) NOT NULL DEFAULT 0,
        sort_order INT NOT NULL DEFAULT 0,
        INDEX idx_product_options_product (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!$conn->query($sql)) {
        throw new Exception('Cannot prepare product options table');
    }
}

function getProductOption(mysqli $conn, int $productId, int $optionId): ?array
{
    if ($optionId <= 0) {
        return null;
    }

    $stmt = $conn->prepare(
        'SELECT option_id, option_name, price_adjustment FROM product_options WHERE option_id = ? AND product_id = ?'
    );
    $stmt->bind_param('ii', $optionId, $productId);
    $stmt->execute();
    $option = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $option ?: null;
}
