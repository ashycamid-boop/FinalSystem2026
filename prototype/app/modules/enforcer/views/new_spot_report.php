<?php
session_start();

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'enforcer') {
    header('Location: /prototype/index.php');
    exit;
}

// Server-side handler to save spot report as JSON and store uploaded files.
// Saves JSON to storage/spot_reports/{ref}.json and files to public/uploads/spot_reports/{ref}/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once dirname(dirname(dirname(__DIR__))) . '/config/db.php'; // loads $pdo

  // Determine public uploads base robustly. Prefer DOCUMENT_ROOT when available.
  $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) : '';
  error_log('SpotReport: DOCUMENT_ROOT=' . $docRoot);
  $publicRoot = '';
  if ($docRoot && is_dir($docRoot)) {
    // If document root already points to public (has uploads), use it
    if (is_dir($docRoot . DIRECTORY_SEPARATOR . 'uploads')) {
      $publicRoot = $docRoot;
    } elseif (is_dir($docRoot . DIRECTORY_SEPARATOR . 'public')) {
      $publicRoot = $docRoot . DIRECTORY_SEPARATOR . 'public';
    }
  }
  // Fallback to previous heuristic if document root wasn't helpful
  if ($publicRoot === '') {
    $publicRoot = realpath(__DIR__ . '/../../../../public');
    if ($publicRoot === false) {
      $publicRoot = dirname(dirname(dirname(dirname(__DIR__))));
      $publicRoot .= DIRECTORY_SEPARATOR . 'public';
    }
  }

  $uploadBase = $publicRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'spot_reports';
  if (!is_dir($uploadBase)) {
    // create with permissive mode for development; tighten in production
    @mkdir($uploadBase, 0777, true);
  }
  error_log(sprintf('SpotReport uploadBase=%s exists=%s writable=%s', $uploadBase, is_dir($uploadBase) ? '1' : '0', is_writable($uploadBase) ? '1' : '0'));

  // Generate server-side reference number with format YYYY-MM-DD-0001 (sequence per day)
  $today = date('Y-m-d');
  $base = $today . '-';
  // count existing today refs to start sequence
  $countStmt = $pdo->prepare('SELECT COUNT(*) AS c FROM spot_reports WHERE reference_no LIKE ?');
  $countStmt->execute([$base . '%']);
  $cntRow = $countStmt->fetch(PDO::FETCH_ASSOC);
  $seq = ($cntRow && isset($cntRow['c'])) ? ((int)$cntRow['c'] + 1) : 1;
  // ensure uniqueness by incrementing if collision found
  $checkStmt = $pdo->prepare('SELECT 1 FROM spot_reports WHERE reference_no = ?');
  while (true) {
    $ref = $base . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    $checkStmt->execute([$ref]);
    if (!$checkStmt->fetch()) break;
    $seq++;
  }

  $incident_datetime = isset($_POST['incident_datetime']) ? $_POST['incident_datetime'] : null;
  $memo_date = isset($_POST['memo_date']) ? $_POST['memo_date'] : null;
  $location = isset($_POST['location']) ? trim((string)$_POST['location']) : '';
  $summary = isset($_POST['summary']) ? trim((string)$_POST['summary']) : '';
  $team_leader = isset($_POST['team_leader']) ? trim((string)$_POST['team_leader']) : '';
  $custodian = isset($_POST['custodian']) ? trim((string)$_POST['custodian']) : '';
  $status = ((isset($_POST['action']) ? $_POST['action'] : '') === 'save_draft') ? 'Draft' : 'Pending';
  $sessionUserId = $_SESSION['uid'] ?? $_SESSION['id'] ?? null;

  // Keep posted values for redisplay if validation fails
  $old = $_POST;

  // Server-side validation for required fields
  $errors = array();
  if (!$incident_datetime) $errors[] = 'Incident date & time is required.';
  if (!$memo_date) $errors[] = 'Memo date is required.';
  if ($location === '') $errors[] = 'Location is required.';
  if ($summary === '') $errors[] = 'Summary is required.';
  if ($team_leader === '') $errors[] = 'Team leader is required.';
  if ($custodian === '') $errors[] = 'Custodian is required.';

  if (empty($errors)) {
    try {
      $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO spot_reports (reference_no, incident_datetime, memo_date, location, summary, team_leader, custodian, status, submitted_by, created_at) VALUES (:ref, :incident, :memo, :location, :summary, :team_leader, :custodian, :status, :submitted_by, NOW())');
    $stmt->execute([
      ':ref' => $ref,
      ':incident' => $incident_datetime ?: null,
      ':memo' => $memo_date ?: null,
      ':location' => $location,
      ':summary' => $summary,
      ':team_leader' => $team_leader,
      ':custodian' => $custodian,
      ':status' => $status,
      ':submitted_by' => $sessionUserId ?: null,
    ]);

    $reportId = (int)$pdo->lastInsertId();

    // persons
    $names = isset($_POST['person_name']) ? $_POST['person_name'] : array();
    $ages = isset($_POST['person_age']) ? $_POST['person_age'] : array();
    $genders = isset($_POST['person_gender']) ? $_POST['person_gender'] : array();
    $addresses = isset($_POST['person_address']) ? $_POST['person_address'] : array();
    $contacts = isset($_POST['person_contact']) ? $_POST['person_contact'] : array();
    $roles = isset($_POST['person_role']) ? $_POST['person_role'] : array();
    $pstatuses = isset($_POST['person_status']) ? $_POST['person_status'] : array();

    $pStmt = $pdo->prepare('INSERT INTO spot_report_persons (report_id, name, age, gender, address, contact, role, status) VALUES (:rid, :name, :age, :gender, :address, :contact, :role, :status)');
    for ($i = 0; $i < count($names); $i++) {
      $n = trim((string)(isset($names[$i]) ? $names[$i] : ''));
      $a = trim((string)(isset($ages[$i]) ? $ages[$i] : ''));
      $g = trim((string)(isset($genders[$i]) ? $genders[$i] : ''));
      $ad = trim((string)(isset($addresses[$i]) ? $addresses[$i] : ''));
      $c = trim((string)(isset($contacts[$i]) ? $contacts[$i] : ''));
      $r = trim((string)(isset($roles[$i]) ? $roles[$i] : ''));
      $ps = trim((string)(isset($pstatuses[$i]) ? $pstatuses[$i] : ''));
      if ($n === '' && $ad === '') continue;
      $pStmt->execute([':rid' => $reportId, ':name' => $n, ':age' => $a, ':gender' => $g, ':address' => $ad, ':contact' => $c, ':role' => $r, ':status' => $ps]);
    }

    // vehicles
    $plates = isset($_POST['vehicle_plate']) ? $_POST['vehicle_plate'] : array();
    $makes = isset($_POST['vehicle_make']) ? $_POST['vehicle_make'] : array();
    $custom_makes = isset($_POST['vehicle_make_custom']) ? $_POST['vehicle_make_custom'] : array();
    $colors = isset($_POST['vehicle_color']) ? $_POST['vehicle_color'] : array();
    $owners = isset($_POST['vehicle_owner']) ? $_POST['vehicle_owner'] : array();
    $vcontacts = isset($_POST['vehicle_contact']) ? $_POST['vehicle_contact'] : array();
    $engines = isset($_POST['vehicle_engine']) ? $_POST['vehicle_engine'] : array();
    $vehicle_remarks = isset($_POST['vehicle_remarks']) ? $_POST['vehicle_remarks'] : array();
    $vehicle_status = isset($_POST['vehicle_status']) ? $_POST['vehicle_status'] : array();

    $vStmt = $pdo->prepare('INSERT INTO spot_report_vehicles (report_id, plate, make, color, owner, contact, engine, status, remarks) VALUES (:rid, :plate, :make, :color, :owner, :contact, :engine, :status, :remarks)');
    for ($i = 0; $i < count($plates); $i++) {
      $pl = trim((string)(isset($plates[$i]) ? $plates[$i] : ''));
      $ow = trim((string)(isset($owners[$i]) ? $owners[$i] : ''));
      if ($pl === '' && $ow === '') continue;
      $vrem = trim((string)(isset($vehicle_remarks[$i]) ? $vehicle_remarks[$i] : ''));
      $vstat = trim((string)(isset($vehicle_status[$i]) ? $vehicle_status[$i] : ''));
      $vStmt->execute([
        ':rid' => $reportId,
        ':plate' => $pl,
        ':make' => (function() use ($makes, $custom_makes, $i) {
          $selected = trim((string)(isset($makes[$i]) ? $makes[$i] : ''));
          $custom = trim((string)(isset($custom_makes[$i]) ? $custom_makes[$i] : ''));
          return ($selected === '__custom__') ? $custom : $selected;
        })(),
        ':color' => trim((string)(isset($colors[$i]) ? $colors[$i] : '')),
        ':owner' => $ow,
        ':contact' => trim((string)(isset($vcontacts[$i]) ? $vcontacts[$i] : '')),
        ':engine' => trim((string)(isset($engines[$i]) ? $engines[$i] : '')),
        ':status' => $vstat,
        ':remarks' => $vrem
      ]);
    }

    // items
    $item_nos = isset($_POST['item_no']) ? $_POST['item_no'] : array();
    $item_types = isset($_POST['item_type']) ? $_POST['item_type'] : array();
    $item_descs = isset($_POST['item_description']) ? $_POST['item_description'] : array();
    $item_qty = isset($_POST['item_quantity']) ? $_POST['item_quantity'] : array();
    $item_vol = isset($_POST['item_volume']) ? $_POST['item_volume'] : array();
    $item_val = isset($_POST['item_value']) ? $_POST['item_value'] : array();
    $item_rem = isset($_POST['item_remarks']) ? $_POST['item_remarks'] : array();
    $item_status = isset($_POST['item_status']) ? $_POST['item_status'] : array();

    $iStmt = $pdo->prepare('INSERT INTO spot_report_items (report_id, item_no, type, description, quantity, volume, value, remarks, status) VALUES (:rid, :no, :type, :description, :quantity, :volume, :value, :remarks, :status)');
    for ($i = 0; $i < count($item_nos); $i++) {
      $desc = trim((string)(isset($item_descs[$i]) ? $item_descs[$i] : ''));
      if ($desc === '') continue;
      $iStmt->execute([
        ':rid' => $reportId,
        ':no' => trim((string)(isset($item_nos[$i]) ? $item_nos[$i] : '')),
        ':type' => trim((string)(isset($item_types[$i]) ? $item_types[$i] : '')),
        ':description' => $desc,
        ':quantity' => trim((string)(isset($item_qty[$i]) ? $item_qty[$i] : '')),
        ':volume' => trim((string)(isset($item_vol[$i]) ? $item_vol[$i] : '')),
        ':value' => (isset($item_val[$i]) && $item_val[$i] !== '' ? $item_val[$i] : null),
        ':remarks' => trim((string)(isset($item_rem[$i]) ? $item_rem[$i] : '')),
        ':status' => trim((string)(isset($item_status[$i]) ? $item_status[$i] : ''))
      ]);
    }

    // files
    $safeRef = preg_replace('/[^a-zA-Z0-9\-_.]/', '_', $ref);
    $uploadDir = $uploadBase . '/' . $safeRef;
    if (!is_dir($uploadDir)) {
      // create with permissive mode for development; tighten in production
      @mkdir($uploadDir, 0777, true);
    }
    error_log(sprintf('SpotReport uploadDir=%s exists=%s writable=%s', $uploadDir, is_dir($uploadDir) ? '1' : '0', is_writable($uploadDir) ? '1' : '0'));

    // Debug: log incoming files and environment for troubleshooting upload issues
    error_log('SpotReport upload - $_FILES: ' . print_r($_FILES, true));
    error_log(sprintf('SpotReport uploadBase=%s exists=%s writable=%s', $uploadBase, is_dir($uploadBase) ? '1' : '0', is_writable($uploadBase) ? '1' : '0'));
    error_log(sprintf('SpotReport uploadDir=%s exists=%s writable=%s', $uploadDir, is_dir($uploadDir) ? '1' : '0', is_writable($uploadDir) ? '1' : '0'));
    error_log('PHP settings: file_uploads=' . ini_get('file_uploads') . ', upload_max_filesize=' . ini_get('upload_max_filesize') . ', post_max_size=' . ini_get('post_max_size') . ', max_file_uploads=' . ini_get('max_file_uploads') . ', memory_limit=' . ini_get('memory_limit'));
    error_log('open_basedir=' . ini_get('open_basedir'));

    function uploadErrorText($code) {
      $map = [
        UPLOAD_ERR_OK => 'OK',
        UPLOAD_ERR_INI_SIZE => 'INI_SIZE (upload_max_filesize)',
        UPLOAD_ERR_FORM_SIZE => 'FORM_SIZE (MAX_FILE_SIZE)',
        UPLOAD_ERR_PARTIAL => 'PARTIAL',
        UPLOAD_ERR_NO_FILE => 'NO_FILE',
        UPLOAD_ERR_NO_TMP_DIR => 'NO_TMP_DIR',
        UPLOAD_ERR_CANT_WRITE => 'CANT_WRITE',
        UPLOAD_ERR_EXTENSION => 'EXTENSION_BLOCKED',
      ];
      return isset($map[$code]) ? $map[$code] : ('UNKNOWN_' . $code);
    }

    // handle per-row evidence files (persons, vehicles, items)
    $perFileStmt = $pdo->prepare('INSERT INTO spot_report_files (report_id, file_type, file_path, orig_name) VALUES (:rid, :type, :path, :orig)');

    if (!empty($_FILES['person_evidence']) && is_array($_FILES['person_evidence']['name'])) {
      for ($i = 0; $i < count($_FILES['person_evidence']['name']); $i++) {
        $err = isset($_FILES['person_evidence']['error'][$i]) ? $_FILES['person_evidence']['error'][$i] : UPLOAD_ERR_NO_FILE;
        if ($err !== UPLOAD_ERR_OK) {
          error_log(sprintf('person_evidence[%d] upload error=%s (%s)', $i, $err, uploadErrorText($err)));
          continue;
        }
        $orig = basename($_FILES['person_evidence']['name'][$i]);
        $ext = pathinfo($orig, PATHINFO_EXTENSION);
        $filename = uniqid('person_') . '.' . $ext;
        $target = $uploadDir . '/' . $filename;
        $tmp = isset($_FILES['person_evidence']['tmp_name'][$i]) ? $_FILES['person_evidence']['tmp_name'][$i] : '';
        if (move_uploaded_file($tmp, $target)) {
          $webPath = '/uploads/spot_reports/' . $safeRef . '/' . $filename;
          $perFileStmt->execute([':rid' => $reportId, ':type' => 'person_evidence', ':path' => $webPath, ':orig' => 'person#' . $i . ':' . $orig]);
        } else {
          if ($tmp && file_exists($tmp)) {
            // Attempt fallback: rename or copy
            if (@rename($tmp, $target) || (@copy($tmp, $target) && @unlink($tmp))) {
              error_log(sprintf('person_evidence[%d] moved via fallback rename/copy tmp=%s target=%s', $i, $tmp, $target));
              $webPath = '/uploads/spot_reports/' . $safeRef . '/' . $filename;
              $perFileStmt->execute([':rid' => $reportId, ':type' => 'person_evidence', ':path' => $webPath, ':orig' => 'person#' . $i . ':' . $orig]);
            } else {
              error_log(sprintf('Failed to move (and fallback) person_evidence tmp=%s target=%s is_uploaded=%s err=%s', $tmp, $target, is_uploaded_file($tmp) ? '1' : '0', isset($_FILES['person_evidence']['error'][$i]) ? $_FILES['person_evidence']['error'][$i] : 'n/a'));
            }
          } else {
            error_log(sprintf('Failed to move person_evidence tmp missing=%s target=%s err=%s', $tmp, $target, isset($_FILES['person_evidence']['error'][$i]) ? $_FILES['person_evidence']['error'][$i] : 'n/a'));
          }
        }
      }
    }

    if (!empty($_FILES['vehicle_evidence']) && is_array($_FILES['vehicle_evidence']['name'])) {
      for ($i = 0; $i < count($_FILES['vehicle_evidence']['name']); $i++) {
        $err = isset($_FILES['vehicle_evidence']['error'][$i]) ? $_FILES['vehicle_evidence']['error'][$i] : UPLOAD_ERR_NO_FILE;
        if ($err !== UPLOAD_ERR_OK) {
          error_log(sprintf('vehicle_evidence[%d] upload error=%s (%s)', $i, $err, uploadErrorText($err)));
          continue;
        }
        $orig = basename($_FILES['vehicle_evidence']['name'][$i]);
        $ext = pathinfo($orig, PATHINFO_EXTENSION);
        $filename = uniqid('vehicle_') . '.' . $ext;
        $target = $uploadDir . '/' . $filename;
        $tmp = isset($_FILES['vehicle_evidence']['tmp_name'][$i]) ? $_FILES['vehicle_evidence']['tmp_name'][$i] : '';
        if (move_uploaded_file($tmp, $target)) {
          $webPath = '/uploads/spot_reports/' . $safeRef . '/' . $filename;
          $perFileStmt->execute([':rid' => $reportId, ':type' => 'vehicle_evidence', ':path' => $webPath, ':orig' => 'vehicle#' . $i . ':' . $orig]);
        } else {
          if ($tmp && file_exists($tmp)) {
            if (@rename($tmp, $target) || (@copy($tmp, $target) && @unlink($tmp))) {
              error_log(sprintf('vehicle_evidence[%d] moved via fallback rename/copy tmp=%s target=%s', $i, $tmp, $target));
              $webPath = '/uploads/spot_reports/' . $safeRef . '/' . $filename;
              $perFileStmt->execute([':rid' => $reportId, ':type' => 'vehicle_evidence', ':path' => $webPath, ':orig' => 'vehicle#' . $i . ':' . $orig]);
            } else {
              error_log(sprintf('Failed to move (and fallback) vehicle_evidence tmp=%s target=%s is_uploaded=%s err=%s', $tmp, $target, is_uploaded_file($tmp) ? '1' : '0', isset($_FILES['vehicle_evidence']['error'][$i]) ? $_FILES['vehicle_evidence']['error'][$i] : 'n/a'));
            }
          } else {
            error_log(sprintf('Failed to move vehicle_evidence tmp missing=%s target=%s err=%s', $tmp, $target, isset($_FILES['vehicle_evidence']['error'][$i]) ? $_FILES['vehicle_evidence']['error'][$i] : 'n/a'));
          }
        }
      }
    }

    if (!empty($_FILES['item_evidence']) && is_array($_FILES['item_evidence']['name'])) {
      for ($i = 0; $i < count($_FILES['item_evidence']['name']); $i++) {
        $err = isset($_FILES['item_evidence']['error'][$i]) ? $_FILES['item_evidence']['error'][$i] : UPLOAD_ERR_NO_FILE;
        if ($err !== UPLOAD_ERR_OK) {
          error_log(sprintf('item_evidence[%d] upload error=%s (%s)', $i, $err, uploadErrorText($err)));
          continue;
        }
        $orig = basename($_FILES['item_evidence']['name'][$i]);
        $ext = pathinfo($orig, PATHINFO_EXTENSION);
        $filename = uniqid('item_') . '.' . $ext;
        $target = $uploadDir . '/' . $filename;
        $tmp = isset($_FILES['item_evidence']['tmp_name'][$i]) ? $_FILES['item_evidence']['tmp_name'][$i] : '';
        if (move_uploaded_file($tmp, $target)) {
          $webPath = '/uploads/spot_reports/' . $safeRef . '/' . $filename;
          $perFileStmt->execute([':rid' => $reportId, ':type' => 'item_evidence', ':path' => $webPath, ':orig' => 'item#' . $i . ':' . $orig]);
        } else {
          if ($tmp && file_exists($tmp)) {
            if (@rename($tmp, $target) || (@copy($tmp, $target) && @unlink($tmp))) {
              error_log(sprintf('item_evidence[%d] moved via fallback rename/copy tmp=%s target=%s', $i, $tmp, $target));
              $webPath = '/uploads/spot_reports/' . $safeRef . '/' . $filename;
              $perFileStmt->execute([':rid' => $reportId, ':type' => 'item_evidence', ':path' => $webPath, ':orig' => 'item#' . $i . ':' . $orig]);
            } else {
              error_log(sprintf('Failed to move (and fallback) item_evidence tmp=%s target=%s is_uploaded=%s err=%s', $tmp, $target, is_uploaded_file($tmp) ? '1' : '0', isset($_FILES['item_evidence']['error'][$i]) ? $_FILES['item_evidence']['error'][$i] : 'n/a'));
            }
          } else {
            error_log(sprintf('Failed to move item_evidence tmp missing=%s target=%s err=%s', $tmp, $target, isset($_FILES['item_evidence']['error'][$i]) ? $_FILES['item_evidence']['error'][$i] : 'n/a'));
          }
        }
      }
    }

    $fStmt = $pdo->prepare('INSERT INTO spot_report_files (report_id, file_type, file_path, orig_name) VALUES (:rid, :type, :path, :orig)');

    if (!empty($_FILES['evidence_files']) && is_array($_FILES['evidence_files']['name'])) {
      for ($i = 0; $i < count($_FILES['evidence_files']['name']); $i++) {
        $err = isset($_FILES['evidence_files']['error'][$i]) ? $_FILES['evidence_files']['error'][$i] : UPLOAD_ERR_NO_FILE;
        if ($err !== UPLOAD_ERR_OK) {
          error_log(sprintf('evidence_files[%d] upload error=%s (%s)', $i, $err, uploadErrorText($err)));
          continue;
        }
        $orig = basename($_FILES['evidence_files']['name'][$i]);
        $ext = pathinfo($orig, PATHINFO_EXTENSION);
        $filename = uniqid('evi_') . '.' . $ext;
        $target = $uploadDir . '/' . $filename;
        $tmp = isset($_FILES['evidence_files']['tmp_name'][$i]) ? $_FILES['evidence_files']['tmp_name'][$i] : '';
        if (move_uploaded_file($tmp, $target)) {
          $webPath = '/uploads/spot_reports/' . $safeRef . '/' . $filename;
          $fStmt->execute([':rid' => $reportId, ':type' => 'evidence', ':path' => $webPath, ':orig' => $orig]);
        } else {
          if ($tmp && file_exists($tmp)) {
            if (@rename($tmp, $target) || (@copy($tmp, $target) && @unlink($tmp))) {
              error_log(sprintf('evidence_files[%d] moved via fallback rename/copy tmp=%s target=%s', $i, $tmp, $target));
              $webPath = '/uploads/spot_reports/' . $safeRef . '/' . $filename;
              $fStmt->execute([':rid' => $reportId, ':type' => 'evidence', ':path' => $webPath, ':orig' => $orig]);
            } else {
              error_log(sprintf('Failed to move (and fallback) evidence_files tmp=%s target=%s is_uploaded=%s err=%s', $tmp, $target, is_uploaded_file($tmp) ? '1' : '0', isset($_FILES['evidence_files']['error'][$i]) ? $_FILES['evidence_files']['error'][$i] : 'n/a'));
            }
          } else {
            error_log(sprintf('Failed to move evidence_files tmp missing=%s target=%s err=%s', $tmp, $target, isset($_FILES['evidence_files']['error'][$i]) ? $_FILES['evidence_files']['error'][$i] : 'n/a'));
          }
        }
      }
    }

    if (!empty($_FILES['pdf_files']) && is_array($_FILES['pdf_files']['name'])) {
      for ($i = 0; $i < count($_FILES['pdf_files']['name']); $i++) {
        $err = isset($_FILES['pdf_files']['error'][$i]) ? $_FILES['pdf_files']['error'][$i] : UPLOAD_ERR_NO_FILE;
        if ($err !== UPLOAD_ERR_OK) {
          error_log(sprintf('pdf_files[%d] upload error=%s (%s)', $i, $err, uploadErrorText($err)));
          continue;
        }
        $orig = basename($_FILES['pdf_files']['name'][$i]);
        $ext = pathinfo($orig, PATHINFO_EXTENSION);
        $filename = uniqid('doc_') . '.' . $ext;
        $target = $uploadDir . '/' . $filename;
        $tmp = isset($_FILES['pdf_files']['tmp_name'][$i]) ? $_FILES['pdf_files']['tmp_name'][$i] : '';
        if (move_uploaded_file($tmp, $target)) {
          $webPath = '/uploads/spot_reports/' . $safeRef . '/' . $filename;
          $fStmt->execute([':rid' => $reportId, ':type' => 'pdf', ':path' => $webPath, ':orig' => $orig]);
        } else {
          if ($tmp && file_exists($tmp)) {
            if (@rename($tmp, $target) || (@copy($tmp, $target) && @unlink($tmp))) {
              error_log(sprintf('pdf_files[%d] moved via fallback rename/copy tmp=%s target=%s', $i, $tmp, $target));
              $webPath = '/uploads/spot_reports/' . $safeRef . '/' . $filename;
              $fStmt->execute([':rid' => $reportId, ':type' => 'pdf', ':path' => $webPath, ':orig' => $orig]);
            } else {
              error_log(sprintf('Failed to move (and fallback) pdf_files tmp=%s target=%s is_uploaded=%s err=%s', $tmp, $target, is_uploaded_file($tmp) ? '1' : '0', isset($_FILES['pdf_files']['error'][$i]) ? $_FILES['pdf_files']['error'][$i] : 'n/a'));
            }
          } else {
            error_log(sprintf('Failed to move pdf_files tmp missing=%s target=%s err=%s', $tmp, $target, isset($_FILES['pdf_files']['error'][$i]) ? $_FILES['pdf_files']['error'][$i] : 'n/a'));
          }
        }
      }
    }

      $pdo->commit();

      header('Location: view_spot_report.php?ref=' . urlencode($ref));
      exit;
    } catch (Exception $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      error_log('Spot report save error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
      // Show detailed error during development. Escape output to avoid XSS.
      http_response_code(500);
      echo '<h3>An error occurred while saving the report</h3>';
      echo '<pre>' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
      exit;
    }
  } // end if no validation errors
}

