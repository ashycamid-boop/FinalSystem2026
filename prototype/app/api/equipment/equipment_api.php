<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Equipment.php';

// Simple API for equipment management
// Supported actions: getAll, getById, create, update, delete

$action = isset($_GET['action']) ? $_GET['action'] : null;

$input = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if ($raw && $input === null) {
        // invalid JSON
        echo json_encode(['error' => 'Invalid JSON payload']);
        exit;
    }
}

// Helper: convert camelCase or PascalCase to snake_case
function to_snake_case($str) {
    if (!is_string($str) || $str === '') return '';
    // replace spaces and hyphens with underscore
    $str = preg_replace('/[\s\-]+/', '_', $str);
    // insert underscore before uppercase letters, then lowercase
    $snake = strtolower(preg_replace('/([a-z0-9])([A-Z])/', '\\1_\\2', $str));
    // cleanup non-alphanumeric/_
    $snake = preg_replace('/[^a-z0-9_]/', '', $snake);
    return $snake;
}

$equipment = new Equipment($pdo);

// Helper: generate QR image (uses api.qrserver.com) that points to public QR view page
function generate_qr_for_equipment($pdo, $equipmentId) {
    // Build public URL for the equipment details page
    if (!defined('BASE_URL')) {
        $basePath = '';
    } else {
        $basePath = BASE_url_safe(BASE_URL);
    }
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $publicLink = $scheme . '://' . $host . $basePath . '/public/qr_view.php?id=' . urlencode($equipmentId);

    // Call external QR API to get PNG
    $qrApi = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($publicLink);

    // prepare upload directory
    $uploadDir = __DIR__ . '/../../../public/uploads/qr';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    $filePath = $uploadDir . '/eq-' . $equipmentId . '.png';

    // fetch image (try file_get_contents, fallback to cURL)
    $img = false;
    if (ini_get('allow_url_fopen')) {
        $img = @file_get_contents($qrApi);
    }
    if ($img === false) {
        // try cURL
        if (function_exists('curl_init')) {
            $ch = curl_init($qrApi);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $img = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode < 200 || $httpCode >= 300) $img = false;
        }
    }
    if ($img === false) return false;

    $saved = @file_put_contents($filePath, $img);
    if ($saved === false) return false;

    // save relative path to DB
    $relative = 'uploads/qr/eq-' . $equipmentId . '.png';
    try {
        $stmt = $pdo->prepare('UPDATE equipment SET qr_code_path = :p WHERE id = :id');
        $stmt->bindParam(':p', $relative);
        $stmt->bindParam(':id', $equipmentId);
        $stmt->execute();
    } catch (Exception $e) {
        // ignore DB update errors
    }

    return $relative;
}

function BASE_url_safe($u) {
    // ensure no trailing slash
    return rtrim($u, '/');
}

