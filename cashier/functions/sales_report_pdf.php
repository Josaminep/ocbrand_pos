<?php
require '../../vendor/autoload.php';
use Dompdf\Dompdf;

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

/* ============================
   HTML (PDF CONTENT)
============================ */
$html = '
<style>
@page {
    margin: 40px;
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    color: #000;
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
    font-size: 11px;
    margin-top: 4px;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th, td {
    border: 1px solid #333;
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
    bottom: 20px;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 10px;
    color: #555;
}
</style>

<div class="header">
    <h1>SALES REPORT</h1>
    <p>Report from <strong>'.$from.'</strong> to <strong>'.$to.'</strong></p>
</div>

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
';

foreach ($sales as $s) {
    $html .= '
    <tr>
        <td>'.date('Y-m-d', strtotime($s['created_at'])).'</td>
        <td>'.$s['invoice_no'].'</td>
        <td>'.($s['customer_name'] ?: 'Walk-in').'</td>
        <td class="amount">₱'.number_format($s['total'], 2).'</td>
        <td class="amount">₱'.number_format($s['profit'], 2).'</td>
    </tr>
    ';
}

$html .= '
</tbody>
</table>

<table class="summary">
<tr>
    <td class="label">TOTAL SALES:</td>
    <td class="amount">₱'.number_format($totalSales, 2).'</td>
</tr>
<tr>
    <td class="label">TOTAL PROFIT:</td>
    <td class="amount">₱'.number_format($totalProfit, 2).'</td>
</tr>
</table>

<div class="footer">
    Generated on '.date('F d, Y h:i A').'
</div>
';

/* ============================
   GENERATE PDF
============================ */
$dompdf = new Dompdf([
    'defaultFont' => 'DejaVu Sans'
]);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "Sales_Report_{$from}_to_{$to}.pdf";
$dompdf->stream($filename, ['Attachment' => false]);
