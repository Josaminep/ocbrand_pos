<?php
session_start();
include "../../db.php";

/* UPDATE SUPPLIER */
if (isset($_POST['update_supplier'])) {

    $id     = (int)$_POST['id'];
    $name   = trim($_POST['name'] ?? '');
    $person = trim($_POST['contact_person'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if ($name === '' || $person === '' || $phone === '') {
        $_SESSION['toast'] = 'Please fill in all required fields.';
    } else {

        $sql = "UPDATE suppliers SET
                name = ?,
                contact_person = ?,
                email = ?,
                phone = ?,
                status = ?
                WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssi",
                $name, $person, $email, $phone, $status, $id
            );

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['toast'] = 'Supplier updated successfully';
            } else {
                $_SESSION['toast'] = 'Failed to update supplier.';
            }

            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['toast'] = 'Database error.';
        }
    }
}

/* DELETE SUPPLIER */
if (isset($_POST['delete_id'])) {

    $deleteId = (int)$_POST['delete_id'];

    $stmt = mysqli_prepare($conn, "DELETE FROM suppliers WHERE id = ?");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $deleteId);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['toast'] = 'Supplier deleted successfully';
        } else {
            $_SESSION['toast'] = 'Failed to delete supplier.';
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['toast'] = 'Database error.';
    }
}

header("Location: ../supplier.php");
exit;

?>