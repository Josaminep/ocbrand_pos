<?php
include '../db.php';
session_start();

/* ===============================
   GET CART DATA
================================ */
$cart = isset($_POST['cart_data'])
    ? json_decode($_POST['cart_data'], true)
    : [];

/* ===============================
   CUSTOMER INFO
================================ */
$customerName    = trim($_POST['customer_name'] ?? '');
$customerTIN     = trim($_POST['customer_tin'] ?? '');
$customerAddress = trim($_POST['customer_address'] ?? '');

$customerName    = $customerName !== '' ? $customerName : '--';
$customerTIN     = $customerTIN  !== '' ? $customerTIN  : 'None';
$customerAddress = $customerAddress !== '' ? $customerAddress : '--';

/* ===============================
   PAYMENT INFO
================================ */
// Existing (kept for compatibility)
$cash   = floatval($_POST['cash_amount'] ?? 0);
$change = floatval($_POST['change_amount'] ?? 0);

// NEW: payment category + paid amount
$paymentMethod = trim($_POST['payment_method'] ?? 'Cash');
$paidAmount    = floatval($_POST['paid_amount'] ?? 0);

// Whitelist payment method
$allowedMethods = ['Cash', 'GCash', 'Maya', 'Online Banking'];
if (!in_array($paymentMethod, $allowedMethods, true)) {
    $paymentMethod = 'Cash';
}

/* ===============================
   CALCULATE TOTALS
================================ */
$grandTotal = 0;
$totalQty   = 0;

foreach ($cart as $item) {
    $grandTotal += $item['price'] * $item['qty'];
    $totalQty   += $item['qty'];
}

// VAT-inclusive calculations
$vatable = round($grandTotal / 1.12, 2);
$vat     = round($grandTotal - $vatable, 2);

/* ===============================
   VALIDATE PAYMENT (server-side)
================================ */
if (empty($cart) || $grandTotal <= 0) {
    die("Invalid cart.");
}

if ($paymentMethod === 'Cash') {
    // Cash must be >= total
    if ($cash < $grandTotal) {
        die("Invalid cash amount.");
    }
    // if paidAmount not sent, set it from cash
    if ($paidAmount <= 0) {
        $paidAmount = $cash;
    }
    // If change not sent (or wrong), recompute
    $change = round($cash - $grandTotal, 2);
} else {
    // Non-cash usually should match exact total
    // If you want allow >= total, change validation here
    if (abs($paidAmount - $grandTotal) > 0.01) {
        die("Invalid non-cash amount.");
    }
    // For non-cash, keep these consistent
    $cash = $paidAmount;
    $change = 0.00;
}

/* ===============================
   FILE NAME (optional)
================================ */
$filename = strtoupper(str_replace(' ', '_', $customerName)) . "_" . date('d_m_Y');

