<?php
session_start();
include "../../db.php";

/* =======================
   DELETE PRODUCT
======================= */
if (isset($_POST['delete_id'])) {

    $id = (int) $_POST['delete_id'];

    /* GET IMAGE */
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($img);
    $stmt->fetch();
    $stmt->close();

    if ($img && file_exists("../../" . $img)) {
        unlink("../../" . $img);
    }

    /* DELETE ROW */
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['toast'] = "Product deleted successfully!";
    } else {
        $_SESSION['toast'] = "Failed to delete product!";
    }

    $stmt->close();
    header("Location: ../inventory.php");
    exit;
}

/* =======================
   UPDATE PRODUCT
======================= */
if (isset($_POST['update_product'])) {

    $id    = (int) $_POST['id'];
    $name  = trim($_POST['name']);
    $cat   = $_POST['category'];
    $srp   = (float) $_POST['srp'];
    $price = (float) $_POST['price'];
    $qty   = (int) $_POST['quantity'];

    /* VALIDATION */
    if ($price > $srp) {
        $_SESSION['toast'] = "Selling price cannot be higher than SRP!";
        header("Location: ../inventory.php");
        exit;
    }

    /* IMAGE CHECK */
    if (!empty($_FILES['image']['name'])) {

        $uploadDir = "../../uploads/products/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $newName = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newName);

        /* DELETE OLD IMAGE */
        $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($oldImg);
        $stmt->fetch();
        $stmt->close();

        if ($oldImg && file_exists("../../" . $oldImg)) {
            unlink("../../" . $oldImg);
        }

        $imagePath = "uploads/products/" . $newName;

        /* UPDATE WITH IMAGE */
        $stmt = $conn->prepare(
            "UPDATE products
             SET name=?, category=?, srp=?, price=?, quantity=?, image=?
             WHERE id=?"
        );

        $stmt->bind_param(
            "sssddisi",
            $name,
            $cat,
            $srp,
            $price,
            $qty,
            $imagePath,
            $id
        );

    } else {

        /* UPDATE WITHOUT IMAGE */
        $stmt = $conn->prepare(
            "UPDATE products
             SET name=?, category=?, srp=?, price=?, quantity=?
             WHERE id=?"
        );

        $stmt->bind_param(
            "sssddi",
            $name,
            $cat,
            $srp,
            $price,
            $qty,
            $id
        );
    }

    if ($stmt->execute()) {
        $_SESSION['toast'] = "Product updated successfully!";
    } else {
        $_SESSION['toast'] = "Failed to update product!";
    }

    $stmt->close();
    header("Location: ../inventory.php");
    exit;
}
?>
