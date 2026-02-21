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
  <title>Case Management</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Admin common styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <!-- Case Management specific styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/case-management.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Fredoka+One:400&display=swap" rel="stylesheet">
  
  <style>
    .badge.bg-primary {
      background-color: #0d6efd !important;
      color: white !important;
    }
    .badge.bg-success {
      background-color: #198754 !important;
      color: white !important;
    }
    /* Summary card sizing tweaks */
    .summary-card {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 0.5rem 0.75rem;
      min-width: 110px;
      box-sizing: border-box;
    }
    .summary-label {
      font-size: 0.78rem;
      color: #555;
      text-align: center;
      white-space: nowrap;
    }
    .summary-value {
      font-size: 1.25rem;
      font-weight: 700;
      margin-top: 0.25rem;
      text-align: center;
      white-space: nowrap;
    }
    /* Compact pill layout */
    .summary-pills {
      display: flex;
      flex-wrap: wrap;
      gap: 0.65rem;
      align-items: center;
    }
    .summary-pill {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      background: #f8f9fa;
      border: 1px solid #e9ecef;
      padding: 0.6rem 0.85rem;
      border-radius: 999px;
      min-width: 120px;
      box-sizing: border-box;
    }
    .pill-label { font-size: 0.85rem; color: #555; white-space: nowrap; }
    .pill-count { font-weight: 800; font-size: 1.15rem; color: #222; }
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
      <div class="sidebar-role">Administrator</div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li><a href="user_management.php"><i class="fa fa-users"></i> User Management</a></li>
          <li><a href="spot_reports.php"><i class="fa fa-file-text"></i> Spot Reports</a></li>
          <li class="active"><a href="case_management.php"><i class="fa fa-briefcase"></i> Case Management</a></li>
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
            <div class="topbar-title">Case Management</div>
            <?php include_once __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        
        </div>
      </div>
      <div class="main-content">
        <!-- Case Management Content -->
        <div class="container-fluid p-4">
          <!-- Search and Filter Section -->
          <div class="filter-section mb-4">
            <div class="row g-3 align-items-end">
              <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Search" id="searchInput">
              </div>
              <div class="col-md-2">
                <input type="date" class="form-control" placeholder="dd/mm/yyyy" id="dateFrom">
              </div>
              <div class="col-md-2">
                <input type="date" class="form-control" placeholder="dd/mm/yyyy" id="dateTo">
              </div>
              <div class="col-md-2">
                <select class="form-select" id="statusFilter">
                  <option value="">All Status</option>
                  <option value="under-investigation">Under Investigation</option>
                  <option value="for-filing">For Filing</option>
                  <option value="ongoing">Ongoing</option>
                  <option value="dismissed">Dismissed</option>
                  <option value="resolved">Resolved</option>
                </select>
              </div>
              <div class="col-md-1">
                <button class="btn btn-primary w-100" id="applyFilter">
                  <i class="fa fa-filter"></i> Apply
                </button>
              </div>
              <div class="col-md-1">
                <button class="btn btn-outline-secondary w-100" id="clearFilter">Clear</button>
              </div>
            </div>
          </div>

          <!-- Summary Cards -->
          <?php
          // Load approved spot reports and status counts for Case Management
          try {
            require_once dirname(dirname(dirname(__DIR__))) . '/config/db.php'; // loads $pdo

            // Initialize counts for compact status summary (admin)
            $counts = [
              'under-investigation' => 0,
              'pending-review' => 0,
              'for-filing' => 0,
              'filed-in-court' => 0,
              'ongoing-trial' => 0,
              'resolved' => 0,
              'dismissed' => 0,
              'archived' => 0,
              'on-hold' => 0,
              'under-appeal' => 0
            ];

            // Count CASE statuses only for reports that were approved (i.e. promoted to cases)
            $stmtCounts = $pdo->query("SELECT LOWER(TRIM(case_status)) AS status, COUNT(*) AS cnt FROM spot_reports WHERE LOWER(TRIM(status)) = 'approved' GROUP BY LOWER(TRIM(case_status))");
            while ($r = $stmtCounts->fetch(PDO::FETCH_ASSOC)) {
              $s = strtolower(trim($r['status'] ?? ''));
              $c = (int)$r['cnt'];
              if ($s === '') {
                $counts['under-investigation'] += $c;
              } elseif (strpos($s, 'under') !== false && (strpos($s, 'invest') !== false || strpos($s, 'review') === false)) {
                $counts['under-investigation'] += $c;
              } elseif (strpos($s, 'pending') !== false || strpos($s, 'pending review') !== false) {
                $counts['pending-review'] += $c;
              } elseif (strpos($s, 'for filing') !== false || strpos($s, 'for-filing') !== false) {
                $counts['for-filing'] += $c;
              } elseif (strpos($s, 'filed') !== false || strpos($s, 'filed in court') !== false || strpos($s, 'filed-in-court') !== false) {
                $counts['filed-in-court'] += $c;
              } elseif (strpos($s, 'ongoing') !== false || strpos($s, 'trial') !== false) {
                $counts['ongoing-trial'] += $c;
              } elseif (strpos($s, 'dismiss') !== false) {
                $counts['dismissed'] += $c;
              } elseif (strpos($s, 'resolv') !== false || strpos($s, 'resolved') !== false) {
                $counts['resolved'] += $c;
              } elseif (strpos($s, 'archiv') !== false) {
                $counts['archived'] += $c;
              } elseif (strpos($s, 'hold') !== false) {
                $counts['on-hold'] += $c;
              } elseif (strpos($s, 'appeal') !== false) {
                $counts['under-appeal'] += $c;
              } else {
                $counts['under-investigation'] += $c;
              }
            }

            // Fetch approved spot reports to show as cases
            $stmt = $pdo->prepare("SELECT s.id, s.reference_no, s.incident_datetime, s.location, s.team_leader, u.full_name AS submitted_by_name, s.status, s.case_status, (SELECT SUM(value) FROM spot_report_items WHERE report_id = s.id) AS est_value FROM spot_reports s LEFT JOIN users u ON u.id = s.submitted_by WHERE LOWER(TRIM(s.status)) = 'approved' ORDER BY s.created_at DESC");
            $stmt->execute();
            $approvedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          } catch (Exception $e) {
            $counts = array_fill_keys(array_keys($counts), 0);
            $approvedRows = [];
          }
          ?>
          <div class="summary-pills mb-3">
            <div class="summary-pill"><div class="pill-label">Under Inv.</div><div class="pill-count" id="count-under-investigation"><?php echo htmlspecialchars($counts['under-investigation']); ?></div></div>
            <div class="summary-pill"><div class="pill-label">Pend. Rev.</div><div class="pill-count" id="count-pending-review"><?php echo htmlspecialchars($counts['pending-review']); ?></div></div>
            <div class="summary-pill"><div class="pill-label">For Filing</div><div class="pill-count" id="count-for-filing"><?php echo htmlspecialchars($counts['for-filing']); ?></div></div>
            <div class="summary-pill"><div class="pill-label">Filed Ct.</div><div class="pill-count" id="count-filed-in-court"><?php echo htmlspecialchars($counts['filed-in-court']); ?></div></div>
            <div class="summary-pill"><div class="pill-label">Ong. Trial</div><div class="pill-count" id="count-ongoing-trial"><?php echo htmlspecialchars($counts['ongoing-trial']); ?></div></div>
            <div class="summary-pill"><div class="pill-label">Resolved</div><div class="pill-count" id="count-resolved"><?php echo htmlspecialchars($counts['resolved']); ?></div></div>
            <div class="summary-pill"><div class="pill-label">Dismissed</div><div class="pill-count" id="count-dismissed"><?php echo htmlspecialchars($counts['dismissed']); ?></div></div>
            <div class="summary-pill"><div class="pill-label">Archived</div><div class="pill-count" id="count-archived"><?php echo htmlspecialchars($counts['archived']); ?></div></div>
            <div class="summary-pill"><div class="pill-label">On Hold</div><div class="pill-count" id="count-on-hold"><?php echo htmlspecialchars($counts['on-hold']); ?></div></div>
            <div class="summary-pill"><div class="pill-label">Under Appeal</div><div class="pill-count" id="count-under-appeal"><?php echo htmlspecialchars($counts['under-appeal']); ?></div></div>
          </div>

          <!-- Cases Table -->
          <div class="card">
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Ref No.</th>
                      <th>Incident Date</th>
                      <th>Location</th>
                      <th>Team Leader</th>
                      <th>Submitted By</th>
                      <th>Review</th>
                      <th>Status</th>
                      <th>Est. Value</th>
                      <th>Details</th>
                    </tr>
                  </thead>
                  <tbody id="casesTableBody">
                    <?php
                    if (!empty($approvedRows)) {
                      foreach ($approvedRows as $r) {
                        $ref = htmlspecialchars($r['reference_no'] ?? '');
                        if (!empty($r['incident_datetime'])) {
                          try {
                            $dt = new DateTime($r['incident_datetime']);
                            $inc = htmlspecialchars($dt->format('m/d/Y h:i A'));
                          } catch (Exception $e) {
                            $inc = htmlspecialchars($r['incident_datetime']);
                          }
                        } else {
                          $inc = '-';
                        }
                        $loc = htmlspecialchars($r['location'] ?? '');
                        $tl = htmlspecialchars($r['team_leader'] ?? '');
                        $submittedBy = htmlspecialchars($r['submitted_by_name'] ?? '-');
                        $statusRaw = strtolower(trim($r['status'] ?? ''));
                        $caseStatusRaw = strtolower(trim($r['case_status'] ?? ''));

                        // Review badge (report approval status)
                        $reviewBadgeClass = 'bg-secondary';
                        if ($statusRaw === 'approved') {
                          $reviewBadgeClass = 'bg-success';
                        } elseif (in_array($statusRaw, ['pending', 'for review', 'under review'])) {
                          $reviewBadgeClass = 'bg-warning';
                        } elseif (in_array($statusRaw, ['rejected', 'denied'])) {
                          $reviewBadgeClass = 'bg-danger';
                        }

                        // Case status badge (case lifecycle)
                        $caseBadgeClass = 'bg-secondary';
                        $hasCaseStatus = isset($r['case_status']) && trim((string)$r['case_status']) !== '';
                        if ($hasCaseStatus) {
                          if (in_array($caseStatusRaw, ['under investigation','under-investigation','under_review','under review'])) {
                            $caseBadgeClass = 'bg-primary';
                          } elseif (in_array($caseStatusRaw, ['for filing','for-filing'])) {
                            $caseBadgeClass = 'bg-warning';
                          } elseif (in_array($caseStatusRaw, ['ongoing','ongoing-trial','ongoing trial'])) {
                            $caseBadgeClass = 'bg-info';
                          } elseif (in_array($caseStatusRaw, ['filed in court','filed-in-court','filed'])) {
                            $caseBadgeClass = 'bg-secondary';
                          } elseif ($caseStatusRaw === 'dismissed') {
                            $caseBadgeClass = 'bg-danger';
                          } elseif ($caseStatusRaw === 'resolved') {
                            $caseBadgeClass = 'bg-success';
                          } elseif ($caseStatusRaw === 'archived') {
                            $caseBadgeClass = 'bg-dark';
                          }
                        } else {
                          // No explicit case_status set: if the report itself is already approved,
                          // show the default case lifecycle color (Under Investigation = blue)
                          if ($statusRaw === 'approved') {
                            $caseBadgeClass = 'bg-primary';
                          } else {
                            $caseBadgeClass = 'bg-secondary';
                          }
                        }
                        $estRaw = isset($r['est_value']) ? $r['est_value'] : null;
                        $est = ($estRaw !== null && $estRaw !== '') ? ('₱ ' . number_format((float)$estRaw, 2)) : '-';
                        $viewUrl = 'case_details.php?ref=' . urlencode($r['reference_no']);
                        $editUrl = 'case_detailsupdate.php?id=' . urlencode($r['reference_no']);
                        echo "<tr>\n";
                        echo "  <td>$ref</td>\n";
                        echo "  <td>" . $inc . "</td>\n";
                        echo "  <td>" . $loc . "</td>\n";
                        echo "  <td>" . $tl . "</td>\n";
                        echo "  <td>" . $submittedBy . "</td>\n";
                        echo "  <td><span class=\"badge $reviewBadgeClass\">" . htmlspecialchars($r['status'] ?? '') . "</span></td>\n";
                        // Show the case's official status (if set), otherwise default to 'Under Investigation'
                        $displayCaseStatus = $r['case_status'] ?? 'Under Investigation';
                        echo "  <td><span class=\"badge $caseBadgeClass\">" . htmlspecialchars($displayCaseStatus) . "</span></td>\n";
                        echo "  <td>" . $est . "</td>\n";
                        echo "  <td><a href=\"$viewUrl\" class=\"btn btn-sm btn-outline-primary\" title=\"View Details\">View</a></td>\n";
                        echo "</tr>\n";
                      }
                    } else {
                      echo '<tr><td colspan="9" class="text-center">No approved cases found.</td></tr>';
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

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Admin Dashboard JavaScript -->
  <script src="../../../../public/assets/js/admin/dashboard.js"></script>
  <!-- Admin Navigation JavaScript -->
  <script src="../../../../public/assets/js/admin/navigation.js"></script>
  <!-- Case Management JavaScript -->
  <script src="../../../../public/assets/js/admin/case-management.js"></script>

  <!-- Case Management Functionality -->
  <script>
    // Initialize page functionality and client-side filters
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Case Management page initialized');

      // Add hover effects to action buttons
      const actionButtons = document.querySelectorAll('.btn-outline-secondary');
      actionButtons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
          this.style.transform = 'scale(1.1)';
          this.style.transition = 'transform 0.2s ease';
        });

        btn.addEventListener('mouseleave', function() {
          this.style.transform = 'scale(1)';
        });
      });

      function parseDateOnly(str) {
        if (!str) return null;
        const m = str.match(/(\d{4}-\d{2}-\d{2})/);
        if (m) return new Date(m[1]);
        const d = new Date(str);
        return isNaN(d.getTime()) ? null : d;
      }

      function parseCurrencyToNumber(text) {
        if (!text) return 0;
        const cleaned = text.replace(/[^0-9.\-]/g, '');
        const n = parseFloat(cleaned);
        return isNaN(n) ? 0 : n;
      }

      function applyFilters() {
        const searchTerm = (document.getElementById('searchInput').value || '').trim().toLowerCase();
        const dateFromVal = document.getElementById('dateFrom').value;
        const dateToVal = document.getElementById('dateTo').value;
        const statusVal = (document.getElementById('statusFilter').value || '').trim().toLowerCase();

        const dateFrom = dateFromVal ? new Date(dateFromVal) : null;
        const dateTo = dateToVal ? new Date(dateToVal) : null;
        if (dateTo) dateTo.setHours(23,59,59,999);

        const rows = document.querySelectorAll('#casesTableBody tr');
        let visibleCount = 0;
        const counts = {
          'under-investigation': 0,
          'pending-review': 0,
          'for-filing': 0,
          'filed-in-court': 0,
          'ongoing-trial': 0,
          'resolved': 0,
          'dismissed': 0,
          'archived': 0,
          'on-hold': 0,
          'under-appeal': 0
        };
        let estSum = 0;

        rows.forEach(row => {
          if (row.querySelector('td') && row.querySelector('td').getAttribute('colspan')) return;
          const cells = row.cells;
          if (!cells) return;

          const ref = (cells[0].textContent || '').toLowerCase();
          const incText = (cells[1].textContent || '').trim();
          const loc = (cells[2].textContent || '').toLowerCase();
          const teamLeader = (cells[3] ? (cells[3].textContent||'') : '').toLowerCase();
          const submittedBy = (cells[4] ? (cells[4].textContent||'') : '').toLowerCase();
          const reviewText = (cells[5] ? (cells[5].textContent||'') : '').toLowerCase();
          const caseStatusText = (cells[6] ? (cells[6].textContent||'') : '').toLowerCase();
          const estText = (cells[7] ? (cells[7].textContent||'') : '').trim();

          let visible = true;

          if (searchTerm) {
            const hay = ref + ' ' + loc + ' ' + teamLeader + ' ' + submittedBy + ' ' + reviewText + ' ' + caseStatusText;
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
            if (!caseStatusText.includes(norm)) visible = false;
          }

          if (visible) {
            row.style.display = '';
            visibleCount++;
            estSum += parseCurrencyToNumber(estText);

            if (caseStatusText.includes('under') || caseStatusText.includes('invest')) counts['under-investigation']++;
            else if (caseStatusText.includes('pending')) counts['pending-review']++;
            else if (caseStatusText.includes('for filing') || caseStatusText.includes('for-filing')) counts['for-filing']++;
            else if (caseStatusText.includes('filed') || caseStatusText.includes('filed in court') || caseStatusText.includes('filed-in-court')) counts['filed-in-court']++;
            else if (caseStatusText.includes('ongoing') || caseStatusText.includes('trial')) counts['ongoing-trial']++;
            else if (caseStatusText.includes('dismiss')) counts['dismissed']++;
            else if (caseStatusText.includes('resolv')) counts['resolved']++;
            else if (caseStatusText.includes('archiv')) counts['archived']++;
            else if (caseStatusText.includes('hold')) counts['on-hold']++;
            else if (caseStatusText.includes('appeal')) counts['under-appeal']++;
          } else {
            row.style.display = 'none';
          }
        });

        document.getElementById('count-under-investigation').textContent = counts['under-investigation'];
        document.getElementById('count-pending-review').textContent = counts['pending-review'];
        document.getElementById('count-for-filing').textContent = counts['for-filing'];
        document.getElementById('count-filed-in-court').textContent = counts['filed-in-court'];
        document.getElementById('count-ongoing-trial').textContent = counts['ongoing-trial'];
        document.getElementById('count-resolved').textContent = counts['resolved'];
        document.getElementById('count-dismissed').textContent = counts['dismissed'];
        document.getElementById('count-archived').textContent = counts['archived'];
        document.getElementById('count-on-hold').textContent = counts['on-hold'];
        document.getElementById('count-under-appeal').textContent = counts['under-appeal'];

        const tbody = document.getElementById('casesTableBody');
        if (tbody) {
          const placeholder = tbody.querySelector('tr[data-placeholder]');
          if (visibleCount === 0) {
            if (!placeholder) {
              const nr = document.createElement('tr');
              nr.setAttribute('data-placeholder','1');
              nr.innerHTML = '<td colspan="9" class="text-center">No approved cases found.</td>';
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
      if (searchInput) searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); applyFilters(); }
      });

    });
  </script>
</body>
</html>