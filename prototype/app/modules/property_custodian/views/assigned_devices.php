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
  <title>Assigned Devices</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Admin common styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <!-- Assigned Devices specific styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/assigned-devices.css">
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
          <li class="active"><a href="assignments.php"><i class="fa fa-tasks"></i> Assignments</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" id="serviceDeskToggle" data-target="serviceDeskMenu">
              <i class="fa fa-headset"></i> Service Desk 
              <i class="fa fa-chevron-down dropdown-arrow"></i>
            </a>
            <ul class="dropdown-menu" id="serviceDeskMenu">
              <li><a href="new_requests.php">New Requests <span class="badge">2</span></a></li>
              <li><a href="ongoing_scheduled.php">Ongoing / Scheduled <span class="badge badge-blue">2</span></a></li>
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
          <div class="topbar-title">Assigned Devices</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        
        <div class="container-fluid">
          
          <!-- Back and Print Buttons -->
          <div class="row mb-3">
            <div class="col-6">
              <button class="btn btn-secondary">
                <i class="fa fa-arrow-left me-2"></i>Back
              </button>
            </div>
            <div class="col-6 text-end">
              <button class="btn btn-outline-dark" onclick="printForm()">
                <i class="fa fa-print me-2"></i>Print
              </button>
            </div>
          </div>

          <!-- Main Content Card -->
          <div class="card">
            <div class="card-body">
              <!-- Header with Logos -->
              <div class="row align-items-center mb-4 header-logos">
                <div class="col-md-2">
                  <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo" style="width: 100px; height: 100px; object-fit: contain;">
                </div>
                <div class="col-md-8 text-center">
                  <h6 class="mb-0"><strong>Department of Environment and Natural Resources</strong></h6>
                  <p class="mb-0"><strong>Kagawaran ng Kapaligiran at Likas na Yaman</strong></p>
                  <p class="mb-0"><strong>Caraga Region</strong></p>
                  <p class="mb-0"><strong>CENRO Nasipit, Agusan del Norte</strong></p>
                </div>
                <div class="col-md-2 text-end">
                  <img src="../../../../public/assets/images/bagong-pilipinas-logo.png" alt="Bagong Pilipinas Logo" style="width: 100px; height: 100px; object-fit: contain;">
                </div>
              </div>

              <hr>

              <!-- Assigned Devices Title -->
              <h5 class="text-center mb-4">Assigned Devices</h5>

              <?php
              $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 2;
              
              // Sample user data
              $users = [
                1 => ['name' => 'Ashmen S. Camid', 'role' => 'Enforcement Officer', 'office' => 'Monitoring and Evaluation Unit', 'email' => 'ashycamid@gmail.com', 'mobile' => '09569926138'],
                2 => ['name' => 'Rich Ian Balaldo', 'role' => 'Enforcer', 'office' => 'Buenavista ENR Monitoring', 'email' => 'richbalaldo@gmail.com', 'mobile' => '09569926138'],
                3 => ['name' => 'Joel Caluya', 'role' => 'Property Custodian', 'office' => 'Support Unit', 'email' => 'joelcaluya@gmail.com', 'mobile' => '09569926138'],
                4 => ['name' => 'Joryn Cagulangan', 'role' => 'Office Staff', 'office' => 'Licensing and Permitting unit', 'email' => 'joryn@gmail.com', 'mobile' => '09569926138'],
                5 => ['name' => 'Ivan Tadena', 'role' => 'Enforcement Officer', 'office' => 'Monitoring and Evaluation Unit', 'email' => 'ivantadena@gmail.com', 'mobile' => '09569926138']
              ];
              
              $user = $users[$user_id] ?? $users[2];
              ?>

              <!-- User Profile Section -->
              <div class="row mb-4">
                <div class="col-md-2">
                  <img src="../../../../public/assets/images/Rich Ian.png" alt="User Photo" class="img-fluid rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                </div>
                <div class="col-md-10">
                  <table class="table table-bordered user-info-table">
                    <tbody>
                      <tr>
                        <td class="info-label">Full Name</td>
                        <td><?= $user['name'] ?></td>
                        <td class="info-label">Email</td>
                        <td><a href="mailto:<?= $user['email'] ?>" class="text-decoration-underline"><?= $user['email'] ?></a></td>
                        <td class="info-label">Mobile Number</td>
                        <td>09894392438</td>
                      </tr>
                      <tr>
                        <td class="info-label">Role</td>
                        <td><?= $user['role'] ?></td>
                        <td class="info-label">Office/Unit</td>
                        <td><?= $user['office'] ?></td>
                        <td class="info-label">Number of Devices</td>
                        <td>3</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Assigned Devices Table -->
              <h6 class="mb-3" style="color: #999;">Assigned Devices</h6>
              <div class="table-responsive">
                <table class="table table-bordered devices-table">
                  <thead class="table-light">
                    <tr>
                      <th>Asset ID</th>
                      <th>Property No.</th>
                      <th>Category</th>
                      <th>Brand</th>
                      <th>Model</th>
                      <th>Serial Number</th>
                      <th>Date Acquired</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>1</td>
                      <td>UPS-2023-001</td>
                      <td>UPS</td>
                      <td>APC</td>
                      <td>Smart-UPS 1500VA</td>
                      <td>SN-APC1500000</td>
                      <td>2023</td>
                      <td>In Use</td>
                    </tr>
                    <tr>
                      <td>4</td>
                      <td>PRN-2024-003</td>
                      <td>Printers</td>
                      <td>HP</td>
                      <td>LaserJet Pro</td>
                      <td>SN-HP2024003</td>
                      <td>2024</td>
                      <td>In Use</td>
                    </tr>
                    <tr>
                      <td>7</td>
                      <td>DRN-2024-006</td>
                      <td>Drones</td>
                      <td>DJI</td>
                      <td>Mavic 3 Enterprise</td>
                      <td>DJI-M3E-006</td>
                      <td>2024</td>
                      <td>In Use</td>
                    </tr>
                  </tbody>
                </table>
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
  <!-- Assigned Devices JavaScript -->
  <script src="../../../../public/assets/js/admin/assigned-devices.js"></script>
  
  <script>
    function printForm() {
      // Hide buttons and sidebar for printing
      const sidebar = document.querySelector('.sidebar');
      const topbar = document.querySelector('.topbar');
      const backButton = document.querySelector('.btn-secondary');
      const printButton = document.querySelector('.btn-outline-dark');
      const controls = document.querySelectorAll('.row.mb-4, .row.mb-3');
      
      // Store original display values
      const originalDisplays = {
        sidebar: sidebar.style.display,
        topbar: topbar.style.display,
        backButton: backButton.style.display,
        printButton: printButton.style.display
      };
      
      // Hide elements for printing
      sidebar.style.display = 'none';
      topbar.style.display = 'none';
      backButton.style.display = 'none';
      printButton.style.display = 'none';
      controls.forEach(el => el.style.display = 'none');
      
      // Adjust main content for printing
      const main = document.querySelector('.main');
      main.style.marginLeft = '0';
      main.style.width = '100%';
      
      // Print
      window.print();
      
      // Restore original display values after printing
      setTimeout(() => {
        sidebar.style.display = originalDisplays.sidebar;
        topbar.style.display = originalDisplays.topbar;
        backButton.style.display = originalDisplays.backButton;
        printButton.style.display = originalDisplays.printButton;
        controls.forEach(el => el.style.display = '');
        main.style.marginLeft = '';
        main.style.width = '';
      }, 100);
    }
  </script>
</body>
</html>