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
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #007bff;"></div>
                      <small class="text-muted">Under Investigation: <span id="caseUnderInvestigation" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #ffc107;"></div>
                      <small class="text-muted">Pending Review: <span id="casePendingReview" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #ffc107;"></div>
                      <small class="text-muted">For Filing: <span id="caseForFiling" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #6c757d;"></div>
                      <small class="text-muted">Filed in Court: <span id="caseFiledInCourt" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #17a2b8;"></div>
                      <small class="text-muted">Ongoing Trial: <span id="caseOngoingTrial" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #28a745;"></div>
                      <small class="text-muted">Resolved: <span id="caseResolved" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #dc3545;"></div>
                      <small class="text-muted">Dismissed: <span id="caseDismissed" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #343a40;"></div>
                      <small class="text-muted">Archived: <span id="caseArchived" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #dc3545;"></div>
                      <small class="text-muted">On Hold: <span id="caseOnHold" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #20c997;"></div>
                      <small class="text-muted">Under Appeal: <span id="caseUnderAppeal" class="fw-bold text-dark">—</span></small>
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
                        <small class="text-muted">Assigned: <span id="equipmentAssignedCount" class="fw-bold text-dark">—</span></small>
                      </div>
                      <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #ffc107;"></div>
                        <small class="text-muted">Available: <span id="equipmentAvailableCount" class="fw-bold text-dark">—</span></small>
                      </div>
                      <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #17a2b8;"></div>
                        <small class="text-muted">Under Maintenance: <span id="equipmentUnderMaintenanceCount" class="fw-bold text-dark">—</span></small>
                      </div>
                      <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #dc3545;"></div>
                        <small class="text-muted">Damaged: <span id="equipmentDamagedCount" class="fw-bold text-dark">—</span></small>
                      </div>
                      <div class="d-flex align-items-center">
                        <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #6c757d;"></div>
                        <small class="text-muted">Out of Service: <span id="equipmentOutOfServiceCount" class="fw-bold text-dark">—</span></small>
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

    // Initialize dashboard charts and return references
    function initializeDashboardCharts() {
      const charts = {};
      const ctx1 = document.getElementById('spotReportsChart');
      const ctx2 = document.getElementById('caseStatusChart');
      const ctx3 = document.getElementById('equipmentChart');
      const ctx4 = document.getElementById('userRolesChart');

      if (ctx1) {
        charts.spotReports = new Chart(ctx1, {
          type: 'bar',
          data: {
            labels: ['Approved','Pending','Rejected'],
            datasets: [{
              label: 'Spot Reports',
              data: [0,0,0],
              backgroundColor: ['#28a745','#ffc107','#dc3545']
            }]
          },
          options: { responsive: true, maintainAspectRatio: false }
        });
      }

      if (ctx2) {
        charts.caseStatus = new Chart(ctx2, {
          type: 'doughnut',
          data: {
            labels: [
              'Under Investigation',
              'Pending Review',
              'For Filing',
              'Filed in Court',
              'Ongoing Trial',
              'Resolved',
              'Dismissed',
              'Archived',
              'On Hold',
              'Under Appeal'
            ],
            datasets: [{
              data: [0,0,0,0,0,0,0,0,0,0],
              backgroundColor: ['#007bff','#ffc107','#ffc107','#6c757d','#17a2b8','#28a745','#dc3545','#343a40','#dc3545','#20c997']
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '45%',
            plugins: {
              legend: { position: 'right', labels: { boxWidth: 12 } },
              datalabels: {
                color: '#ffffff',
                formatter: function(value) { return value > 0 ? value : ''; },
                font: { weight: 'bold', size: 12 }
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const v = context.parsed || 0;
                    const data = context.chart.data.datasets[0].data || [];
                    const sum = data.reduce((a,b) => a + (b || 0), 0);
                    const pct = sum ? ((v / sum) * 100).toFixed(1) + '%' : '0%';
                    return context.label + ': ' + v + ' (' + pct + ')';
                  }
                }
              }
            }
          }
        });
      }

      if (ctx3) {
        charts.equipment = new Chart(ctx3, {
          type: 'bar',
          data: {
            labels: ['Assigned','Available','Under Maintenance','Damaged','Out of Service'],
            datasets: [{ label: 'Equipment', data: [0,0,0,0,0], backgroundColor: ['#28a745','#ffc107','#17a2b8','#dc3545','#6c757d'] }]
          },
          options: { responsive: true, maintainAspectRatio: false }
        });
      }

      if (ctx4) {
        charts.userRoles = new Chart(ctx4, {
          type: 'doughnut',
          data: { labels: [], datasets: [{ data: [], backgroundColor: [] }] },
          options: { responsive: true, maintainAspectRatio: false }
        });
      }

      // expose charts globally for updates
      window.dashboardCharts = charts;
      return charts;
    }

    // Fetch dashboard counts from backend and update DOM + charts
    function loadDashboardData() {
      const url = '/prototype/app/api/dashboard_counts.php';
      fetch(url, { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
          // Basic counts
          if (data.total_users !== undefined) document.getElementById('totalUsersCount').textContent = data.total_users;
          if (data.spot_reports) {
            document.getElementById('spotReportsCount').textContent = data.spot_reports.total ?? '—';
            document.getElementById('spotApprovedCount').textContent = data.spot_reports.approved ?? '—';
            document.getElementById('spotPendingCount').textContent = data.spot_reports.pending ?? '—';
            document.getElementById('spotRejectedCount').textContent = data.spot_reports.rejected ?? '—';
            const srChart = window.dashboardCharts && window.dashboardCharts.spotReports;
            if (srChart) {
              srChart.data.datasets[0].data = [data.spot_reports.approved ?? 0, data.spot_reports.pending ?? 0, data.spot_reports.rejected ?? 0];
              srChart.update();
            }
          }

          if (data.cases) {
            document.getElementById('caseManagementCount').textContent = data.cases.total ?? '—';
            // helper: normalize strings for robust matching
            function normalizeKey(s) {
              if (!s && s !== 0) return '';
              return String(s).toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
            }

            // build normalized map of incoming status keys -> counts
            const incoming = {};
            Object.keys(data.cases.statuses || {}).forEach(k => {
              incoming[normalizeKey(k)] = data.cases.statuses[k];
            });

            // map statuses into card elements using normalized lookup
            const mapIds = {
              'Under Investigation':'caseUnderInvestigation',
              'Pending Review':'casePendingReview',
              'For Filing':'caseForFiling',
              'Filed in Court':'caseFiledInCourt',
              'Ongoing Trial':'caseOngoingTrial',
              'Resolved':'caseResolved',
              'Dismissed':'caseDismissed',
              'Archived':'caseArchived',
              'On Hold':'caseOnHold',
              'Under Appeal':'caseUnderAppeal'
            };
            Object.keys(mapIds).forEach(k => {
              const el = document.getElementById(mapIds[k]);
              if (!el) return;
              const val = incoming[normalizeKey(k)];
              el.textContent = (typeof val === 'number' ? val : (val ? val : 0));
            });

            // update case status chart with normalized matching
            const ch = window.dashboardCharts && window.dashboardCharts.caseStatus;
            if (ch) {
              const labels = ch.data.labels || [];
              ch.data.datasets[0].data = labels.map(lbl => {
                const key = normalizeKey(lbl);
                return incoming[key] ?? 0;
              });
              ch.update();
            }
          }

          if (data.equipment) {
            document.getElementById('equipmentCount').textContent = data.equipment.total ?? '—';
            document.getElementById('equipmentAssignedCount').textContent = data.equipment.assigned ?? 0;
            document.getElementById('equipmentAvailableCount').textContent = data.equipment.available ?? 0;
            document.getElementById('equipmentUnderMaintenanceCount').textContent = data.equipment.under_maintenance ?? 0;
            document.getElementById('equipmentDamagedCount').textContent = data.equipment.damaged ?? 0;
            document.getElementById('equipmentOutOfServiceCount').textContent = data.equipment.out_of_service ?? 0;
            const eq = window.dashboardCharts && window.dashboardCharts.equipment;
            if (eq) {
              eq.data.datasets[0].data = [
                data.equipment.assigned ?? 0,
                data.equipment.available ?? 0,
                data.equipment.under_maintenance ?? 0,
                data.equipment.damaged ?? 0,
                data.equipment.out_of_service ?? 0
              ];
              eq.update();
            }
          }

          if (data.service_requests) {
            document.getElementById('serviceRequestsCount').textContent = data.service_requests.total ?? '—';
            document.getElementById('servicePendingCount').textContent = data.service_requests.pending ?? '—';
            document.getElementById('serviceOnGoingCount').textContent = data.service_requests.ongoing ?? '—';
            document.getElementById('serviceCompletedCount').textContent = data.service_requests.completed ?? '—';
          }

          if (data.apprehended) {
            document.getElementById('apprehendedCount').textContent = ((data.apprehended.persons ?? 0) + (data.apprehended.vehicles ?? 0) + (data.apprehended.items ?? 0));
            document.getElementById('apprehendedPersonCount').textContent = data.apprehended.persons ?? '—';
            document.getElementById('apprehendedVehiclesCount').textContent = data.apprehended.vehicles ?? '—';
            document.getElementById('apprehendedItemsCount').textContent = data.apprehended.items ?? '—';
          }

          // User Roles Distribution: populate chart from API buckets or raw
          try {
            const urChart = window.dashboardCharts && window.dashboardCharts.userRoles;
            if (urChart) {
              // Only display these specific roles (ordered)
              const normalize = s => String(s || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
              const roleMap = {};
              if (data.user_roles && Object.keys(data.user_roles).length) {
                Object.keys(data.user_roles).forEach(k => {
                  roleMap[normalize(k)] = data.user_roles[k] || 0;
                });
              } else if (data.user_roles_raw && Object.keys(data.user_roles_raw).length) {
                Object.keys(data.user_roles_raw).forEach(k => {
                  roleMap[normalize(k)] = data.user_roles_raw[k] || 0;
                });
              }

              const desired = [
                { key: 'enforcement', label: 'Enforcement Officer' },
                { key: 'enforcer', label: 'Enforcer' },
                { key: 'property custodian', label: 'Property Custodian' },
                { key: 'office staff', label: 'Office Staff' }
              ];

              const labels = desired.map(d => d.label);
              const values = desired.map(d => roleMap[d.key] || 0);

              urChart.data.labels = labels;
              urChart.data.datasets[0].data = values;
              // ensure palette length covers our labels
              const palette = ['#28a745','#fd7e14','#6f42c1','#20c997','#007bff','#6c757d','#e83e8c','#ffc107'];
              urChart.data.datasets[0].backgroundColor = labels.map((_,i) => palette[i % palette.length]);
              urChart.update();
            }
          } catch (e) {
            console.error('user roles update failed', e);
          }

        }).catch(err => {
          console.error('Failed to load dashboard counts', err);
        });
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
      
      // Initialize charts and load live data after a short delay
      setTimeout(function() {
        if (typeof Chart !== 'undefined') {
          initializeDashboardCharts();
          // initial load
          loadDashboardData();
          // refresh every 30 seconds
          setInterval(loadDashboardData, 30000);
        }
      }, 500);
    });
  </script>
  </body>
</html>

