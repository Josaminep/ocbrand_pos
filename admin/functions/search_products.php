<?php
include "../../db.php";

$category    = $_GET['category'] ?? '';
$search      = $_GET['search'] ?? '';
$stockStatus = $_GET['stock_status'] ?? '';

$sql = "SELECT * FROM products WHERE 1";

if (!empty($category)) {
    $category = mysqli_real_escape_string($conn, $category);
    $sql .= " AND category = '$category'";
}

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (
        name LIKE '%$search%'
        OR brand LIKE '%$search%'
        OR product_code LIKE '%$search%'
    )";
}

if (!empty($stockStatus)) {
    if ($stockStatus === 'low') {
        $sql .= " AND quantity > 0 AND quantity < 10";
    } elseif ($stockStatus === 'out') {
        $sql .= " AND quantity = 0";
    } elseif ($stockStatus === 'in') {
        $sql .= " AND quantity >= 10";
    }
}

$sql .= " ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $image = !empty($row['image']) ? $row['image'] : 'uploads/no-image.png';
    $qty   = (int)$row['quantity'];

    $rowClass = '';
    if ($qty === 0) {
        $rowClass = 'out-stock';
    } elseif ($qty > 0 && $qty < 10) {
        $rowClass = 'low-stock';
    }

    echo "<tr class='" . $rowClass . "'>";
    echo "<td><img src='../" . htmlspecialchars($image) . "' alt='Product Image'></td>";
    echo "<td>" . htmlspecialchars($row['product_code'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['brand'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
    echo "<td>₱" . number_format((float)$row['srp'], 2) . "</td>";
    echo "<td><strong>₱" . number_format((float)$row['price'], 2) . "</strong></td>";

    echo "<td class='stock-cell'>";
    if ($qty === 0) {
        echo "<span class='stock-count zero'>0</span>";
        echo "<span class='stock-badge out'><i class='fa-solid fa-circle-xmark'></i> Out of Stock</span>";
    } elseif ($qty < 10) {
        echo $qty . " <span class='stock-badge low'><i class='fa-solid fa-triangle-exclamation'></i> Low Stock</span>";
    } else {
        echo $qty;
    }
    echo "</td>";

    echo "<td>
        <button class='edit-btn' onclick=\"openEdit(
            " . (int)$row['id'] . ",
            '" . addslashes($row['name']) . "',
            '" . addslashes($row['category']) . "',
            " . (float)$row['srp'] . ",
            " . (float)$row['price'] . ",
            " . (int)$row['quantity'] . "
        )\">
            <i class='fa fa-edit'></i>
        </button>

        <form action='functions/update_product.php' method='POST' style='display:inline'>
            <input type='hidden' name='delete_id' value='" . (int)$row['id'] . "'>
            <button type='submit' class='delete-btn' onclick='return confirm(\"Delete this product?\")'>
                <i class='fa fa-trash'></i>
            </button>
        </form>
    </td>";

    echo "</tr>";
}
?>