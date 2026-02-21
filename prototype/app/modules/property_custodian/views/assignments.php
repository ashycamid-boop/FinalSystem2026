<?php
session_start();
require_once __DIR__ . '/../../../../app/config/db.php';

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'property_custodian') {
    header('Location: /prototype/index.php');
    exit;
}
$users = [];
try {
  $stmt = $pdo->query("SELECT id, email, full_name, role, office_unit, profile_picture, created_at FROM users WHERE role <> 'Admin' ORDER BY created_at DESC");
  $users = $stmt->fetchAll();
} catch (Exception $e) {
  $users = [];
}

// Compute device counts per user. Use a prepared statement to handle numeric ID, exact name, or partial name matches.
if (!empty($users)) {
  try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM equipment WHERE actual_user = :user_id OR actual_user = :full_name OR actual_user LIKE :like_name");
    foreach ($users as &$u) {
      $uid = (int)($u['id'] ?? 0);
      $full = $u['full_name'] ?? '';
      $like = '%' . $full . '%';
      try {
        $countStmt->execute([':user_id' => (string)$uid, ':full_name' => $full, ':like_name' => $like]);
        $row = $countStmt->fetch();
        $u['device_count'] = $row ? (int)$row['cnt'] : 0;
      } catch (Exception $e) {
        $u['device_count'] = 0;
      }
    }
    unset($u);
  } catch (Exception $e) {
    // ignore counts on failure
    foreach ($users as &$u) { $u['device_count'] = 0; } unset($u);
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assignments</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/assignments.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Fredoka+One:400&display=swap" rel="stylesheet">
  <style>
    .qr-code-image {
      width: 50px;
      height: 50px;
      object-fit: contain;
    }
    /* compact table: smaller font and reduced row padding */
    #assignmentsTable, #assignmentsTable th, #assignmentsTable td {
      font-size: 13px;
    }
    #assignmentsTable thead th {
      font-size: 12px;
      padding: .35rem .5rem;
    }
    #assignmentsTable tbody td {
      padding: .35rem .5rem;
      vertical-align: middle;
    }
    #assignmentsTable tbody tr {
      height: 40px; /* visual row height; browsers may vary */
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
    <div class="main">
      <div class="topbar">
        <div class="topbar-card">
          <div class="topbar-title">Assignments</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <div class="container-fluid">
          
          <div class="top-action-bar mb-4">
            <div class="row align-items-center">
              <div class="col-md-3">
                <div class="search-box">
                  <input type="text" class="form-control" id="searchInput" placeholder="Search">
                </div>
              </div>
              <div class="col-md-2">
                <select class="form-select" id="roleFilter">
                  <option value="">Role</option>
                  <option value="Enforcement Officer">Enforcement Officer</option>
                  <option value="Enforcer">Enforcer</option>
                  <option value="Property Custodian">Property Custodian</option>
                  <option value="Office Staff">Office Staff</option>
                </select>
              </div>
              <div class="col-md-2">
                <select class="form-select" id="officeUnitFilter">
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
              <div class="col-md-2">
                <div class="d-flex gap-2">
                  <button class="btn btn-primary" id="applyBtn">
                    <i class="fa fa-filter me-1"></i>Apply
                  </button>
                  <button class="btn btn-outline-secondary" id="clearBtn">Clear</button>
                </div>
              </div>
              <div class="col-md-3 text-end">
                <button class="btn btn-outline-dark" onclick="printAllQRCodes()">
                  <i class="fa fa-print me-2"></i>Print All QR Codes
                </button>
              </div>
            </div>
          </div>

          <div class="assignments-table-section">
            <div class="table-responsive">
              <table class="table table-bordered" id="assignmentsTable">
                <thead class="table-light">
                  <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Office/Unit</th>
                    <th>Devices</th>
                    <th>QR Code</th>
                    <th>Details</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                      <?php
                        $defaultAvatar = '../../../../public/assets/images/default-avatar.png';
                        $imgSrc = $defaultAvatar;
                        if (!empty($user['profile_picture'])) {
                          $stored = ltrim($user['profile_picture'], '/');
                          $fsPath = __DIR__ . '/../../../../' . $stored;
                          $imgSrc = file_exists($fsPath) ? ('../../../../' . $stored) : ('../../../../' . $stored);
                        }
                        // Build a full URL that points to the assigned-devices page for this user.
                        // Scanning the QR will open the user's assigned devices details.
                        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                        // Directory of current request (e.g. /prototype/app/modules/admin/views)
                        $dir = rtrim(dirname($_SERVER['REQUEST_URI']), '/\\');
                        $assignedPath = $dir . '/assigned-devices.php?user_id=' . urlencode($user['id']);
                        $qrUrl = $scheme . '://' . $host . $assignedPath;
                        $qrData = urlencode($qrUrl);
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td>
                          <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Avatar" style="width:36px;height:36px;object-fit:cover;border-radius:50%;vertical-align:middle;margin-right:8px;">
                          <span><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo !empty($user['office_unit']) ? htmlspecialchars($user['office_unit']) : '-'; ?></td>
                        <td class="text-center"><a href="assigned_devices.php?user_id=<?php echo urlencode($user['id']); ?>"><?php echo (isset($user['device_count']) ? (int)$user['device_count'] : 0); ?></a></td>
                        <td><img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo $qrData; ?>" class="qr-code-image" alt="QR"></td>
                        <td><a href="assigned_devices.php?user_id=<?php echo urlencode($user['id']); ?>" class="btn btn-sm btn-outline-primary">Details</a></td>
                        <td>
                          <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="printAssignedDevices('<?php echo htmlspecialchars($user['id']); ?>')" aria-label="Print QR card">
                              <i class="fa fa-print"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="9" class="text-center text-muted py-3">No users found.</td>
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

  <!-- QR printable grid removed; QR cards will be generated dynamically when needed -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../../../public/assets/js/admin/dashboard.js"></script>
  <script src="../../../../public/assets/js/admin/navigation.js"></script>
  <script src="../../../../public/assets/js/admin/assignments.js"></script>
  
  <script>
    function printAllQRCodes() {
      const printWindow = window.open('', '_blank');
      
      // Collect user rows from the assignments table to build printable data
      const rows = document.querySelectorAll('#assignmentsTable tbody tr');
      const userData = [];
      rows.forEach(r => {
        const idCell = r.cells[0];
        if (!idCell) return;
        // Skip empty/no-data rows
        const possibleText = idCell.textContent.trim();
        if (!possibleText) return;
        const qrImg = r.querySelector('img.qr-code-image');
        const qrSrc = qrImg ? qrImg.src : '';
        const name = r.cells[1] ? r.cells[1].innerText.trim() : '';
        const unit = r.cells[4] ? r.cells[4].innerText.trim() : '';
        userData.push({ name, unit, qrSrc });
      });

      if (userData.length === 0) {
        alert('No QR codes found to print.');
        printWindow.close();
        return;
      }
      
      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>User QR Codes - CENRO NASIPIT</title>
          <style>
            @page { size: A4; margin: 10mm; }
            body {
              font-family: "Times New Roman", Times, serif;
              margin: 0;
              padding: 0; /* remove extra padding to fit 3 rows */
              background: white;
            }
            /* Two columns, three rows per page -> 6 cards per A4 */
            .qr-grid {
              display: grid;
              grid-template-columns: repeat(2, 1fr);
              grid-auto-rows: 86mm; /* reduced from 90mm */
              gap: 4mm 6mm; /* reduced row-gap and col-gap */
              margin: 0;
            }
            .qr-card {
              box-sizing: border-box;
              border: 1px solid #2c5530;
              padding: 5px; /* reduced from 6px */
              text-align: center;
              background: white;
              page-break-inside: avoid;
              width: 100%;
              height: 100%;
              display: flex;
              flex-direction: column;
              justify-content: flex-start;
              align-items: center;
            }
            .header {
              text-align: left;
              margin-bottom: 8px;
              display: flex;
              align-items: center;
              gap: 10px;
            }
            .denr-logo {
              width: 40px;
              height: 40px;
              object-fit: contain;
            }
            .header-text {
              flex: 1;
              text-align: left;
            }
            .header h3 {
              color: #000;
              margin: 0;
              font-size: 11px;
              font-weight: bold;
              font-family: "Times New Roman", Times, serif;
            }
            .header h4 {
              color: #000;
              margin: 2px 0 0 0;
              font-size: 10px;
              font-weight: normal;
              font-family: "Times New Roman", Times, serif;
            }
            .property-title {
              background: #2c5530;
              color: white;
              padding: 6px;
              margin: 8px 0 12px 0;
              font-weight: bold;
              font-size: 12px;
              letter-spacing: 1px;
              width: 100%;
              font-family: "Times New Roman", Times, serif;
            }
            .qr-code {
              margin: 8px 0;
            }
            .qr-code img {
              width: 36mm; /* sticker-appropriate */
              height: 36mm;
              border: 1px solid #ccc;
            }
            .user-info {
              margin-top: 8px;
            }
            .user-name {
              font-weight: bold;
              font-size: 13px;
              color: #2c5530;
              margin-bottom: 4px;
              text-transform: uppercase;
              font-family: "Times New Roman", Times, serif;
            }
            .unit-name {
              font-size: 10px;
              color: #666;
              font-style: italic;
              font-family: "Times New Roman", Times, serif;
            }
            @media print {
              body { margin: 0; padding: 0; }
              .qr-grid { gap: 4mm 6mm; }
              .qr-card { page-break-inside: avoid; }
            }
          </style>
        </head>
        <body>
          <div class="qr-grid">
      `);
      
      // Generate QR code cards
      userData.forEach((user, idx) => {
        printWindow.document.write(`
          <div class="qr-card">
            <div class="header">
              <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo" class="denr-logo">
              <div class="header-text">
                <div style="font-weight:bold;font-size:11px;">Department of Environment and Natural Resources</div>
                <div style="font-size:10px;margin-top:2px;">Community Environment and Natural Resources Office</div>
                <div style="font-size:10px;margin-top:2px;">CENRO Nasipit, Agusan del Norte</div>
              </div>
            </div>

            <div class="property-title">RP GOVERNMENT PROPERTY</div>

            <div class="qr-code">
              <img src="${user.qrSrc}" alt="QR Code">
            </div>

            <div class="user-info">
              <div class="user-name">${user.name}</div>
              <div class="unit-name">${user.unit}</div>
            </div>
          </div>
        `);

        // No explicit page-break element: rely on print pagination and adjusted sizes
      });
      
      printWindow.document.write(`
          </div>
        </body>
        </html>
      `);
      
      printWindow.document.close();
      
      // Wait for images to load before printing
      setTimeout(() => {
        printWindow.print();
      }, 1000);
    }
  </script>
  <script>
    function printAssignedDevices(userId) {
      if (!userId) return;

      // Find the table row for this user to extract the QR src, full name and office/unit
      const rows = document.querySelectorAll('#assignmentsTable tbody tr');
      let targetRow = null;
      rows.forEach(r => {
        const idCell = r.cells[0];
        if (idCell && idCell.textContent.trim() === String(userId)) targetRow = r;
      });

      if (!targetRow) {
        alert('User row not found.');
        return;
      }

      const qrImg = targetRow.querySelector('img.qr-code-image');
      const qrSrc = qrImg ? qrImg.src : '';
      const fullName = (targetRow.cells[1] && targetRow.cells[1].innerText) ? targetRow.cells[1].innerText.trim() : '';
      const office = (targetRow.cells[4] && targetRow.cells[4].innerText) ? targetRow.cells[4].innerText.trim() : '';

      const w = window.open('', '_blank');
      if (!w) { alert('Popup blocked. Please allow popups for this site to print.'); return; }

      const html = `<!doctype html>
        <html>
        <head>
          <meta charset="utf-8">
          <title>QR Sticker - ${escapeHtml(fullName)}</title>
          <style>
            @page { size: A4 portrait; margin: 10mm; }
            body { font-family: "Times New Roman", Times, serif; margin: 0; padding: 0; background: #fff; color: #000; }

            /* Sticker container sized for small UPS label */
            .sticker-wrap { width: 70mm; height: 90mm; margin: 12mm auto; border: 2px solid #2c5530; padding: 4mm; box-sizing: border-box; }

            .sticker-header { display:flex; gap:6px; align-items:flex-start; }
            .sticker-logo img { width: 14mm; height: 14mm; object-fit:contain; }
            .sticker-text { flex:1; text-align:center; font-size:9px; line-height:1.05; }
            .sticker-text .line1 { font-weight:bold; font-size:10px; text-transform:uppercase; }
            .sticker-text .line2 { font-size:8px; }
            .property-title { background:#2c5530; color:#fff; padding:3px 6px; margin:6px 0; font-weight:bold; font-size:9px; letter-spacing:1px; text-align:center; }

            .qr-block { text-align:center; margin-top:4mm; }
            .qr-block img { width: 36mm; height: 36mm; object-fit:contain; border:1px solid #ddd; padding:2px; background:#fff; }

            .info { text-align:center; margin-top:4mm; }
            .info .name { font-weight:bold; font-size:9px; text-transform:uppercase; color:#2c5530; }
            .info .unit { font-size:8px; font-style:italic; color:#444; }

            @media print {
              body { margin: 0; padding: 0; }
              .sticker-wrap { margin: 0; }
            }
          </style>
        </head>
        <body>
          <div class="sticker-wrap">
            <div class="sticker-header">
              <div class="sticker-logo"><img src="../../../../public/assets/images/denr-logo.png" alt="DENR"></div>
              <div class="sticker-text">
                <div class="line1">Department of Environment and Natural Resources</div>
                <div class="line2">Community Environment and Natural Resources Office</div>
                <div class="line2">CENRO Nasipit, Agusan del Norte</div>
              </div>
            </div>

            <div class="property-title">RP GOVERNMENT PROPERTY</div>

            <div class="qr-block">
              <img src="${qrSrc}" alt="QR">
            </div>

            <div class="info">
              <div class="name">${escapeHtml(fullName)}</div>
              <div class="unit">${escapeHtml(office)}</div>
            </div>
          </div>
        </body>
        </html>`;

      w.document.open();
      w.document.write(html);
      w.document.close();

      // Give images a moment to load then print
      setTimeout(() => { try { w.focus(); w.print(); } catch (e) {} }, 600);
    }

    function escapeHtml(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }
  </script>
</body>
</html>