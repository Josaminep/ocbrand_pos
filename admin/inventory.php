<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include "../db.php";

/* FILTER */
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

/* LOW STOCK FILTER */
if (!empty($lowStock)) {
    $sql .= " AND quantity < 10";
}

$sql .= " ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

$lowStock = $_GET['low_stock'] ?? '';




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
}

*{
    margin:0;padding:0;box-sizing:border-box;
    font-family:"Poppins",sans-serif
}

body{
    display:flex;
    background:var(--light);
}

/* MAIN */
.main-content{
    flex:1;
    padding:40px;
    margin-left:250px;
}

.container{
    background:#fff;
    padding:25px;
    border-radius:14px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

h1{
    text-align:center;
    margin-bottom:20px;
    font-weight:600;
}

/* TOP BAR */
.top-bar{
    display:flex;
    justify-content:flex-end;
    margin-bottom:15px;
}

/* BUTTONS */
button{
    border:none;
    cursor:pointer;
    border-radius:8px;
    transition:.2s;
}

.add-btn{
    background:var(--primary);
    color:#fff;
    padding:8px 14px;
    font-size:14px;
    box-shadow:0 4px 10px rgba(40,167,69,.3);
}
.add-btn:hover{background:#218838}

.edit-btn{
    background:var(--blue);
    color:#fff;
    padding:6px 10px;
}
.edit-btn:hover{background:#0056b3}

.delete-btn{
    background:var(--red);
    color:#fff;
    padding:6px 10px;
}
.delete-btn:hover{background:#b02a37}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    font-size:13px;
}

th,td{
    padding:10px;
    text-align:center;
}

th{
    background:var(--dark);
    color:#fff;
    position:sticky;
    top:0;
    z-index:2;
}

tr{
    border-bottom:1px solid #eee;
}

tr:nth-child(even){background:#fafafa}
tr:hover{background:#eef4ff}

td img{
    width:48px;
    height:48px;
    border-radius:8px;
    object-fit:cover;
    border:1px solid #ddd;
}

/* STOCK */
.low-stock{
    background:#fff1f1!important;
}
.stock-badge{
    display:inline-block;
    padding:3px 8px;
    border-radius:20px;
    font-size:11px;
    background:#ffe1e1;
    color:#c00;
    margin-left:5px;
    font-weight:600;
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
input,select{
    width:100%;
    padding:8px;
    margin-bottom:10px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:13px;
}
input:focus,select:focus{
    outline:none;
    border-color:var(--primary);
    box-shadow:0 0 0 2px rgba(40,167,69,.15);
}
.top-bar select{
    border:1px solid #ccc;
    background:#fff;
    font-size:13px;
    cursor:pointer;
}
.top-bar select:focus{
    outline:none;
    border-color:var(--primary);
}
/* TOAST ALERT */
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
</style>

</head>

<body>

<?php include "sidebar.php"; ?>

<div class="main-content">
<div class="container">

<h1>Inventory</h1>

<div class="top-bar" style="justify-content:space-between">

<!-- FILTER & SEARCH -->
<form method="GET" style="display:flex; align-items:center; gap:5px;">
    <!-- Category Dropdown -->
    <select name="category">
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

    <input type="text" id="searchInput" name="search" placeholder="Search..."
           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

<label style="display:flex; align-items:center; gap:4px; cursor:pointer; font-size:13px; white-space:nowrap;">
    <input type="checkbox" name="low_stock" value="1"
        <?= isset($_GET['low_stock']) ? 'checked' : '' ?>
        style="margin:0; vertical-align:middle;">

    <i class="fa-solid fa-triangle-exclamation" style="color:#dc3545; font-size:14px; line-height:1;"></i>
    
    <span style="line-height:1;">Low Stock</span>
</label>


</form>


    <div style="display: flex; gap: 10px; align-items: center;">
        <!-- Print Button -->
        <button type="button" class="add-btn" onclick="printInventory()" style="background:#6c757d;">
            <i class="fa fa-print"></i> Print
        </button>

        <!-- Add Category Button -->
        <button type="button" class="add-btn" onclick="openCategoryModal()">
            <i class="fa fa-plus"></i> Add Category
        </button>

        <!-- Add Product Button -->
        <button class="add-btn" onclick="openAdd()">
            <i class="fa fa-plus"></i> Add Product
        </button>
    </div>
</div>

<table>
<tr>
    <th>Image</th>
    <th>Brand</th>
    <th>Name</th>
    <th>Category</th>
    <th>SRP</th>
    <th>Price</th>
    <th>Stock</th>
    <th>Actions</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($result)): ?>

<?php
$image = !empty($row['image']) ? $row['image'] : 'uploads/no-image.png';
?>

<tr class="<?= ($row['quantity'] < 10) ? 'low-stock' : '' ?>">

<td>
    <img src="../<?= htmlspecialchars($image) ?>" alt="Product Image">
</td>

<td><?= htmlspecialchars($row['brand'] ?? '') ?></td>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['category']) ?></td>
<td>₱<?= number_format((float)$row['srp'], 2) ?></td>
<td><strong>₱<?= number_format((float)$row['price'], 2) ?></strong></td>

<td>
    <?= (int)$row['quantity'] ?>
    <?= $row['quantity'] < 10 ? '<span class="stock-badge">LOW</span>' : '' ?>
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
        <button class="delete-btn" onclick="return confirm('Delete this product?')">
            <i class="fa fa-trash"></i>
        </button>
    </form>
</td>

</tr>

<?php endwhile; ?>
</table>

</div>
</div>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
<div class="modal-content">
<span class="close" onclick="closeAdd()">✖</span>
<h3>Add Product</h3>

<form action="functions/add_product.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Product Name" required>
    <select name="category" id="ecat">
        <?php
        $catResult = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");
        while ($catRow = mysqli_fetch_assoc($catResult)):
            $catName = $catRow['name'];
        ?>
            <option value="<?= htmlspecialchars($catName) ?>"><?= htmlspecialchars($catName) ?></option>
        <?php endwhile; ?>
    </select>

    <input type="number" step="0.01" name="srp" placeholder="SRP" required>
    <input type="number" name="price" placeholder="Price" required>
    <input type="number" name="quantity" placeholder="Stock" required>
    <input type="file" name="image" accept="image/*" required>
    <button class="add-btn" name="add_product">Save</button>
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

<select name="category" id="edit_category">
    <?php
    $catResult = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");
    while ($catRow = mysqli_fetch_assoc($catResult)):
        $catName = $catRow['name'];
    ?>
        <option value="<?= htmlspecialchars($catName) ?>"><?= htmlspecialchars($catName) ?></option>
    <?php endwhile; ?>
</select>


    <input type="number" step="0.01" name="srp" id="esrp" required>
    <input type="number" name="price" id="eprice" required>
    <input type="number" name="quantity" id="eqty" required>
    <input type="file" name="image" accept="image/*">

    <button class="add-btn" name="update_product">Update</button>
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

<script>
function openAdd(){ addModal.style.display="flex"; }
function closeAdd(){ addModal.style.display="none"; }

function openEdit(id, n, c, s, p, q) {
    document.getElementById('eid').value = id;
    document.getElementById('ename').value = n;
    document.getElementById('edit_category').value = c;
    document.getElementById('esrp').value = s;
    document.getElementById('eprice').value = p;
    document.getElementById('eqty').value = q;

    editModal.style.display = "flex";
}

function closeEdit(){ editModal.style.display="none"; }

// Modal functions
const categoryModal = document.getElementById('categoryModal');

function openCategoryModal(){ categoryModal.style.display = 'flex'; }
function closeCategoryModal(){ categoryModal.style.display = 'none'; }

// AJAX submit category
document.getElementById('categoryForm').addEventListener('submit', function(e){
    e.preventDefault();
    const name = document.getElementById('category_name').value.trim();
    if(name === '') return;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'functions/add_category.php', true);
    xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');
    xhr.onload = function(){
        if(xhr.status === 200){
            const response = JSON.parse(xhr.responseText);
            if(response.success){
                // Add new option to dropdown
                const select = document.querySelector('select[name="category"]');
                const option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                option.selected = true;
                select.appendChild(option);

                closeCategoryModal();
                document.getElementById('category_name').value = '';
                alert('Category added!');
            } else {
                alert(response.message);
            }
        }
    };
    xhr.send('name=' + encodeURIComponent(name));
});

const searchInput = document.getElementById('searchInput');
const tableBody = document.querySelector('table tbody');

searchInput.addEventListener('input', function() {
    const search = this.value.trim();
    const category = document.querySelector('select[name="category"]').value;

    const xhr = new XMLHttpRequest();
    xhr.open('GET', `functions/search_products.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`, true);
    xhr.onload = function() {
        if(xhr.status === 200) {
            tableBody.innerHTML = xhr.responseText;
        }
    };
    xhr.send();
});

const categorySelect = document.querySelector('select[name="category"]');

function updateTable() {
    const search = searchInput.value.trim();
    const category = categorySelect.value;
    const lowStock = document.querySelector('input[name="low_stock"]')?.checked ? 1 : '';

    const xhr = new XMLHttpRequest();
    xhr.open(
        'GET',
        `functions/search_products.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}&low_stock=${lowStock}`,
        true
    );
    xhr.onload = function() {
        if (xhr.status === 200) {
            tableBody.innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}

document.querySelector('input[name="low_stock"]').addEventListener('change', updateTable);

// Live search while typing
let debounceTimer;
searchInput.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(updateTable, 200);
});

// Update table when category changes
categorySelect.addEventListener('change', updateTable);

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
