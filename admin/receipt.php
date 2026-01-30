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
$adminId     = $_SESSION['user_id'];
$invoice_no  = 'INV-' . date('YmdHis');

// Fetch cashier full name
$stmtUser = $conn->prepare("SELECT fname, lname FROM accounts WHERE id = ?");
$stmtUser->bind_param("i", $adminId);
$stmtUser->execute();
$resUser = $stmtUser->get_result()->fetch_assoc();
$cashierName = trim($resUser['fname'] . ' ' . $resUser['lname']);

/* Insert sale */
$stmt = $conn->prepare("
    INSERT INTO sales
    (invoice_no, total, vat, cash, change_amount, customer_name, customer_tin, user)
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
    $adminId
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
html, body {
    margin: 0;
    padding: 0;
    background: #f2f2f2;
    font-family: Arial, sans-serif;
}

#receipt-container{
    width: 80mm;
    padding: 4mm;
    box-sizing: border-box;
    background: #fff;
    font-family: monospace;
    font-size: 12px;
    color: #000;
    margin: 0 auto;
}

.center{ text-align:center; }
.bold{ font-weight:bold; }
.line{ border-bottom:1px dashed #000; margin:6px 0; }
.item-name{ font-weight:bold; margin-top:4px; }
.big-total{ font-size:14px; font-weight:bold; }

.row{
    display:flex;
    justify-content:space-between;
    margin:2px 0;
    gap:4px;
}
.row span:first-child{
    max-width:60%;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.row span:last-child{
    white-space:nowrap;
}

.logo{
    display:block;
    margin: 0 auto 4px auto;
    width: 60px;
    height: auto;
}

.save-btn{
    margin-top:10px;
    width:100%;
    padding:8px;
    background:#000;
    color:#fff;
    border:none;
    font-weight:bold;
    cursor:pointer;
}
</style>
</head>

<body>

<div id="receipt-container">

    <!-- LOGO -->
    <img src="../uploads/ocb_logo.jpg" class="logo" alt="Logo">

    <div class="center bold">OC BRAND</div>
    <div class="center">312 RT. REV. G. AGLIPAY OLD ZANIGA</div>
    <div class="center">MANDALUYONG CITY</div>
    <div class="center">VAT REG TIN: 000000000</div>
    <div class="center">TEL NO: 09817382041</div>
    <div class="center">VIBER NO: 09817382041</div>

    <div class="line"></div>

    <div>Customer Name: <?= htmlspecialchars($customerName) ?></div>
    <div>TIN: <?= htmlspecialchars($customerTIN) ?></div>
    <div>Address: __________________________</div>

    <div class="line"></div>

    <!-- ITEMS -->
    <?php 
        $totalItems = 0;
        foreach($cart as $item):
            $lineTotal = $item['price'] * $item['qty'];
            $totalItems += $item['qty'];
    ?>
        <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
        <div class="row">
            <span><?= $item['qty'] ?> x ₱<?= number_format($item['price'],2) ?></span>
            <span>₱<?= number_format($lineTotal,2) ?></span>
        </div>
    <?php endforeach; ?>

    <div class="line"></div>

    <div class="row big-total">
        <span>TOTAL</span>
        <span>₱<?= number_format($grandTotal,2) ?></span>
    </div>

    <div class="row">
        <span>Cash</span>
        <span>₱<?= number_format($cash,2) ?></span>
    </div>
    <div class="row">
        <span>Change</span>
        <span>₱<?= number_format($change,2) ?></span>
    </div>

    <div class="line"></div>

    <?php
        $vatable = round($grandTotal / 1.12, 2);
        $vat12   = round($vatable * 0.12, 2);
    ?>
    <div class="row">
        <span>Vatable</span>
        <span><?= number_format($vatable,2) ?></span>
    </div>
    <div class="row">
        <span>VAT (12%)</span>
        <span><?= number_format($vat12,2) ?></span>
    </div>
    <div class="row">
        <span>VAT Exempt</span>
        <span>0.00</span>
    </div>
    <div class="row">
        <span>Zero Rated</span>
        <span>0.00</span>
    </div>
    <div class="row bold">
        <span>Total</span>
        <span><?= number_format($grandTotal,2) ?></span>
    </div>

    <div class="line"></div>

    <div>Total Items: <?= $totalItems ?></div>
    <div>Cashier: <?= htmlspecialchars($cashierName) ?></div>
    <div>SI No: <?= $invoice_no; ?></div>

    <div class="line"></div>

    <div class="center">Thank you for your purchase!</div>
    <div class="center"><?= date('Y-m-d H:i') ?></div>

    <button class="save-btn" onclick="downloadReceipt()">Save</button>

</div>

<script>
function downloadReceipt(){
    const el  = document.getElementById('receipt-container');
    const btn = document.querySelector('.save-btn');

    btn.style.display = 'none';

    html2pdf().set({
        margin: 0,
        filename: '<?= $filename ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
        jsPDF: {
            unit: 'mm',
            format: [80, el.scrollHeight * 0.264583],
            orientation: 'portrait'
        }
    }).from(el).save().then(()=>{
        btn.style.display = 'block';
    });
}
</script>

</body>
</html>