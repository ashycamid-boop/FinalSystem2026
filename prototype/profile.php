<?php
// Single entry point for the shared profile view
// Keeps a single URL: http://localhost/prototype/profile.php
// It simply includes the shared view so other modules can link to /prototype/profile.php

// Prevent direct access when running from unexpected contexts
if (!file_exists(__DIR__ . '/app/modules/shared/views/profile.php')) {
    http_response_code(404);
    echo 'Profile view not found.';
    exit;
}

require __DIR__ . '/app/modules/shared/views/profile.php';
