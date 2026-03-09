<?php
include '../../db.php';
session_start();

/* ============================
   LOGGED-IN USER INFO
============================ */
$loggedUser = $_SESSION['user_id'] ?? '';
$userRole   = $_SESSION['role'] ?? '';

/* ============================
   DATE RANGE & CASHIER
============================ */
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');
$cashier = $_GET['cashier'] ?? '';

$fromDate = $from . ' 00:00:00';
$toDate   = $to . ' 23:59:59';

/* ============================
   BUILD WHERE CONDITION
============================ */
$where = "s.created_at BETWEEN '$fromDate' AND '$toDate'";

// Restrict to logged-in cashier
if ($userRole === 'cashier' && $loggedUser !== '') {
    $where .= " AND s.user = '$loggedUser'";
} elseif ($cashier !== '') {
    // Admin-selected cashier
    $where .= " AND s.user = '$cashier'";
}

/* ============================
   FETCH SALES ITEMS
============================ */
$items = [];
$q = $conn->query("
    SELECT
        s.user AS cashier_id,
        CONCAT(a.fname,' ',a.lname) AS cashier_name,
        s.invoice_no,
        si.product_code,
        si.product_name,
        p.category,
        si.price,
        si.srp,
        si.quantity,
        si.subtotal,
        si.vatable,
        si.vat,
        si.profit,
        s.payment_method,
        s.created_at
    FROM sales_items si
    INNER JOIN sales s ON s.id = si.sale_id
    LEFT JOIN products p ON p.id = si.product_id
    LEFT JOIN accounts a ON a.id = s.user
    WHERE $where
    ORDER BY a.fname, s.created_at ASC, si.id ASC
");

while ($row = $q->fetch_assoc()) {
    $items[] = $row;
}

/* ============================
   GROUP ITEMS BY CASHIER
============================ */
$grouped = [];
foreach ($items as $i) {
    $grouped[$i['cashier_id']]['name'] = $i['cashier_name'];
    $grouped[$i['cashier_id']]['items'][] = $i;
}

/* ============================
   TOTALS PER CASHIER
============================ */
$totals = [];
foreach ($grouped as $cid => $data) {
    $totals[$cid]['sales']   = array_sum(array_column($data['items'], 'subtotal'));
    $totals[$cid]['vatable'] = array_sum(array_column($data['items'], 'vatable'));
    $totals[$cid]['vat']     = array_sum(array_column($data['items'], 'vat'));
    $totals[$cid]['profit']  = array_sum(array_column($data['items'], 'profit'));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Report PDF</title>
<style>
@media print { @page { size: A4; margin: 20mm; } body { font-family: Arial, sans-serif; font-size: 11px; color: #000; } .no-print { display: none !important; } }
body { margin: 0; background: #fff; font-family: Arial, sans-serif; font-size: 11px; }
.header { text-align: center; margin-bottom: 20px; }
.header h1 { font-size: 18px; margin: 0; letter-spacing: 1px; }
.header p { margin-top: 5px; font-size: 11px; }
.cashier-line { display: flex; justify-content: space-between; margin-top: 20px; margin-bottom: 5px; font-weight: bold; }
table { width: 100%; border-collapse: collapse; margin-top: 5px; }
th, td { border: 1px solid #000; padding: 6px; font-size: 11px; }
th { background: #ffeb3b; font-weight: bold; }
.amount { text-align: right; }
.center { text-align: center; }
.totals { margin-top: 8px; padding: 6px 10px; background: #fff8dc; border-radius: 6px; max-width: 300px; margin-left: auto; font-size: 12px; }
.totals div { display: flex; justify-content: space-between; padding: 2px 0; }
.footer { text-align: center; font-size: 10px; color: #555; margin-top: 15px; }
.no-print { margin-bottom: 10px; text-align: right; }
</style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">Print / Save as PDF</button>
</div>

<div class="header">
    <h1>SALES ITEMS REPORT</h1>
</div>

<?php if (!$items): ?>
<p>No records found for selected date range.</p>
<?php else: ?>
    <?php foreach ($grouped as $cid => $data): ?>
        <div class="cashier-line">
            <div>Sales Person: <?= htmlspecialchars($data['name']) ?></div>
            <div>Date Range: <?= $from ?> to <?= $to ?></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice No</th>
                    <th>SKU ID</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th class="amount">Price</th>
                    <th class="amount">SRP</th>
                    <th class="center">QTY</th>
                    <th class="amount">Total</th>
                    <th class="amount">Vatable</th>
                    <th class="amount">12% VAT</th>
                    <th class="amount">Profit</th>
                    <th>Payment Method</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data['items'] as $i): ?>
                <tr>
                    <td><?= date('Y-m-d', strtotime($i['created_at'])) ?></td>
                    <td><?= htmlspecialchars($i['invoice_no']) ?></td>
                    <td><?= htmlspecialchars($i['product_code']) ?></td>
                    <td><?= htmlspecialchars($i['product_name']) ?></td>
                    <td><?= htmlspecialchars($i['category'] ?? '—') ?></td>
                    <td class="amount">₱<?= number_format($i['price'],2) ?></td>
                    <td class="amount">₱<?= number_format($i['srp'],2) ?></td>
                    <td class="center"><?= $i['quantity'] ?></td>
                    <td class="amount">₱<?= number_format($i['subtotal'],2) ?></td>
                    <td class="amount">₱<?= number_format($i['vatable'],2) ?></td>
                    <td class="amount">₱<?= number_format($i['vat'],2) ?></td>
                    <td class="amount" style="color:<?= $i['profit']<0?'#dc3545':'#28a745' ?>">
                        ₱<?= number_format($i['profit'],2) ?>
                    </td>
                    <td><?= htmlspecialchars($i['payment_method'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <div><span>Total Sales:</span><span>₱<?= number_format($totals[$cid]['sales'],2) ?></span></div>
            <div><span>Total Vatable:</span><span>₱<?= number_format($totals[$cid]['vatable'],2) ?></span></div>
            <div><span>Total 12% VAT:</span><span>₱<?= number_format($totals[$cid]['vat'],2) ?></span></div>
            <div><span>Total Profit:</span><span>₱<?= number_format($totals[$cid]['profit'],2) ?></span></div>
        </div>
        <br><br>
    <?php endforeach; ?>
<?php endif; ?>

<div class="footer">
    Generated on <?= date('F d, Y h:i A') ?>
</div>

<script>
window.onload = function () { window.print(); };
</script>

</body>
</html>