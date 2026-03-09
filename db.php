<?php
declare(strict_types=1);

/* =========================
   DATABASE CONNECTION
========================= */

$host = "localhost";
$user = "root";
$pass = "";
$db   = "pos_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    // Never output HTML before headers
    error_log("Database connection failed: " . mysqli_connect_error());
    http_response_code(500);
    exit;
}
?>