try {
    switch ($action) {
        case 'generateQR':
            // Accept id via GET or POST JSON
            $id = 0;
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['id'])) $id = (int)$input['id'];
            if (!$id && isset($_GET['id'])) $id = (int)$_GET['id'];
            if (!$id) { echo json_encode(['success' => false, 'error' => 'Invalid id']); exit; }

            $res = generate_qr_for_equipment($pdo, $id);
            if ($res) {
                echo json_encode(['success' => true, 'qr_path' => $res]);
            } else {
                echo json_encode(['success' => false, 'error' => 'QR generation failed']);
            }
            break;
        case 'getAll':
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $status = isset($_GET['status']) ? trim($_GET['status']) : 'All';

            $stmt = $pdo->prepare('SELECT * FROM equipment ' .
                ($search !== '' ? "WHERE (property_number LIKE :s OR brand LIKE :s OR equipment_type LIKE :s)" : '') .
                ' ORDER BY id DESC');

            if ($search !== '') {
                $s = "%" . $search . "%";
                $stmt->bindParam(':s', $s);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Optionally filter by status on server side
            if ($status !== 'All') {
                $rows = array_values(array_filter($rows, function($r) use ($status) {
                    return isset($r['status']) && strtolower($r['status']) === strtolower($status);
                }));
            }

            // Map user IDs to names for accountable_person and actual_user
            $userIds = [];
            foreach ($rows as $r) {
                if (isset($r['accountable_person']) && is_numeric($r['accountable_person'])) $userIds[] = (int)$r['accountable_person'];
                if (isset($r['actual_user']) && is_numeric($r['actual_user'])) $userIds[] = (int)$r['actual_user'];
            }
            $userMap = [];
            if (count($userIds)) {
                $userIds = array_values(array_unique($userIds));
                // Build placeholders
                $placeholders = implode(',', array_fill(0, count($userIds), '?'));
                $ustmt = $pdo->prepare("SELECT id, full_name FROM users WHERE id IN ($placeholders)");
                foreach ($userIds as $i => $uid) $ustmt->bindValue($i+1, $uid, PDO::PARAM_INT);
                $ustmt->execute();
                $users = $ustmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($users as $u) {
                    $userMap[(int)$u['id']] = $u['full_name'];
                }
            }

            // Apply mapping and expose *_id fields
            foreach ($rows as &$r) {
                if (isset($r['accountable_person']) && is_numeric($r['accountable_person'])) {
                    $id = (int)$r['accountable_person'];
                    $r['accountable_person_id'] = $id;
                    $r['accountable_person'] = isset($userMap[$id]) ? $userMap[$id] : $r['accountable_person'];
                }
                if (isset($r['actual_user']) && is_numeric($r['actual_user'])) {
                    $id = (int)$r['actual_user'];
                    $r['actual_user_id'] = $id;
                    $r['actual_user'] = isset($userMap[$id]) ? $userMap[$id] : $r['actual_user'];
                }
            }

            echo json_encode($rows);
            break;

        case 'getById':
        case 'read_one':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) { echo json_encode(['error' => 'Invalid id']); exit; }
            $equipment->id = $id;
            if ($equipment->readOne()) {
                // convert object properties to array
                $data = [];
                foreach (get_object_vars($equipment) as $k => $v) {
                    if ($k === 'conn' || $k === 'table_name') continue;
                    $data[$k] = $v;
                }

                // If accountable_person/actual_user are numeric IDs, resolve to names
                $toResolve = [];
                if (isset($data['accountable_person']) && is_numeric($data['accountable_person'])) $toResolve[] = (int)$data['accountable_person'];
                if (isset($data['actual_user']) && is_numeric($data['actual_user'])) $toResolve[] = (int)$data['actual_user'];
                if (count($toResolve)) {
                    $toResolve = array_values(array_unique($toResolve));
                    $placeholders = implode(',', array_fill(0, count($toResolve), '?'));
                    $ust = $pdo->prepare("SELECT id, full_name FROM users WHERE id IN ($placeholders)");
                    foreach ($toResolve as $i => $uid) $ust->bindValue($i+1, $uid, PDO::PARAM_INT);
                    $ust->execute();
                    $users = $ust->fetchAll(PDO::FETCH_ASSOC);
                    $map = [];
                    foreach ($users as $u) $map[(int)$u['id']] = $u['full_name'];

                    if (isset($data['accountable_person']) && is_numeric($data['accountable_person'])) {
                        $id = (int)$data['accountable_person'];
                        $data['accountable_person_id'] = $id;
                        $data['accountable_person'] = isset($map[$id]) ? $map[$id] : $data['accountable_person'];
                    }
                    if (isset($data['actual_user']) && is_numeric($data['actual_user'])) {
                        $id = (int)$data['actual_user'];
                        $data['actual_user_id'] = $id;
                        $data['actual_user'] = isset($map[$id]) ? $map[$id] : $data['actual_user'];
                    }
                }

                echo json_encode($data);
            } else {
                echo json_encode(['error' => 'Equipment not found']);
            }
            break;

        case 'create':
            if (!$input) { echo json_encode(['error' => 'Missing payload']); exit; }

            // Map incoming fields (convert camelCase -> snake_case)
            foreach ($input as $k => $v) {
                $prop = to_snake_case($k);
                if (property_exists($equipment, $prop)) {
                    $equipment->$prop = $v;
                }
            }

            // default status
            if (empty($equipment->status)) $equipment->status = 'Available';

            // Validate required fields
            if (empty($equipment->property_number)) {
                echo json_encode(['error' => "Validation failed: property_number is required"]);
                exit;
            }

            if ($equipment->create()) {
                $id = $pdo->lastInsertId();

                // try generate QR and update path (best-effort)
                try { generate_qr_for_equipment($pdo, $id); } catch (Exception $e) {}

                echo json_encode(['success' => true, 'id' => $id]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to create equipment']);
            }
            break;

        case 'update':
            if (!$input) { echo json_encode(['error' => 'Missing payload']); exit; }
            $id = isset($input['id']) ? (int)$input['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
            if (!$id) { echo json_encode(['error' => 'Invalid id']); exit; }

            $equipment->id = $id;

            // assign fields (convert camelCase -> snake_case)
            foreach ($input as $k => $v) {
                if ($k === 'id') continue;
                $prop = to_snake_case($k);
                if (property_exists($equipment, $prop)) {
                    $equipment->$prop = $v;
                }
            }

            // simple validation
            if (isset($equipment->property_number) && $equipment->property_number === '') {
                echo json_encode(['error' => "Validation failed: property_number cannot be empty"]);
                exit;
            }

            // Debugging: log incoming input and mapped equipment properties (append-only)
            try {
                $dbgPath = __DIR__ . '/../../../storage/logs/equipment_update_debug.log';
                $dbg = "[" . date('Y-m-d H:i:s') . "] update request for id={$id}\n";
                $dbg .= "INPUT: " . json_encode($input, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
                $mapped = [];
                foreach (get_object_vars($equipment) as $k => $v) {
                    if ($k === 'conn' || $k === 'table_name') continue;
                    $mapped[$k] = $v;
                }
                $dbg .= "MAPPED: " . json_encode($mapped, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
                @file_put_contents($dbgPath, $dbg, FILE_APPEND | LOCK_EX);
            } catch (Exception $e) {
                // ignore logging failures
            }

            if ($equipment->update()) {
                // regenerate QR if property_number changed or no qr exists
                try { generate_qr_for_equipment($pdo, $id); } catch (Exception $e) {}

                // Return mapped properties so client can verify what was saved
                $after = [];
                try {
                    $equipment->readOne();
                    foreach (get_object_vars($equipment) as $k => $v) {
                        if ($k === 'conn' || $k === 'table_name') continue;
                        $after[$k] = $v;
                    }
                } catch (Exception $e) {}

                echo json_encode(['success' => true, 'id' => $id, 'saved' => $after]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to update equipment']);
            }
            break;

        case 'delete':
            // accept POST JSON or GET id
            $id = 0;
            if ($input && isset($input['id'])) $id = (int)$input['id'];
            if (!$id && isset($_GET['id'])) $id = (int)$_GET['id'];
            if (!$id) { echo json_encode(['error' => 'Invalid id']); exit; }

            $equipment->id = $id;
            if ($equipment->delete()) {
                echo json_encode(['success' => true, 'deleted' => $id]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete equipment']);
            }
            break;

        case 'getUsers':
            // Proxy to existing get_users endpoint if available
            try {
                $stmt = $pdo->prepare("SELECT id, full_name, sex FROM users WHERE status = 1 ORDER BY full_name ASC");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $users, 'users' => $users]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

?>