?>
<?php
// Pre-compute next reference for display (GET). If DB not available, fallback to YYYY-MM-DD-0001
$nextRef = date('Y-m-d') . '-0001';
try {
  require_once dirname(dirname(dirname(__DIR__))) . '/config/db.php';
  $today = date('Y-m-d');
  $base = $today . '-';
  $countStmt = $pdo->prepare('SELECT COUNT(*) AS c FROM spot_reports WHERE reference_no LIKE ?');
  $countStmt->execute([$base . '%']);
  $cntRow = $countStmt->fetch(PDO::FETCH_ASSOC);
  $seq = ($cntRow && isset($cntRow['c'])) ? ((int)$cntRow['c'] + 1) : 1;
  $nextRef = $base . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
} catch (Exception $e) {
  // leave fallback
}

// ensure $old and $errors are defined for form rendering
if (!isset($old) || !is_array($old)) $old = array();
if (!isset($errors) || !is_array($errors)) $errors = array();

// choose which reference to display: freshly generated $ref (on POST) or $nextRef
$displayRef = $nextRef;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($ref) && $ref) {
  $displayRef = $ref;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Spot Report - CENRO NASIPIT</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Admin common styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <!-- View Spot Report specific styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/view-spot-report.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Fredoka+One:400&display=swap" rel="stylesheet">
  <style>
    /* Page-specific override to enlarge the main content area */
    /* Make sidebar a fixed narrow column and let .main take remaining space */
    .layout > .sidebar { flex: 0 0 230px; max-width: 230px; }
    .layout > .main { flex: 1 1 auto; width: auto; }

    /* Increase the inner container max width so content appears larger on wide screens */
    .main .container-fluid { max-width: 1300px; margin: 0 auto; }

    /* Slightly larger padding inside main for better spacing */
    .main .main-content, .main .container-fluid { padding-left: 2rem; padding-right: 2rem; }

    @media (max-width: 991px) {
      /* revert to stacked layout on smaller screens */
      .layout > .sidebar { flex: 0 0 200px; max-width: 200px; }
      .main .container-fluid { max-width: 100%; padding-left: 1rem; padding-right: 1rem; }
    }
  </style>
</head>
<body>
  <div class="layout">
    <!-- Sidebar -->
    <nav class="sidebar" role="navigation" aria-label="Main sidebar">
      <div class="sidebar-logo">
        <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo">
        <span>CENRO</span>
      </div>
      <div class="sidebar-role">Enforcer</div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li><a href="spot_reports.php"><i class="fa fa-file-text"></i> Spot Reports</a></li>
          <li><a href="service_requests.php"><i class="fa fa-cog"></i> Service Requests</a></li>
        </ul>
      </nav>
    </nav>
    <!-- Main -->
    <div class="main">
      <div class="topbar">
        <div class="topbar-card">
          <div class="topbar-title">Dashboard</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <!-- Action Buttons -->
        <div class="action-buttons mb-3 px-4">
          <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">Back</button>
        </div>
        <!-- New Spot Report Form -->
        <div class="container-fluid p-4">
          <form id="spotReportForm" method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" onsubmit="return handleSubmit(event)" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="">
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                  <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>
            <!-- Header Section -->
            <div class="report-header text-center mb-4">
              <div class="d-flex justify-content-between align-items-start">
                <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo" class="logo-left">
                <div class="header-content">
                  <h6>Department of Environment and Natural Resources</h6>
                  <h6>Kagawaran ng Kapaligiran at Likas Yaman</h6>
                  <h6>Caraga Region</h6>
                  <h6>CENRO Nasipit, Agusan del Norte</h6>
                  <hr style="border-top: 2px solid #ff0000; margin: 5px 0 10px 0;">
                  <h4 class="mt-3">New Spot Report</h4>
                </div>
                <img src="../../../../public/assets/images/bagong-pilipinas-logo.png" alt="Bagong Pilipinas Logo" class="logo-right">
              </div>
            </div>

            <!-- Incident Details Section -->
            <div class="report-section mb-4">
              <h5>Incident Details</h5>
              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="incidentDateTime" class="form-label">Incident Date & Time:</label>
                  <input type="datetime-local" class="form-control" id="incidentDateTime" name="incident_datetime" value="<?php echo htmlspecialchars($old['incident_datetime'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                  <label for="memoDate" class="form-label">Memo Date:</label>
                  <input type="datetime-local" class="form-control" id="memoDate" name="memo_date" value="<?php echo htmlspecialchars($old['memo_date'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                  <label for="referenceNo" class="form-label">Reference No.:</label>
                  <input type="text" class="form-control" id="referenceNo" name="reference_no" value="<?php echo htmlspecialchars($displayRef); ?>" readonly>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-12">
                  <label for="location" class="form-label">Location:</label>
                  <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Brgy Rizal, Buenavista, Agusan del Norte" value="<?php echo htmlspecialchars($old['location'] ?? ''); ?>" required>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-12">
                  <label for="summary" class="form-label">Summary:</label>
                  <textarea class="form-control" id="summary" name="summary" rows="4" placeholder="Detailed description of the incident..." required><?php echo htmlspecialchars($old['summary'] ?? ''); ?></textarea>
                </div>
              </div>
            </div>

            <!-- Personnel Section -->
            <div class="report-section mb-4">
              <h5>Personnel Information</h5>
              <div class="row">
                <div class="col-md-6">
                  <label for="teamLeader" class="form-label">Team Leader:</label>
                  <input type="text" class="form-control" id="teamLeader" name="team_leader" placeholder="Enter team leader name" value="<?php echo htmlspecialchars($old['team_leader'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                  <label for="custodian" class="form-label">Custodian:</label>
                  <input type="text" class="form-control" id="custodian" name="custodian" placeholder="Enter custodian name" value="<?php echo htmlspecialchars($old['custodian'] ?? ''); ?>" required>
                </div>
              </div>
            </div>

            <!-- Apprehended Persons Section -->
            <div class="report-section mb-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Apprehended Person(s)</h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="addPersonRow()">
                  <i class="fa fa-plus"></i> Add Person
                </button>
              </div>
              <div class="table-responsive">
                <table class="table table-bordered" id="personsTable">
                  <thead>
                    <tr>
                      <th>Full Name</th>
                      <th>Age</th>
                      <th>Gender</th>
                      <th>Address</th>
                      <th>Contact No.</th>
                      <th>Role</th>
                      <th>Status</th>
                      <th>Evidence</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="personsTableBody">
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Vehicles Section -->
            <div class="report-section mb-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Vehicle(s)</h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="addVehicleRow()">
                  <i class="fa fa-plus"></i> Add Vehicle
                </button>
              </div>
              <div class="table-responsive">
                <table class="table table-bordered" id="vehiclesTable">
                  <thead>
                    <tr>
                      <th>Plate No.</th>
                      <th>Make/Model</th>
                      <th>Color</th>
                      <th>Registered Owner Name</th>
                      <th>Contact No.</th>
                      <th>Engine/Chassis No.</th>
                      <th>Remarks</th>
                      <th>Status</th>
                      <th>Evidence</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="vehiclesTableBody">
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Seizure Items Section -->
            <div class="report-section mb-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Seizure Items</h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="addSeizureRow()">
                  <i class="fa fa-plus"></i> Add Item
                </button>
              </div>
              <div class="table-responsive">
                <table class="table table-bordered" id="seizureTable">
                  <thead>
                    <tr>
                      <th>Item No.</th>
                      <th>Item Type</th>
                      <th>Description</th>
                      <th>Quantity</th>
                      <th>Volume (Bd.ft./cu.m.)</th>
                      <th>Estimated Value (₱)</th>
                      <th>Remarks</th>
                      <th>Status</th>
                      <th>Evidence</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="seizureTableBody">
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Evidence Section -->
            <div class="report-section mb-4">
              <h5>File Attachments</h5>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="evidenceFiles" class="form-label">Evidence Files (Photos/Videos)</label>
                    <input type="file" class="form-control" id="evidenceFiles" name="evidence_files[]" 
                           accept="image/*,video/*" multiple onchange="updateFileList('evidenceFiles', 'evidenceList')">
                    <div class="form-text">Supported formats: JPG, PNG, MP4, MOV. Max size: 50MB per file.</div>
                    <div id="evidenceList" class="mt-2"></div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="pdfFiles" class="form-label">Documents (PDF)</label>
                    <input type="file" class="form-control" id="pdfFiles" name="pdf_files[]" 
                           accept=".pdf" multiple onchange="updateFileList('pdfFiles', 'pdfList')">
                    <div class="form-text">PDF documents only. Max size: 10MB per file.</div>
                    <div id="pdfList" class="mt-2"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Submit Section -->
            <div class="report-section mb-4">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6>Status: <span class="badge bg-warning">Draft</span></h6>
                </div>
                <div>
                  <button type="button" class="btn btn-secondary me-2" onclick="saveDraft()">
                    <i class="fa fa-save"></i> Save as Draft
                  </button>
                  <button type="submit" class="btn btn-primary">
                    <i class="fa fa-paper-plane"></i> Submit Report
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Admin Dashboard JavaScript -->
  <script src="../../../../public/assets/js/admin/dashboard.js"></script>
  <!-- Admin Navigation JavaScript -->
  <script src="../../../../public/assets/js/admin/navigation.js"></script>

  <!-- Spot Report Form JavaScript -->
  <script>
    // Reference number is provided by user (no auto-generation)
    const VEHICLE_MAKE_MODEL_OPTIONS = [
      'Toyota - Vios',
      'Toyota - Wigo',
      'Toyota - Yaris',
      'Toyota - Corolla Altis',
      'Toyota - Corolla Cross',
      'Toyota - Camry',
      'Toyota - Raize',
      'Toyota - Rush',
      'Toyota - Avanza',
      'Toyota - Innova',
      'Toyota - Fortuner',
      'Toyota - Hilux',
      'Toyota - Land Cruiser',
      'Toyota - Hiace',
      'Mitsubishi - Mirage',
      'Mitsubishi - Mirage G4',
      'Mitsubishi - Xpander',
      'Mitsubishi - Montero Sport',
      'Mitsubishi - Strada',
      'Mitsubishi - L300',
      'Nissan - Almera',
      'Nissan - Navara',
      'Nissan - Terra',
      'Nissan - Urvan',
      'Ford - Ranger',
      'Ford - Everest',
      'Ford - Territory',
      'Isuzu - D-Max',
      'Isuzu - mu-X',
      'Isuzu - Traviz',
      'Honda - Brio',
      'Honda - City',
      'Honda - Civic',
      'Honda - HR-V',
      'Honda - BR-V',
      'Honda - CR-V',
      'Hyundai - Accent',
      'Hyundai - Reina',
      'Hyundai - Tucson',
      'Hyundai - Santa Fe',
      'Hyundai - Starex',
      'Hyundai - H-100',
      'Kia - Soluto',
      'Kia - Stonic',
      'Kia - Seltos',
      'Kia - Sportage',
      'Kia - Carnival',
      'Suzuki - Dzire',
      'Suzuki - Celerio',
      'Suzuki - Swift',
      'Suzuki - Ertiga',
      'Suzuki - XL7',
      'Suzuki - Jimny',
      'Suzuki - Carry',
      'Mazda - Mazda2',
      'Mazda - Mazda3',
      'Mazda - CX-3',
      'Mazda - CX-5',
      'Mazda - BT-50',
      'Chevrolet - Spark',
      'Chevrolet - Sail',
      'Chevrolet - Tracker',
      'Chevrolet - Trailblazer',
      'Chevrolet - Colorado',
      'Subaru - XV',
      'Subaru - Forester',
      'Subaru - Outback',
      'Hino - 300 Series',
      'Hino - 500 Series',
      'Hino - 700 Series',
      'Fuso - Canter',
      'Fuso - Fighter',
      'Fuso - Super Great',
      'UD Trucks - Croner',
      'UD Trucks - Quester',
      'Foton - Tornado',
      'Foton - Thunder',
      'Dongfeng - Captain',
      'Dongfeng - KR Series',
      'Sinotruk/Howo - Howo A7',
      'Sinotruk/Howo - Howo NX',
      'Caterpillar - 320',
      'Caterpillar - 336',
      'Caterpillar - D6',
      'Caterpillar - 966',
      'Komatsu - PC200',
      'Komatsu - PC300',
      'Komatsu - D65',
      'Komatsu - WA380',
      'Hitachi - ZX200',
      'Hitachi - ZX210',
      'Volvo CE - EC210',
      'Volvo CE - EC360',
      'Honda (Motorcycle) - TMX',
      'Honda (Motorcycle) - XRM',
      'Honda (Motorcycle) - Wave',
      'Honda (Motorcycle) - Click',
      'Yamaha - Mio',
      'Yamaha - NMAX',
      'Yamaha - Sniper',
      'Yamaha - Aerox',
      'Kawasaki - Barako',
      'Kawasaki - Rouser',
      'Suzuki (Motorcycle) - Raider',
      'Suzuki (Motorcycle) - Smash',
      'Rusi - Rusi 125',
      'Motorstar - Star-X'
    ];

    function escapeHtml(value) {
      return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function buildVehicleMakeModelSelect() {
      const options = VEHICLE_MAKE_MODEL_OPTIONS
        .map(function(item) { return '<option value="' + escapeHtml(item) + '">' + escapeHtml(item) + '</option>'; })
        .join('');
      return `
        <select class="form-select vehicle-make-select" name="vehicle_make[]" onchange="toggleVehicleCustomInput(this)">
          <option value="">Select Make/Model</option>
          ${options}
          <option value="__custom__">Add model</option>
        </select>
        <input type="text" class="form-control mt-2 vehicle-make-custom d-none" name="vehicle_make_custom[]" placeholder="Enter make/model (custom)">
      `;
    }

    function toggleVehicleCustomInput(selectEl) {
      if (!selectEl) return;
      const holder = selectEl.closest('td');
      if (!holder) return;
      const customInput = holder.querySelector('.vehicle-make-custom');
      if (!customInput) return;
      const useCustom = selectEl.value === '__custom__';
      customInput.classList.toggle('d-none', !useCustom);
      customInput.required = useCustom;
      if (!useCustom) customInput.value = '';
    }

    // Add person row
    function addPersonRow() {
      const tbody = document.getElementById('personsTableBody');
      const newRow = `
        <tr>
          <td><input type="text" class="form-control" name="person_name[]" placeholder="Full Name"></td>
          <td><input type="text" class="form-control" name="person_age[]" placeholder="Age" inputmode="numeric" pattern="[0-9]*" maxlength="3"></td>
          <td>
            <select class="form-select" name="person_gender[]">
              <option value="">Select</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </td>
          <td><input type="text" class="form-control" name="person_address[]" placeholder="Address"></td>
          <td><input type="text" class="form-control" name="person_contact[]" placeholder="Contact Number"></td>
          <td>
            <select class="form-select" name="person_role[]">
              <option value="">Select Role</option>
              <option value="Financier">Financier</option>
              <option value="Operator">Operator</option>
              <option value="Timber Cutter">Timber Cutter</option>
              <option value="Chainsaw Operator">Chainsaw Operator</option>
              <option value="Helper">Helper</option>
              <option value="Laborer">Laborer</option>
              <option value="Driver">Driver</option>
              <option value="Lookout">Lookout</option>
              <option value="Loader">Loader</option>
              <option value="Broker">Broker</option>
              <option value="Middleman">Middleman</option>
              <option value="Buyer">Buyer</option>
              <option value="Consignee">Consignee</option>
              <option value="Permit Falsifier">Permit Falsifier</option>
            </select>
          </td>
          <td>
            <select class="form-select" name="person_status[]">
              <option value="">Select Status</option>
              <option value="Under Custody / Detained">Under Custody / Detained</option>
              <option value="Under Inquest / For Filing of Case">Under Inquest / For Filing of Case</option>
              <option value="Respondent / Accused">Respondent / Accused</option>
              <option value="Released Pending Investigation">Released Pending Investigation</option>
              <option value="On Bail">On Bail</option>
              <option value="Convicted">Convicted</option>
              <option value="Case Dismissed / Acquitted">Case Dismissed / Acquitted</option>
            </select>
          </td>
          <td>
            <input type="file" class="form-control person-evidence-input" name="person_evidence[]" accept="image/*,video/*,.pdf">
            <div class="file-preview mt-2"></div>
          </td>
          <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
              <i class="fa fa-trash"></i>
            </button>
          </td>
        </tr>
      `;
      tbody.insertAdjacentHTML('beforeend', newRow);
      bindRowFileInputs();
    }

    // Add vehicle row
    function addVehicleRow() {
      const tbody = document.getElementById('vehiclesTableBody');
      const newRow = `
        <tr>
          <td><input type="text" class="form-control" name="vehicle_plate[]" placeholder="Plate Number"></td>
          <td>${buildVehicleMakeModelSelect()}</td>
          <td><input type="text" class="form-control" name="vehicle_color[]" placeholder="Color"></td>
          <td><input type="text" class="form-control" name="vehicle_owner[]" placeholder="Owner Name"></td>
          <td><input type="text" class="form-control" name="vehicle_contact[]" placeholder="Contact Number"></td>
          <td><input type="text" class="form-control" name="vehicle_engine[]" placeholder="Engine/Chassis No."></td>
          <td>
            <input type="text" class="form-control" name="vehicle_remarks[]" placeholder="Remarks">
          </td>
          <td>
            <select class="form-select" name="vehicle_status[]">
              <option value="">Select Status</option>
              <option value="Confiscated">Confiscated</option>
              <option value="Seized">Seized</option>
              <option value="Under Custody">Under Custody</option>
              <option value="For Disposal">For Disposal</option>
              <option value="Disposed">Disposed</option>
              <option value="Burned/Destroyed">Burned/Destroyed</option>
              <option value="Forfeited to Government">Forfeited to Government</option>
              <option value="Donated to LGU">Donated to LGU</option>
              <option value="Returned to Owner">Returned to Owner</option>
              <option value="Publicly Auctioned">Publicly Auctioned</option>
            </select>
          </td>
          <td>
            <input type="file" class="form-control vehicle-evidence-input" name="vehicle_evidence[]" accept="image/*,video/*,.pdf">
            <div class="file-preview mt-2"></div>
          </td>
          <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
              <i class="fa fa-trash"></i>
            </button>
          </td>
        </tr>
      `;
      tbody.insertAdjacentHTML('beforeend', newRow);
      bindRowFileInputs();
    }

    // Add seizure item row
    function addSeizureRow() {
      const tbody = document.getElementById('seizureTableBody');
      const rowCount = tbody.children.length + 1;
      const newRow = `
        <tr>
          <td><input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control" name="item_no[]" placeholder="${rowCount}" value="${rowCount}"></td>
          <td>
            <select class="form-select" name="item_type[]">
              <option value="">Select Type</option>
              <option value="Forest Product">Forest Product</option>
              <option value="Equipment">Equipment</option>
              <option value="Other">Other</option>
            </select>
          </td>
          <td><input type="text" class="form-control" name="item_description[]" placeholder="Description"></td>
          <td><input type="text" class="form-control" name="item_quantity[]" placeholder="e.g., 13 pcs"></td>
          <td><input type="text" class="form-control" name="item_volume[]" placeholder="e.g., 88 Bd.ft."></td>
          <td><input type="number" class="form-control" name="item_value[]" placeholder="0.00" step="0.01" min="0"></td>
          <td><input type="text" class="form-control" name="item_remarks[]" placeholder="Remarks"></td>
          <td>
            <select class="form-select" name="item_status[]">
              <option value="">Select Status</option>
              <option value="Confiscated">Confiscated</option>
              <option value="Seized">Seized</option>
              <option value="Under Custody">Under Custody</option>
              <option value="For Disposal">For Disposal</option>
              <option value="Disposed">Disposed</option>
              <option value="Burned/Destroyed">Burned/Destroyed</option>
              <option value="Forfeited to Government">Forfeited to Government</option>
              <option value="Donated to LGU">Donated to LGU</option>
              <option value="Returned to Owner">Returned to Owner</option>
              <option value="Publicly Auctioned">Publicly Auctioned</option>
            </select>
          </td>
          <td>
            <input type="file" class="form-control item-evidence-input" name="item_evidence[]" accept="image/*,video/*,.pdf">
            <div class="file-preview mt-2"></div>
          </td>
          <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
              <i class="fa fa-trash"></i>
            </button>
          </td>
        </tr>
      `;
      tbody.insertAdjacentHTML('beforeend', newRow);
      bindRowFileInputs();
    }

    // Remove row function
    function removeRow(button) {
      const row = button.closest('tr');
      if (row) row.remove();
    }

    // Update file list display
    function updateFileList(inputId, listId) {
      const input = document.getElementById(inputId);
      const listDiv = document.getElementById(listId);
      
      listDiv.innerHTML = '';
      
      if (input && input.files && input.files.length > 0) {
        for (let i = 0; i < input.files.length; i++) {
          const file = input.files[i];
          const fileItem = document.createElement('div');
          fileItem.className = 'file-item mb-2 p-2 border rounded d-flex align-items-center';

          // preview for images
          if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.maxWidth = '120px';
            img.style.maxHeight = '80px';
            img.className = 'me-2';
            fileItem.appendChild(img);
          } else if (file.type.startsWith('video/')) {
            const ico = document.createElement('i');
            ico.className = 'fa fa-video fa-2x me-2';
            fileItem.appendChild(ico);
          } else {
            const ico = document.createElement('i');
            ico.className = 'fa fa-file-pdf fa-2x me-2';
            fileItem.appendChild(ico);
          }

          const info = document.createElement('div');
          info.innerHTML = `<div><strong>${file.name}</strong></div>`;
          info.className = 'flex-grow-1 text-truncate';
          fileItem.appendChild(info);

          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn btn-sm btn-outline-danger ms-2';
          btn.innerHTML = '<i class="fa fa-times"></i>';
          btn.addEventListener('click', function() { removeFile(inputId, i); });
          fileItem.appendChild(btn);

          listDiv.appendChild(fileItem);
        }
      }
    }

    // Bind change handlers for per-row file inputs (dynamic rows)
    function bindRowFileInputs() {
      // person evidence
      document.querySelectorAll('.person-evidence-input').forEach(function(inp) {
        if (!inp._bound) {
          inp.addEventListener('change', function() { previewRowFiles(inp); });
          inp._bound = true;
        }
      });
      document.querySelectorAll('.vehicle-evidence-input').forEach(function(inp) {
        if (!inp._bound) {
          inp.addEventListener('change', function() { previewRowFiles(inp); });
          inp._bound = true;
        }
      });
      document.querySelectorAll('.item-evidence-input').forEach(function(inp) {
        if (!inp._bound) {
          inp.addEventListener('change', function() { previewRowFiles(inp); });
          inp._bound = true;
        }
      });
    }

    function previewRowFiles(input) {
      const previewDiv = input.closest('td').querySelector('.file-preview');
      if (!previewDiv) return;
      previewDiv.innerHTML = '';
      if (!input.files || input.files.length === 0) return;
      for (let i = 0; i < input.files.length; i++) {
        const file = input.files[i];
        const item = document.createElement('div');
        item.className = 'd-inline-block me-2 mb-2 text-center';
        if (file.type.startsWith('image/')) {
          const img = document.createElement('img');
          img.src = URL.createObjectURL(file);
          img.style.maxWidth = '120px';
          img.style.maxHeight = '80px';
          img.className = 'd-block mb-1 border';
          item.appendChild(img);
        } else if (file.type.startsWith('video/')) {
          const ico = document.createElement('i');
          ico.className = 'fa fa-video fa-2x d-block mb-1';
          item.appendChild(ico);
        } else {
          const ico = document.createElement('i');
          ico.className = 'fa fa-file-pdf fa-2x d-block mb-1';
          item.appendChild(ico);
        }
        const name = document.createElement('div');
        name.className = 'small text-truncate';
        name.style.maxWidth = '120px';
        name.textContent = file.name;
        item.appendChild(name);
        previewDiv.appendChild(item);
      }
    }

    // Remove file (clears selection and refreshes list)
    function removeFile(inputId, index) {
      const input = document.getElementById(inputId);
      if (!input) return;
      // Can't remove single file from FileList; clear all and let user re-select
      input.value = '';
      const listId = inputId === 'evidenceFiles' ? 'evidenceList' : 'pdfList';
      updateFileList(inputId, listId);
    }

    // Save as draft — set action and submit (skip required-field validation)
    function saveDraft() {
      const form = document.getElementById('spotReportForm');
      const actionInput = document.getElementById('formAction');
      actionInput.value = 'save_draft';
      form.submit();
    }

    // Handle submission client-side: validate then submit form to server
    function handleSubmit(e, saveAsDraft = false) {
      const form = document.getElementById('spotReportForm');
      const actionInput = document.getElementById('formAction');

      // determine if this submit is a draft save (either requested or already set)
      const isDraft = saveAsDraft || (actionInput && actionInput.value === 'save_draft');

      // If this is a draft, skip required-field validation
      if (isDraft) {
        // ensure hidden input reflects draft
        if (actionInput) actionInput.value = 'save_draft';
        return true; // allow normal submit to proceed
      }

      if (e && e.preventDefault) {
        // Basic validation for full submit
        const requiredFields = [
          'incident_datetime', 'memo_date', 'location', 'summary',
          'team_leader', 'custodian'
        ];
        let isValid = true;
        requiredFields.forEach(field => {
          const input = document.querySelector(`[name="${field}"]`);
          if (!input || !input.value.trim()) {
            if (input) input.classList.add('is-invalid');
            isValid = false;
          } else {
            input.classList.remove('is-invalid');
          }
        });
        if (!isValid) {
          e.preventDefault();
          alert('Please fill in all required fields.');
          return false;
        }
      }

      // ensure action input is empty for normal submit
      if (actionInput) actionInput.value = '';
      return true; // allow browser to submit form (files included)
    }

    function generateRef() {
      const d = new Date();
      const iso = d.toISOString().slice(0,10);
      return iso + '-0001';
    }

    // Populate reference number on load if empty
    document.addEventListener('DOMContentLoaded', function() {
      const refEl = document.getElementById('referenceNo');
      if (refEl && !refEl.value) {
        refEl.value = generateRef();
      }
      // bind handlers for any existing dynamic row file inputs
      if (typeof bindRowFileInputs === 'function') bindRowFileInputs();
      // render previews for any pre-selected top-level files
      if (document.getElementById('evidenceFiles')) updateFileList('evidenceFiles', 'evidenceList');
      if (document.getElementById('pdfFiles')) updateFileList('pdfFiles', 'pdfList');
    });
  </script>
</body>
</html>
