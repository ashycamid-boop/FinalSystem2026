<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

try {
    // Group service requests by requester_office (department) and return counts
    $stmt = $pdo->prepare("SELECT COALESCE(requester_office, 'Unknown') AS office, COUNT(*) AS cnt FROM service_requests GROUP BY office ORDER BY cnt DESC");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = [];
    foreach ($rows as $r) {
        $out[] = ['label' => $r['office'], 'count' => (int)$r['cnt']];
    }

    echo json_encode(['success' => true, 'data' => $out]);
    exit;
} catch (Exception $e) {
    error_log('service_requests_by_department error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

?>
