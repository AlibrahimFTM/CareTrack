<?php
// Authentication check - include before any page that requires login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $scriptPath = $_SERVER['PHP_SELF'] ?? '';

    if (strpos($scriptPath, '/elderly/') !== false) {
        header('Location: ../elderly/connect.php');
    } else {
        header('Location: ../auth/login.php');
    }

    exit;
}
