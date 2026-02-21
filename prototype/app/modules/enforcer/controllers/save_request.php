<?php
// Simple wrapper to reuse office_staff save_request logic
// Keeps the enforcer form action pointing to ../controllers/save_request.php
// and avoids duplicating code.
session_start();

// Path to office_staff controller
$officeController = __DIR__ . '/../../office_staff/controllers/save_request.php';
if (file_exists($officeController)) {
    require_once $officeController;
    return;
}

// If the delegated controller is missing, return 404
header('HTTP/1.1 404 Not Found');
echo 'Controller not found';
exit;
