<?php
include '../../db.php';
session_start();

/* ============================
   LOGGED-IN USER INFO
============================ */
$loggedUser = $_SESSION['user_id'] ?? '';
$userRole   = $_SESSION['role'] ?? '';

/* ============================
   DATE RANGE
============================ */
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');

$fromDate = $from . ' 00:00:00';
$toDate   = $to   . ' 23:59:59';

/* ============================
   CASHIER FILTER
============================ */
$cashier = $_GET['cashier'] ?? '';

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
        si.sale_id,
        s.invoice_no,
        si.product_id,
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
        s.payment_method
    FROM sales_items si
    INNER JOIN sales s ON s.id = si.sale_id
    LEFT JOIN products p ON p.id = si.product_id
    WHERE $where
    ORDER BY s.created_at DESC, si.id ASC
");

while ($row = $q->fetch_assoc()) {
    $items[] = $row;
}

/* ============================
   TOTALS
============================ */
$totalSales   = array_sum(array_column($items, 'subtotal'));
$totalVatable = array_sum(array_column($items, 'vatable'));
$totalVat     = array_sum(array_column($items, 'vat'));
$totalProfit  = array_sum(array_column($items, 'profit'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Items Report</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { font-family:"Poppins",sans-serif; background:#f4f6f9; padding:40px; }
.header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.actions a { text-decoration:none; margin-left:10px; padding:10px 14px; border-radius:8px; font-weight:600; }
.btn-back { background:#e5e5e5; color:#111; }
.btn-print { background:#111; color:#fff; }
.filter-bar { display:flex; gap:10px; margin-bottom:20px; }
.filter-bar input, .filter-bar select, .filter-bar button { padding:8px 12px; border-radius:8px; border:1px solid #ccc; }
.filter-bar button { background:#111; color:#fff; border:none; cursor:pointer; }
.card { background:#fff; padding:20px; border-radius:16px; box-shadow:0 8px 30px rgba(0,0,0,0.08); margin-bottom:25px; }
table { width:100%; border-collapse:collapse; }
thead th { background:#ffeb3b; padding:12px; text-align:left; position:sticky; top:0; z-index:2; }
tbody td { padding:10px; border-bottom:1px solid #eee; }
tfoot td { font-weight:bold; padding:12px; background:#fafafa; }
.text-right { text-align:right; }
.text-center { text-align:center; }
.totals { margin-top:20px; background:#fff8dc; padding:16px 20px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); max-width:400px; margin-left:auto; }
.totals-row { display:flex; justify-content:space-between; padding:6px 0; font-size:16px; }
@media print { .filter-bar, .actions { display:none; } body { padding:20px; } }
</style>
</head>
<body>

<div class="header">
    <h1><i class="fas fa-file-alt"></i> Sales Items Report</h1>
    <div class="actions">
        <a href="../reports.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back</a>
        <a href="sales_report_pdf.php?from=<?= $from ?>&to=<?= $to ?>" target="_blank" class="btn-print">
            <i class="fas fa-print"></i> Print PDF
        </a>
    </div>
</div>

<!-- FILTER -->
<form class="filter-bar" method="GET">
    <input type="date" name="from" value="<?= $from ?>" required>
    <input type="date" name="to" value="<?= $to ?>" required>
    <?php if ($userRole !== 'cashier'): ?>
        <select name="cashier">
            <option value="">All Sales Person</option>
            <?php
            $cq = $conn->query("SELECT DISTINCT a.id, CONCAT(a.fname,' ',a.lname) AS fullname 
                                FROM sales s INNER JOIN accounts a ON a.id=s.user
                                WHERE a.fname != '' AND a.lname != ''
                                ORDER BY fullname ASC");
            while ($c = $cq->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>" <?= $cashier==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['fullname']) ?></option>
            <?php endwhile; ?>
        </select>
    <?php endif; ?>
    <button>Filter</button>
</form>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>SKU ID</th>
                <th>Item Name</th>
                <th>Category</th>
                <th class="text-right">Price</th>
                <th class="text-right">SRP</th>
                <th class="text-center">QTY</th>
                <th class="text-right">Total</th>
                <th class="text-right">Vatable</th>
                <th class="text-right">12% VAT</th>
                <th class="text-right">Profit</th>
                <th>Payment Method</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$items): ?>
            <tr><td colspan="11" class="text-center">No records found</td></tr>
        <?php else: foreach($items as $i): ?>
            <tr>
                <td><?= htmlspecialchars($i['product_code']) ?></td>
                <td><?= htmlspecialchars($i['product_name']) ?></td>
                <td><?= htmlspecialchars($i['category'] ?? '—') ?></td>
                <td class="text-right">₱<?= number_format($i['price'],2) ?></td>
                <td class="text-right">₱<?= number_format($i['srp'],2) ?></td>
                <td class="text-center"><?= $i['quantity'] ?></td>
                <td class="text-right">₱<?= number_format($i['subtotal'],2) ?></td>
                <td class="text-right">₱<?= number_format($i['vatable'],2) ?></td>
                <td class="text-right">₱<?= number_format($i['vat'],2) ?></td>
                <td class="text-right" style="color:<?= $i['profit']<0?'#dc3545':'#28a745' ?>">
                    ₱<?= number_format($i['profit'],2) ?>
                </td>
                <td><?= htmlspecialchars($i['payment_method'] ?? '—') ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row"><span>Total Sales:</span><span>₱<?= number_format($totalSales,2) ?></span></div>
        <div class="totals-row"><span>Total Vatable:</span><span>₱<?= number_format($totalVatable,2) ?></span></div>
        <div class="totals-row"><span>Total 12% VAT:</span><span>₱<?= number_format($totalVat,2) ?></span></div>
        <div class="totals-row"><span>Total Profit:</span><span>₱<?= number_format($totalProfit,2) ?></span></div>
    </div>
</div>

</body>
</html>
