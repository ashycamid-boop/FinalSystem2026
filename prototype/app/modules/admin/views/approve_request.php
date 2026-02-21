<?php
session_start();

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: /prototype/index.php');
    exit;
}

// Only accept POST to approve
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: new_requests.php');
    exit;
}

$id = $_POST['id'];

require_once __DIR__ . '/../../../config/db.php';

try {
    $stmt = $pdo->prepare("UPDATE service_requests SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute(['Ongoing', $id]);
} catch (Exception $e) {
    error_log('approve_request error: ' . $e->getMessage());
}

header('Location: ongoing_scheduled.php');
exit;

?>
