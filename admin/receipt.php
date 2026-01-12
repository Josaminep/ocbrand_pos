<?php
include '../db.php';
session_start();

/* ===============================
   GET CART
================================ */
$cart = isset($_POST['cart_data'])
    ? json_decode($_POST['cart_data'], true)
    : [];

/* ===============================
   CUSTOMER INFO
================================ */
$customerName = trim($_POST['customer_name'] ?? '');
$customerTIN  = trim($_POST['customer_tin'] ?? '');

$customerName = $customerName !== '' ? $customerName : '--';
$customerTIN  = $customerTIN  !== '' ? $customerTIN  : 'None';

/* ===============================
   PAYMENT
================================ */
$cash   = floatval($_POST['cash_amount'] ?? 0);
$change = floatval($_POST['change_amount'] ?? 0);

/* ===============================
   TOTALS
================================ */
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['qty']; // SRP × qty
}

$vat        = round($subtotal * 0.12, 2);
$grandTotal = $subtotal + $vat;

/* ===============================
   FILE NAME
================================ */
$filename = strtoupper(str_replace(' ', '_', $customerName)) . "_" . date('d_m_Y');

/* ===============================
   STOCK REDUCTION
================================ */
foreach ($cart as $item) {
    $stmt = $conn->prepare(
        "UPDATE products 
         SET quantity = quantity - ? 
         WHERE id = ? AND quantity >= ?"
    );
    $stmt->bind_param("iii", $item['qty'], $item['id'], $item['qty']);
    $stmt->execute();
}

/* ===============================
   SAVE SALE
================================ */
$admin      = $_SESSION['user_id'];
$invoice_no = 'INV-' . date('YmdHis');

$stmt = $conn->prepare("
    INSERT INTO sales
    (invoice_no, total, vat, cash, change_amount, customer_name, customer_tin, admin)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sddddsss",
    $invoice_no,
    $grandTotal,
    $vat,
    $cash,
    $change,
    $customerName,
    $customerTIN,
    $admin
);
$stmt->execute();

$sale_id = $conn->insert_id;

/* ===============================
   PREPARE STATEMENTS
================================ */
$getCostStmt = $conn->prepare("SELECT price FROM products WHERE id = ?");

