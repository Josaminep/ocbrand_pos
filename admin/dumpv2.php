<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Accounts | OC Brand</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
:root{
    --primary:#16a34a;
    --primary-dark:#15803d;
    --bg:#f8fafc;
    --card:#ffffff;
    --text:#1f2937;
    --muted:#6b7280;
    --border:#e5e7eb;
    --radius:18px;
}

/* BASE */
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins,sans-serif}
body{background:var(--bg);display:flex;color:var(--text)}
.content{flex:1;margin-left:250px;padding:40px}

/* LAYOUT */
.page-grid{
    display:grid;
    grid-template-columns:380px 1fr;
    gap:28px;
    align-items:start;
}

/* CARD */
.card{
    background:var(--card);
    border-radius:var(--radius);
    padding:26px;
    box-shadow:0 14px 40px rgba(0,0,0,.06);
}

.card h2{
    font-size:18px;
    margin-bottom:18px;
    font-weight:600;
}

/* FORM SECTIONS */
.form-section{
    background:#f9fafb;
    border:1px solid var(--border);
    border-radius:14px;
    padding:16px;
    margin-bottom:14px;
}

.form-section h4{
    font-size:12px;
    color:var(--muted);
    text-transform:uppercase;
    margin-bottom:10px;
    letter-spacing:.06em;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
}

/* INPUTS */
input,select{
    width:100%;
    padding:10px 12px;
    border-radius:10px;
    border:1px solid var(--border);
    font-size:13px;
}
input:focus,select:focus{
    outline:none;
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(22,163,74,.15);
}

/* ACCOUNT ID */
.account-id{
    display:grid;
    grid-template-columns:1fr 40px;
    gap:8px;
}

/* BUTTONS */
button{
    border:none;
    border-radius:12px;
    padding:12px;
    font-size:14px;
    cursor:pointer;
    transition:.2s;
}

.primary{
    background:var(--primary);
    color:#fff;
}
.primary:hover{background:var(--primary-dark)}

.secondary{
    background:#e5e7eb;
}

/* IMAGE */
#preview{
    width:64px;
    height:64px;
    border-radius:14px;
    border:1px solid var(--border);
    object-fit:cover;
    display:none;
    margin-top:8px;
}

