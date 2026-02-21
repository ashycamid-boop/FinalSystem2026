<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Service Requests</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Admin common styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <!-- Dashboard specific styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/dashboard.css">
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
      <div class="sidebar-role">Enforcement Officer</div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li><a href="spot_reports.php"><i class="fa fa-file-text"></i> Spot Reports</a></li>
          <li><a href="case_management.php"><i class="fa fa-briefcase"></i> Case Management</a></li>
          <li><a href="apprehended_items.php"><i class="fa fa-archive"></i> Apprehended Items</a></li>
          <li class="active"><a href="service_requests.php"><i class="fa fa-cog"></i> Service Requests</a></li>
          <li><a href="statistical_report.php"><i class="fa fa-chart-bar"></i> Statistical Report</a></li>
        </ul>
      </nav>
    </nav>
    <!-- Main -->
    <div class="main">
      <div class="topbar">
        <div class="topbar-card">
          <div class="topbar-title">Service Requests</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <div class="container-fluid p-2">
            <?php
            // Load DB and fetch user's service requests with optional filters (search, date range)
            require_once __DIR__ . '/../../../config/db.php';
            $currentUserId = $_SESSION['uid'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
            $currentUserEmail = $_SESSION['email'] ?? null;
            $service_requests = [];

            // Filter inputs (GET)
            $search = trim($_GET['search'] ?? '');
            $rawDateFrom = trim($_GET['date_from'] ?? '');
            $rawDateTo = trim($_GET['date_to'] ?? '');

            // Normalize dates: accept MM-DD-YYYY (display) and convert to YYYY-MM-DD for SQL
            $dateFrom = '';
            $dateTo = '';
            $displayDateFrom = '';
            $displayDateTo = '';

            if ($rawDateFrom !== '') {
              $dt = DateTime::createFromFormat('m-d-Y', $rawDateFrom);
              if ($dt && $dt->format('m-d-Y') === $rawDateFrom) {
                $dateFrom = $dt->format('Y-m-d');
                $displayDateFrom = $rawDateFrom;
              } else {
                $dt2 = DateTime::createFromFormat('Y-m-d', $rawDateFrom);
                if ($dt2 && $dt2->format('Y-m-d') === $rawDateFrom) {
                  $dateFrom = $dt2->format('Y-m-d');
                  $displayDateFrom = $dt2->format('m-d-Y');
                }
              }
            }

            if ($rawDateTo !== '') {
              $dt = DateTime::createFromFormat('m-d-Y', $rawDateTo);
              if ($dt && $dt->format('m-d-Y') === $rawDateTo) {
                $dateTo = $dt->format('Y-m-d');
                $displayDateTo = $rawDateTo;
              } else {
                $dt2 = DateTime::createFromFormat('Y-m-d', $rawDateTo);
                if ($dt2 && $dt2->format('Y-m-d') === $rawDateTo) {
                  $dateTo = $dt2->format('Y-m-d');
                  $displayDateTo = $dt2->format('m-d-Y');
                }
              }
            }

            try {
              // Enforce ownership: require authenticated user (created_by) or matching requester_email.
              // If neither is present, do not run any queries and return empty results.
              if (empty($currentUserId) && empty($currentUserEmail)) {
                $service_requests = [];
              } else {
                $where = [];
                $params = [];

                // Owner constraint is mandatory and always applied
                if (!empty($currentUserId)) {
                  $where[] = 'created_by = ?';
                  $params[] = $currentUserId;
                } else {
                  $where[] = 'requester_email = ?';
                  $params[] = $currentUserEmail;
                }

                // Additional optional filters (search, date range)
                if ($search !== '') {
                  $where[] = '(ticket_no LIKE ? OR request_type LIKE ? OR request_description LIKE ? OR requester_name LIKE ?)';
                  $like = '%' . $search . '%';
                  $params[] = $like;
                  $params[] = $like;
                  $params[] = $like;
                  $params[] = $like;
                }

                if ($dateFrom !== '') {
                  $where[] = 'DATE(COALESCE(ticket_date, created_at)) >= ?';
                  $params[] = $dateFrom;
                }

                if ($dateTo !== '') {
                  $where[] = 'DATE(COALESCE(ticket_date, created_at)) <= ?';
                  $params[] = $dateTo;
                }

                // Build and execute query — ownership condition is guaranteed to be present
                $sql = 'SELECT * FROM service_requests WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $service_requests = $stmt->fetchAll();
              }
            } catch (Exception $e) {
              error_log('service_requests fetch error: ' . $e->getMessage());
              $service_requests = [];
            }
            ?>

            <!-- Filter Controls -->
          <form method="get" action="service_requests.php">
          <div class="row mb-4 g-3 align-items-end">
            <!-- Search -->
            <div class="col-md-3">
              <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Search" style="border-radius: 8px; border: 1px solid #e0e0e0; height: 38px;">
            </div>
            <!-- Date From -->
            <div class="col-md-2">
              <input type="text" name="date_from" value="<?php echo htmlspecialchars($displayDateFrom); ?>" class="form-control" placeholder="MM-DD-YYYY" pattern="\d{2}-\d{2}-\d{4}" style="border-radius: 8px; border: 1px solid #e0e0e0; height: 38px;">
            </div>
            <!-- Date To -->
            <div class="col-md-2">
              <input type="text" name="date_to" value="<?php echo htmlspecialchars($displayDateTo); ?>" class="form-control" placeholder="MM-DD-YYYY" pattern="\d{2}-\d{2}-\d{4}" style="border-radius: 8px; border: 1px solid #e0e0e0; height: 38px;">
            </div>
            <!-- Apply Button -->
            <div class="col-md-1">
              <button type="submit" class="btn btn-primary w-100" style="border-radius: 8px; background: #1976d2; border: none; height: 38px; font-size: 14px;">Apply</button>
            </div>
            <!-- Clear Button -->
            <div class="col-md-1">
              <a href="service_requests.php" class="btn btn-outline-secondary w-100" style="border-radius: 8px; height: 38px; font-size: 14px;">Clear</a>
            </div>
          </div>
          </form>

          <!-- New Request Button -->
          <div class="row mb-3">
            <div class="col-12 d-flex justify-content-end">
              <a href="new_requests.php" class="btn btn-primary" style="border-radius: 8px; background: #1976d2; border: none; padding: 8px 16px; font-size: 14px; text-decoration: none;">
                + New Request
              </a>
            </div>
          </div>

          <!-- Data Table -->
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr>
                          <th>Ticket ID</th>
                          <th>Date Logged</th>
                          <th>Type of Request</th>
                          <th>Description of Request</th>
                          <th>Status</th>
                          <th>Details</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (!empty($service_requests) && is_array($service_requests)): ?>
                          <?php foreach ($service_requests as $req): ?>
                            <tr>
                              <td><?php echo htmlspecialchars($req['ticket_no'] ?? $req['id'] ?? ''); ?></td>
                              <td><?php echo !empty($req['ticket_date']) ? htmlspecialchars(date('Y-m-d', strtotime($req['ticket_date']))) : htmlspecialchars($req['created_at'] ?? ''); ?></td>
                              <td><?php echo htmlspecialchars($req['request_type'] ?? ''); ?></td>
                              <td><?php echo htmlspecialchars($req['request_description'] ?? $req['request_description'] ?? ''); ?></td>
                              <td>
                                <?php
                                  // Normalize status values: map legacy 'open' to 'pending'
                                  $rawStatus = strtolower(trim($req['status'] ?? ''));
                                  if ($rawStatus === 'open') $rawStatus = 'pending';
                                  $displayStatus = ucfirst($rawStatus ?: '');
                                  $badgeColor = ($rawStatus === 'pending') ? '#ffc107' : (($rawStatus === 'completed') ? '#28a745' : '#6c757d');
                                ?>
                                <span class="badge" style="background-color: <?php echo htmlspecialchars($badgeColor); ?>; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px;"><?php echo htmlspecialchars($displayStatus); ?></span>
                              </td>
                              <td>
                                <a href="request_details.php?id=<?php echo urlencode($req['id'] ?? ''); ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <?php if ($rawStatus === 'completed'): ?>
                                  <a href="rate_request.php?id=<?php echo urlencode($req['id'] ?? ''); ?>" class="btn btn-sm btn-warning text-dark ms-2"><i class="fa fa-star me-1"></i>Rate</a>
                                <?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <tr>
                            <td colspan="6" class="text-center">No service requests found.</td>
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
    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Admin Navigation JavaScript -->
  <script src="../../../../public/assets/js/admin/navigation.js"></script>
  <!-- Flatpickr JS -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      flatpickr('input[name="date_from"], input[name="date_to"]', {
        dateFormat: 'm-d-Y',
        allowInput: true,
        clickOpens: true
      });
    });
  </script>
  
  <!-- Dashboard functionality -->
  <script>
    // Initialize profile dropdown
    function initializeProfileDropdown() {
      const profileCard = document.getElementById('profileCard');
      const profileDropdown = document.getElementById('profileDropdown');
      
      if (!profileCard || !profileDropdown) return;
      
      let dropdownOpen = false;

      function toggleDropdown() {
          dropdownOpen = !dropdownOpen;
          profileDropdown.style.display = dropdownOpen ? 'flex' : 'none';
      }

      profileCard.addEventListener('click', function(e) {
          toggleDropdown();
          e.stopPropagation();
      });

      document.addEventListener('click', function(e) {
          if (!profileCard.contains(e.target)) {
              dropdownOpen = false;
              profileDropdown.style.display = 'none';
          }
      });

      document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape' && dropdownOpen) {
              dropdownOpen = false;
              profileDropdown.style.display = 'none';
          }
      });
    }
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
      initializeProfileDropdown();
    });
  </script>
  </body>
</html>

