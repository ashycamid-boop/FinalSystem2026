<?php
session_start();

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'enforcement_officer') {
    header('Location: /prototype/index.php');
    exit;
}

// Load spot report JSON by reference (ref GET parameter)
$report = null;
if (!empty($_GET['ref'])) {
  $ref = preg_replace('/[^A-Za-z0-9\-]/', '', $_GET['ref']);
  $jsonPath = __DIR__ . '/../../../../storage/spot_reports/' . $ref . '.json';
  if (is_readable($jsonPath)) {
    $content = file_get_contents($jsonPath);
    $report = json_decode($content, true);
  }
}

// Debug helper: when ?debug=1 is present, output file path resolution info
if (!empty($_GET['debug']) && $report) {
  echo "<pre style='background:#f8f9fa;border:1px solid #ddd;padding:10px;'>File resolution debug:\n";
  $all = [];
  foreach (['evidence_files','pdf_files','person_files','vehicle_files','item_files'] as $k) {
    if (!empty($report[$k])) {
      if (is_array($report[$k])) {
        foreach ($report[$k] as $idx => $v) {
          if (is_array($v)) {
            // person/vehicle/item entries are arrays of arrays with 'path'
            foreach ($v as $vv) {
              $all[] = $vv['path'] ?? $vv;
            }
          } else {
            $all[] = $v;
          }
        }
      }
    }
  }
  $public = realpath(__DIR__ . '/../../../../public');
  foreach ($all as $ap) {
    $href = web_href($ap);
    $real = @realpath($ap);
    $cand = '';
    if ($public) {
      $p = str_replace('\\','/',$ap);
      if (stripos($p,'/public/')!==false) {
        $parts = explode('/public/',$p,2);
        $cand = $public . '/' . $parts[1];
      } elseif (stripos($p,'public/')===0) {
        $cand = $public . '/' . substr($p,strlen('public/'));
      } elseif (stripos($p,'uploads/')===0) {
        $cand = $public . '/' . $p;
      }
    }
    echo "path: ".htmlspecialchars($ap)."\n  web_href: ".htmlspecialchars($href)."\n  realpath: ".htmlspecialchars($real ?: '') ."\n  public_candidate: ".htmlspecialchars($cand ?: '') ."\n  exists(realpath): ".(is_file($real)?'yes':'no') ."\n  exists(candidate): ".(is_file($cand)?'yes':'no') ."\n\n";
  }
  echo "</pre>";
}

