<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include "../db.php";

/* FILTER */
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

/* STOCK STATUS FILTER */
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

/* PAGE INFO */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle   = ucfirst(str_replace('_', ' ', $currentPage));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inventory</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root{
    --primary:#28a745;
    --blue:#007bff;
    --red:#dc3545;
    --dark:#222;
    --light:#f5f6fa;
    --warning:#ffc107;
    --warning-bg:#fff8e1;
    --danger-bg:#fff1f1;
    --danger-border:#ffb3b3;
    --warning-border:#ffe08a;
    --gray-btn:#6c757d;
    --muted:#6b7280;
    --border:#e5e7eb;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Poppins",sans-serif;
}

body{
    display:flex;
    background:var(--light);
}

/* MAIN */
.main-content{
    flex:1;
    padding:32px;
    margin-left:250px;
}

.container{
    background:#fff;
    padding:24px;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

/* PAGE HEADER */
.page-header{
    text-align:center;
    margin-bottom:20px;
}

.page-header h1{
    font-size:30px;
    font-weight:700;
    color:#111827;
    margin-bottom:6px;
}

.page-header p{
    font-size:14px;
    color:var(--muted);
}

/* TOP PANEL */
.top-panel{
    background:#f9fafb;
    border:1px solid var(--border);
    border-radius:18px;
    padding:18px 22px;
    margin-bottom:22px;
}

.top-bar{
    display:flex;
    justify-content:flex-start;
    align-items:center;
    gap:10px;
    flex-wrap:nowrap;
}

/* FILTERS */
.filter-form{
    flex:0 1 auto;
    display:grid;
    grid-template-columns:210px 300px 90px;
    gap:8px;
    align-items:center;
    margin-right:4px;
}

.filter-form select,
.filter-form input{
    padding:12px 14px;
    border-radius:12px;
    border:1px solid #d1d5db;
    font-size:14px;
    background:#fff;
    transition:.2s ease;
    margin-bottom:0;
}

#categoryFilter{
    width:210px;
    min-width:210px;
    max-width:210px;
}

#searchInput{
    width:300px;
    min-width:300px;
    max-width:300px;
}

#stockStatusFilter{
    width:100px;
    min-width:100px;
    max-width:100px;
    justify-self:start;
}

.filter-form input:focus,
.filter-form select:focus,
input:focus,
select:focus{
    outline:none;
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(40,167,69,.12);
}

/* ACTION BUTTON GROUP */
.action-group{
    display:flex;
    gap:8px;
    align-items:center;
    flex-shrink:0;
    margin-left:0;
}

/* BUTTONS */
button{
    border:none;
    cursor:pointer;
    border-radius:12px;
    transition:.2s ease;
}

.add-btn{
    background:var(--primary);
    color:#fff;
    padding:9px 12px;
    font-size:11px;
    font-weight:600;
    line-height:1.1;
    box-shadow:0 6px 14px rgba(40,167,69,.22);
    white-space:nowrap;
}

.add-btn i,
.print-btn i{
    font-size:11px;
    margin-right:3px;
}

.add-btn:hover{
    background:#218838;
    transform:translateY(-1px);
}

.edit-btn{
    background:var(--blue);
    color:#fff;
    padding:7px 11px;
}
.edit-btn:hover{
    background:#0056b3;
}

.delete-btn{
    background:var(--red);
    color:#fff;
    padding:7px 11px;
}
.delete-btn:hover{
    background:#b02a37;
}

.print-btn{
    background:var(--gray-btn);
    box-shadow:0 6px 14px rgba(108,117,125,.22);
    padding:9px 12px;
    font-size:11px;
    font-weight:600;
    line-height:1.1;
    white-space:nowrap;
}

.print-btn:hover{
    background:#5c636a;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    font-size:13px;
    overflow:hidden;
    border-radius:12px;
}

th, td{
    padding:11px 10px;
    text-align:center;
    vertical-align:middle;
}

th{
    background:var(--dark);
    color:#fff;
    position:sticky;
    top:0;
    z-index:2;
}

tbody tr{
    border-bottom:1px solid #eee;
}

tbody tr:nth-child(even){
    background:#fafafa;
}

tbody tr:hover{
    background:#eef4ff;
}

td img{
    width:48px;
    height:48px;
    border-radius:10px;
    object-fit:cover;
    border:1px solid #ddd;
    background:#fff;
}

/* STOCK */
.low-stock{
    background:var(--warning-bg) !important;
}

.out-stock{
    background:var(--danger-bg) !important;
}

.stock-cell{
    font-weight:600;
    white-space:nowrap;
}

