<!-- ===== ADMIN SIDEBAR NAVIGATION ===== -->
<nav class="sidebar" role="navigation" aria-label="Main sidebar">
  <div class="sidebar-logo">
    <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo">
    <span>CENRO</span>
  </div>
  <div class="sidebar-role">Administrator</div>
  <nav class="sidebar-nav" aria-label="Sidebar menu">
    <ul>
      <!-- Single Menu Items -->
      <li class="<?= ($current_page == 'dashboard') ? 'active' : '' ?>">
        <a href="dashboard.php">
          <i class="fa fa-th-large"></i> Dashboard
        </a>
      </li>
      
      <li class="<?= ($current_page == 'user_management') ? 'active' : '' ?>">
        <a href="user_management.php">
          <i class="fa fa-users"></i> User Management
        </a>
      </li>
      
      <li class="<?= ($current_page == 'spot_reports') ? 'active' : '' ?>">
        <a href="spot_reports.php">
          <i class="fa fa-file-text"></i> Spot Reports
        </a>
      </li>
      
      <li class="<?= ($current_page == 'case_management') ? 'active' : '' ?>">
        <a href="case_management.php">
          <i class="fa fa-briefcase"></i> Case Management
        </a>
      </li>
      
      <li class="<?= ($current_page == 'apprehended_items') ? 'active' : '' ?>">
        <a href="apprehended_items.php">
          <i class="fa fa-archive"></i> Apprehended Items
        </a>
      </li>
      
      <li class="<?= ($current_page == 'equipment_management') ? 'active' : '' ?>">
        <a href="equipment_management.php">
          <i class="fa fa-cogs"></i> Equipment Management
        </a>
      </li>
      
      <li class="<?= ($current_page == 'assignments') ? 'active' : '' ?>">
        <a href="assignments.php">
          <i class="fa fa-tasks"></i> Assignments
        </a>
      </li>
      
      <!-- Service Desk Accordion -->
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" id="serviceDeskToggle" data-target="serviceDeskMenu">
          <i class="fa fa-headset"></i> Service Desk 
          <i class="fa fa-chevron-down dropdown-arrow"></i>
        </a>
        <ul class="dropdown-menu" id="serviceDeskMenu">
          <li class="<?= ($current_page == 'new_requests') ? 'active' : '' ?>">
            <a href="new_requests.php">New Requests <span class="badge">2</span></a>
          </li>
          <li class="<?= ($current_page == 'ongoing_scheduled') ? 'active' : '' ?>">
            <a href="ongoing_scheduled.php">Ongoing / Scheduled <span class="badge badge-blue">2</span></a>
          </li>
          <li class="<?= ($current_page == 'completed') ? 'active' : '' ?>">
            <a href="completed.php">Completed</a>
          </li>
          <li class="<?= ($current_page == 'all_requests') ? 'active' : '' ?>">
            <a href="all_requests.php">All Requests</a>
          </li>
        </ul>
      </li>
      
      <li class="<?= ($current_page == 'statistical_report') ? 'active' : '' ?>">
        <a href="statistical_report.php">
          <i class="fa fa-chart-bar"></i> Statistical Report
        </a>
      </li>
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" id="serviceDeskToggle" data-target="serviceDeskMenu">
          <i class="fa fa-headset"></i> Service Desk 
          <i class="fa fa-chevron-down dropdown-arrow"></i>
        </a>
        <ul class="dropdown-menu" id="serviceDeskMenu">
          <li class="<?= ($current_page == 'new_requests') ? 'active' : '' ?>">
            <a href="new_requests.php">New Requests <span class="badge">2</span></a>
          </li>
          <li class="<?= ($current_page == 'ongoing_scheduled') ? 'active' : '' ?>">
            <a href="ongoing_scheduled.php">Ongoing / Scheduled <span class="badge badge-blue">2</span></a>
          </li>
          <li class="<?= ($current_page == 'completed') ? 'active' : '' ?>">
            <a href="completed.php">Completed</a>
          </li>
          <li class="<?= ($current_page == 'all_requests') ? 'active' : '' ?>">
            <a href="all_requests.php">All Requests</a>
          </li>
        </ul>
      </li>
    </ul>
  </nav>
</nav>