$itemStmt = $conn->prepare("
    INSERT INTO sales_items
    (sale_id, product_id, product_name, srp, price, quantity, subtotal, profit, discount)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

/* ===============================
   SAVE SALE ITEMS
================================ */
foreach ($cart as $item) {

    // COST price
    $getCostStmt->bind_param("i", $item['id']);
    $getCostStmt->execute();
    $res = $getCostStmt->get_result()->fetch_assoc();

    $cost = floatval($res['price'] ?? 0);      // COST
    $srp  = floatval($item['price']);          // SELLING PRICE

    $itemSubtotal = $srp * $item['qty'];
    $profit       = ($srp - $cost) * $item['qty'];
    $discount     = 0;

    $itemStmt->bind_param(
        "iisdidddi",
        $sale_id,
        $item['id'],
        $item['name'],
        $srp,
        $cost,
        $item['qty'],
        $itemSubtotal,
        $profit,
        $discount
    );
    $itemStmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    display: flex;
    justify-content: center;
    padding: 20px;
}

.receipt {
    width: 320px;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    font-size: 14px;
    line-height: 1.5;
    color: #333;
}

.center { text-align: center; }
.dashed { border-bottom: 1px dashed #aaa; margin: 12px 0; }
.customer-info div { margin-bottom: 4px; }
.item-block { margin-bottom: 10px; }
.item-name { font-weight: 600; font-size: 14px; }
.item-meta { display: flex; justify-content: space-between; font-size: 13px; }
.totals { display: flex; justify-content: space-between; margin-top: 6px; }
.grand-total { font-weight: bold; font-size: 16px; border-top: 1px solid #ccc; padding-top: 4px; }
.cash-change { display: flex; justify-content: space-between; font-weight: 600; margin-top: 4px; color: #007BFF; }
.thank-you { margin-top: 14px; text-align: center; font-style: italic; font-size: 13px; color: #555; }
.footer-time { text-align: center; font-size: 12px; margin-top: 8px; color: #888; }
.save-btn {
    margin-top: 15px;
    display: block;
    width: 100%;
    padding: 10px;
    background: #28a745;
    color: #fff;
    border: none;
    cursor: pointer;
    font-weight: bold;
    border-radius: 4px;
}
.save-btn:hover { background: #218838; }
/* Only affects receipt container */
#receipt-container {
    width: 80mm;           /* or 58mm */
    padding: 6mm 4mm;
    box-sizing: border-box;
    font-family: monospace;
    font-size: 12px;
    background: #fff;
    color: #000;
    border-radius: 0;
    box-shadow: none;
    height: auto !important;
    min-height: 0 !important;
}


/* Receipt content */
#receipt-container .center { text-align: center; }
#receipt-container .dashed { border-bottom: 1px dashed #000; margin: 6px 0; }
#receipt-container .customer-info div { margin-bottom: 2px; }
#receipt-container .item-block { margin-bottom: 6px; }
#receipt-container .item-name { font-weight: bold; font-size: 12px; }
#receipt-container .item-meta { display: flex; justify-content: space-between; font-size: 11px; }
#receipt-container .totals { display: flex; justify-content: space-between; margin-top: 4px; }
#receipt-container .grand-total { font-weight: bold; font-size: 14px; border-top: 1px solid #000; padding-top: 4px; }
#receipt-container .cash-change { display: flex; justify-content: space-between; font-weight: bold; margin-top: 3px; }
#receipt-container .thank-you { margin-top: 8px; text-align: center; font-size: 11px; font-style: italic; }
#receipt-container .footer-time { text-align: center; font-size: 10px; margin-top: 4px; }

/* Keep your save button styled, it's outside receipt */
.save-btn {
    margin-top: 10px;
    width: 100%;
    padding: 8px;
    background: #28a745;
    color: #fff;
    border: none;
    font-weight: bold;
    cursor: pointer;
}
.save-btn:hover { background: #218838; }

</style>
</head>

<body>
<div id="receipt-container" class="receipt">

    <div class="center" style="font-size:12px; font-weight:bold;">OC BRAND - MANDALUYONG CITY</div>
    <div class="center" style="font-size:12px;">312 RT. REV. G. AGLIPAY, MANDALUYONG</div>
    <div class="center" style="font-size:12px;">VAT REG TIN: 000000000</div>
    <div class="center" style="font-size:12px;">TEL NO: 09817382041</div>
    <div class="center" style="font-size:12px;">VIBER NO: 09817382041</div>

    <div class="dashed"></div>

    <div class="customer-info">
        <div><strong>Customer Name:</strong> <?= htmlspecialchars($customerName) ?></div>
        <div><strong>TIN ID:</strong> <?= htmlspecialchars($customerTIN) ?></div>
    </div>

    <div class="dashed"></div>

    <?php foreach($cart as $item): ?>
        <div class="item-block">
            <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
            <div class="item-meta">
                <span><?= $item['qty'] ?> x ₱<?= number_format($item['price'],2) ?></span>
                <span>₱<?= number_format($item['price'] * $item['qty'],2) ?></span>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="dashed"></div>

    <div class="totals">
        <span>Subtotal:</span>
        <span>₱<?= number_format($subtotal,2) ?></span>
    </div>

    <div class="totals">
        <span>VAT (12%):</span>
        <span>₱<?= number_format($vat,2) ?></span>
    </div>

    <div class="totals grand-total">
        <span>Grand Total:</span>
        <span>₱<?= number_format($grandTotal,2) ?></span>
    </div>

    <div class="cash-change">
        <span>Cash:</span>
        <span>₱<?= number_format($cash,2) ?></span>
    </div>

    <div class="cash-change">
        <span>Change:</span>
        <span>₱<?= number_format($change,2) ?></span>
    </div>

    <div class="dashed"></div>

    <div class="thank-you">Thank you for your purchase!</div>
    <div class="footer-time"><?= date('Y-m-d H:i') ?></div>

    <button class="save-btn" onclick="downloadReceipt()">Save</button>
</div>

<script>
function downloadReceipt() {
    const el = document.getElementById('receipt-container');
    const btn = document.querySelector('.save-btn');
    btn.style.display = 'none';

    // px to mm
    const pxToMm = px => px * 0.264583;

    // Use scrollHeight for full content
    const receiptHeight = pxToMm(el.scrollHeight);

    html2pdf().set({
        margin: 0,
        filename: '<?= $filename ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
            scale: 2,
            useCORS: true,
            scrollY: -window.scrollY, // capture full content
            windowWidth: el.scrollWidth // ensures full width is rendered
        },
        jsPDF: {
            unit: 'mm',
            format: [80, receiptHeight], // dynamic height
            orientation: 'portrait'
        }
    }).from(el).save().then(() => {
        btn.style.display = 'block';
    });
}

</script>


</body>
</html>
