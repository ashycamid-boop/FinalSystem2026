<?php
// Simple migration runner for local use only.
// Usage: open in browser (e.g. http://localhost/prototype/app/tools/run_migrations.php)
// WARNING: Always BACKUP your DB before applying migrations.

require_once __DIR__ . '/../config/db.php';

$migrations = [
    __DIR__ . '/../database/add_auth_fields_service_requests.sql',
    __DIR__ . '/../database/create_service_request_actions.sql',
];

function load_sql_file($path) {
    if (!file_exists($path)) return null;
    return file_get_contents($path);
}

$results = [];
$apply = isset($_GET['apply']) && ($_GET['apply'] === '1');

if ($apply) {
    try {
        $pdo->beginTransaction();
        foreach ($migrations as $m) {
            $sql = load_sql_file($m);
            if ($sql === null) {
                throw new Exception("Migration file not found: $m");
            }
            // Execute the SQL; some files contain a single statement
            $pdo->exec($sql);
            $results[] = ['file' => $m, 'status' => 'ok'];
        }
        $pdo->commit();
    } catch (Exception $e) {
        try { $pdo->rollBack(); } catch (Exception $rb) {}
        $results[] = ['file' => $m ?? 'unknown', 'status' => 'error', 'message' => $e->getMessage()];
    }
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Run Migrations</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;padding:16px} pre{background:#f7f7f7;padding:8px;border:1px solid #ddd;}</style>
</head>
<body>
  <h2>Migration Runner (local use only)</h2>
  <p><strong>Warning:</strong> Back up your database before applying these migrations.</p>

  <?php if (!$apply): ?>
    <h3>Pending migration files</h3>
    <ul>
      <?php foreach ($migrations as $m): ?>
        <li><?php echo htmlspecialchars($m); ?></li>
      <?php endforeach; ?>
    </ul>

    <?php foreach ($migrations as $m): $sql = load_sql_file($m); ?>
      <h4><?php echo htmlspecialchars(basename($m)); ?></h4>
      <?php if ($sql === null): ?>
        <div style="color:crimson">File not found: <?php echo htmlspecialchars($m); ?></div>
      <?php else: ?>
        <pre><?php echo htmlspecialchars($sql); ?></pre>
      <?php endif; ?>
    <?php endforeach; ?>

    <form method="get">
      <input type="hidden" name="apply" value="1">
      <button type="submit">Apply migrations</button>
    </form>
  <?php else: ?>
    <h3>Results</h3>
    <ul>
      <?php foreach ($results as $r): ?>
        <li>
          <?php echo htmlspecialchars($r['file']); ?> -
          <?php if ($r['status'] === 'ok'): ?>
            <strong style="color:green">OK</strong>
          <?php else: ?>
            <strong style="color:red">ERROR</strong>: <?php echo htmlspecialchars($r['message'] ?? 'unknown'); ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
    <p><a href="run_migrations.php">Back</a></p>
  <?php endif; ?>

</body>
</html>
