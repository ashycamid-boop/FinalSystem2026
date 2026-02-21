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
  <title>Dashboard</title>
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
      <div class="sidebar-role"><?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'Administrator'; ?></div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li class="active"><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li><a href="user_management.php"><i class="fa fa-users"></i> User Management</a></li>
          <li><a href="spot_reports.php"><i class="fa fa-file-text"></i> Spot Reports</a></li>
          <li><a href="case_management.php"><i class="fa fa-briefcase"></i> Case Management</a></li>
          <li><a href="apprehended_items.php"><i class="fa fa-archive"></i> Apprehended Items</a></li>
          <li><a href="equipment_management.php"><i class="fa fa-cogs"></i> Equipment Management</a></li>
          <li><a href="assignments.php"><i class="fa fa-tasks"></i> Assignments</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" id="serviceDeskToggle" data-target="serviceDeskMenu">
              <i class="fa fa-headset"></i> Service Desk 
              <i class="fa fa-chevron-down dropdown-arrow"></i>
            </a>
            <ul class="dropdown-menu" id="serviceDeskMenu">
              <li><a href="new_requests.php">New Requests</a></li>
              <li><a href="ongoing_scheduled.php">Ongoing / Scheduled</a></li>
              <li><a href="completed.php">Completed</a></li>
              <li><a href="all_requests.php">All Requests</a></li>
            </ul>
          </li>
          <li><a href="statistical_report.php"><i class="fa fa-chart-bar"></i> Statistical Report</a></li>
        </ul>
      </nav>
    </nav>
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
        <div class="container-fluid p-4">
          <!-- Top Statistics Cards -->
          <div class="row mb-4">
            <!-- Total Users -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 15px !important; transition: all 0.3s ease;">
                <div class="card-body p-4 position-relative">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h6 class="text-muted mb-1 fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">TOTAL USERS</h6>
                      <h1 class="mb-0 fw-bold" style="font-size: 2.5rem; color: #2c3e50;"><span id="totalUsersCount">—</span></h1>
                    </div>
                    <div class="rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                      <i class="fa fa-users text-white" style="font-size: 24px;"></i>
                    </div>
                  </div>

                  <div class="position-absolute" style="top: -20px; right: -20px; width: 100px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; opacity: 0.1;"></div>
                </div>
              </div>
            </div>

            <!-- Spot Reports -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 15px !important; transition: all 0.3s ease;">
                <div class="card-body p-4 position-relative">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h6 class="text-muted mb-1 fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">SPOT REPORTS</h6>
                      <h1 class="mb-0 fw-bold" style="font-size: 2.5rem; color: #2c3e50;"><span id="spotReportsCount">—</span></h1>
                    </div>
                    <div class="rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                      <i class="fa fa-file-alt text-white" style="font-size: 24px;"></i>
                    </div>
                  </div>
                  <div class="mt-3">
                    <div class="d-flex align-items-center mb-2">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #28a745;"></div>
                      <small class="text-muted">Approved: <span id="spotApprovedCount" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #ffc107;"></div>
                      <small class="text-muted">Pending: <span id="spotPendingCount" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #dc3545;"></div>
                      <small class="text-muted">Rejected: <span id="spotRejectedCount" class="fw-bold text-dark">—</span></small>
                    </div>
                  </div>
                  <div class="position-absolute" style="top: -20px; right: -20px; width: 100px; height: 100px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 50%; opacity: 0.1;"></div>
                </div>
              </div>
            </div>

            <!-- Case Management -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 15px !important; transition: all 0.3s ease;">
                <div class="card-body p-4 position-relative">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h6 class="text-muted mb-1 fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">CASE MANAGEMENT</h6>
                      <h1 class="mb-0 fw-bold" style="font-size: 2.5rem; color: #2c3e50;"><span id="caseManagementCount">—</span></h1>
                    </div>
                    <div class="rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                      <i class="fa fa-briefcase text-white" style="font-size: 24px;"></i>
                    </div>
                  </div>
                  <div class="mt-3">
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #fd7e14;"></div>
                      <small class="text-muted">Under Investigation: <span id="caseUnderInvestigation" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #ffc107;"></div>
                      <small class="text-muted">For Filing: <span id="caseForFiling" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #17a2b8;"></div>
                      <small class="text-muted">On Going: <span id="caseOnGoing" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #dc3545;"></div>
                      <small class="text-muted">Dismissed: <span id="caseDismissed" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #6c757d;"></div>
                      <small class="text-muted">Resolved: <span id="caseResolved" class="fw-bold text-dark">—</span></small>
                    </div>
                  </div>
                  <div class="position-absolute" style="top: -20px; right: -20px; width: 100px; height: 100px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 50%; opacity: 0.1;"></div>
                </div>
              </div>
            </div>

            <!-- Apprehended -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 15px !important; transition: all 0.3s ease;">
                <div class="card-body p-4 position-relative">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h6 class="text-muted mb-1 fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">APPREHENDED</h6>
                      <h1 class="mb-0 fw-bold" style="font-size: 2.5rem; color: #2c3e50;"><span id="apprehendedCount">—</span></h1>
                    </div>
                    <div class="rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);">
                      <i class="fa fa-exclamation-triangle text-white" style="font-size: 24px;"></i>
                    </div>
                  </div>
                  <div class="mt-3">
                    <div class="d-flex align-items-center mb-2">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #fd7e14;"></div>
                      <small class="text-muted">Person: <span id="apprehendedPersonCount" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #dc3545;"></div>
                      <small class="text-muted">Vehicles: <span id="apprehendedVehiclesCount" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #6f42c1;"></div>
                      <small class="text-muted">Items: <span id="apprehendedItemsCount" class="fw-bold text-dark">—</span></small>
                    </div>
                  </div>
                  <div class="position-absolute" style="top: -20px; right: -20px; width: 100px; height: 100px; background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); border-radius: 50%; opacity: 0.1;"></div>
                </div>
              </div>
            </div>

            <!-- Equipment -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 15px !important; transition: all 0.3s ease;">
                <div class="card-body p-4 position-relative">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h6 class="text-muted mb-1 fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">EQUIPMENT</h6>
                      <h1 class="mb-0 fw-bold" style="font-size: 2.5rem; color: #2c3e50;"><span id="equipmentCount">—</span></h1>
                    </div>
                    <div class="rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                      <i class="fa fa-cogs text-white" style="font-size: 24px;"></i>
                    </div>
                  </div>
                  <div class="mt-3">
                    <div class="d-flex align-items-center mb-2">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #28a745;"></div>
                      <small class="text-muted">In Use: <span id="equipmentInUseCount" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #ffc107;"></div>
                      <small class="text-muted">Available: <span id="equipmentAvailableCount" class="fw-bold text-dark">—</span></small>
                    </div>
                  </div>
                  <div class="position-absolute" style="top: -20px; right: -20px; width: 100px; height: 100px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 50%; opacity: 0.1;"></div>
                </div>
              </div>
            </div>

            <!-- Service Requests -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 15px !important; transition: all 0.3s ease;">
                <div class="card-body p-4 position-relative">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h6 class="text-muted mb-1 fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">SERVICE REQUESTS</h6>
                      <h1 class="mb-0 fw-bold" style="font-size: 2.5rem; color: #2c3e50;"><span id="serviceRequestsCount">—</span></h1>
                    </div>
                    <div class="rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                      <i class="fa fa-headset text-white" style="font-size: 24px;"></i>
                    </div>
                  </div>
                  <div class="mt-3">
                    <div class="d-flex align-items-center mb-2">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #ffc107;"></div>
                      <small class="text-muted">Pending: <span id="servicePendingCount" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #17a2b8;"></div>
                      <small class="text-muted">On Going: <span id="serviceOnGoingCount" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #28a745;"></div>
                      <small class="text-muted">Completed: <span id="serviceCompletedCount" class="fw-bold text-dark">—</span></small>
                    </div>
                  </div>
                  <div class="position-absolute" style="top: -20px; right: -20px; width: 100px; height: 100px; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); border-radius: 50%; opacity: 0.1;"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts Section -->
          <div class="row">
            <!-- Current Spot Reports Status -->
            <div class="col-lg-6 mb-4">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex align-items-center">
                  <i class="fa fa-chart-bar text-success me-2"></i>
                  <h6 class="mb-0">Current Spot Reports Status</h6>
                </div>
                <div class="card-body">
                  <div style="height: 300px; position: relative;">
                    <canvas id="spotReportsChart" style="position: absolute; width: 100%; height: 100%;"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- Case Status Distribution -->
            <div class="col-lg-6 mb-4">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex align-items-center">
                  <i class="fa fa-chart-pie text-warning me-2"></i>
                  <h6 class="mb-0">Case Status Distribution</h6>
                </div>
                <div class="card-body">
                  <div style="height: 300px; position: relative;">
                    <canvas id="caseStatusChart" style="position: absolute; width: 100%; height: 100%;"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- Equipment Status by Category -->
            <div class="col-lg-6 mb-4">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex align-items-center">
                  <i class="fa fa-chart-bar text-info me-2"></i>
                  <h6 class="mb-0">Equipment Status by Category</h6>
                </div>
                <div class="card-body">
                  <div style="height: 300px; position: relative;">
                    <canvas id="equipmentChart" style="position: absolute; width: 100%; height: 100%;"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- User Roles Distribution -->
            <div class="col-lg-6 mb-4">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex align-items-center">
                  <i class="fa fa-users text-primary me-2"></i>
                  <h6 class="mb-0">User Roles Distribution</h6>
                </div>
                <div class="card-body">
                  <div style="height: 300px; position: relative;">
                    <canvas id="userRolesChart" style="position: absolute; width: 100%; height: 100%;"></canvas>
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
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Chart.js DataLabels Plugin -->
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
  <!-- Admin Navigation JavaScript -->
  <script src="../../../../public/assets/js/admin/navigation.js"></script>
  
  <!-- Dashboard functionality -->
  <script>
    // Register Chart.js plugins
    Chart.register(ChartDataLabels);
    
    // Initialize profile dropdown (from dashboard.js)
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

    // Initialize dashboard charts
    function initializeDashboardCharts() {
      // Sample chart data
      const ctx1 = document.getElementById('spotReportsChart');
      const ctx2 = document.getElementById('caseStatusChart');
      const ctx3 = document.getElementById('equipmentChart');
      const ctx4 = document.getElementById('userRolesChart');
      
      if (ctx1) {
        // Chart data should be loaded from the server/API. Initialize empty chart for now.
        new Chart(ctx1, {
          type: 'bar',
          data: {
            labels: [],
            datasets: [{
              label: 'Spot Reports',
              data: [],
              backgroundColor: []
            }]
          }
        });
      }
      
      if (ctx2) {
        // Case status distribution - data to be provided dynamically
        new Chart(ctx2, {
          type: 'doughnut',
          data: {
            labels: [],
            datasets: [{
              data: [],
              backgroundColor: []
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false
          }
        });
      }
      
      if (ctx3) {
        new Chart(ctx3, {
          type: 'bar',
          data: {
            labels: [],
            datasets: [{
              label: 'Equipment',
              data: [],
              backgroundColor: []
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false
          }
        });
      }
      
      if (ctx4) {
        new Chart(ctx4, {
          type: 'doughnut',
          data: {
            labels: [],
            datasets: [{
              data: [],
              backgroundColor: []
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false
          }
        });
      }
    }
    
    // Initialize everything when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
      initializeProfileDropdown();
      
      // Debug: Check if Service Desk elements exist
      setTimeout(function() {
        const serviceDeskToggle = document.getElementById('serviceDeskToggle');
        const serviceDeskMenu = document.getElementById('serviceDeskMenu');
        
        if (serviceDeskToggle && serviceDeskMenu) {
          serviceDeskToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const currentDisplay = serviceDeskMenu.style.display;
            if (currentDisplay === 'block') {
              serviceDeskMenu.style.display = 'none';
              serviceDeskToggle.classList.remove('active');
            } else {
              serviceDeskMenu.style.display = 'block';
              serviceDeskMenu.style.maxHeight = '300px';
              serviceDeskMenu.style.opacity = '1';
              serviceDeskMenu.style.padding = '5px 0';
              serviceDeskToggle.classList.add('active');
            }
            serviceDeskMenu.classList.toggle('show');
            const arrow = serviceDeskToggle.querySelector('.dropdown-arrow');
            if (arrow) arrow.classList.toggle('rotated');
          });
        }
      }, 200);
      
      // Initialize charts after a short delay
      setTimeout(function() {
        if (typeof Chart !== 'undefined') {
          initializeDashboardCharts();
        }
      }, 500);
    });
  </script>
  </body>
</html>

