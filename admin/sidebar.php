<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../db.php";

/* redirect if not logged in */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../home.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT fname, role FROM accounts WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

/* basic values */
$fname = htmlspecialchars($user['fname'] ?? '', ENT_QUOTES, 'UTF-8');
$role  = htmlspecialchars($user['role'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<style>
/* =========================
   SIDEBAR LAYOUT
========================= */
.sidebar {
    width: 250px;
    height: 100vh;
    background-color: #1a1a1a;
    color: #fff;
    position: fixed;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 20px 0;
}

.sidebar h2 {
    color: #d4af37;
    text-align: center;
    margin-bottom: 25px;
    font-size: 24px;
    text-transform: uppercase;
}

/* =========================
   USER PROFILE
========================= */
.sidebar-user {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 25px;
    margin-bottom: 30px;
    border-bottom: 1px solid #333;
}

.sidebar-user .avatar {
    width: 42px;
    height: 42px;
    background: #333;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4af37;
    font-size: 18px;
}

.sidebar-user .user-info {
    display: flex;
    flex-direction: column;
}

.sidebar-user .name {
    font-size: 15px;
    font-weight: 600;
}

.sidebar-user .role {
    font-size: 12px;
    color: #bbb;
    text-transform: uppercase;
}

/* =========================
   NAV LINKS
========================= */
.sidebar a {
    display: flex;
    align-items: center;
    padding: 12px 25px;
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    transition: background 0.3s, color 0.3s;
}

.sidebar a i {
    width: 20px;
    margin-right: 15px;
    text-align: center;
}

.sidebar a:hover,
.sidebar a.active {
    background-color: #333;
    color: #d4af37;
}

/* =========================
   LOGOUT
========================= */
.logout {
    border-top: 1px solid #333;
    padding-top: 15px;
}
</style>

<div class="sidebar">

    <div>
        <h2>OC Brand</h2>

        <!-- USER INFO -->
        <div class="sidebar-user">
            <div class="avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-info">
                <span class="name"><?= $fname ?></span>
                <span class="role"><?= ucfirst($role) ?></span>
            </div>
        </div>

        <!-- NAVIGATION -->
        <a href="dashboard.php" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <a href="pos.php" class="<?= $currentPage === 'pos' ? 'active' : '' ?>">
            <i class="fas fa-cash-register"></i> POS
        </a>

        <a href="inventory.php" class="<?= $currentPage === 'inventory' ? 'active' : '' ?>">
            <i class="fas fa-boxes"></i> Manage Inventory
        </a>

        <a href="supplier.php" class="<?= $currentPage === 'supplier' ? 'active' : '' ?>">
            <i class="fas fa-truck"></i> Manage Supplier
        </a>

        <?php if ($role === 'admin'): ?>
        <a href="account.php" class="<?= $currentPage === 'account' ? 'active' : '' ?>">
            <i class="fas fa-users-cog"></i> Manage Account
        </a>
        <?php endif; ?>

        <a href="reports.php" class="<?= $currentPage === 'reports' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> Reports
        </a>

    </div>

    <!-- LOGOUT -->
    <div class="logout">
        <a href="#" onclick="confirmLogout(event)">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

</div>

<script>
function confirmLogout(e){
    e.preventDefault();

    if (confirm("Are you sure you want to logout?")) {
        window.location.href = "../logout.php";
    }
}
</script>