.stock-count.zero{
    color:#c62828;
    font-weight:700;
}

.stock-badge{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:4px 10px;
    border-radius:999px;
    font-size:11px;
    font-weight:700;
    margin-left:6px;
}

.stock-badge.low{
    background:#fff3cd;
    color:#9a6700;
    border:1px solid var(--warning-border);
}

.stock-badge.out{
    background:#ffe1e1;
    color:#c62828;
    border:1px solid var(--danger-border);
}

/* MODAL */
.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.45);
    justify-content:center;
    align-items:center;
    z-index:999;
}

.modal-content{
    background:#fff;
    width:340px;
    padding:20px;
    border-radius:14px;
    box-shadow:0 10px 30px rgba(0,0,0,.25);
    animation:fade .25s ease;
}

@keyframes fade{
    from{transform:scale(.95);opacity:0}
    to{transform:scale(1);opacity:1}
}

.modal-content h3{
    text-align:center;
    margin-bottom:15px;
}

.close{
    float:right;
    font-size:18px;
    cursor:pointer;
    color:#c00;
}

/* FORM */
input, select{
    width:100%;
    padding:11px 14px;
    margin-bottom:10px;
    border-radius:12px;
    border:1px solid #ccc;
    font-size:14px;
}

/* TOAST */
#toast{
    position:fixed;
    top:20px;
    right:20px;
    background:#333;
    color:#fff;
    padding:14px 20px;
    border-radius:10px;
    box-shadow:0 10px 25px rgba(0,0,0,.2);
    font-size:14px;
    opacity:0;
    transform:translateY(-10px);
    animation:toast-in .4s forwards, toast-out .4s forwards 3s;
    z-index:9999;
}

@keyframes toast-in{
    to{
        opacity:1;
        transform:translateY(0);
    }
}

@keyframes toast-out{
    to{
        opacity:0;
        transform:translateY(-10px);
    }
}

/* RESPONSIVE */
@media (max-width:1100px){
    .top-bar{
        flex-direction:column;
        align-items:stretch;
    }

    .filter-form{
        grid-template-columns:1fr;
        margin-right:0;
    }

    #categoryFilter,
    #searchInput,
    #stockStatusFilter{
        width:100%;
        min-width:0;
        max-width:100%;
    }

    .action-group{
        justify-content:flex-start;
        flex-wrap:wrap;
    }
}

@media (max-width:900px){
    .main-content{
        padding:20px;
    }

    .container{
        padding:18px;
    }

    .page-header h1{
        font-size:26px;
    }
}
</style>

</head>
<body>

<?php include "sidebar.php"; ?>

<div class="main-content">
<div class="container">

<div class="page-header">
    <h1>Inventory</h1>
</div>

