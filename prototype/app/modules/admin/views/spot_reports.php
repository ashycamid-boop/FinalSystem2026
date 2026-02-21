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
  <title>Spot Reports</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Admin common styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <!-- Spot Reports specific styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/spot-reports.css">
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
          <li><a href="user_management.php"><i class="fa fa-users"></i> User Management</a></li>
          <li class="active"><a href="spot_reports.php"><i class="fa fa-file-text"></i> Spot Reports</a></li>
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
          <div class="topbar-title">Spot Reports</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <!-- Spot Reports Content -->
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
                  <option value="approved">Approved</option>
                  <option value="pending">Pending</option>
                  <option value="rejected">Rejected</option>
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
          try {
            require_once dirname(dirname(dirname(__DIR__))) . '/config/db.php'; // loads $pdo
            $stmt = $pdo->prepare("SELECT s.id, s.reference_no, s.incident_datetime, s.location, s.summary, s.team_leader, s.custodian, s.status, s.status_comment, u.full_name AS submitted_by_name, (SELECT SUM(value) FROM spot_report_items WHERE report_id = s.id) AS est_value FROM spot_reports s LEFT JOIN users u ON u.id = s.submitted_by WHERE u.role = 'Enforcer' ORDER BY s.created_at DESC");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          } catch (Exception $e) {
            $rows = array();
          }

          function short_text($s, $len = 120) {
            $s = trim(strip_tags((string)$s));
            if (mb_strlen($s) <= $len) return $s;
            return mb_substr($s, 0, $len) . '...';
          }

          $totalReports = count($rows);
          $totalEst = 0.0;
          foreach ($rows as $r) {
            $estRaw = isset($r['est_value']) ? $r['est_value'] : null;
            $totalEst += ($estRaw !== null && $estRaw !== '') ? (float)$estRaw : 0.0;
          }
          $totalEstFormatted = $totalReports > 0 ? '₱ ' . number_format($totalEst, 2) : '-';
          ?>

          <div class="row mb-3">
            <div class="col-md-6 mb-3">
              <div class="summary-card">
                <div class="summary-label">Total</div>
                <div id="summaryTotal" class="summary-value"><?php echo $totalReports; ?></div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="summary-card">
                <div class="summary-label">Est. Value</div>
                <div id="summaryEst" class="summary-value"><?php echo $totalEstFormatted; ?></div>
              </div>
            </div>
          </div>

          <!-- Reports Table -->
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
                      <th>Details</th>
                      <!-- Actions column removed -->
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    if (!empty($rows)) {
                      foreach ($rows as $r) {
                        $ref = htmlspecialchars($r['reference_no']);
                        $inc = $r['incident_datetime'] ? htmlspecialchars($r['incident_datetime']) : '-';
                        $loc = htmlspecialchars($r['location'] ?? '');
                        $sum = htmlspecialchars(short_text($r['summary'] ?? ''));
                        $tl = htmlspecialchars($r['team_leader'] ?? '');
                        $cust = htmlspecialchars($r['custodian'] ?? '');
                        $submittedBy = htmlspecialchars($r['submitted_by_name'] ?? '-');
                        $statusRaw = strtolower(trim($r['status'] ?? ''));
                        $badgeClass = 'bg-secondary';
                        if ($statusRaw === 'approved') $badgeClass = 'bg-success';
                        elseif ($statusRaw === 'pending') $badgeClass = 'bg-warning';
                        elseif ($statusRaw === 'rejected') $badgeClass = 'bg-danger';
                        elseif ($statusRaw === 'under_review' || $statusRaw === 'under review') $badgeClass = 'bg-info';
                        $status = htmlspecialchars($r['status'] ?? '');
                        $statusComment = isset($r['status_comment']) ? $r['status_comment'] : '';
                        $estRaw = isset($r['est_value']) ? $r['est_value'] : null;
                        $est = ($estRaw !== null && $estRaw !== '') ? ('₱ ' . number_format((float)$estRaw, 2)) : '-';
                        $viewUrl = 'view_spot_report.php?ref=' . urlencode($r['reference_no']);
                        echo "<tr>\n";
                        echo "  <td><a href=\"$viewUrl\">$ref</a></td>\n";
                        echo "  <td>$inc</td>\n";
                        echo "  <td>$loc</td>\n";
                        echo "  <td>$sum</td>\n";
                        echo "  <td>$tl</td>\n";
                        echo "  <td>$cust</td>\n";
                        echo "  <td>$submittedBy</td>\n";
                        // If there's a status comment, show a small '?' button next to the badge
                        $commentHtml = '';
                        if (!empty($statusComment)) {
                          $commentAttr = htmlspecialchars($statusComment, ENT_QUOTES);
                          $commentHtml = " <button type=\"button\" class=\"status-comment-btn\" data-comment=\"{$commentAttr}\" title=\"View comment\" style=\"display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;margin-left:8px;vertical-align:middle;border:1px solid #ced4da;border-radius:6px;background:#fff;color:#495057;padding:0;\"><i class=\"fa fa-question-circle\"></i></button>";
                        }
                        echo "  <td><span class=\"badge $badgeClass\">$status</span>$commentHtml</td>\n";
                        echo "  <td>$est</td>\n";
                        echo "  <td><a class=\"btn btn-sm btn-outline-primary\" href=\"$viewUrl\">Details</a></td>\n";
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

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Admin Dashboard JavaScript -->
  <script src="../../../../public/assets/js/admin/dashboard.js"></script>
  <!-- Admin Navigation JavaScript -->
  <script src="../../../../public/assets/js/admin/navigation.js"></script>

  <!-- Spot Reports Action Functionality -->
  <script>
    // Delegated handler for status comment icon — show Bootstrap modal like enforcement officer
    function showStatusCommentModal(text) {
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
      const btn = e.target.closest && e.target.closest('.status-comment-btn');
      if (!btn) return;
      e.stopPropagation();
      const txt = btn.getAttribute('data-comment') || '';
      showStatusCommentModal(txt);
    });

    function editSpotReportStatus(reportId) {
      // Create modal for editing status
      const modalHtml = `
        <div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editStatusModalLabel">Edit Status - ${reportId}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="editStatusForm">
                  <div class="mb-3">
                    <label for="statusSelect" class="form-label">Select Status:</label>
                    <select class="form-select" id="statusSelect" name="status">
                      <option value="pending" data-class="bg-warning">Pending</option>
                      <option value="approved" data-class="bg-success">Approved</option>
                      <option value="rejected" data-class="bg-danger">Rejected</option>
                      <option value="under_review" data-class="bg-info">Under Review</option>
                    </select>
                  </div>
                  <!-- comments removed per request -->
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateSpotReportStatus('${reportId}')">
                  <i class="fa fa-save me-2"></i>Update Status
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
      
      // Remove existing modal if any
      const existingModal = document.getElementById('editStatusModal');
      if (existingModal) {
        existingModal.remove();
      }
      
      // Add modal to page
      document.body.insertAdjacentHTML('beforeend', modalHtml);
      
      // Show modal
      const modal = new bootstrap.Modal(document.getElementById('editStatusModal'));
      modal.show();
      
      // Set current status as selected
      const row = document.querySelector(`[onclick*="${reportId}"]`).closest('tr');
      const currentStatusText = row.querySelector('td:nth-child(8) .badge').textContent.trim().toLowerCase();
      const statusSelect = document.getElementById('statusSelect');
      statusSelect.value = currentStatusText;
    }

    function updateSpotReportStatus(reportId) {
      const statusSelect = document.getElementById('statusSelect');
      const selectedOption = statusSelect.options[statusSelect.selectedIndex];
      const newStatus = statusSelect.value;
      const badgeClass = selectedOption.getAttribute('data-class');
      const statusText = selectedOption.text;

      const confirmMessage = `Are you sure you want to change the status to "${statusText}"?`;

      if (!confirm(confirmMessage)) return;

      // Close modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('editStatusModal'));
      modal.hide();

      // Find the edit button and show loading state
      const editButton = document.querySelector(`[onclick*="${reportId}"]`);
      const originalContent = editButton ? editButton.innerHTML : null;
      if (editButton) {
        editButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        editButton.disabled = true;
      }

      // Map frontend values to DB-stored status labels
      const statusMap = {
        'pending': 'Pending',
        'approved': 'Approved',
        'rejected': 'Rejected',
        'under_review': 'Under Review'
      };
      const payloadStatus = statusMap[newStatus] || statusText || newStatus;

      // Call backend to persist status
      fetch('../update_spot_report_status.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ref: reportId, status: payloadStatus })
      })
      .then(r => r.json())
      .then(data => {
        if (data && data.success) {
          // Update the status badge in the same row
          if (editButton) {
            const row = editButton.closest('tr');
            const statusCell = row.querySelector('td:nth-child(8)'); // Status column
            if (statusCell) {
              statusCell.innerHTML = `<span class="badge ${badgeClass}">${payloadStatus}</span>`;
            }
          }
          showActionMessage(`Spot report ${reportId} status updated to "${statusText}" successfully!`, 'success');
        } else {
          showActionMessage(data.message || 'Failed to update status', 'danger');
        }
      })
      .catch(err => {
        console.error(err);
        showActionMessage('Network or server error while updating status', 'danger');
      })
      .finally(() => {
        if (editButton) {
          editButton.innerHTML = originalContent;
          editButton.disabled = false;
        }
      });
    }
    
    function showActionMessage(message, type = 'info') {
      // Remove existing alerts
      const existingAlert = document.querySelector('.action-alert');
      if (existingAlert) {
        existingAlert.remove();
      }
      
      // Create new alert
      const alertDiv = document.createElement('div');
      alertDiv.className = `alert alert-${type} alert-dismissible fade show action-alert`;
      alertDiv.style.position = 'fixed';
      alertDiv.style.top = '20px';
      alertDiv.style.right = '20px';
      alertDiv.style.zIndex = '9999';
      alertDiv.style.minWidth = '300px';
      alertDiv.innerHTML = `
        <i class="fa fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      
      // Add to page
      document.body.appendChild(alertDiv);
      
      // Auto-dismiss after 4 seconds
      setTimeout(() => {
        if (alertDiv.parentNode) {
          alertDiv.remove();
        }
      }, 4000);
    }
    
    // Initialize page functionality
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Spot Reports page initialized with action buttons');
      
      // Add hover effects to action buttons
      const actionButtons = document.querySelectorAll('.btn-group .btn');
      actionButtons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
          if (!this.disabled) {
            this.style.transform = 'scale(1.1)';
            this.style.transition = 'transform 0.2s ease';
          }
        });
        
        btn.addEventListener('mouseleave', function() {
          this.style.transform = 'scale(1)';
        });
      });
      
      // Initialize filter functionality if needed
      const applyFilterBtn = document.getElementById('applyFilter');
      const clearFilterBtn = document.getElementById('clearFilter');
      
      function parseDateOnly(str) {
        if (!str) return null;
        // Try to extract YYYY-MM-DD from the string
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
        if (dateTo) {
          // include whole day
          dateTo.setHours(23,59,59,999);
        }

        const rows = document.querySelectorAll('table tbody tr');
        let visibleCount = 0;
        let estSum = 0;

        rows.forEach(row => {
          // skip placeholder row with colspan
          if (row.querySelector('td') && row.querySelector('td').getAttribute('colspan')) return;

          const cells = row.cells;
          if (!cells || cells.length < 9) return;

          const ref = (cells[0].textContent || '').toLowerCase();
          const incText = (cells[1].textContent || '').trim();
          const loc = (cells[2].textContent || '').toLowerCase();
          const items = (cells[3].textContent || '').toLowerCase();
          const teamLeader = (cells[4].textContent || '').toLowerCase();
          const custodian = (cells[5].textContent || '').toLowerCase();
          const submittedBy = (cells[6].textContent || '').toLowerCase();
          const statusText = (cells[7].textContent || '').toLowerCase();
          const estText = (cells[8].textContent || '').trim();

          let visible = true;

          if (searchTerm) {
            const hay = ref + ' ' + loc + ' ' + items + ' ' + teamLeader + ' ' + custodian + ' ' + submittedBy;
            if (!hay.includes(searchTerm)) visible = false;
          }

          if (visible && (dateFrom || dateTo)) {
            const incDate = parseDateOnly(incText);
            if (!incDate) {
              visible = false;
            } else {
              if (dateFrom && incDate < dateFrom) visible = false;
              if (dateTo && incDate > dateTo) visible = false;
            }
          }

          if (visible && statusVal) {
            // Normalize status values (allow matches like 'under review')
            const norm = statusVal.replace(/_/g, ' ');
            if (!statusText.includes(norm)) visible = false;
          }

          if (visible) {
            row.style.display = '';
            visibleCount++;
            estSum += parseCurrencyToNumber(estText);
          } else {
            row.style.display = 'none';
          }
        });

        // Update summary cards
        const totalEl = document.getElementById('summaryTotal');
        const estEl = document.getElementById('summaryEst');
        if (totalEl) totalEl.textContent = visibleCount;
        if (estEl) estEl.textContent = (visibleCount > 0) ? ('₱ ' + estSum.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})) : '-';
      }

      if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
          applyFilters();
        });
      }

      if (clearFilterBtn) {
        clearFilterBtn.addEventListener('click', function() {
          document.getElementById('searchInput').value = '';
          document.getElementById('dateFrom').value = '';
          document.getElementById('dateTo').value = '';
          document.getElementById('statusFilter').value = '';
          applyFilters();
        });
      }

      // Allow pressing Enter in search box to apply filters
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
  </script>
</body>
</html>