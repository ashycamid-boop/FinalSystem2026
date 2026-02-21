<?php
session_start();
// Simple debug page to show last POST and mapping from edit_requests_ongoing.php
// This is temporary — remove after debugging.
$lastPost = $_SESSION['last_post'] ?? null;
$lastMapping = $_SESSION['last_mapping'] ?? null;
$intended = $_SESSION['intended_redirect'] ?? null; // may be absent
function h($v){ return htmlspecialchars((string)$v); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Status Debug</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{padding:20px}</style>
</head>
<body>
  <div class="container">
    <h4>Temporary Status Debug</h4>
    <p class="text-muted">This page shows the last POST payload and mapping created when processing a status action.</p>
    <div class="mb-3">
      <div class="card">
        <div class="card-header">POST payload</div>
        <div class="card-body"><pre><?php echo $lastPost ? htmlspecialchars(print_r($lastPost, true)) : 'No POST recorded.'; ?></pre></div>
      </div>
    </div>
    <div class="mb-3">
      <div class="card">
        <div class="card-header">Mapping</div>
        <div class="card-body"><pre><?php echo $lastMapping ? htmlspecialchars($lastMapping) : 'No mapping recorded.'; ?></pre></div>
      </div>
    </div>
    <div class="mb-3">
      <div class="card">
        <div class="card-header">Intended Redirect</div>
        <div class="card-body"><pre><?php echo $intended ? htmlspecialchars($intended) : 'No intended redirect captured.'; ?></pre></div>
      </div>
    </div>
    <div class="d-flex gap-2">
      <?php if (!empty($intended)): ?>
        <a href="<?php echo h($intended); ?>" class="btn btn-primary">Proceed to intended page</a>
      <?php endif; ?>
      <a href="edit_requests.php?id=<?php echo isset($lastPost['id']) ? urlencode($lastPost['id']) : ''; ?>" class="btn btn-secondary">Back to Edit</a>
      <a href="javascript:window.history.back();" class="btn btn-light">Close</a>
    </div>
    <hr />
    <p class="small text-muted">After you reproduce the issue, copy the POST and Mapping text and paste here so I can analyze why 'complete' became 'Rejected'.</p>
  </div>
</body>
</html>
