<?php
session_start();
header('Content-Type: application/json');

echo json_encode([
    'session_data' => $_SESSION,
    'role' => $_SESSION['role'] ?? 'NOT SET',
    'uid' => $_SESSION['uid'] ?? 'NOT SET',
    'email' => $_SESSION['email'] ?? 'NOT SET'
]);
?>
