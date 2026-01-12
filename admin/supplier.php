<?php
session_start();
include "../db.php";

/* SEARCH */
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM suppliers";
if ($search != '') {
    $safe = mysqli_real_escape_string($conn, $search);
    $sql .= " WHERE name LIKE '%$safe%' OR contact_person LIKE '%$safe%'";
}
$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

/* Page info */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle   = ucfirst(str_replace('_', ' ', $currentPage));

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Suppliers - OC Brand</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins,sans-serif}
body{
    display:flex;
    background:var(--light);
}
.main-content{flex:1;padding:50px;margin-left:250px}
.container{background:#fff;padding:25px;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.08)}
h1{margin-bottom:20px;font-size:26px;font-weight:600}

.top-bar{display:flex;justify-content:space-between;gap:10px;margin-bottom:15px}
.search-input{width:260px;padding:8px 14px;border-radius:8px;border:1px solid #ccc}
.add-btn{background:#28a745;color:#fff;border:none;padding:8px 16px;border-radius:8px;cursor:pointer}
.add-btn:hover{background:#218838}

table{width:100%;border-collapse:collapse;font-size:14px}
th{background:#333;color:#fff;padding:10px;text-align:left}
td{padding:10px;border-bottom:1px solid #eee}
tr:hover td{background:#f5faff}

.status-pill{padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600}
.status-active{background:#e6f8ec;color:#1f7a3e;border:1px solid #1f7a3e}
.status-inactive{background:#ffe7e7;color:#c62828;border:1px solid #c62828}

.edit-btn,.delete-btn{
    padding:5px 10px;border:none;border-radius:6px;
    font-size:12px;cursor:pointer
}
.edit-btn{background:#007bff;color:#fff}
.delete-btn{background:#dc3545;color:#fff}

.modal{
    position:fixed;inset:0;
    background:rgba(0,0,0,.35);
    display:none;justify-content:center;align-items:center
}
.modal-content{
    width:350px;background:#fff;padding:20px;border-radius:12px
}
input,select{
    width:100%;padding:8px 12px;
    border:1px solid #ccc;border-radius:8px;margin-bottom:10px
}
.close{float:right;font-size:20px;color:#d00000;cursor:pointer}
.save-btn{
    width:100%;background:#28a745;border:none;
    padding:8px;border-radius:8px;color:#fff;cursor:pointer
}
h1{
    text-align:center;
    margin-bottom:20px;
    font-weight:600;
}
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
    to{opacity:1;transform:translateY(0)}
}
@keyframes toast-out{
    to{opacity:0;transform:translateY(-10px)}
}
</style>
</head>

<body>
<?php include "sidebar.php"; ?>

<div class="main-content">
<div class="container">

<h1>Suppliers</h1>

<div class="top-bar">
    <form method="GET">
        <input type="text"
            name="search"
            class="search-input"
            placeholder="Search supplier..."
            value="<?= htmlspecialchars($search) ?>"
            onkeyup="this.form.submit()">
    </form>
    <button class="add-btn" onclick="openAdd()">
        <i class="fa fa-plus"></i> Add Supplier
    </button>
</div>

<table>
<tr>
    <th>Supplier Name</th>
    <th>Contact Person</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

<?php while($row=mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?= $row['name'] ?></td>
    <td><?= $row['contact_person'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['phone'] ?></td>
    <td>
        <span class="status-pill <?= $row['status']=='Active'?'status-active':'status-inactive' ?>">
            <?= $row['status'] ?>
        </span>
    </td>
    <td>
        <button class="edit-btn" onclick="openEdit(
            <?= $row['id'] ?>,
            '<?= addslashes($row['name']) ?>',
            '<?= addslashes($row['contact_person']) ?>',
            '<?= $row['email'] ?>',
            '<?= $row['phone'] ?>',
            '<?= $row['status'] ?>'
        )"><i class="fa fa-edit"></i></button>

        <form action="functions/update_supplier.php" method="POST" style="display:inline">
            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
            <button class="delete-btn" onclick="return confirm('Delete supplier?')">
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
<span class="close" onclick="closeAdd()">×</span>
<h3>Add Supplier</h3>

<form action="functions/add_supplier.php" method="POST">
    <input type="text" name="name" placeholder="Supplier Name" required>
    <input type="text" name="contact_person" placeholder="Contact Person" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="phone" placeholder="Phone Number" required>
    <select name="status">
        <option>Active</option>
        <option>Inactive</option>
    </select>
    <button class="save-btn" name="add_supplier">Save</button>
</form>
</div>
</div>

<!-- EDIT MODAL -->
<div class="modal" id="editModal">
<div class="modal-content">
<span class="close" onclick="closeEdit()">×</span>
<h3>Edit Supplier</h3>

<form action="functions/update_supplier.php" method="POST">
    <input type="hidden" name="id" id="sid">
    <input type="text" name="name" id="sname" required>
    <input type="text" name="contact_person" id="sperson" required>
    <input type="email" name="email" id="semail" required>
    <input type="text" name="phone" id="sphone" required>
    <select name="status" id="sstatus">
        <option>Active</option>
        <option>Inactive</option>
    </select>
    <button class="save-btn" name="update_supplier">Update</button>
</form>
</div>
</div>

<script>
function openAdd(){addModal.style.display="flex"}
function closeAdd(){addModal.style.display="none"}

function openEdit(id,n,p,e,ph,s){
    sid.value=id;
    sname.value=n;
    sperson.value=p;
    semail.value=e;
    sphone.value=ph;
    sstatus.value=s;
    editModal.style.display="flex";
}
function closeEdit(){editModal.style.display="none"}
</script>

<?php
if (isset($_SESSION['toast'])) {

    $toastMessage = is_array($_SESSION['toast'])
        ? ($_SESSION['toast'][0] ?? '')
        : $_SESSION['toast'];

    if (!empty(trim($toastMessage))) {
        echo '<div id="toast">' . htmlspecialchars($toastMessage) . '</div>';
    }

    unset($_SESSION['toast']);
}
?>

</body>
</html>
