<?php
session_start();
header('Content-Type: application/json');

// Accept role from either `user_role` or `role` session keys and allow
// both Office Staff and Enforcement Officer accounts to save feedback.
$role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? null;
$roleNorm = is_string($role) ? strtolower(trim($role)) : '';
$allowed = ['office_staff', 'office staff', 'enforcement_officer', 'enforcement officer'];
if (empty($role) || !in_array($roleNorm, $allowed, true)) {
  http_response_code(403);
  echo json_encode(['ok' => false, 'msg' => 'Unauthorized']);
  exit;
}

require_once __DIR__ . '/../../../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$request_id = $input['request_id'] ?? null;
$feedback = isset($input['feedback_rating']) ? trim($input['feedback_rating']) : null;
$completed = isset($input['completed']) ? (int)$input['completed'] : null;
$dataURL = $input['signature'] ?? null;

if (empty($request_id)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'msg' => 'Missing request_id']);
  exit;
}

// optional signature handling
$dbPath = null;
if (!empty($dataURL) && preg_match('/^data:image\/png;base64,/', $dataURL)) {
  $base64 = substr($dataURL, strpos($dataURL, ',') + 1);
  $binary = base64_decode($base64);
  if ($binary === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Invalid signature data']);
    exit;
  }

  $uploadDir = __DIR__ . '/../../../../public/uploads/ack_signatures/';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
  $filename = 'ack_' . intval($request_id) . '_' . time() . '.png';
  $fullpath = $uploadDir . $filename;
  if (file_put_contents($fullpath, $binary) === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Failed to write signature file']);
    exit;
  }
  $dbPath = 'public/uploads/ack_signatures/' . $filename;
}

try {
  // inspect columns in service_requests to avoid SQL errors
  $cols = [];
  $desc = $pdo->query("DESCRIBE service_requests");
  foreach ($desc->fetchAll(PDO::FETCH_ASSOC) as $r) $cols[] = $r['Field'];

  // If neither feedback column exists, add `feedback_rating` so we can store values.
  if (!in_array('feedback_rating', $cols, true) && !in_array('feedback', $cols, true)) {
    try {
      $pdo->exec("ALTER TABLE service_requests ADD COLUMN feedback_rating VARCHAR(50) NULL AFTER request_description");
      $cols[] = 'feedback_rating';
    } catch (Exception $e) {
      // if ALTER fails (permissions/etc), continue and return error later when attempting update
      error_log('Could not add feedback_rating column: ' . $e->getMessage());
    }
  }

  $sets = [];
  $params = [];

  if ($feedback !== null) {
    if (in_array('feedback_rating', $cols)) {
      $sets[] = 'feedback_rating = ?';
      $params[] = $feedback;
    } elseif (in_array('feedback', $cols)) {
      $sets[] = 'feedback = ?';
      $params[] = $feedback;
    }
  }

  if ($completed !== null) {
    // store status as 'completed' when true, otherwise keep existing or set to 'open'
    if (in_array('status', $cols)) {
      $sets[] = 'status = ?';
      $params[] = $completed ? 'Completed' : 'Open';
    }
  }

  if ($dbPath !== null && in_array('ack_signature_path', $cols)) {
    $sets[] = 'ack_signature_path = ?';
    $params[] = $dbPath;
    if (in_array('acknowledged_at', $cols)) $sets[] = 'acknowledged_at = NOW()';
    if (in_array('acknowledged_by', $cols)) { $sets[] = 'acknowledged_by = ?'; $params[] = $_SESSION['full_name'] ?? 'Office Staff'; }
  }

  if (!empty($sets)) {
    // build SQL
    $sql = 'UPDATE service_requests SET ' . implode(', ', $sets) . ' WHERE id = ?';
    $params[] = intval($request_id);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
  }

  $out = ['ok' => true];
  if ($dbPath) $out['path'] = $dbPath;
  echo json_encode($out);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}

?>
