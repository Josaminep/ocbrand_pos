<?php
include "../../db.php";

$category = $_GET['category'] ?? '';
$search   = $_GET['search'] ?? '';

$sql = "SELECT * FROM products WHERE 1";

if (!empty($category)) {
    $category = mysqli_real_escape_string($conn, $category);
    $sql .= " AND category = '$category'";
}

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (name LIKE '%$search%' OR brand LIKE '%$search%')";
}

$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

// Output table rows only
while ($row = mysqli_fetch_assoc($result)) {
    $image = !empty($row['image']) ? $row['image'] : 'uploads/no-image.png';
    $lowStock = ($row['quantity'] < 10) ? 'low-stock' : '';
    echo "<tr class='$lowStock'>";
    echo "<td><img src='../" . htmlspecialchars($image) . "' alt='Product Image'></td>";
    echo "<td>" . htmlspecialchars($row['brand'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
    echo "<td>₱" . number_format((float)$row['srp'], 2) . "</td>";
    echo "<td><strong>₱" . number_format((float)$row['price'], 2) . "</strong></td>";
    echo "<td>" . (int)$row['quantity'] . (($row['quantity'] < 10) ? '<span class="stock-badge">LOW</span>' : '') . "</td>";
    echo "<td>
        <button class='edit-btn' onclick=\"openEdit({$row['id']}, '".addslashes($row['name'])."', '".addslashes($row['category'])."', {$row['srp']}, {$row['price']}, {$row['quantity']})\">
            <i class='fa fa-edit'></i>
        </button>
        <form action='functions/update_product.php' method='POST' style='display:inline'>
            <input type='hidden' name='delete_id' value='{$row['id']}'>
            <button class='delete-btn' onclick='return confirm(\"Delete this product?\")'>
                <i class='fa fa-trash'></i>
            </button>
        </form>
    </td>";
    echo "</tr>";
}
