<?php
include "../../db.php";

header("Content-Type: application/json");

/* =====================
   REQUEST CHECK
===================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
    exit;
}

/* =====================
   DELETE ACCOUNT
===================== */
if (isset($_POST['action']) && $_POST['action'] === 'delete') {

    $id = (int)($_POST['id'] ?? 0);

    if (!$id) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid account ID"
        ]);
        exit;
    }

    // Get photo first
    $stmt = $conn->prepare("SELECT photo FROM accounts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (!empty($row['photo'])) {
            $photoPath = "../../uploads/" . $row['photo'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }
    }

    // Delete account
    $del = $conn->prepare("DELETE FROM accounts WHERE id = ?");
    $del->bind_param("i", $id);

    if ($del->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Account deleted successfully"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to delete account"
        ]);
    }

    exit;
}


/* =====================
   GET FORM DATA
===================== */
$id        = !empty($_POST['id']) ? (int)$_POST['id'] : null;
$accountId = trim($_POST['account_id'] ?? '');
$role      = trim($_POST['role'] ?? '');
$fname     = trim($_POST['fname'] ?? '');
$mname     = trim($_POST['mname'] ?? '');
$lname     = trim($_POST['lname'] ?? '');
$address   = trim($_POST['address'] ?? '');
$contact   = trim($_POST['contact'] ?? '');
$password  = $_POST['password'] ?? '';

/* =====================
   BASIC VALIDATION
===================== */
if (!$accountId || !$role || !$fname || !$lname || !$address || !$contact) {
    echo json_encode([
        "status" => "error",
        "message" => "All required fields must be filled"
    ]);
    exit;
}

/* =====================
   ACCOUNT ID FORMAT
===================== */
if (
    ($role === "admin"   && !preg_match('/^A\d{5}$/', $accountId)) ||
    ($role === "cashier" && !preg_match('/^C\d{5}$/', $accountId))
) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Account ID format"
    ]);
    exit;
}

/* =====================
   CHECK DUPLICATE ACCOUNT ID
===================== */
$sql = "SELECT id FROM accounts WHERE account_id = ?";
$params = [$accountId];
$types = "s";

if ($id) {
    $sql .= " AND id != ?";
    $params[] = $id;
    $types .= "i";
}

$check = $conn->prepare($sql);
$check->bind_param($types, ...$params);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Account ID already exists"
    ]);
    exit;
}

/* =====================
   PHOTO UPLOAD (OPTIONAL)
===================== */
$photoName = null;

if (!empty($_FILES['photo']['name'])) {

    $uploadDir = "../../uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid image format"
        ]);
        exit;
    }

    $photoName = time() . "_" . uniqid() . "." . $ext;

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photoName)) {
        echo json_encode([
            "status" => "error",
            "message" => "Photo upload failed"
        ]);
        exit;
    }
}

/* =====================
   PASSWORD (OPTIONAL)
===================== */
$passwordSql = "";
$passwordHash = null;

if (!empty($password)) {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $passwordSql = ", password = ?";
}

/* =====================
   CREATE OR UPDATE
===================== */
if ($id) {

    // UPDATE ACCOUNT
    $sql = "
        UPDATE accounts SET
            account_id = ?,
            role = ?,
            fname = ?,
            mname = ?,
            lname = ?,
            address = ?,
            contact = ?
            " . ($photoName ? ", photo = ?" : "") . "
            $passwordSql
        WHERE id = ?
    ";

    $params = [$accountId, $role, $fname, $mname, $lname, $address, $contact];
    $types  = "sssssss";

    if ($photoName) {
        $params[] = $photoName;
        $types .= "s";
    }

    if ($passwordHash) {
        $params[] = $passwordHash;
        $types .= "s";
    }

    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

} else {

    // CREATE ACCOUNT
    if (!$photoName || !$password) {
        echo json_encode([
            "status" => "error",
            "message" => "Password and photo are required"
        ]);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO accounts
        (account_id, role, fname, mname, lname, address, contact, password, photo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssssssss",
        $accountId,
        $role,
        $fname,
        $mname,
        $lname,
        $address,
        $contact,
        $passwordHash,
        $photoName
    );
}

/* =====================
   EXECUTE
===================== */
if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => $id ? "Account updated successfully" : "Account created successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Database error"
    ]);
}
