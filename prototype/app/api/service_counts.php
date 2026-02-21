<?php
// Simple JSON endpoint to return service request counts for sidebar badges
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$result = ['new_requests' => 0, 'ongoing_scheduled' => 0, 'completed' => 0];
try {
    // New requests: pending or open
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE LOWER(status) IN ('pending','open')");
    $stmt->execute();
    $result['new_requests'] = (int)$stmt->fetchColumn();

    // Ongoing / scheduled
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE LOWER(status) IN ('ongoing','scheduled')");
    $stmt->execute();
    $result['ongoing_scheduled'] = (int)$stmt->fetchColumn();

    // Completed
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE LOWER(status) IN ('completed','done')");
    $stmt->execute();
    $result['completed'] = (int)$stmt->fetchColumn();
} catch (Exception $e) {
    error_log('service_counts error: ' . $e->getMessage());
}

echo json_encode($result);
exit;
