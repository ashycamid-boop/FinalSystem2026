<?php
// Delegates save_request handling to the office_staff controller to avoid code duplication
session_start();

$officeController = __DIR__ . '/../../office_staff/controllers/save_request.php';
if (file_exists($officeController)) {
    require_once $officeController;
    return;
}

// If the delegated controller is missing, return a 404 response
header('HTTP/1.1 404 Not Found');
echo 'Controller not found';
exit;
