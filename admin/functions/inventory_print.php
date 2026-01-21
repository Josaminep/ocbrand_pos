<?php
include "../../db.php";

/* SAME FILTERS */
$category = $_GET['category'] ?? '';
$search   = $_GET['search'] ?? '';
$lowStock = $_GET['low_stock'] ?? '';

$sql = "SELECT * FROM products WHERE 1";

if ($category) $sql .= " AND category='$category'";
if ($search)   $sql .= " AND (name LIKE '%$search%' OR brand LIKE '%$search%')";
if ($lowStock) $sql .= " AND quantity < 10";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Inventory Report</title>
<style>
@media print {
    @page { size: A4; margin: 15mm; }
}
body { font-family: Arial; font-size: 12px; }
table { width:100%; border-collapse: collapse; }
th, td { border:1px solid #000; padding:6px; text-align:center; }
th { background:#eee; }
</style>
</head>

<body onload="window.print()">

<h2 style="text-align:center">INVENTORY REPORT</h2>

<table>
<tr>
    <th>Brand</th>
    <th>Name</th>
    <th>Category</th>
    <th>Price</th>
    <th>Stock</th>
</tr>

<?php while($r = mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?= htmlspecialchars($r['brand']) ?></td>
    <td><?= htmlspecialchars($r['name']) ?></td>
    <td><?= htmlspecialchars($r['category']) ?></td>
    <td>₱<?= number_format($r['price'],2) ?></td>
    <td><?= $r['quantity'] ?></td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>
