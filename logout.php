<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* Set logout toast BEFORE destroying session */
$_SESSION['toast'] = [
    'type' => 'success',
    'msg'  => 'You have been logged out successfully'
];

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

/* Redirect to login / home */
header("Location: home.php");
exit;
