<?php
// Disable notices/warnings from messing up JSON
error_reporting(E_ERROR | E_PARSE);

include "../../db.php";
header('Content-Type: application/json');

// Get POSTed category name
$name = trim($_POST['name'] ?? '');
if($name === ''){
    echo json_encode(['success'=>false,'message'=>'Category name is required']);
    exit;
}

// Check for duplicate
$name_safe = mysqli_real_escape_string($conn, $name);
$check = mysqli_query($conn, "SELECT id FROM categories WHERE name='$name_safe'");
if(mysqli_num_rows($check) > 0){
    echo json_encode(['success'=>false,'message'=>'Category already exists']);
    exit;
}

// Insert into DB
if(mysqli_query($conn, "INSERT INTO categories (name) VALUES ('$name_safe')")){
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'message'=>'Database error']);
}
exit; // Important to stop any further output
