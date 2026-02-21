<?php
require_once __DIR__ . '/../app/config/db.php';
try {
    $stmt = $pdo->query("SHOW FULL COLUMNS FROM equipment");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo $c['Field'] . "\t" . $c['Type'] . "\t" . $c['Null'] . "\t" . $c['Default'] . "\t" . $c['Extra'] . "\n";
    }
} catch (PDOException $e) {
    echo "DB error: " . $e->getMessage() . "\n";
    exit(1);
}
