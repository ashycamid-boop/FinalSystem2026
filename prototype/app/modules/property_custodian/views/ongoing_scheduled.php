<?php
session_start();

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'property_custodian') {
  header('Location: /prototype/index.php');
  exit;
}
// Fetch ongoing and scheduled requests
require_once __DIR__ . '/../../../config/db.php';
$ongoing = [];
try {
  $stmt = $pdo->prepare("SELECT * FROM service_requests WHERE LOWER(status) IN ('ongoing','scheduled') ORDER BY created_at DESC");
  $stmt->execute();
  $ongoing = $stmt->fetchAll();
} catch (Exception $e) {
  error_log('admin ongoing_scheduled fetch error: ' . $e->getMessage());
  $ongoing = [];
}

$ongoingCount = count($ongoing);

// Prepare statement to fetch the earliest action start datetime for a request
try {
  $actionStmt = $pdo->prepare("SELECT action_date, action_time FROM service_request_actions WHERE service_request_id = :id ORDER BY action_date ASC, action_time ASC LIMIT 1");
} catch (Exception $e) {
  error_log('prepare actionStmt error: ' . $e->getMessage());
  $actionStmt = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ongoing / Scheduled</title>
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
</head>
<body>
  <div class="layout">
    <!-- Sidebar -->
    <nav class="sidebar" role="navigation" aria-label="Main sidebar">
      <div class="sidebar-logo">
        <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo">
        <span>CENRO</span>
      </div>
      <div class="sidebar-role">Property Custodian</div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li><a href="equipment_management.php"><i class="fa fa-cogs"></i> Equipment Management</a></li>
          <li><a href="assignments.php"><i class="fa fa-tasks"></i> Assignments</a></li>
          <li class="dropdown active">
            <a href="#" class="dropdown-toggle active" id="serviceDeskToggle" data-target="serviceDeskMenu">
              <i class="fa fa-headset"></i> Service Desk 
              <i class="fa fa-chevron-down dropdown-arrow rotated"></i>
            </a>
            <ul class="dropdown-menu show" id="serviceDeskMenu">
              <li><a href="new_requests.php">New Requests <span class="badge">2</span></a></li>
              <li class="active"><a href="ongoing_scheduled.php">Ongoing / Scheduled <span class="badge badge-blue">2</span></a></li>
              <li><a href="completed.php">Completed</a></li>
              <li><a href="all_requests.php">All Requests</a></li>
            </ul>
        </ul>
      </nav>
    </nav>
    <!-- Main -->
    <div class="main">
      <div class="topbar">
        <div class="topbar-card">
          <div class="topbar-title">Ongoing / Scheduled Requests</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <!-- Ongoing / Scheduled Content -->
        <div class="container-fluid p-4">
          <!-- Top Controls -->
          <div class="row mb-4 align-items-center">
            <div class="col-md-2">
              <input type="text" class="form-control" placeholder="Search">
            </div>
            <div class="col-md-2">
              <input type="text" id="date_from" class="form-control date-picker" placeholder="mm/dd/yyyy" autocomplete="off">
            </div>
            <div class="col-md-2">
              <input type="text" id="date_to" class="form-control date-picker" placeholder="mm/dd/yyyy" autocomplete="off">
            </div>
            <div class="col-md-3">
              <div class="filter-buttons">
                <button id="applyFilter" class="btn btn-primary">Apply</button>
                <button id="clearFilter" class="btn btn-outline-secondary">Clear</button>
              </div>
            </div>
          </div>

          <!-- Ongoing / Scheduled Table -->
          <div class="new-requests-table-section">
            <div class="table-responsive">
              <table class="table table-bordered table-sm" id="ongoingRequestsTable" style="font-size: 0.85rem;">
                <thead class="table-light">
                  <tr>
                    <th style="padding: 8px;">Ticket ID</th>
                    <th style="padding: 8px;">Date Logged</th>
                    <th style="padding: 8px;">Requester</th>
                    <th style="padding: 8px;">Position</th>
                    <th style="padding: 8px;">Office/Unit</th>
                    <th style="padding: 8px;">Type of Request</th>
                    <th style="padding: 8px;">Start Date/Time</th>
                    <th style="padding: 8px;">Status</th>
                    <th style="padding: 8px;">Details</th>
                    <th style="padding: 8px;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($ongoing)): foreach ($ongoing as $r): 
                    $firstActionStart = '-';
                    if ($actionStmt) {
                      try {
                        $actionStmt->execute(['id' => $r['id']]);
                        $firstActionRow = $actionStmt->fetch(PDO::FETCH_ASSOC);
                        if (!empty($firstActionRow) && !empty($firstActionRow['action_date'])) {
                          $firstActionStart = $firstActionRow['action_date'] . (!empty($firstActionRow['action_time']) ? ' ' . $firstActionRow['action_time'] : '');
                        } elseif (!empty($r['start_datetime'])) {
                          $firstActionStart = $r['start_datetime'];
                        }
                      } catch (Exception $e) {
                        error_log('fetch first action start error: ' . $e->getMessage());
                      }
                    } else {
                      if (!empty($r['start_datetime'])) {
                        $firstActionStart = $r['start_datetime'];
                      }
                    }

                    // Prepare display value and format if it's a valid datetime
                    $displayStart = '-';
                    if ($firstActionStart !== '-' && strtotime($firstActionStart) !== false) {
                      $displayStart = date('m/d/Y h:i A', strtotime($firstActionStart));
                    } elseif ($firstActionStart !== '-') {
                      $displayStart = $firstActionStart;
                    }
                  ?>
                    <tr>
                      <td style="padding:8px; vertical-align: middle;"><?php echo htmlspecialchars($r['ticket_no'] ?? $r['id'] ?? ''); ?></td>
                      <td style="padding:8px; vertical-align: middle;">
                        <?php
                          if (!empty($r['ticket_date'])) {
                            echo htmlspecialchars(date('m/d/Y', strtotime($r['ticket_date'])));
                          } elseif (!empty($r['created_at'])) {
                            echo htmlspecialchars(date('m/d/Y', strtotime($r['created_at'])));
                          } else {
                            echo '';
                          }
                        ?>
                      </td>
                      <td style="padding:8px; vertical-align: middle;"><?php echo htmlspecialchars($r['requester_name'] ?? ''); ?></td>
                      <td style="padding:8px; vertical-align: middle;"><?php echo htmlspecialchars($r['requester_position'] ?? ''); ?></td>
                      <td style="padding:8px; vertical-align: middle;"><?php echo htmlspecialchars($r['requester_office'] ?? ''); ?></td>
                      <td style="padding:8px; vertical-align: middle;"><?php echo htmlspecialchars($r['request_type'] ?? ''); ?></td>
                      <td style="padding:8px; vertical-align: middle;"><?php echo htmlspecialchars($displayStart); ?></td>
                      <td style="padding:8px; vertical-align: middle;"><span class="badge bg-info text-dark"><?php echo htmlspecialchars($r['status'] ?? ''); ?></span></td>
                      <td style="padding:8px; vertical-align: middle;"><a href="request_details.php?id=<?php echo urlencode($r['id']); ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                      <td style="padding:8px; vertical-align: middle;">
                        <a href="edit_requests_ongoing.php?id=<?php echo urlencode($r['id'] ?? $r['ticket_no'] ?? ''); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                      </td>
                    </tr>
                  <?php endforeach; else: ?>
                    <tr>
                      <td colspan="10" class="text-center">No ongoing or scheduled requests.</td>
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

  <!-- Timeline CSS -->
  <style>
    .timeline {
      position: relative;
      padding-left: 30px;
    }
    .timeline::before {
      content: '';
      position: absolute;
      left: 15px;
      top: 0;
      bottom: 0;
      width: 2px;
      background: #dee2e6;
    }
    .timeline-item {
      position: relative;
      margin-bottom: 20px;
    }
    .timeline-marker {
      position: absolute;
      left: -22px;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      border: 2px solid white;
    }
    .timeline-content {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 5px;
      border-left: 3px solid #007bff;
    }
  </style>

  <!-- Bootstrap 5 JS Bundle -->
  <!-- Flatpickr JS -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <!-- Filtering and datepicker initialization -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.flatpickr) {
        flatpickr('.date-picker', { dateFormat: 'm/d/Y', allowInput: true });
      }

      function parseMDY(str) {
        if (!str) return null;
        var parts = str.split('/');
        if (parts.length !== 3) return null;
        var m = parseInt(parts[0], 10) - 1;
        var d = parseInt(parts[1], 10);
        var y = parseInt(parts[2], 10);
        if (isNaN(m) || isNaN(d) || isNaN(y)) return null;
        return new Date(y, m, d);
      }

      var applyBtn = document.getElementById('applyFilter');
      var clearBtn = document.getElementById('clearFilter');
      var table = document.getElementById('ongoingRequestsTable');
      var tbody = table ? table.tBodies[0] : null;

      function applyFilter() {
        var search = (document.querySelector('input[type="text"].form-control') || { value: '' }).value.trim().toLowerCase();
        var fromStr = document.getElementById('date_from').value.trim();
        var toStr = document.getElementById('date_to').value.trim();
        var fromDate = parseMDY(fromStr);
        var toDate = parseMDY(toStr);

        if (tbody) {
          Array.from(tbody.rows).forEach(function (row) {
            var cells = row.cells;
            if (!cells || cells.length < 2) return;
            var dateText = cells[1].innerText.trim();
            var rowDate = parseMDY(dateText);
            var textContent = row.innerText.toLowerCase();

            var matchesSearch = !search || textContent.indexOf(search) !== -1;
            var matchesDate = true;
            if (fromDate && rowDate) {
              matchesDate = rowDate >= fromDate;
            }
            if (toDate && rowDate && matchesDate) {
              matchesDate = rowDate <= toDate;
            }
            if ((fromDate || toDate) && !rowDate) matchesDate = false;

            if (matchesSearch && matchesDate) {
              row.style.display = '';
            } else {
              row.style.display = 'none';
            }
          });
        }
      }

      function clearFilter() {
        var searchInput = document.querySelector('input[type="text"].form-control');
        if (searchInput) searchInput.value = '';
        document.getElementById('date_from').value = '';
        document.getElementById('date_to').value = '';
        if (tbody) Array.from(tbody.rows).forEach(function (row) { row.style.display = ''; });
      }

      if (applyBtn) applyBtn.addEventListener('click', function (e) { e.preventDefault(); applyFilter(); });
      if (clearBtn) clearBtn.addEventListener('click', function (e) { e.preventDefault(); clearFilter(); });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Admin Dashboard JavaScript -->
  <script src="../../../../public/assets/js/admin/dashboard.js"></script>
  <!-- Admin Navigation JavaScript -->
  <script src="../../../../public/assets/js/admin/navigation.js"></script>
</body>
</html>