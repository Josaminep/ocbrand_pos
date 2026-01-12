<?php

session_start();
include "../db.php";

$data = json_decode($_POST['cart_data'], true);

$subtotal = 0;
foreach($data as $item){
    $subtotal += $item['price'] * $item['qty'];
}
$vat = $subtotal * 0.12;
$total = $subtotal + $vat;

$cash = $_POST['cash_amount'];
$change = $_POST['change_amount'];

$customer = $_POST['customer_name'];
$tin = $_POST['customer_tin'];
$admin = $_SESSION['admin_name'] ?? 'Admin';
$invoice = 'INV-' . time();

mysqli_query($conn,"
INSERT INTO sales 
(invoice_no,total,vat,cash,change_amount,customer_name,customer_tin,admin)
VALUES
('$invoice','$total','$vat','$cash','$change','$customer','$tin','$admin')
");

$sale_id = mysqli_insert_id($conn);

/* SAVE ITEMS + UPDATE STOCK */
foreach($data as $item){
    $pid = $item['id'];
    $qty = $item['qty'];
    $price = $item['price'];
    $sub = $price * $qty;

    mysqli_query($conn,"
    INSERT INTO sales_items 
    (sale_id,product_id,product_name,price,quantity,subtotal)
    VALUES
    ('$sale_id','$pid','{$item['name']}','$price','$qty','$sub')
    ");

    mysqli_query($conn,"
    UPDATE products SET quantity = quantity - $qty WHERE id = $pid
    ");
}

?>