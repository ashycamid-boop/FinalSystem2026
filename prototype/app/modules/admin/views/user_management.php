<?php
session_start();
require_once __DIR__ . '/../../../../app/config/db.php';

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: /prototype/index.php');
    exit;
}
// Fetch all users from database
$users = [];
try {
  // Exclude Admin role from listing so administrator accounts are not shown here
  // include profile_picture so avatar can be displayed in the table
  $stmt = $pdo->query("SELECT id, email, full_name, role, status, contact_number, office_unit, created_at, profile_picture FROM users WHERE role <> 'Admin' ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    // Log error silently if needed
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Management</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Admin common styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <!-- User Management specific styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/user-management.css">
  <style>
    /* Ensure full name stays on one line; shrink font when needed */
    .full-name-cell {
      white-space: nowrap;
      overflow: hidden;
    }
    .full-name-cell img {
      vertical-align: middle;
      width: 36px;
      height: 36px;
      object-fit: cover;
      border-radius: 50%;
      border: 2px solid rgba(0,0,0,0.06);
      box-shadow: 0 1px 2px rgba(0,0,0,0.06);
      display: inline-block;
      vertical-align: middle;
      margin-right: 8px;
    }
    .full-name-cell span {
      display: inline-block;
      vertical-align: middle;
      max-width: calc(100% - 48px); /* account for avatar + gap */
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis; /* fallback if JS doesn't shrink enough */
      font-size: 14px;
      line-height: 1.1;
    }
  </style>
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
      <div class="sidebar-role">Administrator</div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li class="active"><a href="user_management.php"><i class="fa fa-users"></i> User Management</a></li>
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
              <li><a href="new_requests.php">New Requests <span class="badge">2</span></a></li>
              <li><a href="ongoing_scheduled.php">Ongoing / Scheduled <span class="badge badge-blue">2</span></a></li>
              <li><a href="completed.php">Completed</a></li>
              <li><a href="all_requests.php">All Requests</a></li>
            </ul>
          </li>
          <li><a href="statistical_report.php"><i class="fa fa-chart-bar"></i> Statistical Report</a></li>
        </ul>
      </nav>
    </nav>
    <!-- Main -->
    <div class="main">
      <div class="topbar">
        <div class="topbar-card">
          <div class="topbar-title">User Management</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <!-- User Management Content -->
        <div class="container-fluid p-4">
          <!-- Search and Filter Section -->
          <div class="row g-3 mb-4">
            <div class="col-12 col-lg-4">
              <div class="input-group">
                <input type="text" class="form-control" placeholder="Search users..." id="searchInput">
                <span class="input-group-text"><i class="fa fa-search"></i></span>
              </div>
            </div>
            <div class="col-12 col-lg-8">
              <div class="d-flex flex-column flex-sm-row gap-2 align-items-start align-items-sm-center">
                <span class="me-2 fw-bold d-none d-sm-inline">Filters:</span>
                <div class="d-flex flex-wrap gap-2 flex-grow-1">
                  <button class="btn btn-primary btn-sm filter-btn active" data-role="all">All</button>
                  <button class="btn btn-outline-secondary btn-sm filter-btn d-none d-md-inline-block" data-role="Enforcement Officer">Enforcement Officer</button>
                  <button class="btn btn-outline-secondary btn-sm filter-btn d-md-none" data-role="Enforcement Officer">Officer</button>
                  <button class="btn btn-outline-secondary btn-sm filter-btn" data-role="Enforcer">Enforcer</button>
                  <button class="btn btn-outline-secondary btn-sm filter-btn d-none d-lg-inline-block" data-role="Property Custodian">Property Custodian</button>
                  <button class="btn btn-outline-secondary btn-sm filter-btn d-lg-none" data-role="Property Custodian">Custodian</button>
                  <button class="btn btn-outline-secondary btn-sm filter-btn d-none d-md-inline-block" data-role="Office Staff">Office Staff</button>
                  <button class="btn btn-outline-secondary btn-sm filter-btn d-md-none" data-role="Office Staff">Staff</button>
                </div>
                <button class="btn btn-success btn-sm align-self-end align-self-sm-center" onclick="window.location.href='add_user.php'">
                  <i class="fa fa-plus me-1"></i><span class="d-none d-sm-inline">Add User</span><span class="d-sm-none">Add</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Users Table -->
          <div class="card shadow-sm">
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0" id="usersTable">
                  <thead class="table-light">
                    <tr>
                      <th class="d-none d-md-table-cell">ID</th>
                      <th>FULL NAME</th>
                      <th class="d-none d-lg-table-cell">EMAIL</th>
                      <th>ROLE</th>
                      <th class="d-none d-xl-table-cell">OFFICE/UNIT</th>
                      <th class="d-none d-lg-table-cell">CONTACT NUMBER</th>
                      <th>STATUS</th>
                      <th class="d-none d-md-table-cell">CREATED AT</th>
                      <th>ACTIONS</th>
                    </tr>
                  </thead>
                  <tbody id="usersTableBody">
                    <?php if (count($users) > 0): ?>
                      <?php foreach ($users as $user): ?>
                        <?php
                          // Build avatar src per-user with fallback to default avatar
                          $defaultAvatar = '../../../../public/assets/images/default-avatar.png';
                          $imgSrc = $defaultAvatar;
                          if (!empty($user['profile_picture'])) {
                              $stored = ltrim($user['profile_picture'], '/'); // e.g. 'public/uploads/..'
                              // prefer filesystem-backed file if present; otherwise still try the relative URL
                              $fsPath = __DIR__ . '/../../../../' . $stored;
                              $imgSrc = file_exists($fsPath) ? ('../../../../' . $stored) : ('../../../../' . $stored);
                          }
                          // ensure escaped when printed
                        ?>
                         <tr data-role="<?php echo htmlspecialchars($user['role']); ?>" data-user-id="<?php echo htmlspecialchars($user['id']); ?>" data-user-name="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                           <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($user['id']); ?></td>
                           <td class="full-name-cell">
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Avatar" style="width:36px;height:36px;object-fit:cover;vertical-align:middle;margin-right:8px;">
                            <span><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></span>
                           </td>
                           <td class="d-none d-lg-table-cell"><?php echo htmlspecialchars($user['email']); ?></td>
                           <td><?php echo htmlspecialchars($user['role']); ?></td>
                           <td class="d-none d-xl-table-cell"><?php echo !empty($user['office_unit']) ? htmlspecialchars($user['office_unit']) : '-'; ?></td>
                           <td class="d-none d-lg-table-cell"><?php echo !empty($user['contact_number']) ? htmlspecialchars($user['contact_number']) : '-'; ?></td>
                           <td><span class="status-badge <?php echo ((int)$user['status'] === 1) ? 'status-enable' : 'status-disable'; ?>">
                             <?php echo ((int)$user['status'] === 1) ? 'Enable' : 'Disabled'; ?>
                           </span></td>
                           <td class="d-none d-md-table-cell"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                           <td>
                             <div class="d-flex gap-1">
                               <button class="btn-edit">
                                 <i class="fa fa-edit"></i><span class="d-none d-sm-inline"> Edit</span>
                               </button>
                               <button class="btn-disable" data-current-status="<?php echo ((int)$user['status'] === 1) ? '1' : '0'; ?>">
                                 <i class="fa <?php echo ((int)$user['status'] === 1) ? 'fa-ban' : 'fa-check-circle'; ?>"></i><span class="d-none d-sm-inline"> <?php echo ((int)$user['status'] === 1) ? 'Disable' : 'Enable'; ?></span>
                               </button>
                             </div>
                           </td>
                         </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="9" class="text-center text-muted py-3">No users found. <a href="add_user.php">Create the first user</a>.</td>
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

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Admin Dashboard JavaScript -->
  <script src="../../../../public/assets/js/admin/dashboard.js"></script>
  <!-- Admin Navigation JavaScript -->
  <script src="../../../../public/assets/js/admin/navigation.js"></script>
  
  <!-- User Management JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Adjust long names to fit a single line by reducing font-size
      function adjustNameFont() {
        const spans = document.querySelectorAll('.full-name-cell span');
        spans.forEach(span => {
          // reset to base size
          span.style.fontSize = '';
          const computed = window.getComputedStyle(span);
          let fontSize = parseFloat(computed.fontSize) || 14;
          const minSize = 10;
          // shrink until fits or reach minSize
          while (span.scrollWidth > span.clientWidth && fontSize > minSize) {
            fontSize -= 0.5;
            span.style.fontSize = fontSize + 'px';
          }
        });
      }
      // Run on load and resize
      adjustNameFont();
      window.addEventListener('resize', function() {
        // debounce
        clearTimeout(window._adjustNameFontTimer);
        window._adjustNameFontTimer = setTimeout(adjustNameFont, 120);
      });
      const searchInput = document.getElementById('searchInput');
      const filterButtons = document.querySelectorAll('.filter-btn');
      const tableBody = document.getElementById('usersTableBody');
      const allRows = Array.from(tableBody.getElementsByTagName('tr'));

      // Search functionality
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterRows(searchTerm, getCurrentFilter());
      });

      // Filter functionality
      filterButtons.forEach(button => {
        button.addEventListener('click', function() {
          // Update active button
          filterButtons.forEach(btn => {
            btn.classList.remove('active');
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-secondary');
          });
          
          this.classList.add('active');
          this.classList.add('btn-primary');
          this.classList.remove('btn-outline-secondary');
          
          // Filter rows
          const role = this.getAttribute('data-role');
          const searchTerm = searchInput.value.toLowerCase();
          filterRows(searchTerm, role);
        });
      });

      function getCurrentFilter() {
        const activeButton = document.querySelector('.filter-btn.active');
        return activeButton ? activeButton.getAttribute('data-role') : 'all';
      }

      function filterRows(searchTerm, roleFilter) {
        allRows.forEach(row => {
          const role = row.getAttribute('data-role');
          const text = row.textContent.toLowerCase();
          
          const matchesSearch = searchTerm === '' || text.includes(searchTerm);
          const matchesRole = roleFilter === 'all' || role === roleFilter;
          
          if (matchesSearch && matchesRole) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      }

      // Edit button functionality
      document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-edit')) {
          const row = e.target.closest('tr');
          const userId = row.getAttribute('data-user-id');
          window.location.href = `edit_user.php?id=${encodeURIComponent(userId)}`;
        }
      });

      document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-disable')) {
          const row = e.target.closest('tr');
          const userFullName = row.getAttribute('data-user-name');
          const userId = row.getAttribute('data-user-id');
          const statusBadge = row.querySelector('.status-badge');
          const isCurrentlyEnabled = statusBadge.classList.contains('status-enable');
          
          const action = isCurrentlyEnabled ? 'disable' : 'enable';
          const message = isCurrentlyEnabled 
            ? `Are you sure you want to disable ${userFullName}?` 
            : `Are you sure you want to enable ${userFullName}?`;
          
            if (confirm(message)) {
            // Send request to server to update user status
            // include credentials so the session cookie is sent (important for auth)
            fetch('../../../../app/admin/update_user_status.php', {
              method: 'POST',
              credentials: 'same-origin',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: `user_id=${encodeURIComponent(userId)}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Update the UI
                const disableBtn = e.target.closest('.btn-disable');
                if (action === 'disable') {
                  statusBadge.textContent = 'Disabled';
                  statusBadge.classList.remove('status-enable');
                  statusBadge.classList.add('status-disable');
                  disableBtn.innerHTML = '<i class="fa fa-check-circle"></i><span class="d-none d-sm-inline"> Enable</span>';
                  disableBtn.setAttribute('data-current-status', '0');
                } else {
                  statusBadge.textContent = 'Enable';
                  statusBadge.classList.remove('status-disable');
                  statusBadge.classList.add('status-enable');
                  disableBtn.innerHTML = '<i class="fa fa-ban"></i><span class="d-none d-sm-inline"> Disable</span>';
                  disableBtn.setAttribute('data-current-status', '1');
                }
                alert(data.message || 'User status updated successfully!');
              } else {
                alert('Error: ' + (data.message || 'Failed to update user status'));
              }
            })
            .catch(error => {
              alert('Error: ' + error.message);
            });
          }
        }
      });

      // Initialize profile dropdown functionality
      initializeProfileDropdown();
      // readjust after any dynamic filtering
      const observer = new MutationObserver(() => adjustNameFont());
      observer.observe(document.getElementById('usersTableBody'), { childList: true, subtree: true });
    });

    function initializeProfileDropdown() {
      const profileCard = document.getElementById('profileCard');
      const profileDropdown = document.getElementById('profileDropdown');
      
      if (!profileCard || !profileDropdown) return;
      
      let dropdownOpen = false;

      function toggleDropdown() {
        dropdownOpen = !dropdownOpen;
        if (dropdownOpen) {
          profileDropdown.classList.add('show');
        } else {
          profileDropdown.classList.remove('show');
        }
      }

      profileCard.addEventListener('click', function(e) {
        toggleDropdown();
        e.stopPropagation();
      });

      document.addEventListener('click', function(e) {
        if (!profileCard.contains(e.target)) {
          dropdownOpen = false;
          profileDropdown.classList.remove('show');
        }
      });

      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && dropdownOpen) {
          dropdownOpen = false;
          profileDropdown.classList.remove('show');
        }
      });
    }
  </script>
</body>
</html>