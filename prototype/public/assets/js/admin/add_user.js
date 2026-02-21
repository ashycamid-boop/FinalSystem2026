// ===== ADD USER PAGE JAVASCRIPT - Clean & Simple =====

document.addEventListener('DOMContentLoaded', function() {
  initializeAddUserForm();
  initializeProfileDropdown();
});

// Main form initialization
function initializeAddUserForm() {
  const form = document.getElementById('addUserForm');
  const passwordField = document.getElementById('password');
  const confirmPasswordField = document.getElementById('confirmPassword');
  
  if (form) {
    form.addEventListener('submit', handleFormSubmission);
  }
  
  if (passwordField && confirmPasswordField) {
    confirmPasswordField.addEventListener('input', validatePasswordMatch);
  }
  
  // Initialize real-time validation
  initializeFormValidation();
}

// Handle form submission
function handleFormSubmission(event) {
  event.preventDefault();
  
  if (!validateForm()) {
    return;
  }
  
  const formData = collectFormData();
  
  // Show loading state
  showFormLoading(true);
  
  // Simulate API call
  setTimeout(() => {
    showFormLoading(false);
    showNotification('User account created successfully!', 'success');
    
    // Redirect after short delay
    setTimeout(() => {
      window.location.href = 'user_management.php';
    }, 1500);
  }, 2000);
}

// Collect form data
function collectFormData() {
  return {
    firstName: getValue('firstName'),
    middleName: getValue('middleName'),
    lastName: getValue('lastName'),
    suffix: getValue('suffix'),
    email: getValue('email'),
    contactNumber: getValue('contactNumber'),
    password: getValue('password'),
    confirmPassword: getValue('confirmPassword'),
    officeUnit: getValue('officeUnit'),
    role: getValue('role')
  };
}

// Get form field value
function getValue(id) {
  const element = document.getElementById(id);
  return element ? element.value.trim() : '';
}

// Validate entire form
function validateForm() {
  let isValid = true;
  
  // Required fields validation
  const requiredFields = ['firstName', 'lastName', 'email', 'contactNumber', 'password', 'confirmPassword', 'officeUnit', 'role'];
  
  requiredFields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    if (field && !field.value.trim()) {
      showFieldError(field, 'This field is required');
      isValid = false;
    } else if (field) {
      clearFieldError(field);
    }
  });
  
  // Email validation
  const emailField = document.getElementById('email');
  if (emailField && emailField.value.trim()) {
    if (!isValidEmail(emailField.value.trim())) {
      showFieldError(emailField, 'Please enter a valid email address');
      isValid = false;
    }
  }
  
  // Password validation
  const passwordField = document.getElementById('password');
  if (passwordField && passwordField.value.trim()) {
    if (passwordField.value.length < 6) {
      showFieldError(passwordField, 'Password must be at least 6 characters');
      isValid = false;
    }
  }
  
  // Password match validation
  if (!validatePasswordMatch()) {
    isValid = false;
  }
  
  // Phone number validation
  const phoneField = document.getElementById('contactNumber');
  if (phoneField && phoneField.value.trim()) {
    if (!isValidPhoneNumber(phoneField.value.trim())) {
      showFieldError(phoneField, 'Please enter a valid phone number');
      isValid = false;
    }
  }
  
  return isValid;
}

// Password match validation
function validatePasswordMatch() {
  const passwordField = document.getElementById('password');
  const confirmPasswordField = document.getElementById('confirmPassword');
  
  if (!passwordField || !confirmPasswordField) return true;
  
  if (passwordField.value !== confirmPasswordField.value) {
    showFieldError(confirmPasswordField, 'Passwords do not match');
    return false;
  } else {
    clearFieldError(confirmPasswordField);
    return true;
  }
}

// Initialize real-time form validation
function initializeFormValidation() {
  const fields = document.querySelectorAll('.form-control-clean, .form-select-clean');
  
  fields.forEach(field => {
    field.addEventListener('blur', function() {
      validateSingleField(this);
    });
    
    field.addEventListener('input', function() {
      if (this.classList.contains('is-invalid')) {
        validateSingleField(this);
      }
    });
  });
}

