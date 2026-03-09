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
   CASHIER / SALES PERSON FILTER
============================ */
$cashier = $_GET['cashier'] ?? '';

$isCashier  = ($_SESSION['role'] ?? '') === 'cashier';
$loggedUser = (int)($_SESSION['user_id'] ?? 0);

/* ============================
   BUILD WHERE CONDITION
============================ */
$where = "s.created_at BETWEEN '$fromDate' AND '$toDate'";

/* Cashier: only their own sales */
if ($isCashier && $loggedUser > 0) {
    $where .= " AND s.`user` = $loggedUser";
}

/* Admin-selected salesperson */
if (!$isCashier && $cashier !== '') {
    $cashierId = (int)$cashier;
    $where .= " AND s.`user` = $cashierId";
}

/* ============================
   GET SALES PERSON LABEL
============================ */
$salesPersonLabel = 'All Sales Person';

if ($isCashier && $loggedUser > 0) {
    $uq = $conn->query("
        SELECT CONCAT(fname, ' ', lname) AS fullname
        FROM accounts
        WHERE id = $loggedUser
        LIMIT 1
    ");
    if ($uq && $uq->num_rows > 0) {
        $u = $uq->fetch_assoc();
        $salesPersonLabel = $u['fullname'];
    }
} elseif (!$isCashier && $cashier !== '') {
    $cashierId = (int)$cashier;
    $uq = $conn->query("
        SELECT CONCAT(fname, ' ', lname) AS fullname
        FROM accounts
        WHERE id = $cashierId
        LIMIT 1
    ");
    if ($uq && $uq->num_rows > 0) {
        $u = $uq->fetch_assoc();
        $salesPersonLabel = $u['fullname'];
    }
}

/* ============================
   FETCH SALES ITEMS
============================ */
$items = [];

$q = $conn->query("
    SELECT 
        si.sale_id,
        s.invoice_no,
        s.created_at,
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
        s.payment_method,
        CONCAT(a.fname, ' ', a.lname) AS salesperson
    FROM sales_items si
    INNER JOIN sales s ON s.id = si.sale_id
    LEFT JOIN products p ON p.id = si.product_id
    LEFT JOIN accounts a ON a.id = s.`user`
    WHERE $where
    ORDER BY s.created_at DESC, si.id ASC
");

if ($q) {
    while ($row = $q->fetch_assoc()) {
        $items[] = $row;
    }
}

/* ============================
   TOTALS
============================ */
$totalSales   = array_sum(array_column($items, 'subtotal'));
$totalVatable = array_sum(array_column($items, 'vatable'));
$totalVat     = array_sum(array_column($items, 'vat'));
$totalProfit  = array_sum(array_column($items, 'profit'));

/* ============================
   EXCEL HEADERS
============================ */
$filename = 'Sales_Items_Report_' . $from . '_to_' . $to . '.xls';

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF"; // UTF-8 BOM
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Items Report Excel</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
        }
        th {
            background: #d9ead3;
            font-weight: bold;
            text-align: center;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .meta {
            font-weight: bold;
            text-align: left;
            background: #f3f3f3;
        }
        .total-label {
            font-weight: bold;
            text-align: right;
            background: #fff2cc;
        }
        .total-value {
            font-weight: bold;
            background: #fff2cc;
        }
    </style>
</head>
<body>

<table>
    <tr>
        <th colspan="14" class="title">Sales Items Report</th>
    </tr>
    <tr>
        <td colspan="14" class="meta">Date From: <?= htmlspecialchars($from) ?></td>
    </tr>
    <tr>
        <td colspan="14" class="meta">Date To: <?= htmlspecialchars($to) ?></td>
    </tr>
    <tr>
        <td colspan="14" class="meta">Sales Person: <?= htmlspecialchars($salesPersonLabel) ?></td>
    </tr>
    <tr>
        <td colspan="14">&nbsp;</td>
    </tr>

    <tr>
        <th>Date</th>
        <th>Invoice No</th>
        <th>Sales Person</th>
        <th>SKU ID</th>
        <th>Item Name</th>
        <th>Category</th>
        <th>Price</th>
        <th>SRP</th>
        <th>QTY</th>
        <th>Total</th>
        <th>Vatable</th>
        <th>12% VAT</th>
        <th>Profit</th>
        <th>Payment Method</th>
    </tr>

    <?php if (empty($items)): ?>
        <tr>
            <td colspan="14" class="text-center">No records found</td>
        </tr>
    <?php else: ?>
        <?php foreach ($items as $i): ?>
            <tr>
                <td><?= htmlspecialchars(date('Y-m-d', strtotime($i['created_at']))) ?></td>
                <td><?= htmlspecialchars($i['invoice_no']) ?></td>
                <td><?= htmlspecialchars($i['salesperson'] ?? '—') ?></td>
                <td><?= htmlspecialchars($i['product_code']) ?></td>
                <td><?= htmlspecialchars($i['product_name']) ?></td>
                <td><?= htmlspecialchars($i['category'] ?? '—') ?></td>
                <td class="text-right"><?= number_format((float)$i['price'], 2) ?></td>
                <td class="text-right"><?= number_format((float)$i['srp'], 2) ?></td>
                <td class="text-center"><?= (int)$i['quantity'] ?></td>
                <td class="text-right"><?= number_format((float)$i['subtotal'], 2) ?></td>
                <td class="text-right"><?= number_format((float)$i['vatable'], 2) ?></td>
                <td class="text-right"><?= number_format((float)$i['vat'], 2) ?></td>
                <td class="text-right"><?= number_format((float)$i['profit'], 2) ?></td>
                <td><?= htmlspecialchars($i['payment_method'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    <tr>
        <td colspan="14">&nbsp;</td>
    </tr>

    <tr>
        <td colspan="9" class="total-label">Total Sales</td>
        <td class="total-value text-right"><?= number_format((float)$totalSales, 2) ?></td>
        <td colspan="4"></td>
    </tr>
    <tr>
        <td colspan="10" class="total-label">Total Vatable</td>
        <td class="total-value text-right"><?= number_format((float)$totalVatable, 2) ?></td>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td colspan="11" class="total-label">Total 12% VAT</td>
        <td class="total-value text-right"><?= number_format((float)$totalVat, 2) ?></td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td colspan="12" class="total-label">Total Profit</td>
        <td class="total-value text-right"><?= number_format((float)$totalProfit, 2) ?></td>
        <td></td>
    </tr>
</table>

</body>
</html>