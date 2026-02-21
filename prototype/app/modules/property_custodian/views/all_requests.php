<?php
session_start();

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'property_custodian') {
  header('Location: /prototype/index.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Requests</title>
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
              <li><a href="ongoing_scheduled.php">Ongoing / Scheduled <span class="badge badge-blue">2</span></a></li>
              <li><a href="completed.php">Completed</a></li>
              <li class="active"><a href="all_requests.php">All Requests</a></li>
            </ul>
        </ul>
      </nav>
    </nav>
    <!-- Main -->
    <div class="main">
      <div class="topbar">
        <div class="topbar-card">
          <div class="topbar-title">All Requests</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <div class="container-fluid p-4">
          <div class="row mb-4">
            <div class="col-12">
              <div class="d-flex gap-2 align-items-center">
                <input type="text" class="form-control" placeholder="Search" style="width: 250px;">
                <input type="text" id="date_from" class="form-control date-picker" placeholder="mm/dd/yyyy" style="width: 150px;" autocomplete="off">
                <input type="text" id="date_to" class="form-control date-picker" placeholder="mm/dd/yyyy" style="width: 150px;" autocomplete="off">
                <button id="applyFilter" class="btn btn-primary">Apply</button>
                <button id="clearFilter" class="btn btn-outline-secondary">Clear</button>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body p-0">
              <div class="table-responsive">
                <table id="allRequestsTable" class="table table-sm table-hover mb-0" style="font-size: 0.85rem;">
                  <thead class="table-light">
                    <tr>
                      <th style="padding: 8px;">Ticket ID</th>
                      <th style="padding: 8px;">Date Logged</th>
                      <th style="padding: 8px;">Requester</th>
                      <th style="padding: 8px;">Position</th>
                      <th style="padding: 8px;">Office Unit</th>
                      <th style="padding: 8px;">Type of Request</th>
                      <th style="padding: 8px;">Start Date/Time</th>
                      <th style="padding: 8px;">End Date/Time</th>
                      <th style="padding: 8px;">Status</th>
                      <th style="padding: 8px;">Details</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    // Load all service requests (any status)
                    require_once __DIR__ . '/../../../config/db.php';
                    if (isset($pdo) && is_object($pdo)) {
                      try { $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); } catch (Exception $e) {}
                    }
                    $requests = [];
                    try {
                      $stmt = $pdo->query('SELECT * FROM service_requests ORDER BY created_at DESC');
                      $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                      error_log('all_requests fetch error: ' . $e->getMessage());
                      $requests = [];
                    }

                    // Prepare statements to fetch earliest and latest action datetimes for each request
                    try {
                      $earliestStmt = $pdo->prepare("SELECT action_date, action_time FROM service_request_actions WHERE service_request_id = :id AND action_date IS NOT NULL ORDER BY action_date ASC, action_time ASC LIMIT 1");
                      $latestStmt = $pdo->prepare("SELECT action_date, action_time FROM service_request_actions WHERE service_request_id = :id ORDER BY id DESC LIMIT 1");
                    } catch (Exception $e) {
                      error_log('prepare action stmts error: ' . $e->getMessage());
                      $earliestStmt = $latestStmt = null;
                    }

                    if (!empty($requests)):
                      foreach ($requests as $r):
                    ?>
                      <tr>
                        <td style="padding:8px;"><?php echo htmlspecialchars($r['ticket_no'] ?? $r['id'] ?? ''); ?></td>
                        <td style="padding:8px;"><?php echo !empty($r['created_at']) ? date('m/d/Y h:i A', strtotime($r['created_at'])) : ''; ?></td>
                        <td style="padding:8px;"><?php echo htmlspecialchars($r['requester_name'] ?? ''); ?></td>
                        <td style="padding:8px;"><?php echo htmlspecialchars($r['requester_position'] ?? ''); ?></td>
                        <td style="padding:8px;"><?php echo htmlspecialchars($r['requester_office'] ?? ''); ?></td>
                        <td style="padding:8px;"><?php echo htmlspecialchars($r['devices'] ?? $r['request_type'] ?? ''); ?></td>
                        <?php
                          $start = '-';
                          $end = '-';
                          if (isset($earliestStmt) && $earliestStmt) {
                            try {
                              $earliestStmt->execute(['id' => $r['id']]);
                              $er = $earliestStmt->fetch(PDO::FETCH_ASSOC);
                              if (!empty($er) && !empty($er['action_date'])) {
                                $start = $er['action_date'];
                                if (!empty($er['action_time'])) {
                                  $t_ts = strtotime($er['action_time']);
                                  $t_disp = $t_ts !== false ? date('h:i A', $t_ts) : $er['action_time'];
                                  $start .= ' ' . $t_disp;
                                }
                              } elseif (!empty($r['start_datetime'])) {
                                $start = $r['start_datetime'];
                              }
                            } catch (Exception $e) { error_log('earliest fetch error: ' . $e->getMessage()); }
                          } else {
                            if (!empty($r['start_datetime'])) $start = $r['start_datetime'];
                          }

                          if (isset($latestStmt) && $latestStmt) {
                            try {
                              $latestStmt->execute(['id' => $r['id']]);
                              $le = $latestStmt->fetch(PDO::FETCH_ASSOC);
                              if (!empty($le)) {
                                $ad = $le['action_date'] ?? '';
                                $at = $le['action_time'] ?? '';
                                if ($ad !== '' || $at !== '') {
                                  $end = trim(($ad !== '' ? $ad : '') . (!empty($at) ? ' ' . $at : ''));
                                } elseif (!empty($r['updated_at'])) {
                                  $end = $r['updated_at'];
                                }
                              } elseif (!empty($r['updated_at'])) {
                                $end = $r['updated_at'];
                              }
                            } catch (Exception $e) { error_log('latest fetch error: ' . $e->getMessage()); }
                          } else {
                            if (!empty($r['updated_at'])) $end = $r['updated_at'];
                          }

                          $displayStart = ($start !== '-' && strtotime($start) !== false) ? date('m/d/Y h:i A', strtotime($start)) : $start;
                          $displayEnd = ($end !== '-' && strtotime($end) !== false) ? date('m/d/Y h:i A', strtotime($end)) : $end;
                        ?>
                        <td style="padding:8px;"><?php echo htmlspecialchars($displayStart); ?></td>
                        <td style="padding:8px;"><?php echo htmlspecialchars($displayEnd); ?></td>
                        <td style="padding:8px; font-weight:600;">
                          <?php
                            $st = $r['status'] ?? '';
                            $s = strtolower(trim($st));
                            $badgeClass = 'badge bg-light text-dark';
                            if ($s === 'pending' || $s === 'open') {
                              $badgeClass = 'badge bg-warning text-dark';
                            } elseif ($s === 'ongoing' || $s === 'scheduled') {
                              $badgeClass = 'badge bg-info text-dark';
                            } elseif ($s === 'completed') {
                              $badgeClass = 'badge bg-success text-white';
                            } elseif (in_array($s, ['closed', 'cancelled', 'rejected', 'declined'])) {
                              $badgeClass = 'badge bg-secondary text-white';
                            }
                          ?>
                          <span class="<?php echo htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($st); ?></span>
                        </td>
                        <td style="padding:8px;"><a href="request_details.php?id=<?php echo urlencode($r['id'] ?? $r['ticket_no']); ?>" class="btn btn-sm btn-outline-primary">Details</a></td>
                      </tr>
                    <?php
                      endforeach;
                    else:
                    ?>
                      <tr>
                        <td colspan="10" class="text-center py-3">No requests found.</td>
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
  </div>

  <!-- Flatpickr JS -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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
      var table = document.getElementById('allRequestsTable');
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
  <script src="../../../../public/assets/js/admin/dashboard.js"></script>
  <script src="../../../../public/assets/js/admin/navigation.js"></script>
</body>
</html>