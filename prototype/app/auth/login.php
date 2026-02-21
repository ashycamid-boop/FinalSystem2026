<?php
// --- Secure session cookie flags BEFORE session_start ---
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
           || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');

session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',
  'secure'   => $isHttps,  // set to true if always HTTPS
  'httponly' => true,
  'samesite' => 'Lax',
]);
session_start();

// from /app/auth/login.php to /app/config/db.php
require_once __DIR__ . '/../config/db.php';
if (!defined('BASE_URL')) { define('BASE_URL', '/prototype'); } // fallback

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . BASE_URL . '/index.php'); exit;
}

// Validate input
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
  header('Location: ' . BASE_URL . '/index.php?err=Please+provide+both+email+and+password'); exit;
}

// Simple throttling (per-session/IP)
$ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$key = "login_{$ip}";
if (!isset($_SESSION[$key])) $_SESSION[$key] = ['count' => 0, 'first' => time()];

$window = 300; 
$limit  = 10;  
if (time() - $_SESSION[$key]['first'] > $window) {
  $_SESSION[$key] = ['count' => 0, 'first' => time()];
}
if ($_SESSION[$key]['count'] >= $limit) {
  header('Location: ' . BASE_URL . '/index.php?err=Too+many+attempts,+try+again+later'); exit;
}

// Fetch user
$stmt = $pdo->prepare('SELECT id, email, password_hash, role, status, full_name FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

// Dummy hash (bcrypt of "password")
$dummyHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
$hashToCheck = $user['password_hash'] ?? $dummyHash;
$ok = password_verify($password, $hashToCheck);

// If disabled
if ($ok && $user && (int)$user['status'] !== 1) {
  $_SESSION[$key]['count']++;
  // Redirect back to login page with an error so the user remains on the login page
  header('Location: ' . BASE_URL . '/index.php?err=Account+disabled'); exit;
}

// Fail
if (!$ok || !$user) {
  $_SESSION[$key]['count']++;
  header('Location: ' . BASE_URL . '/index.php?err=Invalid+email+or+password.+Please+check+your+credentials'); exit;
}

// Include role-based routing
require_once __DIR__ . '/../config/role_permissions.php';
require_once __DIR__ . '/../config/role_router.php';

// Map database roles to session role constants
$roleMapping = [
    'Admin' => RolePermissions::ADMIN,
    'Enforcement Officer' => RolePermissions::ENFORCEMENT_OFFICER,
    'Enforcer' => RolePermissions::ENFORCER,
    'Property Custodian' => RolePermissions::PROPERTY_CUSTODIAN,
    'Office Staff' => RolePermissions::OFFICE_STAFF
];

// Get mapped role or default to office staff
$sessionRole = $roleMapping[$user['role']] ?? RolePermissions::OFFICE_STAFF;

// Success
session_regenerate_id(true);
$_SESSION['uid']   = (int)$user['id'];
$_SESSION['email'] = $user['email'];
$_SESSION['role']  = $user['role']; // Original database role
$_SESSION['user_role'] = $sessionRole; // Mapped role for permissions system
$_SESSION['full_name'] = $user['full_name'];

// Update last_login
$pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);

// Role-based redirect to dashboards
$destinations = [
    RolePermissions::ADMIN => '../../app/modules/admin/views/dashboard.php',
    RolePermissions::ENFORCEMENT_OFFICER => '../../app/modules/enforcement_officer/views/dashboard.php',
    RolePermissions::ENFORCER => '../../app/modules/enforcer/views/dashboard.php',
    RolePermissions::PROPERTY_CUSTODIAN => '../../app/modules/property_custodian/views/dashboard.php',
    RolePermissions::OFFICE_STAFF => '../../app/modules/office_staff/views/dashboard.php',
];

$redirectUrl = $destinations[$sessionRole] ?? '../../dashboard.php';
header('Location: ' . $redirectUrl);
exit;
