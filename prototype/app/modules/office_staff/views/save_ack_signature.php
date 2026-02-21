<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'office_staff') {
  http_response_code(403);
  echo json_encode(['ok' => false, 'msg' => 'Unauthorized']);
  exit;
}

require_once __DIR__ . '/../../../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$request_id = $input['request_id'] ?? null;
$dataURL = $input['signature'] ?? null;

if (empty($request_id) || empty($dataURL)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'msg' => 'Missing request_id or signature']);
  exit;
}

if (!preg_match('/^data:image\/png;base64,/', $dataURL)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'msg' => 'Invalid image format']);
  exit;
}

$base64 = substr($dataURL, strpos($dataURL, ',') + 1);
$binary = base64_decode($base64);

if ($binary === false) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'msg' => 'Base64 decode failed']);
  exit;
}

// Save file
$uploadDir = __DIR__ . '/../../../../public/uploads/ack_signatures/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

$filename = 'ack_' . intval($request_id) . '_' . time() . '.png';
$fullpath = $uploadDir . $filename;

if (file_put_contents($fullpath, $binary) === false) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => 'Failed to write file']);
  exit;
}

// Path to store in DB (relative)
$dbPath = 'public/uploads/ack_signatures/' . $filename;

try {
  $ackBy = $_SESSION['full_name'] ?? 'Office Staff';
  $stmt = $pdo->prepare("UPDATE service_requests
    SET ack_signature_path = ?, acknowledged_at = NOW(), acknowledged_by = ?
    WHERE id = ?");
  $stmt->execute([$dbPath, $ackBy, intval($request_id)]);

  echo json_encode(['ok' => true, 'path' => $dbPath, 'ack_by' => $ackBy]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
