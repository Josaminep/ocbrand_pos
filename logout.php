<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "db.php"; // adjust path if logout.php is inside a folder (ex: ../db.php)

// ✅ update logout time + session seconds if log_id exists
if (!empty($_SESSION["log_id"])) {
    $log_id = (int)$_SESSION["log_id"];

    $stmt = $conn->prepare("
        UPDATE user_logs
        SET logout_at = NOW(),
            session_seconds = TIMESTAMPDIFF(SECOND, login_at, NOW())
        WHERE log_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $log_id);
    $stmt->execute();
}

/* Set logout toast BEFORE destroying session */
$_SESSION['toast'] = [
    'type' => 'success',
    'msg'  => 'You have been logged out successfully'
];

/*
  IMPORTANT:
  We must keep the toast after session_destroy.
  So we store it temporarily, destroy session, then start again and restore toast.
*/
$toast = $_SESSION['toast'];

/* Unset all session variables */
$_SESSION = [];

/* Destroy the session */
session_destroy();

/* Prevent back button cache */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

/* Start fresh session just to carry toast */
session_start();
$_SESSION['toast'] = $toast;

/* Redirect to login / home */
header("Location: home.php");
exit;