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
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
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
      <div class="sidebar-role">Property Custodian</div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li class="active"><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
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
        </ul>
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
            <!-- Total Equipment -->
            <div class="col-lg-3 col-md-6 mb-4">
              <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 15px !important;">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 11px; letter-spacing: 1px;">TOTAL EQUIPMENT</div>
                      <h2 class="mb-0 fw-bold" style="color: #2c3e50;"><span id="totalEquipmentCount">0</span></h2>
                    </div>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(135deg, #4285f4, #34a853);">
                      <i class="fa fa-cogs text-white" style="font-size: 20px;"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Assigned Devices -->
            <div class="col-lg-3 col-md-6 mb-4">
              <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 15px !important;">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 11px; letter-spacing: 1px;">ASSIGNED DEVICES</div>
                      <h2 class="mb-0 fw-bold" style="color: #2c3e50;"><span id="assignedDevicesCount">0</span></h2>
                    </div>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(135deg, #00d4aa, #00b894);">
                      <i class="fa fa-check text-white" style="font-size: 20px;"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pending Requests -->
            <div class="col-lg-3 col-md-6 mb-4">
              <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 15px !important;">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 11px; letter-spacing: 1px;">PENDING REQUESTS</div>
                      <h2 class="mb-0 fw-bold" style="color: #2c3e50;"><span id="pendingRequestsCount">0</span></h2>
                    </div>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(135deg, #ffa726, #ff9800);">
                      <i class="fa fa-clock text-white" style="font-size: 20px;"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Available Equipment -->
            <div class="col-lg-3 col-md-6 mb-4">
              <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 15px !important;">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 11px; letter-spacing: 1px;">AVAILABLE EQUIPMENT</div>
                      <h2 class="mb-0 fw-bold" style="color: #2c3e50;"><span id="availableEquipmentCount">0</span></h2>
                    </div>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(135deg, #9c27b0, #8e24aa);">
                      <i class="fa fa-box text-white" style="font-size: 20px;"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts Section -->
          <div class="row">
            <!-- Current Requests by Department -->
            <div class="col-lg-7 mb-4">
              <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px !important;">
                <div class="card-header bg-white border-0 pb-0">
                  <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                      <i class="fa fa-chart-bar text-muted me-2"></i>
                      <h6 class="mb-0 fw-bold">Current Requests by Department</h6>
                    </div>
                    <small class="text-muted">As of Today</small>
                  </div>
                </div>
                <div class="card-body">
                  <div style="height: 350px; position: relative;">
                    <canvas id="departmentRequestsChart"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- Current Equipment Status -->
            <div class="col-lg-5 mb-4">
              <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px !important;">
                <div class="card-header bg-white border-0 pb-0">
                  <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                      <i class="fa fa-chart-pie text-muted me-2"></i>
                      <h6 class="mb-0 fw-bold">Current Equipment Status</h6>
                    </div>
                    <small class="text-muted">Real-time Status</small>
                  </div>
                </div>
                <div class="card-body">
                  <div class="row align-items-center">
                    <!-- Pie Chart -->
                    <div class="col-7">
                      <div style="height: 250px; position: relative;">
                        <canvas id="equipmentStatusChart"></canvas>
                      </div>
                    </div>
                    <!-- Legend -->
                    <div class="col-5">
                      <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle me-3" style="width: 14px; height: 14px; background-color: #00d4aa;"></div>
                          <div>
                            <div class="fw-semibold text-dark" style="font-size: 14px;">Available</div>
                            <small class="text-muted"><span id="legendAvailableCount">0</span> units</small>
                          </div>
                        </div>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle me-3" style="width: 14px; height: 14px; background-color: #4285f4;"></div>
                          <div>
                            <div class="fw-semibold text-dark" style="font-size: 14px;">Assigned</div>
                            <small class="text-muted"><span id="legendAssignedCount">0</span> units</small>
                          </div>
                        </div>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle me-3" style="width: 14px; height: 14px; background-color: #ffa726;"></div>
                          <div>
                            <div class="fw-semibold text-dark" style="font-size: 14px;">Under Maintenance</div>
                            <small class="text-muted"><span id="legendUnderMaintenanceCount">0</span> units</small>
                          </div>
                        </div>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle me-3" style="width: 14px; height: 14px; background-color: #f44336;"></div>
                          <div>
                            <div class="fw-semibold text-dark" style="font-size: 14px;">Damaged</div>
                            <small class="text-muted"><span id="legendDamagedCount">0</span> units</small>
                          </div>
                        </div>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle me-3" style="width: 14px; height: 14px; background-color: #9e9e9e;"></div>
                          <div>
                            <div class="fw-semibold text-dark" style="font-size: 14px;">Out of Service</div>
                            <small class="text-muted"><span id="legendOutOfServiceCount">0</span> units</small>
                          </div>
                        </div>
                      </div>
                    </div>
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
  
  <!-- Equipment Service (used to fetch equipment list for counts) -->
  <script src="../../../../public/assets/js/admin/equipment-service.js"></script>
  
  <!-- Dashboard functionality -->
  <script>
    // Register Chart.js plugins
    if (typeof Chart !== 'undefined' && Chart && Chart.register) {
      try { Chart.register(ChartDataLabels); } catch(e) {}
    }

    // Fetch equipment list via EquipmentService and populate counts + status chart
    async function fetchAndPopulateCounts() {
      try {
        let items = [];
        if (typeof EquipmentService !== 'undefined' && EquipmentService && EquipmentService.getAll) {
          const resp = await EquipmentService.getAll();
          // EquipmentService.getAll returns { data: [...] } or similar
          if (Array.isArray(resp)) items = resp;
          else if (resp && Array.isArray(resp.data)) items = resp.data;
          else if (resp && Array.isArray(resp.results)) items = resp.results;
        } else {
          // fallback: try fetching the API directly
          const r = await fetch('../../../../app/api/equipment/equipment_api.php?action=getAll');
          const json = await r.json();
          if (Array.isArray(json)) items = json; else if (json && Array.isArray(json.data)) items = json.data;
        }

        const total = items.length;
        const counts = { available: 0, assigned: 0, maintenance: 0, damaged: 0, out_of_service: 0 };
        for (const it of items) {
          const s = (it.status || '').toString().toLowerCase();
          if (s === 'available' || s === 'available ' ) counts.available++;
          else if (s === 'in use' || s === 'assigned' || s === 'assigned ') counts.assigned++;
          else if (s.includes('maintenance') || s === 'under maintenance') counts.maintenance++;
          else if (s === 'damaged') counts.damaged++;
          else if (s.includes('out') || s.includes('service')) counts.out_of_service++;
        }

        // Update DOM placeholders
        const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        setText('totalEquipmentCount', total);
        setText('assignedDevicesCount', counts.assigned);
        setText('availableEquipmentCount', counts.available);
        setText('pendingRequestsCount', 0);
        setText('legendAvailableCount', counts.available);
        setText('legendAssignedCount', counts.assigned);
        setText('legendUnderMaintenanceCount', counts.maintenance);
        setText('legendDamagedCount', counts.damaged);
        setText('legendOutOfServiceCount', counts.out_of_service);

        // Update equipmentStatusChart if present
        if (window.dashboardCharts && window.dashboardCharts.equipmentStatusChart) {
          const chart = window.dashboardCharts.equipmentStatusChart;
          chart.data.datasets[0].data = [counts.available, counts.assigned, counts.maintenance, counts.damaged, counts.out_of_service];
          chart.update();
        }

        // Fetch service request counts (pending/ongoing/completed) and set pendingRequestsCount
        try {
          const sc = await fetch('../../../../app/api/service_counts.php');
          const scj = await sc.json();
          if (scj && typeof scj.new_requests !== 'undefined') {
            setText('pendingRequestsCount', scj.new_requests);
          }
        } catch (e) {
          // ignore service counts errors
        }
      } catch (err) {
        console.warn('Failed to fetch equipment counts:', err);
      }
    }

    // Fetch requests grouped by department and populate departmentRequestsChart
    async function fetchAndPopulateDepartmentChart() {
      try {
        const resp = await fetch('../../../../app/api/service_requests_by_department.php');
        const json = await resp.json();
        if (!json || !json.success || !Array.isArray(json.data)) return;
        const data = json.data.slice(0, 12); // limit to top 12
        const labels = data.map(d => d.label);
        const counts = data.map(d => d.count);
        const colors = labels.map((_, i) => {
          // generate pleasant palette
          const palette = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#6f42c1','#20c997','#fd7e14','#6610f2','#20a8d8','#f3a712'];
          return palette[i % palette.length];
        });

        if (window.dashboardCharts && window.dashboardCharts.departmentRequestsChart) {
          const ch = window.dashboardCharts.departmentRequestsChart;
          ch.data.labels = labels;
          ch.data.datasets[0].data = counts;
          ch.data.datasets[0].backgroundColor = colors;
          ch.update();
        }
      } catch (e) {
        console.warn('Failed to fetch department requests:', e);
      }
    }

    // Initialize profile dropdown
    function initializeProfileDropdown() {
      const profileCard = document.getElementById('profileCard');
      const profileDropdown = document.getElementById('profileDropdown');
      if (!profileCard || !profileDropdown) return;
      let dropdownOpen = false;
      function toggleDropdown() { dropdownOpen = !dropdownOpen; profileDropdown.style.display = dropdownOpen ? 'flex' : 'none'; }
      profileCard.addEventListener('click', function(e) { toggleDropdown(); e.stopPropagation(); });
      document.addEventListener('click', function(e) { if (!profileCard.contains(e.target)) { dropdownOpen = false; profileDropdown.style.display = 'none'; } });
      document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && dropdownOpen) { dropdownOpen = false; profileDropdown.style.display = 'none'; } });
    }

    // Initialize dashboard charts (no demo/static data)
    // Exposes chart instances on `window.dashboardCharts` for later updates
    function initializeDashboardCharts() {
      // Department Requests Bar Chart (no static demo data)
      const ctx1 = document.getElementById('departmentRequestsChart');
      if (ctx1 && typeof Chart !== 'undefined') {
        const deptChart = new Chart(ctx1, {
          type: 'bar',
          data: { labels: [], datasets: [{ label: 'Equipment Requests', data: [], backgroundColor: [], borderRadius: 4, borderSkipped: false }] },
          options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, datalabels: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } }
        });
        window.dashboardCharts = window.dashboardCharts || {};
        window.dashboardCharts.departmentRequestsChart = deptChart;
      }

      // Equipment Status Doughnut - start with zeros
      const ctx2 = document.getElementById('equipmentStatusChart');
      if (ctx2 && typeof Chart !== 'undefined') {
        const labels = ['Available', 'Assigned', 'Under Maintenance', 'Damaged', 'Out of Service'];
        const data = [0,0,0,0,0];
        const statusChart = new Chart(ctx2, {
          type: 'doughnut',
          data: { labels: labels, datasets: [{ data: data, backgroundColor: ['#00d4aa', '#4285f4', '#ffa726', '#f44336', '#9e9e9e'], borderWidth: 3, borderColor: '#ffffff', hoverOffset: 6 }] },
          options: { responsive: true, maintainAspectRatio: false, cutout: '40%', plugins: { legend: { display: false }, datalabels: { display: true, color: '#ffffff', font: { weight: 'bold', size: 12 }, formatter: (value, context) => { const total = context.dataset.data.reduce((sum, val) => sum + val, 0)||1; const percentage = ((value / total) * 100).toFixed(0); return `${percentage}%`; }, anchor: 'center', align: 'center' } } }
        });
        window.dashboardCharts = window.dashboardCharts || {};
        window.dashboardCharts.equipmentStatusChart = statusChart;
      }

      // Update counts placeholders (leave zeros - expected to be populated by backend calls later)
      const setZero = (id) => { const el = document.getElementById(id); if (el) el.textContent = 0; };
      setZero('totalEquipmentCount');
      setZero('assignedDevicesCount');
      setZero('pendingRequestsCount');
      setZero('availableEquipmentCount');
      setZero('legendAvailableCount');
      setZero('legendAssignedCount');
      setZero('legendUnderMaintenanceCount');
      setZero('legendDamagedCount');
      setZero('legendOutOfServiceCount');
    }

    // Initialize everything when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
      initializeProfileDropdown();
      // Service Desk dropdown
      setTimeout(function() {
        const serviceDeskToggle = document.getElementById('serviceDeskToggle');
        const serviceDeskMenu = document.getElementById('serviceDeskMenu');
        if (serviceDeskToggle && serviceDeskMenu) {
          serviceDeskToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const currentDisplay = serviceDeskMenu.style.display;
            if (currentDisplay === 'block') { serviceDeskMenu.style.display = 'none'; serviceDeskToggle.classList.remove('active'); }
            else { serviceDeskMenu.style.display = 'block'; serviceDeskMenu.style.maxHeight = '300px'; serviceDeskMenu.style.opacity = '1'; serviceDeskMenu.style.padding = '5px 0'; serviceDeskToggle.classList.add('active'); }
            serviceDeskMenu.classList.toggle('show');
            const arrow = serviceDeskToggle.querySelector('.dropdown-arrow'); if (arrow) { arrow.classList.toggle('rotated'); }
          });
        }
      }, 200);

      // Initialize charts after a short delay and populate counts
      setTimeout(async function() {
        if (typeof Chart !== 'undefined') initializeDashboardCharts();
        // populate counts and department chart once charts are initialized
        try {
          await fetchAndPopulateCounts();
          await fetchAndPopulateDepartmentChart();
        } catch(e) { /* ignore */ }
      }, 300);
    });
  </script>
</body>
</html>

