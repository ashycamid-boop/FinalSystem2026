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
  <title>Spot Reports</title>
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
  <!-- Status badge palette copied from admin spot-reports.css -->
  <style>
    .badge { font-size: 0.7rem; padding: 4px 8px; border-radius: 20px; }
    .badge.bg-success { background-color: #28a745 !important; }
    .badge.bg-warning { background-color: #ffc107 !important; color: #212529 !important; }
    .badge.bg-danger { background-color: #dc3545 !important; }
    /* Compact table styling */
    .table thead th, .table tbody td {
      font-size: 0.85rem;
      padding: 0.45rem 0.6rem;
    }
    .table thead th { font-size: 0.8rem; }
    .status-comment-btn i { font-size: 0.95rem; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.82rem; }
    /* Ensure Bootstrap utility classes like fs-6 don't enlarge these badges */
    .badge.fs-6 { font-size: 0.7rem !important; padding: 4px 8px !important; }
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
          <li><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li class="active"><a href="spot_reports.php"><i class="fa fa-file-text"></i> Spot Reports</a></li>
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
          <div class="topbar-title">Spot Reports</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <div class="container-fluid p-2">
          <!-- Filter Controls -->
          <div class="row mb-4 g-3 align-items-end">
            <!-- Search -->
            <div class="col-md-3">
              <input id="searchInput" type="text" class="form-control" placeholder="Search" style="border-radius: 8px; border: 1px solid #e0e0e0; height: 38px;">
            </div>
            <!-- Date From -->
            <div class="col-md-2">
              <input id="dateFrom" type="date" class="form-control" placeholder="dd/mm/yyyy" style="border-radius: 8px; border: 1px solid #e0e0e0; height: 38px;">
            </div>
            <!-- Date To -->
            <div class="col-md-2">
              <input id="dateTo" type="date" class="form-control" placeholder="dd/mm/yyyy" style="border-radius: 8px; border: 1px solid #e0e0e0; height: 38px;">
            </div>
            <!-- Status Filter -->
            <div class="col-md-2">
              <select id="statusFilter" class="form-select" style="border-radius: 8px; border: 1px solid #e0e0e0; height: 38px;">
                <option value="">All Status</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
            <!-- Apply Button -->
            <div class="col-md-1">
              <button id="applyFilter" class="btn btn-primary w-100" style="border-radius: 8px; background: #1976d2; border: none; height: 38px; font-size: 14px;">Apply</button>
            </div>
            <!-- Clear Button -->
            <div class="col-md-1">
              <button id="clearFilter" class="btn btn-outline-secondary w-100" style="border-radius: 8px; height: 38px; font-size: 14px;">Clear</button>
            </div>
          </div>

          <!-- New Spot Report Button -->
          <div class="row mb-3">
            <div class="col-12 d-flex justify-content-end">
              <a href="new_spot_report.php" class="btn btn-primary" style="border-radius: 8px; background: #1976d2; border: none; padding: 8px 16px; font-size: 14px; text-decoration: none;">
                New Spot Report
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
                          <th>Ref #</th>
                          <th>Incident Date</th>
                          <th>Location</th>
                          <th>Items</th>
                          <th>Team Leader</th>
                          <th>Custodian</th>
                          <th>Submitted By</th>
                          <th>Status</th>
                          <th>Est. Value</th>
                          <th>Check</th>
                        </tr>
                      </thead>
                      <tbody>
<?php
// Load spot reports from DB and show summary (truncated)
try {
  require_once dirname(dirname(dirname(__DIR__))) . '/config/db.php'; // loads $pdo
  $rows = array();

  // Show only spot reports submitted by the currently logged-in user
  $sessionUid = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
  if ($sessionUid) {
    $stmt = $pdo->prepare("SELECT s.id, s.reference_no, s.incident_datetime, s.location, s.summary, s.team_leader, s.custodian, s.status, s.status_comment, u.full_name AS submitted_by_name, (SELECT SUM(value) FROM spot_report_items WHERE report_id = s.id) AS est_value FROM spot_reports s LEFT JOIN users u ON u.id = s.submitted_by WHERE s.submitted_by = ? ORDER BY s.created_at DESC");
    $stmt->execute([$sessionUid]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    // Fallback: no user in session, return empty set
    $rows = array();
  }
} catch (Exception $e) {
  $rows = array();
}

function short($s, $len = 120) {
  $s = trim(strip_tags((string)$s));
  if (mb_strlen($s) <= $len) return $s;
  return mb_substr($s, 0, $len) . '...';
}

if (!empty($rows)) {
  foreach ($rows as $r) {
    $ref = htmlspecialchars($r['reference_no']);
    $inc = $r['incident_datetime'] ? htmlspecialchars($r['incident_datetime']) : '-';
    $loc = htmlspecialchars($r['location'] ?? '');
    $sum = htmlspecialchars(short($r['summary'] ?? ''));
    $tl = htmlspecialchars($r['team_leader'] ?? '');
    $cust = htmlspecialchars($r['custodian'] ?? '');
    $submittedBy = htmlspecialchars($r['submitted_by_name'] ?? '-');
    $stRaw = $r['status'] ?? '';
    $status = htmlspecialchars($stRaw);
    $commentRaw = isset($r['status_comment']) ? $r['status_comment'] : '';
    $commentAttr = htmlspecialchars($commentRaw, ENT_QUOTES);
    $badge = 'secondary';
    $stTrim = trim($stRaw);
    if (strcasecmp($stTrim, 'Draft') === 0 || strcasecmp($stTrim, 'Pending') === 0) $badge = 'warning';
    elseif (strcasecmp($stTrim, 'Approved') === 0) $badge = 'success';
    elseif (strcasecmp($stTrim, 'Rejected') === 0) $badge = 'danger';
    elseif (strcasecmp($stTrim, 'Under Review') === 0 || strcasecmp($stTrim, 'Under_Review') === 0) $badge = 'info';
    // Map to a specific CSS class for custom palette
    $statusClass = 'status-secondary';
    if ($badge === 'success') $statusClass = 'status-approved';
    elseif ($badge === 'warning') $statusClass = 'status-warning';
    elseif ($badge === 'danger') $statusClass = 'status-danger';
    elseif ($badge === 'info') $statusClass = 'status-info';
    $estRaw = isset($r['est_value']) ? $r['est_value'] : null;
    $est = ($estRaw !== null && $estRaw !== '') ? ('₱ ' . number_format((float)$estRaw, 2)) : '-';
    $viewUrl = 'view_spot_report.php?ref=' . urlencode($r['reference_no']);
    echo "<tr>\n";
    echo "  <td><a href=\"$viewUrl\">$ref</a></td>\n";
    echo "  <td>$inc</td>\n";
    echo "  <td>$loc</td>\n";
    // Display summary only in the 'Items' column as requested
    echo "  <td>$sum</td>\n";
    echo "  <td>$tl</td>\n";
    echo "  <td>$cust</td>\n";
    echo "  <td>" . $submittedBy . "</td>\n";
    // Show question icon when rejected and a comment exists
    $statusCellHtml = "<span class=\"badge bg-{$badge} fs-6\">$status</span>";
    if (strcasecmp(trim($stRaw), 'Rejected') === 0 && $commentRaw !== '') {
      $statusCellHtml .= " <button type=\"button\" class=\"status-comment-btn\" data-comment=\"{$commentAttr}\" title=\"View comment\" style=\"display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;margin-left:8px;vertical-align:middle;border:1px solid #ced4da;border-radius:6px;background:#fff;color:#495057;padding:0;\"><i class=\"fa fa-question-circle\"></i></button>";
    }
    echo "  <td>$statusCellHtml</td>\n";
    echo "  <td>$est</td>\n";
    echo "  <td><a class=\"btn btn-sm btn-outline-primary\" href=\"$viewUrl\">Open</a></td>\n";
    echo "</tr>\n";
  }
} else {
  echo '<tr><td colspan="10" class="text-center">No spot reports found.</td></tr>';
}
?>
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

      function parseDateOnly(str) {
        if (!str) return null;
        const m = str.match(/(\d{4}-\d{2}-\d{2})/);
        if (m) return new Date(m[1]);
        const d = new Date(str);
        return isNaN(d.getTime()) ? null : d;
      }

      function applyFilters() {
        const searchTerm = (document.getElementById('searchInput').value || '').trim().toLowerCase();
        const dateFromVal = document.getElementById('dateFrom').value;
        const dateToVal = document.getElementById('dateTo').value;
        const statusVal = (document.getElementById('statusFilter').value || '').trim().toLowerCase();

        const dateFrom = dateFromVal ? new Date(dateFromVal) : null;
        const dateTo = dateToVal ? new Date(dateToVal) : null;
        if (dateTo) dateTo.setHours(23,59,59,999);

        const rows = document.querySelectorAll('table tbody tr');
        let anyVisible = false;

        rows.forEach(row => {
          // skip placeholder row with colspan
          if (row.querySelector('td') && row.querySelector('td').getAttribute('colspan')) return;

          const cells = row.cells;
          if (!cells) return;

          const ref = (cells[0].textContent || '').toLowerCase();
          const incText = (cells[1].textContent || '').trim();
          const loc = (cells[2].textContent || '').toLowerCase();
          const items = (cells[3].textContent || '').toLowerCase();
          const teamLeader = (cells[4] ? (cells[4].textContent||'') : '').toLowerCase();
          const custodian = (cells[5] ? (cells[5].textContent||'') : '').toLowerCase();
          const submittedBy = (cells[6] ? (cells[6].textContent||'') : '').toLowerCase();
          const statusText = (cells[7] ? (cells[7].textContent||'') : '').toLowerCase();

          let visible = true;

          if (searchTerm) {
            const hay = ref + ' ' + loc + ' ' + items + ' ' + teamLeader + ' ' + custodian + ' ' + submittedBy;
            if (!hay.includes(searchTerm)) visible = false;
          }

          if (visible && (dateFrom || dateTo)) {
            const incDate = parseDateOnly(incText);
            if (!incDate) visible = false;
            else {
              if (dateFrom && incDate < dateFrom) visible = false;
              if (dateTo && incDate > dateTo) visible = false;
            }
          }

          if (visible && statusVal) {
            const norm = statusVal.replace(/_/g,' ');
            if (!statusText.includes(norm)) visible = false;
          }

          row.style.display = visible ? '' : 'none';
          if (visible) anyVisible = true;
        });

        // If no rows visible, show the 'No spot reports' row if exists
        const tbody = document.querySelector('table tbody');
        if (tbody) {
          const placeholder = tbody.querySelector('tr[data-placeholder]');
          if (!anyVisible) {
            if (!placeholder) {
              const nr = document.createElement('tr');
              nr.setAttribute('data-placeholder','1');
              nr.innerHTML = '<td colspan="10" class="text-center">No spot reports found.</td>';
              tbody.appendChild(nr);
            }
          } else {
            if (placeholder) placeholder.remove();
          }
        }
      }

      const applyFilterBtn = document.getElementById('applyFilter');
      const clearFilterBtn = document.getElementById('clearFilter');
      if (applyFilterBtn) applyFilterBtn.addEventListener('click', applyFilters);
      if (clearFilterBtn) clearFilterBtn.addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        document.getElementById('statusFilter').value = '';
        applyFilters();
      });

      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
          if (e.key === 'Enter') {
            e.preventDefault();
            applyFilters();
          }
        });
      }

    });

      // Attach click handlers to status comment buttons (delegated)
      function showStatusCommentModal(text) {
        // Remove existing modal if present
        const existing = document.getElementById('statusCommentModal');
        if (existing) existing.remove();

        const modalHtml = `
          <div class="modal fade" id="statusCommentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Rejection Comment</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="statusCommentModalBody"></div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const bodyEl = document.getElementById('statusCommentModalBody');
        if (bodyEl) bodyEl.textContent = text || '';
        const modal = new bootstrap.Modal(document.getElementById('statusCommentModal'));
        modal.show();
      }

      document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.status-comment-btn');
        if (!btn) return;
        e.stopPropagation();
        const txt = btn.getAttribute('data-comment') || '';
        showStatusCommentModal(txt);
      });
  </script>
  </body>
</html>

