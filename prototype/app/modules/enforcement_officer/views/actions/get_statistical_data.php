<?php
declare(strict_types=1);
header('Content-Type: application/json');

try {
  $dbPath = dirname(__DIR__, 4) . '/config/db.php';
  if (!file_exists($dbPath)) throw new Exception('db.php not found');
  require_once $dbPath; // provides $pdo
  if (!isset($pdo)) throw new Exception('PDO not available');

  $months = isset($_GET['months']) ? (int)$_GET['months'] : 24;
  if ($months < 1) $months = 24;
  $months = min($months, 60);

  // Build labels for last $months months (YYYY-MM)
  $labels = [];
  $startTs = strtotime(date('Y-m-01') . ' -' . ($months - 1) . ' months');
  for ($i = 0; $i < $months; $i++) {
    $ts = strtotime('+' . $i . ' months', $startTs);
    $labels[] = date('Y-m', $ts);
  }
  $startDate = date('Y-m-01 00:00:00', $startTs);

  // Session may hold current user id/email; attempt to load it for per-user counts
  if (session_status() === PHP_SESSION_NONE) {
    @session_start();
  }
  $sessionUserId = $_SESSION['uid'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
  $sessionUserEmail = $_SESSION['email'] ?? null;

  // Helper to zero-fill series
  $fill = function(array $rows) use ($labels) {
    $map = [];
    foreach ($rows as $r) $map[$r['ym']] = (int)$r['cnt'];
    $out = [];
    foreach ($labels as $l) $out[] = $map[$l] ?? 0;
    return $out;
  };

  // Spot reports time series (by created_at)
  $stmt = $pdo->prepare("SELECT DATE_FORMAT(COALESCE(created_at, NOW()), '%Y-%m') AS ym, COUNT(*) AS cnt FROM spot_reports WHERE COALESCE(created_at, NOW()) >= ? GROUP BY ym ORDER BY ym ASC");
  $stmt->execute([$startDate]);
  $spotRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $spotSeries = $fill($spotRows);

  // Cases (approved spot_reports) - exclude reports whose case_status is 'resolved' (closed)
  $stmt = $pdo->prepare("SELECT DATE_FORMAT(COALESCE(created_at, NOW()), '%Y-%m') AS ym, COUNT(*) AS cnt FROM spot_reports WHERE LOWER(TRIM(COALESCE(status,''))) = 'approved' AND LOWER(TRIM(COALESCE(case_status,''))) != 'resolved' AND COALESCE(created_at, NOW()) >= ? GROUP BY ym ORDER BY ym ASC");
  $stmt->execute([$startDate]);
  $caseRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $caseSeries = $fill($caseRows);

  // Apprehended: persons/vehicles/items (counted by parent report created_at for approved reports)
  $stmt = $pdo->prepare("SELECT DATE_FORMAT(r.created_at, '%Y-%m') AS ym, COUNT(*) AS cnt FROM spot_report_persons p JOIN spot_reports r ON r.id = p.report_id WHERE LOWER(TRIM(COALESCE(r.status,''))) = 'approved' AND r.created_at >= ? GROUP BY ym ORDER BY ym ASC");
  $stmt->execute([$startDate]);
  $personRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $personsSeries = $fill($personRows);

  $stmt = $pdo->prepare("SELECT DATE_FORMAT(r.created_at, '%Y-%m') AS ym, COUNT(*) AS cnt FROM spot_report_vehicles v JOIN spot_reports r ON r.id = v.report_id WHERE LOWER(TRIM(COALESCE(r.status,''))) = 'approved' AND r.created_at >= ? GROUP BY ym ORDER BY ym ASC");
  $stmt->execute([$startDate]);
  $vehRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $vehiclesSeries = $fill($vehRows);

  $stmt = $pdo->prepare("SELECT DATE_FORMAT(r.created_at, '%Y-%m') AS ym, COUNT(*) AS cnt FROM spot_report_items i JOIN spot_reports r ON r.id = i.report_id WHERE LOWER(TRIM(COALESCE(r.status,''))) = 'approved' AND r.created_at >= ? GROUP BY ym ORDER BY ym ASC");
  $stmt->execute([$startDate]);
  $itemRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $itemsSeries = $fill($itemRows);

  // Breakdown: persons by role
  $stmt = $pdo->prepare("SELECT COALESCE(NULLIF(TRIM(p.role),''),'Unknown') AS role, COUNT(*) AS cnt FROM spot_report_persons p JOIN spot_reports r ON r.id = p.report_id WHERE LOWER(TRIM(COALESCE(r.status,''))) = 'approved' AND r.created_at >= ? GROUP BY role ORDER BY cnt DESC");
  $stmt->execute([$startDate]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $rolesBy = [];
  foreach ($rows as $r) { $rolesBy[$r['role']] = (int)$r['cnt']; }

  // Breakdown: persons by gender
  $stmt = $pdo->prepare("SELECT COALESCE(NULLIF(TRIM(p.gender),''),'Unknown') AS gender, COUNT(*) AS cnt FROM spot_report_persons p JOIN spot_reports r ON r.id = p.report_id WHERE LOWER(TRIM(COALESCE(r.status,''))) = 'approved' AND r.created_at >= ? GROUP BY gender ORDER BY cnt DESC");
  $stmt->execute([$startDate]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $genderBy = [];
  foreach ($rows as $r) { $genderBy[$r['gender']] = (int)$r['cnt']; }

  // Vehicles by make (no explicit status column on vehicles table)
  $stmt = $pdo->prepare("SELECT COALESCE(NULLIF(TRIM(v.make),''),'Unknown') AS make, COUNT(*) AS cnt FROM spot_report_vehicles v JOIN spot_reports r ON r.id = v.report_id WHERE LOWER(TRIM(COALESCE(r.status,''))) = 'approved' AND r.created_at >= ? GROUP BY make ORDER BY cnt DESC");
  $stmt->execute([$startDate]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $vehiclesByMake = [];
  foreach ($rows as $r) { $vehiclesByMake[$r['make']] = (int)$r['cnt']; }

  // Items by type
  $stmt = $pdo->prepare("SELECT COALESCE(NULLIF(TRIM(i.type),''),'Unknown') AS type, COUNT(*) AS cnt FROM spot_report_items i JOIN spot_reports r ON r.id = i.report_id WHERE LOWER(TRIM(COALESCE(r.status,''))) = 'approved' AND r.created_at >= ? GROUP BY type ORDER BY cnt DESC");
  $stmt->execute([$startDate]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $itemsByType = [];
  foreach ($rows as $r) { $itemsByType[$r['type']] = (int)$r['cnt']; }

  // Spot by status
  $rows = $pdo->query("SELECT LOWER(TRIM(COALESCE(status,''))) AS status, COUNT(*) AS cnt FROM spot_reports GROUP BY LOWER(TRIM(COALESCE(status,'')))")->fetchAll(PDO::FETCH_ASSOC);
  $spotBy = [];
  foreach ($rows as $r) { $k = $r['status'] !== '' ? $r['status'] : 'unknown'; $spotBy[$k] = (int)$r['cnt']; }

  // Case statuses (case_status on approved reports)
  $rows = $pdo->query("SELECT LOWER(TRIM(COALESCE(case_status,''))) AS status, COUNT(*) AS cnt FROM spot_reports WHERE LOWER(TRIM(COALESCE(status,''))) = 'approved' GROUP BY LOWER(TRIM(COALESCE(case_status,'')))")->fetchAll(PDO::FETCH_ASSOC);
  $caseBy = [];
  foreach ($rows as $r) { $k = $r['status'] !== '' ? $r['status'] : 'unknown'; $caseBy[$k] = (int)$r['cnt']; }

  // Service requests by status
  $rows = $pdo->query("SELECT LOWER(TRIM(COALESCE(status,''))) AS status, COUNT(*) AS cnt FROM service_requests GROUP BY LOWER(TRIM(COALESCE(status,'')))")->fetchAll(PDO::FETCH_ASSOC);
  $svcBy = [];
  foreach ($rows as $r) { $k = $r['status'] !== '' ? $r['status'] : 'unknown'; $svcBy[$k] = (int)$r['cnt']; }

  // Per-user service request counts (if session user id or email available)
  $svcByUser = [];
  // initialize svcByUser with overall status keys (defaults to 0) so frontend can rely on it
  foreach ($svcBy as $k => $v) { $svcByUser[$k] = 0; }
  if (!empty($sessionUserId) || !empty($sessionUserEmail)) {
    if (!empty($sessionUserId)) {
      $stmt = $pdo->prepare("SELECT LOWER(TRIM(COALESCE(status,''))) AS status, COUNT(*) AS cnt FROM service_requests WHERE created_by = ? GROUP BY LOWER(TRIM(COALESCE(status,'')))");
      $stmt->execute([$sessionUserId]);
    } else {
      $stmt = $pdo->prepare("SELECT LOWER(TRIM(COALESCE(status,''))) AS status, COUNT(*) AS cnt FROM service_requests WHERE LOWER(TRIM(COALESCE(requester_email,''))) = LOWER(TRIM(?)) GROUP BY LOWER(TRIM(COALESCE(status,'')))");
      $stmt->execute([$sessionUserEmail]);
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) { $k = $r['status'] !== '' ? $r['status'] : 'unknown'; $svcByUser[$k] = (int)$r['cnt']; }
  }

  // Service requests by type (top 10)
  $rows = $pdo->query("SELECT COALESCE(request_type,'Unknown') AS type, COUNT(*) AS cnt FROM service_requests GROUP BY COALESCE(request_type,'Unknown') ORDER BY cnt DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
  $svcTypes = [];
  foreach ($rows as $r) { $svcTypes[] = ['label'=>$r['type'],'count'=>(int)$r['cnt']]; }

  echo json_encode([
    'ok' => true,
    'labels' => $labels,
    'spot' => ['series' => $spotSeries, 'by_status' => $spotBy],
    'cases' => ['series' => $caseSeries, 'by_status' => $caseBy],
    'apprehended' => ['persons' => $personsSeries, 'vehicles' => $vehiclesSeries, 'items' => $itemsSeries],
    'service_requests' => ['by_status' => $svcBy, 'by_status_user' => $svcByUser, 'by_type' => $svcTypes],
    'breakdowns' => [
      'roles' => $rolesBy,
      'genders' => $genderBy,
      'vehicles_make' => $vehiclesByMake,
      'items_type' => $itemsByType
    ]
  ]);
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
  exit;
}
