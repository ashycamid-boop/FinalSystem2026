<?php
session_start();

// Enable verbose error reporting for debugging while we troubleshoot save failures
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// holder for save error diagnostics (shown in-page during debugging)
$save_error = null;
$debug_info = [];

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: /prototype/index.php');
    exit;
}
// current session user id (supports multiple session key conventions)
$sessionUserId = $_SESSION['uid'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
// Handle approve/reject POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id']) && !empty($_POST['status_action'])) {
  $postId = $_POST['id'];
  $action = $_POST['status_action']; // 'approve', 'reject', 'complete'
  require_once __DIR__ . '/../../../config/db.php';
  // Map action to status
  if ($action === 'approve') {
    $newStatus = 'Ongoing';
  } elseif ($action === 'complete') {
    $newStatus = 'Completed';
  } elseif ($action === 'reject') {
    $newStatus = 'Rejected';
  } else {
    $newStatus = null;
  }
  if ($newStatus !== null) {
    try {
      if (ctype_digit((string)$postId)) {
        $stmt = $pdo->prepare('UPDATE service_requests SET status = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$newStatus, $postId]);
      } else {
        $stmt = $pdo->prepare('UPDATE service_requests SET status = ?, updated_at = NOW() WHERE ticket_no = ?');
        $stmt->execute([$newStatus, $postId]);
      }
    } catch (Exception $e) {
      error_log('edit_requests status update error: ' . $e->getMessage());
    }

    // Redirect after action
    if ($action === 'approve') {
      header('Location: ongoing_scheduled.php');
    } elseif ($action === 'complete') {
      header('Location: completed.php');
    } elseif ($action === 'reject') {
      header('Location: new_requests.php');
    } else {
      header('Location: edit_requests_ongoing.php?id=' . urlencode($postId));
    }
    exit;
  }
}
// Handle saving edits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id']) && !empty($_POST['save_changes'])) {
  $postId = $_POST['id'];
  require_once __DIR__ . '/../../../config/db.php';
  $auth1_name = $_POST['auth1_name'] ?? '';
  $auth1_position = $_POST['auth1_position'] ?? '';
  $auth1_date = $_POST['auth1_date'] ?? null;
  $auth2_name = $_POST['auth2_name'] ?? '';
  $auth2_position = $_POST['auth2_position'] ?? '';
  $auth2_date = $_POST['auth2_date'] ?? null;
  // handle signature data (base64) for auth1/auth2
  $auth1_sig_path = null;
  $auth2_sig_path = null;
  if (!empty($_POST['auth1_signature_data'])) {
    $data = $_POST['auth1_signature_data'];
    if (preg_match('/^data:\w+\/\w+;base64,/', $data)) {
      $data = preg_replace('/^data:\w+\/\w+;base64,/', '', $data);
    }
    $decoded = base64_decode($data);
    if ($decoded !== false) {
      $dir = __DIR__ . '/../../../../public/uploads/signatures/';
      if (!is_dir($dir)) mkdir($dir, 0755, true);
      $fname = 'auth1_' . uniqid() . '.png';
      $full = $dir . $fname;
      file_put_contents($full, $decoded);
      $auth1_sig_path = 'public/uploads/signatures/' . $fname;
    }
  }
  if (!empty($_POST['auth2_signature_data'])) {
    $data = $_POST['auth2_signature_data'];
    if (preg_match('/^data:\w+\/\w+;base64,/', $data)) {
      $data = preg_replace('/^data:\w+\/\w+;base64,/', '', $data);
    }
    $decoded = base64_decode($data);
    if ($decoded !== false) {
      $dir = __DIR__ . '/../../../../public/uploads/signatures/';
      if (!is_dir($dir)) mkdir($dir, 0755, true);
      $fname = 'auth2_' . uniqid() . '.png';
      $full = $dir . $fname;
      file_put_contents($full, $decoded);
      $auth2_sig_path = 'public/uploads/signatures/' . $fname;
    }
  }
  try {
    // ensure PDO throws exceptions for easier debugging
    if (is_object($pdo)) {
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Determine which columns actually exist in the table so we don't try to update non-existent columns
    $cols = [];
    try {
      $colStmt = $pdo->query("SHOW COLUMNS FROM service_requests");
      $cols = $colStmt->fetchAll(PDO::FETCH_COLUMN, 0);
    } catch (Exception $inner) {
      // If SHOW COLUMNS fails for any reason, fall back to empty list and continue
      $debug_info[] = ['show_columns_error' => $inner->getMessage()];
      $cols = [];
    }

    // Map of potential columns to values
    $fieldMap = [
      'auth1_name' => $auth1_name,
      'auth1_position' => $auth1_position,
      'auth1_date' => $auth1_date,
      'auth2_name' => $auth2_name,
      'auth2_position' => $auth2_position,
      'auth2_date' => $auth2_date,
    ];

    // Build SET clause only with columns that exist
    $setParts = [];
    $params = [];
    foreach ($fieldMap as $col => $val) {
      if (in_array($col, $cols, true)) {
        $setParts[] = "$col = ?";
        $params[] = $val === '' ? null : $val;
      }
    }
    if ($auth1_sig_path !== null && in_array('auth1_signature_path', $cols, true)) { $setParts[] = 'auth1_signature_path = ?'; $params[] = $auth1_sig_path; }
    if ($auth2_sig_path !== null && in_array('auth2_signature_path', $cols, true)) { $setParts[] = 'auth2_signature_path = ?'; $params[] = $auth2_sig_path; }

    // Always update updated_at if the column exists
    if (in_array('updated_at', $cols, true)) {
      $setParts[] = 'updated_at = NOW()';
    }

    if (empty($setParts)) {
      $save_error = 'No updatable columns present in service_requests table.';
      $debug_info[] = ['available_columns' => $cols];
    } else {
      $sql = 'UPDATE service_requests SET ' . implode(', ', $setParts);
      if (ctype_digit((string)$postId)) {
        $sql .= ' WHERE id = ?';
        $params[] = $postId;
      } else {
        $sql .= ' WHERE ticket_no = ?';
        $params[] = $postId;
      }

      $stmt = $pdo->prepare($sql);
      $ok = $stmt->execute($params);
      if (!$ok || $stmt->errorCode() !== '00000') {
        $save_error = 'Failed to execute UPDATE';
        $debug_info[] = ['sql' => $sql, 'params' => $params, 'error' => $stmt->errorInfo()];
      } else {
        $debug_info[] = ['updated_rows' => $stmt->rowCount(), 'sql' => $sql];
      }
    }
  } catch (Exception $e) {
    $save_error = 'Exception during save: ' . $e->getMessage();
    $debug_info[] = ['exception' => $e->getMessage(), 'sql' => $sql ?? null, 'params' => $params ?? null];
    error_log('edit_requests save error: ' . $e->getMessage());
  }
  // ---- Persist action rows (staff actions) if any were submitted ----
  try {
    // resolve numeric service_request id
    $service_request_id = null;
    if (ctype_digit((string)$postId)) {
      $service_request_id = (int)$postId;
    } else {
      $stmt = $pdo->prepare('SELECT id FROM service_requests WHERE ticket_no = ? LIMIT 1');
      $stmt->execute([$postId]);
      $service_request_id = $stmt->fetchColumn();
    }

      if ($service_request_id) {
        $debug_info[] = ['service_request_id' => $service_request_id];

        try {
          $pdo->beginTransaction();

          // Delete existing actions for this request (we'll re-insert from the form)
          $del = $pdo->prepare('DELETE FROM service_request_actions WHERE service_request_id = ?');
          $ok = $del->execute([$service_request_id]);
          if (!$ok || $del->errorCode() !== '00000') {
            $save_error = 'Failed to delete existing actions';
            $debug_info[] = ['delete_error' => $del->errorInfo(), 'service_request_id' => $service_request_id];
            $pdo->rollBack();
          } else {
            $debug_info[] = ['deleted_rows' => $del->rowCount()];
          }

          $action_dates = $_POST['action_date'] ?? [];
          $action_times = $_POST['action_time'] ?? [];
          $action_details = $_POST['action_details'] ?? [];
          $action_staff = $_POST['action_staff'] ?? [];
          $action_signatures = $_POST['action_signature_data'] ?? [];
          $action_old_staff_ids = $_POST['action_old_staff_id'] ?? [];
          $action_existing_signature_paths = $_POST['action_existing_signature_path'] ?? [];

          $debug_info[] = ['posted_action_counts' => [count($action_dates), count($action_times), count($action_details), count($action_staff), count($action_signatures)]];

          $insert = $pdo->prepare('INSERT INTO service_request_actions (service_request_id, action_date, action_time, action_details, action_staff_id, action_signature_path) VALUES (?, ?, ?, ?, ?, ?)');

          // prepare upload dir
          $dir = __DIR__ . '/../../../../public/uploads/signatures/';
          if (!is_dir($dir)) mkdir($dir, 0755, true);

          $count = max(count($action_dates), count($action_times), count($action_details), count($action_staff), count($action_signatures));
          for ($i = 0; $i < $count; $i++) {
            $ad = trim($action_dates[$i] ?? '');
            $at = trim($action_times[$i] ?? '');
            $det = trim($action_details[$i] ?? '');
            $staffId = !empty($action_staff[$i]) ? $action_staff[$i] : null;
            $sigPath = null;

            // skip empty rows (no date, time, details and no staff and no signature)
            if ($ad === '' && $at === '' && $det === '' && empty($staffId) && empty($action_signatures[$i])) continue;

            // handle signature data for this action
            $sigData = $action_signatures[$i] ?? '';
            if (!empty($sigData)) {
              // security: only the assigned Action Staff (current user) may supply a signature for this row
              if (empty($sessionUserId) || (string)$staffId !== (string)$sessionUserId) {
                $save_error = 'Signature not allowed: only the assigned Action Staff may sign this action.';
                $debug_info[] = ['action_index' => $i, 'staffId' => $staffId, 'sessionUserId' => $sessionUserId];
                $pdo->rollBack();
                break;
              }
              $data = $sigData;
              if (preg_match('/^data:\w+\/\w+;base64,/', $data)) {
                $data = preg_replace('/^data:\w+\/\w+;base64,/', '', $data);
              }
              $decoded = base64_decode($data);
              if ($decoded !== false) {
                $fname = 'action_' . $service_request_id . '_' . uniqid() . '.png';
                $full = $dir . $fname;
                $res = @file_put_contents($full, $decoded);
                if ($res === false) {
                  $debug_info[] = ['file_write_failed' => $full];
                } else {
                  $sigPath = 'public/uploads/signatures/' . $fname;
                }
              }
            }

            // If no new signature drawn but an existing path was provided, preserve it
            // only when the assigned staff for this row did not change (security / avoid accidental permanence)
            $oldStaff = $action_old_staff_ids[$i] ?? null;
            if (empty($sigPath) && !empty($action_existing_signature_paths[$i]) && $oldStaff !== null && (string)$oldStaff === (string)$staffId) {
              $sigPath = $action_existing_signature_paths[$i];
            }

            // convert empty date/time to nulls for DB
            $dbDate = $ad === '' ? null : $ad;
            $dbTime = $at === '' ? null : $at;

            $okInsert = $insert->execute([$service_request_id, $dbDate, $dbTime, $det, $staffId, $sigPath]);
            if (!$okInsert || $insert->errorCode() !== '00000') {
              $save_error = 'Failed to insert action row ' . $i;
              $debug_info[] = ['insert_error' => $insert->errorInfo(), 'row_index' => $i, 'params' => [$service_request_id, $dbDate, $dbTime, $det, $staffId, $sigPath]];
              $pdo->rollBack();
              break;
            } else {
              $debug_info[] = ['inserted_row' => $i, 'lastInsertId' => $pdo->lastInsertId()];
            }
          }

          if (empty($save_error)) {
            $pdo->commit();
          }

        } catch (Exception $e) {
          try { $pdo->rollBack(); } catch (Exception $rb) {}
          $save_error = 'Exception saving actions: ' . $e->getMessage();
          $debug_info[] = ['exception' => $e->getMessage()];
          error_log('edit_requests actions save error: ' . $e->getMessage());
        }
      }
  } catch (Exception $e) {
    error_log('edit_requests actions save error: ' . $e->getMessage());
  }
  // redirect back to the same page to show saved values if there were no save errors
  if (empty($save_error)) {
    // If the Completed checkbox was checked when saving, update status to Completed
    if (!empty($_POST['completed_checkbox'])) {
      // Ensure at least one action signature exists and belongs to the assigned Action Staff (current user)
      $action_staff_post = $_POST['action_staff'] ?? [];
      $action_signatures_post = $_POST['action_signature_data'] ?? [];
      $action_existing_sig_post = $_POST['action_existing_signature_path'] ?? [];
      $hasValidSigner = false;
      $maxCount = max(count($action_staff_post), count($action_signatures_post), count($action_existing_sig_post));
      for ($j = 0; $j < $maxCount; $j++) {
        $as = $action_staff_post[$j] ?? null;
        $newSig = trim($action_signatures_post[$j] ?? '');
        $existingSig = trim($action_existing_sig_post[$j] ?? '');
        if (!empty($as) && (string)$as === (string)$sessionUserId && (!empty($newSig) || !empty($existingSig))) {
          $hasValidSigner = true;
          break;
        }
      }
      if (!$hasValidSigner) {
        $save_error = 'Cannot mark as Completed: a signature from the assigned Action Staff (you) is required.';
        $debug_info[] = ['completion_requires_assigned_signature' => true, 'sessionUserId' => $sessionUserId];
      } else {
        try {
          if (ctype_digit((string)$postId)) {
            $updateStmt = $pdo->prepare('UPDATE service_requests SET status = :status, updated_at = NOW() WHERE id = :id');
            $updateStmt->execute([':status' => 'Completed', ':id' => $postId]);
          } else {
            $updateStmt = $pdo->prepare('UPDATE service_requests SET status = :status, updated_at = NOW() WHERE ticket_no = :ticket_no');
            $updateStmt->execute([':status' => 'Completed', ':ticket_no' => $postId]);
          }
        } catch (Exception $e) {
          error_log('Failed to update status to Completed in edit_requests_ongoing.php: ' . $e->getMessage());
          $debug_info[] = ['complete_update_error' => $e->getMessage()];
        }
      }
    }

    if (!empty($_POST['completed_checkbox']) && empty($save_error)) {
      // After marking Completed via Save (and no validation errors), take user to the Completed list
      header('Location: completed.php');
      exit;
    }

    header('Location: edit_requests_ongoing.php?id=' . urlencode($postId));
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Details</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Admin common styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <!-- Service Desk specific styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/service-desk.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Fredoka+One:400&display=swap" rel="stylesheet">
  <style>
    /* Style inputs with light background; admin can edit fields here */
    input, textarea, select {
      background-color: #f8f9fa !important;
    }
    /* Increase main content readability: enlarge base font and override tiny inline sizes */
    .main .main-content { font-size: 14px; }
    .main .main-content table,
    .main .main-content td,
    .main .main-content th,
    .main .main-content input,
    .main .main-content select,
    .main .main-content textarea,
    .main .main-content .signature-box {
      font-size: 14px !important;
    }
    .main .main-content .container-fluid { max-width: 1100px; }
    /* Shift Back button left so it doesn't align with centered main content */
    .back-left { position: relative; left: -150px; }
    /* On narrow screens, keep Back button inside viewport */
    @media (max-width: 768px) {
      .back-left { left: 0; }
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
      <div class="sidebar-role">Administrator</div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li><a href="user_management.php"><i class="fa fa-users"></i> User Management</a></li>
          <li><a href="spot_reports.php"><i class="fa fa-file-text"></i> Spot Reports</a></li>
          <li><a href="case_management.php"><i class="fa fa-briefcase"></i> Case Management</a></li>
          <li><a href="apprehended_items.php"><i class="fa fa-archive"></i> Apprehended Items</a></li>
          <li><a href="equipment_management.php"><i class="fa fa-cogs"></i> Equipment Management</a></li>
          <li><a href="assignments.php"><i class="fa fa-tasks"></i> Assignments</a></li>
          <li class="dropdown active">
            <a href="#" class="dropdown-toggle active" id="serviceDeskToggle" data-target="serviceDeskMenu">
              <i class="fa fa-headset"></i> Service Desk 
              <i class="fa fa-chevron-down dropdown-arrow rotated"></i>
            </a>
            <ul class="dropdown-menu show" id="serviceDeskMenu">
              <li><a href="new_requests.php">New Requests <span class="badge">2</span></a></li>
              <li><a href="ongoing_scheduled.php">Ongoing / Scheduled <span class="badge badge-blue">2</span></a></li>
              <li><a href="completed.php">Completed</a></li>
              <li><a href="all_requests.php">All Requests</a></li>
            </ul>
          </li>
          <li><a href="statistical_report.php"><i class="fa fa-chart-bar"></i> Statistical Report</a></li>
        </ul>
      </nav>
    </nav>
    <!-- Main -->
    <div class="main">
      <div class="topbar">
        <div class="topbar-card">
          <div class="topbar-title">Edit Details</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <div class="container-fluid p-4">
          
          <?php
          $request_id = isset($_GET['id']) ? $_GET['id'] : null;

          // Load request from DB
          require_once __DIR__ . '/../../../config/db.php';
          // Ensure PDO throws exceptions for easier debugging when available
          if (isset($pdo) && is_object($pdo)) {
            try { $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); } catch (Exception $e) { error_log('Could not set PDO ERRMODE: ' . $e->getMessage()); }
          }
          $request = null;
          if (!empty($request_id)) {
            try {
              if (ctype_digit((string)$request_id)) {
                $stmt = $pdo->prepare('SELECT * FROM service_requests WHERE id = ? LIMIT 1');
                $stmt->execute([$request_id]);
              } else {
                $stmt = $pdo->prepare('SELECT * FROM service_requests WHERE ticket_no = ? LIMIT 1');
                $stmt->execute([$request_id]);
              }
              $request = $stmt->fetch();
            } catch (Exception $e) {
              error_log('request_details fetch error: ' . $e->getMessage());
              $request = null;
            }
          }

          // Populate display variables (fallback to empty)
          $ticket_no = $request['ticket_no'] ?? $request_id ?? '';
          $ticket_date = '';
          if (!empty($request['ticket_date'])) {
            $ticket_date = date('m/d/Y', strtotime($request['ticket_date']));
          } else if (!empty($request['created_at'])) {
            $ticket_date = date('m/d/Y', strtotime($request['created_at']));
          }

          $requester_name = $request['requester_name'] ?? '';
          $requester_position = $request['requester_position'] ?? 'Project Support Staff';
          $requester_office = $request['requester_office'] ?? 'CENRO Nasipit';
          $requester_division = $request['requester_division'] ?? 'Construction Development Section';
          $requester_phone = $request['requester_phone'] ?? $request['phone'] ?? '';
          $requester_email = $request['requester_email'] ?? 'amyrcamid@gmail.com';
          $request_type = $request['request_type'] ?? 'ASSIST IN THE ORIENTATION OF WATERSHED';
          $request_description = $request['request_description'] ?? $request['description'] ?? '';

          // Optional auth fields (may be empty if not stored)
          // Do not default to a specific person/position here — show DB values or blank
          $auth1_name = $request['auth1_name'] ?? $request['auth1_fullname'] ?? '';
          $auth1_position = $request['auth1_position'] ?? '';
          $auth2_name = $request['auth2_name'] ?? $request['auth2_fullname'] ?? '';
          $auth2_position = $request['auth2_position'] ?? '';

          // Signature URLs (saved as public/uploads/... in DB)
          $requester_sig_url = !empty($request['requester_signature_path']) ? (defined('BASE_URL') ? BASE_URL . '/' . ltrim($request['requester_signature_path'], '/') : '/' . ltrim($request['requester_signature_path'], '/')) : '';
          $auth1_sig_url = !empty($request['auth1_signature_path']) ? (defined('BASE_URL') ? BASE_URL . '/' . ltrim($request['auth1_signature_path'], '/') : '/' . ltrim($request['auth1_signature_path'], '/')) : '';
          $auth2_sig_url = !empty($request['auth2_signature_path']) ? (defined('BASE_URL') ? BASE_URL . '/' . ltrim($request['auth2_signature_path'], '/') : '/' . ltrim($request['auth2_signature_path'], '/')) : '';

          // Load existing action rows for this request so they can be displayed in the form
          $request_actions = [];
          try {
            $service_request_id_for_actions = null;
            if (!empty($request['id'])) {
              $service_request_id_for_actions = (int)$request['id'];
            } else if (!empty($request_id) && ctype_digit((string)$request_id)) {
              $service_request_id_for_actions = (int)$request_id;
            } else if (!empty($request_id)) {
              $tstmt = $pdo->prepare('SELECT id FROM service_requests WHERE ticket_no = ? LIMIT 1');
              $tstmt->execute([$request_id]);
              $service_request_id_for_actions = $tstmt->fetchColumn();
            }
            if (!empty($service_request_id_for_actions)) {
              $as = $pdo->prepare('SELECT * FROM service_request_actions WHERE service_request_id = ? ORDER BY created_at ASC');
              $as->execute([$service_request_id_for_actions]);
              $request_actions = $as->fetchAll(PDO::FETCH_ASSOC);
            }
          } catch (Exception $e) {
            error_log('fetch request actions error: ' . $e->getMessage());
            $request_actions = [];
          }
          ?>

          <?php if (!empty($save_error) || !empty($debug_info)): ?>
            <div class="mb-3">
              <div class="alert alert-warning" role="alert">
                <strong>Save diagnostics:</strong>
                <?php if (!empty($save_error)): ?>
                  <div><?php echo htmlspecialchars($save_error); ?></div>
                <?php endif; ?>
                <?php if (!empty($debug_info)): ?>
                  <pre style="white-space:pre-wrap; margin-top:8px; background:#fff; color:#000; padding:8px; border:1px solid #ddd"><?php echo htmlspecialchars(print_r($debug_info, true)); ?></pre>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
          
          <!-- Professional toolbar: Back left, actions right -->
          <div class="row mb-3">
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <a href="ongoing_scheduled.php" class="btn btn-outline-secondary btn-sm back-left">
                    <i class="fa fa-arrow-left me-2"></i>Back
                  </a>
                </div>
                <div>
                  <?php if (!empty($request_id) && !empty($request)): ?>
                        <!-- Approve button removed per request -->
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Service Request Form (editable) -->
          <form id="editForm" method="post" action="edit_requests_ongoing.php?id=<?php echo urlencode($request_id); ?>" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($request_id); ?>">
            <input type="hidden" name="save_changes" value="1">
            <div style="max-width: 1100px; margin: 0 auto; background: white; font-family: Arial, sans-serif; font-size: 14px;">
            
            <!-- Header Section with Border -->
            <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
              <tr>
                <td rowspan="2" style="width: 100px; text-align: center; vertical-align: middle; padding: 8px; border-right: 1px solid black;">
                  <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo" style="width: 70px; height: 70px;">
                </td>
                <td style="text-align: center; vertical-align: middle; padding: 12px; border-right: 1px solid black; border-bottom: 1px solid black;">
                  <div style="font-size: 16px; font-weight: bold; margin-bottom: 3px;">DENR-PENRO AGUSAN DEL NORTE</div>
                  <div style="font-size: 12px;">Information and Communication Technology Unit (ICTU)</div>
                </td>
                <td style="width: 200px; padding: 0; border-bottom: 1px solid black;">
                  <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                    <tr>
                      <td style="border-bottom: 1px solid black; border-right: 1px solid black; padding: 4px; font-weight: bold; width: 60%;">Department ID No.</td>
                      <td style="border-bottom: 1px solid black; padding: 4px; text-align: center;">R13-CN-FO-003</td>
                    </tr>
                    <tr>
                      <td style="border-right: 1px solid black; padding: 4px; font-weight: bold;">Revision No.</td>
                      <td style="padding: 4px; text-align: center;">1</td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td style="text-align: center; vertical-align: middle; padding: 12px; border-right: 1px solid black; font-size: 14px; font-weight: bold;">
                  SERVICE REQUEST FROM (SRF)
                </td>
                <td style="width: 200px; padding: 0;">
                  <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                    <tr>
                      <td style="border-right: 1px solid black; padding: 4px; font-weight: bold; width: 60%;">Effectivity</td>
                      <td style="padding: 4px; text-align: center;">9/1/2022</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            <!-- Reminder Section -->
            <div style="padding: 10px; border-left: 1px solid black; border-right: 1px solid black;">
              <p style="margin-bottom: 10px; font-size: 9px; line-height: 1.2; text-align: justify;">
                <strong>Reminder:</strong> Please complete this form and submit it at the PENRO ICT Unit Service Desk located on the ground floor PENRO Agusan del Norte Building, Tiniwisan, Butuan City or email a scanned a copy to <span style="color: blue;">ictu@denr.gov.ph</span>. Once processed, a Technical Support Representative will contact you to schedule service.
              </p>
              
              <table style="width: 100%; margin-bottom: 10px;">
                <tr>
                  <td style="width: 50%;"><strong style="font-size: 10px;">Ticket No: <?php echo htmlspecialchars($ticket_no); ?></strong></td>
                  <td style="width: 50%; text-align: right;"><strong style="font-size: 10px;">Date (mm/dd/yyyy): <?php echo htmlspecialchars($ticket_date); ?></strong></td>
                </tr>
              </table>
            </div>

            <!-- Requester's Information -->
            <div style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="background-color: #f0f0f0; padding: 5px 10px; border-bottom: 1px solid black; font-weight: bold; font-size: 10px;">
                    Requester's Information
                  </td>
                </tr>
              </table>
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-weight: bold; width: 12%; font-size: 9px;">Name:</td>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; width: 38%; font-size: 9px;">
                    <?php echo htmlspecialchars($requester_name); ?>
                  </td>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-weight: bold; width: 12%; font-size: 9px;">Position:</td>
                  <td style="border-bottom: 1px solid black; padding: 5px; width: 38%; font-size: 9px;"><?php echo htmlspecialchars($requester_position); ?></td>
                </tr>
                <tr>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-weight: bold; font-size: 9px;">Office:</td>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-size: 9px;"><?php echo htmlspecialchars($requester_office); ?></td>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-weight: bold; font-size: 9px;">Division/Section:</td>
                  <td style="border-bottom: 1px solid black; padding: 5px; font-size: 9px;"><?php echo htmlspecialchars($requester_division); ?></td>
                </tr>
                <tr>
                  <td style="border-right: 1px solid black; padding: 5px; font-weight: bold; font-size: 9px;">Phone Number:</td>
                  <td style="border-right: 1px solid black; padding: 5px; font-size: 9px;">
                    <?php echo htmlspecialchars($requester_phone); ?>
                  </td>
                  <td style="border-right: 1px solid black; padding: 5px; font-weight: bold; font-size: 9px;">Email Address:</td>
                  <td style="padding: 5px; font-size: 9px;"><?php echo htmlspecialchars($requester_email); ?></td>
                </tr>
              </table>
            </div>

            <!-- Request Information -->
            <div style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="background-color: #f0f0f0; padding: 5px 10px; border-bottom: 1px solid black; font-weight: bold; font-size: 10px;">
                    Request Information
                  </td>
                </tr>
                <tr>
                  <td style="border-bottom: 1px solid black; padding: 5px;">
                    <table style="width: 100%; border-collapse: collapse;">
                      <tr>
                        <td style="border-right: 1px solid black; padding: 5px; font-weight: bold; width: 20%; font-size: 9px;">Type of Request:</td>
                        <td style="padding: 5px; font-weight: bold; font-size: 9px;"><?php echo htmlspecialchars($request_type); ?></td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
              
              <div style="padding: 8px;">
                <div style="font-weight: bold; margin-bottom: 5px; font-size: 9px;">DESCRIPTION OF REQUEST (Please clearly write down the details of the request.)</div>
                <div style="border: 1px solid black; padding: 12px; min-height: 100px; position: relative;">
                  <div style="font-size: 9px;">
                    <?php echo nl2br(htmlspecialchars($request_description)); ?>
                  </div>
                    <div style="position: absolute; bottom: 12px; right: 15px; text-align: center;">
                    <?php if (!empty($requester_sig_url)): ?>
                      <img src="<?php echo htmlspecialchars($requester_sig_url); ?>" alt="Requester Signature" style="max-width:140px; height:auto; display:block; margin-bottom:4px;" />
                    <?php else: ?>
                      <div style="font-family: 'Brush Script MT', cursive; font-size: 12px; font-style: italic; color: #003366; text-align: center; margin-bottom: 3px;">
                        <?php echo htmlspecialchars($requester_name); ?>
                      </div>
                      <div style="border-bottom: 1px solid black; width: 100px; margin-bottom: 2px;"></div>
                    <?php endif; ?>
                    <div style="font-size: 8px;">Requester Signature</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Authorization Section -->
            <div style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="background-color: #f0f0f0; padding: 5px 10px; border-bottom: 1px solid black; font-weight: bold; font-size: 10px;">
                    Authorization
                  </td>
                </tr>
              </table>
              
              <div style="padding: 6px;">
                <p style="font-size: 8px; margin: 0 0 6px 0; line-height: 1.1; text-align: justify;">
                  All requests for service must be approved by the appropriate manager/supervisor (at least division chief, OIC, immediate supervisor or head clerk staff of the requester). By signing below, the manager/supervisor certifies that the service is required.
                </p>
              </div>
              
              <table style="width: 100%; border-collapse: collapse; border-top: 1px solid black; border-bottom: 1px solid black;">
                <tr>
                  <td style="border-right: 1px solid black; padding: 4px; font-weight: bold; width: 15%; font-size: 9px;">Full Name:</td>
                  <td style="border-right: 1px solid black; padding: 2px; width: 35%;">
                    <input type="text" name="auth1_name" value="<?php echo htmlspecialchars($auth1_name); ?>" style="width: 100%; border: none; font-size: 9px; padding: 2px;" />
                  </td>
                  <td style="border-right: 1px solid black; padding: 4px; font-weight: bold; width: 20%; font-size: 9px;">Title/Position:</td>
                  <td style="padding: 2px; width: 30%;">
                    <input type="text" name="auth1_position" value="<?php echo htmlspecialchars($auth1_position); ?>" style="width: 100%; border: none; font-size: 9px; padding: 2px;" />
                  </td>
                </tr>
              </table>

              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="width: 50%; padding: 4px; border-right: 1px solid black;">
                    <div class="signature-box" data-field="auth1" style="border: 1px solid black; text-align: center; height: 50px; display: flex; align-items: center; justify-content: center; padding: 6px; cursor: pointer;">
                      <?php if (!empty($auth1_sig_url)): ?>
                        <img id="auth1_preview" src="<?php echo htmlspecialchars($auth1_sig_url); ?>" alt="Auth1 Signature" style="max-height:48px; max-width:100%; display:block;" />
                      <?php else: ?>
                        <div id="auth1_preview" style="width:100%; height:100%;"></div>
                      <?php endif; ?>
                    </div>
                    <input type="hidden" name="auth1_signature_data" id="auth1_signature_data" value="">
                    <div style="text-align:center; font-size:8px; margin-top:6px;">
                      <div style="border-bottom:1px solid #000; width:140px; margin:0 auto 4px; height:0;"></div>
                      <div style="font-size:9px;">Signature (Manager/Supervisor)</div>
                    </div>
                  </td>
                  <td style="width: 50%; padding: 4px;">
                    <div style="border: 1px solid black; text-align: center; height: 50px; display: flex; align-items: center; justify-content: center; padding: 2px;">
                      <input type="date" name="auth1_date" value="<?php echo !empty($request['auth1_date']) ? htmlspecialchars($request['auth1_date']) : ''; ?>" style="border: none; font-size: 8px; text-align: center; width: 100%;" />
                    </div>
                  </td>
                </tr>
              </table>
            </div>

            <!-- Infrastructure Service Authorization -->
            <div style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="background-color: #f0f0f0; padding: 5px 10px; border-bottom: 1px solid black; font-weight: bold; font-size: 10px;">
                    Infrastructure Service Authorization
                  </td>
                </tr>
              </table>
              
              <div style="padding: 6px;">
                <p style="font-size: 8px; margin: 0 0 6px 0; line-height: 1.1; text-align: justify;">
                  All requests for service must be approved by the appropriate manager/supervisor (at least division chief, OIC, immediate supervisor or head clerk staff of the requester). By signing below, the manager/supervisor certifies that the service is required.
                </p>
              </div>
              
              <table style="width: 100%; border-collapse: collapse; border-top: 1px solid black; border-bottom: 1px solid black;">
                <tr>
                  <td style="border-right: 1px solid black; padding: 4px; font-weight: bold; width: 15%; font-size: 9px;">Full Name:</td>
                  <td style="border-right: 1px solid black; padding: 2px; width: 35%;">
                    <input type="text" name="auth2_name" value="<?php echo htmlspecialchars($auth2_name); ?>" style="width: 100%; border: none; font-size: 9px; padding: 2px;" />
                  </td>
                  <td style="border-right: 1px solid black; padding: 4px; font-weight: bold; width: 20%; font-size: 9px;">Title/Position:</td>
                  <td style="padding: 2px; width: 30%;">
                    <input type="text" name="auth2_position" value="<?php echo htmlspecialchars($auth2_position); ?>" style="width: 100%; border: none; font-size: 9px; padding: 2px;" />
                  </td>
                </tr>
              </table>

              <table style="width: 100%; border-collapse: collapse; border-bottom: 1px solid black;">
                <tr>
                  <td style="width: 50%; padding: 4px; border-right: 1px solid black;">
                    <div class="signature-box" data-field="auth2" style="border: 1px solid black; text-align: center; height: 50px; display: flex; align-items: center; justify-content: center; padding: 6px; cursor: pointer;">
                      <?php if (!empty($auth2_sig_url)): ?>
                        <img id="auth2_preview" src="<?php echo htmlspecialchars($auth2_sig_url); ?>" alt="Auth2 Signature" style="max-height:48px; max-width:100%; display:block;" />
                      <?php else: ?>
                        <div id="auth2_preview" style="width:100%; height:100%;"></div>
                      <?php endif; ?>
                    </div>
                    <input type="hidden" name="auth2_signature_data" id="auth2_signature_data" value="">
                    <div style="text-align:center; font-size:8px; margin-top:6px;">
                      <div style="border-bottom:1px solid #000; width:140px; margin:0 auto 4px; height:0;"></div>
                      <div style="font-size:9px;">Signature (Manager/Supervisor)</div>
                    </div>
                  </td>
                  <td style="width: 50%; padding: 4px;">
                    <div style="border: 1px solid black; text-align: center; height: 50px; display: flex; align-items: center; justify-content: center; padding: 2px;">
                      <input type="date" name="auth2_date" value="<?php echo !empty($request['auth2_date']) ? htmlspecialchars($request['auth2_date']) : ''; ?>" style="border: none; font-size: 8px; text-align: center; width: 100%;" placeholder="Date" />
                    </div>
                  </td>
                </tr>
              </table>

              <div style="padding: 6px;">
                <p style="font-weight: bold; font-size: 9px;">For PENRO ICT Staff only (Use back of the Form or Separate sheet if necessary)</p>
              </div>
            </div>

            <!-- Staff Table -->
            <?php
            // Fetch users with role Admin for staff dropdowns (only Admin full names)
            try {
              $stmt = $pdo->prepare("SELECT id, full_name, role FROM users WHERE status = 1 AND TRIM(LOWER(role)) = 'admin' ORDER BY full_name ASC");
              $stmt->execute();
              $staff_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
              $staff_users = [];
              error_log('fetch staff users error: ' . $e->getMessage());
            }
            ?>
            <div style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">
              <table style="width: 100%; border-collapse: collapse;">
                <thead>
                  <tr>
                    <th style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; width: 15%; text-align: center; font-weight: bold; font-size: 9px;">Date</th>
                    <th style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; width: 15%; text-align: center; font-weight: bold; font-size: 9px;">Time</th>
                    <th style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; width: 40%; text-align: center; font-weight: bold; font-size: 9px;">Action Details</th>
                    <th style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; width: 15%; text-align: center; font-weight: bold; font-size: 9px;">Action Staff</th>
                    <th style="border-bottom: 1px solid black; padding: 4px; width: 15%; text-align: center; font-weight: bold; font-size: 9px;">Signature</th>
                  </tr>
                </thead>
                <tbody id="actions_tbody">
                  <?php if (!empty($request_actions)): ?>
                    <?php foreach ($request_actions as $idx => $act): $i = $idx + 1; ?>
                      <tr data-action-row="<?php echo $i; ?>">
                        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px; height: 25px;">
                          <input type="date" name="action_date[]" value="<?php echo htmlspecialchars($act['action_date']); ?>" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
                        </td>
                        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                          <input type="time" name="action_time[]" value="<?php echo htmlspecialchars($act['action_time']); ?>" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
                        </td>
                        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                          <textarea name="action_details[]" style="width: 100%; border: none; font-size: 8px; padding: 2px; height: 20px; resize: none;" placeholder="Action details..."><?php echo htmlspecialchars($act['action_details']); ?></textarea>
                        </td>
                        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                          <select name="action_staff[]" class="form-select form-select-sm" style="width:100%; border:none; font-size:8px; padding:2px;">
                            <option value="">-- Select staff --</option>
                            <?php foreach ($staff_users as $su): ?>
                              <option value="<?php echo htmlspecialchars($su['id']); ?>" <?php echo ($su['id'] == $act['action_staff_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($su['full_name']); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </td>
                        <td style="border-bottom: 1px solid black; padding: 2px;">
                          <div class="signature-box" data-field="action_sig_<?php echo $i; ?>" data-staff-id="<?php echo htmlspecialchars($act['action_staff_id']); ?>" style="border: 1px solid #000; height:40px; display:flex; align-items:center; justify-content:center; padding:4px; cursor:pointer;">
                            <?php if (!empty($act['action_signature_path'])): ?>
                              <img id="action_sig_<?php echo $i; ?>_preview" src="<?php echo htmlspecialchars((defined('BASE_URL') ? BASE_URL . '/' : '/') . ltrim($act['action_signature_path'], '/')); ?>" style="max-height:48px; max-width:100%; display:block;" />
                            <?php else: ?>
                              <div id="action_sig_<?php echo $i; ?>_preview" style="width:100%; height:100%;"></div>
                            <?php endif; ?>
                          </div>
                          <input type="hidden" name="action_signature_data[]" id="action_sig_<?php echo $i; ?>_signature_data" value="">
                          <input type="hidden" name="action_existing_signature_path[]" value="<?php echo htmlspecialchars($act['action_signature_path']); ?>">
                          <input type="hidden" name="action_old_staff_id[]" value="<?php echo htmlspecialchars($act['action_staff_id']); ?>">
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <?php for ($i=1; $i<=4; $i++): ?>
                      <tr data-action-row="<?php echo $i; ?>">
                        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px; height: 25px;">
                          <input type="date" name="action_date[]" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
                        </td>
                        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                          <input type="time" name="action_time[]" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
                        </td>
                        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                          <textarea name="action_details[]" style="width: 100%; border: none; font-size: 8px; padding: 2px; height: 20px; resize: none;" placeholder="Action details..."></textarea>
                        </td>
                        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                          <select name="action_staff[]" class="form-select form-select-sm" style="width:100%; border:none; font-size:8px; padding:2px;">
                            <option value="">-- Select staff --</option>
                            <?php foreach ($staff_users as $su): ?>
                              <option value="<?php echo htmlspecialchars($su['id']); ?>"><?php echo htmlspecialchars($su['full_name']); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </td>
                        <td style="border-bottom: 1px solid black; padding: 2px;">
                          <div class="signature-box" data-field="action_sig_<?php echo $i; ?>" data-staff-id="" style="border: 1px solid #000; height:40px; display:flex; align-items:center; justify-content:center; padding:4px; cursor:pointer;">
                            <div id="action_sig_<?php echo $i; ?>_preview" style="width:100%; height:100%;"></div>
                          </div>
                          <input type="hidden" name="action_signature_data[]" id="action_sig_<?php echo $i; ?>_signature_data" value="">
                          <input type="hidden" name="action_existing_signature_path[]" value="">
                          <input type="hidden" name="action_old_staff_id[]" value="">
                        </td>
                      </tr>
                    <?php endfor; ?>
                  <?php endif; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="5" style="padding:8px; text-align:left;">
                      <button type="button" id="addActionRow" class="btn btn-sm btn-outline-secondary">+ Add Row</button>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <!-- Feedback Section -->
            <div style="border-left: 1px solid black; border-right: 1px solid black;">
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="padding: 8px;">
                    <div style="font-size: 9px; margin-bottom: 6px;">
                      <strong>Feedback Rating:</strong> 
                      <input type="checkbox" id="excellent" name="feedback_rating" value="excellent" disabled>
                      <label for="excellent"> Excellent</label>
                      <input type="checkbox" id="very_satisfactory_feed" name="feedback_rating" value="very_satisfactory" style="margin-left: 10px;" disabled>
                      <label for="very_satisfactory_feed"> Very Satisfactory</label>
                      <input type="checkbox" id="below_satisfactory" name="feedback_rating" value="below_satisfactory" style="margin-left: 10px;" disabled>
                      <label for="below_satisfactory"> Below Satisfactory</label>
                      <input type="checkbox" id="poor" name="feedback_rating" value="poor" style="margin-left: 10px;" disabled>
                      <label for="poor"> Poor</label>
                    </div>
                    <div style="margin-bottom: 6px; font-size: 9px;">
                      <?php $isCompleted = !empty($request['status']) && strtolower($request['status']) === 'completed'; ?>
                      <input type="checkbox" id="completed_checkbox" name="completed_checkbox" value="1" <?php echo $isCompleted ? 'checked disabled' : ''; ?> />
                      <label for="completed_checkbox"> Completed</label>
                      <!-- completeForm moved below outside the main edit form to avoid accidental submission -->
                    </div>
                    <div style="font-weight: bold; font-size: 9px; margin-bottom: 6px;">Acknowledged by:</div>
                  </td>
                </tr>
              </table>
            </div>

            <!-- Footer -->
            <div style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; padding: 12px;">
              <table style="width: 100%; margin-bottom: 10px;">
                <tr>
                  <td style="width: 50%; padding-right: 10px;">
                    <div style="border-bottom: 1px solid black; height: 20px; margin-bottom: 2px; position: relative;">
                      <span style="position: absolute; bottom: 2px; left: 0; width: 100%; font-size: 10px; font-weight:600; color: #222;">
                        <?php echo htmlspecialchars($requester_name ?: '[Client will sign here]'); ?>
                      </span>
                    </div>
                    <div style="font-size: 8px;">Signature over printed name</div>
                  </td>
                  <td style="width: 50%; padding-left: 10px;">
                    <div style="border-bottom: 1px solid black; height: 20px; margin-bottom: 2px; position: relative;">
                      <input type="datetime-local" style="position: absolute; bottom: 2px; left: 0; width: 100%; border: none; font-size: 8px; background: transparent;" />
                    </div>
                    <div style="font-size: 8px;">Date/Time</div>
                  </td>
                </tr>
              </table>

              <div style="text-align: right;">
                <div style="font-size: 8px; font-weight: bold;">Ref: NIMD Service Request Form 22 March 2021</div>
              </div>
            </div>

            <div class="d-flex justify-content-end mt-2 mb-4">
              <button form="editForm" type="submit" class="btn btn-primary btn-sm">Save Changes</button>
            </div>

          </div>
          </form>

            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Signature Pad -->
  <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
  <!-- Admin Dashboard JavaScript -->
  <script src="../../../../public/assets/js/admin/dashboard.js"></script>
  <!-- Admin Navigation JavaScript -->
  <script src="../../../../public/assets/js/admin/navigation.js"></script>

  <!-- Hidden complete form (kept outside editForm so Save Changes doesn't submit status_action) -->
  <form id="completeForm" method="post" style="display:none;">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($request_id); ?>" />
    <input type="hidden" name="status_action" value="complete" />
  </form>

  <!-- Signature Pad Modal + Handler -->
  <div class="modal fade" id="signatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="signatureModalLabel">Draw Signature</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <canvas id="sigCanvas" style="border:1px solid #ccc; width:100%; height:200px;"></canvas>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" id="sigClear">Clear</button>
          <button type="button" class="btn btn-primary btn-sm" id="sigSave">Save</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    (function(){
      // visual style for required signature (red edge + badge + subtle pulse)
      const style = document.createElement('style');
      style.innerHTML = `
        .signature-box{ position: relative; }
        .signature-box.sig-required{ border:2px solid #b72b2b !important; box-shadow:0 0 8px rgba(183,43,43,0.18); animation: sigPulse 1.6s ease-in-out infinite; }
        .signature-box.sig-required::after{ content: 'Required'; position: absolute; top: -10px; right: -2px; background: #b72b2b; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 3px; font-weight: 700; }
        @keyframes sigPulse{ 0%{ box-shadow:0 0 0 rgba(183,43,43,0.06);} 50%{ box-shadow:0 0 14px rgba(183,43,43,0.12);} 100%{ box-shadow:0 0 0 rgba(183,43,43,0.06);} }
      `;
      document.head.appendChild(style);
      const CURRENT_USER_ID = <?php echo json_encode($sessionUserId); ?>;
      let currentField = null;
      const sigModalEl = document.getElementById('signatureModal');
      const canvas = document.getElementById('sigCanvas');
      let signaturePad = null;
      const sigModal = new bootstrap.Modal(sigModalEl);

      function ensurePreviewIsImg(id) {
        const existing = document.getElementById(id);
        if (!existing) return null;
        if (existing.tagName === 'IMG') return existing;
        const img = document.createElement('img');
        img.id = id;
        img.style.maxHeight = '48px';
        img.style.maxWidth = '100%';
        img.style.display = 'block';
        existing.parentNode.replaceChild(img, existing);
        return img;
      }

      function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = canvas.getBoundingClientRect();
        // guard: if modal hidden, rect may be 0 — avoid setting zero size
        const w = rect.width || 400;
        const h = rect.height || 200;
        canvas.width = w * ratio;
        canvas.height = h * ratio;
        const ctx = canvas.getContext('2d');
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(ratio, ratio);
      }

      function createSignaturePad() {
        if (signaturePad) {
          try { signaturePad.off && signaturePad.off(); } catch (e) {}
          signaturePad = null;
        }
        signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgba(255,255,255,0)' });
      }

      window.addEventListener('resize', function(){
        const prevData = signaturePad && !signaturePad.isEmpty() ? signaturePad.toDataURL() : null;
        resizeCanvas();
        createSignaturePad();
        if (prevData) {
          try { signaturePad.fromDataURL(prevData); } catch (e) { console.warn('restore prev signature failed', e); }
        }
      });

      sigModalEl.addEventListener('shown.bs.modal', function () {
        const previewId = (currentField || '') + '_preview';
        resizeCanvas();
        createSignaturePad();
        const preview = document.getElementById(previewId);
        if (preview && preview.tagName === 'IMG' && preview.src) {
          if (preview.src.indexOf('data:') === 0) {
            try { signaturePad.fromDataURL(preview.src); } catch (e) { signaturePad.clear(); console.warn('fromDataURL failed', e); }
          } else {
            signaturePad.clear();
          }
        } else {
          signaturePad.clear();
        }
      });

      // Use event delegation so dynamically added .signature-box elements work
      document.addEventListener('click', function(e){
        const box = e.target.closest && e.target.closest('.signature-box');
        if (!box) return;
        const field = box.getAttribute('data-field') || '';
        // If this is an action signature, only allow the assigned staff (current user) to open the pad
          if (field.indexOf('action_sig_') === 0) {
          let staffId = box.getAttribute('data-staff-id') || '';
          if (!staffId) {
            const row = box.closest && box.closest('tr[data-action-row]');
            if (row) {
              const sel = row.querySelector('select[name="action_staff[]"]');
              if (sel) staffId = sel.value || '';
            }
          }
          if (!staffId || String(staffId) !== String(CURRENT_USER_ID)) {
            alert('Only the assigned Action Staff may sign this action.');
            return;
          }
        }
        currentField = field;
        sigModal.show();
      });

      document.getElementById('sigClear').addEventListener('click', function(){ if (signaturePad) signaturePad.clear(); });

      function savePadToHidden() {
        if (!signaturePad) return false;
        const hidden = document.getElementById(currentField + '_signature_data');
        const previewId = (currentField || '') + '_preview';
        const preview = ensurePreviewIsImg(previewId) || document.getElementById(previewId);
        try {
          if (signaturePad.isEmpty()) {
            if (preview) { preview.src = ''; preview.style.display = 'none'; }
            if (hidden) hidden.value = '';
            return false;
          }
          let dataURL;
          try {
            dataURL = signaturePad.toDataURL('image/png');
          } catch (err) {
            console.warn('signaturePad.toDataURL failed, falling back to canvas.toDataURL', err);
            dataURL = canvas.toDataURL('image/png');
          }
          if (hidden) hidden.value = dataURL;
          if (preview) { preview.src = dataURL; preview.style.display = 'block'; }
          console.log('Signature saved to hidden for', currentField);
          return true;
        } catch (err) {
          console.error('Signature export failed:', err);
          alert('Hindi ma-save ang signature: browser security restriction or invalid image. I-clear at muling i-draw, o i-upload ang scanned signature.');
          return false;
        }
      }

      document.getElementById('sigSave').addEventListener('click', function(){
        savePadToHidden();
        sigModal.hide();
      });

      sigModalEl.addEventListener('hide.bs.modal', function () {
        try { savePadToHidden(); } catch (e) { console.error('autosave failed', e); }
      });

      const editForm = document.getElementById('editForm');
      if (editForm) {
        editForm.addEventListener('submit', function(e){
          const a1 = document.getElementById('auth1_signature_data');
          const a2 = document.getElementById('auth2_signature_data');
          console.log('Submitting form - auth1 signature length:', a1 && a1.value ? a1.value.length : 0, 'auth2:', a2 && a2.value ? a2.value.length : 0);
          // Client-side: if Completed is checked, ensure at least one action signature exists for CURRENT_USER_ID
          const completed = document.getElementById('completed_checkbox');
            function findMissingSignatures(){
              const rows = document.querySelectorAll('tr[data-action-row]');
              const missing = [];
              rows.forEach(function(row){
                const sel = row.querySelector('select[name="action_staff[]"]');
                if (!sel) return;
                const staffId = sel.value || '';
                // only consider rows that have an assigned Action Staff
                if (!staffId) return;
                const idx = row.getAttribute('data-action-row');
                const hid = document.getElementById('action_sig_' + idx + '_signature_data');
                const existing = row.querySelector('input[name="action_existing_signature_path[]"]');
                const hasNew = hid && hid.value && hid.value.trim() !== '';
                const hasExisting = existing && existing.value && existing.value.trim() !== '';
                if (!hasNew && !hasExisting) missing.push({row: row, staffId: staffId, staffName: (sel.options[sel.selectedIndex] && sel.options[sel.selectedIndex].text) || ''});
              });
              return missing;
            }

            function updateHighlights(missing){
              // clear previous highlights
              document.querySelectorAll('.signature-box.sig-required').forEach(function(el){ el.classList.remove('sig-required'); });
              if (!missing || missing.length === 0) return;
              missing.forEach(function(item){
                const box = item.row.querySelector('.signature-box');
                if (box) box.classList.add('sig-required');
              });
            }

            function validateCompletedUI(showAlert){
              if (!completed || !completed.checked) { updateHighlights([]); return true; }
              const missing = findMissingSignatures();
              updateHighlights(missing);
              if (missing.length > 0) {
                        if (showAlert) {
                          const names = missing.map(m=>m.staffName || m.staffId).join(', ');
                          alert('Cannot mark as Completed. Missing signature from the assigned Action Staff: ' + names);
                        }
                if (missing[0] && missing[0].row) missing[0].row.scrollIntoView({behavior:'smooth', block:'center'});
                return false;
              }
              return true;
            }

            // live: when Completed checkbox toggled, validate and highlight
            if (completed){
              completed.addEventListener('change', function(){ validateCompletedUI(false); });
            }

            // when any action_staff select changes, update the data-staff-id and revalidate
            document.getElementById('actions_tbody').addEventListener('change', function(ev){
              const sel = ev.target.closest && ev.target.closest('select[name="action_staff[]"]');
              if (sel) {
                const row = sel.closest('tr[data-action-row]');
                if (row) {
                  const idx = row.getAttribute('data-action-row');
                  const box = row.querySelector('.signature-box');
                  if (box) box.setAttribute('data-staff-id', sel.value || '');
                }
                validateCompletedUI(false);
              }
            });

            // when a signature hidden input changes (new signature saved), remove highlight for that row
            document.getElementById('actions_tbody').addEventListener('input', function(ev){
              const hid = ev.target.closest && ev.target.closest('input[id^="action_sig_"]');
              if (hid && hid.id) {
                // extract index
                const m = hid.id.match(/action_sig_(\d+)_signature_data/);
                if (m) {
                  const idx = m[1];
                  const row = document.querySelector('tr[data-action-row="' + idx + '"]');
                  if (row) {
                    const box = row.querySelector('.signature-box');
                    if (box) box.classList.remove('sig-required');
                  }
                }
                validateCompletedUI(false);
              }
            });

            // final check on submit: show alert and prevent submit when missing
            if (!validateCompletedUI(false)) { e.preventDefault(); validateCompletedUI(true); return false; }
        });
      }

      // Add-row handling
      (function(){
        const tbody = document.getElementById('actions_tbody');
        const addBtn = document.getElementById('addActionRow');
        let nextIndex = tbody.querySelectorAll('tr[data-action-row]').length + 1;
        function createRow(index) {
          const tr = document.createElement('tr');
          tr.setAttribute('data-action-row', index);
          tr.innerHTML = `
            <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px; height: 25px;">
              <input type="date" name="action_date[]" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
            </td>
            <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
              <input type="time" name="action_time[]" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
            </td>
            <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
              <textarea name="action_details[]" style="width: 100%; border: none; font-size: 8px; padding: 2px; height: 20px; resize: none;" placeholder="Action details..."></textarea>
            </td>
            <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
              <select name="action_staff[]" class="form-select form-select-sm" style="width:100%; border:none; font-size:8px; padding:2px;">
                <option value="">-- Select staff --</option>
                <?php foreach ($staff_users as $su): ?>
                  <option value="<?php echo htmlspecialchars($su['id']); ?>"><?php echo htmlspecialchars($su['full_name']); ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td style="border-bottom: 1px solid black; padding: 2px;">
              <div class="signature-box" data-field="action_sig_${index}" data-staff-id="" style="border: 1px solid #000; height:40px; display:flex; align-items:center; justify-content:center; padding:4px; cursor:pointer;">
                <div id="action_sig_${index}_preview" style="width:100%; height:100%;"></div>
              </div>
              <input type="hidden" name="action_signature_data[]" id="action_sig_${index}_signature_data" value="">
              <input type="hidden" name="action_existing_signature_path[]" value="">
              <input type="hidden" name="action_old_staff_id[]" value="">
            </td>
          `;
          return tr;
        }
        if (addBtn) {
          addBtn.addEventListener('click', function(){
            const row = createRow(nextIndex++);
            tbody.appendChild(row);
          });
        }
      })();

      try { resizeCanvas(); createSignaturePad(); } catch (e) { console.warn('initial signature pad setup failed', e); }
    })();
  </script>
</body>
</html>