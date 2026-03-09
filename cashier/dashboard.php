<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require "../db.php";

/* ---------- AUTH ---------- */
if (empty($_SESSION['user_id'])) {
    header("Location: ../home.php");
    exit;
}

/* ---------- USER ROLE ---------- */
$stmt = $conn->prepare("SELECT role FROM accounts WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$_SESSION['role'] = $user['role'] ?? '';

$userId  = (int)$_SESSION['user_id'];
$role    = $_SESSION['role'] ?? '';
$isAdmin = ($role === 'admin'); // change if your admin role name is different

/* ---------- TODAY RANGE ---------- */
$todayStart = date('Y-m-d 00:00:00');
$todayEnd   = date('Y-m-d 23:59:59');

/* ---------- DASHBOARD METRICS ---------- */

/* Today Sales (Admin = all, Sales = own) */
if ($isAdmin) {
    $stmt = $conn->prepare("
        SELECT 
            SUM(total) AS sales,
            COUNT(*) AS transactions
        FROM sales
        WHERE created_at BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $todayStart, $todayEnd);
} else {
    $stmt = $conn->prepare("
        SELECT 
            SUM(total) AS sales,
            COUNT(*) AS transactions
        FROM sales
        WHERE `user` = ?
          AND created_at BETWEEN ? AND ?
    ");
    $stmt->bind_param("iss", $userId, $todayStart, $todayEnd);
}
$stmt->execute();
$today = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* Items Sold Today (Admin = all, Sales = own) */
if ($isAdmin) {
    $stmt = $conn->prepare("
        SELECT SUM(si.quantity) AS qty
        FROM sales_items si
        JOIN sales s ON s.id = si.sale_id
        WHERE s.created_at BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $todayStart, $todayEnd);
} else {
    $stmt = $conn->prepare("
        SELECT SUM(si.quantity) AS qty
        FROM sales_items si
        JOIN sales s ON s.id = si.sale_id
        WHERE s.`user` = ?
          AND s.created_at BETWEEN ? AND ?
    ");
    $stmt->bind_param("iss", $userId, $todayStart, $todayEnd);
}
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$itemsSold = $row['qty'] ?? 0;

/* Low Stock Count (global) */
$q = $conn->query("
    SELECT COUNT(*) AS low_stock
    FROM products
    WHERE quantity <= 9
");
$lowStockCount = $q->fetch_assoc()['low_stock'];

/* ---------- RECENT TRANSACTIONS (Admin = all, Sales = own) ---------- */
$recentSales = [];

if ($isAdmin) {
    $stmt = $conn->prepare("
        SELECT 
            invoice_no,
            total,
            customer_name,
            created_at
        FROM sales
        ORDER BY created_at DESC
        LIMIT 20
    ");
} else {
    $stmt = $conn->prepare("
        SELECT 
            invoice_no,
            total,
            customer_name,
            created_at
        FROM sales
        WHERE `user` = ?
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->bind_param("i", $userId);
}

$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $recentSales[] = $r;
}
$stmt->close();

/* ---------- LOW STOCK ITEMS (global) ---------- */
$lowStockItems = [];
$q = $conn->query("
    SELECT name, quantity, category
    FROM products
    WHERE quantity <= 9
    ORDER BY quantity ASC
");
while ($r = $q->fetch_assoc()) {
    $lowStockItems[] = $r;
}

/* Page info */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle   = ucfirst(str_replace('_', ' ', $currentPage));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - OC Brand</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins,sans-serif}
body{display:flex;background:#f4f6f9;min-height:100vh}
.main-content{flex:1;padding:40px;margin-left:250px}

.dashboard-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:35px}
.dashboard-header h1{font-size:34px;font-weight:700}

.datetime-box{text-align:right}
.datetime-box .time{font-size:26px;font-weight:700}

.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:25px;margin-bottom:40px}
.card{background:#fff;padding:25px;border-radius:18px;box-shadow:0 6px 20px rgba(0,0,0,.08)}
.card i{float:right;font-size:40px;color:#d4af37}
.card h3{font-size:17px;color:#555}
.value{font-size:32px;font-weight:700;margin-top:10px}

.section-title{font-size:22px;margin:25px 0 15px}

.table-wrapper{max-height:280px;overflow-y:auto;background:#fff;border-radius:14px;box-shadow:0 6px 20px rgba(0,0,0,.08)}
table{width:100%;border-collapse:collapse}
thead th{position:sticky;top:0;background:#1a1a1a;color:#d4af37;padding:14px}
td{padding:14px;border-bottom:1px solid #eee;font-size:15px}
tr:hover{background:#f5f5f5}

.card-link{text-decoration:none;color:inherit;display:block}
.card-link .card:hover{transform:translateY(-5px);box-shadow:0 10px 28px rgba(0,0,0,.14)}

.toast{position:fixed;top:30px;right:30px;padding:14px 22px;border-radius:14px;font-size:14px;font-weight:600;color:#fff;
box-shadow:0 10px 25px rgba(0,0,0,.25);animation:slideIn .4s ease,fadeOut .4s ease 2.6s forwards;z-index:9999}
.toast.success{background:linear-gradient(135deg,#22c55e,#16a34a)}
.toast.error{background:linear-gradient(135deg,#ef4444,#b91c1c)}
@keyframes slideIn{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}
@keyframes fadeOut{to{opacity:0;transform:translateX(120%)}}

/* Table header and body alignment */
table th, table td { text-align:left; }
table th:nth-child(2), table td:nth-child(2) { text-align:right; }
table th:nth-child(4), table td:nth-child(4) { text-align:center; }
</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="main-content">

<div class="dashboard-header">
    <h1>Dashboard</h1>
    <div class="datetime-box">
        <div class="time" id="realTime">--:--:--</div>
        <div id="realDate">Loading date...</div>
    </div>
</div>

<div class="cards">
    <a href="reports.php" class="card-link">
        <div class="card">
            <i class="fas fa-peso-sign"></i>
            <h3>Today's Sales</h3>
            <div class="value">₱<?= number_format($today['sales'] ?? 0, 2) ?></div>
        </div>
    </a>

    <a href="reports.php" class="card-link">
        <div class="card">
            <i class="fas fa-receipt"></i>
            <h3>Transactions</h3>
            <div class="value"><?= $today['transactions'] ?? 0 ?></div>
        </div>
    </a>

    <a href="reports.php" class="card-link">
        <div class="card">
            <i class="fas fa-shopping-basket"></i>
            <h3>Items Sold Today</h3>
            <div class="value"><?= $itemsSold ?></div>
        </div>
    </a>

    <a href="inventory.php" class="card-link">
        <div class="card">
            <i class="fas fa-box-open"></i>
            <h3>Low Stock Items</h3>
            <div class="value"><?= $lowStockCount ?></div>
        </div>
    </a>
</div>

<h2 class="section-title">Recent Transactions</h2>
<div class="table-wrapper">
<table>
<thead>
<tr>
    <th>Invoice No</th>
    <th>Total</th>
    <th>Customer Name</th>
    <th>Date & Time</th>
</tr>
</thead>
<tbody>
<?php if (empty($recentSales)): ?>
<tr><td colspan="4" style="text-align:center">No data</td></tr>
<?php else: foreach ($recentSales as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['invoice_no']) ?></td>
    <td>₱<?= number_format($r['total'], 2) ?></td>
    <td><?= htmlspecialchars($r['customer_name'] ?: 'Walk-in') ?></td>
    <td><?= date('M d, Y h:i A', strtotime($r['created_at'])) ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<h2 class="section-title">Low Stock Items</h2>
<div class="table-wrapper">
<table>
<thead>
<tr>
    <th>Item</th>
    <th>Stock</th>
    <th>Category</th>
</tr>
</thead>
<tbody>
<?php if (empty($lowStockItems)): ?>
<tr><td colspan="3" style="text-align:center">All stocks healthy 🎉</td></tr>
<?php else: foreach ($lowStockItems as $p): ?>
<tr>
    <td><?= htmlspecialchars($p['name']) ?></td>
    <td><?= $p['quantity'] ?></td>
    <td><?= htmlspecialchars($p['category']) ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

</div>

<script>
function updateClock(){
    const now=new Date();
    document.getElementById("realTime").textContent=now.toLocaleTimeString('en-US');
    document.getElementById("realDate").textContent=now.toLocaleDateString('en-US',{
        weekday:"long",year:"numeric",month:"long",day:"numeric"
    });
}
setInterval(updateClock,1000);
updateClock();
</script>

<?php if(isset($_SESSION['toast'])): ?>
<div class="toast <?= $_SESSION['toast']['type'] ?>">
    <?= htmlspecialchars($_SESSION['toast']['msg']) ?>
</div>
<?php unset($_SESSION['toast']); endif; ?>

</body>
</html>