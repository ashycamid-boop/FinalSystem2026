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
  <title>Request Details</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Admin common styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <!-- Service Desk specific styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/service-desk.css">
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
          <li><a href="assignments.php"><i class="fa fa-tasks"></i> Assignments</a></li>
          <li class="dropdown active">
            <a href="#" class="dropdown-toggle active" id="serviceDeskToggle" data-target="serviceDeskMenu">
              <i class="fa fa-headset"></i> Service Desk 
              <i class="fa fa-chevron-down dropdown-arrow rotated"></i>
            </a>
            <ul class="dropdown-menu show" id="serviceDeskMenu">
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
          <div class="topbar-title">Ongoing Request Details</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <div class="container-fluid p-4">
          
          <?php
          $request_id = isset($_GET['id']) ? $_GET['id'] : 'CN-2025-08-0107';
          ?>
          
          <!-- Back Button -->
          <div class="row mb-3">
            <div class="col-12">
              <a href="ongoing_scheduled.php" class="btn btn-secondary">
                <i class="fa fa-arrow-left me-2"></i>Back
              </a>
            </div>
          </div>

          <!-- Service Request Form -->
          <div style="max-width: 850px; margin: 0 auto; background: white; font-family: Arial, sans-serif; font-size: 11px;">
            
            <!-- Header Section with Border -->
            <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
              <tr>
                <td rowspan="2" style="width: 100px; text-align: center; vertical-align: middle; padding: 8px; border-right: 1px solid black;">
                  <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo" style="width: 70px; height: 70px;">
                </td>
                <td style="text-align: center; vertical-align: middle; padding: 12px; border-right: 1px solid black; border-bottom: 1px solid black;">
                  <div style="font-size: 16px; font-weight: bold; margin-bottom: 3px;">DENR-PENRO AGUSAN DEL NORTE</div>
                  <div style="font-size: 12px;">Information and Communication Technology Unit (ICTU)</div>
                </td>
                <td style="width: 200px; padding: 0; border-bottom: 1px solid black;">
                  <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                    <tr>
                      <td style="border-bottom: 1px solid black; border-right: 1px solid black; padding: 4px; font-weight: bold; width: 60%;">Department ID No.</td>
                      <td style="border-bottom: 1px solid black; padding: 4px; text-align: center;">R13-CN-FO-003</td>
                    </tr>
                    <tr>
                      <td style="border-right: 1px solid black; padding: 4px; font-weight: bold;">Revision No.</td>
                      <td style="padding: 4px; text-align: center;">1</td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td style="text-align: center; vertical-align: middle; padding: 12px; border-right: 1px solid black; font-size: 14px; font-weight: bold;">
                  SERVICE REQUEST FROM (SRF)
                </td>
                <td style="width: 200px; padding: 0;">
                  <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                    <tr>
                      <td style="border-right: 1px solid black; padding: 4px; font-weight: bold; width: 60%;">Effectivity</td>
                      <td style="padding: 4px; text-align: center;">9/1/2022</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            <!-- Reminder Section -->
            <div style="padding: 10px; border-left: 1px solid black; border-right: 1px solid black;">
              <p style="margin-bottom: 10px; font-size: 9px; line-height: 1.2; text-align: justify;">
                <strong>Reminder:</strong> Please complete this form and submit it at the PENRO ICT Unit Service Desk located on the ground floor PENRO Agusan del Norte Building, Tiniwisan, Butuan City or email a scanned a copy to <span style="color: blue;">ictu@denr.gov.ph</span>. Once processed, a Technical Support Representative will contact you to schedule service.
              </p>
              
              <table style="width: 100%; margin-bottom: 10px;">
                <tr>
                  <td style="width: 50%;"><strong style="font-size: 10px;">Ticket No: CN-2025-08-0107</strong></td>
                  <td style="width: 50%; text-align: right;"><strong style="font-size: 10px;">Date (mm/dd/yyyy): 08/28/2025</strong></td>
                </tr>
              </table>
            </div>

            <!-- Requester's Information -->
            <div style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="background-color: #f0f0f0; padding: 5px 10px; border-bottom: 1px solid black; font-weight: bold; font-size: 10px;">
                    Requester's Information
                  </td>
                </tr>
              </table>
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-weight: bold; width: 12%; font-size: 9px;">Name:</td>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; width: 38%; font-size: 9px;">Ashmen S. Camid</td>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-weight: bold; width: 12%; font-size: 9px;">Position:</td>
                  <td style="border-bottom: 1px solid black; padding: 5px; width: 38%; font-size: 9px;">Project Support Staff</td>
                </tr>
                <tr>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-weight: bold; font-size: 9px;">Office:</td>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-size: 9px;">CENRO Nasipit</td>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-weight: bold; font-size: 9px;">Division/Section:</td>
                  <td style="border-bottom: 1px solid black; padding: 5px; font-size: 9px;">Construction Development Section</td>
                </tr>
                <tr>
                  <td style="border-right: 1px solid black; padding: 5px; font-weight: bold; font-size: 9px;">Phone Number:</td>
                  <td style="border-right: 1px solid black; padding: 5px; font-size: 9px;">085041831</td>
                  <td style="border-right: 1px solid black; padding: 5px; font-weight: bold; font-size: 9px;">Email Address:</td>
                  <td style="padding: 5px; font-size: 9px;">amyrcamid@gmail.com</td>
                </tr>
              </table>
            </div>

            <!-- Request Information -->
            <div style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="background-color: #f0f0f0; padding: 5px 10px; border-bottom: 1px solid black; font-weight: bold; font-size: 10px;">
                    Request Information
                  </td>
                </tr>
                <tr>
                  <td style="border-bottom: 1px solid black; padding: 5px;">
                    <table style="width: 100%; border-collapse: collapse;">
                      <tr>
                        <td style="border-right: 1px solid black; padding: 5px; font-weight: bold; width: 20%; font-size: 9px;">Type of Request:</td>
                        <td style="padding: 5px; font-weight: bold; font-size: 9px;">ASSIST IN THE ORIENTATION OF WATERSHED</td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
              
              <div style="padding: 8px;">
                <div style="font-weight: bold; margin-bottom: 5px; font-size: 9px;">DESCRIPTION OF REQUEST (Please clearly write down the details of the request.)</div>
                <div style="border: 1px solid black; padding: 12px; min-height: 100px; position: relative;">
                  <div style="font-size: 9px;">SET UP PROJECTOR AND SOUND SYSTEM</div>
                  <div style="position: absolute; bottom: 12px; right: 15px;">
                    <div style="font-family: 'Brush Script MT', cursive; font-size: 12px; font-style: italic; color: #003366; text-align: center; margin-bottom: 3px;">Joryn Cagulangan</div>
                    <div style="border-bottom: 1px solid black; width: 100px; margin-bottom: 2px;"></div>
                    <div style="font-size: 8px; text-align: center;">Requester Signature</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Authorization Section -->
            <div style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="background-color: #f0f0f0; padding: 5px 10px; border-bottom: 1px solid black; font-weight: bold; font-size: 10px;">
                    Authorization
                  </td>
                </tr>
              </table>
              
              <div style="padding: 6px;">
                <p style="font-size: 8px; margin: 0 0 6px 0; line-height: 1.1; text-align: justify;">
                  All requests for service must be approved by the appropriate manager/supervisor (at least division chief, OIC, immediate supervisor or head clerk staff of the requester). By signing below, the manager/supervisor certifies that the service is required.
                </p>
              </div>
              
              <table style="width: 100%; border-collapse: collapse; border-top: 1px solid black; border-bottom: 1px solid black;">
                <tr>
                  <td style="border-right: 1px solid black; padding: 4px; font-weight: bold; width: 15%; font-size: 9px;">Full Name:</td>
                  <td style="border-right: 1px solid black; padding: 2px; width: 35%;">
                    <input type="text" value="Roel C. Jomawig" style="width: 100%; border: none; font-size: 9px; padding: 2px;" />
                  </td>
                  <td style="border-right: 1px solid black; padding: 4px; font-weight: bold; width: 20%; font-size: 9px;">Title/Position:</td>
                  <td style="padding: 2px; width: 30%;">
                    <input type="text" value="Chief Conservation and Development Section" style="width: 100%; border: none; font-size: 9px; padding: 2px;" />
                  </td>
                </tr>
              </table>

              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="width: 50%; padding: 4px; border-right: 1px solid black;">
                    <div style="border: 1px solid black; text-align: center; height: 50px; display: flex; flex-direction: column; justify-content: center; position: relative;">
                      <div style="font-family: 'Brush Script MT', cursive; font-size: 14px; font-style: italic; color: #003366; margin-bottom: 5px;">Roel C. Jomawig</div>
                      <div style="font-size: 8px; font-weight: bold;">Signature (Manager/Supervisor)</div>
                    </div>
                  </td>
                  <td style="width: 50%; padding: 4px;">
                    <div style="border: 1px solid black; text-align: center; height: 50px; display: flex; flex-direction: column; justify-content: center; padding: 2px;">
                      <div style="font-size: 10px; font-weight: bold; margin-bottom: 2px;">08/28/2025</div>
                      <div style="font-size: 8px; border-top: 1px solid black; padding-top: 2px;">Date (mm-dd-yyyy)</div>
                    </div>
                  </td>
                </tr>
              </table>
            </div>

            <!-- Infrastructure Service Authorization -->
            <div style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="background-color: #f0f0f0; padding: 5px 10px; border-bottom: 1px solid black; font-weight: bold; font-size: 10px;">
                    Infrastructure Service Authorization
                  </td>
                </tr>
              </table>
              
              <div style="padding: 6px;">
                <p style="font-size: 8px; margin: 0 0 6px 0; line-height: 1.1; text-align: justify;">
                  All requests for service must be approved by the appropriate manager/supervisor (at least division chief, OIC, immediate supervisor or head clerk staff of the requester). By signing below, the manager/supervisor certifies that the service is required.
                </p>
              </div>
              
              <table style="width: 100%; border-collapse: collapse; border-top: 1px solid black; border-bottom: 1px solid black;">
                <tr>
                  <td style="border-right: 1px solid black; padding: 4px; font-weight: bold; width: 15%; font-size: 9px;">Full Name:</td>
                  <td style="border-right: 1px solid black; padding: 2px; width: 35%;">
                    <input type="text" value="JOAN P. DAGULPO" style="width: 100%; border: none; font-size: 9px; padding: 2px;" />
                  </td>
                  <td style="border-right: 1px solid black; padding: 4px; font-weight: bold; width: 20%; font-size: 9px;">Title/Position:</td>
                  <td style="padding: 2px; width: 30%;">
                    <input type="text" value="Ems I/Planning Designate" style="width: 100%; border: none; font-size: 9px; padding: 2px;" />
                  </td>
                </tr>
              </table>

              <table style="width: 100%; border-collapse: collapse; border-bottom: 1px solid black;">
                <tr>
                  <td style="width: 50%; padding: 4px; border-right: 1px solid black;">
                    <div style="border: 1px solid black; text-align: center; height: 50px; display: flex; flex-direction: column; justify-content: center; position: relative;">
                      <div style="font-family: 'Brush Script MT', cursive; font-size: 14px; font-style: italic; color: #003366; margin-bottom: 5px;">JOAN P. DAGUPLO</div>
                      <div style="font-size: 8px; font-weight: bold;">Signature (Manager/Supervisor)</div>
                    </div>
                  </td>
                  <td style="width: 50%; padding: 4px;">
                    <div style="border: 1px solid black; text-align: center; height: 50px; display: flex; flex-direction: column; justify-content: center; padding: 2px;">
                      <div style="font-size: 10px; font-weight: bold; margin-bottom: 2px;">08/28/2025</div>
                      <div style="font-size: 8px; border-top: 1px solid black; padding-top: 2px;">Date (mm-dd-yyyy)</div>
                    </div>
                  </td>
                </tr>
              </table>

              <div style="padding: 6px;">
                <p style="font-weight: bold; font-size: 9px;">For PENRO ICT Staff only (Use back of the Form or Separate sheet if necessary)</p>
              </div>
            </div>

            <!-- Staff Table -->
            <div style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">
              <table style="width: 100%; border-collapse: collapse;">
                <thead>
                  <tr>
                    <th style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; width: 15%; text-align: center; font-weight: bold; font-size: 9px;">Date</th>
                    <th style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; width: 15%; text-align: center; font-weight: bold; font-size: 9px;">Time</th>
                    <th style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; width: 40%; text-align: center; font-weight: bold; font-size: 9px;">Action Details</th>
                    <th style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; width: 15%; text-align: center; font-weight: bold; font-size: 9px;">Action Staff</th>
                    <th style="border-bottom: 1px solid black; padding: 4px; width: 15%; text-align: center; font-weight: bold; font-size: 9px;">Signature</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; height: 25px; text-align: center; font-size: 8px;">
                      8-28-2025
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; text-align: center; font-size: 8px;">
                      8:00 AM
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; font-size: 8px;">
                      Kindly set up Projector & Sound System
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; text-align: center; font-size: 8px;">
                      Joan P. Daguplo
                    </td>
                    <td style="border-bottom: 1px solid black; padding: 4px; text-align: center;">
                      <div style="font-family: 'Brush Script MT', cursive; font-size: 12px; font-style: italic; color: #003366;">Joan P. Daguplo</div>
                    </td>
                  </tr>
                  <tr>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; height: 25px; text-align: center; font-size: 8px;">
                      8-28-2025
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; text-align: center; font-size: 8px;">
                      8:32 AM
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; font-size: 8px;">
                      Already Set Up
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; text-align: center; font-size: 8px;">
                      Pj Mordeno
                    </td>
                    <td style="border-bottom: 1px solid black; padding: 4px; text-align: center;">
                      <div style="font-family: 'Brush Script MT', cursive; font-size: 12px; font-style: italic; color: #003366;">Pj Mordeno</div>
                    </td>
                  </tr>
                  <tr>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px; height: 25px;">
                      <input type="date" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                      <input type="time" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                      <textarea style="width: 100%; border: none; font-size: 8px; padding: 2px; height: 20px; resize: none;" placeholder="Action details..."></textarea>
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                      <input type="text" style="width: 100%; border: none; font-size: 8px; padding: 2px;" placeholder="Staff name" />
                    </td>
                    <td style="border-bottom: 1px solid black; padding: 2px;">
                      <input type="file" accept="image/*" style="width: 100%; border: none; font-size: 7px; padding: 1px;" title="Upload scanned signature" />
                    </td>
                  </tr>
                  <tr>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px; height: 25px;">
                      <input type="date" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                      <input type="time" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                      <textarea style="width: 100%; border: none; font-size: 8px; padding: 2px; height: 20px; resize: none;" placeholder="Action details..."></textarea>
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                      <input type="text" style="width: 100%; border: none; font-size: 8px; padding: 2px;" placeholder="Staff name" />
                    </td>
                    <td style="border-bottom: 1px solid black; padding: 2px;">
                      <input type="file" accept="image/*" style="width: 100%; border: none; font-size: 7px; padding: 1px;" title="Upload scanned signature" />
                    </td>
                  </tr>
                  <tr>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px; height: 25px;">
                      <input type="date" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                      <input type="time" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                      <textarea style="width: 100%; border: none; font-size: 8px; padding: 2px; height: 20px; resize: none;" placeholder="Action details..."></textarea>
                    </td>
                    <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 2px;">
                      <input type="text" style="width: 100%; border: none; font-size: 8px; padding: 2px;" placeholder="Staff name" />
                    </td>
                    <td style="border-bottom: 1px solid black; padding: 2px;">
                      <input type="file" accept="image/*" style="width: 100%; border: none; font-size: 7px; padding: 1px;" title="Upload scanned signature" />
                    </td>
                  </tr>
                  <tr>
                    <td style="border-right: 1px solid black; padding: 2px; height: 25px;">
                      <input type="date" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
                    </td>
                    <td style="border-right: 1px solid black; padding: 2px;">
                      <input type="time" style="width: 100%; border: none; font-size: 8px; padding: 2px;" />
                    </td>
                    <td style="border-right: 1px solid black; padding: 2px;">
                      <textarea style="width: 100%; border: none; font-size: 8px; padding: 2px; height: 20px; resize: none;" placeholder="Action details..."></textarea>
                    </td>
                    <td style="border-right: 1px solid black; padding: 2px;">
                      <input type="text" style="width: 100%; border: none; font-size: 8px; padding: 2px;" placeholder="Staff name" />
                    </td>
                    <td style="padding: 2px;">
                      <input type="file" accept="image/*" style="width: 100%; border: none; font-size: 7px; padding: 1px;" title="Upload scanned signature" />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Feedback Section -->
            <div style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="padding: 8px;">
                    <div style="font-size: 9px; margin-bottom: 6px;">
                      <strong>Feedback Rating:</strong> 
                      <input type="checkbox" id="excellent" name="feedback_rating" value="excellent" disabled>
                      <label for="excellent"> Excellent</label>
                      <input type="checkbox" id="very_satisfactory_feed" name="feedback_rating" value="very_satisfactory" style="margin-left: 10px;" disabled>
                      <label for="very_satisfactory_feed"> Very Satisfactory</label>
                      <input type="checkbox" id="below_satisfactory" name="feedback_rating" value="below_satisfactory" style="margin-left: 10px;" disabled>
                      <label for="below_satisfactory"> Below Satisfactory</label>
                      <input type="checkbox" id="poor" name="feedback_rating" value="poor" style="margin-left: 10px;" disabled>
                      <label for="poor"> Poor</label>
                    </div>
                    <div style="margin-bottom: 6px; font-size: 9px;">
                      <input type="checkbox" id="completed" name="status" value="completed" disabled checked>
                      <label for="completed" style="background-color: #28a745; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;"> Completed</label>
                    </div>
                    <div style="font-weight: bold; font-size: 9px; margin-bottom: 6px;">Acknowledged by:</div>
                    <div style="display: flex; gap: 20px; align-items: center;">
                      <div style="flex: 1;">
                        <input type="text" style="border: none; border-bottom: 1px solid black; width: 80%; font-size: 9px;" placeholder="Name" readonly />
                      </div>
                      <div style="flex: 1;">
                        <input type="text" style="border: none; border-bottom: 1px solid black; width: 80%; font-size: 9px;" placeholder="Position" readonly />
                      </div>
                    </div>
                  </td>
                </tr>
              </table>
            </div>

            <!-- Footer -->
            <div style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; padding: 12px;">
              <table style="width: 100%; margin-bottom: 10px;">
                <tr>
                  <td style="width: 50%; padding-right: 10px;">
                    <div style="border-bottom: 1px solid black; height: 20px; margin-bottom: 2px; position: relative;">
                      <span style="position: absolute; bottom: 2px; left: 0; width: 100%; font-size: 8px; color: #888;">[Client will sign here]</span>
                    </div>
                    <div style="font-size: 8px;">Signature over printed name</div>
                  </td>
                  <td style="width: 50%; padding-left: 10px;">
                    <div style="border-bottom: 1px solid black; height: 20px; margin-bottom: 2px; position: relative;">
                      <input type="datetime-local" style="position: absolute; bottom: 2px; left: 0; width: 100%; border: none; font-size: 8px; background: transparent;" />
                    </div>
                    <div style="font-size: 8px;">Date/Time</div>
                  </td>
                </tr>
              </table>

              <div style="text-align: right;">
                <div style="font-size: 8px; font-weight: bold;">Ref: NIMD Service Request Form 22 March 2021</div>
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
  <!-- Admin Dashboard JavaScript -->
  <script src="../../../../public/assets/js/admin/dashboard.js"></script>
  <!-- Admin Navigation JavaScript -->
  <script src="../../../../public/assets/js/admin/navigation.js"></script>
</body>
</html>