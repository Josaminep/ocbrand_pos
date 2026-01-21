<?php
include "../../db.php";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=inventory_report.xls");
header("Cache-Control: max-age=0");

$category = $_GET['category'] ?? '';
$search   = $_GET['search'] ?? '';
$lowStock = $_GET['low_stock'] ?? '';

$sql = "SELECT brand, name, category, srp, price, quantity FROM products WHERE 1";

if ($category) $sql .= " AND category='".mysqli_real_escape_string($conn,$category)."'";
if ($search)   $sql .= " AND (name LIKE '%$search%' OR brand LIKE '%$search%')";
if ($lowStock) $sql .= " AND quantity < 10";

$result = mysqli_query($conn, $sql);
?>

<html>
<head>
<meta charset="UTF-8">
<style>
body {
    font-family: Arial, sans-serif;
    font-size: 11px;
}

table {
    border-collapse: collapse;
    width: 100%;
}

th {
    background: #e5e7eb;
    font-weight: bold;
    border: 1px solid #000;
    padding: 6px;
    text-align: center;
}

td {
    border: 1px solid #000;
    padding: 5px;
}

.currency {
    text-align: right;
    mso-number-format:"₱#,##0.00";
}

.low {
    background-color: #f8d7da;
}
</style>
</head>
<body>

<h2 style="text-align:center;">INVENTORY REPORT</h2>

<table>
    <tr>
        <th>Brand</th>
        <th>Product Name</th>
        <th>Category</th>
        <th>Price</th>
        <th>SRP</th>
        <th>Stock</th>
    </tr>

<?php
$totalStock = 0;
while ($r = mysqli_fetch_assoc($result)) {
    $lowClass = ($r['quantity'] < 10) ? 'low' : '';
    $totalStock += $r['quantity'];
    echo "
    <tr class='{$lowClass}'>
        <td>{$r['brand']}</td>
        <td>{$r['name']}</td>
        <td>{$r['category']}</td>
        <td class='currency'>{$r['price']}</td>
        <td class='currency'>{$r['srp']}</td>
        <td style='text-align:center'>{$r['quantity']}</td>
    </tr>";
}
?>

<tr>
    <td colspan="5" style="font-weight:bold;text-align:right;">TOTAL STOCK</td>
    <td style="font-weight:bold;text-align:center;"><?php echo $totalStock; ?></td>
</tr>

</table>

</body>
</html>