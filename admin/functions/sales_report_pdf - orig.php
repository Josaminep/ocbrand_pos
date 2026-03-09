<?php
include '../../db.php';
session_start();

/* ============================
   DATE RANGE
============================ */
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');

$fromDate = $from . ' 00:00:00';
$toDate   = $to   . ' 23:59:59';

/* ============================
   FETCH SALES + PROFIT
============================ */
$sales = [];

$sql = "
    SELECT
        s.invoice_no,
        s.total,
        s.customer_name,
        s.created_at,
        COALESCE(SUM(si.profit), 0) AS profit
    FROM sales s
    LEFT JOIN sales_items si ON si.sale_id = s.id
    WHERE s.created_at BETWEEN '$fromDate' AND '$toDate'
    GROUP BY s.id
    ORDER BY s.created_at ASC
";

$q = $conn->query($sql);

while ($row = $q->fetch_assoc()) {
    $sales[] = $row;
}

$totalSales  = array_sum(array_column($sales, 'total'));
$totalProfit = array_sum(array_column($sales, 'profit'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Report</title>

<style>
/* ============================
   PRINT SETTINGS
============================ */
@media print {
    @page {
        size: A4;
        margin: 20mm;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 11px;
        color: #000;
    }

    .no-print {
        display: none !important;
    }
}

body {
    margin: 0;
    background: #fff;
    font-family: Arial, sans-serif;
    font-size: 11px;
}

/* HEADER */
.header {
    text-align: center;
    margin-bottom: 20px;
}

.header h1 {
    font-size: 18px;
    margin: 0;
    letter-spacing: 1px;
}

.header p {
    margin-top: 5px;
    font-size: 11px;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th, td {
    border: 1px solid #000;
    padding: 6px;
}

th {
    background: #f2f2f2;
    font-weight: bold;
}

.amount {
    text-align: right;
}

/* SUMMARY */
.summary {
    margin-top: 20px;
    width: 100%;
}

.summary td {
    border: none;
    padding: 4px 0;
    font-size: 12px;
}

.summary .label {
    text-align: right;
    font-weight: bold;
    padding-right: 10px;
}

/* FOOTER */
.footer {
    position: fixed;
    bottom: 15mm;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 10px;
    color: #555;
}
</style>
</head>

<body>

<!-- PRINT BUTTON -->
<div class="no-print" style="text-align:right;margin-bottom:10px">
    <button onclick="window.print()">Print / Save as PDF</button>
</div>

<!-- HEADER -->
<div class="header">
    <h1>SALES REPORT</h1>
    <p>Report from <strong><?= $from ?></strong> to <strong><?= $to ?></strong></p>
</div>

<!-- TABLE -->
<table>
<thead>
<tr>
    <th width="18%">Date</th>
    <th width="20%">Invoice No</th>
    <th width="26%">Customer</th>
    <th width="18%">Total</th>
    <th width="18%">Profit</th>
</tr>
</thead>
<tbody>
<?php foreach ($sales as $s): ?>
<tr>
    <td><?= date('Y-m-d', strtotime($s['created_at'])) ?></td>
    <td><?= htmlspecialchars($s['invoice_no']) ?></td>
    <td><?= htmlspecialchars($s['customer_name'] ?: 'Walk-in') ?></td>
    <td class="amount">₱<?= number_format($s['total'], 2) ?></td>
    <td class="amount">₱<?= number_format($s['profit'], 2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<!-- SUMMARY -->
<table class="summary">
<tr>
    <td class="label">TOTAL SALES:</td>
    <td class="amount">₱<?= number_format($totalSales, 2) ?></td>
</tr>
<tr>
    <td class="label">TOTAL PROFIT:</td>
    <td class="amount">₱<?= number_format($totalProfit, 2) ?></td>
</tr>
</table>

<!-- FOOTER -->
<div class="footer">
    Generated on <?= date('F d, Y h:i A') ?>
</div>

<script>
/* AUTO PRINT ON LOAD (optional) */
window.onload = function () {
    window.print();
};
</script>

</body>
</html>
