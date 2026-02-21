<?php
session_start();

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'enforcer') {
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
  <style>
    /* Modern Dashboard Styles */
    .main-content {
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
    }
    
    .stats-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      border: none;
      transition: all 0.3s ease;
      overflow: hidden;
      position: relative;
    }
    
    .stats-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .stats-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }
    
    .stats-card.primary::before {
      background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }
    
    .stats-card.success::before {
      background: linear-gradient(90deg, #56ab2f 0%, #a8e6cf 100%);
    }
    
    .stats-card.info::before {
      background: linear-gradient(90deg, #3498db 0%, #85c1e9 100%);
    }
    
    .stats-card.warning::before {
      background: linear-gradient(90deg, #f39c12 0%, #f7dc6f 100%);
    }
    
    .stats-number {
      font-size: 2.5rem;
      font-weight: 700;
      color: #2c3e50;
      margin: 0;
    }
    
    .stats-label {
      font-size: 0.9rem;
      font-weight: 600;
      color: #7f8c8d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 10px;
    }
    
    .stats-icon {
      font-size: 3rem;
      opacity: 0.1;
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
    }
    
    .activity-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      border: none;
      overflow: hidden;
    }
    
    .activity-card .card-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      color: white;
      border-radius: 15px 15px 0 0;
      padding: 1.25rem;
    }
    
    .activity-card .card-header h6 {
      margin: 0;
      font-weight: 600;
    }
    
    .table {
      margin: 0;
    }
    
    .table thead th {
      background: #f8f9fa;
      border: none;
      font-weight: 600;
      color: #495057;
      padding: 1rem;
    }
    
    .table td {
      padding: 1rem;
      border: none;
      border-bottom: 1px solid #e9ecef;
    }
    
    .badge {
      font-size: 0.75rem;
      padding: 0.4rem 0.8rem;
      border-radius: 20px;
    }
    
    .btn-outline-primary {
      border-radius: 20px;
      padding: 0.375rem 1rem;
      font-size: 0.8rem;
      font-weight: 500;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      border-radius: 25px;
      padding: 0.5rem 1.5rem;
      font-weight: 500;
    }
    
    .container-fluid {
      max-width: 1200px;
      margin: 0 auto;
    }
    
    /* Modern Card Styles */
    .modern-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      border: none;
      transition: all 0.3s ease;
      overflow: hidden;
      margin-bottom: 2rem;
    }
    
    .modern-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .modern-card-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 1.25rem;
      border-bottom: none;
    }
    
    .modern-card-header h5 {
      margin: 0;
      font-weight: 600;
      font-size: 1.1rem;
    }
    
    .modern-card-body {
      padding: 0;
    }
    
    /* Modern Table Styles */
    .modern-table {
      width: 100%;
      margin: 0;
      border-collapse: collapse;
    }
    
    .modern-table thead th {
      background: #f8f9fa;
      color: #495057;
      font-weight: 600;
      padding: 1rem;
      border: none;
      text-align: left;
    }
    
    .modern-table tbody td {
      padding: 1rem;
      border-bottom: 1px solid #e9ecef;
      vertical-align: middle;
    }
    
    .modern-table tbody tr:hover {
      background-color: #f8f9fa;
    }
    
    /* Status Badge Styles */
    .status-badge {
      padding: 0.375rem 0.75rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .status-badge.success {
      background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
      color: white;
    }
    
    .status-badge.warning {
      background: linear-gradient(135deg, #f39c12 0%, #f7dc6f 100%);
      color: white;
    }
    
    .status-badge.info {
      background: linear-gradient(135deg, #3498db 0%, #85c1e9 100%);
    }
    
    /* Modern Button Styles */
    .btn-modern {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      border: none;
      font-weight: 500;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s ease;
      font-size: 0.875rem;
    }
    
    .btn-modern.primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    
    .btn-modern.success {
      background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
      color: white;
    }
    
    .btn-modern:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
      color: white;
      text-decoration: none;
    }
    
    /* Activity and Location Items */
    .activity-item, .location-item, .request-type {
      display: flex;
      align-items: center;
    }
    
    /* Code styling */
    code {
      background: #f8f9fa;
      color: #e83e8c;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 0.85rem;
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
      <div class="sidebar-role">Enforcer</div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li class="active"><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li><a href="spot_reports.php"><i class="fa fa-file-text"></i> Spot Reports</a></li>
          <li><a href="service_requests.php"><i class="fa fa-cog"></i> Service Requests</a></li>
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
          <?php
          // Load DB and compute per-user stats
          require_once __DIR__ . '/../../../config/db.php';
          $sessionUid = $_SESSION['uid'] ?? null;
          $totalSpot = $approvedSpot = $pendingSpot = 0;
          $totalReq = $completedReq = $pendingReq = 0;
          try {
            if ($sessionUid) {
              $s = $pdo->prepare('SELECT COUNT(*) FROM spot_reports WHERE submitted_by = ?');
              $s->execute([$sessionUid]);
              $totalSpot = (int)$s->fetchColumn();

              $s = $pdo->prepare("SELECT COUNT(*) FROM spot_reports WHERE submitted_by = ? AND LOWER(TRIM(status)) = 'approved'");
              $s->execute([$sessionUid]);
              $approvedSpot = (int)$s->fetchColumn();

              $s = $pdo->prepare("SELECT COUNT(*) FROM spot_reports WHERE submitted_by = ? AND LOWER(TRIM(status)) IN ('pending','draft','open')");
              $s->execute([$sessionUid]);
              $pendingSpot = (int)$s->fetchColumn();

              $r = $pdo->prepare('SELECT COUNT(*) FROM service_requests WHERE created_by = ?');
              $r->execute([$sessionUid]);
              $totalReq = (int)$r->fetchColumn();

              $r = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE created_by = ? AND LOWER(TRIM(status)) = 'completed'");
              $r->execute([$sessionUid]);
              $completedReq = (int)$r->fetchColumn();

              $pendingReq = max(0, $totalReq - $completedReq);
            }
          } catch (Exception $e) {
            // leave counts at zero on error
          }
          ?>
          <!-- Spot Reports Statistics -->
          <div class="row mb-5 g-4">
            <div class="col-lg-4">
              <div class="stats-card primary h-100">
                <div class="p-4 position-relative">
                  <div class="stats-label">Total Spot Reports</div>
                  <div class="stats-number"><?php echo htmlspecialchars((string)$totalSpot); ?></div>
                  <i class="fa fa-file-text stats-icon"></i>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="stats-card success h-100">
                <div class="p-4 position-relative">
                  <div class="stats-label">Approved Spot Reports</div>
                  <div class="stats-number"><?php echo htmlspecialchars((string)$approvedSpot); ?></div>
                  <i class="fa fa-check-circle stats-icon"></i>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="stats-card warning h-100">
                <div class="p-4 position-relative">
                  <div class="stats-label">Pending Spot Reports</div>
                  <div class="stats-number"><?php echo htmlspecialchars((string)$pendingSpot); ?></div>
                  <i class="fa fa-clock stats-icon"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Service Requests Statistics -->
          <div class="row mb-5 g-4">
            <div class="col-lg-4">
              <div class="stats-card info h-100">
                <div class="p-4 position-relative">
                  <div class="stats-label">Total Service Requests</div>
                  <div class="stats-number"><?php echo htmlspecialchars((string)$totalReq); ?></div>
                  <i class="fa fa-cog stats-icon"></i>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="stats-card success h-100">
                <div class="p-4 position-relative">
                  <div class="stats-label">Completed Service Requests</div>
                  <div class="stats-number"><?php echo htmlspecialchars((string)$completedReq); ?></div>
                  <i class="fa fa-check stats-icon"></i>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="stats-card warning h-100">
                <div class="p-4 position-relative">
                  <div class="stats-label">Pending Service Requests</div>
                  <div class="stats-number"><?php echo htmlspecialchars((string)$pendingReq); ?></div>
                  <i class="fa fa-hourglass-half stats-icon"></i>
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