/* ===============================
   STOCK REDUCTION
================================ */
foreach ($cart as $item) {
    $stmt = $conn->prepare("
        UPDATE products
        SET quantity = quantity - ?
        WHERE id = ? AND quantity >= ?
    ");
    $stmt->bind_param("iii", $item['qty'], $item['id'], $item['qty']);
    $stmt->execute();
}

/* ===============================
   SAVE SALE
================================ */
$adminId    = $_SESSION['user_id'] ?? 0;
$invoice_no = 'INV-' . date('YmdHis');

// Fetch cashier name (optional, not used below but kept)
$stmtUser = $conn->prepare("SELECT fname, lname FROM accounts WHERE id = ?");
$stmtUser->bind_param("i", $adminId);
$stmtUser->execute();
$resUser = $stmtUser->get_result()->fetch_assoc();
$cashierName = trim(($resUser['fname'] ?? '') . ' ' . ($resUser['lname'] ?? ''));

// Insert sale (UPDATED with payment_method + paid_amount)
$stmt = $conn->prepare("
    INSERT INTO sales
    (invoice_no, total, vatable, vat, qty, cash, change_amount, payment_method, paid_amount, customer_name, customer_tin, customer_address, user)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sdddiddsdsssi",
    $invoice_no,
    $grandTotal,
    $vatable,
    $vat,
    $totalQty,
    $cash,
    $change,
    $paymentMethod,
    $paidAmount,
    $customerName,
    $customerTIN,
    $customerAddress,
    $adminId
);
$stmt->execute();

$sale_id = $conn->insert_id;

/* ===============================
   PREPARE STATEMENTS
================================ */
$getProductStmt = $conn->prepare("
    SELECT price, product_code
    FROM products
    WHERE id = ?
");

$itemStmt = $conn->prepare("
    INSERT INTO sales_items
    (sale_id, invoice_no, product_id, product_code, product_name, srp, price, quantity, subtotal, vatable, vat, profit, discount)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

/* ===============================
   SAVE EACH ITEM (with Vatable & VAT)
================================ */
foreach ($cart as $item) {

    // Get COST + product_code from products
    $getProductStmt->bind_param("i", $item['id']);
    $getProductStmt->execute();
    $res = $getProductStmt->get_result()->fetch_assoc();

    $cost        = floatval($res['price'] ?? 0);
    $productCode = $res['product_code'] ?? '';

    $srp          = floatval($item['price']);
    $itemSubtotal = $srp * $item['qty'];

    // Calculate vatable and VAT per item
    $itemVatable = round($itemSubtotal / 1.12, 2);
    $itemVAT     = round($itemSubtotal - $itemVatable, 2);

    $profit   = ($srp - $cost) * $item['qty'];
    $discount = 0;

    $itemStmt->bind_param(
        "isissdidddddd",
        $sale_id,       // int
        $invoice_no,    // string
        $item['id'],    // int
        $productCode,   // string
        $item['name'],  // string
        $srp,           // double
        $cost,          // double
        $item['qty'],   // int
        $itemSubtotal,  // double
        $itemVatable,   // double
        $itemVAT,       // double
        $profit,        // double
        $discount       // double
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
html, body { margin: 0; padding: 0; background: #f2f2f2; font-family: Arial, sans-serif; }
#receipt-container { width: 80mm; padding: 4mm; box-sizing: border-box; background: #fff; font-family: monospace; font-size: 12px; color: #000; margin: 0 auto; }
.center{ text-align:center; } .bold{ font-weight:bold; } .line{ border-bottom:1px dashed #000; margin:6px 0; }
.item-name{ font-weight:bold; margin-top:4px; } .big-total{ font-size:14px; font-weight:bold; }
.row{ display:flex; justify-content:space-between; margin:2px 0; gap:4px; }
.row span:first-child{ max-width:60%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.row span:last-child{ white-space:nowrap; }
.logo{ display:block; margin: 0 auto 4px auto; width: 60px; height: auto; }
.save-btn{ margin-top:10px; width:100%; padding:8px; background:#000; color:#fff; border:none; font-weight:bold; cursor:pointer; }
</style>
</head>
<body>
<div id="receipt-container">

    <img src="../uploads/ocb_logo.jpg" class="logo" alt="Logo">
    <div class="center bold">OC BRAND</div>
    <div class="center">312 RT. REV. G. AGLIPAY OLD ZANIGA</div>
    <div class="center">MANDALUYONG CITY</div>
    <div class="center">VAT REG TIN: 462-184-304-00000</div>
    <div class="center">TEL NO: 09817382041</div>
    <div class="center">VIBER NO: 09817382041</div>

    <div class="line"></div>
    <div>Customer Name: <?= htmlspecialchars($customerName) ?></div>
    <div>TIN: <?= htmlspecialchars($customerTIN) ?></div>
    <div>Address: <?= htmlspecialchars($customerAddress) ?></div>
    <div class="line"></div>

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

    <!-- ✅ PAYMENT DISPLAY -->
    <div class="row">
        <span>Payment Method</span>
        <span><?= htmlspecialchars($paymentMethod ?? 'Cash') ?></span>
    </div>

    <?php if (($paymentMethod ?? 'Cash') === 'Cash'): ?>
        <div class="row">
            <span>Cash</span>
            <span>₱<?= number_format($cash,2) ?></span>
        </div>
        <div class="row">
            <span>Change</span>
            <span>₱<?= number_format($change,2) ?></span>
        </div>
    <?php else: ?>
        <div class="row">
            <span>Amount Paid</span>
            <span>₱<?= number_format($paidAmount ?? $cash,2) ?></span>
        </div>
    <?php endif; ?>

    <div class="line"></div>

    <div class="row">
        <span>Vatable</span>
        <span>₱<?= number_format($vatable,2) ?></span>
    </div>
    <div class="row">
        <span>VAT (12%)</span>
        <span>₱<?= number_format($vat,2) ?></span>
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
        <span>₱<?= number_format($grandTotal,2) ?></span>
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
        filename: '<?= $invoice_no ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
        jsPDF: { unit: 'mm', format: [80, el.scrollHeight * 0.264583], orientation: 'portrait' }
    }).from(el).save().then(()=>{
        btn.style.display = 'block';
    });
}
</script>
</body>
</html>