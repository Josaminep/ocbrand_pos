<?php
include '../db.php';
session_start();

/* ---------- LOGGED-IN USER INFO ---------- */
$loggedUser = $_SESSION['user_id'] ?? 0;
$userRole   = $_SESSION['role'] ?? '';

/* ---------- DATE RANGE (TODAY BY DEFAULT) ---------- */
$today = date('Y-m-d');
$from  = $_GET['from'] ?? $today;
$to    = $_GET['to']   ?? $today;

$fromDate = $from . " 00:00:00";
$toDate   = $to . " 23:59:59";

/* ---------- WHERE CONDITION ---------- */
$where = "s.created_at BETWEEN '$fromDate' AND '$toDate'";

// Restrict to cashier if role is cashier
if ($userRole === 'cashier' && $loggedUser > 0) {
    $where .= " AND s.user = $loggedUser";
}

/* ---------- TOTAL SALES ---------- */
$q = $conn->query("
    SELECT 
        SUM(total) AS total_sales,
        COUNT(*) AS total_transactions
    FROM sales s
    WHERE $where
");
$data = $q->fetch_assoc();
$totalSales        = $data['total_sales'] ?? 0;
$totalTransactions = $data['total_transactions'] ?? 0;

/* ---------- TOP SELLING ITEM ---------- */
$topItem = "N/A";
$topQty  = 0;

$q = $conn->query("
    SELECT si.product_name, SUM(si.quantity) AS qty
    FROM sales_items si
    JOIN sales s ON s.id = si.sale_id
    WHERE $where
    GROUP BY si.product_id
    ORDER BY qty DESC
    LIMIT 1
");
if ($row = $q->fetch_assoc()) {
    $topItem = $row['product_name'];
    $topQty  = $row['qty'];
}

/* ---------- SALES CHART (PER DAY) ---------- */
$chartLabels = [];
$chartData   = [];

$q = $conn->query("
    SELECT DATE(created_at) as sale_date, SUM(total) as total
    FROM sales s
    WHERE $where
    GROUP BY sale_date
    ORDER BY sale_date
");
while ($r = $q->fetch_assoc()) {
    $chartLabels[] = $r['sale_date'];
    $chartData[]   = $r['total'];
}

/* ---------- SALES PER CASHIER / USER ---------- */
$admins = [];
if ($userRole !== 'cashier') {
    // Show all users for admin
    $q = $conn->query("
        SELECT 
            u.fname AS cashier_name,
            u.role,
            SUM(s.total) AS total_sales
        FROM sales s
        JOIN accounts u ON u.id = s.user
        WHERE s.created_at BETWEEN '$fromDate' AND '$toDate'
        GROUP BY s.user
    ");
    while ($r = $q->fetch_assoc()) {
        $admins[] = $r;
    }
} else {
    // Only self for cashier
    $q = $conn->query("
        SELECT 
            u.fname AS cashier_name,
            u.role,
            SUM(s.total) AS total_sales
        FROM sales s
        JOIN accounts u ON u.id = s.user
        WHERE s.user = $loggedUser AND s.created_at BETWEEN '$fromDate' AND '$toDate'
        GROUP BY s.user
    ");
    if ($r = $q->fetch_assoc()) {
        $admins[] = $r;
    }
}

/* ---------- PAGE INFO ---------- */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle   = ucfirst(str_replace('_', ' ', $currentPage));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports - OC Brand</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins",sans-serif; }
body { background:#f4f6f9; font-family:Poppins, sans-serif; }
.main-content { margin-left: 250px; padding:40px; }
.filter-bar { display:flex; gap:12px; margin-bottom:30px; }
.filter-bar input, .filter-bar button { padding:10px 14px; border-radius:8px; border:1px solid #ccc; }
.filter-bar button { background:#111; color:#fff; cursor:pointer; }
.report-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:30px; }
.report-card { background:#fff; padding:24px; border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,0.08); text-align:center; }
.report-card i { font-size:36px; color:#d4af37; }
.report-card h3 { margin:10px 0; }
.value { font-size:24px; font-weight:bold; }
.section { background:#fff; padding:24px; border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,0.08); margin-bottom:30px; }
table { width:100%; border-collapse:collapse; }
th, td { padding:12px; border-bottom:1px solid #eee; text-align:left; }
@media print {
    body { background:#fff; }
    .filter-bar, .sidebar { display:none !important; }
    .main-content { margin:0; padding:20px; }
    .section, .report-card { box-shadow:none; border:1px solid #ddd; }
}
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="main-content">

<!-- DATE FILTER -->
<form class="filter-bar" method="GET" action="reports.php">
    <!--<input type="date" name="from" value="<?= $from ?>" required>
    <input type="date" name="to" value="<?= $to ?>" required>
    <button type="submit"><i class="fas fa-filter"></i> Filter</button>-->

    <button type="button" onclick="window.location.href='functions/sales_report.php?from=<?= $from ?>&to=<?= $to ?>'">
        <i class="fas fa-file-pdf"></i> Generate Report
    </button>
</form>

<div class="report-header" style="margin-bottom:20px;">
    <h2><i class="fas fa-calendar-day"></i> Your Summary — <?= date("F j, Y") ?></h2>
</div>

<!-- SUMMARY -->
<div class="report-grid">

    <div class="report-card">
        <i class="fas fa-peso-sign"></i>
        <h3>Total Sales</h3>
        <div class="value">₱<?= number_format($totalSales,2) ?></div>
    </div>

    <div class="report-card">
        <i class="fas fa-receipt"></i>
        <h3>Transactions</h3>
        <div class="value"><?= number_format($totalTransactions) ?></div>
    </div>

    <div class="report-card">
        <i class="fas fa-box"></i>
        <h3>Top Item</h3>
        <div class="value"><?= htmlspecialchars($topItem) ?></div>
        <small><?= $topQty ?> sold</small>
    </div>

</div>

<!-- SALES CHART -->
<div class="section">
    <h3>📊 Sales Chart</h3>
    <canvas id="salesChart"></canvas>
</div>

<!-- Uncomment this section if you want to show per-user totals -->
<!--
<div class="section">
<h3>👤 Sales per User</h3>
<table>
<tr><th>Name</th><th>Role</th><th>Total Sales</th></tr>
<?php foreach ($admins as $a): ?>
<tr>
<td><?= htmlspecialchars($a['cashier_name']) ?></td>
<td><?= ucfirst($a['role']) ?></td>
<td>₱<?= number_format($a['total_sales'], 2) ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
-->

</div>

<script>
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Sales',
            data: <?= json_encode($chartData) ?>,
            borderWidth: 3,
            fill: true,
            backgroundColor: 'rgba(52, 152, 219,0.2)',
            borderColor: 'rgba(52, 152, 219,1)',
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});
</script>

</body>
</html>
