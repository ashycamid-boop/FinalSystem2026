<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Case Details - CENRO NASIPIT</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Admin common styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <!-- Case Details specific styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/case-details.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Fredoka+One:400&display=swap" rel="stylesheet">
  
  <style>
    .badge.bg-primary {
      background-color: #0d6efd !important;
      color: white !important;
    }

    /* Print / A4 formatting */
    @page {
      size: A4;
      margin: 10mm;
    }

    @media print {
      html, body {
        width: 210mm;
        height: 297mm;
        margin: 0;
        padding: 6mm 8mm 6mm 8mm;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
        background: #ffffff !important;
        -webkit-print-color-adjust: exact;
      }

      /* Hide navigation and controls for print */
      .sidebar, .topbar, .action-buttons, .sidebar-nav, .sidebar-logo, .topbar-card {
        display: none !important;
      }

      /* Make main content take full print width */
      .layout, .main, .main-content, .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        background: #ffffff !important;
        box-shadow: none !important;
      }

      /* Prevent tables from breaking awkwardly */
      .report-header, .report-section, table, tbody, thead, tr, .table {
        page-break-inside: avoid !important;
      }

      table {
        width: 100% !important;
        border-collapse: collapse;
        font-size: 12px;
      }

      .table-bordered td, .table-bordered th {
        border: 1px solid #000 !important;
      }


      /* Logos scaling */
      .logo-left, .logo-right {
        max-width: 140px !important;
        height: auto !important;
      }

      /* Header sizing for print */
      .report-header {
        padding-top: 6mm !important;
        padding-bottom: 6mm !important;
      }

      .report-header .header-content h6 {
        margin: 0;
        font-size: 12px !important;
        line-height: 1.1;
        font-weight: 600;
      }

      .report-header .header-content h4 {
        margin-top: 8px;
        margin-bottom: 0;
        font-size: 20px !important;
        font-weight: 700;
      }

      .report-header .header-content {
        text-align: center !important;
      }

      /* Ensure badges print with color */
      .badge {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
      }

      /* Utility: hide anything explicitly marked no-print */
      .no-print { display: none !important; }
      /* Remove visual scrollbars (hide in webkit browsers and others) */
      *::-webkit-scrollbar { width: 0 !important; height: 0 !important; display: none !important; background: transparent !important; }
      html { -ms-overflow-style: none; scrollbar-width: none; }
      /* Hide evidence row, preview boxes, PDF file tiles and PDF label when printing */
      .evidence-row, .evidence-files, .pdf-files, .file-item, .pdf-label { display: none !important; }
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
      <div class="sidebar-role">Enforcement Officer</div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li><a href="spot_reports.php"><i class="fa fa-file-text"></i> Spot Reports</a></li>
          <li class="active"><a href="case_management.php"><i class="fa fa-briefcase"></i> Case Management</a></li>
          <li><a href="apprehended_items.php"><i class="fa fa-archive"></i> Apprehended Items</a></li>
          <li><a href="service_requests.php"><i class="fa fa-cog"></i> Service Requests</a></li>
          <li><a href="statistical_report.php"><i class="fa fa-chart-bar"></i> Statistical Report</a></li>
        </ul>
      </nav>
    </nav>
    <!-- Main -->
    <div class="main">
      <div class="topbar">
        <div class="topbar-card">
          <div class="topbar-title">Case Details</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
          <?php
          // Load report data based on ?ref= or ?id=
          $refParam = $_GET['ref'] ?? $_GET['id'] ?? null;
          $report = null;
          $apprehended_persons = [];
          $vehicles = [];
          $items = [];
          $evidences = [];
          if ($refParam) {
            try {
              require_once dirname(dirname(dirname(__DIR__))) . '/config/db.php'; // loads $pdo

              $stmt = $pdo->prepare("SELECT * FROM spot_reports WHERE reference_no = ? OR id = ? LIMIT 1");
              $stmt->execute([$refParam, $refParam]);
              $report = $stmt->fetch(PDO::FETCH_ASSOC);

              if ($report) {
                $rid = $report['id'];
                // Apprehended persons (if table exists)
                try {
                  $sp = $pdo->prepare("SELECT * FROM spot_report_persons WHERE report_id = ? ORDER BY id ASC");
                  $sp->execute([$rid]);
                  $apprehended_persons = $sp->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) { $apprehended_persons = []; }

                // Vehicles
                try {
                  $sv = $pdo->prepare("SELECT * FROM spot_report_vehicles WHERE report_id = ? ORDER BY id ASC");
                  $sv->execute([$rid]);
                  $vehicles = $sv->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) { $vehicles = []; }

                // Items
                try {
                  $si = $pdo->prepare("SELECT * FROM spot_report_items WHERE report_id = ? ORDER BY id ASC");
                  $si->execute([$rid]);
                  $items = $si->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) { $items = []; }

                // Files / evidences
                try {
                  $sf = $pdo->prepare("SELECT * FROM spot_report_files WHERE report_id = ? ORDER BY id ASC");
                  $sf->execute([$rid]);
                  $evidences = $sf->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) { $evidences = []; }

                // Group evidences by row index when orig_name encodes owner (person#N:, vehicle#N:, item#N:)
                $person_files = [];
                $vehicle_files = [];
                $item_files = [];
                $other_evidences = [];
                foreach ($evidences as $f) {
                  $orig = $f['orig_name'] ?? $f['file_name'] ?? basename($f['file_path'] ?? $f['path'] ?? '');
                  $path = $f['file_path'] ?? $f['path'] ?? '';
                  if (preg_match('/^(person|vehicle|item)#(\d+):(.+)$/', $orig, $m)) {
                    $type = $m[1]; $idx = (int)$m[2]; $name = $m[3];
                    $entry = ['path' => $path, 'orig' => $name];
                    if ($type === 'person') {
                      if (!isset($person_files[$idx])) $person_files[$idx] = [];
                      $person_files[$idx][] = $entry;
                    } elseif ($type === 'vehicle') {
                      if (!isset($vehicle_files[$idx])) $vehicle_files[$idx] = [];
                      $vehicle_files[$idx][] = $entry;
                    } elseif ($type === 'item') {
                      if (!isset($item_files[$idx])) $item_files[$idx] = [];
                      $item_files[$idx][] = $entry;
                    }
                  } else {
                    $other_evidences[] = ['path' => $path, 'orig' => $orig];
                  }
                }
              }
            } catch (Exception $e) {
              $report = null;
            }
          }
          ?>
           <?php
           // Helper to build file URLs that work whether BASE_URL is defined
           if (!function_exists('build_file_url')) {
             function build_file_url($href) {
               if (empty($href)) return '';
               // If already absolute URL, return as-is
               if (preg_match('#^(https?:)?//#i', $href)) return $href;
               $href = '/' . ltrim($href, '/');

               // Determine project root (4 levels up from this views folder)
               $projectRoot = dirname(__DIR__, 4);

               // If file is stored under public dir, ensure URL contains /public
               if (file_exists($projectRoot . $href)) {
                 // file exists at projectRoot + href (rare)
               } elseif (file_exists($projectRoot . '/public' . $href)) {
                 $href = '/public' . $href;
               }

               if (defined('BASE_URL') && BASE_URL) {
                 return rtrim(BASE_URL, '/') . $href;
               }

               // Fallback: build host-based absolute URL
               $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
               $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
               return $scheme . '://' . $host . $href;
             }
           }
          ?>
          <?php
          // Map status text to badge CSS class so server-rendered badges match client updates
          if (!function_exists('map_status_to_class')) {
            function map_status_to_class($s) {
              $sRaw = strtolower(trim((string)$s));
              if ($sRaw === '') return 'bg-secondary';

              // Person statuses (match apprehended_items mapping)
              if (strpos($sRaw, 'for custody') !== false || strpos($sRaw, 'for-custody') !== false || $sRaw === 'for custody' || $sRaw === 'for-custody') return 'bg-warning';
              if ($sRaw === 'in custody' || $sRaw === 'in-custody') return 'bg-info';
              if ($sRaw === 'detained') return 'bg-danger';
              if ($sRaw === 'bailed') return 'bg-cyan';
              if ($sRaw === 'released') return 'bg-success';
              if (strpos($sRaw, 'transferred') !== false) return 'bg-purple';
              if ($sRaw === 'convicted') return 'bg-dark';
              if ($sRaw === 'acquitted') return 'bg-teal';

              // Item/vehicle statuses
              if (strpos($sRaw, 'confiscat') !== false) return 'bg-warning';
              if (strpos($sRaw, 'seized') !== false) return 'bg-info';
              if (strpos($sRaw, 'under-custody') !== false || strpos($sRaw, 'under custody') !== false) return 'bg-primary';
              if (strpos($sRaw, 'for disposal') !== false || strpos($sRaw, 'for-disposal') !== false) return 'bg-orange';
              if (strpos($sRaw, 'disposed') !== false) return 'bg-success';
              if (strpos($sRaw, 'burn') !== false || strpos($sRaw, 'destroy') !== false) return 'bg-danger';
              if (strpos($sRaw, 'forfeited') !== false) return 'bg-purple';
              if (strpos($sRaw, 'donat') !== false || strpos($sRaw, 'donated') !== false) return 'bg-teal';
              if (strpos($sRaw, 'returned') !== false) return 'bg-cyan';
              if (strpos($sRaw, 'auction') !== false) return 'bg-indigo';

              // Fallbacks
              if (strpos($sRaw, 'custody') !== false) return 'bg-warning';
              if (strpos($sRaw, 'impound') !== false || strpos($sRaw, 'impounded') !== false) return 'bg-info';
              return 'bg-secondary';
            }
          }
          ?>
          <?php if (!empty($report)): ?>
            <script>
              window.reportId = <?php echo (int)($report['id'] ?? 0); ?>;
              window.updateStatusUrl = <?php echo json_encode(build_file_url('app/modules/enforcement_officer/actions/update_status.php')); ?>;
            </script>
          <?php endif; ?>
        </div>
      </div>
      <div class="main-content">
        <!-- Action Buttons -->
        <div class="action-buttons mb-3 px-4">
          <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">Back</button>
        </div>
        <!-- Case Details Content -->
        <div class="container-fluid p-4">
          <!-- Header Section -->
          <div class="report-header text-center mb-4">
            <div class="d-flex justify-content-between align-items-start">
              <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo" class="logo-left">
              <div class="header-content">
                <h6>Department of Environment and Natural Resources</h6>
                <h6>Kagawaran ng Kapaligiran at Likas Yaman</h6>
                <h6>Caraga Region</h6>
                <h6>CENRO Nasipit, Agusan del Norte</h6>
                <hr style="border-top: 4px solid #ff0000; margin: 5px 0 10px 0;">
                <h4 class="mt-3">Spot Report</h4>
                <?php if ($report): ?>
                  <div class="small text-muted mt-1">Reference: <?php echo htmlspecialchars($report['reference_no'] ?? '-'); ?></div>
                <?php endif; ?>
              </div>
              <img src="../../../../public/assets/images/bagong-pilipinas-logo.png" alt="Bagong Pilipinas Logo" class="logo-right">
            </div>
          </div>

          <!-- Main Details Table -->
          <div class="report-section mb-4">
            <table class="table table-bordered">
              <tr>
                <td class="field-label">Incident Date & Time:</td>
                <td><?php echo $report && !empty($report['incident_datetime']) ? htmlspecialchars(date('M d, Y g:i a', strtotime($report['incident_datetime']))) : '-'; ?></td>
                <td class="field-label">Memo Date:</td>
                <td><?php echo $report && !empty($report['memo_date']) ? htmlspecialchars(date('M d, Y g:i a', strtotime($report['memo_date']))) : '-'; ?></td>
                <td class="field-label">Reference No.:</td>
                <td><?php echo $report ? htmlspecialchars($report['reference_no']) : '-'; ?></td>
              </tr>
              <tr>
                <td class="field-label">Location:</td>
                <td colspan="5"><?php echo $report ? htmlspecialchars($report['location'] ?? '-') : '-'; ?></td>
              </tr>
              <tr>
                <td class="field-label">Summary:</td>
                <td colspan="5"><?php echo $report ? nl2br(htmlspecialchars($report['summary'] ?? '-')) : '-'; ?></td>
              </tr>
              <tr>
                <td class="field-label">Team Leader:</td>
                <td colspan="2"><?php echo $report ? htmlspecialchars($report['team_leader'] ?? '-') : '-'; ?></td>
                <td class="field-label">Custodian:</td>
                <td colspan="2"><?php echo $report ? htmlspecialchars($report['custodian'] ?? '-') : '-'; ?></td>
              </tr>
            </table>
          </div>

          <!-- Apprehended Persons Section -->
          <div class="report-section mb-4">
            <h6>Apprehended Person(s)</h6>
            <table class="table table-bordered">
              <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Address</th>
                    <th>Contact No.</th>
                    <th>Role/Remarks</th>
                    <th>Evidence</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($apprehended_persons)): ?>
                  <?php for ($pi = 0; $pi < count($apprehended_persons); $pi++): $p = $apprehended_persons[$pi]; ?>
                    <?php
                      $person_name = $p['full_name'] ?? $p['name'] ?? '-';
                      $person_age = $p['age'] ?? '-';
                      $person_gender = $p['gender'] ?? '-';
                      $person_address = $p['address'] ?? '-';
                      $person_contact = $p['contact_no'] ?? $p['contact'] ?? '-';
                      $person_role = $p['role'] ?? '-';
                      $person_status = $p['status'] ?? 'For Custody';
                      $person_id = (int)($p['id'] ?? 0);
                      $pfiles = $person_files[$pi] ?? array();
                    ?>
                    <tr>
                      <td><?php echo htmlspecialchars($person_name); ?></td>
                      <td><?php echo htmlspecialchars($person_age); ?></td>
                      <td><?php echo htmlspecialchars($person_gender); ?></td>
                      <td><?php echo htmlspecialchars($person_address); ?></td>
                      <td><?php echo htmlspecialchars($person_contact); ?></td>
                      <td><?php echo htmlspecialchars($person_role); ?></td>
                      <td>
                        <?php if (!empty($pfiles)): ?>
                          <?php foreach ($pfiles as $fentry): $href = $fentry['path'] ?? ''; $label = $fentry['orig'] ?? basename($href); $ext = strtolower(pathinfo($label, PATHINFO_EXTENSION)); $iconClass = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? 'fa-image text-primary' : ($ext === 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file text-secondary'); ?>
                            <div style="display:inline-block; margin-right:8px;">
                              <a href="<?php echo htmlspecialchars(build_file_url($href)); ?>" target="_blank" title="<?php echo htmlspecialchars($label); ?>" style="color:inherit; text-decoration:none;">
                                <i class="fa <?php echo $iconClass; ?>" style="font-size:18px;"></i>
                              </a>
                            </div>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endfor; ?>
                  <?php else: ?>
                  <tr><td colspan="7" class="text-center">No apprehended persons recorded.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Vehicles Section -->
          <div class="report-section mb-4">
            <h6>Vehicle(s)</h6>
            <table class="table table-bordered">
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
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($vehicles)): ?>
                  <?php for ($vi = 0; $vi < count($vehicles); $vi++): $v = $vehicles[$vi]; ?>
                    <?php
                      $vehicle_plate = $v['plate_no'] ?? $v['plate'] ?? '-';
                      $vehicle_make = $v['make_model'] ?? $v['make'] ?? '-';
                      $vehicle_color = $v['color'] ?? '-';
                      $vehicle_owner = $v['registered_owner'] ?? $v['owner'] ?? '-';
                      $vehicle_contact = $v['contact_no'] ?? $v['contact'] ?? '-';
                      $vehicle_engine = $v['engine_chassis_no'] ?? $v['engine'] ?? '-';
                      $vehicle_status = $v['status'] ?? 'For Custody';
                      $vehicle_id = (int)($v['id'] ?? 0);
                      $vfiles = $vehicle_files[$vi] ?? array();
                    ?>
                    <tr>
                      <td><?php echo htmlspecialchars($vehicle_plate); ?></td>
                      <td><?php echo htmlspecialchars($vehicle_make); ?></td>
                      <td><?php echo htmlspecialchars($vehicle_color); ?></td>
                      <td><?php echo htmlspecialchars($vehicle_owner); ?></td>
                      <td><?php echo htmlspecialchars($vehicle_contact); ?></td>
                      <td><?php echo htmlspecialchars($vehicle_engine); ?></td>
                      <td><?php echo htmlspecialchars($v['remarks'] ?? ''); ?></td>
                      <?php $vehicleBadge = map_status_to_class($vehicle_status); ?>
                      <td><span class="badge <?php echo $vehicleBadge; ?>" id="vehicle-status-<?php echo $vehicle_id; ?>"><?php echo htmlspecialchars($vehicle_status); ?></span></td>
                      <td>
                        <?php if (!empty($vfiles)): ?>
                          <?php foreach ($vfiles as $fentry): $href = $fentry['path'] ?? ''; $label = $fentry['orig'] ?? basename($href); $ext = strtolower(pathinfo($label, PATHINFO_EXTENSION)); $iconClass = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? 'fa-image text-primary' : ($ext === 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file text-secondary'); ?>
                            <div style="display:inline-block; margin-right:8px;">
                              <a href="<?php echo htmlspecialchars(build_file_url($href)); ?>" target="_blank" title="<?php echo htmlspecialchars($label); ?>" style="color:inherit; text-decoration:none;">
                                <i class="fa <?php echo $iconClass; ?>" style="font-size:18px;"></i>
                              </a>
                            </div>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="editVehicleStatus(<?php echo $vehicle_id; ?>)">
                          <i class="fa fa-edit"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endfor; ?>
                <?php else: ?>
                  <tr><td colspan="10" class="text-center">No vehicles recorded.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Seizure Items Section -->
          <div class="report-section mb-4">
            <h6>Seizure Items</h6>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Item No.</th>
                  <th>Item Type</th>
                  <th>Description</th>
                  <th>Quantity</th>
                  <th>Volume (Bd.ft./cu.m.)</th>
                  <th>Estimated Value (₱)</th>
                  <th>Remarks No.</th>
                  <th>Status</th>
                  <th>Evidence</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($items)): ?>
                  <?php for ($ii = 0; $ii < count($items); $ii++): $it = $items[$ii]; ?>
                    <?php
                      $item_no = $it['item_no'] ?? $it['id'] ?? '-';
                      $item_type = $it['item_type'] ?? $it['type'] ?? '-';
                      $item_desc = $it['description'] ?? '-';
                      $item_qty = $it['quantity'] ?? '-';
                      $item_vol = $it['volume'] ?? '-';
                      $item_val = isset($it['value']) ? number_format((float)$it['value'], 2) : '-';
                      $item_remarks = $it['remarks'] ?? '-';
                      $item_status = $it['status'] ?? 'For Custody';
                      $item_id = (int)($it['id'] ?? 0);
                      $ifiles = $item_files[$ii] ?? array();
                    ?>
                    <tr>
                      <td><?php echo htmlspecialchars($item_no); ?></td>
                      <td><?php echo htmlspecialchars($item_type); ?></td>
                      <td><?php echo htmlspecialchars($item_desc); ?></td>
                      <td><?php echo htmlspecialchars($item_qty); ?></td>
                      <td><?php echo htmlspecialchars($item_vol); ?></td>
                      <td><?php echo is_string($item_val) ? htmlspecialchars($item_val) : htmlspecialchars(number_format((float)$item_val,2)); ?></td>
                      <td><?php echo htmlspecialchars($item_remarks); ?></td>
                      <?php $itemBadge = map_status_to_class($item_status); ?>
                      <td><span class="badge <?php echo $itemBadge; ?>" id="item-status-<?php echo $item_id; ?>"><?php echo htmlspecialchars($item_status); ?></span></td>
                      <td>
                        <?php if (!empty($ifiles)): ?>
                          <?php foreach ($ifiles as $fentry): $href = $fentry['path'] ?? ''; $label = $fentry['orig'] ?? basename($href); $ext = strtolower(pathinfo($label, PATHINFO_EXTENSION)); $iconClass = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? 'fa-image text-primary' : ($ext === 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file text-secondary'); ?>
                            <div style="display:inline-block; margin-right:8px;">
                              <a href="<?php echo htmlspecialchars(build_file_url($href)); ?>" target="_blank" title="<?php echo htmlspecialchars($label); ?>" style="color:inherit; text-decoration:none;">
                                <i class="fa <?php echo $iconClass; ?>" style="font-size:18px;"></i>
                              </a>
                            </div>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="editItemStatus(<?php echo $item_id; ?>)">
                          <i class="fa fa-edit"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endfor; ?>
                <?php else: ?>
                  <tr><td colspan="10" class="text-center">No seizure items recorded.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Evidence and Status Section -->
          <div class="report-section mb-4">
            <table class="table table-bordered">
              <tr>
                <td class="field-label" style="width: 150px;">Evidence(s)</td>
                <td style="width: 250px;">
                  <div class="evidence-files">
                    <?php if (!empty($other_evidences)): ?>
                      <div class="d-flex flex-wrap gap-2">
                      <?php foreach ($other_evidences as $f): ?>
                        <?php
                          $orig = $f['orig_name'] ?? $f['orig'] ?? $f['file_name'] ?? basename($f['file_path'] ?? $f['path'] ?? 'file');
                          $webpath = $f['file_path'] ?? $f['path'] ?? '';
                          $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                          // Skip PDF here so it only shows in the "Spot Report Memorandum (PDF)" column
                          if ($ext === 'pdf') continue;
                        ?>
                        <div class="file-item mb-2 text-center" style="width:36px;">
                          <?php if ($webpath && in_array($ext, ['jpg','jpeg','png','gif','webp'])): ?>
                            <a href="<?php echo htmlspecialchars(build_file_url($webpath)); ?>" target="_blank" title="<?php echo htmlspecialchars($orig); ?>" class="d-block text-center" style="text-decoration:none;">
                              <i class="fa fa-image text-primary" style="font-size:20px; display:block; margin:6px auto 2px;"></i>
                            </a>
                          <?php else: ?>
                            <?php
                              $iconClass = 'fa-file text-secondary';
                              if (in_array($ext, ['pdf'])) $iconClass = 'fa-file-pdf text-danger';
                              if (in_array($ext, ['doc','docx'])) $iconClass = 'fa-file-word text-primary';
                              if (in_array($ext, ['xls','xlsx','csv'])) $iconClass = 'fa-file-excel text-success';
                            ?>
                            <div style="display:flex; align-items:center; gap:8px; justify-content:center;">
                              <?php if ($webpath): ?>
                                <a href="<?php echo htmlspecialchars(build_file_url($webpath)); ?>" target="_blank" title="<?php echo htmlspecialchars($orig); ?>" style="text-decoration:none; color:inherit;">
                                  <i class="fa <?php echo $iconClass; ?>" style="font-size:18px;"></i>
                                </a>
                              <?php else: ?>
                                <i class="fa <?php echo $iconClass; ?> text-muted" style="font-size:18px;"></i>
                              <?php endif; ?>
                            </div>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <div class="text-muted">No evidence files uploaded.</div>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="field-label" style="width: 200px;">Spot Report Memorandum (PDF)</td>
                <td>
                  <div class="pdf-files">
                    <?php
                    $pdfShown = false;
                    foreach ($other_evidences as $f) {
                      $orig = $f['orig'] ?? $f['file_name'] ?? basename($f['path'] ?? $f['file_path'] ?? 'file');
                      $webpath = $f['path'] ?? $f['file_path'] ?? '';
                      $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                        if ($ext === 'pdf' || stripos($webpath, '.pdf') !== false) {
                        if ($webpath) {
                          echo '<div class="file-item mb-2"><a href="' . htmlspecialchars(build_file_url($webpath)) . '" target="_blank" title="' . htmlspecialchars($orig) . '"><i class="fa fa-file-pdf text-danger" style="font-size:18px;"></i></a></div>';
                        } else {
                          echo '<div class="file-item mb-2"><i class="fa fa-file-pdf text-danger" style="font-size:18px;"></i></div>';
                        }
                        $pdfShown = true;
                      }
                    }
                    if (!$pdfShown) echo '<div class="text-muted">No memorandum PDF attached.</div>';
                    ?>
                  </div>
                </td>
              </tr>
            </table>
          </div>

          <!-- Status Section -->
          <div class="report-section mb-4">
            <table class="table table-bordered" style="width: 280px;">
              <tr>
                <td class="text-center">
                  <div><strong>Case Status</strong></div>
                  <div class="mt-2 d-flex align-items-center justify-content-center gap-2">
                      <?php
                        $caseStatusDisplay = $report ? ($report['case_status'] ?? $report['status'] ?? 'Under Investigation') : 'Under Investigation';
                        $caseStatusKey = strtolower(trim($caseStatusDisplay));
                        // Normalize: remove extra whitespace and unify separators
                        $norm = preg_replace('/[^a-z0-9]+/', ' ', $caseStatusKey);
                        $caseBadgeClass = 'bg-secondary';

                        if (strpos($norm, 'under') !== false && strpos($norm, 'investig') !== false) {
                          $caseBadgeClass = 'bg-primary';
                        } elseif (strpos($norm, 'for filing') !== false || strpos($norm, 'for filing') !== false || strpos($norm, 'for filing') !== false) {
                          $caseBadgeClass = 'bg-warning';
                        } elseif (strpos($norm, 'ongoing') !== false || strpos($norm, 'trial') !== false) {
                          $caseBadgeClass = 'bg-info';
                        } elseif (strpos($norm, 'filed') !== false && strpos($norm, 'court') !== false) {
                          $caseBadgeClass = 'bg-secondary';
                        } elseif (strpos($norm, 'dismiss') !== false) {
                          $caseBadgeClass = 'bg-danger';
                        } elseif (strpos($norm, 'resolv') !== false || strpos($norm, 'resolved') !== false) {
                          $caseBadgeClass = 'bg-success';
                        } elseif (strpos($norm, 'archiv') !== false) {
                          $caseBadgeClass = 'bg-dark';
                        }
                      ?>
                      <span class="badge <?php echo $caseBadgeClass; ?>" id="case-status"><?php echo htmlspecialchars($caseStatusDisplay); ?></span>
                      <button class="btn btn-sm btn-outline-primary" onclick="editCaseStatus()" title="Edit Case Status">
                        <i class="fa fa-edit"></i>
                      </button>
                    </div>
                </td>
              </tr>
            </table>
          </div>

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
  <!-- Case Details JavaScript -->
  <script src="../../../../public/assets/js/admin/case-details.js"></script>

  <!-- Case Details Status Management -->
  <script>
    // Edit Person Status
    function editPersonStatus(personId) {
      const statusOptions = [
        { value: 'for-custody', text: 'For Custody', class: 'bg-warning' },
        { value: 'in-custody', text: 'In Custody', class: 'bg-info' },
        { value: 'detained', text: 'Detained', class: 'bg-danger' },
        { value: 'bailed', text: 'Bailed', class: 'bg-cyan' },
        { value: 'released', text: 'Released', class: 'bg-success' },
        { value: 'transferred', text: 'Transferred to Court', class: 'bg-purple' },
        { value: 'convicted', text: 'Convicted', class: 'bg-dark' },
        { value: 'acquitted', text: 'Acquitted', class: 'bg-teal' }
      ];
      
      showStatusModal(`person-status-${personId}`, 'Person', statusOptions, (newStatus) => {
        saveStatusToDB('person', personId, newStatus);
      }, false);
    }
    
    // Edit Vehicle Status
    function editVehicleStatus(vehicleId) {
      const statusOptions = [
        { value: 'for-custody', text: 'For Custody', class: 'bg-warning' },
        { value: 'impounded', text: 'Impounded', class: 'bg-info' },
        { value: 'under-investigation', text: 'Under Investigation', class: 'bg-primary' },
        { value: 'for-auction', text: 'For Public Auction', class: 'bg-orange' },
        { value: 'released', text: 'Released to Owner', class: 'bg-success' },
        { value: 'forfeited', text: 'Forfeited to Government', class: 'bg-purple' },
        { value: 'donated', text: 'Donated', class: 'bg-teal' },
        { value: 'destroyed', text: 'Destroyed', class: 'bg-danger' }
      ];
      
      showStatusModal(`vehicle-status-${vehicleId}`, 'Vehicle', statusOptions, (newStatus) => {
        saveStatusToDB('vehicle', vehicleId, newStatus);
      }, false);
    }
    
    // Edit Item Status
    function editItemStatus(itemId) {
      const statusOptions = [
        { value: 'confiscated', text: 'Confiscated', class: 'bg-warning' },
        { value: 'seized', text: 'Seized', class: 'bg-info' },
        { value: 'under-custody', text: 'Under Custody', class: 'bg-primary' },
        { value: 'for-disposal', text: 'For Disposal', class: 'bg-orange' },
        { value: 'disposed', text: 'Disposed', class: 'bg-success' },
        { value: 'burned', text: 'Burned/Destroyed', class: 'bg-danger' },
        { value: 'forfeited', text: 'Forfeited to Government', class: 'bg-purple' },
        { value: 'donated', text: 'Donated to LGU', class: 'bg-teal' },
        { value: 'returned', text: 'Returned to Owner', class: 'bg-cyan' },
        { value: 'auctioned', text: 'Publicly Auctioned', class: 'bg-indigo' }
      ];
      
      showStatusModal(`item-status-${itemId}`, 'Item', statusOptions, (newStatus) => {
        saveStatusToDB('item', itemId, newStatus);
      }, false);
    }
    
    // Edit Case Status
    function editCaseStatus() {
      const statusOptions = [
        { value: 'under-investigation', text: 'Under Investigation', class: 'bg-primary' },
        { value: 'pending-review', text: 'Pending Review', class: 'bg-warning' },
        { value: 'for-filing', text: 'For Filing', class: 'bg-warning' },
        { value: 'filed-in-court', text: 'Filed in Court', class: 'bg-secondary' },
        { value: 'ongoing-trial', text: 'Ongoing Trial', class: 'bg-info' },
        { value: 'resolved', text: 'Resolved', class: 'bg-success' },
        { value: 'dismissed', text: 'Dismissed', class: 'bg-danger' },
        { value: 'archived', text: 'Archived', class: 'bg-dark' },
        { value: 'on-hold', text: 'On Hold', class: 'bg-danger' },
        { value: 'appealed', text: 'Under Appeal', class: 'bg-teal' }
      ];
      
      // Do not show comments field for Case status
      showStatusModal('case-status', 'Case', statusOptions, (newStatus) => {
        saveStatusToDB('case', window.reportId || 0, newStatus);
      }, false);
    }

    // Save status to server and update UI on success
    async function saveStatusToDB(type, id, newStatus) {
      if (!window.updateStatusUrl) {
        console.error('update URL not set');
        alert('Update URL not configured.');
        return;
      }

      try {
        const res = await fetch(window.updateStatusUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            type: type,
            id: parseInt(id, 10) || 0,
            status: newStatus.text,
            status_key: newStatus.value
          })
        });

        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Update failed');

        // Update UI after successful DB update
        if (type === 'vehicle') updateApprehendedStatus(`vehicle-status-${id}`, newStatus);
        else if (type === 'item') updateApprehendedStatus(`item-status-${id}`, newStatus);
        else if (type === 'case') updateApprehendedStatus('case-status', newStatus);
        else if (type === 'person') updateApprehendedStatus(`person-status-${id}`, newStatus);

      } catch (err) {
        console.error('Failed to save status:', err);
        alert('Hindi na-save sa database: ' + (err.message || err));
      }
    }
    
    // Show Status Modal
    function showStatusModal(elementId, itemType, statusOptions, callback, showComments = true) {
      const currentBadge = document.getElementById(elementId);
      const currentStatus = currentBadge ? currentBadge.textContent.trim().toLowerCase().replace(' ', '-') : '';

      let optionsHtml = '';
      statusOptions.forEach(option => {
        const selected = option.value === currentStatus ? 'selected' : '';
        optionsHtml += `<option value="${option.value}" data-class="${option.class}" ${selected}>${option.text}</option>`;
      });

      const commentsHtml = showComments ? `
                <div class="mb-3">
                  <label for="statusComments" class="form-label">Comments (Optional):</label>
                  <textarea class="form-control" id="statusComments" rows="3" placeholder="Add comments for this status change..."></textarea>
                </div>
      ` : '';

      const modalHtml = `
        <div class="modal fade" id="statusModal" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Edit ${itemType} Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                  <label for="statusSelect" class="form-label">Select Status:</label>
                  <select class="form-select" id="statusSelect">
                    ${optionsHtml}
                  </select>
                </div>
                ${commentsHtml}
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmStatusUpdate()">
                  <i class="fa fa-save me-2"></i>Update Status
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
      
      // Remove existing modal
      const existingModal = document.getElementById('statusModal');
      if (existingModal) {
        existingModal.remove();
      }
      
      document.body.insertAdjacentHTML('beforeend', modalHtml);
      
      const modal = new bootstrap.Modal(document.getElementById('statusModal'));
      modal.show();
      
      // Store callback for later use
      window.currentStatusCallback = callback;
    }
    
    // Confirm Status Update
    function confirmStatusUpdate() {
      const statusSelect = document.getElementById('statusSelect');
      const selectedOption = statusSelect.options[statusSelect.selectedIndex];
      const newStatus = {
        value: statusSelect.value,
        text: selectedOption.text,
        class: selectedOption.getAttribute('data-class')
      };
      const commentsEl = document.getElementById('statusComments');
      const comments = commentsEl ? commentsEl.value : '';
      
      if (confirm(`Are you sure you want to change the status to "${newStatus.text}"?`)) {
        const modal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
        modal.hide();
        
        // Execute callback with new status
        if (window.currentStatusCallback) {
          window.currentStatusCallback(newStatus);
        }
        
        // Log the change
        console.log('Status updated:', {
          newStatus: newStatus,
          comments: comments,
          timestamp: new Date().toLocaleString(),
          user: 'Pj Mordeno (Enforcement Officer)'
        });
      }
    }
    
    // Update Apprehended Status
    function updateApprehendedStatus(elementId, newStatus) {
      const badge = document.getElementById(elementId);
      if (badge) {
        badge.className = `badge ${newStatus.class}`;
        badge.textContent = newStatus.text;
        
        // Show success message
        showSuccessMessage(`Status updated to "${newStatus.text}" successfully!`);
      }
    }
    
    // Show Success Message
    function showSuccessMessage(message) {
      const alertDiv = document.createElement('div');
      alertDiv.className = 'alert alert-success alert-dismissible fade show';
      alertDiv.style.position = 'fixed';
      alertDiv.style.top = '20px';
      alertDiv.style.right = '20px';
      alertDiv.style.zIndex = '9999';
      alertDiv.style.minWidth = '300px';
      alertDiv.innerHTML = `
        <i class="fa fa-check-circle me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      
      document.body.appendChild(alertDiv);
      
      setTimeout(() => {
        if (alertDiv.parentNode) {
          alertDiv.remove();
        }
      }, 4000);
    }
    
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Case Details page initialized with status editing functionality');
    });
  </script>
</body>
</html>
