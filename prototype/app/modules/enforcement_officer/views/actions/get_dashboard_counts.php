<?php
declare(strict_types=1);
header('Content-Type: application/json');

try {
  // actions/ is one level deeper than your view files
  // so go 4 levels up to reach app and then /config/db.php
  $dbPath = dirname(__DIR__, 4) . '/config/db.php';
  if (!file_exists($dbPath)) {
    throw new Exception("db.php not found at: " . $dbPath);
  }

  require_once $dbPath; // loads $pdo
  if (!isset($pdo)) {
    throw new Exception("PDO connection ($pdo) not available from db.php");
  }

  // ---------- Spot Reports ----------
  $spotTotal = (int)$pdo->query("SELECT COUNT(*) FROM spot_reports")->fetchColumn();

  $spotByStmt = $pdo->query(
    "SELECT LOWER(TRIM(COALESCE(status,''))) AS status, COUNT(*) AS cnt
     FROM spot_reports
     GROUP BY LOWER(TRIM(COALESCE(status,'')))"
  );
  $spotBy = [];
  foreach ($spotByStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $k = $r['status'] !== '' ? $r['status'] : 'unknown';
    $spotBy[$k] = (int)$r['cnt'];
  }

  // ---------- Case Management (approved spot reports only) ----------
  $caseTotal = (int)$pdo->query(
    "SELECT COUNT(*) FROM spot_reports
     WHERE LOWER(TRIM(COALESCE(status,''))) = 'approved'"
  )->fetchColumn();

  $caseByStmt = $pdo->query(
    "SELECT LOWER(TRIM(COALESCE(case_status,''))) AS status, COUNT(*) AS cnt
     FROM spot_reports
     WHERE LOWER(TRIM(COALESCE(status,''))) = 'approved'
     GROUP BY LOWER(TRIM(COALESCE(case_status,'')))"
  );
  $caseBy = [];
  foreach ($caseByStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $k = $r['status'] !== '' ? $r['status'] : 'unknown';
    $caseBy[$k] = (int)$r['cnt'];
  }

  // ---------- Apprehended (only from approved spot reports) ----------
  $persons = (int)$pdo->query(
    "SELECT COUNT(*)
     FROM spot_report_persons p
     JOIN spot_reports r ON r.id = p.report_id
     WHERE LOWER(TRIM(COALESCE(r.status,''))) = 'approved'"
  )->fetchColumn();

  $vehicles = (int)$pdo->query(
    "SELECT COUNT(*)
     FROM spot_report_vehicles v
     JOIN spot_reports r ON r.id = v.report_id
     WHERE LOWER(TRIM(COALESCE(r.status,''))) = 'approved'"
  )->fetchColumn();

  $items = (int)$pdo->query(
    "SELECT COUNT(*)
     FROM spot_report_items i
     JOIN spot_reports r ON r.id = i.report_id
     WHERE LOWER(TRIM(COALESCE(r.status,''))) = 'approved'"
  )->fetchColumn();

  echo json_encode([
    "ok" => true,
    "spot_reports" => ["total" => $spotTotal, "by_status" => $spotBy],
    "case_statuses" => ["total" => $caseTotal, "by_status" => $caseBy],
    "apprehended" => [
      "persons" => $persons,
      "vehicles" => $vehicles,
      "items" => $items,
      "total" => ($persons + $vehicles + $items)
    ]
  ]);
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok" => false, "error" => $e->getMessage()]);
  exit;
}
