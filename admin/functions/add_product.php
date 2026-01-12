<?php
session_start();
include "../../db.php";

if (isset($_POST['add_product'])) {

    $brand    = "OC Brand";
    $name     = trim($_POST['name']);
    $category = $_POST['category'];
    $srp      = (float) $_POST['srp'];
    $price    = (float) $_POST['price'];
    $qty      = (int) $_POST['quantity'];

    /* VALIDATION */
    if ($price > $srp) {
        $_SESSION['toast'] = "Selling price cannot be higher than SRP!";
        header("Location: ../inventory.php");
        exit();
    }

    /* IMAGE UPLOAD */
    $imageName = $_FILES['image']['name'];
    $tmpName   = $_FILES['image']['tmp_name'];

    $uploadDir = "../../uploads/products/";
    $newName   = time() . "_" . basename($imageName);

    if (!move_uploaded_file($tmpName, $uploadDir . $newName)) {
        $_SESSION['toast'] = "Image upload failed!";
        header("Location: ../inventory.php");
        exit();
    }

    $imagePath = "uploads/products/" . $newName;

    /* INSERT PRODUCT */
    $stmt = $conn->prepare(
        "INSERT INTO products (brand, name, category, srp, price, quantity, image)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "sssddis",
        $brand,
        $name,
        $category,
        $srp,
        $price,
        $qty,
        $imagePath
    );

    if ($stmt->execute()) {
        $_SESSION['toast'] = "Product added successfully!";
    } else {
        $_SESSION['toast'] = "Failed to add product!";
    }

    $stmt->close();
    header("Location: ../inventory.php");
    exit();
}
?>
