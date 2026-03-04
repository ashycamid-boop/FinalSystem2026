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
  <title>Equipment Management</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Admin common styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <!-- Equipment Management specific styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/equipment-management.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Fredoka+One:400&display=swap" rel="stylesheet">
  <!-- Print Styles -->
  <style>
    @media print {
      @page { size: Legal landscape; margin: 2mm 10mm 10mm 10mm; }
      html, body {
        margin: 0;
        padding: 0;
        background: #fff;
      }
      body * {
        visibility: hidden;
      }
      .print-container,
      .print-container * {
        visibility: visible;
        font-size: 11px !important;
      }
      .print-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        font-family: "Times New Roman", Times, serif;
        color: #000;
        background: #fff;
        line-height: 1.15;
      }
      .no-print {
        display: none !important;
      }

      .print-header {
        margin: 0;
        background: #fff;
        border: none;
      }
      .print-logo-section {
        display: grid;
        grid-template-columns: 86px 1fr 86px;
        align-items: center;
        column-gap: 8px;
        padding: 6px 8px;
        border-bottom: 1px solid #4b5563;
      }
      .print-logo {
        width: 66px;
        height: 66px;
        object-fit: contain;
        justify-self: start;
      }
      .print-logo-right {
        justify-self: end;
      }
      .print-header-text {
        text-align: center;
        line-height: 1.15;
      }
      .print-header-text .line-1 {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
      }
      .print-header-text .line-2,
      .print-header-text .line-3 {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
      }
      .print-header-text .line-4 {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
      }
      .rp-center {
        text-align: center;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 6px 8px;
        border-bottom: 1px solid #4b5563;
      }
      .print-report-title {
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 6px 8px;
        margin: 0;
        border-bottom: 1px solid #4b5563;
      }
      .print-doc-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        gap: 8px;
        font-size: 8px;
        padding: 8px 6px 6px;
        border-bottom: 1px solid #4b5563;
      }
      .print-doc-meta > div:last-child {
        text-align: right;
      }

      .print-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-top: 0;
        border-left: 1px solid #4b5563;
        border-right: 1px solid #4b5563;
        border-bottom: 1px solid #4b5563;
      }
      .print-table th,
      .print-table td {
        border: 1px solid #4b5563;
        padding: 3px 4px;
        vertical-align: top;
        word-break: break-word;
        color: #000;
      }
      .print-table th {
        background: #fff;
        text-transform: uppercase;
        font-weight: 700;
        text-align: center;
        font-size: 7px;
        line-height: 1.05;
      }
      .print-table thead tr.group-row th {
        background: #fff;
        font-size: 7px;
        letter-spacing: 0.2px;
      }
      .print-table td {
        font-size: 7px;
        line-height: 1.1;
      }
      .print-table tbody tr {
        page-break-inside: avoid;
      }

      .print-footer {
        margin-top: 8px;
        padding-top: 0;
        border-top: none;
        font-size: 8px;
      }
      .print-footer > div:first-child {
        font-weight: 700;
      }
      .print-signatories {
        margin-top: 24px;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        column-gap: 14px;
      }
      .sig-box {
        text-align: center;
        font-size: 8px;
      }
      .sig-line {
        border-bottom: 1px solid #374151;
        height: 16px;
        margin: 0 0 6px;
      }
      .sig-role {
        text-transform: uppercase;
        font-weight: 700;
        font-size: 8px;
        letter-spacing: 0.2px;
      }

      .print-info-section,
      .print-divider,
      .print-summary,
      .signature-section,
      .print-watermark,
      .document-footer {
        display: none !important;
      }
    }
  </style>
  <style>
    /* Top action bar UI tweaks */
    .top-action-bar .input-group {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: nowrap;
    }

    .search-box #searchInput {
      border-radius: 28px;
      padding: 12px 18px;
      height: 48px;
      box-shadow: none;
      border: 1px solid #e0e0e0;
      flex: 1 1 360px; /* grow, shrink, preferred */
      min-width: 220px;
      font-size: 14px;
    }

    .search-box .input-group-text {
      background: transparent;
      border: none;
      color: #666;
      padding: 0 10px;
    }

    #statusFilter {
      border-radius: 8px;
      min-width: 180px;
      height: 44px;
      white-space: nowrap;
    }

    #clearFiltersBtn {
      border-radius: 8px;
      height: 44px;
      margin-left: 4px;
      white-space: nowrap;
      padding: 8px 12px;
    }

    #printQRCodesBtn, #printEquipmentListBtn {
      height: 48px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      white-space: nowrap;
    }

    #printEquipmentListBtn {
      border-radius: 40px;
      padding: 10px 26px;
      font-weight: 600;
    }

    /* Responsive: stack controls on small screens */
    @media (max-width: 991px) {
      .top-action-bar .d-flex { flex-wrap: wrap; }
    }

    @media (max-width: 767px) {
      .top-action-bar .input-group { flex-direction: column; align-items: stretch; }
      #statusFilter, #clearFiltersBtn, #printQRCodesBtn, #printEquipmentListBtn { width: 100%; margin-left: 0; }
      .search-box #searchInput { border-radius: 8px; }
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
      <div class="sidebar-role">Administrator</div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li><a href="user_management.php"><i class="fa fa-users"></i> User Management</a></li>
          <li><a href="spot_reports.php"><i class="fa fa-file-text"></i> Spot Reports</a></li>
          <li><a href="case_management.php"><i class="fa fa-briefcase"></i> Case Management</a></li>
          <li><a href="apprehended_items.php"><i class="fa fa-archive"></i> Apprehended Items</a></li>
          <li class="active"><a href="equipment_management.php"><i class="fa fa-cogs"></i> Equipment Management</a></li>
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
          <div class="topbar-title">Equipment Management</div>
          <?php include_once __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <div class="container-fluid">
          
          <!-- Top Action Bar -->
          <div class="top-action-bar mb-4">
            <div class="row align-items-center">
              <div class="col-md-8">
                <div class="d-flex gap-2">
                  <div class="search-box flex-grow-1">
                    <div class="input-group">
                      <input type="text" class="form-control" id="searchInput" placeholder="Search">
                      <span class="input-group-text d-none d-md-inline"><i class="fa fa-search"></i></span>
                      <select id="statusFilter" class="form-select" style="max-width:220px;" aria-label="Filter by status">
                        <option value="All">All Status</option>
                        <option value="Available">Available</option>
                        <option value="Assigned">Assigned</option>
                        <option value="Under Maintenance">Under Maintenance</option>
                        <option value="Damaged">Damaged</option>
                        <option value="Out of Service">Out of Service</option>
                      </select>
                      <button class="btn btn-outline-secondary" type="button" id="clearFiltersBtn" title="Clear filters">Clear</button>
                    </div>
                  </div>
                  <button class="btn btn-outline-dark" id="printQRCodesBtn">
                    <i class="fa fa-print me-2"></i>Print All QR Codes
                  </button>
                  <button class="btn btn-success" id="printEquipmentListBtn">
                    <i class="fa fa-file-pdf me-2"></i>Print Equipment List
                  </button>
                </div>
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="addNewDeviceBtn">
                  <i class="fa fa-plus me-2"></i>Add New Device
                </button>
              </div>
            </div>
          </div>

          <!-- Equipment Table -->
          <div class="equipment-table-section">
            <div class="table-responsive">
              <table class="table table-hover" id="equipmentTable">
                <thead class="table-light">
                  <tr>
                    <th>Asset ID</th>
                    <th>Property No.</th>
                    <th>Equipment Type</th>
                    <th>Brand</th>
                    <th>Year Acquired</th>
                    <th>Actual User</th>
                    <th>Accountable Person</th>
                    <th>Status</th>
                    <th>QR Code</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Equipment data will be populated dynamically via JavaScript/AJAX -->
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Add New Device Modal -->
  <div class="add-device-modal" id="addDeviceModal">
    <div class="modal-content" style="max-width: 900px; width: 90%;">
      <div class="modal-header">
        <h5 class="modal-title">Add New Equipment</h5>
        <button type="button" class="btn-close" id="closeAddDeviceModal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="addDeviceForm">
          <!-- Basic Information -->
          <h6 class="section-title">Basic Information</h6>
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label for="officeDevision" class="form-label">Office/Division</label>
                <input type="text" class="form-control" id="officeDevision" name="officeDevision">
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="equipmentType" class="form-label">Type of Equipment</label>
                <select class="form-select" id="equipmentType" name="equipmentType">
                  <option value="">Select Equipment Type</option>
                  <option value="Desktop Computer">Desktop Computer</option>
                  <option value="UPS">UPS</option>
                  <option value="Laptop Computers">Laptop Computers</option>
                  <option value="Printers">Printers</option>
                  <option value="Scanners">Scanners</option>
                  <option value="Storage Devices">Storage Devices</option>
                  <option value="Geotagging Devices">Geotagging Devices</option>
                  <option value="Cameras">Cameras</option>
                  <option value="Communication Equipment">Communication Equipment</option>
                  <option value="NVR">NVR</option>
                  <option value="CCTV / IP Camera">CCTV / IP Camera</option>
                  <option value="LCD Projectors">LCD Projectors</option>
                  <option value="Drones">Drones</option>
                  <option value="Interactive Kiosk / SmartTV">Interactive Kiosk / SmartTV</option>
                  <option value="Biometric Devices">Biometric Devices</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="yearAcquired" class="form-label">Year Acquired</label>
                <select class="form-select" id="yearAcquired" name="yearAcquired">
                  <option value="">Select Year</option>
                  <option value="2025">2025</option>
                  <option value="2024">2024</option>
                  <option value="2023">2023</option>
                  <option value="2022">2022</option>
                  <option value="2021">2021</option>
                  <option value="2020">2020</option>
                  <option value="2019">2019</option>
                  <option value="2018">2018</option>
                  <option value="2017">2017</option>
                  <option value="2016">2016</option>
                  <option value="2015">2015</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label for="shelfLife" class="form-label">Shelf Life</label>
                <select class="form-select" id="shelfLife" name="shelfLife">
                  <option value="">Select Shelf Life</option>
                  <option value="Beyond 5 Years">Beyond 5 Years</option>
                  <option value="Within 5 Years">Within 5 Years</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="brand" class="form-label">Brand</label>
                <input type="text" class="form-control" id="brand" name="brand">
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="model" class="form-label">Model</label>
                <input type="text" class="form-control" id="model" name="model">
              </div>
            </div>
          </div>

          <!-- Computer Specifications (For Desktop & Laptop) -->
          <h6 class="section-title">Computer Specifications (For Desktop & Laptop)</h6>
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label for="processor" class="form-label">Processor</label>
                <input type="text" class="form-control" id="processor" name="processor" placeholder="e.g. i7-10700T">
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="ramSize" class="form-label">Installed Memory RAM Size</label>
                <input type="text" class="form-control" id="ramSize" name="ramSize" placeholder="e.g. 8GB DDR4">
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="gpu" class="form-label">Installed GPU</label>
                <input type="text" class="form-control" id="gpu" name="gpu" placeholder="e.g. nvidia, shared graphics">
              </div>
            </div>
          </div>

          <!-- Software Information -->
          <h6 class="section-title">Software Information</h6>
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label for="osVersion" class="form-label">Operating System Version</label>
                <select class="form-select" id="osVersion" name="osVersion">
                  <option value="">Select OS</option>
                  <option value="Windows 11">Windows 11</option>
                  <option value="Windows 10">Windows 10</option>
                  <option value="Windows 8">Windows 8</option>
                  <option value="Windows 7">Windows 7</option>
                  <option value="Windows Server 2022">Windows Server 2022</option>
                  <option value="Windows Server 2019">Windows Server 2019</option>
                  <option value="Windows Server 2016">Windows Server 2016</option>
                  <option value="macOS">macOS</option>
                  <option value="Linux">Linux</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="officeProductivity" class="form-label">Office Productivity</label>
                <select class="form-select" id="officeProductivity" name="officeProductivity">
                  <option value="">Select Office Suite</option>
                  <option value="Microsoft Office 2016">Microsoft Office 2016</option>
                  <option value="Microsoft Office 2019">Microsoft Office 2019</option>
                  <option value="Microsoft Office 2021">Microsoft Office 2021</option>
                  <option value="Microsoft 365 (Office 365)">Microsoft 365 (Office 365)</option>
                  <option value="LibreOffice">LibreOffice</option>
                  <option value="Apache OpenOffice">Apache OpenOffice</option>
                  <option value="WPS Office (Free)">WPS Office (Free)</option>
                  <option value="Google Workspace (Docs, Sheets, Slides)">Google Workspace (Docs, Sheets, Slides)</option>
                  <option value="Trial Version">Trial Version</option>
                  <option value="Unactivated Office">Unactivated Office</option>
                  <option value="Crack / Counterfeit">Crack / Counterfeit</option>
                  <option value="None / N/A">None / N/A</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="endpointProtection" class="form-label">Endpoint Protection</label>
                <select class="form-select" id="endpointProtection" name="endpointProtection">
                  <option value="">Select Protection</option>
                  <option value="Windows Defender / Windows Firewall">Windows Defender / Windows Firewall</option>
                  <option value="Trend Micro">Trend Micro</option>
                  <option value="McAfee">McAfee</option>
                  <option value="Avast">Avast</option>
                  <option value="AVG">AVG</option>
                  <option value="Kaspersky">Kaspersky</option>
                  <option value="Norton / Symantec">Norton / Symantec</option>
                  <option value="Bitdefender">Bitdefender</option>
                  <option value="ESET NOD32">ESET NOD32</option>
                  <option value="None / N/A">None / N/A</option>
                  <option value="Expired License">Expired License</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Property Information -->
          <h6 class="section-title">Property Information</h6>
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label for="computerName" class="form-label">Computer Name</label>
                <input type="text" class="form-control" id="computerName" name="computerName">
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="serialNumber" class="form-label">Serial Number</label>
                <input type="text" class="form-control" id="serialNumber" name="serialNumber">
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="propertyNumber" class="form-label">Property Number</label>
                <input type="text" class="form-control" id="propertyNumber" name="propertyNumber">
              </div>
            </div>
          </div>
          <!-- Accountable Person -->
          <h6 class="section-title">Accountable Person</h6>
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label for="accountablePerson" class="form-label">Accountable Person</label>
                <select class="form-select" id="accountablePerson" name="accountablePerson">
                  <option value="">Select Person</option>
                  <!-- options will be populated dynamically by EquipmentService.getUsers() -->
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="accountableSex" class="form-label">Sex</label>
                <select class="form-select" id="accountableSex" name="accountableSex">
                  <option value="">Select Sex</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="accountableEmployment" class="form-label">Status of Employment</label>
                <select class="form-select" id="accountableEmployment" name="accountableEmployment">
                  <option value="">Select Status</option>
                  <option value="Permanent">Permanent</option>
                  <option value="Contract of Service / Job Order">Contract of Service / Job Order</option>
                  <option value="Job Order">Job Order</option>
                  <option value="Casual">Casual</option>
                  <option value="Probationary">Probationary</option>
                  <option value="Temporary">Temporary</option>
                  <option value="Part-Time">Part-Time</option>
                  <option value="Project-Based">Project-Based</option>
                  <option value="Consultant">Consultant</option>
                  <option value="Intern / OJT">Intern / OJT</option>
                  <option value="N/A">N/A</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Actual User -->
          <h6 class="section-title">Actual User</h6>
          <div class="row">
            <div class="col-md-3">
              <div class="mb-3">
                <label for="actualUser" class="form-label">Actual User</label>
                <select class="form-select" id="actualUser" name="actualUser">
                  <option value="">Select User</option>
                  <!-- options will be populated dynamically by EquipmentService.getUsers() -->
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label for="actualUserSex" class="form-label">Sex</label>
                <select class="form-select" id="actualUserSex" name="actualUserSex">
                  <option value="">Select Sex</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label for="actualUserEmployment" class="form-label">Status of Employment</label>
                <select class="form-select" id="actualUserEmployment" name="actualUserEmployment">
                  <option value="">Select Status</option>
                  <option value="Permanent">Permanent</option>
                  <option value="Contractual">Contractual</option>
                  <option value="Job Order">Job Order</option>
                  <option value="Casual">Casual</option>
                  <option value="Probationary">Probationary</option>
                  <option value="Temporary">Temporary</option>
                  <option value="Part-Time">Part-Time</option>
                  <option value="Project-Based">Project-Based</option>
                  <option value="Consultant">Consultant</option>
                  <option value="Intern / OJT">Intern / OJT</option>
                  <option value="N/A">N/A</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label for="natureOfWork" class="form-label">Nature of Work</label>
                <select class="form-select" id="natureOfWork" name="natureOfWork">
                  <option value="">Select Nature</option>
                  <option value="Administrative Works / Clerical">Administrative Works / Clerical</option>
                  <option value="Technical Works">Technical Works</option>
                  <option value="Field Works / Inspection">Field Works / Inspection</option>
                  <option value="Supervisory / Managerial">Supervisory / Managerial</option>
                  <option value="IT-Related / Computer-Based Tasks">IT-Related / Computer-Based Tasks</option>
                  <option value="Maintenance / Utility">Maintenance / Utility</option>
                  <option value="Research / Planning">Research / Planning</option>
                  <option value="Finance / Accounting">Finance / Accounting</option>
                  <option value="Human Resource / Personnel">Human Resource / Personnel</option>
                  <option value="Procurement / Supply">Procurement / Supply</option>
                  <option value="Customer Service / Frontline">Customer Service / Frontline</option>
                  <option value="Legal / Compliance">Legal / Compliance</option>
                  <option value="Training / Education">Training / Education</option>
                  <option value="N/A">N/A</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Remarks -->
          <div class="row">
            <div class="col-12">
              <div class="mb-3">
                <label for="remarks" class="form-label">Remarks</label>
                <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
              </div>
            </div>
          </div>
          <!-- Status (Add/Edit) - moved below Remarks -->
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                  <option value="Assigned" selected>Assigned</option>
                  <option value="Available">Available</option>
                  <option value="Under Maintenance">Under Maintenance</option>
                  <option value="Damaged">Damaged</option>
                  <option value="Out of Service">Out of Service</option>
                </select>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="cancelAddDeviceBtn">Cancel</button>
        <button type="button" class="btn btn-primary" id="addDeviceBtn">Add Equipment</button>
      </div>
    </div>
  </div>

  <!-- Equipment Details Modal -->
  <div class="equipment-details-modal" id="equipmentDetailsModal">
    <div class="modal-content" style="max-width: 1000px; width: 95%;">
      <div class="modal-header">
        <div class="d-flex align-items-center">
          <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo" class="modal-logo me-3" style="width: 50px; height: 50px;">
          <div class="header-text">
            <div class="dept-name" style="font-size: 14px; font-weight: bold;">Department of Environment and Natural Resources</div>
            <div class="dept-name" style="font-size: 12px;">Kagawaran ng Kapaligiran at Likas Yaman</div>
            <div class="region" style="font-size: 12px; color: #666;">Caraga Region</div>
            <div class="office" style="font-size: 12px; color: #666;">CENRO Nasipit, Agusan del Norte</div>
          </div>
        </div>
        <button type="button" class="btn-close" id="closeModal">&times;</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div class="property-title" style="text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; color: #333;">
          GOVERNMENT PROPERTY DETAILS
        </div>

        <!-- Read-only form layout matching Add New Equipment -->
        <form id="equipmentDetailsForm" autocomplete="off" novalidate>
          <!-- Basic Information -->
          <h6 class="section-title">Basic Information</h6>
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Asset ID</label>
                <input type="text" id="detailAssetId" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Property Number</label>
                <input type="text" id="detailPropertyNumber" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Office/Division</label>
                <input type="text" id="detailOfficeDevision" class="form-control" disabled>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Equipment Type</label>
                <input type="text" id="detailEquipmentType" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Year Acquired</label>
                <input type="text" id="detailYearAcquired" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Shelf Life</label>
                <input type="text" id="detailShelfLife" class="form-control" disabled>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Brand</label>
                <input type="text" id="detailBrand" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Model</label>
                <input type="text" id="detailModel" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Serial Number</label>
                <input type="text" id="detailSerialNumber" class="form-control" disabled>
              </div>
            </div>
          </div>

          <!-- Computer Specifications (For Desktop & Laptop) -->
          <h6 class="section-title">Computer Specifications (For Desktop & Laptop)</h6>
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Processor</label>
                <input type="text" id="detailProcessor" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Installed Memory RAM Size</label>
                <input type="text" id="detailRamSize" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Installed GPU</label>
                <input type="text" id="detailGpu" class="form-control" disabled>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Range Category</label>
                <input type="text" id="detailRangeCategory" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Computer Name</label>
                <input type="text" id="detailComputerName" class="form-control" disabled>
              </div>
            </div>
          </div>

          <!-- Software Information -->
          <h6 class="section-title">Software Information</h6>
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Operating System Version</label>
                <input type="text" id="detailOsVersion" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Office Productivity</label>
                <input type="text" id="detailOfficeProductivity" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Endpoint Protection</label>
                <input type="text" id="detailEndpointProtection" class="form-control" disabled>
              </div>
            </div>
          </div>

          <!-- Property Information -->
          <h6 class="section-title">Property Information</h6>
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Property Number (readonly)</label>
                <input type="text" id="detailPropertyNumber2" class="form-control" disabled style="display:none;">
                <!-- kept for exact structure parity; primary prop shown above -->
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Year Acquired (readonly)</label>
                <input type="text" id="detailYearAcquired2" class="form-control" disabled style="display:none;">
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Shelf Life (readonly)</label>
                <input type="text" id="detailShelfLife2" class="form-control" disabled style="display:none;">
              </div>
            </div>
          </div>

          <!-- Accountable Person -->
          <h6 class="section-title">Accountable Person</h6>
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Accountable Person</label>
                <input type="text" id="detailAccountablePerson" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Sex</label>
                <input type="text" id="detailAccountableSex" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Status of Employment</label>
                <input type="text" id="detailAccountableEmployment" class="form-control" disabled>
              </div>
            </div>
          </div>

          <!-- Actual User -->
          <h6 class="section-title">Actual User</h6>
          <div class="row">
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label">Actual User</label>
                <input type="text" id="detailActualUser" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label">Sex</label>
                <input type="text" id="detailActualUserSex" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label">Status of Employment</label>
                <input type="text" id="detailActualUserEmployment" class="form-control" disabled>
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label">Nature of Work</label>
                <input type="text" id="detailNatureOfWork" class="form-control" disabled>
              </div>
            </div>
          </div>

          <!-- Remarks -->
          <h6 class="section-title">Remarks</h6>
          <div class="row">
            <div class="col-12">
              <div class="mb-3">
                <label class="form-label">Remarks</label>
                <textarea id="detailRemarks" class="form-control" rows="3" disabled></textarea>
              </div>
            </div>
          </div>

          <!-- QR Code Section -->
          <div class="details-section text-center">
            <h6 class="section-header">QR Code</h6>
            <img src="../../../../public/assets/images/QR_Code.png" alt="Equipment QR Code" style="width: 150px; height: 150px;" id="detailQrCode">
            <div class="mt-2">
              <button class="btn btn-sm btn-outline-primary" type="button" onclick="printQRCode()">
                <i class="fa fa-print me-1"></i>Print QR Code
              </button>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeEquipmentDetails()">Close</button>
        <button type="button" class="btn btn-primary" onclick="printEquipmentDetails()">
          <i class="fa fa-print me-1"></i>Print Details
        </button>
      </div>
    </div>
  </div>

  <!-- Print Container (Hidden) -->
  <div class="print-container" id="printContainer">
    <div class="print-header">
      <div class="print-logo-section">
        <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo" class="print-logo">
        <div class="print-header-text">
          <div class="line-1">Republic of the Philippines</div>
          <div class="line-2">Department of Environment and Natural Resources</div>
          <div class="line-3">Caraga Region - CENRO Nasipit</div>
          <div class="line-4">Equipment Management Inventory</div>
        </div>
        <img src="../../../../public/assets/images/bagong-pilipinas-logo.png" alt="Bagong Pilipinas Logo" class="print-logo print-logo-right">
      </div>
      <div class="rp-center">RP GOVERNMENT PROPERTY</div>
      <div class="print-report-title">Equipment List Report</div>
      <div class="print-doc-meta">
        <div><strong>Form Code:</strong> CENRO-ICT-INV-01</div>
        <div><strong>Document Type:</strong> Government Property Inventory</div>
      </div>
    </div>

    <table class="print-table" id="printTable">
      <thead>
        <tr class="group-row">
          <th colspan="5">Equipment Details</th>
          <th colspan="8">Assignment Information</th>
          <th colspan="4">Technical and Lifecycle</th>
          <th colspan="2">Disposition</th>
        </tr>
        <tr>
          <th>Asset ID</th>
          <th>Property No.</th>
          <th>Type</th>
          <th>Brand / Model</th>
          <th>Year</th>
          <th>Office/Division</th>
          <th>Accountable Person</th>
          <th>A. Sex</th>
          <th>A. Employment</th>
          <th>Actual User</th>
          <th>U. Sex</th>
          <th>U. Employment</th>
          <th>Nature of Work</th>
          <th>Specs (Proc / RAM / GPU)</th>
          <th>Software / Protection</th>
          <th>Serial No.</th>
          <th>Shelf Life</th>
          <th>Status</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody id="printTableBody">
      </tbody>
    </table>

    <div class="print-footer" style="margin-top:12px;">
      <div style="font-size:11px;">
        <strong>Total Equipment Count:</strong> <span id="totalCount"></span>
        &nbsp;&nbsp;|&nbsp;&nbsp; <strong>Filter:</strong> <span id="printFilter"></span>
        &nbsp;&nbsp;|&nbsp;&nbsp; <strong>Generated:</strong> <span id="footerDate"></span>
      </div>
      <div class="print-signatories">
        <div class="sig-box">
          <div class="sig-line"></div>
          <div class="sig-role">Prepared By</div>
          <div>Property Custodian</div>
        </div>
        <div class="sig-box">
          <div class="sig-line"></div>
          <div class="sig-role">Checked By</div>
          <div>Administrative Officer</div>
        </div>
        <div class="sig-box">
          <div class="sig-line"></div>
          <div class="sig-role">Approved By</div>
          <div>CENRO Officer</div>
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
  <!-- Equipment Service -->
  <script src="../../../../public/assets/js/admin/equipment-service.js"></script>
  
  <!-- Equipment Management JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', async function() {
      let equipmentData = {};
      let usersData = [];

     // Helper: return first existing property value from a list of possible keys
     function getProp(obj, ...keys) {
       if (!obj) return '';
       for (const k of keys) {
         if (obj[k] !== undefined && obj[k] !== null && String(obj[k]).trim() !== '') return obj[k];
       }
       return '';
     }
    
      // load users first then equipment
      await loadUsers();
      await loadEquipmentData();

     // Helper: wrap service calls to avoid JSON parse errors when server returns HTML (session expired / login redirect)
     async function safeServiceCall(promise) {
       try {
         const res = await promise;

         // If service returned an object that indicates session expiry, handle it
         if (res && (res.sessionExpired === true || (typeof res.error === 'string' && res.error.toLowerCase().includes('session')))) {
          console.warn('Service indicates session expired or returned HTML:', res);
          // give user a short message then redirect to login page
          alert('Session expired or server returned an authentication page. You will be redirected to the login page.');
          window.location.href = '../../../../index.php'; // adjust if your login path differs
          return { error: 'session' , sessionExpired: true };
        }
 
         // If response is undefined/null, normalize
         if (res === undefined || res === null) {
           return { error: 'Empty response from server' };
         }
 
         return res;
       } catch (err) {
         console.error('Service call failed:', err);
         return { error: err && err.message ? err.message : 'Service call failed' };
       }
     }
 
      async function loadUsers() {
        const accountablePersonSelect = document.getElementById('accountablePerson');
        const actualUserSelect = document.getElementById('actualUser');

        if (accountablePersonSelect) accountablePersonSelect.innerHTML = '<option value="">Loading users...</option>';
        if (actualUserSelect) actualUserSelect.innerHTML = '<option value="">Loading users...</option>';

        try {
          let users = [];

          // 1) Try EquipmentService.getUsers() safely
          try {
            if (window.EquipmentService && typeof EquipmentService.getUsers === 'function') {
              const res = await EquipmentService.getUsers();
              console.log('EquipmentService.getUsers() ->', res);

              if (typeof res === 'string' && res.trim().startsWith('<')) {
                // service returned HTML (login redirect or page) — avoid JSON.parse error, will fallback below
                console.warn('getUsers returned HTML string; skipping JSON parse here.');
                console.warn('getUsers returned HTML string; skipping JSON parse here.');
              } else if (res && res.data && Array.isArray(res.data)) {
                users = res.data;
              } else if (Array.isArray(res)) {
                users = res;
              } else if (res && Array.isArray(res.users)) {
                users = res.users;
              }
            }
          } catch (e) {
            console.warn('EquipmentService.getUsers() failed:', e);
          }

          // 2) If empty, try a few known JSON endpoints (safe fetch + try parse)
          if (!users.length) {
            const endpoints = [
              '../../../../public/api/users.php',
              '/api/users.php',
              'user_management.php?format=json',
              'user_management.php' // will be parsed as HTML if JSON fails
            ];

            for (const ep of endpoints) {
              try {
                const resp = await fetch(ep, { credentials: 'same-origin' });
                if (!resp.ok) continue;
                const text = await resp.text();

                // try parse JSON first
                try {
                  const parsed = JSON.parse(text);
                  if (parsed && parsed.data && Array.isArray(parsed.data)) users = parsed.data;
                  else if (Array.isArray(parsed)) users = parsed;
                  if (users.length) break;
                } catch (jsonErr) {
                  // not JSON — if endpoint is HTML (like user_management.php), try parsing table rows
                  const doc = new DOMParser().parseFromString(text, 'text/html');
                  const rows = doc.querySelectorAll('table tbody tr');
                  if (rows && rows.length) {
                    rows.forEach(row => {
                      const cells = row.querySelectorAll('td');
                      if (cells.length >= 2) {
                        const id = cells[0].textContent.trim();
                        const name = cells[1].textContent.trim();
                        if (name) users.push({ id: id || null, full_name: name });
                      }
                    });
                    if (users.length) break;
                  }
                }
              } catch (fetchErr) {
                // ignore and try next endpoint
              }
            }
          }

          // Normalize users objects
          users = (users || []).map(u => {
            if (typeof u === 'string') return { id: null, full_name: u, sex: '' };
            return {
              id: u.id !== undefined ? u.id : (u.user_id !== undefined ? u.user_id : (u.id_user !== undefined ? u.id_user : null)),
              full_name: u.full_name || u.name || `${u.first_name || ''} ${u.last_name || ''}`.trim(),
              sex: u.sex || u.gender || ''
            };
          }).filter(u => u.full_name);

          // sort
          users.sort((a,b)=> (a.full_name||'').toLowerCase().localeCompare((b.full_name||'').toLowerCase()));

          usersData = users;
          populateUserDropdowns();
        } catch (err) {
          console.error('Error loading users (final):', err);
          usersData = [];
          if (accountablePersonSelect) accountablePersonSelect.innerHTML = '<option value="">Unable to load users</option>';
          if (actualUserSelect) actualUserSelect.innerHTML = '<option value="">Unable to load users</option>';
        }

        return usersData;
      }

      function populateUserDropdowns() {
        const accountablePersonSelect = document.getElementById('accountablePerson');
        const actualUserSelect = document.getElementById('actualUser');
        if (!accountablePersonSelect || !actualUserSelect) return;

        // default options
        accountablePersonSelect.innerHTML = '<option value="">Select Person</option>';
        actualUserSelect.innerHTML = '<option value="">Select User</option>';

        if (!usersData || usersData.length === 0) {
          const noOption1 = document.createElement('option');
          noOption1.value = '';
          noOption1.textContent = 'No users found';
          accountablePersonSelect.appendChild(noOption1);

          const noOption2 = document.createElement('option');
          noOption2.value = '';
          noOption2.textContent = 'No users found';
          actualUserSelect.appendChild(noOption2);

          // small helper link
          if (!document.getElementById('manageUsersHint')) {
            const hint = document.createElement('div');
            hint.id = 'manageUsersHint';
            hint.style.marginTop = '6px';
            hint.innerHTML = '<small>No users available. <a href="user_management.php">Open User Management</a> to add users.</small>';
            accountablePersonSelect.parentElement.appendChild(hint);
          }
          return;
        }

        // remove previous hint if present
        const oldHint = document.getElementById('manageUsersHint');
        if (oldHint) oldHint.remove();

        usersData.forEach(user => {
          const display = user.full_name;
          const value = (user.id !== null && user.id !== undefined && String(user.id) !== '') ? String(user.id) : display;

          const option1 = document.createElement('option');
          option1.value = value;
          option1.textContent = display;
          if (user.sex) option1.setAttribute('data-sex', user.sex);
          if (user.id !== undefined && user.id !== null) option1.setAttribute('data-id', String(user.id));
          accountablePersonSelect.appendChild(option1);

          const option2 = document.createElement('option');
          option2.value = value;
          option2.textContent = display;
          if (user.sex) option2.setAttribute('data-sex', user.sex);
          if (user.id !== undefined && user.id !== null) option2.setAttribute('data-id', String(user.id));
          actualUserSelect.appendChild(option2);
        });
      }

      async function loadEquipmentData(search = '', status = 'All') {
        try {
          // Map UI status values to backend/DB values when needed
          let queryStatus = status;
          if (String(status).toLowerCase() === 'assigned') queryStatus = 'In Use';
          const data = await safeServiceCall(EquipmentService.getAll(search, queryStatus));
          if (!data || data.error) {
            console.error('Unable to load equipment data:', data && data.error ? data.error : data);
            if (data && typeof data.error === 'string' && data.error.toLowerCase().includes('session')) {
              alert('Unable to load equipment. ' + data.error);
            }
            const tbody = document.querySelector('#equipmentTable tbody');
            tbody.innerHTML = '<tr><td colspan="10" class="text-center">Unable to load equipment</td></tr>';
            return;
          }
          
          equipmentData = {};
          const tbody = document.querySelector('#equipmentTable tbody');
          tbody.innerHTML = '';

          if (!Array.isArray(data) || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center">No equipment found</td></tr>';
            return;
          }

           data.forEach((equipment, index) => {
            equipmentData[equipment.id] = equipment;

            const statusClass = (equipment.status || '').toLowerCase().replace(/ /g, '-');
            const displayStatus = ((equipment.status || '').toString().toLowerCase() === 'in use') ? 'Assigned' : (equipment.status || '');

            // Determine QR code source: prefer stored qr_code_path (from DB), else fallback to generator
            let qrSrc = '';
            if (equipment.qr_code_path) {
              if (/^https?:\/\//i.test(equipment.qr_code_path)) {
                qrSrc = equipment.qr_code_path;
              } else if (equipment.qr_code_path.startsWith('/')) {
                qrSrc = equipment.qr_code_path;
              } else {
                qrSrc = '../../../../public/' + equipment.qr_code_path.replace(/^(\.\/|\/)/, '');
              }
            } else {
              const prop = equipment.property_number || '';
              qrSrc = `https://api.qrserver.com/v1/create-qr-code/?size=50x50&data=${encodeURIComponent(prop)}`;
            }

            const row = document.createElement('tr');
            row.innerHTML = `
              <td>${equipment.id}</td>
              <td>${equipment.property_number}</td>
              <td>${equipment.equipment_type || '-'}</td>
              <td>${equipment.brand || '-'}</td>
              <td>${equipment.year_acquired || '-'}</td>
              <td>${equipment.actual_user || '-'}</td>
              <td>${equipment.accountable_person || '-'}</td>
              <td><span class="badge status-${statusClass}">${displayStatus}</span></td>
              <td>
                <div class="qr-code-container text-center">
                  <a href="../../../../public/qr_view.php?id=${encodeURIComponent(equipment.id)}" target="_blank" title="Open details in new tab">
                    <img src="${qrSrc}" 
                         alt="QR Code" class="qr-code-img" style="width: 40px; height: 40px; cursor: pointer;">
                  </a>
                </div>
              </td>
              <td>
                <div class="action-buttons">
                  <button class="btn btn-sm btn-outline-primary view-details" data-id="${equipment.id}" title="View Details">
                    <i class="fa fa-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-secondary regenerate-qr" data-id="${equipment.id}" title="Regenerate QR">
                    <i class="fa fa-qrcode"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-success edit-equipment" data-id="${equipment.id}" title="Edit">
                    <i class="fa fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger delete-equipment" data-id="${equipment.id}" title="Delete">
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </td>
            `;
            tbody.appendChild(row);
          });

          // Reattach event listeners
          attachEventListeners();
        } catch (error) {
          console.error('Error loading equipment:', error);
        }
      }

      function attachEventListeners() {
        // View details
        document.querySelectorAll('.view-details').forEach(button => {
          button.addEventListener('click', async function() {
            const equipmentId = this.getAttribute('data-id');
            await viewEquipmentDetails(equipmentId);
          });
        });

        // Edit equipment
        document.querySelectorAll('.edit-equipment').forEach(button => {
          button.addEventListener('click', async function() {
            const equipmentId = this.getAttribute('data-id');
            await editEquipment(equipmentId);
          });
        });

        // Delete equipment
        document.querySelectorAll('.delete-equipment').forEach(button => {
          button.addEventListener('click', async function() {
            const equipmentId = this.getAttribute('data-id');
            await deleteEquipment(equipmentId);
          });
        });

        // Regenerate QR
        document.querySelectorAll('.regenerate-qr').forEach(button => {
          button.addEventListener('click', async function() {
            const equipmentId = this.getAttribute('data-id');
            if (!confirm('Regenerate QR for this equipment? This will overwrite existing QR image.')) return;
            const res = await safeServiceCall(EquipmentService.generateQR(equipmentId));
            if (!res || res.error || !res.success) {
              alert('Failed to generate QR: ' + (res && (res.error || JSON.stringify(res))));
              return;
            }
            alert('QR regenerated successfully. Refreshing list.');
            await loadEquipmentData();
          });
        });
      }

    // Helper to set detail fields for modal (handles inputs, textareas, selects and fallback to textContent)
    function setDetailField(id, value) {
      const el = document.getElementById(id);
      if (!el) return;
      const val = (value === null || value === undefined) ? '' : value;
      const tag = (el.tagName || '').toUpperCase();
      if (tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA') {
        el.value = val;
      } else {
        el.textContent = val;
      }
    }

    async function viewEquipmentDetails(id) {
      const equipment = equipmentData[id];
      if (!equipment) return;

      // Populate read-only form fields using helper
      setDetailField('detailAssetId', equipment.id || '');
      setDetailField('detailPropertyNumber', getProp(equipment, 'property_number', 'propertyNumber') || '');
      // support different possible server keys: office_division, office_devision, officeDevision, officeDivision
      setDetailField('detailOfficeDevision', getProp(equipment, 'office_division', 'office_devision', 'officeDevision', 'officeDivision') || '');
      setDetailField('detailEquipmentType', equipment.equipment_type || '');
      setDetailField('detailYearAcquired', equipment.year_acquired || '');
      setDetailField('detailShelfLife', equipment.shelf_life || '');
      setDetailField('detailBrand', equipment.brand || '');
      setDetailField('detailModel', equipment.model || '');
      setDetailField('detailProcessor', equipment.processor || '');
      setDetailField('detailRamSize', equipment.ram_size || '');
      setDetailField('detailGpu', equipment.gpu || '');
      setDetailField('detailRangeCategory', equipment.range_category || '');
      setDetailField('detailComputerName', equipment.computer_name || '');
      setDetailField('detailOsVersion', equipment.os_version || '');
      setDetailField('detailOfficeProductivity', equipment.office_productivity || '');
      setDetailField('detailEndpointProtection', equipment.endpoint_protection || '');
      setDetailField('detailSerialNumber', equipment.serial_number || '');
      setDetailField('detailAccountablePerson', equipment.accountable_person || '');
      setDetailField('detailAccountableSex', equipment.accountable_sex || '');
      setDetailField('detailAccountableEmployment', equipment.accountable_employment || '');
      setDetailField('detailActualUser', equipment.actual_user || '');
      setDetailField('detailActualUserSex', equipment.actual_user_sex || '');
      setDetailField('detailActualUserEmployment', equipment.actual_user_employment || '');
      setDetailField('detailNatureOfWork', equipment.nature_of_work || '');
      setDetailField('detailRemarks', equipment.remarks || 'No remarks');

      // Set QR code in details modal (prefer stored path)
      try {
        let detailQrSrc = '';
        if (equipment.qr_code_path) {
          if (/^https?:\/\//i.test(equipment.qr_code_path)) {
            detailQrSrc = equipment.qr_code_path;
          } else if (equipment.qr_code_path.startsWith('/')) {
            detailQrSrc = equipment.qr_code_path;
          } else {
            detailQrSrc = '../../../../public/' + equipment.qr_code_path.replace(/^(\.\/|\/)*/, '');
          }
        } else {
          const publicUrl = '../../../../public/qr_view.php?id=' + encodeURIComponent(equipment.id);
          detailQrSrc = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(publicUrl)}`;
        }
        const qrImg = document.getElementById('detailQrCode');
        if (qrImg) {
          qrImg.setAttribute('src', detailQrSrc);
          qrImg.style.cursor = 'pointer';
          qrImg.onclick = function() {
            window.open('../../../../public/qr_view.php?id=' + encodeURIComponent(equipment.id), '_blank');
          };
        }
      } catch (e) {
        console.warn('Failed to set detail QR image', e);
      }

      document.getElementById('equipmentDetailsModal').style.display = 'flex';
    }

    async function editEquipment(id) {
        const equipment = equipmentData[id];
        if (!equipment) return;

        // ensure users are loaded so selects exist and have options
        if (!usersData || usersData.length === 0) {
          await loadUsers();
        }

        // Populate form with equipment data
        // support different possible server keys
        document.getElementById('officeDevision').value = getProp(equipment, 'office_division', 'office_devision', 'officeDevision', 'officeDivision') || '';
        document.getElementById('equipmentType').value = equipment.equipment_type || '';
        document.getElementById('yearAcquired').value = equipment.year_acquired || '';
        document.getElementById('shelfLife').value = equipment.shelf_life || '';
        document.getElementById('brand').value = equipment.brand || '';
        document.getElementById('model').value = equipment.model || '';
        document.getElementById('processor').value = equipment.processor || '';
        document.getElementById('ramSize').value = equipment.ram_size || '';
        document.getElementById('gpu').value = equipment.gpu || '';
        document.getElementById('osVersion').value = equipment.os_version || '';
        document.getElementById('officeProductivity').value = equipment.office_productivity || '';
        document.getElementById('endpointProtection').value = equipment.endpoint_protection || '';
        document.getElementById('computerName').value = equipment.computer_name || '';
        document.getElementById('serialNumber').value = equipment.serial_number || '';
        document.getElementById('propertyNumber').value = equipment.property_number || '';

        // Set accountable person - try matching by full_name first, then by id (if equipment contains accountable_person_id)
        const accountableSelect = document.getElementById('accountablePerson');
        if (accountableSelect) {
          const byName = Array.from(accountableSelect.options).find(opt => opt.value === (equipment.accountable_person || ''));
          const byId = equipment.accountable_person_id ? Array.from(accountableSelect.options).find(opt => opt.getAttribute('data-id') === String(equipment.accountable_person_id)) : null;
          if (byName) accountableSelect.value = byName.value;
          else if (byId) accountableSelect.value = byId.value;
          else accountableSelect.value = equipment.accountable_person || '';
        }

        // Set actual user similarly
        const actualSelect = document.getElementById('actualUser');
        if (actualSelect) {
          const byName = Array.from(actualSelect.options).find(opt => opt.value === (equipment.actual_user || ''));
          const byId = equipment.actual_user_id ? Array.from(actualSelect.options).find(opt => opt.getAttribute('data-id') === String(equipment.actual_user_id)) : null;
          if (byName) actualSelect.value = byName.value;
          else if (byId) actualSelect.value = byId.value;
          else actualSelect.value = equipment.actual_user || '';
        }

        // Continue populating remaining fields
        document.getElementById('accountableSex').value = equipment.accountable_sex || '';
        document.getElementById('accountableEmployment').value = equipment.accountable_employment || '';
        document.getElementById('actualUserSex').value = equipment.actual_user_sex || '';
        document.getElementById('actualUserEmployment').value = equipment.actual_user_employment || '';
        document.getElementById('natureOfWork').value = equipment.nature_of_work || '';
        document.getElementById('remarks').value = equipment.remarks || '';
        // Populate the status select so edits include the current status
        const statusSelect = document.getElementById('status');
        if (statusSelect) {
          // Map DB value 'In Use' to UI label 'Assigned'
          if ((equipment.status || '').toString().toLowerCase() === 'in use') statusSelect.value = 'Assigned';
          else statusSelect.value = equipment.status || 'Assigned';
        }

        // Change modal title and button
        document.querySelector('#addDeviceModal .modal-title').textContent = 'Edit Equipment';
        document.getElementById('addDeviceBtn').textContent = 'Update Equipment';
        document.getElementById('addDeviceBtn').setAttribute('data-edit-id', id);

        document.getElementById('addDeviceModal').style.display = 'flex';
      }

      async function deleteEquipment(id) {
        if (!confirm('Are you sure you want to delete this equipment?')) return;

        const result = await safeServiceCall(EquipmentService.delete(id));
        if (!result || result.error) {
          alert('Error deleting equipment: ' + (result && result.error ? result.error : 'Unknown error'));
          return;
        }

        if (result.success || result.deleted || result.id) {
          alert('Equipment deleted successfully!');
          await loadEquipmentData();
        } else {
          alert('Error deleting equipment: ' + (result.error || 'Unknown error'));
        }
      }

      // Close modal functionality
      document.getElementById('closeModal').addEventListener('click', function() {
        document.getElementById('equipmentDetailsModal').style.display = 'none';
      });

      document.getElementById('equipmentDetailsModal').addEventListener('click', function(e) {
        if (e.target === this) {
          this.style.display = 'none';
        }
      });

      // Add Device Modal functionality
      document.getElementById('addNewDeviceBtn').addEventListener('click', async function() {
        // ensure users are loaded before showing modal so dropdowns are populated
        if (!usersData || usersData.length === 0) {
          await loadUsers();
        } else {
          // refresh dropdowns in case usersData changed
          populateUserDropdowns();
        }

        document.getElementById('addDeviceForm').reset();
        document.querySelector('#addDeviceModal .modal-title').textContent = 'Add New Equipment';
        document.getElementById('addDeviceBtn').textContent = 'Add Equipment';
        document.getElementById('addDeviceBtn').removeAttribute('data-edit-id');
        document.getElementById('addDeviceModal').style.display = 'flex';
      });

      document.getElementById('closeAddDeviceModal').addEventListener('click', function() {
        document.getElementById('addDeviceModal').style.display = 'none';
      });

      document.getElementById('cancelAddDeviceBtn').addEventListener('click', function() {
        document.getElementById('addDeviceModal').style.display = 'none';
      });

      document.getElementById('addDeviceModal').addEventListener('click', function(e) {
        if (e.target === this) {
          this.style.display = 'none';
        }
      });

      // Add/Update equipment functionality
      document.getElementById('addDeviceBtn').addEventListener('click', async function() {
        // collect values from form (unchanged)
        const formData = {
          officeDevision: document.getElementById('officeDevision').value,
          equipmentType: document.getElementById('equipmentType').value,
          yearAcquired: document.getElementById('yearAcquired').value,
          shelfLife: document.getElementById('shelfLife').value,
          brand: document.getElementById('brand').value,
          model: document.getElementById('model').value,
          processor: document.getElementById('processor').value,
          ramSize: document.getElementById('ramSize').value,
          gpu: document.getElementById('gpu').value,
          osVersion: document.getElementById('osVersion').value,
          officeProductivity: document.getElementById('officeProductivity').value,
          endpointProtection: document.getElementById('endpointProtection').value,
          computerName: document.getElementById('computerName').value,
          serialNumber: document.getElementById('serialNumber').value,
          propertyNumber: document.getElementById('propertyNumber').value,
          accountablePerson: document.getElementById('accountablePerson').value,
          accountableSex: document.getElementById('accountableSex').value,
          accountableEmployment: document.getElementById('accountableEmployment').value,
          actualUser: document.getElementById('actualUser').value,
          actualUserSex: document.getElementById('actualUserSex').value,
          actualUserEmployment: document.getElementById('actualUserEmployment').value,
          natureOfWork: document.getElementById('natureOfWork').value,
          remarks: document.getElementById('remarks').value,
          status: (document.getElementById('status') ? document.getElementById('status').value : 'Assigned')
        };

        // simple validation (keep existing requirement)
        if (!formData.propertyNumber) {
          alert('Property Number is required!');
          return;
        }

        // normalize/mapping to expected backend keys (snake_case)
        const payload = {
          office_division: formData.officeDevision || formData.office_division || formData.officeDevision || '',
          equipment_type: formData.equipmentType || '',
          year_acquired: formData.yearAcquired || '',
          shelf_life: formData.shelfLife || '',
          brand: formData.brand || '',
          model: formData.model || '',
          processor: formData.processor || '',
          ram_size: formData.ramSize || '',
          gpu: formData.gpu || '',
          os_version: formData.osVersion || '',
          office_productivity: formData.officeProductivity || '',
          endpoint_protection: formData.endpointProtection || '',
          computer_name: formData.computerName || '',
          serial_number: formData.serialNumber || '',
          property_number: formData.propertyNumber || '',
          accountable_person: formData.accountablePerson || '',
          accountable_sex: formData.accountableSex || '',
          accountable_employment: formData.accountableEmployment || '',
          actual_user: formData.actualUser || '',
          actual_user_sex: formData.actualUserSex || '',
          actual_user_employment: formData.actualUserEmployment || '',
          nature_of_work: formData.natureOfWork || '',
          remarks: formData.remarks || '',
          // Map UI value 'Assigned' back to DB value 'In Use' before sending
          status: (formData.status === 'Assigned') ? 'In Use' : (formData.status || 'In Use')
        };

        const editId = this.getAttribute('data-edit-id');
        let result = null;
        try {
          console.log('Sending payload for ' + (editId ? ('update id=' + editId) : 'create') + ':', payload);
        } catch (e) { console.warn('Failed to log payload', e); }

        if (editId) {
          result = await safeServiceCall(EquipmentService.update(editId, payload));
        } else {
          result = await safeServiceCall(EquipmentService.create(payload));
        }

        if (!result || result.error) {
          alert('Error: ' + (result && result.error ? result.error : 'Unknown error'));
          return;
        }

        if (result.success || result.id) {
          // Debug: show server's saved object to confirm shelf_life persisted
          try {
            console.log('Equipment save result:', result);
            if (result.saved) {
              console.log('Saved object:', result.saved);
              console.log('Saved shelf_life:', result.saved.shelf_life);
              console.log('Saved status:', result.saved.status);
            }
          } catch (e) { console.warn('Logging response failed', e); }

          // Notify user and show the status that the server reports as saved
          if (result.saved && result.saved.status !== undefined) {
            alert((editId ? 'Equipment updated successfully!\n' : 'Equipment added successfully!\n') + 'Status saved as: ' + result.saved.status);
          } else {
            alert(editId ? 'Equipment updated successfully!' : 'Equipment added successfully!');
          }
          document.getElementById('addDeviceModal').style.display = 'none';
          await loadEquipmentData();
        } else {
          alert('Error: ' + (result.error || 'Unknown error'));
        }
      });
 
      // Search and filter functionality
      const searchInput = document.getElementById('searchInput');
      const statusFilter = document.getElementById('statusFilter');
      const clearFiltersBtn = document.getElementById('clearFiltersBtn');
 
      if (searchInput) {
        searchInput.addEventListener('input', debounce(async function() {
          await loadEquipmentData(this.value, statusFilter.value);
        }, 300));
      }
 
      if (statusFilter) {
        statusFilter.addEventListener('change', async function() {
          await loadEquipmentData(searchInput.value, this.value);
        });
      }
 
      if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', async function() {
          searchInput.value = '';
          statusFilter.value = 'All';
          await loadEquipmentData();
        });
      }
 
      // Debounce helper
      function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
          const later = () => {
            clearTimeout(timeout);
            func(...args);
          };
          clearTimeout(timeout);
          timeout = setTimeout(later, wait);
        };
      }
 
      // Print Equipment List Button Event
      document.getElementById('printEquipmentListBtn').addEventListener('click', function() {
        printEquipmentList();
      });
      
      function printEquipmentList() {
        const now = new Date();
        const currentDate = now.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        const currentTime = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

        document.getElementById('footerDate').textContent = `${currentDate} at ${currentTime}`;
        const statusFilterEl = document.getElementById('statusFilter');
        const activeFilter = statusFilterEl ? (statusFilterEl.value || 'All') : 'All';
        document.getElementById('printFilter').textContent = activeFilter;

        const printTableBody = document.getElementById('printTableBody');
        printTableBody.innerHTML = '';

        const items = Object.values(equipmentData || {}).sort((a, b) => {
          const left = String(a.property_number || a.id || '').trim();
          const right = String(b.property_number || b.id || '').trim();
          return left.localeCompare(right, undefined, { numeric: true, sensitivity: 'base' });
        });
        let totalCount = 0;

        if (!items || items.length === 0) {
          printTableBody.innerHTML = '<tr><td colspan="19" style="text-align:center;">No equipment to print</td></tr>';
        } else {
          const rows = [];
          items.forEach(eq => {
            // show only loaded/visible items (equipmentData holds loaded results based on filters)
            const id = eq.id || '';
            const prop = eq.property_number || '';
            const type = eq.equipment_type || '';
            const brandModel = ((eq.brand || '') + ' / ' + (eq.model || '')).replace(/^\s*\/\s*$/, '');
            const year = eq.year_acquired || '';
            const office = getProp(eq, 'office_division', 'office_devision', 'officeDevision', 'officeDivision') || '';
            const accountable = eq.accountable_person || '';
            const accountable_sex = eq.accountable_sex || '';
            const accountable_employment = eq.accountable_employment || '';
            const actual = eq.actual_user || '';
            const actual_sex = eq.actual_user_sex || '';
            const actual_employment = eq.actual_user_employment || '';
            const nature = eq.nature_of_work || '';
            const specs = [(eq.processor || ''), (eq.ram_size || ''), (eq.gpu || '')].filter(Boolean).join(' / ');
            const software = [(eq.office_productivity || ''), (eq.endpoint_protection || '')].filter(Boolean).join(' / ');
            const serial = eq.serial_number || '';
            const shelf = eq.shelf_life || '';
            const statusRaw = (eq.status || '').toString();
            const statusDisplay = statusRaw.toLowerCase() === 'in use' ? 'Assigned' : statusRaw;
            const remarks = eq.remarks || '';

            rows.push(`<tr>
              <td>${escapeHtml(id)}</td>
              <td>${escapeHtml(prop)}</td>
              <td>${escapeHtml(type)}</td>
              <td>${escapeHtml(brandModel)}</td>
              <td>${escapeHtml(year)}</td>
              <td>${escapeHtml(office)}</td>
              <td>${escapeHtml(accountable)}</td>
              <td>${escapeHtml(accountable_sex)}</td>
              <td>${escapeHtml(accountable_employment)}</td>
              <td>${escapeHtml(actual)}</td>
              <td>${escapeHtml(actual_sex)}</td>
              <td>${escapeHtml(actual_employment)}</td>
              <td>${escapeHtml(nature)}</td>
              <td>${escapeHtml(specs)}</td>
              <td>${escapeHtml(software)}</td>
              <td>${escapeHtml(serial)}</td>
              <td>${escapeHtml(shelf)}</td>
              <td>${escapeHtml(statusDisplay)}</td>
              <td>${escapeHtml(remarks)}</td>
            </tr>`);

            totalCount++;
          });

          printTableBody.innerHTML = rows.join('\n');
        }

        document.getElementById('totalCount').textContent = totalCount;

        // Show and print
        const printContainer = document.getElementById('printContainer');
        printContainer.style.display = 'block';
        setTimeout(function() {
          window.print();
          setTimeout(function() { printContainer.style.display = 'none'; }, 200);
        }, 150);
      }

      // small helper to avoid XSS-injecting innerHTML
      function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }
      
      // Print QR Codes functionality
      const printQRBtn = document.getElementById('printQRCodesBtn');
      if (printQRBtn) {
        printQRBtn.addEventListener('click', function() {
          printQRCodes();
        });

      }
      
      function printQRCodes() {
        // Create a new window for printing QR codes
        const printWindow = window.open('', '_blank');
        
        // Get equipment data from table
        const equipmentTable = document.getElementById('equipmentTable');
        const rows = equipmentTable.querySelector('tbody').querySelectorAll('tr');
        const qrEquipmentData = [];
        
        rows.forEach(function(row) {
          if (row.style.display !== 'none') {
            const cells = row.querySelectorAll('td');
            if (cells.length > 0) {
              // try to read QR image src from the QR cell (usually cell index 8)
              const img = row.querySelector('img.qr-code-img');
              const qrSrc = img ? img.getAttribute('src') : null;
              qrEquipmentData.push({
                propertyNumber: cells[1].textContent.trim(),
                qrSrc: qrSrc
              });
            }
          }
        });
        
        printWindow.document.write(`
          <!DOCTYPE html>
          <html>
          <head>
            <title>QR Codes - CENRO NASIPIT</title>
            <style>
              body {
                font-family: "Times New Roman", Times, serif;
                margin: 0;
                padding: 20px;
                background: white;
              }
              .qr-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                margin: 20px 0;
              }
              .qr-card {
                border: 2px solid #2c5530;
                padding: 20px;
                text-align: center;
                background: white;
                page-break-inside: avoid;
                width: 300px;
                height: 350px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
              }
              .header {
                text-align: center;
                margin-bottom: 15px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 15px;
              }
              .denr-logo {
                width: 45px;
                height: 45px;
                object-fit: contain;
              }
              .header-text {
                flex: 1;
                text-align: center;
              }
              .header h3 {
                color: #000;
                margin: 2px 0;
                font-size: 11px;
                font-weight: bold;
                font-family: "Times New Roman", Times, serif;
              }
              .header h4 {
                color: #000;
                margin: 1px 0;
                font-size: 9px;
                font-weight: normal;
                font-family: "Times New Roman", Times, serif;
              }
              .property-title {
                background: #2c5530;
                color: white;
                padding: 8px;
                margin: 10px 0 20px 0;
                font-weight: bold;
                font-size: 14px;
                letter-spacing: 1px;
                width: 100%;
                font-family: "Times New Roman", Times, serif;
              }
              .qr-code {
                margin: 20px 0;
              }
              .qr-code img {
                width: 150px;
                height: 150px;
                border: 1px solid #ccc;
              }
              .property-number {
                font-weight: bold;
                font-size: 16px;
                color: #2c5530;
                margin-top: 15px;
                text-transform: uppercase;
                letter-spacing: 1px;
                font-family: "Times New Roman", Times, serif;
              }
              @media print {
                body { margin: 0; padding: 10px; }
                .qr-grid { gap: 15px; }
                .qr-card { 
                  page-break-inside: avoid;
                  margin-bottom: 15px;
                }
              }
            </style>
          </head>
          <body>
            <div class="qr-grid">
        `);
        
        // Generate QR code cards
        qrEquipmentData.forEach(equipment => {
          printWindow.document.write(`
            <div class="qr-card">
              <div class="header">
                <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo" class="denr-logo">
                <div class="header-text">
                  <h3>Department of Environment and Natural Resources</h3>
                  <h4>Community Environment and Natural Resources Office</h4>
                  <h4>CENRO Nasipit, Agusan del Norte</h4>
                </div>
              </div>
              
              <div class="property-title">RP GOVERNMENT PROPERTY</div>
              
              <div class="qr-code">
                ${ (equipment.qrSrc && !String(equipment.qrSrc).toLowerCase().includes('api.qrserver.com')) 
                    ? ('<img src="' + equipment.qrSrc + '" alt="QR Code">')
                    : ('<div style="width:150px;height:150px;border:1px solid #ccc;display:flex;align-items:center;justify-content:center;color:#666">No QR</div>') }
              </div>
              
              <div class="property-number">${equipment.propertyNumber}</div>
            </div>
          `);
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
    });

    // Global functions for modal actions
    function closeEquipmentDetails() {
      document.getElementById('equipmentDetailsModal').style.display = 'none';
    }

    function printEquipmentDetails() {
      const modal = document.getElementById('equipmentDetailsModal');
      const modalStyle = modal.style.display;
      modal.style.display = 'none';
      
      setTimeout(() => {
        window.print();
        modal.style.display = modalStyle;
      }, 100);
    }

    function printQRCode() {
      alert('Print QR Code functionality: This would generate and print the QR code for this specific equipment item.');
    }
  </script>
</body>
</html>