/* TABLE */
.table-card table{
    width:100%;
    border-collapse:separate;
    border-spacing:0 10px;
}
th{
    background:#111827;
    color:#fff;
    padding:12px;
    font-size:13px;
}
td{
    padding:12px;
    background:#fff;
    text-align:center;
    font-size:13px;
}
tbody tr:hover td{background:#f9fafb}

/* ACTIONS */
.actions{display:flex;gap:8px;justify-content:center}
.action{
    width:36px;height:36px;
    display:flex;align-items:center;justify-content:center;
    border-radius:10px;cursor:pointer
}
.edit{background:#e0f2fe;color:#0369a1}
.delete{background:#fee2e2;color:#991b1b}

/* RESPONSIVE */
@media(max-width:1100px){
    .page-grid{grid-template-columns:1fr}
}
</style>
</head>

<body>
<?php include "sidebar.php"; ?>

<div class="content">
<div class="page-grid">

<!-- CREATE ACCOUNT -->
<div class="card">
<h2 id="formTitle">Create Account</h2>

<form id="accountForm" enctype="multipart/form-data">
<input type="hidden" id="edit_id" name="id">

<div class="form-section">
<h4>Account</h4>
<select name="role" id="role" onchange="generateAccountId()" required>
    <option value="cashier">Cashier</option>
    <option value="admin">Admin</option>
</select>

<div class="account-id">
    <input type="text" id="account_id" name="account_id" readonly required>
    <button type="button" class="secondary" onclick="generateAccountId()" id="regenBtn">
        <i class="fa fa-rotate"></i>
    </button>
</div>
</div>

<div class="form-section">
<h4>Personal</h4>
<div class="grid">
    <input name="fname" placeholder="First Name" required>
    <input name="lname" placeholder="Last Name" required>
    <input name="mname" placeholder="Middle Name">
    <input name="contact" placeholder="Contact">
</div>
<input name="address" placeholder="Address">
</div>

<div class="form-section">
<h4>Security</h4>
<div class="grid">
    <input type="password" name="password" placeholder="Password">
    <input type="file" name="photo" accept="image/*">
</div>
<img id="preview">
</div>

<button class="primary" id="submitBtn">
    <i class="fa fa-user-plus"></i> Save Account
</button>
</form>
</div>

<!-- TABLE -->
<div class="card table-card">
<h2>Accounts</h2>

<table>
<thead>
<tr>
<th>ID</th>
<th>Role</th>
<th>Name</th>
<th>Contact</th>
<th>Actions</th>
</tr>
</thead>

<tbody id="tableBody">
<?php while($row = $result->fetch_assoc()): 
$fullName = trim($row['fname'].' '.($row['mname']?$row['mname'].' ':'').$row['lname']);
?>
<tr data-role="<?= $row['role'] ?>">
<td><?= htmlspecialchars($row['account_id']) ?></td>
<td><span class="role-badge role-<?= $row['role'] ?>"><?= ucfirst($row['role']) ?></span></td>
<td><img src="../uploads/<?= htmlspecialchars($row['photo']) ?>"></td>
<td><?= htmlspecialchars($fullName) ?></td>
<td><?= htmlspecialchars($row['address']) ?></td>
<td><?= htmlspecialchars($row['contact']) ?></td>
<td>
<div class="actions">
<div class="action-btn action-edit"
onclick="editAccount(
<?= (int)$row['id'] ?>,
'<?= htmlspecialchars($row['account_id']) ?>',
'<?= $row['role'] ?>',
'<?= htmlspecialchars($row['fname']) ?>',
'<?= htmlspecialchars($row['mname']) ?>',
'<?= htmlspecialchars($row['lname']) ?>',
'<?= htmlspecialchars($row['address']) ?>',
'<?= htmlspecialchars($row['contact']) ?>'
)">
<i class="fa fa-pen"></i>
</div>

<div class="action-btn action-delete" onclick="deleteAccount(<?= $row['id'] ?>)">
<i class="fa fa-trash"></i>
</div>
</div>
</td>
</tr>
<?php endwhile; ?>
</tbody>

</table>
</div>

</div>
</div>

<script>
let currentPage = 1;
const rowsPerPage = 8;

const searchInput = document.getElementById("searchInput");
const roleFilter  = document.getElementById("roleFilter");
const rows        = [...document.querySelectorAll("#tableBody tr")];
const pageInfo    = document.getElementById("pageInfo");

function applyFilters(){
    const search = searchInput.value.toLowerCase();
    const role   = roleFilter.value;

    return rows.filter(row=>{
        const text = row.innerText.toLowerCase();
        return text.includes(search) && (!role || row.dataset.role === role);
    });
}

function renderTable(){
    rows.forEach(r=>r.style.display="none");
    const filtered = applyFilters();

    const start = (currentPage-1)*rowsPerPage;
    const end   = start + rowsPerPage;

    filtered.slice(start,end).forEach(r=>r.style.display="");

    const pages = Math.max(1,Math.ceil(filtered.length/rowsPerPage));
    pageInfo.textContent = `Page ${currentPage} of ${pages}`;
}

function nextPage(){currentPage++;renderTable()}
function prevPage(){if(currentPage>1)currentPage--;renderTable()}

searchInput.oninput = ()=>{currentPage=1;renderTable()};
roleFilter.onchange = ()=>{currentPage=1;renderTable()};

renderTable();
</script>

<script>
/* =========================
   GENERATE ACCOUNT ID
========================= */
function generateAccountId() {
    // Do not regenerate when editing
    if (document.getElementById("edit_id").value) return;

    const role = document.getElementById("role").value;
    const prefix = role === "admin" ? "A" : "C";
    const random = Math.floor(10000 + Math.random() * 90000);

    document.getElementById("account_id").value = prefix + random;
}

/* =========================
   EDIT ACCOUNT (FILL FORM)
========================= */
function editAccount(id, accountId, role, fname, mname, lname, address, contact) {
    document.getElementById("edit_id").value = id;
    document.getElementById("role").value = role;
    document.getElementById("account_id").value = accountId;

    document.querySelector('[name="fname"]').value = fname;
    document.querySelector('[name="mname"]').value = mname || "";
    document.querySelector('[name="lname"]').value = lname;
    document.querySelector('[name="address"]').value = address;
    document.querySelector('[name="contact"]').value = contact;

    document.getElementById("formTitle").innerText = "Edit Account";
    document.getElementById("submitBtn").innerHTML =
        '<i class="fa fa-save"></i> Update Account';

    // Disable regenerate button if exists
    const regenBtn = document.getElementById("regenBtn");
    if (regenBtn) regenBtn.disabled = true;

    window.scrollTo({ top: 0, behavior: "smooth" });
}

/* =========================
   DELETE ACCOUNT
========================= */
function deleteAccount(id) {
    Swal.fire({
        title: "Delete this account?",
        text: "This action cannot be undone",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc2626",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Yes, delete it"
    }).then(async (result) => {
        if (!result.isConfirmed) return;

        const formData = new FormData();
        formData.append("action", "delete");
        formData.append("id", id);

        const res = await fetch("functions/save_account.php", {
            method: "POST",
            body: formData
        });

        const data = await res.json();

        if (data.status === "success") {
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: data.message,
                showConfirmButton: false,
                timer: 2000
            });

            setTimeout(() => location.reload(), 1500);
        } else {
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "error",
                title: data.message,
                showConfirmButton: false,
                timer: 3000
            });
        }
    });
}
</script>

<script>
/* =========================
   SWEETALERT TOAST
========================= */
const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true
});

/* =========================
   FORM SUBMIT (CREATE / EDIT)
========================= */
document.getElementById("accountForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    try {
        const res = await fetch("functions/save_account.php", {
            method: "POST",
            body: formData
        });

        const data = await res.json();

        if (data.status === "success") {
            Toast.fire({
                icon: "success",
                title: "Account saved successfully"
            });

            setTimeout(() => {
                window.location.href = "account.php";
            }, 1500);

        } else {
            Toast.fire({
                icon: "error",
                title: data.message || "Something went wrong"
            });
        }

    } catch (err) {
        Toast.fire({
            icon: "error",
            title: "Server error"
        });
    }
});
</script>

</body>
</html>
