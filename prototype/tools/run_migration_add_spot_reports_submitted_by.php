<?php
// Runner for the submitted_by migration
// Usage: php tools/run_migration_add_spot_reports_submitted_by.php
require_once __DIR__ . '/../config/db.php'; // expects $pdo

$sqlFile = __DIR__ . '/migrations/2026-02-02_add_spot_reports_submitted_by.sql';
if (!is_readable($sqlFile)) {
    echo "Migration SQL file not found: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
$stmts = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));
try {
    foreach ($stmts as $s) {
        if ($s === '' || stripos($s, '--') === 0) continue;
        $pdo->exec($s);
        echo "Executed: " . substr(trim($s), 0, 120) . "...\n";
    }
    echo "Migration completed.\n";
} catch (PDOException $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
