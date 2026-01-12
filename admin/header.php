<!-- header.php -->
<div class="header">
    <div class="store-name">ADMIN</div>
    <div class="header-actions">
        <span class="admin-name">Admin Name</span>
        <a href="../home.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<style>
.header {
    background-color: #1a1a1a;
    color: #d4af37;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 30px;
    border-bottom: 1px solid #333;
    position: sticky;
    top: 0;
    z-index: 100;
}

.store-name {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: 1px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.admin-name {
    font-size: 16px;
    color: #fff;
}

.logout-btn {
    text-decoration: none;
    background-color: #d4af37;
    color: #000;
    padding: 8px 15px;
    border-radius: 8px;
    font-weight: 600;
    transition: 0.3s;
}

.logout-btn:hover {
    background-color: #b8922c;
    color: #fff;
}
</style>
