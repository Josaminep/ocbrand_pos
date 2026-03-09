<?php
require "../middleware/auth.php";
require "../db.php";

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT fname, email, role, created_at FROM accounts WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
</head>
<body>

<?php include "sidebar.php"; ?>

<div style="margin-left:250px; padding:40px;">
    <h2>My Profile</h2>
    <p><strong>Name:</strong> <?= htmlspecialchars($user['fname']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Role:</strong> <?= ucfirst($user['role']) ?></p>
    <p><strong>Joined:</strong> <?= date("F d, Y", strtotime($user['created_at'])) ?></p>
</div>

</body>
</html>