<div class="top-panel">
    <div class="top-bar">

        <form method="GET" class="filter-form">
            <select name="category" id="categoryFilter">
                <option value="">All Categories</option>
                <?php
                $catResult = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");
                while ($catRow = mysqli_fetch_assoc($catResult)):
                    $catName = $catRow['name'];
                ?>
                    <option value="<?= htmlspecialchars($catName) ?>" <?= $category === $catName ? 'selected' : '' ?>>
                        <?= htmlspecialchars($catName) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <input
                type="text"
                id="searchInput"
                name="search"
                placeholder="Search name, brand, SKU..."
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
            >

            <select name="stock_status" id="stockStatusFilter">
                <option value="">All Stock</option>
                <option value="low" <?= $stockStatus === 'low' ? 'selected' : '' ?>>Low Stock</option>
                <option value="out" <?= $stockStatus === 'out' ? 'selected' : '' ?>>Out of Stock</option>
                <option value="in" <?= $stockStatus === 'in' ? 'selected' : '' ?>>In Stock</option>
            </select>
        </form>

        <div class="action-group">
            <button type="button" class="add-btn print-btn" onclick="openPrintModal()">
                <i class="fa fa-print"></i> Print
            </button>

            <button type="button" class="add-btn" onclick="openCategoryModal()">
                <i class="fa fa-plus"></i> Add Category
            </button>

            <button type="button" class="add-btn" onclick="openAdd()">
                <i class="fa fa-plus"></i> Add Product
            </button>
        </div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Image</th>
            <th>SKU ID</th>
            <th>Brand</th>
            <th>Name</th>
            <th>Category</th>
            <th>SRP</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody id="inventoryTableBody">
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <?php
        $image = !empty($row['image']) ? $row['image'] : 'uploads/no-image.png';
        $qty   = (int)$row['quantity'];

        $rowClass = '';
        if ($qty === 0) {
            $rowClass = 'out-stock';
        } elseif ($qty > 0 && $qty < 10) {
            $rowClass = 'low-stock';
        }
        ?>
        <tr class="<?= $rowClass ?>">
            <td>
                <img src="../<?= htmlspecialchars($image) ?>" alt="Product Image">
            </td>
            <td><?= htmlspecialchars($row['product_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['brand'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td>₱<?= number_format((float)$row['srp'], 2) ?></td>
            <td><strong>₱<?= number_format((float)$row['price'], 2) ?></strong></td>

            <td class="stock-cell">
                <?php if ($qty === 0): ?>
                    <span class="stock-count zero">0</span>
                    <span class="stock-badge out">
                        <i class="fa-solid fa-circle-xmark"></i> Out of Stock
                    </span>
                <?php elseif ($qty < 10): ?>
                    <?= $qty ?>
                    <span class="stock-badge low">
                        <i class="fa-solid fa-triangle-exclamation"></i> Low Stock
                    </span>
                <?php else: ?>
                    <?= $qty ?>
                <?php endif; ?>
            </td>

            <td>
                <button class="edit-btn" onclick="openEdit(
                    <?= (int)$row['id'] ?>,
                    '<?= addslashes($row['name']) ?>',
                    '<?= addslashes($row['category']) ?>',
                    <?= (float)$row['srp'] ?>,
                    <?= (float)$row['price'] ?>,
                    <?= (int)$row['quantity'] ?>
                )">
                    <i class="fa fa-edit"></i>
                </button>

                <form action="functions/update_product.php" method="POST" style="display:inline">
                    <input type="hidden" name="delete_id" value="<?= (int)$row['id'] ?>">
                    <button type="submit" class="delete-btn" onclick="return confirm('Delete this product?')">
                        <i class="fa fa-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</div>
</div>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
<div class="modal-content">
<span class="close" onclick="closeAdd()">✖</span>
<h3>Add Product</h3>

<form action="functions/add_product.php" method="POST" enctype="multipart/form-data">
    <input type="text" id="product_code" placeholder="Product Code" readonly>
    <input type="text" name="name" placeholder="Product Name" required>

    <select name="category" id="categorySelect" required>
        <?php
        $catResult = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");
        while ($catRow = mysqli_fetch_assoc($catResult)):
        ?>
            <option value="<?= htmlspecialchars($catRow['name']) ?>">
                <?= htmlspecialchars($catRow['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <input type="number" step="0.01" name="srp" placeholder="SRP" required>
    <input type="number" step="0.01" name="price" placeholder="Price" required>
    <input type="number" name="quantity" placeholder="Stock" required>
    <input type="file" name="image" accept="image/*" required>

    <button type="submit" class="add-btn" name="add_product">Save</button>
</form>
</div>
</div>

<!-- EDIT MODAL -->
<div class="modal" id="editModal">
<div class="modal-content">
<span class="close" onclick="closeEdit()">✖</span>
<h3>Edit Product</h3>

<form action="functions/update_product.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" id="eid">
    <input type="text" name="name" id="ename" required>

    <select name="category" id="edit_category" required>
        <?php
        $catResult = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");
        while ($catRow = mysqli_fetch_assoc($catResult)):
            $catName = $catRow['name'];
        ?>
            <option value="<?= htmlspecialchars($catName) ?>"><?= htmlspecialchars($catName) ?></option>
        <?php endwhile; ?>
    </select>

    <input type="number" step="0.01" name="srp" id="esrp" required>
    <input type="number" step="0.01" name="price" id="eprice" required>
    <input type="number" name="quantity" id="eqty" required>
    <input type="file" name="image" accept="image/*">

    <button type="submit" class="add-btn" name="update_product">Update</button>
</form>
</div>
</div>

<!-- ADD CATEGORY MODAL -->
<div class="modal" id="categoryModal">
<div class="modal-content">
    <span class="close" onclick="closeCategoryModal()">✖</span>
    <h3>Add New Category</h3>

    <form id="categoryForm" method="POST">
        <input type="text" name="category_name" id="category_name" placeholder="Category Name" required>
        <button class="add-btn" type="submit">Save</button>
    </form>
</div>
</div>

<!-- PRINT OPTIONS MODAL -->
<div class="modal" id="printModal">
  <div class="modal-content">
    <span class="close" onclick="closePrintModal()">✖</span>
    <h3>Export Inventory</h3>

    <button type="button" class="add-btn" style="width:100%;margin-bottom:10px" onclick="printAsPDF()">
        🖨 Print / Save as PDF
    </button>

    <button type="button" class="add-btn" style="width:100%;background:#007bff" onclick="exportExcel()">
        📊 Export as Excel
    </button>
  </div>
</div>

<script>
const addModal = document.getElementById('addModal');
const editModal = document.getElementById('editModal');
const categoryModal = document.getElementById('categoryModal');
const printModal = document.getElementById('printModal');

const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const stockStatusFilter = document.getElementById('stockStatusFilter');
const tableBody = document.getElementById('inventoryTableBody');

function updateProductCode() {
    const category = document.getElementById('categorySelect').value;

    fetch('functions/get_next_product_code.php?category=' + encodeURIComponent(category))
        .then(res => res.text())
        .then(code => {
            document.getElementById('product_code').value = code;
        });
}

document.getElementById('categorySelect').addEventListener('change', updateProductCode);
updateProductCode();

function openAdd() {
    addModal.style.display = "flex";
}

function closeAdd() {
    addModal.style.display = "none";
}

function openEdit(id, n, c, s, p, q) {
    document.getElementById('eid').value = id;
    document.getElementById('ename').value = n;
    document.getElementById('edit_category').value = c;
    document.getElementById('esrp').value = s;
    document.getElementById('eprice').value = p;
    document.getElementById('eqty').value = q;
    editModal.style.display = "flex";
}

function closeEdit() {
    editModal.style.display = "none";
}

function openCategoryModal() {
    categoryModal.style.display = 'flex';
}

function closeCategoryModal() {
    categoryModal.style.display = 'none';
}

function openPrintModal() {
    printModal.style.display = 'flex';
}

function closePrintModal() {
    printModal.style.display = 'none';
}

document.getElementById('categoryForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const name = document.getElementById('category_name').value.trim();
    if (name === '') return;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'functions/add_category.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);

            if (response.success) {
                const addCategorySelect = document.getElementById('categorySelect');
                const editCategorySelect = document.getElementById('edit_category');

                [addCategorySelect, categoryFilter, editCategorySelect].forEach(select => {
                    const option = document.createElement('option');
                    option.value = name;
                    option.textContent = name;
                    select.appendChild(option);
                });

                addCategorySelect.value = name;
                categoryFilter.value = name;

                closeCategoryModal();
                document.getElementById('category_name').value = '';
                updateProductCode();
                updateTable();
                alert('Category added!');
            } else {
                alert(response.message);
            }
        }
    };

    xhr.send('name=' + encodeURIComponent(name));
});

