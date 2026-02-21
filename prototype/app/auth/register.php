<?php
// Server-side user registration handler
// Receives POST from add_user form and inserts into users table

session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../modules/admin/views/add_user.php');
    exit;
}

// Collect and sanitize
$firstName = trim($_POST['firstName'] ?? '');
$middleName = trim($_POST['middleName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$suffix = trim($_POST['suffix'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$contact = trim($_POST['contactNumber'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirmPassword'] ?? '';
$officeUnit = trim($_POST['officeUnit'] ?? '');
$role = trim($_POST['role'] ?? '');
$position = trim($_POST['position'] ?? '');

// Handle profile picture upload (optional)
$profile_picture_path = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_picture'];
    // Validate size (<=2MB) and type
    if ($file['size'] > 2 * 1024 * 1024) {
        header('Location: ../modules/admin/views/add_user.php?err=Profile+picture+too+large'); exit;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ['image/jpeg', 'image/png'])) {
        header('Location: ../modules/admin/views/add_user.php?err=Invalid+profile+picture+type'); exit;
    }

    // Ensure upload directory exists
    $uploadDir = __DIR__ . '/../../public/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = $mime === 'image/png' ? '.png' : '.jpg';
    $filename = time() . '_' . bin2hex(random_bytes(6)) . $ext;
    $dest = $uploadDir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        $profile_picture_path = 'public/uploads/' . $filename;
    }
}

// Basic validation
if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6 || $password !== $confirm || $role === '') {
    header('Location: ../modules/admin/views/add_user.php?err=Invalid+input');
    exit;
}

// Check if email exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    header('Location: ../modules/admin/views/user_management.php?err=Email+already+exists');
    exit;
}

// Compose full name
$fullName = $firstName;
if ($middleName !== '') $fullName .= ' ' . $middleName;
$fullName .= ' ' . $lastName;
if ($suffix !== '') $fullName .= ', ' . $suffix;

// Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert user with all fields (including position)
$insert = $pdo->prepare('INSERT INTO users (email, password_hash, full_name, contact_number, office_unit, profile_picture, role, position, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)');
try {
    $insert->execute([$email, $hash, $fullName, $contact, $officeUnit, $profile_picture_path, $role, $position]);
} catch (Exception $e) {
    header('Location: ../modules/admin/views/add_user.php?err=Unable+to+create+user');
    exit;
}

// Optionally insert contact info into audit_logs or user profile tables; omitted for brevity

header('Location: ../modules/admin/views/user_management.php?success=1');
exit;
