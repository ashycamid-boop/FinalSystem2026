<?php
require_once __DIR__ . '/../app/config/db.php';

$id = isset($argv[1]) ? (int)$argv[1] : 1;
try {
    $stmt = $pdo->prepare('SELECT id, property_number, shelf_life, updated_at, created_at FROM equipment WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo "No equipment found with id={$id}\n";
        exit(1);
    }
    echo "id: " . $row['id'] . "\n";
    echo "property_number: " . ($row['property_number'] ?? '') . "\n";
    echo "shelf_life: " . ($row['shelf_life'] ?? '(null)') . "\n";
    echo "created_at: " . ($row['created_at'] ?? '(null)') . "\n";
    echo "updated_at: " . ($row['updated_at'] ?? '(null)') . "\n";
} catch (PDOException $e) {
    echo "DB error: " . $e->getMessage() . "\n";
    exit(2);
}
