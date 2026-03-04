<?php
// Server-side handler for changing a user's password
// Location: app/modules/admin/controllers/change_password.php

// Start session (keep cookie flags simple; auth pages set them earlier during login)
session_start();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /prototype/app/modules/admin/views/change_password.php');
    exit;
}

// Require DB (this file lives in app/modules/admin/controllers)
require_once dirname(dirname(dirname(__DIR__))) . '/config/db.php'; // loads $pdo and defines BASE_URL

$userId = $_SESSION['uid'] ?? null;
if (!$userId) {
    $_SESSION['cp_message'] = 'You must be logged in to change your password.';
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/app/modules/admin/views/change_password.php');
    exit;
}

$current = $_POST['current_password'] ?? '';
$new     = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($new !== $confirm) {
    $_SESSION['cp_message'] = 'New password and confirm password do not match.';
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/app/modules/admin/views/change_password.php');
    exit;
}

if (strlen($new) < 8) {
    $_SESSION['cp_message'] = 'New password must be at least 8 characters.';
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/app/modules/admin/views/change_password.php');
    exit;
}

try {
    // Fetch current hash
    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $row = $stmt->fetch();

    if (!$row) {
        $_SESSION['cp_message'] = 'User not found.';
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/app/modules/admin/views/change_password.php');
        exit;
    }

    $currentHash = $row['password_hash'] ?? '';
    if (!password_verify($current, $currentHash)) {
        $_SESSION['cp_message'] = 'Current password is incorrect.';
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/app/modules/admin/views/change_password.php');
        exit;
    }

    // Hash new password and update
    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $upd = $pdo->prepare('UPDATE users SET password_hash = :ph WHERE id = :id');
    $upd->execute([':ph' => $newHash, ':id' => $userId]);

    $_SESSION['cp_message'] = 'Password changed successfully.';
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/app/modules/admin/views/change_password.php');
    exit;

} catch (Exception $e) {
    error_log('Change password error: ' . $e->getMessage());
    $_SESSION['cp_message'] = 'An error occurred while changing the password. Please try again.';
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/app/modules/admin/views/change_password.php');
    exit;
}

?>
