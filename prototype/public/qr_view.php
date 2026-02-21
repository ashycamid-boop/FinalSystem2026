<?php
// Public QR code view page - displays equipment details when QR is scanned
require_once __DIR__ . '/../app/config/db.php';

$equipmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$equipmentId) {
    die('Invalid equipment ID');
}

// Fetch equipment details
try {
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
    $stmt->execute([$equipmentId]);
    $equipment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$equipment) {
        die('Equipment not found');
    }
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Details - <?php echo htmlspecialchars($equipment['property_number']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #ffffffff 0%, #ffffffff 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .equipment-card {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .card-header {
            background: #ffffff;
            padding: 18px 24px;
            display:flex;
            align-items:center;
            gap:16px;
            border-bottom: 1px solid #e6e6e6;
        }
        .header-left { flex: 0 0 auto; }
        .header-logo { width:84px; height:84px; display:block; }
        .header-center { flex:1 1 auto; text-align:center; }
        .header-center h1 { font-size:1.1rem; margin:0; color:#083a93; font-weight:700; }
        .header-center p { margin:3px 0 0 0; color:#556b8a; font-size:0.9rem; }
        .header-right { flex: 0 0 220px; text-align:right; }
        .header-property-badge { background:#083a93; color:#fff; padding:10px 14px; border-radius:10px; font-weight:700; letter-spacing:1px; display:inline-block; }
        @media (max-width:700px) {
            .card-header { flex-direction:column; text-align:center; gap:10px; }
            .header-right { text-align:center; width:100%; }
        }
        .card-body {
            padding: 30px;
        }
        .property-number {
            background: #083a93;
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 30px;
            letter-spacing: 2px;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-section h5 {
            color: #083a93;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #083a93;
        }
        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            flex: 0 0 200px;
            font-weight: 600;
            color: #555;
        }
        .info-value {
            flex: 1;
            color: #333;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        /* Status color palette (accessible, semantic) */
        .status-in-use {
            background: #198754; /* Bootstrap success (green) */
            color: #fff;
        }
        .status-available {
            background: #0d6efd; /* Bootstrap primary (blue) */
            color: #fff;
        }
        .status-maintenance {
            background: #ffc107; /* warning (yellow) */
            color: #212529;
        }
        .status-retired {
            background: #dc3545; /* danger (red) */
            color: #fff;
        }
        .qr-section {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-top: 20px;
        }
        .qr-section img {
            max-width: 200px;
            border: 3px solid #083a93;
            border-radius: 10px;
            padding: 10px;
            background: white;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .equipment-card {
                box-shadow: none;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="equipment-card">
        <div class="card-header">
            <div class="header-left">
                <img class="header-logo" src="assets/images/denr-logo.png" alt="DENR Logo">
            </div>

            <div class="header-center">
                <h1>Department of Environment and Natural Resources</h1>
                <p>RP GOVERNMENT PROPERTY</p>
            </div>

            <div class="header-right">
                <div class="header-property-badge">
                    <?php echo htmlspecialchars($equipment['property_number'] ?? ''); ?>
                </div>
            </div>
        </div>
        
            <div class="card-body">
            
            <!-- Basic Information -->
            <div class="info-section">
                <h5><i class="fas fa-info-circle"></i> Basic Information</h5>
                <div class="info-row">
                    <div class="info-label">Equipment Type:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['equipment_type'] ?? '-'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Brand:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['brand'] ?? '-'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Model:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['model'] ?? '-'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Serial Number:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['serial_number'] ?? '-'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status:</div>
                    <div class="info-value">
                        <?php 
                        $status = $equipment['status'] ?? 'Available';
                        $statusClass = 'status-available';
                        if (stripos($status, 'in use') !== false) $statusClass = 'status-in-use';
                        if (stripos($status, 'maintenance') !== false) $statusClass = 'status-maintenance';
                        if (stripos($status, 'retired') !== false) $statusClass = 'status-retired';
                        ?>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($status); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Assignment Information -->
            <div class="info-section">
                <h5><i class="fas fa-user"></i> Assignment Information</h5>
                <div class="info-row">
                    <div class="info-label">Accountable Person:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['accountable_person'] ?? '-'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Actual User:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['actual_user'] ?? '-'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Office/Division:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['office_division'] ?? '-'); ?></div>
                </div>
            </div>
            
            <!-- Technical Specifications (if applicable) -->
            <?php if (!empty($equipment['processor']) || !empty($equipment['ram_size']) || !empty($equipment['gpu'])): ?>
            <div class="info-section">
                <h5><i class="fas fa-cog"></i> Technical Specifications</h5>
                <?php if (!empty($equipment['processor'])): ?>
                <div class="info-row">
                    <div class="info-label">Processor:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['processor']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($equipment['ram_size'])): ?>
                <div class="info-row">
                    <div class="info-label">RAM:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['ram_size']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($equipment['gpu'])): ?>
                <div class="info-row">
                    <div class="info-label">GPU:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['gpu']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($equipment['os_version'])): ?>
                <div class="info-row">
                    <div class="info-label">Operating System:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['os_version']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($equipment['office_productivity'])): ?>
                <div class="info-row">
                    <div class="info-label">Office Suite:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['office_productivity']); ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Additional Information -->
            <div class="info-section">
                <h5><i class="fas fa-calendar"></i> Additional Information</h5>
                <div class="info-row">
                    <div class="info-label">Year Acquired:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['year_acquired'] ?? '-'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Shelf Life:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['shelf_life'] ?? '-'); ?></div>
                </div>
                <?php if (!empty($equipment['remarks'])): ?>
                <div class="info-row">
                    <div class="info-label">Remarks:</div>
                    <div class="info-value"><?php echo htmlspecialchars($equipment['remarks']); ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- QR Code Display -->
            <?php if (!empty($equipment['qr_code_path'])): ?>
            <div class="qr-section">
                <h6 class="mb-3">QR Code</h6>
                <img src="<?php echo htmlspecialchars($equipment['qr_code_path']); ?>" alt="QR Code">
                <p class="text-muted mt-2 mb-0" style="font-size: 0.85rem;">Scan this code to view equipment details</p>
            </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="text-center mt-4">
                <button onclick="window.print()" class="btn btn-primary print-btn">
                    <i class="fas fa-print"></i> Print Details
                </button>
                <button onclick="window.close()" class="btn btn-secondary print-btn ms-2">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
