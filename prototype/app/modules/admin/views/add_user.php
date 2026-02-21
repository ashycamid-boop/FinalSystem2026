<?php
session_start();
require_once __DIR__ . '/../../../../app/config/db.php';
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: /prototype/index.php');
    exit;
}


// Prepare topbar profile using logged-in user's DB values (fallbacks for prototype)
$topImg = '../../../../public/assets/images/default-avatar.png';
$topName = 'Guest';
$topRole = 'User';
$sessionUserId = $_SESSION['uid'] ?? $_SESSION['id'] ?? null;
$sessionUserEmail = $_SESSION['email'] ?? null;
try {
  $r = null;
  if (!empty($sessionUserId)) {
    $stmt = $pdo->prepare('SELECT id, full_name, profile_picture, role FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$sessionUserId]);
    $r = $stmt->fetch();
  } elseif (!empty($sessionUserEmail)) {
    $stmt = $pdo->prepare('SELECT id, full_name, profile_picture, role FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$sessionUserEmail]);
    $r = $stmt->fetch();
  }
  if (!empty($r)) {
    $topName = !empty($r['full_name']) ? $r['full_name'] : $topName;
    $topRole = !empty($r['role']) ? $r['role'] : $topRole;
    if (!empty($r['profile_picture'])) {
      $stored = ltrim($r['profile_picture'], '/');
      $fsPath = __DIR__ . '/../../../../' . $stored;
      if (file_exists($fsPath)) {
        $topImg = '../../../../' . $stored;
      }
    }
  }
} catch (Exception $e) {
  // silent fallback to defaults
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Admin common styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <!-- Add User specific styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/add_user.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Fredoka+One:400&display=swap" rel="stylesheet">
</head>
<body>
  <div class="layout">
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
    <div class="main">
      <div class="topbar">
          <div class="topbar-card">
          <div class="topbar-title">Add New User</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <div class="container-fluid p-4">
          <div>
            <div>
              <h1 class="page-title">
                <i class="fa fa-user-plus"></i>
                Add New User Account
              </h1>
            </div>
          </div>

          
          <form id="addUserForm" method="post" enctype="multipart/form-data" action="../../../auth/register.php">
          <div class="row g-4">
            
            <div class="col-12 col-lg-4">
              <div class="card form-card">
                <div class="card-header-simple">
                  <h5>
                    <i class="card-icon fa fa-camera"></i>
                    Profile Picture
                  </h5>
                </div>
                <div class="profile-upload-section">
                  <div class="profile-placeholder" id="profilePreview" onclick="document.getElementById('profile_picture').click()">
                    <i class="fa fa-user"></i>
                  </div>
                  <input type="file" name="profile_picture" id="profile_picture" accept="image/png, image/jpeg" style="display:none" onchange="previewProfile(event)">
                  <button type="button" class="upload-btn" onclick="document.getElementById('profile_picture').click()">
                    <i class="fa fa-upload me-2"></i>Choose Photo
                  </button>
                  <p class="form-help mt-2">
                    JPG, PNG format. Max 2MB
                  </p>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-8">
              <div class="card form-card">
                <div class="card-header-simple">
                  <h5>
                    <i class="card-icon fa fa-user-edit"></i>
                    User Information
                  </h5>
                </div>
                <div class="form-section">
                  
                    <div class="form-section-spacing">
                      <div class="row g-3">
                        <div class="col-12 col-md-3">
                          <div class="form-group-clean">
                            <label for="firstName" class="form-label-clean">
                              First Name<span class="required-mark">*</span>
                            </label>
                            <input type="text" name="firstName" class="form-control-clean" id="firstName" required placeholder="Enter first name">
                          </div>
                        </div>
                        <div class="col-12 col-md-3">
                          <div class="form-group-clean">
                            <label for="middleName" class="form-label-clean">Middle Name</label>
                            <input type="text" name="middleName" class="form-control-clean" id="middleName" placeholder="Enter middle name">
                          </div>
                        </div>
                        <div class="col-12 col-md-3">
                          <div class="form-group-clean">
                            <label for="lastName" class="form-label-clean">
                              Last Name<span class="required-mark">*</span>
                            </label>
                            <input type="text" name="lastName" class="form-control-clean" id="lastName" required placeholder="Enter last name">
                          </div>
                        </div>
                        <div class="col-12 col-md-3">
                          <div class="form-group-clean">
                            <label for="suffix" class="form-label-clean">Suffix</label>
                            <input type="text" name="suffix" class="form-control-clean" id="suffix" placeholder="Jr., Sr., III">
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="form-section-spacing">
                      <h6 class="section-title">
                        <i class="section-icon fa fa-phone"></i>
                        Contact Information
                      </h6>
                      <div class="row g-3">
                        <div class="col-12 col-md-6">
                          <div class="form-group-clean">
                            <label for="email" class="form-label-clean">
                              Email Address<span class="required-mark">*</span>
                            </label>
                            <input type="email" name="email" class="form-control-clean" id="email" required placeholder="">
                            <div class="form-help"></div>
                          </div>
                        </div>
                        <div class="col-12 col-md-6">
                          <div class="form-group-clean">
                            <label for="contactNumber" class="form-label-clean">
                              Contact Number<span class="required-mark">*</span>
                            </label>
                            <input type="tel" name="contactNumber" class="form-control-clean" id="contactNumber" required placeholder="">
                            <div class="form-help"></div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="form-section-spacing">
                      <h6 class="section-title">
                        <i class="section-icon fa fa-lock"></i>
                        Account Security
                      </h6>
                      <div class="row g-3">
                        <div class="col-12 col-md-6">
                          <div class="form-group-clean">
                            <label for="password" class="form-label-clean">
                              Password<span class="required-mark">*</span>
                            </label>
                            <input type="password" name="password" class="form-control-clean" id="password" required placeholder="Create secure password">
                            <div class="form-help">Minimum 6 characters</div>
                          </div>
                        </div>
                        <div class="col-12 col-md-6">
                          <div class="form-group-clean">
                            <label for="confirmPassword" class="form-label-clean">
                              Confirm Password<span class="required-mark">*</span>
                            </label>
                            <input type="password" name="confirmPassword" class="form-control-clean" id="confirmPassword" required placeholder="Repeat password">
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="form-section-spacing">
                      <h6 class="section-title">
                        <i class="section-icon fa fa-building"></i>
                        Work Assignment
                      </h6>
                      <div class="row g-3">
                        <div class="col-12 col-md-6">
                          <div class="form-group-clean">
                            <label for="officeUnit" class="form-label-clean">
                              Office/Unit<span class="required-mark">*</span>
                            </label>
                            <select class="form-select-clean" name="officeUnit" id="officeUnit" required>
                              <option value="">Select Office/Unit</option>
                              <option value="Antongalon ENR Monitoring Information and Assistance Center">Antongalon ENR Monitoring Information and Assistance Center</option>
                              <option value="BIT-OS ENR Monitoring Information and Assistance Center">BIT-OS ENR Monitoring Information and Assistance Center</option>
                              <option value="Bokbokon Anti-Illegal Logging Taskforce Checkpoint">Bokbokon Anti-Illegal Logging Taskforce Checkpoint</option>
                              <option value="Buenavista ENR Monitoring Information and Assistance Center">Buenavista ENR Monitoring Information and Assistance Center</option>
                              <option value="Camagong Anti-Environmental Crime Task Force (AECTF) Checkpoint">Camagong Anti-Environmental Crime Task Force (AECTF) Checkpoint</option>
                              <option value="CBFM">CBFM</option>
                              <option value="CRFMU">CRFMU</option>
                              <option value="Dankias ENR Monitoring Information and Assistance Center">Dankias ENR Monitoring Information and Assistance Center</option>
                              <option value="Foreshore Management Unit">Foreshore Management Unit</option>
                              <option value="Licensing and Permitting Unit">Licensing and Permitting Unit</option>
                              <option value="Lumbocan ENR Monitoring Information and Assistance Center">Lumbocan ENR Monitoring Information and Assistance Center</option>
                              <option value="Monitoring and Evaluation Unit">Monitoring and Evaluation Unit</option>
                              <option value="Nasipit Port ENR Monitoring Information and Assistance Center">Nasipit Port ENR Monitoring Information and Assistance Center</option>
                              <option value="NGP">NGP</option>
                              <option value="PABEU">PABEU</option>
                              <option value="Patents and Deeds Unit">Patents and Deeds Unit</option>
                              <option value="Planning Unit">Planning Unit</option>
                              <option value="Support Unit">Support Unit</option>
                              <option value="Survey and Mapping Unit">Survey and Mapping Unit</option>
                              <option value="WATERSHED">WATERSHED</option>
                              <option value="WRUS">WRUS</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-12 col-md-6">
                          <div class="form-group-clean">
                            <label for="role" class="form-label-clean">
                              Role<span class="required-mark">*</span>
                            </label>
                            <select class="form-select-clean" name="role" id="role" required>
                              <option value="">Select Role</option>
                              <option value="Enforcement Officer">Enforcement Officer</option>
                              <option value="Enforcer">Enforcer</option>
                              <option value="Property Custodian">Property Custodian</option>
                              <option value="Office Staff">Office Staff</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="form-section-spacing">
                      <h6 class="section-title">
                        <i class="section-icon fa fa-id-badge"></i>
                        Position
                      </h6>
                      <div class="row g-3">
                        <div class="col-12 col-md-6">
                          <div class="form-group-clean">
                            <label for="position" class="form-label-clean">
                              Position<span class="required-mark">*</span>
                            </label>
                            <input type="text" name="position" class="form-control-clean" id="position" required placeholder="Enter position">
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="action-buttons">
                      <button type="button" class="btn-cancel" onclick="window.location.href='user_management.php'">
                        <i class="fa fa-times me-2"></i>Cancel
                      </button>
                      <button type="submit" class="btn-create">
                        <i class="fa fa-user-plus me-2"></i>Create Account
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../../../public/assets/js/admin/dashboard.js"></script>
  <script src="../../../../public/assets/js/admin/navigation.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Allow normal POST to server-side register handler. Client-side validation only.
      const passwordField = document.getElementById('password');
      const confirmPasswordField = document.getElementById('confirmPassword');

      confirmPasswordField.addEventListener('input', function() {
        if (passwordField.value !== confirmPasswordField.value) {
          confirmPasswordField.setCustomValidity('Passwords do not match');
          confirmPasswordField.classList.add('is-invalid');
        } else {
          confirmPasswordField.setCustomValidity('');
          confirmPasswordField.classList.remove('is-invalid');
        }
      });

      initializeProfileDropdown();
    });
    function simulatePhotoUpload() {
      showNotification('Photo upload functionality - this is a prototype interface.', 'info');
    }

    function showNotification(message, type) {
      const notification = document.createElement('div');
      notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} position-fixed`;
      notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);';
      notification.innerHTML = `
        <div class="d-flex align-items-center">
          <i class="fa fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
          <span>${message}</span>
          <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
      `;
      document.body.appendChild(notification);
      
      setTimeout(() => {
        if (notification.parentElement) {
          notification.remove();
        }
      }, 5000);
    }

    // Preview selected profile image - moved into script block so function is defined
    function previewProfile(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('profilePreview');
      if (!preview) return;

      if (!file) {
        // no file selected, reset placeholder
        preview.innerHTML = '<i class="fa fa-user"></i>';
        return;
      }

      const allowed = ['image/jpeg','image/png'];
      if (!allowed.includes(file.type)) {
        showNotification('Only JPG and PNG images are allowed.', 'error');
        e.target.value = '';
        return;
      }
      if (file.size > 2 * 1024 * 1024) {
        showNotification('File is too large. Max 2MB.', 'error');
        e.target.value = '';
        return;
      }
      const reader = new FileReader();
      reader.onload = function(ev) {
        preview.innerHTML = `<img src="${ev.target.result}" alt="Profile" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
      };
      reader.readAsDataURL(file);
    }

    function showLoading() {
      const loading = document.createElement('div');
      loading.id = 'loadingOverlay';
      loading.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;';
      loading.innerHTML = `
        <div class="text-center text-white">
          <div class="spinner-border mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
          </div>
          <h5>Creating user account...</h5>
        </div>
      `;
      document.body.appendChild(loading);
    }

    function hideLoading() {
      const loading = document.getElementById('loadingOverlay');
      if (loading) {
        loading.remove();
      }
    }

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