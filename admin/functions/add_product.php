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

    /* ==============================
       AUTO-GENERATE PRODUCT CODE
       ============================== */

    // Create prefix from category (Caps → CAP)
    $prefix = strtoupper(substr($category, 0, 3));

    // Get last product code for this prefix
    $codeStmt = $conn->prepare(
        "SELECT product_code 
         FROM products 
         WHERE product_code LIKE CONCAT(?, '%')
         ORDER BY product_code DESC
         LIMIT 1"
    );
    $codeStmt->bind_param("s", $prefix);
    $codeStmt->execute();
    $result = $codeStmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $lastNumber = (int) substr($row['product_code'], 3);
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }

    $product_code = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    $codeStmt->close();

    /* ==============================
       IMAGE UPLOAD
       ============================== */

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

    /* ==============================
       INSERT PRODUCT
       ============================== */

    $stmt = $conn->prepare(
        "INSERT INTO products
        (product_code, brand, name, category, srp, price, quantity, image)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "ssssddis",
        $product_code,
        $brand,
        $name,
        $category,
        $srp,
        $price,
        $qty,
        $imagePath
    );

    if ($stmt->execute()) {
        $_SESSION['toast'] = "Product added successfully! ($product_code)";
    } else {
        $_SESSION['toast'] = "Failed to add product!";
    }

    $stmt->close();
    header("Location: ../inventory.php");
    exit();
}
?>
