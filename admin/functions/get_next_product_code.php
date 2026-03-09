<?php
include "../../db.php";

$category = $_GET['category'] ?? '';

if ($category === '') {
    echo '';
    exit();
}

$prefix = strtoupper(substr($category, 0, 3));

$stmt = $conn->prepare(
    "SELECT product_code 
     FROM products 
     WHERE product_code LIKE CONCAT(?, '%')
     ORDER BY product_code DESC
     LIMIT 1"
);
$stmt->bind_param("s", $prefix);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $num = (int) substr($row['product_code'], 3) + 1;
} else {
    $num = 1;
}

echo $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
