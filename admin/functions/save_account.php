<?php
include "../../db.php";

header("Content-Type: application/json");

/* =====================
   CONFIG
===================== */
$defaultPhotoFile = "default.jpg";
$uploadDir = "../../uploads/";

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

    /* Get photo first */
    $stmt = $conn->prepare("SELECT photo FROM accounts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (!empty($row['photo']) && $row['photo'] !== $defaultPhotoFile) {
            $photoPath = $uploadDir . $row['photo'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }
    }
    $stmt->close();

    /* Delete account */
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

    $del->close();
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
   CONTACT FORMAT
   Expected: +639XXXXXXXXX
===================== */
if (!preg_match('/^\+639\d{9}$/', $contact)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid contact format"
    ]);
    exit;
}

/* =====================
   PASSWORD VALIDATION
===================== */
if (!$id && empty($password)) {
    echo json_encode([
        "status" => "error",
        "message" => "Password is required"
    ]);
    exit;
}

if (!empty($password) && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/', $password)) {
    echo json_encode([
        "status" => "error",
        "message" => "Password must be at least 8 characters and include uppercase, lowercase, number, and symbol"
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
    $check->close();
    exit;
}
$check->close();

/* =====================
   GET OLD PHOTO IF EDIT
===================== */
$oldPhoto = $defaultPhotoFile;

if ($id) {
    $oldStmt = $conn->prepare("SELECT photo FROM accounts WHERE id = ?");
    $oldStmt->bind_param("i", $id);
    $oldStmt->execute();
    $oldResult = $oldStmt->get_result();

    if ($oldRow = $oldResult->fetch_assoc()) {
        $oldPhoto = !empty($oldRow['photo']) ? $oldRow['photo'] : $defaultPhotoFile;
    }

    $oldStmt->close();
}

/* =====================
   PHOTO UPLOAD (OPTIONAL)
===================== */
$photoName = $id ? $oldPhoto : $defaultPhotoFile;

if (!empty($_FILES['photo']['name'])) {

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

    /* delete old photo only if editing and old photo is not default */
    if ($id && !empty($oldPhoto) && $oldPhoto !== $defaultPhotoFile) {
        $oldPhotoPath = $uploadDir . $oldPhoto;
        if (file_exists($oldPhotoPath)) {
            unlink($oldPhotoPath);
        }
    }
}

/* =====================
   PASSWORD HASH
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

    $sql = "
        UPDATE accounts SET
            account_id = ?,
            role = ?,
            fname = ?,
            mname = ?,
            lname = ?,
            address = ?,
            contact = ?,
            photo = ?
            $passwordSql
        WHERE id = ?
    ";

    $params = [$accountId, $role, $fname, $mname, $lname, $address, $contact, $photoName];
    $types  = "ssssssss";

    if ($passwordHash) {
        $params[] = $passwordHash;
        $types .= "s";
    }

    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

} else {

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
        "message" => "Database error: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>