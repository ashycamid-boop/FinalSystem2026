<?php
declare(strict_types=1);

$source = dirname(__DIR__, 3) . '/enforcement_officer/views/actions/get_statistical_data.php';
if (!file_exists($source)) {
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'error' => 'Statistical data source not found']);
  exit;
}

require $source;
