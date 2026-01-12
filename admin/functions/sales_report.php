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

$q = $conn->query("
    SELECT
        s.id,
        s.invoice_no,
        s.total,
        s.customer_name,
        s.created_at,
        COALESCE(SUM(si.profit),0) AS profit
    FROM sales s
    LEFT JOIN sales_items si ON si.sale_id = s.id
    WHERE s.created_at BETWEEN '$fromDate' AND '$toDate'
    GROUP BY s.id
    ORDER BY s.created_at DESC
");

while ($row = $q->fetch_assoc()) {
    $sales[] = $row;
}

/* ============================
   TOTALS
============================ */
$totalSales        = array_sum(array_column($sales, 'total'));
$totalProfit       = array_sum(array_column($sales, 'profit'));
$totalTransactions = count($sales);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Report</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
}

body {
    background: #f4f6f9;
}

.main-content {
    padding: 40px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 14px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 600;
}

.btn-back { background: #e5e5e5; }
.btn-print { background: #111; color: #fff; }

.filter-bar {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.filter-bar input {
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.filter-bar button {
    background: #111;
    color: #fff;
}

.card {
    background: #fff;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}

/* ===== SUMMARY ===== */
.summary {
    display: flex;
    text-align: center;
}

.summary div {
    flex: 1;
}

.summary h2 {
    margin-top: 6px;
}

.sales { color: #d4af37; }
.profit { color: #28a745; }
.count  { color: #111; }

/* ===== TABLE ===== */
.table-wrapper {
    max-height: 480px;
    overflow-y: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th, td {
    padding: 14px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

th {
    background: #fafafa;
    position: sticky;
    top: 0;
}

tfoot td {
    font-weight: bold;
    background: #fafafa;
    border-top: 2px solid #000;
}

/* PRINT */
@media print {
    .filter-bar, .actions, .sidebar {
        display: none;
    }
    .main-content {
        margin: 0;
        padding: 20px;
    }
}
</style>
</head>

<body>


<div class="main-content">

<!-- HEADER -->
<div class="header">
    <h1><i class="fas fa-file-alt"></i> Sales Report</h1>
    <div class="actions">
<a href="../reports.php" class="btn btn-back" style="text-decoration: none;">
    <i class="fas fa-arrow-left"></i> Back
</a>

<a 
    href="sales_report_pdf.php?from=<?= $from ?>&to=<?= $to ?>" 
    target="_blank" 
    class="btn btn-print" 
    style="text-decoration: none;">
    <i class="fas fa-print"></i> Print PDF
</a>


    </div>
</div>

<!-- FILTER -->
<form class="filter-bar" method="GET">
    <input type="date" name="from" value="<?= $from ?>" required>
    <input type="date" name="to" value="<?= $to ?>" required>
    <button class="btn">Filter</button>
</form>

<!-- SUMMARY -->
<div class="card summary">
    <div>
        <small>Total Sales</small>
        <h2 class="sales">₱<?= number_format($totalSales,2) ?></h2>
    </div>
    <div>
        <small>Total Profit</small>
        <h2 class="profit">₱<?= number_format($totalProfit,2) ?></h2>
    </div>
    <div>
        <small>Transactions</small>
        <h2 class="count"><?= $totalTransactions ?></h2>
    </div>
</div>

<!-- TABLE -->
<div class="card">
    <h3>🧾 Sales Transactions</h3>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Total Sale</th>
                    <th>Profit</th>
                </tr>
            </thead>

            <tbody>
            <?php if (!$sales): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">No records found</td>
                </tr>
            <?php else: foreach ($sales as $s): ?>
                <tr>
                    <td><?= date('Y-m-d H:i', strtotime($s['created_at'])) ?></td>
                    <td><strong><?= htmlspecialchars($s['invoice_no']) ?></strong></td>
                    <td><?= $s['customer_name'] ?: 'Walk-in' ?></td>
                    <td>₱<?= number_format($s['total'],2) ?></td>
                    <td style="color:<?= $s['profit'] < 0 ? '#dc3545' : '#28a745' ?>">
                        ₱<?= number_format($s['profit'],2) ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="3">TOTAL</td>
                    <td>₱<?= number_format($totalSales,2) ?></td>
                    <td>₱<?= number_format($totalProfit,2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

</div>
</body>
</html>
