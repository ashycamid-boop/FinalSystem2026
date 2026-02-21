<?php
// Simple runner to execute the migration SQL file above against your configured DB.
// Usage: php tools/run_migration_add_vehicle_columns.php

// Adjust path if your script location differs.
require_once __DIR__ . '/../config/db.php'; // expects $pdo

$sqlFile = __DIR__ . '/migrations/2026-02-02_add_vehicle_columns.sql';
if (!is_readable($sqlFile)) {
    echo "Migration SQL file not found: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
// Split on semicolons to support executing multiple statements safely.
$stmts = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));
if (empty($stmts)) {
    echo "No SQL statements found in migration file.\n";
    exit(1);
}

try {
    foreach ($stmts as $s) {
        if ($s === '' || stripos($s, '--') === 0) continue;
        $pdo->exec($s);
        echo "Executed: " . substr(trim($s), 0, 80) . "...\n";
    }
    echo "Migration completed.\n";
} catch (PDOException $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
