<?php
session_start();

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'office_staff') {
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
  <style>
    /* Allow input for new requests form */
    .readonly-section input, 
    .readonly-section textarea, 
    .readonly-section select {
      background-color: #f8f9fa !important;
      pointer-events: none !important;
    }
    .readonly-section input[type="file"] {
      opacity: 0.5 !important;
      pointer-events: none !important;
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
      <div class="sidebar-role">Office Staff</div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li><a href="service_requests.php"><i class="fa fa-cog"></i> Service Requests</a></li>
        </ul>
      </nav>
    </nav>
    <!-- Main -->
    <div class="main">
      <div class="topbar">
        <div class="topbar-card">
          <div class="topbar-title">New Request</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <div class="main-content">
        <div class="container-fluid p-4">
          
          <?php
          $request_id = isset($_GET['id']) ? $_GET['id'] : '';

          // Server-side preview ticket number and current date (shows next sequential ticket)
          $current_date = date('m/d/Y');
          $ticket_no = '';
          try {
            require_once __DIR__ . '/../../../../app/config/db.php';
            $year = date('Y');
            $month = date('m');
            $like = $year . '-' . $month . '-%';
            $maxStmt = $pdo->prepare("SELECT MAX(CAST(RIGHT(ticket_no,4) AS UNSIGNED)) FROM service_requests WHERE ticket_no LIKE ?");
            $maxStmt->execute([$like]);
            $maxVal = (int)$maxStmt->fetchColumn();
            $next = $maxVal + 1;
            $ticket_no = 'CN-' . sprintf('%s-%s-%04d', $year, $month, $next);
          } catch (Exception $e) {
            // Fallback: show a readable placeholder if DB unavailable
            $ticket_no = 'CN-' . date('Y') . '-' . date('m') . '-0001';
          }

          // Auto-populate requester details from logged-in user (session -> database)
          $requester_name = $requester_email = $requester_phone = $requester_office = '';
          $sessionUserId = $_SESSION['uid'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
          $sessionUserEmail = $_SESSION['email'] ?? null;
          if (!empty($sessionUserId) || !empty($sessionUserEmail)) {
            // include DB connection (safe to include; db.php uses PDO)
            require_once __DIR__ . '/../../../../app/config/db.php';
            try {
              if (!empty($sessionUserId)) {
                $stmt = $pdo->prepare('SELECT full_name, email, contact_number, office_unit FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$sessionUserId]);
              } else {
                $stmt = $pdo->prepare('SELECT full_name, email, contact_number, office_unit FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$sessionUserEmail]);
              }
              $r = $stmt->fetch();
              if (!empty($r)) {
                $requester_name = $r['full_name'] ?? $requester_name;
                $requester_email = $r['email'] ?? $requester_email;
                $requester_phone = $r['contact_number'] ?? $requester_phone;
                $requester_office = $r['office_unit'] ?? $requester_office;
              }
            } catch (Exception $e) {
              // silently ignore DB errors; fields will remain empty
            }
          }
          ?>
          
          <!-- Back Button -->
          <div class="row mb-3">
            <div class="col-12">
              <a href="service_requests.php" class="btn btn-secondary">
                <i class="fa fa-arrow-left me-2"></i>Back
              </a>
            </div>
          </div>

          <!-- Service Request Form -->
          <form id="serviceRequestForm" method="POST" action="../controllers/save_request.php" enctype="multipart/form-data">
            <input type="hidden" name="ticket_no" id="ticketNoInput" value="<?php echo htmlspecialchars($ticket_no); ?>">
            <input type="hidden" name="ticket_date" id="ticketDateInput" value="<?php echo htmlspecialchars($current_date); ?>">
          <div style="max-width: 1100px; margin: 0 auto; background: white; font-family: Arial, sans-serif; font-size: 13px;">
            
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
                  <td style="width: 50%;"><strong style="font-size: 10px;">Ticket No: <?php echo htmlspecialchars($ticket_no); ?></strong></td>
                  <td style="width: 50%; text-align: right;"><strong style="font-size: 10px;">Auto-generated: <?php echo htmlspecialchars($current_date); ?></strong></td>
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
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; width: 38%; font-size: 9px;">
                    <input type="text" name="requester_name" style="width: 100%; border: none; font-size: 9px; padding: 2px;" value="<?php echo htmlspecialchars($requester_name ?? ''); ?>" required />
                  </td>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-weight: bold; width: 12%; font-size: 9px;">Position:</td>
                  <td style="border-bottom: 1px solid black; padding: 5px; width: 38%; font-size: 9px;">
                    <input type="text" name="requester_position" style="width: 100%; border: none; font-size: 9px; padding: 2px;" placeholder="Enter position" required />
                  </td>
                </tr>
                <tr>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-weight: bold; font-size: 9px;">Office:</td>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-size: 9px;">
                    <input type="text" name="requester_office" style="width: 100%; border: none; font-size: 9px; padding: 2px;" value="" required />
                  </td>
                  <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; font-weight: bold; font-size: 9px;">Division/Section:</td>
                  <td style="border-bottom: 1px solid black; padding: 5px; font-size: 9px;">
                    <input type="text" name="requester_division" style="width: 100%; border: none; font-size: 9px; padding: 2px;" value="" required />
                  </td>
                </tr>
                <tr>
                  <td style="border-right: 1px solid black; padding: 5px; font-weight: bold; font-size: 9px;">Phone Number:</td>
                  <td style="border-right: 1px solid black; padding: 5px; font-size: 9px;">
                    <input type="tel" name="requester_phone" style="width: 100%; border: none; font-size: 9px; padding: 2px;" value="<?php echo htmlspecialchars($requester_phone ?? ''); ?>" required />
                  </td>
                  <td style="border-right: 1px solid black; padding: 5px; font-weight: bold; font-size: 9px;">Email Address:</td>
                  <td style="padding: 5px; font-size: 9px;">
                    <input type="email" name="requester_email" style="width: 100%; border: none; font-size: 9px; padding: 2px;" value="<?php echo htmlspecialchars($requester_email ?? ''); ?>" required />
                  </td>
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
                        <td style="padding: 5px; font-size: 9px;">
                          <input type="text" name="request_type" style="width: 100%; border: none; font-size: 9px; padding: 2px; font-weight: bold;" placeholder="Enter type of request" required />
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
              
              <div style="padding: 8px;">
                <div style="font-weight: bold; margin-bottom: 5px; font-size: 9px;">DESCRIPTION OF REQUEST (Please clearly write down the details of the request.)</div>
                  <div style="border: 1px solid black; padding: 12px; min-height: 100px; position: relative;">
                  <textarea name="request_description" style="width: 100%; height: 80px; border: none; font-size: 9px; resize: none; outline: none;" placeholder="Enter detailed description of the request..." required></textarea>
                  <div style="position: absolute; bottom: 12px; right: 15px;">
                    <div style="border: 1px solid black; width: 100px; height: 40px; display: flex; flex-direction: column; justify-content: center; align-items: center; position: relative; background: #f9f9f9;">
                      <canvas id="requester_signature_pad" style="width:100px; height:40px; touch-action: none;"></canvas>
                      <input type="hidden" name="requester_signature_data" id="requester_signature_data" />
                      <div style="position: absolute; right: 4px; top: 4px;">
                        <button type="button" id="requester_sig_clear" class="btn btn-sm btn-link" style="font-size:8px;padding:0;color:blue;">Clear</button>
                      </div>
                    </div>
                    <div style="font-size: 8px; text-align: center; margin-top: 2px;">Requester Signature</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="d-flex justify-content-end my-3" style="max-width:1100px; margin:0 auto;">
              <input type="hidden" name="save_draft" id="save_draft" value="">
              <button type="button" class="btn btn-secondary me-2" id="saveDraftBtn">Save Draft</button>
              <button type="submit" class="btn btn-primary" id="submitBtn">Submit Request</button>
            </div>
          </div>
          </form>

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
  <!-- Signature Pad library -->
  <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

  <!-- Signature Modal -->
  <div class="modal fade" id="signatureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Please sign below</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div style="width:100%; height:300px; border:1px solid #ddd;">
            <canvas id="signature_modal_canvas" style="width:100%; height:100%; touch-action: none;"></canvas>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="modal_clear">Clear</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="modal_cancel">Cancel</button>
          <button type="button" class="btn btn-primary" id="modal_save">Save Signature</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Service Request Form JavaScript -->
  <script>
    // Auto-generate ticket number and set current date
    document.addEventListener('DOMContentLoaded', function() {
      // Ticket number and current date are provided by the server; no client-side override.

      // Signature pad setup
      function resizeCanvas(canvas) {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const w = canvas.offsetWidth;
        const h = canvas.offsetHeight;
        canvas.width = w * ratio;
        canvas.height = h * ratio;
        const ctx = canvas.getContext('2d');
        ctx.scale(ratio, ratio);
      }

      window.signaturePads = {};
      function initPad(canvasId, clearBtnId, key) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || typeof SignaturePad === 'undefined') return;
        resizeCanvas(canvas);
        const pad = new SignaturePad(canvas, { backgroundColor: 'rgba(255,255,255,0)' });
        window.signaturePads[key] = pad;
        const clearBtn = document.getElementById(clearBtnId);
        if (clearBtn) clearBtn.addEventListener('click', function() { pad.clear(); });
      }

      initPad('requester_signature_pad', 'requester_sig_clear', 'requester');
      initPad('auth1_signature_pad', 'auth1_sig_clear', 'auth1');
      initPad('auth2_signature_pad', 'auth2_sig_clear', 'auth2');

      // Modal signature pad (initialize after modal is shown to ensure sizes)
      let modalPad = null;
      let modalCurrent = null;
      const signatureModalEl = document.getElementById('signatureModal');
      const bsModal = signatureModalEl ? new bootstrap.Modal(signatureModalEl) : null;

      // When modal is shown, resize canvas and (re)create SignaturePad instance
      if (signatureModalEl) {
        signatureModalEl.addEventListener('shown.bs.modal', function () {
          const canvas = document.getElementById('signature_modal_canvas');
          if (!canvas) return;
          resizeCanvas(canvas);
          // destroy previous instance
          try { if (modalPad) modalPad.off && modalPad.off(); } catch (e) {}
          modalPad = new SignaturePad(canvas, { backgroundColor: 'rgba(255,255,255,0)' });

          // If there's existing data for the current target, draw it onto the modal canvas
          if (modalCurrent && modalCurrent.hiddenInputId) {
            const hidden = document.getElementById(modalCurrent.hiddenInputId);
            if (hidden && hidden.value) {
              const img = new Image();
              img.onload = function() {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0,0,canvas.width,canvas.height);
                // draw image scaled to canvas CSS size
                ctx.drawImage(img, 0, 0, canvas.width / (window.devicePixelRatio||1), canvas.height / (window.devicePixelRatio||1));
              };
              img.src = hidden.value;
            } else {
              modalPad.clear();
            }
          }
        });

        signatureModalEl.addEventListener('hidden.bs.modal', function () {
          // clear modalPad to free memory
          try { if (modalPad) modalPad.clear(); } catch (e) {}
          modalPad = null;
        });
      }

      function openModalFor(key, smallCanvasId, hiddenInputId) {
        modalCurrent = { key, smallCanvasId, hiddenInputId };
        if (bsModal) bsModal.show();
      }

      function drawDataUrlToSmallCanvas(smallCanvasId, dataUrl) {
        const small = document.getElementById(smallCanvasId);
        if (!small) return;
        const ctx = small.getContext('2d');
        const img = new Image();
        img.onload = function() {
          // clear and draw
          ctx.clearRect(0,0,small.width, small.height);
          ctx.drawImage(img, 0, 0, small.width, small.height);
        };
        img.src = dataUrl;
      }

      // wire small canvases to open modal on click
      ['requester','auth1','auth2'].forEach(function(k){
        const smallCanvas = document.getElementById(k + '_signature_pad');
        const hiddenId = k === 'requester' ? 'requester_signature_data' : (k === 'auth1' ? 'auth1_signature_data' : 'auth2_signature_data');
        if (smallCanvas) {
          smallCanvas.style.cursor = 'pointer';
          smallCanvas.addEventListener('click', function(){ openModalFor(k, k + '_signature_pad', hiddenId); });
        }
      });

      // modal controls
      const modalSaveBtn = document.getElementById('modal_save');
      const modalClearBtn = document.getElementById('modal_clear');
      const modalCancelBtn = document.getElementById('modal_cancel');
      if (modalSaveBtn) modalSaveBtn.addEventListener('click', function(){
        if (!modalPad || !modalCurrent) return;
        const dataUrl = modalPad.toDataURL('image/png');
        const hidden = document.getElementById(modalCurrent.hiddenInputId);
        if (hidden) hidden.value = dataUrl;
        drawDataUrlToSmallCanvas(modalCurrent.smallCanvasId, dataUrl);
        if (bsModal) bsModal.hide();
      });
      if (modalClearBtn) modalClearBtn.addEventListener('click', function(){ if (modalPad) modalPad.clear(); });
      if (modalCancelBtn) modalCancelBtn.addEventListener('click', function(){ if (modalPad) modalPad.clear(); });

      // Save Draft and Submit button wiring
      const saveBtn = document.getElementById('saveDraftBtn');
      const submitBtn = document.getElementById('submitBtn');
      const saveHidden = document.getElementById('save_draft');
      if (saveBtn) saveBtn.addEventListener('click', function(){ if (saveHidden) saveHidden.value = '1'; form.submit(); });
      if (submitBtn) submitBtn.addEventListener('click', function(){ if (saveHidden) saveHidden.value = ''; });

      window.addEventListener('resize', function() {
        ['requester_signature_pad','auth1_signature_pad','auth2_signature_pad'].forEach(function(id){
          const c = document.getElementById(id);
          if (c) resizeCanvas(c);
        });
      });
    });

    // Preview signature function
    function previewSignature(input) {
      const preview = document.getElementById('signature_preview');
      
      if (input.files && input.files[0]) {
        // Check file size (max 5MB)
        if (input.files[0].size > 5 * 1024 * 1024) {
          alert('File size should be less than 5MB');
          input.value = '';
          return;
        }
        
        // Check file type
        if (!input.files[0].type.match('image.*')) {
          alert('Please select an image file');
          input.value = '';
          return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
          preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; max-height: 100%; object-fit: contain;" alt="Signature Preview">`;
        };
        
        reader.readAsDataURL(input.files[0]);
      } else {
        preview.innerHTML = `
          <div style="text-align: center;">
            <div style="font-size: 7px; color: #666; margin-bottom: 2px;">Click to upload</div>
            <div style="font-size: 6px; color: #999;">signature image</div>
          </div>
        `;
      }
    }

    // Save as draft function
    function saveDraft() {
      const form = document.getElementById('serviceRequestForm');
      if (form) {
        const formData = new FormData(form);
        formData.append('action', 'save_draft');
        
        // Here you would send the data to your backend
        alert('Request saved as draft!');
      }
    }

    // Form validation and submission
    const form = document.getElementById('serviceRequestForm');
    if (form) {
      form.addEventListener('submit', function(e) {
        // Save signature pad data into hidden inputs (if any)
        try {
          if (window.signaturePads) {
            const pads = window.signaturePads;
            if (pads.requester && !pads.requester.isEmpty()) {
              const el = document.getElementById('requester_signature_data');
              if (el) el.value = pads.requester.toDataURL('image/png');
            }
            if (pads.auth1 && !pads.auth1.isEmpty()) {
              const el = document.getElementById('auth1_signature_data');
              if (el) el.value = pads.auth1.toDataURL('image/png');
            }
            if (pads.auth2 && !pads.auth2.isEmpty()) {
              const el = document.getElementById('auth2_signature_data');
              if (el) el.value = pads.auth2.toDataURL('image/png');
            }
          }
        } catch (err) {
          // ignore
        }

        // Basic validation for required fields
        const requiredFields = [
          'requester_name', 'requester_position', 'requester_office', 
          'requester_division', 'requester_phone', 'requester_email', 
          'request_type', 'request_description'
        ];
        
        let isValid = true;
        
        requiredFields.forEach(field => {
          const input = document.querySelector(`[name="${field}"]`);
          if (input && !input.value.trim()) {
            input.style.borderBottom = '2px solid red';
            isValid = false;
          } else if (input) {
            input.style.borderBottom = '';
          }
        });
        
        if (!isValid) {
          e.preventDefault();
          alert('Please fill in all required fields.');
        }
        // If valid, allow normal POST to `../controllers/save_request.php`
      });
    }
  </script>
</body>
</html>