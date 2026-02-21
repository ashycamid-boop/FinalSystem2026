<?php
session_start();
require_once __DIR__ . '/../../../../app/config/db.php';

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: /prototype/index.php');
    exit;
}


$user_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';
$user = null;
$devices = [];
if (!empty($user_id)) {
  // normalize numeric id
  $user_id_int = (int)$user_id;
  try {
    $stmt = $pdo->prepare("SELECT id, full_name, email, role, office_unit, profile_picture, contact_number FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id_int]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    $user = null;
  }

  if ($user) {
    // Try to fetch equipment where actual_user references this user.
    // actual_user may store a numeric user id, a full name, or free text — check multiple possibilities for robustness.
    try {
      $q = "SELECT id, property_number, equipment_type, brand, model, serial_number, year_acquired, status
            FROM equipment
            WHERE actual_user = :user_id
               OR actual_user = :full_name
               OR actual_user LIKE :like_name
            ORDER BY property_number ASC";
      $stmt2 = $pdo->prepare($q);
      $like = '%' . $user['full_name'] . '%';
      $stmt2->execute([
        ':user_id' => (string)$user_id_int,
        ':full_name' => $user['full_name'],
        ':like_name' => $like
      ]);
      $devices = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      $devices = [];
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assigned Devices</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/assigned-devices.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Fredoka+One:400&display=swap" rel="stylesheet">
  <style>
    /* Use Times New Roman for this printable page */
    body, table, h5, h6, p, .user-info-table, .devices-table {
      font-family: "Times New Roman", Times, serif !important;
    }

    /* Improve print layout */
    @media print {
      body { background: #fff; }
      .container { padding: 0; }
      .card { box-shadow: none; border: none; }
      .header-logos img { max-width: 100px; height: auto; }
      .user-info-table td.info-label { font-weight: bold; }
    }
    /* profile photo adjustment */
    .profile-photo {
      display: inline-block;
      width: 140px;
      height: 140px;
      object-fit: cover;
      margin-top: -12px;
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <div class="card" id="print-card">
      <div class="card-body">
        <div class="row align-items-center mb-4 header-logos">
          <div class="col-md-2">
            <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo" style="width: 100px; height: 100px; object-fit: contain;">
          </div>
          <div class="col-md-8 text-center">
            <h6 class="mb-0"><strong>Department of Environment and Natural Resources</strong></h6>
            <p class="mb-0"><strong>Kagawaran ng Kapaligiran at Likas na Yaman</strong></p>
            <p class="mb-0"><strong>Caraga Region</strong></p>
            <p class="mb-0"><strong>CENRO Nasipit, Agusan del Norte</strong></p>
          </div>
          <div class="col-md-2 text-end">
            <img src="../../../../public/assets/images/bagong-pilipinas-logo.png" alt="Bagong Pilipinas Logo" style="width: 100px; height: 100px; object-fit: contain;">
          </div>
        </div>

        <hr>

        <h5 class="text-center mb-4">Assigned Devices</h5>

        <?php
        // $user and $devices are prepared at the top of the file.
        $displayName = $user['full_name'] ?? '';
        $displayEmail = $user['email'] ?? '';
        $displayRole = $user['role'] ?? '';
        $displayOffice = $user['office_unit'] ?? '';
        $displayContact = $user['contact_number'] ?? '';

        // build avatar src
        $defaultAvatar = '../../../../public/assets/images/default-avatar.png';
        $imgSrc = $defaultAvatar;
        if (!empty($user['profile_picture'])) {
          $stored = ltrim($user['profile_picture'], '/');
          $fsPath = __DIR__ . '/../../../../' . $stored;
          $imgSrc = file_exists($fsPath) ? ('../../../../' . $stored) : ('../../../../' . $stored);
        }
        ?>

        <div class="row mb-4">
          <div class="col-md-2 text-center">
            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="User Photo" class="img-fluid rounded-circle profile-photo" style="width: 140px; height: 140px; object-fit: cover;">
          </div>
          <div class="col-md-10">
            <table class="table table-bordered user-info-table">
              <tbody>
                <tr>
                  <td class="info-label">Full Name</td>
                  <td><?php echo htmlspecialchars($displayName); ?></td>
                  <td class="info-label">Email</td>
                  <td><?php if (!empty($displayEmail)) { echo '<a href="mailto:' . htmlspecialchars($displayEmail) . '" class="text-decoration-underline">' . htmlspecialchars($displayEmail) . '</a>'; } else { echo '-'; } ?></td>
                  <td class="info-label">Mobile Number</td>
                  <td><?php echo !empty($displayContact) ? htmlspecialchars($displayContact) : '-'; ?></td>
                </tr>
                <tr>
                  <td class="info-label">Role</td>
                  <td><?php echo htmlspecialchars($displayRole); ?></td>
                  <td class="info-label">Office/Unit</td>
                  <td><?php echo htmlspecialchars($displayOffice); ?></td>
                  <td class="info-label">Number of Devices</td>
                  <td><?php echo count($devices); ?></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <h6 class="mb-3" style="color: #999;">Assigned Devices</h6>
        <div class="table-responsive">
          <table class="table table-bordered devices-table">
            <thead class="table-light">
              <tr>
                <th>Asset ID</th>
                <th>Property No.</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Serial Number</th>
                <th>Date Acquired</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($devices) && count($devices) > 0): ?>
                <?php foreach ($devices as $d): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($d['id'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($d['property_number'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($d['equipment_type'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($d['brand'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($d['model'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($d['serial_number'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($d['year_acquired'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($d['status'] ?? '-'); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-3">No assigned devices found for this user.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../../../public/assets/js/admin/assigned-devices.js"></script>
  <script>
    function printForm() {
      const card = document.getElementById('print-card');
      if (!card) { window.print(); return; }

      const bodyChildren = Array.from(document.body.children);
      const hidden = [];
      bodyChildren.forEach(c => {
        if (c !== card && c.style.display !== 'none') {
          hidden.push({el: c, display: c.style.display});
          c.style.display = 'none';
        }
      });

      // ensure card is visible
      card.style.display = '';

      window.print();

      setTimeout(() => {
        hidden.forEach(h => h.el.style.display = h.display);
      }, 200);
    }
  </script>
</body>
</html>