// If JSON not found, try loading from database (static view)
if (!$report && !empty($ref)) {
  try {
    require_once dirname(dirname(dirname(__DIR__))) . '/config/db.php'; // loads $pdo
    $s = $pdo->prepare('SELECT * FROM spot_reports WHERE reference_no = ? LIMIT 1');
    $s->execute([$ref]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    if ($r) {
      $report = [];
      $report['reference_no'] = $r['reference_no'];
      $report['incident_datetime'] = $r['incident_datetime'];
      $report['memo_date'] = $r['memo_date'];
      $report['location'] = $r['location'];
      $report['summary'] = $r['summary'];
      $report['team_leader'] = $r['team_leader'];
      $report['custodian'] = $r['custodian'];
      $report['status'] = $r['status'];

      // persons (include status)
      $pstmt = $pdo->prepare('SELECT name, age, gender, address, contact, role, status FROM spot_report_persons WHERE report_id = ?');
      $pstmt->execute([$r['id']]);
      $report['persons'] = $pstmt->fetchAll(PDO::FETCH_ASSOC);

      // vehicles (include status and remarks)
      $vstmt = $pdo->prepare('SELECT plate, make, color, owner, contact, engine, status, remarks FROM spot_report_vehicles WHERE report_id = ?');
      $vstmt->execute([$r['id']]);
      $report['vehicles'] = $vstmt->fetchAll(PDO::FETCH_ASSOC);

      // items (include status)
      $itstmt = $pdo->prepare('SELECT item_no, type, description, quantity, volume, value, remarks, status FROM spot_report_items WHERE report_id = ?');
      $itstmt->execute([$r['id']]);
      $items = $itstmt->fetchAll(PDO::FETCH_ASSOC);
      $report['items'] = array_map(function($it){
        return [
          'no' => $it['item_no'],
          'type' => $it['type'],
          'description' => $it['description'],
          'quantity' => $it['quantity'],
          'volume' => $it['volume'],
          'value' => $it['value'],
          'remarks' => $it['remarks'],
          'status' => isset($it['status']) ? $it['status'] : ''
        ];
      }, $items);

      // files: group by orig_name tokens (person#N:, vehicle#N:, item#N:) when present
      $fst = $pdo->prepare('SELECT file_type, file_path, orig_name FROM spot_report_files WHERE report_id = ?');
      $fst->execute([$r['id']]);
      $files = $fst->fetchAll(PDO::FETCH_ASSOC);
      $report['evidence_files'] = [];
      $report['pdf_files'] = [];
      $report['person_files'] = [];
      $report['vehicle_files'] = [];
      $report['item_files'] = [];
      foreach ($files as $f) {
        $path = $f['file_path'];
        $orig = $f['orig_name'] ?? '';
        if ($f['file_type'] === 'pdf') {
          $report['pdf_files'][] = $path;
          continue;
        }
        // expect orig_name like: person#0:photo.jpg or vehicle#1:photo.png or item#2:doc.jpg
        if (preg_match('/^(person|vehicle|item)#(\d+):(.+)$/', $orig, $m)) {
          $type = $m[1];
          $idx = intval($m[2]);
          $origfn = $m[3];
          $entry = ['path' => $path, 'orig' => $origfn];
          if ($type === 'person') {
            if (!isset($report['person_files'][$idx])) $report['person_files'][$idx] = [];
            $report['person_files'][$idx][] = $entry;
          } elseif ($type === 'vehicle') {
            if (!isset($report['vehicle_files'][$idx])) $report['vehicle_files'][$idx] = [];
            $report['vehicle_files'][$idx][] = $entry;
          } elseif ($type === 'item') {
            if (!isset($report['item_files'][$idx])) $report['item_files'][$idx] = [];
            $report['item_files'][$idx][] = $entry;
          }
        } else {
          // not mapped to a specific row
          $report['evidence_files'][] = $path;
        }
      }
    }
  } catch (Exception $e) {
    // ignore DB errors, $report stays null
  }
}

// Helper: format datetime for display
function fmt_dt($dt) {
  if (empty($dt)) return '';
  $ts = strtotime($dt);
  if (!$ts) return htmlspecialchars($dt);
  return date('M j, Y g:i a', $ts);
}

// Helper: human readable filesize
function hr_filesize_from_webpath($webpath) {
  if (!$webpath) return '';
  // Try to find the physical file and return its filesize.
  $fs = null;
  $p = str_replace('\\', '/', $webpath);
  // 1) If path is an absolute filesystem path
  $real = @realpath($webpath);
  if ($real && is_file($real)) {
    $fs = filesize($real);
  }
  // 2) Try mapping via document root + web_href
  if ($fs === null) {
    $docroot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    if ($docroot) {
      $href = web_href($webpath);
      if ($href && strpos($href, '/') === 0) {
        $candidate = $docroot . $href;
        if (is_file($candidate)) $fs = filesize($candidate);
      }
    }
  }
  // 3) Try resolving under project public folder
  if ($fs === null) {
    $public = realpath(__DIR__ . '/../../../../public');
    if ($public) {
      // if webpath contains '/public/' or starts with 'public/' or 'uploads/'
      if (stripos($p, '/public/') !== false) {
        $parts = explode('/public/', $p, 2);
        $candidate = $public . '/' . $parts[1];
        if (is_file($candidate)) $fs = filesize($candidate);
      }
      if ($fs === null && stripos($p, 'public/') === 0) {
        $candidate = $public . '/' . substr($p, strlen('public/'));
        if (is_file($candidate)) $fs = filesize($candidate);
      }
      if ($fs === null && stripos($p, 'uploads/') === 0) {
        $candidate = $public . '/' . $p;
        if (is_file($candidate)) $fs = filesize($candidate);
      }
    }
  }
  if ($fs === null) return '';
  $units = ['B','KB','MB','GB'];
  $i = 0;
  while ($fs >= 1024 && $i < 3) { $fs /= 1024; $i++; }
  return round($fs, 2) . ' ' . $units[$i];
}

function file_icon_class($path) {
  $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
  if (in_array($ext, ['jpg','jpeg','png','gif','bmp','webp'])) return 'fa-image text-primary';
  if (in_array($ext, ['mp4','mov','webm','avi','mkv'])) return 'fa-video text-success';
  if ($ext === 'pdf') return 'fa-file-pdf text-danger';
  return 'fa-file text-secondary';
}

// Helper: produce a web-accessible href from stored file_path
function web_href($path) {
  if (!$path) return '';
  if (preg_match('#^https?://#i', $path)) return $path;

  $p = str_replace('\\', '/', $path);
  $projectFolder = 'prototype';

  // Normalize leading "/uploads/..." to "uploads/..."
  if (strpos($p, '/uploads/') === 0) {
    $p = ltrim($p, '/');
  }

  // Our uploads are served from /<projectFolder>/public/uploads/...
  if (stripos($p, 'uploads/') === 0) {
    return '/' . $projectFolder . '/public/' . $p;
  }

  // If already an absolute URL path like "/prototype/..." return as-is
  if (strpos($p, '/') === 0) return $p;

  // Fallback: assume project-relative
  return '/' . $projectFolder . '/' . ltrim($p, '/');
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
  
  <!-- Print styles -->
  <style>
    @media print {
      /* Hide non-printable elements */
      .sidebar, .topbar, .action-buttons {
        display: none !important;
      }
      
      /* Adjust main content for print */
      .main {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
      }
      
      .main-content {
        margin: 0 !important;
        padding: 20px !important;
      }
      
      .container-fluid {
        padding: 0 !important;
      }
      
      /* Print-specific styling */
      body {
        font-size: 12px !important;
        line-height: 1.4 !important;
      }
      
      .report-header {
        page-break-inside: avoid;
        margin-bottom: 20px !important;
      }
      
      .report-section {
        page-break-inside: avoid;
        margin-bottom: 15px !important;
      }
      
      .table {
        font-size: 11px !important;
      }
      
      .table th,
      .table td {
        padding: 5px !important;
        border: 1px solid #000 !important;
      }
      
      .table thead th {
        background-color: #f8f9fa !important;
        font-weight: bold !important;
      }
      
      /* Logo sizing for print */
      .logo-left, .logo-right {
        width: 60px !important;
        height: auto !important;
      }
      
      /* Badge styling for print */
      .badge {
        border: 1px solid #000 !important;
        padding: 3px 6px !important;
        color: #000 !important;
        background-color: #fff !important;
      }
      
      /* File items for print */
      .file-item {
        margin-bottom: 5px !important;
      }
      
      /* Force black text */
      * {
        color: #000 !important;
      }
      
      /* Page breaks */
      .page-break {
        page-break-before: always;
      }
    }
  </style>
  
  <!-- Layout override: enlarge main content like admin case_details -->
  <style>
    /* Increase the inner content max width so the report area is wider */
    .main .main-content .container-fluid {
      max-width: 1200px;
      width: 100%;
    }

    /* Add a bit more internal padding to match case details spacing */
    .main .main-content {
      padding: 2rem !important;
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
          <li class="active"><a href="spot_reports.php"><i class="fa fa-file-text"></i> Spot Reports</a></li>
          <li><a href="case_management.php"><i class="fa fa-briefcase"></i> Case Management</a></li>
          <li><a href="apprehended_items.php"><i class="fa fa-archive"></i> Apprehended Items</a></li>
          <li><a href="service_requests.php"><i class="fa fa-cog"></i> Service Requests</a></li>
          <li><a href="statistical_report.php"><i class="fa fa-chart-bar"></i> Statistical Report</a></li>
        </ul>
      </nav>
    </nav>
    <div class="main">
      <div class="topbar">
          <div class="topbar-card">
            <div class="topbar-title">Spot Reports Details</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <!-- Action Buttons -->
        <div class="action-buttons mb-3 px-4">
          <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">Back</button>
        </div>
        <!-- View Spot Report Content -->
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
                <hr style="border-top: 2px solid #ff0000ff; margin: 0px 0 0px 0;">
                <h4 class="mt-3">Spot Report</h4>
              </div>
              <img src="../../../../public/assets/images/bagong-pilipinas-logo.png" alt="Bagong Pilipinas Logo" class="logo-right">
            </div>
          </div>

          <!-- Incident Details Section -->
          <div class="report-section mb-4">
            <table class="table table-bordered">
              <?php if ($report): ?>
              <tr>
                <td class="field-label">Incident Date & Time:</td>
                <td><?= htmlspecialchars(fmt_dt($report['incident_datetime'] ?? '')) ?></td>
                <td class="field-label">Memo Date:</td>
                <td><?= htmlspecialchars(fmt_dt($report['memo_date'] ?? '')) ?></td>
                <td class="field-label">Reference No.:</td>
                <td><?= htmlspecialchars($report['reference_no'] ?? '') ?></td>
              </tr>
              <tr>
                <td class="field-label">Location:</td>
                <td colspan="5"><?= nl2br(htmlspecialchars($report['location'] ?? '')) ?></td>
              </tr>
              <tr>
                <td class="field-label">Summary:</td>
                <td colspan="5"><?= nl2br(htmlspecialchars($report['summary'] ?? '')) ?></td>
              </tr>
              <?php else: ?>
              <tr>
                <td colspan="6">Report not found. Provide a valid reference.</td>
              </tr>
              <?php endif; ?>
            </table>
          </div>

          <!-- Personnel Section -->
          <div class="report-section mb-4">
            <div class="row">
              <div class="col-md-6">
                <table class="table table-bordered">
                  <?php if ($report): ?>
                  <tr>
                    <td class="field-label">Team Leader:</td>
                    <td><?= htmlspecialchars($report['team_leader'] ?? '') ?></td>
                  </tr>
                  <?php endif; ?>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-bordered">
                  <?php if ($report): ?>
                  <tr>
                    <td class="field-label">Custodian:</td>
                    <td><?= htmlspecialchars($report['custodian'] ?? '') ?></td>
                  </tr>
                  <?php endif; ?>
                </table>
              </div>
            </div>
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
                  <th>Status</th>
                  <th>Evidence</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($report && !empty($report['persons'])): ?>
                  <?php for ($pi = 0; $pi < count($report['persons']); $pi++): $p = $report['persons'][$pi]; ?>
                    <tr>
                      <td><?= htmlspecialchars($p['name'] ?? '') ?></td>
                      <td><?= htmlspecialchars($p['age'] ?? '') ?></td>
                      <td><?= htmlspecialchars($p['gender'] ?? '') ?></td>
                      <td><?= htmlspecialchars($p['address'] ?? '') ?></td>
                      <td><?= htmlspecialchars($p['contact'] ?? '') ?></td>
                      <td><?= htmlspecialchars($p['role'] ?? '') ?></td>
                      <td><span class="badge bg-light text-dark"><?= htmlspecialchars($p['status'] ?? '-') ?></span></td>
                      <td>
                        <?php
                          $pf = $report['person_files'][$pi] ?? array();
                          if (!empty($pf)) {
                            foreach ($pf as $f) {
                              $icon = file_icon_class($f['path']);
                              $href = web_href($f['path']);
                              $label = !empty($f['orig']) ? $f['orig'] : basename($f['path']);
                              echo '<div class="file-item mb-1">';
                              echo '<a href="' . htmlspecialchars($href) . '" target="_blank" title="' . htmlspecialchars($label) . '"><i class="fa ' . htmlspecialchars($icon) . ' fa-lg"></i></a>';
                              echo '</div>';
                            }
                          } else {
                            echo '<span class="text-muted">-</span>';
                          }
                        ?>
                      </td>
                    </tr>
                  <?php endfor; ?>
                <?php else: ?>
                  <tr><td colspan="8">No apprehended persons recorded.</td></tr>
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
                </tr>
              </thead>
              <tbody>
                <?php if ($report && !empty($report['vehicles'])): ?>
                  <?php for ($vi = 0; $vi < count($report['vehicles']); $vi++): $v = $report['vehicles'][$vi]; ?>
                    <tr>
                      <td><?= htmlspecialchars($v['plate'] ?? '') ?></td>
                      <td><?= htmlspecialchars($v['make'] ?? '') ?></td>
                      <td><?= htmlspecialchars($v['color'] ?? '') ?></td>
                      <td><?= htmlspecialchars($v['owner'] ?? '') ?></td>
                      <td><?= htmlspecialchars($v['contact'] ?? '') ?></td>
                      <td><?= htmlspecialchars($v['engine'] ?? '') ?></td>
                      <td><?= htmlspecialchars($v['remarks'] ?? '') ?></td>
                      <td><?= htmlspecialchars($v['status'] ?? '') ?></td>
                      <td>
                        <?php
                          $vf = $report['vehicle_files'][$vi] ?? array();
                          if (!empty($vf)) {
                            foreach ($vf as $f) {
                              $icon = file_icon_class($f['path']);
                              $href = web_href($f['path']);
                              $label = !empty($f['orig']) ? $f['orig'] : basename($f['path']);
                              echo '<div class="file-item mb-1">';
                              echo '<a href="' . htmlspecialchars($href) . '" target="_blank" title="' . htmlspecialchars($label) . '"><i class="fa ' . htmlspecialchars($icon) . ' fa-lg"></i></a>';
                              echo '</div>';
                            }
                          } else {
                            echo '<span class="text-muted">-</span>';
                          }
                        ?>
                      </td>
                    </tr>
                  <?php endfor; ?>
                <?php else: ?>
                  <tr><td colspan="9">No vehicles recorded.</td></tr>
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
                </tr>
              </thead>
              <tbody>
                <?php if ($report && !empty($report['items'])): ?>
                  <?php for ($ii = 0; $ii < count($report['items']); $ii++): $it = $report['items'][$ii]; ?>
                    <tr>
                      <td><?= htmlspecialchars($it['no'] ?? '') ?></td>
                      <td><?= htmlspecialchars($it['type'] ?? '') ?></td>
                      <td><?= htmlspecialchars($it['description'] ?? '') ?></td>
                      <td><?= htmlspecialchars($it['quantity'] ?? '') ?></td>
                      <td><?= htmlspecialchars($it['volume'] ?? '') ?></td>
                      <td><?= htmlspecialchars($it['value'] ?? '') ?></td>
                      <td><?= htmlspecialchars($it['remarks'] ?? '') ?></td>
                      <td><?= htmlspecialchars($it['status'] ?? '') ?></td>
                      <td>
                        <?php
                          $itf = $report['item_files'][$ii] ?? array();
                          if (!empty($itf)) {
                            foreach ($itf as $f) {
                              $icon = file_icon_class($f['path']);
                              $href = web_href($f['path']);
                              $label = !empty($f['orig']) ? $f['orig'] : basename($f['path']);
                              echo '<div class="file-item mb-1">';
                              echo '<a href="' . htmlspecialchars($href) . '" target="_blank" title="' . htmlspecialchars($label) . '"><i class="fa ' . htmlspecialchars($icon) . ' fa-lg"></i></a>';
                              echo '</div>';
                            }
                          } else {
                            echo '<span class="text-muted">-</span>';
                          }
                        ?>
                      </td>
                    </tr>
                  <?php endfor; ?>
                <?php else: ?>
                  <tr><td colspan="9">No seizure items recorded.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Evidence Section -->
          <div class="report-section mb-4">
            <div class="row">
              <div class="col-md-6">
                <h6>Evidence(s)</h6>
                <div class="evidence-files">
                  <?php if ($report && !empty($report['evidence_files'])): ?>
                    <?php foreach ($report['evidence_files'] as $ef): ?>
                        <div class="file-item mb-2">
                          <?php $icon = file_icon_class($ef); $href = web_href($ef); $label = basename($ef); ?>
                          <a href="<?= htmlspecialchars($href) ?>" target="_blank" title="<?= htmlspecialchars($label) ?>"><i class="fa <?= htmlspecialchars($icon) ?> fa-lg"></i></a>
                        </div>
                      <?php endforeach; ?>
                  <?php else: ?>
                    <div class="text-muted">No evidence files attached.</div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-md-6">
                <h6>Spot Report Memorandum (PDF)</h6>
                <div class="pdf-files">
                  <?php if ($report && !empty($report['pdf_files'])): ?>
                    <?php foreach ($report['pdf_files'] as $pf): ?>
                        <div class="file-item mb-2">
                          <?php $icon2 = file_icon_class($pf); $href2 = web_href($pf); $label2 = basename($pf); ?>
                          <a href="<?= htmlspecialchars($href2) ?>" target="_blank" title="<?= htmlspecialchars($label2) ?>"><i class="fa <?= htmlspecialchars($icon2) ?> fa-lg"></i></a>
                        </div>
                      <?php endforeach; ?>
                  <?php else: ?>
                    <div class="text-muted">No PDF attachments.</div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Status Section -->
          <div class="report-section mb-4">
            <table class="table table-bordered" style="width: 200px;">
              <tr>
                <td class="text-center">
                  <h6>Status</h6>
                  <?php if ($report):
                    $st = ($report['status'] ?? '');
                    $stNorm = trim($st);
                    $badge = 'secondary';
                    if (strcasecmp($stNorm, 'Draft') === 0) $badge = 'warning';
                    elseif (strcasecmp($stNorm, 'Pending') === 0) $badge = 'warning';
                    elseif (strcasecmp($stNorm, 'Approved') === 0) $badge = 'success';
                    elseif (strcasecmp($stNorm, 'Rejected') === 0) $badge = 'danger';
                    elseif (strcasecmp($stNorm, 'Under Review') === 0 || strcasecmp($stNorm, 'Under_Review') === 0) $badge = 'info';
                  ?>
                    <span class="badge bg-<?= htmlspecialchars($badge) ?> fs-6"><?= htmlspecialchars($st) ?></span>
                  <?php else: ?>
                    <span class="badge bg-secondary fs-6">N/A</span>
                  <?php endif; ?>
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
</body>
</html>