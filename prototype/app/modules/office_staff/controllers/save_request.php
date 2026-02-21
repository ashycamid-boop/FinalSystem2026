<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Method not allowed');
}

require_once __DIR__ . '/../../../config/db.php';

// Determine if saving draft
$isDraft = !empty($_POST['save_draft']);

// Basic server-side validation (skip for draft)
if (!$isDraft) {
    $required = [
        'ticket_no', 'ticket_date', 'requester_name', 'requester_position', 'requester_office',
        'requester_division', 'requester_phone', 'requester_email', 'request_type', 'request_description'
    ];
    foreach ($required as $f) {
        if (empty($_POST[$f])) {
            $_SESSION['request_error'] = "Missing required field: $f";
            header('Location: ../views/new_requests.php');
            exit;
        }
    }
}

$ticket_no = trim($_POST['ticket_no']);
$ticket_date_raw = trim($_POST['ticket_date']);
// ticket_date expected format mm/dd/YYYY -> convert to YYYY-MM-DD
$ticket_date = null;
try {
    $d = DateTime::createFromFormat('m/d/Y', $ticket_date_raw);
    if ($d) $ticket_date = $d->format('Y-m-d');
} catch (Exception $e) {
}
if (!$ticket_date) {
    // fallback: try to parse
    $ticket_date = date('Y-m-d');
}

$requester_name = trim($_POST['requester_name']);
$requester_position = trim($_POST['requester_position']);
$requester_office = trim($_POST['requester_office']);
$requester_division = trim($_POST['requester_division']);
$requester_phone = trim($_POST['requester_phone']);
$requester_email = trim($_POST['requester_email']);
$request_type = trim($_POST['request_type']);
$request_description = trim($_POST['request_description']);

$uploaderId = $_SESSION['uid'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

// Prepare upload directory
$uploadDir = __DIR__ . '/../../../../public/uploads/signatures/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

function saveBase64Image($base64, $pathPrefix)
{
    if (empty($base64)) return null;
    if (preg_match('/^data:(image\/[a-zA-Z]+);base64,/', $base64, $m)) {
        $data = substr($base64, strpos($base64, ',') + 1);
        $decoded = base64_decode($data);
        if ($decoded === false) return null;
        $ext = 'png';
        $filename = $pathPrefix . '_' . time() . '.' . $ext;
        global $uploadDir;
        $full = $uploadDir . $filename;
        if (file_put_contents($full, $decoded) === false) return null;
        // Return web-accessible path relative to project root
        return 'public/uploads/signatures/' . $filename;
    }
    return null;
}

$requester_sig_path = saveBase64Image($_POST['requester_signature_data'] ?? '', 'sig_' . preg_replace('/[^a-z0-9_-]/i','', $ticket_no) . '_requester');
$auth1_sig_path = saveBase64Image($_POST['auth1_signature_data'] ?? '', 'sig_' . preg_replace('/[^a-z0-9_-]/i','', $ticket_no) . '_auth1');
$auth2_sig_path = saveBase64Image($_POST['auth2_signature_data'] ?? '', 'sig_' . preg_replace('/[^a-z0-9_-]/i','', $ticket_no) . '_auth2');

try {
    // New submissions should be 'pending' so admin can review/update status
    $status = $isDraft ? 'draft' : 'pending';

    // If a request with the same ticket_no exists, update that row instead of inserting a duplicate
    $check = $pdo->prepare("SELECT id FROM service_requests WHERE ticket_no = ? LIMIT 1");
    $check->execute([$ticket_no]);
    $existingId = $check->fetchColumn();

    if ($existingId) {
        // Build update SQL, include signature paths only when provided
        $fields = [
            'ticket_date' => $ticket_date,
            'requester_name' => $requester_name,
            'requester_position' => $requester_position,
            'requester_office' => $requester_office,
            'requester_division' => $requester_division,
            'requester_phone' => $requester_phone,
            'requester_email' => $requester_email,
            'request_type' => $request_type,
            'request_description' => $request_description,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $sqlParts = [];
        $params = [];
        foreach ($fields as $k => $v) {
            $sqlParts[] = "`$k` = ?";
            $params[] = $v;
        }

        if (!empty($requester_sig_path)) { $sqlParts[] = '`requester_signature_path` = ?'; $params[] = $requester_sig_path; }
        if (!empty($auth1_sig_path)) { $sqlParts[] = '`auth1_signature_path` = ?'; $params[] = $auth1_sig_path; }
        if (!empty($auth2_sig_path)) { $sqlParts[] = '`auth2_signature_path` = ?'; $params[] = $auth2_sig_path; }

        $sql = 'UPDATE service_requests SET ' . implode(', ', $sqlParts) . ' WHERE id = ?';
        $params[] = $existingId;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['request_success'] = 'Existing request updated successfully';
        header('Location: ../views/service_requests.php?updated=1');
        exit;
    } else {
        // For new rows, generate canonical ticket_no server-side to ensure sequential numbering per year/month
        // Format: YYYY-MM-XXXX (e.g. 2026-01-0001) where the 4-digit suffix increments per year+month
        try {
            $year = date('Y');
            $month = date('m');
            $like = $year . '-' . $month . '-%';
            $maxStmt = $pdo->prepare("SELECT MAX(CAST(RIGHT(ticket_no,4) AS UNSIGNED)) FROM service_requests WHERE ticket_no LIKE ?");
            $maxStmt->execute([$like]);
            $maxVal = (int)$maxStmt->fetchColumn();
            $next = $maxVal + 1;
            $ticket_no = sprintf('%s-%s-%04d', $year, $month, $next);
        } catch (Exception $e) {
            error_log('ticket_no generation error: ' . $e->getMessage());
            // fallback to timestamp-based id if generation fails
            $ticket_no = date('Y-m-d-His') . '-' . bin2hex(random_bytes(2));
        }

        // Insert new row
        $sql = "INSERT INTO service_requests
            (ticket_no, ticket_date, requester_name, requester_position, requester_office, requester_division,
             requester_phone, requester_email, request_type, request_description,
             requester_signature_path, auth1_signature_path, auth2_signature_path, status, created_by)
            VALUES
            (:ticket_no, :ticket_date, :requester_name, :requester_position, :requester_office, :requester_division,
             :requester_phone, :requester_email, :request_type, :request_description,
             :requester_signature_path, :auth1_signature_path, :auth2_signature_path, :status, :created_by)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ticket_no' => $ticket_no,
            ':ticket_date' => $ticket_date,
            ':requester_name' => $requester_name,
            ':requester_position' => $requester_position,
            ':requester_office' => $requester_office,
            ':requester_division' => $requester_division,
            ':requester_phone' => $requester_phone,
            ':requester_email' => $requester_email,
            ':request_type' => $request_type,
            ':request_description' => $request_description,
            ':requester_signature_path' => $requester_sig_path,
            ':auth1_signature_path' => $auth1_sig_path,
            ':auth2_signature_path' => $auth2_sig_path,
            ':status' => $status,
            ':created_by' => $uploaderId
        ]);

        $_SESSION['request_success'] = 'Request saved successfully';
        if ($isDraft) {
            header('Location: ../views/new_requests.php?draft=1');
        } else {
            header('Location: ../views/service_requests.php?created=1');
        }
        exit;
    }
} catch (Exception $e) {
    error_log('save_request error: ' . $e->getMessage());
    $_SESSION['request_error'] = 'Unable to save request';
    header('Location: ../views/new_requests.php');
    exit;
}
