<?php
session_start();
include "../../db.php";

if (isset($_POST['add_supplier'])) {

    $name   = trim($_POST['name'] ?? '');
    $person = trim($_POST['contact_person'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if ($name === '' || $person === '' || $phone === '') {
        $_SESSION['toast'] = 'Please fill in all required fields.';
        header("Location: ../supplier.php");
        exit;
    }

    $sql = "INSERT INTO suppliers (name, contact_person, email, phone, status)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssss", $name, $person, $email, $phone, $status);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['toast'] = 'Supplier added successfully';
        } else {
            $_SESSION['toast'] = 'Failed to add supplier.';
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['toast'] = 'Database error.';
    }

    header("Location: ../supplier.php");
    exit;
}
