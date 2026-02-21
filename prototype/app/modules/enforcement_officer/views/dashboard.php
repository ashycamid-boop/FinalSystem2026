<?php session_start(); ?>
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
      <div class="sidebar-role"><?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'Enforcement Officer'; ?></div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li class="active"><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li><a href="spot_reports.php"><i class="fa fa-file-text"></i> Spot Reports</a></li>
          <li><a href="case_management.php"><i class="fa fa-briefcase"></i> Case Management</a></li>
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
          <div class="topbar-title">Dashboard</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <div class="container-fluid p-4">
          <!-- Top Statistics Cards -->
          <div class="row mb-4">
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
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #6f42c1;"></div>
                      <small class="text-muted">Pending Review: <span id="casePendingReview" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #ffc107;"></div>
                      <small class="text-muted">For Filing: <span id="caseForFiling" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #11998e;"></div>
                      <small class="text-muted">Filed in Court: <span id="caseFiledInCourt" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #17a2b8;"></div>
                      <small class="text-muted">Ongoing Trial: <span id="caseOnGoing" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #6c757d;"></div>
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
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #adb5bd;"></div>
                      <small class="text-muted">On Hold: <span id="caseOnHold" class="fw-bold text-dark">—</span></small>
                    </div>
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle me-2" style="width: 8px; height: 8px; background-color: #f093fb;"></div>
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
    // persistent chart instances for live updates
    let spotChart = null;
    let caseChart = null;
    
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

    // Initialize or update dashboard charts with server-provided data
    function initializeDashboardCharts(data) {
      const ctx1 = document.getElementById('spotReportsChart');
      const ctx2 = document.getElementById('caseStatusChart');

      function palette(n) {
        const base = ['#11998e','#38ef7d','#f093fb','#f5576c','#ffc107','#17a2b8','#6f42c1','#dc3545','#6c757d'];
        const out = [];
        for (let i=0;i<n;i++) out.push(base[i % base.length]);
        return out;
      }

      // Spot reports bar chart
      if (ctx1) {
        const spotBy = (data && data.spot_reports && data.spot_reports.by_status) ? data.spot_reports.by_status : {};
        const labels = Object.keys(spotBy).map(k => k.charAt(0).toUpperCase() + k.slice(1));
        const values = Object.keys(spotBy).map(k => spotBy[k]);

        if (spotChart) {
          spotChart.data.labels = labels;
          spotChart.data.datasets[0].data = values;
          spotChart.data.datasets[0].backgroundColor = palette(values.length);
          spotChart.update();
        } else {
          spotChart = new Chart(ctx1, {
            type: 'bar',
            data: {
              labels: labels,
              datasets: [{ label: 'Spot Reports', data: values, backgroundColor: palette(values.length) }]
            },
            options: { responsive: true, maintainAspectRatio: false }
          });
        }
      }

      // Case statuses doughnut chart
      if (ctx2) {
        const caseBy = (data && data.case_statuses && data.case_statuses.by_status) ? data.case_statuses.by_status : {};
        // Aggregate and normalize case status keys into canonical buckets so the chart
        // always shows the main lifecycle categories even if DB keys vary.
        const agg = {
          'Under Investigation': 0,
          'Pending Review': 0,
          'For Filing': 0,
          'Filed in Court': 0,
          'Ongoing Trial': 0,
          'Resolved': 0,
          'Dismissed': 0,
          'Archived': 0,
          'On Hold': 0,
          'Under Appeal': 0
        };
        const normalizeKey = s => ('' + s).toLowerCase();
        Object.keys(caseBy).forEach(k => {
          const v = caseBy[k] || 0;
          const n = normalizeKey(k);
          if (n === '' || n.includes('under') && (n.includes('invest') || n.includes('investigation') || !n.includes('review'))) {
            agg['Under Investigation'] += v;
          } else if (n.includes('pending')) {
            agg['Pending Review'] += v;
          } else if (n.includes('for filing') || n.includes('for-filing') || n.includes('forfiling')) {
            agg['For Filing'] += v;
          } else if (n.includes('filed') || n.includes('court')) {
            agg['Filed in Court'] += v;
          } else if (n.includes('ongoing') || n.includes('trial')) {
            agg['Ongoing Trial'] += v;
          } else if (n.includes('resolv')) {
            agg['Resolved'] += v;
          } else if (n.includes('dismiss')) {
            agg['Dismissed'] += v;
          } else if (n.includes('archiv')) {
            agg['Archived'] += v;
          } else if (n.includes('hold')) {
            agg['On Hold'] += v;
          } else if (n.includes('appeal')) {
            agg['Under Appeal'] += v;
          } else {
            // fallback into Under Investigation
            agg['Under Investigation'] += v;
          }
        });

        // Always show the canonical case status buckets (include zeros)
        const labels = Object.keys(agg);
        const values = labels.map(k => agg[k]);

        if (caseChart) {
          caseChart.data.labels = labels;
          caseChart.data.datasets[0].data = values;
          caseChart.data.datasets[0].backgroundColor = palette(values.length);
          caseChart.update();
        } else {
          caseChart = new Chart(ctx2, {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: values, backgroundColor: palette(values.length) }] },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: { position: 'bottom' },
                datalabels: {
                  display: true,
                  color: '#fff',
                  font: { weight: 'bold', size: 12 },
                  formatter: (value, context) => {
                    const total = context.dataset.data.reduce((sum, val) => sum + val, 0) || 0;
                    const percentage = total ? ((value / total) * 100).toFixed(1) : '0.0';
                    return `${value}\n${percentage}%`;
                  }
                }
              }
            }
          });
        }
      }
    }

    // Fetch counts from server and populate UI
    async function fetchDashboardCounts(url = 'actions/get_dashboard_counts.php') {
      try {
        console.log('Requesting dashboard counts from', url);
        const res = await fetch(url, { credentials: 'same-origin' });
        if (!res.ok) {
          const txt = await res.text().catch(() => '[no body]');
          console.error('Counts endpoint returned non-OK', res.status, txt);
          return;
        }

        let json;
        try {
          json = await res.json();
        } catch (e) {
          const txt = await res.text().catch(() => '[no body]');
          console.error('Failed to parse JSON from counts endpoint:', txt);
          return;
        }

        if (!json || json.ok === false) {
          console.error('Counts endpoint returned error object', json);
          return;
        }

        const spot = json.spot_reports || { total: 0, by_status: {} };
        const cases = json.case_statuses || { total: 0, by_status: {} };
        const app = json.apprehended || { persons: 0, vehicles: 0, items: 0, total: 0 };

        // helper to get status-insensitive value (normalize hyphens/underscores/spaces)
        function getStatus(map, name) {
          if (!map) return 0;
          const norm = (s) => ('' + s).toLowerCase().replace(/[_\s\-]+/g, '');
          const target = norm(name);
          for (const k of Object.keys(map)) {
            if (norm(k) === target) return map[k];
          }
          return 0;
        }

        document.getElementById('spotReportsCount').textContent = spot.total || 0;
        document.getElementById('spotApprovedCount').textContent = getStatus(spot.by_status, 'approved') || 0;
        document.getElementById('spotPendingCount').textContent = getStatus(spot.by_status, 'pending') || 0;
        document.getElementById('spotRejectedCount').textContent = getStatus(spot.by_status, 'rejected') || 0;

        const caseTotal = cases.total || 0;
        document.getElementById('caseManagementCount').textContent = caseTotal;
        document.getElementById('caseUnderInvestigation').textContent = getStatus(cases.by_status, 'under investigation') || getStatus(cases.by_status, 'under_investigation') || getStatus(cases.by_status, 'investigation') || 0;
        document.getElementById('casePendingReview').textContent = getStatus(cases.by_status, 'pending review') || getStatus(cases.by_status, 'pending_review') || getStatus(cases.by_status, 'pending') || 0;
        document.getElementById('caseForFiling').textContent = getStatus(cases.by_status, 'for filing') || getStatus(cases.by_status, 'for_filing') || 0;
        document.getElementById('caseFiledInCourt').textContent = getStatus(cases.by_status, 'filed in court') || getStatus(cases.by_status, 'filed') || getStatus(cases.by_status, 'court') || 0;
        document.getElementById('caseOnGoing').textContent =
          getStatus(cases.by_status, 'ongoing trial') ||
          getStatus(cases.by_status, 'ongoing') ||
          getStatus(cases.by_status, 'on going') || 0;
        document.getElementById('caseResolved').textContent = getStatus(cases.by_status, 'resolved') || 0;
        document.getElementById('caseDismissed').textContent = getStatus(cases.by_status, 'dismissed') || 0;
        document.getElementById('caseArchived').textContent = getStatus(cases.by_status, 'archived') || getStatus(cases.by_status, 'archive') || 0;
        document.getElementById('caseOnHold').textContent = getStatus(cases.by_status, 'on hold') || getStatus(cases.by_status, 'on_hold') || 0;
        document.getElementById('caseUnderAppeal').textContent = getStatus(cases.by_status, 'under appeal') || getStatus(cases.by_status, 'appeal') || 0;

        document.getElementById('apprehendedCount').textContent = app.total || 0;
        document.getElementById('apprehendedPersonCount').textContent = app.persons || 0;
        document.getElementById('apprehendedVehiclesCount').textContent = app.vehicles || 0;
        document.getElementById('apprehendedItemsCount').textContent = app.items || 0;

        // Initialize charts with fetched data
        console.log('Dashboard counts received', json);
        initializeDashboardCharts(json);
      } catch (err) {
        console.error('Failed to fetch dashboard counts', err);
      }
    }
    
    // Initialize everything when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
      initializeProfileDropdown();
      
      // Debug: Check if Service Desk elements exist
      setTimeout(function() {
        const serviceDeskToggle = document.getElementById('serviceDeskToggle');
        const serviceDeskMenu = document.getElementById('serviceDeskMenu');
        
        console.log('=== DASHBOARD DEBUG ===');
        console.log('Service Desk Toggle found:', !!serviceDeskToggle);
        console.log('Service Desk Menu found:', !!serviceDeskMenu);
        
        if (serviceDeskToggle && serviceDeskMenu) {
          console.log('Adding manual click handler for dashboard...');
          
          // Force show dropdown with basic display toggle for testing
          serviceDeskToggle.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Service Desk CLICKED on dashboard!');
            
            // Simple display toggle test
            const currentDisplay = serviceDeskMenu.style.display;
            
            if (currentDisplay === 'block') {
              serviceDeskMenu.style.display = 'none';
              serviceDeskToggle.classList.remove('active');
              console.log('Hiding with display none...');
            } else {
              serviceDeskMenu.style.display = 'block';
              serviceDeskMenu.style.maxHeight = '300px';
              serviceDeskMenu.style.opacity = '1';
              serviceDeskMenu.style.padding = '5px 0';
              serviceDeskToggle.classList.add('active');
              console.log('Showing with display block...');
            }
            
            // Also try the class method
            serviceDeskMenu.classList.toggle('show');
            
            // Handle arrow
            const arrow = serviceDeskToggle.querySelector('.dropdown-arrow');
            if (arrow) {
              arrow.classList.toggle('rotated');
            }
          });
        } else {
          console.log('Service Desk elements NOT FOUND!');
        }
      }, 200);
      
      // Fetch real dashboard counts and initialize charts
      setTimeout(function() {
        if (typeof Chart !== 'undefined') {
          console.log('Fetching dashboard counts...');
          const countsUrl = new URL('actions/get_dashboard_counts.php', window.location.href).href;
          fetchDashboardCounts(countsUrl);
        }
      }, 500);
    });
  </script>
  </body>
</html>

