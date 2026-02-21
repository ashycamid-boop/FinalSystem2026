<?php
header('Content-Type: application/json');

try {
    require_once dirname(dirname(dirname(__DIR__))) . '/config/db.php'; // loads $pdo
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB config not found']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$type = $input['type'] ?? '';
$id = (int)($input['id'] ?? 0);
$status = trim($input['status'] ?? '');
$status_key = trim($input['status_key'] ?? '');

if (!$type || !$id || (!$status && !$status_key)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit;
}

try {
    if ($type === 'vehicle') {
        $stmt = $pdo->prepare("UPDATE spot_report_vehicles SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $id]);
    } elseif ($type === 'item') {
        $stmt = $pdo->prepare("UPDATE spot_report_items SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $id]);
    } elseif ($type === 'case') {
        $stmt = $pdo->prepare("UPDATE spot_reports SET case_status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $id]);
    } elseif ($type === 'person') {
        // If persons table has 'status' column
        $stmt = $pdo->prepare("UPDATE spot_report_persons SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $id]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown type']);
        exit;
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
