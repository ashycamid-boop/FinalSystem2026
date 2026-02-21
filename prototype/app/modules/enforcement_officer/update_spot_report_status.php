<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method not allowed']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$ref = isset($data['ref']) ? trim((string)$data['ref']) : '';
$status = isset($data['status']) ? trim((string)$data['status']) : '';
// Accept either `comment` or legacy `comments` from clients
$comments = null;
if (isset($data['comment'])) $comments = trim((string)$data['comment']);
elseif (isset($data['comments'])) $comments = trim((string)$data['comments']);

if ($ref === '' || $status === '') {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Missing parameters']);
  exit;
}

// Ensure role constants are available and accept mapped role values
require_once dirname(dirname(__DIR__)) . '/config/role_permissions.php';

$userRole = trim((string)($_SESSION['user_role'] ?? ''));
$allowed = [
  RolePermissions::ADMIN,
  RolePermissions::ENFORCEMENT_OFFICER,
  RolePermissions::ENFORCER,
];

// Also accept legacy/display role strings for backward compatibility
$legacyAllowed = ['admin', 'enforcement officer', 'enforcer', 'property custodian', 'office staff'];
if (!in_array($userRole, $allowed, true) && !in_array(strtolower($userRole), $legacyAllowed, true)) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Forbidden']);
  exit;
}

require_once dirname(dirname(__DIR__)) . '/config/db.php'; // loads $pdo

try {
  if ($comments !== null) {
    $stmt = $pdo->prepare('UPDATE spot_reports SET status = :status, status_comment = :comment, updated_at = NOW() WHERE reference_no = :ref');
    $stmt->execute([':status' => $status, ':comment' => $comments, ':ref' => $ref]);
  } else {
    $stmt = $pdo->prepare('UPDATE spot_reports SET status = :status, updated_at = NOW() WHERE reference_no = :ref');
    $stmt->execute([':status' => $status, ':ref' => $ref]);
  }

  // Optional: log to audit_logs table if exists
  if (isset($_SESSION['uid']) && !empty($pdo)) {
    try {
      $logStmt = $pdo->prepare('INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values, ip_address, user_agent) VALUES (:uid, :action, :table, :rid, :newv, :ip, :ua)');
      $logStmt->execute([
        ':uid' => $_SESSION['uid'],
        ':action' => 'update_status',
        ':table' => 'spot_reports',
        ':rid' => $ref,
        ':newv' => json_encode(['status' => $status, 'comments' => $comments]),
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
      ]);
    } catch (Exception $e) {
      // ignore audit logging errors
    }
  }

  echo json_encode(['success' => true, 'status' => $status]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Database error']);
}

exit;