function updateTable() {
    const search = searchInput.value.trim();
    const category = categoryFilter.value;
    const stockStatus = stockStatusFilter.value;

    const xhr = new XMLHttpRequest();
    xhr.open(
        'GET',
        `functions/search_products.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}&stock_status=${encodeURIComponent(stockStatus)}`,
        true
    );

    xhr.onload = function() {
        if (xhr.status === 200) {
            tableBody.innerHTML = xhr.responseText;
        }
    };

    xhr.send();
}

let debounceTimer;
searchInput.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(updateTable, 200);
});

categoryFilter.addEventListener('change', updateTable);
stockStatusFilter.addEventListener('change', updateTable);

function printAsPDF() {
    closePrintModal();

    const params = new URLSearchParams();
    if (categoryFilter.value) params.set('category', categoryFilter.value);
    if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
    if (stockStatusFilter.value) params.set('stock_status', stockStatusFilter.value);

    window.open('functions/inventory_print.php?' + params.toString(), '_blank');
}

function exportExcel() {
    const params = new URLSearchParams();
    if (categoryFilter.value) params.set('category', categoryFilter.value);
    if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
    if (stockStatusFilter.value) params.set('stock_status', stockStatusFilter.value);

    window.location.href = 'functions/inventory_excel.php?' + params.toString();
}

window.addEventListener('click', function(e) {
    if (e.target === addModal) closeAdd();
    if (e.target === editModal) closeEdit();
    if (e.target === categoryModal) closeCategoryModal();
    if (e.target === printModal) closePrintModal();
});
</script>

<?php
if (isset($_SESSION['toast'])) {
    $toastMessage = '';

    if (is_array($_SESSION['toast'])) {
        $toastMessage = $_SESSION['toast'][0] ?? '';
    } else {
        $toastMessage = $_SESSION['toast'];
    }

    if (!empty(trim($toastMessage))) {
        echo '<div id="toast">' . htmlspecialchars($toastMessage) . '</div>';
    }

    unset($_SESSION['toast']);
}
?>

</body>
</html>