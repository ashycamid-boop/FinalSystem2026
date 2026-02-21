<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Check if user is authenticated (optional, add authentication check if needed)

try {
    // Fetch all active users from database
    $stmt = $pdo->prepare("SELECT id, full_name, role, sex FROM users WHERE status = 1 ORDER BY full_name ASC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
