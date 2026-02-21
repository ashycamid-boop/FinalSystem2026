<?php
// Display error message if exists in URL
$error = isset($_GET['err']) ? htmlspecialchars(urldecode($_GET['err'])) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="public/assets/css/login.css">
</head>
<body>
  <div class="login-bg">
    <div class="login-container">

      <div class="login-left">
        <div class="login-logo-card">
          <img src="public/assets/images/denr-logo.png" alt="DENR Logo" class="denr-logo">
          <div class="login-agency">CENRO NASIPIT</div>
        </div>
      </div>

      <div class="login-right">
        <div class="login-card">
          <div class="login-header">
            <div class="login-title">LOGIN</div>
            <img src="public/assets/images/bagong-pilipinas-logo.png" alt="Region Logo" class="region-logo">
          </div>

          <!-- Error Alert Box -->
          <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-circle me-2"></i>
              <strong>Login Failed:</strong> <?php echo $error; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <form action="app/auth/login.php" method="post" class="needs-validation" novalidate onsubmit="return validateLogin()">
            <div class="login-field mb-3">
              <label class="form-label">Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control" required>
                <div class="invalid-feedback">
                  Please provide a valid email.
                </div>
              </div>
            </div>
            
            <div class="login-field mb-3">
              <label class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="password" class="form-control" required>
                <button class="btn btn-outline-secondary password-toggle" type="button" onclick="togglePassword()">
                  <i class="bi bi-eye-slash"></i>
                </button>
                <div class="invalid-feedback">
                  Please provide a password.
                </div>
              </div>
            </div>

            <div class="login-options mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="rememberMe">
                <label class="form-check-label" for="rememberMe">
                  Remember Me
                </label>
              </div>
            </div>

            <button type="submit" class="btn btn-primary login-btn w-100">
              Login
            </button>
          </form>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    function validateLogin() {
      const email = document.querySelector('input[name="email"]').value.trim();
      const password = document.querySelector('input[name="password"]').value.trim();

      // Check if both fields are filled
      if (!email || !password) {
        showError('Please provide both email and password.');
        return false;
      }

      // Check if email is valid format
      if (!email.includes('@')) {
        showError('Please provide a valid email address.');
        return false;
      }

      return true; // Allow form submission
    }

    function showError(message) {
      // Remove existing error if present
      const existingAlert = document.querySelector('.alert');
      if (existingAlert) {
        existingAlert.remove();
      }

      // Create and insert error alert
      const alert = document.createElement('div');
      alert.className = 'alert alert-danger alert-dismissible fade show';
      alert.setAttribute('role', 'alert');
      alert.innerHTML = `
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Login Failed:</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
      
      const form = document.querySelector('form');
      form.parentElement.insertBefore(alert, form);
    }

    function togglePassword() {
      const pass = document.getElementById("password");
      const icon = document.querySelector(".password-toggle i");
      
      if (pass.type === "password") {
        pass.type = "text";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
      } else {
        pass.type = "password";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
      }
    }
  </script>
</body>
</html>
