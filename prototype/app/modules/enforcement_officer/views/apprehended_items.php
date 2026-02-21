<!DOCTYPE html>
<?php
// Load vehicles and seizure items for the Apprehended Items list
$items = [];
try {
  require_once dirname(__DIR__, 3) . '/config/db.php'; // loads $pdo

  // Helper: map item/vehicle status text to badge class used elsewhere
  if (!function_exists('map_status_to_class')) {
    function map_status_to_class($s) {
      $sRaw = strtolower(trim((string)$s));
      if ($sRaw === '') return 'bg-secondary';

      // Person statuses (case_detailsupdate mapping)
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

  // Helper: build URL for stored file paths
  if (!function_exists('build_file_url_local')) {
    function build_file_url_local($path) {
      if (empty($path)) return '';
      if (preg_match('#^(https?:)?//#i', $path)) return $path;
      $p = '/' . ltrim($path, '/');
      $projectRoot = dirname(__DIR__, 4);
      // If file exists under project root or /public, prefer those web paths
      if (file_exists($projectRoot . $p)) {
        if (defined('BASE_URL') && BASE_URL) return rtrim(BASE_URL, '/') . $p;
        return $p;
      }
      if (file_exists($projectRoot . '/public' . $p)) {
        if (defined('BASE_URL') && BASE_URL) return rtrim(BASE_URL, '/') . '/public' . $p;
        return '/public' . $p;
      }
      // If file path already starts with /public or /uploads, and BASE_URL is defined, prepend it
      if (defined('BASE_URL') && BASE_URL) return rtrim(BASE_URL, '/') . $p;
      // fallback to host absolute URL
      $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
      $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
      return $scheme . '://' . $host . $p;
    }
  }

  // Vehicles (only approved cases - check parent spot_reports.status)
  $vstmt = $pdo->prepare("SELECT v.*, r.reference_no FROM spot_report_vehicles v JOIN spot_reports r ON r.id = v.report_id WHERE LOWER(TRIM(COALESCE(r.status,''))) = 'approved' ORDER BY v.id DESC");
  $vstmt->execute();
  $vehicles = $vstmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($vehicles as $v) {
    $plate = $v['plate_no'] ?? $v['plate'] ?? '';
    $make = $v['make_model'] ?? $v['make'] ?? '';
    $color = $v['color'] ?? '';
    $descParts = array_filter([$make, $color, $plate]);
    $description = implode(', ', $descParts);

    $items[] = [
      'type' => 'vehicle',
      'type_label' => 'Vehicle',
      'reference_no' => $v['reference_no'] ?? '',
      'description' => $description,
      'quantity' => 1,
      'volume' => '-',
      'evidence' => '',
      'status_label' => $v['status'] ?? '',
      'status_class' => map_status_to_class($v['status'] ?? ''),
      'report_id' => $v['report_id'] ?? $v['reportId'] ?? null,
      'last_updated' => $v['updated_at'] ?? $v['created_at'] ?? ''
    ];
  }

  // Seizure / Items
  // Seizure / Items (only approved cases - check parent spot_reports.status)
  $istmt = $pdo->prepare("SELECT i.*, r.reference_no FROM spot_report_items i JOIN spot_reports r ON r.id = i.report_id WHERE LOWER(TRIM(COALESCE(r.status,''))) = 'approved' ORDER BY i.id DESC");
  $istmt->execute();
  $seizures = $istmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($seizures as $s) {
    $items[] = [
      'type' => 'item',
      'type_label' => $s['item_type'] ?? $s['type'] ?? 'Item',
      'reference_no' => $s['reference_no'] ?? '',
      'description' => $s['description'] ?? '',
      'quantity' => $s['quantity'] ?? '',
      'volume' => $s['volume'] ?? '',
      'evidence' => '',
      'status_label' => $s['status'] ?? '',
      'status_class' => map_status_to_class($s['status'] ?? ''),
      'report_id' => $s['report_id'] ?? $s['reportId'] ?? null,
      'last_updated' => $s['updated_at'] ?? $s['created_at'] ?? ''
    ];
  }

  // Attach files/evidence: fetch files for involved reports
  $reportIds = array_values(array_filter(array_unique(array_map(function($it){ return $it['report_id'] ?? null; }, $items))));
  if (!empty($reportIds)) {
    $placeholders = implode(',', array_fill(0, count($reportIds), '?'));
    $ff = $pdo->prepare("SELECT * FROM spot_report_files WHERE report_id IN ($placeholders) ORDER BY id ASC");
    $ff->execute($reportIds);
    $files = $ff->fetchAll(PDO::FETCH_ASSOC);
    $filesByReport = [];
    foreach ($files as $f) {
      $rid = $f['report_id'] ?? null;
      if (!isset($filesByReport[$rid])) $filesByReport[$rid] = [];
      $filesByReport[$rid][] = $f;
    }

    foreach ($items as &$it) {
      $rid = $it['report_id'] ?? null;
      $ehtml = '';
      if ($rid && !empty($filesByReport[$rid])) {
        // prefer files that reference the same kind (vehicle/item) in orig_name
        $chosen = null;
        foreach ($filesByReport[$rid] as $f) {
          $orig = strtolower($f['orig_name'] ?? $f['file_name'] ?? basename($f['file_path'] ?? $f['path'] ?? ''));
          if ($it['type'] === 'vehicle' && strpos($orig, 'vehicle') !== false) { $chosen = $f; break; }
          if ($it['type'] === 'item' && strpos($orig, 'item') !== false) { $chosen = $f; break; }
        }
        if (!$chosen) $chosen = $filesByReport[$rid][0];

        $fpath = $chosen['file_path'] ?? $chosen['path'] ?? $chosen['file_name'] ?? '';
        $url = build_file_url_local($fpath);
        $ext = strtolower(pathinfo($fpath, PATHINFO_EXTENSION));
        // Render an icon-only link (no thumbnail or filename) — clickable to open the file
        $iconClass = 'fa-file';
        $typeAttr = 'file';
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
          $iconClass = 'fa-image';
          $typeAttr = 'image';
        } elseif ($ext === 'pdf') {
          $iconClass = 'fa-file-pdf';
          $typeAttr = 'pdf';
        }
        $title = htmlspecialchars($chosen['orig_name'] ?? $chosen['file_name'] ?? basename($fpath));
        $ehtml = '<a href="' . htmlspecialchars($url) . '" target="_blank" title="' . $title . '" class="evidence-icon" data-type="' . $typeAttr . '"><i class="fa ' . $iconClass . '"></i></a>';
      }
      $it['evidence'] = $ehtml;
    }
    unset($it);
  }

} catch (Exception $e) {
  // If DB fails, leave $items empty so view shows the "No apprehended items found." message
  $items = [];
}
// Ensure items are shown in ascending reference number order (natural/date-like)
if (!empty($items) && is_array($items)) {
  usort($items, function($a, $b) {
    $ra = $a['reference_no'] ?? '';
    $rb = $b['reference_no'] ?? '';
    // Reverse order: newest/large reference first
    $c = strcmp($rb, $ra);
    if ($c !== 0) return $c;
    // Tie-breaker: keep consistent reverse order
    return strcmp($b['type'] ?? '', $a['type'] ?? '');
  });
}
?>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Apprehended Items - CENRO NASIPIT</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Admin common styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <!-- Apprehended Items specific styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/apprehended-items.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Fredoka+One:400&display=swap" rel="stylesheet">
  <style>
    .bg-purple { background-color: #6f42c1 !important; color: white !important; }
    .bg-orange { background-color: #fd7e14 !important; color: white !important; }
    .bg-teal { background-color: #20c997 !important; color: white !important; }
    .bg-indigo { background-color: #6610f2 !important; color: white !important; }
    .bg-pink { background-color: #e83e8c !important; color: white !important; }
    .bg-cyan { background-color: #0dcaf0 !important; color: white !important; }
    .badge.bg-success { background-color: #28a745 !important; color: white !important; }
    .badge.bg-primary { background-color: #0d6efd !important; color: white !important; }
    /* Evidence icon styles */
    .evidence-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 8px;
      box-shadow: 0 1px 2px rgba(16,24,40,0.04), 0 1px 3px rgba(16,24,40,0.06);
      border: 1px solid rgba(13,110,253,0.06);
      text-decoration: none;
      color: #2c3e50;
      transition: transform .12s ease, box-shadow .12s ease;
      padding: 0;
      margin-right: 6px;
    }
    .evidence-icon .fa { font-size: 16px; }
    .evidence-icon:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(16,24,40,0.08); }
    .evidence-icon[data-type="image"] { background: linear-gradient(180deg, #fff 0%, #f8fafc 100%); color: #0d6efd; }
    .evidence-icon[data-type="pdf"] { background: linear-gradient(180deg, #fff 0%, #fff5f5 100%); color: #d63384; }
    .evidence-icon[data-type="file"] { background: linear-gradient(180deg, #fff 0%, #fbfbfc 100%); color: #6c757d; }
    .evidence-icon[title] { position: relative; }
    .evidence-icon:focus { outline: none; box-shadow: 0 0 0 3px rgba(13,110,253,0.12); }
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
          <li><a href="case_management.php"><i class="fa fa-briefcase"></i> Case Management</a></li>
          <li class="active"><a href="apprehended_items.php"><i class="fa fa-archive"></i> Apprehended Items</a></li>
          <li><a href="service_requests.php"><i class="fa fa-cog"></i> Service Requests</a></li>
          <li><a href="statistical_report.php"><i class="fa fa-chart-bar"></i> Statistical Report</a></li>
        </ul>
      </nav>
    </nav>
    <!-- Main -->
    <div class="main">
      <div class="topbar">
        <div class="topbar-card">
          <div class="topbar-title">Apprehended Items</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <div class="container-fluid">
          
          <!-- Search and Filter Section -->
          <div class="search-filter-section mb-4">
            <div class="row">
              <div class="col-md-6">
                <div class="search-box">
                  <input type="text" class="form-control" id="searchInput" placeholder="Search">
                  <i class="fa fa-search search-icon"></i>
                </div>
              </div>
              <div class="col-md-6">
                <div class="filter-buttons d-flex gap-2">
                  <button class="btn btn-filter active" data-filter="all">All</button>
                  <button class="btn btn-filter" data-filter="vehicle">Vehicle</button>
                  <button class="btn btn-filter" data-filter="item">Items</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Items Table -->
          <div class="items-table-section">
            <div class="table-responsive">
              <table class="table table-hover" id="itemsTable">
                <thead class="table-light">
                  <tr>
                    <th>Reference No.</th>
                    <th>Item Type</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Volume</th>
                    <th>Evidence</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($items) && is_array($items)): ?>
                    <?php foreach ($items as $item): ?>
                      <?php
                        $type = strtolower(trim($item['type'] ?? ''));
                        // Only display vehicles and seizure items (map other allowed types to 'item')
                        $allowedItemTypes = ['equipment', 'forest-product', 'item', 'seizure', 'seizure-item'];
                        if ($type !== 'vehicle' && !in_array($type, $allowedItemTypes, true)) {
                          continue;
                        }
                        $rowType = ($type === 'vehicle') ? 'vehicle' : 'item';
                      ?>
                      <tr data-type="<?php echo $rowType; ?>">
                        <td><?php echo htmlspecialchars($item['reference_no'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($item['type_label'] ?? ($type === 'vehicle' ? 'Vehicle' : 'Item')); ?></td>
                        <td><?php echo htmlspecialchars($item['description'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($item['volume'] ?? ''); ?></td>
                        <td><?php echo $item['evidence'] ?? ''; ?></td>
                        <td><span class="badge <?php echo htmlspecialchars($item['status_class'] ?? ''); ?>"><?php echo htmlspecialchars($item['status_label'] ?? ''); ?></span></td>
                        <?php
                          $luRaw = $item['last_updated'] ?? '';
                          $lastUpdatedDisplay = '-';
                          if (!empty($luRaw)) {
                            // If numeric timestamp, convert to int; else try strtotime
                            if (is_numeric($luRaw)) {
                              $ts = (int)$luRaw;
                            } else {
                              $ts = strtotime($luRaw);
                            }
                            if ($ts !== false && $ts > 0) {
                              $lastUpdatedDisplay = date('M d, Y g:i a', $ts);
                            } else {
                              // Fallback to raw string if parsing failed
                              $lastUpdatedDisplay = $luRaw;
                            }
                          }
                        ?>
                        <td><?php echo htmlspecialchars($lastUpdatedDisplay); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="8" class="text-center">No apprehended items found.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
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
  <!-- Apprehended Items JavaScript -->
  <script src="../../../../public/assets/js/admin/apprehended-items.js"></script>
</body>
</html>
              </div>
            </div>
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