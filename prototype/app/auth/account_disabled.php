<?php
// app/auth/account_disabled.php
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Account Disabled</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .centered-card {
      max-width: 400px;
      margin: 80px auto;
      box-shadow: 0 2px 16px rgba(0,0,0,0.08);
      border-radius: 12px;
      background: #fff;
      padding: 2rem 2.5rem;
      text-align: center;
    }
    .icon {
      font-size: 3rem;
      color: #dc3545;
      margin-bottom: 1rem;
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
  <div class="centered-card">
    <div class="icon"><i class="fa fa-ban"></i></div>
    <h2 class="mb-3">Account Disabled</h2>
    <p class="mb-4">Your account has been disabled. Please contact the administrator if you believe this is a mistake.</p>
    <a href="../../index.php" class="btn btn-primary">Back to Home</a>
  </div>
</body>
</html>
