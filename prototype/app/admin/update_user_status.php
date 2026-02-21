<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Set JSON response header
header('Content-Type: application/json');

// Debug: Log all session variables
error_log('DEBUG: Session variables: ' . json_encode($_SESSION));
error_log('DEBUG: Role value: ' . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET'));
error_log('DEBUG: User role value: ' . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'NOT SET'));

// Check if user is logged in and is admin (support both 'role' and 'user_role')
$isAdmin = (
    (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') ||
    (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin')
);

if (!$isAdmin) {
    http_response_code(403);
    error_log('DEBUG: Authorization failed. Role: ' . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET') . ' | User Role: ' . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'NOT SET'));
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - Admin role required']);
    exit;
}

// Check if required parameters are present
if (!isset($_POST['user_id']) || !isset($_POST['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

$userId = $_POST['user_id'];
$action = $_POST['action'];

// Validate action
if (!in_array($action, ['enable', 'disable'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    exit;
}

// Set new status based on action
$newStatus = ($action === 'enable') ? 1 : 0;

try {
    // Update user status
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $result = $stmt->execute([$newStatus, $userId]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'User status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update user status'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
