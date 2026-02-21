<?php
session_start();

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: /prototype/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Request Details</title>
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
    /* Prevent horizontal scroll and keep main containers free to expand */
    html, body { overflow-x: hidden; }
    .layout, .main, .main-content, .container-fluid, #viewForm, #viewForm > div { overflow: visible !important; max-height: none !important; height: auto !important; }
    /* Shift Back button left so it doesn't align with centered main content */
    .back-left { position: relative; left: -150px; }
    @media (max-width: 768px) { .back-left { left: 0; } }
    /* Remove default border for ack signature box (screen and print) */
    #ack_sig_box { border: none !important; box-shadow: none !important; background: transparent !important; }
    /* Print styles: use Legal (8.5in x 14in) portrait with a comfortable margin */
    /* Explicit portrait orientation helps browsers choose the correct layout */
    /* Reduce margins slightly to gain printable space for single-page output */
    @page { size: legal portrait; margin: 8mm; }
    @media print {
      html, body { background: #fff !important; color-adjust: exact; -webkit-print-color-adjust: exact; }
      /* Hide navigation, controls and non-essential UI */
      .sidebar, .topbar, .sidebar-nav, .btn, .dropdown, .modal, .no-print, .navigation, .topbar-card { display: none !important; }
      /* Expand main content to full width for printing */
      .layout, .main, .main-content, .container-fluid { width: 100% !important; max-width: none !important; padding: 0 !important; margin: 0 !important; }
      /* Ensure tables and borders print correctly */
      table { page-break-inside: avoid; }
      tr, td, th { -webkit-print-color-adjust: exact; color-adjust: exact; }
      /* Avoid clipping of images/signatures */
      img { max-width: 100% !important; height: auto !important; }
      /* Remove pointer cursors and interactive affordances */
      .signature-box { cursor: default !important; }
      /* Specifically hide border on the acknowledgment signature box when printing */
      #ack_sig_box { border: none !important; box-shadow: none !important; }
      /* Size and center the printable SRF to match hardcopy sample on Legal paper */
      /* Legal width (8.5in) minus left/right margins (12mm each ~= 0.472in) => ~7.56in printable width */
      #viewForm > div { width: 7.55in !important; max-width: none !important; margin: 0 auto !important; box-sizing: border-box !important; }
      /* Prevent unexpected scaling/rotation from browser print preview */
      html, body, #viewForm { height: auto !important; transform: none !important; -webkit-transform: none !important; }
      /* Ensure images and signatures render at natural size and don't rotate */
      img { max-width: 100% !important; height: auto !important; -webkit-transform: none !important; transform: none !important; }
      /* Ensure content doesn't get scaled by browser; prefer real 100% size */
      #viewForm, #viewForm * { -webkit-transform: none !important; transform: none !important; }
      /* Prevent scrollbars on printed page by forcing visible overflow and auto heights */
      html, body, .layout, .main, .main-content, .container-fluid, #viewForm, #viewForm > div { overflow: visible !important; height: auto !important; max-height: none !important; }
      /* Allow the form container and tables to break normally to avoid an extra blank page.
         Keep avoiding breaks inside individual table rows so rows are not split across pages. */
      #viewForm > div {
        page-break-inside: auto !important;
        break-inside: auto !important;
        margin-top: 0 !important;
        padding-top: 0 !important;
      }
      #viewForm table {
        page-break-inside: auto !important;
        break-inside: auto !important;
      }
      /* Prevent rows from being split across pages */
      tr {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
      }
      /* Tighter print layout: reduce font-size, paddings and slightly scale to fit one page */
      #viewForm, #viewForm * { font-size: 10px !important; }
      #viewForm table td, #viewForm table th { padding: 4px !important; }
      /* Apply a small uniform scale so the whole SRF fits a single printable page */
      #viewForm > div { transform-origin: top left; transform: scale(0.94); }
      /* Remove any top margin that could push content down */
      body, #viewForm { margin-top: 0 !important; padding-top: 0 !important; }
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
          <div class="topbar-title">Request Details</div>
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
          $ack_sig_url = !empty($request['ack_signature_path'])
            ? (defined('BASE_URL') ? BASE_URL . '/' . ltrim($request['ack_signature_path'], '/') : '/' . ltrim($request['ack_signature_path'], '/'))
            : '';

          // Load existing action rows for this request so they can be displayed in the form
          $request_actions = [];

          // Feedback and completion state (fall back to common fields)
          $feedback_rating = $request['feedback_rating'] ?? $request['feedback'] ?? '';
          $feedback_rating = is_string($feedback_rating) ? strtolower(trim($feedback_rating)) : '';
          $is_completed = false;
          if (!empty($request['status'])) {
            $status_val = strtolower(trim((string)$request['status']));
            $is_completed = in_array($status_val, ['completed', 'done', '1', 'true'], true);
          }
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
                  <a href="new_requests.php" class="btn btn-outline-secondary btn-sm no-print back-left" onclick="if (document.referrer) { history.back(); return false; }">
                    <i class="fa fa-arrow-left me-2"></i>Back
                  </a>
                </div>
                <div>
                  <?php if (!empty($is_completed) && $is_completed): ?>
                    <button type="button" id="printBtn" class="btn btn-primary btn-sm no-print" onclick="window.print();">
                      <i class="fa fa-print me-2"></i>Print
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Service Request (view-only) -->
          <div id="viewForm">
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
                  <td style="border-right: 1px solid black; padding: 2px; width: 35%; font-size:9px;">
                    <?php echo htmlspecialchars($auth1_name); ?>
                  </td>
                  <td style="border-right: 1px solid black; padding: 4px; font-weight: bold; width: 20%; font-size: 9px;">Title/Position:</td>
                  <td style="padding: 2px; width: 30%; font-size:9px;">
                    <?php echo htmlspecialchars($auth1_position); ?>
                  </td>
                </tr>
              </table>

              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="width: 50%; padding: 4px; border-right: 1px solid black;">
                    <div style="border: 1px solid black; text-align: center; height: 50px; display: flex; align-items: center; justify-content: center; padding: 6px;">
                      <?php if (!empty($auth1_sig_url)): ?>
                        <img src="<?php echo htmlspecialchars($auth1_sig_url); ?>" alt="Auth1 Signature" style="max-height:48px; max-width:100%; display:block;" />
                      <?php else: ?>
                        <div style="width:100%; height:100%;"></div>
                      <?php endif; ?>
                    </div>
                    <div style="text-align:center; font-size:8px; margin-top:6px;">
                      <div style="border-bottom:1px solid #000; width:140px; margin:0 auto 4px; height:0;"></div>
                      <div style="font-size:9px;">Signature (Manager/Supervisor)</div>
                    </div>
                  </td>
                  <td style="width: 50%; padding: 4px;">
                    <div style="border: 1px solid black; text-align: center; height: 50px; display: flex; align-items: center; justify-content: center; padding: 2px;">
                      <div style="border: none; font-size: 8px; text-align: center; width: 100%;">
                        <?php echo !empty($request['auth1_date']) ? htmlspecialchars($request['auth1_date']) : ''; ?>
                      </div>
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
                  <td style="border-right: 1px solid black; padding: 2px; width: 35%; font-size:9px;">
                    <?php echo htmlspecialchars($auth2_name); ?>
                  </td>
                  <td style="border-right: 1px solid black; padding: 4px; font-weight: bold; width: 20%; font-size: 9px;">Title/Position:</td>
                  <td style="padding: 2px; width: 30%; font-size:9px;">
                    <?php echo htmlspecialchars($auth2_position); ?>
                  </td>
                </tr>
              </table>

              <table style="width: 100%; border-collapse: collapse; border-bottom: 1px solid black;">
                <tr>
                  <td style="width: 50%; padding: 4px; border-right: 1px solid black;">
                    <div style="border: 1px solid black; text-align: center; height: 50px; display: flex; align-items: center; justify-content: center; padding: 6px;">
                      <?php if (!empty($auth2_sig_url)): ?>
                        <img src="<?php echo htmlspecialchars($auth2_sig_url); ?>" alt="Auth2 Signature" style="max-height:48px; max-width:100%; display:block;" />
                      <?php else: ?>
                        <div style="width:100%; height:100%;"></div>
                      <?php endif; ?>
                    </div>
                    <div style="text-align:center; font-size:8px; margin-top:6px;">
                      <div style="border-bottom:1px solid #000; width:140px; margin:0 auto 4px; height:0;"></div>
                      <div style="font-size:9px;">Signature (Manager/Supervisor)</div>
                    </div>
                  </td>
                  <td style="width: 50%; padding: 4px;">
                    <div style="border: 1px solid black; text-align: center; height: 50px; display: flex; align-items: center; justify-content: center; padding: 2px;">
                      <div style="border: none; font-size: 8px; text-align: center; width: 100%;">
                        <?php echo !empty($request['auth2_date']) ? htmlspecialchars($request['auth2_date']) : ''; ?>
                      </div>
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
            // Fetch users with role Admin or Property Custodian for staff dropdowns
            try {
              $stmt = $pdo->prepare("SELECT id, full_name, role FROM users WHERE status = 1 AND role IN ('Admin','Property Custodian') ORDER BY full_name ASC");
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
                <tbody>
                  <?php if (!empty($request_actions)): ?>
                    <?php foreach ($request_actions as $action): ?>
                      <tr>
                        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 6px; height: 25px; text-align:center; font-size:9px;">
                          <?php echo htmlspecialchars(!empty($action['action_date']) ? $action['action_date'] : ''); ?>
                        </td>
                        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 6px; text-align:center; font-size:9px;">
                          <?php
                            $at_raw = $action['action_time'] ?? '';
                            if ($at_raw !== '') {
                              $at_ts = strtotime($at_raw);
                              $at_display = $at_ts !== false ? date('h:i A', $at_ts) : $at_raw;
                              echo htmlspecialchars($at_display);
                            } else {
                              echo '';
                            }
                          ?>
                        </td>
                        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 6px; font-size:9px; vertical-align:top;">
                          <?php echo nl2br(htmlspecialchars($action['action_details'] ?? '')); ?>
                        </td>
                        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 6px; font-size:9px; text-align:center; vertical-align:middle;">
                          <?php
                            $staff_name = '';
                            if (!empty($action['action_staff'])) { $staff_name = $action['action_staff']; }
                            elseif (!empty($action['action_staff_id'])) {
                              foreach ($staff_users as $su) { if ($su['id'] == $action['action_staff_id']) { $staff_name = $su['full_name']; break; } }
                            }
                            echo htmlspecialchars($staff_name);
                          ?>
                        </td>
                        <td style="border-bottom: 1px solid black; padding: 6px; text-align:center; vertical-align:middle;">
                          <?php if (!empty($action['action_signature_path'])): ?>
                            <img src="<?php echo htmlspecialchars((defined('BASE_URL') ? BASE_URL . '/' : '/') . ltrim($action['action_signature_path'], '/')); ?>" alt="Action Signature" style="max-height:48px; max-width:100%;" />
                          <?php else: ?>
                            <span style="color:#666; font-size:9px;">&mdash;</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="5" style="padding:8px; text-align:center; color:#666; font-size:9px;">No actions recorded.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
                <!-- Feedback Section -->
              <div class="no-edit-below">
              <div style="border-left: 1px solid black; border-right: 1px solid black;">
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="padding: 8px;">
                    <div style="font-size: 9px; margin-bottom: 6px;">
                      <strong>Feedback Rating:</strong> 
                      <input type="checkbox" id="excellent" name="feedback_rating" value="excellent" disabled <?php echo ($feedback_rating === 'excellent') ? 'checked' : ''; ?> />
                      <label for="excellent"> Excellent</label>
                      <input type="checkbox" id="very_satisfactory_feed" name="feedback_rating" value="very_satisfactory" style="margin-left: 10px;" disabled <?php echo ($feedback_rating === 'very_satisfactory' || $feedback_rating === 'very satisfactory') ? 'checked' : ''; ?> />
                      <label for="very_satisfactory_feed"> Very Satisfactory</label>
                      <input type="checkbox" id="below_satisfactory" name="feedback_rating" value="below_satisfactory" style="margin-left: 10px;" disabled <?php echo ($feedback_rating === 'below_satisfactory' || $feedback_rating === 'below satisfactory') ? 'checked' : ''; ?> />
                      <label for="below_satisfactory"> Below Satisfactory</label>
                      <input type="checkbox" id="poor" name="feedback_rating" value="poor" style="margin-left: 10px;" disabled <?php echo ($feedback_rating === 'poor') ? 'checked' : ''; ?> />
                      <label for="poor"> Poor</label>
                    </div>
                    <div style="margin-bottom: 6px; font-size: 9px;">
                      <input type="checkbox" id="completed" name="status" value="completed" disabled <?php echo $is_completed ? 'checked' : ''; ?> />
                      <label for="completed"> Completed</label>
                    </div>
                    <div style="font-weight: bold; font-size: 9px; margin-bottom: 6px;">Acknowledged by:</div>
                  </td>
                </tr>
              </table>
            </div>

            <!-- Small Acknowledgement Signature Pad (click box to draw) -->
                <div style="border-left: 1px solid black; border-right: 1px solid black; padding: 8px;">
                  <div style="font-size: 9px; margin-bottom: 5px;">
                  </div>
                  <div style="display: flex; align-items: center; gap: 12px;">
                    <div id="ack_sig_box" class="signature-box" style="border:1px solid #000; width:180px; height:60px; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                      <img id="ack_sig_preview" src="<?php echo htmlspecialchars($ack_sig_url); ?>" alt="Signature preview" style="max-width:100%; max-height:100%; <?php echo !empty($ack_sig_url) ? '' : 'display:none;'; ?>" />
                      <span id="ack_sig_placeholder" style="font-size:8px; color:#666; <?php echo !empty($ack_sig_url) ? 'display:none;' : ''; ?>">Click to sign</span>
                    </div>
                    <input type="hidden" id="ack_signature_data" name="ack_signature_data" value="">
                  </div>
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
                      <div style="position: absolute; bottom: 2px; left: 0; width: 100%; font-size: 8px;">
                          <span id="ack_datetime_display"><?php
                            $dt_val = !empty($request['updated_at']) ? $request['updated_at'] : ($request['created_at'] ?? '');
                            if (!empty($dt_val)) {
                              $ts = strtotime($dt_val);
                              if ($ts !== false) {
                                echo htmlspecialchars(date('m/d/Y h:i:s A', $ts));
                              } else {
                                echo htmlspecialchars($dt_val);
                              }
                            } else { echo '';} ?></span>
                        </div>
                    </div>
                    <div style="font-size: 8px;">Date/Time</div>
                  </td>
                </tr>
              </table>

              <div style="text-align: right;">
                <div style="font-size: 8px; font-weight: bold;">Ref: NIMD Service Request Form 22 March 2021</div>
              </div>
            </div>
            </div>

          </div>


            </div>
          </div>

          <!-- Global Save button removed -->

        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Signature Pad library -->
  <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
  <!-- Admin Dashboard JavaScript -->
  <script src="../../../../public/assets/js/admin/dashboard.js"></script>
  <!-- Admin Navigation JavaScript -->
  <script src="../../../../public/assets/js/admin/navigation.js"></script>
  
  <!-- Acknowledgement Signature Modal -->
  <div class="modal fade" id="ackSignatureModal" tabindex="-1" aria-labelledby="ackSignatureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ackSignatureModalLabel">Draw Signature</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <canvas id="ackSigCanvas" style="border:1px solid #ccc; width:100%; height:200px;"></canvas>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" id="ackSigClear">Clear</button>
          <button type="button" class="btn btn-primary btn-sm" id="ackSigSave">Save</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    (function(){
      const ackBox = document.getElementById('ack_sig_box');
      const ackModalEl = document.getElementById('ackSignatureModal');
      const ackSigCanvas = document.getElementById('ackSigCanvas');
      const ackSigPreview = document.getElementById('ack_sig_preview');
      const ackPlaceholder = document.getElementById('ack_sig_placeholder');
      const ackHidden = document.getElementById('ack_signature_data');
      const ackSaveBtn = document.getElementById('ack_save_btn_global');
      const ackSavedLabel = document.getElementById('ack_saved_label_global');
      let signaturePad = null;
      const ackModal = ackModalEl ? new bootstrap.Modal(ackModalEl) : null;

      function resizeCanvas() {
        if (!ackSigCanvas) return;
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = ackSigCanvas.getBoundingClientRect();
        const w = rect.width || 400;
        const h = rect.height || 200;
        ackSigCanvas.width = w * ratio;
        ackSigCanvas.height = h * ratio;
        const ctx = ackSigCanvas.getContext('2d');
        ctx.setTransform(1,0,0,1,0,0);
        ctx.scale(ratio, ratio);
      }

      function createPad() {
        if (!ackSigCanvas) return;
        if (signaturePad) try { signaturePad.off && signaturePad.off(); } catch(e){}
        signaturePad = new SignaturePad(ackSigCanvas, { backgroundColor: 'rgba(255,255,255,0)' });
      }

      function formatDate12(d) {
        if (!d || !(d instanceof Date)) return '';
        const pad = (n) => String(n).padStart(2, '0');
        const month = pad(d.getMonth() + 1);
        const day = pad(d.getDate());
        const year = d.getFullYear();
        let hour = d.getHours();
        const minute = pad(d.getMinutes());
        const second = pad(d.getSeconds());
        const ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12; hour = hour ? hour : 12; // convert 0 -> 12
        const hourPad = String(hour).padStart(2, '0');
        // Format: MM/DD/YYYY hh:mm:ss AM/PM
        return `${month}/${day}/${year} ${hourPad}:${minute}:${second} ${ampm}`;
      }

      if (ackBox) {
        ackBox.addEventListener('click', function(){
          // Prevent editing if this signature box is inside a read-only area
          if (ackBox.closest && ackBox.closest('.no-edit-below')) return;
          // if already finalized, don't allow editing
          if (ackBox.dataset.saved === '1') return;
          if (!ackModal) return;
          // prepare canvas
          try { resizeCanvas(); createPad(); } catch (e) { console.warn(e); }
          // if existing data, restore
          if (ackHidden && ackHidden.value) {
            try { signaturePad.fromDataURL(ackHidden.value); } catch (e) { /* ignore */ }
          } else if (signaturePad) {
            signaturePad.clear();
          }
          ackModal.show();
        });
      }

      if (ackModalEl) {
        ackModalEl.addEventListener('shown.bs.modal', function(){
          try { resizeCanvas(); createPad(); } catch (e) { console.warn(e); }
          if (ackHidden && ackHidden.value && signaturePad) {
            try { signaturePad.fromDataURL(ackHidden.value); } catch (e) {}
          }
        });
      }

      const clearBtn = document.getElementById('ackSigClear');
      const saveBtn = document.getElementById('ackSigSave');
      if (clearBtn) clearBtn.addEventListener('click', function(){ if (signaturePad) signaturePad.clear(); });
        if (saveBtn) saveBtn.addEventListener('click', function(){
        if (!signaturePad) return;
        if (signaturePad.isEmpty()) {
          // clear preview and hidden
          if (ackHidden) ackHidden.value = '';
          if (ackSigPreview) { ackSigPreview.style.display = 'none'; ackSigPreview.src = ''; }
          if (ackPlaceholder) ackPlaceholder.style.display = 'inline';
          ackModal.hide();
          return;
        }
        let dataURL = null;
        try { dataURL = signaturePad.toDataURL('image/png'); } catch (e) { console.error(e); }
        if (dataURL) {
          if (ackHidden) ackHidden.value = dataURL;
          if (ackSigPreview) { ackSigPreview.src = dataURL; ackSigPreview.style.display = 'block'; }
          if (ackPlaceholder) ackPlaceholder.style.display = 'none';
          // enable the global Save button so user can finalize
          if (ackSaveBtn) { ackSaveBtn.disabled = false; }
        }
        ackModal.hide();
      });

      // Save/finalize button handler - mark signature as saved and prevent edits
      if (ackSaveBtn) {
        ackSaveBtn.addEventListener('click', async function(){
          if (!ackHidden || !ackHidden.value) { alert('Please draw a signature first.'); return; }

          const requestId = "<?php echo htmlspecialchars($service_request_id_for_actions ?? $request['id'] ?? $request_id ?? ''); ?>";

          try {
            const res = await fetch('save_ack_signature.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ request_id: requestId, signature: ackHidden.value })
            });

            const data = await res.json();
            if (!data.ok) throw new Error(data.msg || 'Save failed');

            // success UI
            ackBox.dataset.saved = '1';
            ackBox.style.cursor = 'default';
            ackBox.style.pointerEvents = 'none';
            ackSaveBtn.textContent = 'Saved';
            ackSaveBtn.disabled = true;
            ackSaveBtn.classList.remove('btn-primary');
            ackSaveBtn.classList.add('btn-success');
            if (ackSavedLabel) ackSavedLabel.style.display = 'inline-block';

            // set datetime display
            const dtEl = document.getElementById('ack_datetime_display');
            if (dtEl) dtEl.textContent = formatDate12(new Date());

            // if server returned a path, set ackSigPreview src to it
            if (data.path && ackSigPreview) {
              ackSigPreview.src = data.path;
              ackSigPreview.style.display = 'block';
              if (ackPlaceholder) ackPlaceholder.style.display = 'none';
            }

          } catch (e) {
            console.error(e);
            alert('Hindi na-save: ' + (e.message || e));
          }
        });
      }

      // If page already has a signature value (loaded from DB), show preview and Save button state
      try {
        if (ackHidden && ackHidden.value) {
          if (ackSigPreview) { ackSigPreview.src = ackHidden.value; ackSigPreview.style.display = 'block'; }
          if (ackPlaceholder) ackPlaceholder.style.display = 'none';
          if (ackSaveBtn) { ackSaveBtn.disabled = false; }
          // if server-side data indicates finalized, mark saved (optional)
          if (ackBox.dataset.saved === '1') {
            if (ackSaveBtn) { ackSaveBtn.textContent = 'Saved'; ackSaveBtn.disabled = true; ackSaveBtn.classList.remove('btn-primary'); ackSaveBtn.classList.add('btn-success'); }
            if (ackSavedLabel) ackSavedLabel.style.display = 'inline-block';
            ackBox.style.cursor = 'default';
            ackBox.style.pointerEvents = 'none';
          }
        }
      } catch (e) { /* ignore */ }

      window.addEventListener('resize', function(){ try { resizeCanvas(); createPad(); } catch(e){} });
    })();
  </script>
  
</body>
</html>