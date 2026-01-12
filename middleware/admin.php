<?php
require __DIR__ . "/auth.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin/dashboard.php");
    exit();
}
