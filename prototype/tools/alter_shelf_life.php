<?php
require_once __DIR__ . '/../app/config/db.php';
try {
    echo "Altering column shelf_life to VARCHAR(50)...\n";
    $pdo->exec("ALTER TABLE equipment MODIFY shelf_life VARCHAR(50) NULL");
    echo "ALTER TABLE succeeded.\n";

    // update the test row if provided
    $id = isset($argv[1]) ? (int)$argv[1] : 1;
    $new = isset($argv[2]) ? $argv[2] : 'Beyond 5 Years';
    $stmt = $pdo->prepare('UPDATE equipment SET shelf_life = :s WHERE id = :id');
    $stmt->bindParam(':s', $new);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $res = $stmt->execute();
    if ($res) echo "Updated equipment id={$id} shelf_life to '{$new}'.\n";
    else echo "Failed to update equipment row.\n";
} catch (PDOException $e) {
    echo "DB error: " . $e->getMessage() . "\n";
    exit(1);
}