// Validate single field
function validateSingleField(field) {
  const value = field.value.trim();
  const fieldId = field.id;
  
  // Clear previous validation
  clearFieldError(field);
  
  // Check if required
  if (field.hasAttribute('required') && !value) {
    showFieldError(field, 'This field is required');
    return false;
  }
  
  // Specific validations
  switch (fieldId) {
    case 'email':
      if (value && !isValidEmail(value)) {
        showFieldError(field, 'Please enter a valid email address');
        return false;
      }
      break;
    case 'contactNumber':
      if (value && !isValidPhoneNumber(value)) {
        showFieldError(field, 'Please enter a valid phone number');
        return false;
      }
      break;
    case 'password':
      if (value && value.length < 6) {
        showFieldError(field, 'Password must be at least 6 characters');
        return false;
      }
      break;
    case 'confirmPassword':
      return validatePasswordMatch();
  }
  
  // Show valid state for filled fields
  if (value) {
    showFieldSuccess(field);
  }
  
  return true;
}

// Show field error
function showFieldError(field, message) {
  field.classList.remove('is-valid');
  field.classList.add('is-invalid');
  
  // Remove existing feedback
  const existingFeedback = field.parentNode.querySelector('.invalid-feedback');
  if (existingFeedback) {
    existingFeedback.remove();
  }
  
  // Add error message
  const feedback = document.createElement('div');
  feedback.className = 'invalid-feedback';
  feedback.textContent = message;
  field.parentNode.appendChild(feedback);
}

// Show field success
function showFieldSuccess(field) {
  field.classList.remove('is-invalid');
  field.classList.add('is-valid');
  
  // Remove error feedback
  const existingFeedback = field.parentNode.querySelector('.invalid-feedback');
  if (existingFeedback) {
    existingFeedback.remove();
  }
}

// Clear field error
function clearFieldError(field) {
  field.classList.remove('is-invalid', 'is-valid');
  
  const feedback = field.parentNode.querySelector('.invalid-feedback');
  if (feedback) {
    feedback.remove();
  }
}

// Email validation
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

// Phone number validation
function isValidPhoneNumber(phone) {
  const phoneRegex = /^(\+63|0)?[9]\d{9}$/;
  return phoneRegex.test(phone.replace(/\s/g, ''));
}

// Profile photo upload simulation
function simulatePhotoUpload() {
  showNotification('Photo upload feature - This is a prototype interface', 'info');
}

// Show/hide form loading state
function showFormLoading(show) {
  const form = document.getElementById('addUserForm');
  const submitButton = document.querySelector('.btn-create');
  
  if (show) {
    form.classList.add('form-loading');
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Creating Account...';
    }
  } else {
    form.classList.remove('form-loading');
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.innerHTML = '<i class="fa fa-user-plus me-2"></i>Create Account';
    }
  }
}

// Notification system
function showNotification(message, type = 'info') {
  // Remove existing notifications
  const existingNotifications = document.querySelectorAll('.notification');
  existingNotifications.forEach(notification => notification.remove());
  
  const notification = document.createElement('div');
  notification.className = `notification notification-${type} alert alert-dismissible`;
  
  const iconClass = type === 'success' ? 'fa-check-circle' : 
                   type === 'error' ? 'fa-exclamation-circle' : 
                   'fa-info-circle';
  
  notification.innerHTML = `
    <div class="d-flex align-items-center">
      <i class="fa ${iconClass} me-2"></i>
      <span>${message}</span>
      <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
    </div>
  `;
  
  document.body.appendChild(notification);
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentElement) {
      notification.remove();
    }
  }, 5000);
}

// Initialize profile dropdown (reused from common functionality)
function initializeProfileDropdown() {
  const profileCard = document.getElementById('profileCard');
  const profileDropdown = document.getElementById('profileDropdown');
  
  if (!profileCard || !profileDropdown) return;
  
  let dropdownOpen = false;

  function toggleDropdown() {
    dropdownOpen = !dropdownOpen;
    if (dropdownOpen) {
      profileDropdown.classList.add('show');
    } else {
      profileDropdown.classList.remove('show');
    }
  }

  profileCard.addEventListener('click', function(e) {
    toggleDropdown();
    e.stopPropagation();
  });

  document.addEventListener('click', function(e) {
    if (!profileCard.contains(e.target)) {
      dropdownOpen = false;
      profileDropdown.classList.remove('show');
    }
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && dropdownOpen) {
      dropdownOpen = false;
      profileDropdown.classList.remove('show');
    }
  });
}