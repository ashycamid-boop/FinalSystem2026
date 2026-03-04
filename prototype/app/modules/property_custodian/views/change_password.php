<?php
session_start();

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'property_custodian') {
    header('Location: /prototype/index.php');
    exit;
}
?>
<style>
    :root{--primary:#2b6cb0;--muted:#6b7280}
    html,body{height:100%;margin:0;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:linear-gradient(180deg,#f3f7fb 0%,#eef4fb 50%);} 
    .cp-wrapper{display:flex;align-items:center;justify-content:center;min-height:calc(100vh - 80px);padding:24px}
    .cp-card{width:100%;max-width:540px;background:#fff;border-radius:12px;box-shadow:0 12px 30px rgba(16,24,40,0.08);overflow:hidden}
    .cp-body{padding:28px}
    .cp-title{text-align:center;margin:0 0 12px 0;font-size:20px;color:#0f172a}
    .cp-sub{font-size:13px;color:var(--muted);text-align:center;margin-bottom:18px}
    .cp-form-group{margin-bottom:14px}
    .cp-form-group label{display:block;font-weight:600;margin-bottom:6px;font-size:13px;color:#0f172a}
    .cp-input-wrapper{position:relative}
    .cp-input{width:100%;box-sizing:border-box;padding:10px 12px;padding-right:44px;border:1px solid #e6e9ef;border-radius:8px;font-size:14px;color:#0f172a}
    .cp-input:focus{outline:none;border-color:var(--primary);box-shadow:0 6px 18px rgba(43,108,176,0.12)}
    .cp-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:transparent;border:0;padding:6px;cursor:pointer;color:var(--muted)}
    .input-error{border-color:#ef4444 !important}
    .label-error{color:#ef4444 !important}
    .cp-error{color:#b91c1c;font-size:13px;margin-bottom:12px;display:none}
    .cp-btn{display:inline-block;width:100%;padding:10px 14px;background:var(--primary);color:#fff;border-radius:8px;border:0;font-weight:600;cursor:pointer}
    .cp-alert{background:#eef2ff;color:#1e3a8a;padding:10px;border-radius:8px;margin-bottom:12px;font-size:14px}
    .cp-back{background:var(--primary);border:0;color:#ffffff;font-weight:700;cursor:pointer;padding:7px 10px;border-radius:8px;font-size:13px;box-shadow:0 6px 18px rgba(43,108,176,0.12)}
    .cp-back:hover{filter:brightness(0.95);transform:translateY(-1px)}
    @media (max-width:520px){.cp-body{padding:20px}.cp-title{font-size:18px}}
</style>

<div class="cp-wrapper">
    <div class="cp-card">
        <div class="cp-body">
            <div style="display:flex;align-items:center;justify-content:flex-start;margin-bottom:8px">
                <button type="button" class="cp-back" onclick="goBack()">← Back</button>
            </div>
            <h4 class="cp-title">Change Password</h4>
            <div class="cp-sub">Enter your current password and choose a new secure password.</div>
            <?php if (!empty($_SESSION['cp_message'])): ?>
                <div class="cp-alert"><?php echo htmlspecialchars($_SESSION['cp_message']); unset($_SESSION['cp_message']); ?></div>
            <?php endif; ?>

            <form id="changePasswordForm" method="post" action="/prototype/app/modules/admin/controllers/change_password.php">
                <div class="cp-form-group">
                    <label for="current_password" id="lbl_current">Current password</label>
                    <div class="cp-input-wrapper">
                            <input type="password" id="current_password" name="current_password" class="cp-input" required>
                        <button type="button" class="cp-toggle" data-target="current_password" aria-label="Show current password"> 
                            <!-- eye-off (initial: hidden) -->
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6 0-10-7-10-7 .95-1.62 2.47-3.62 4.4-5.16" stroke="#111827" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M1 1l22 22" stroke="#111827" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>

                <div class="cp-form-group">
                    <label for="new_password" id="lbl_new">New password</label>
                    <div class="cp-input-wrapper">
                            <input type="password" id="new_password" name="new_password" class="cp-input" required minlength="8">
                        <button type="button" class="cp-toggle" data-target="new_password" aria-label="Show new password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6 0-10-7-10-7 .95-1.62 2.47-3.62 4.4-5.16" stroke="#111827" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M1 1l22 22" stroke="#111827" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>

                <div class="cp-form-group">
                    <label for="confirm_password" id="lbl_confirm">Confirm password</label>
                    <div class="cp-input-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" class="cp-input" required>
                        <button type="button" class="cp-toggle" data-target="confirm_password" aria-label="Show confirm password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6 0-10-7-10-7 .95-1.62 2.47-3.62 4.4-5.16" stroke="#111827" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M1 1l22 22" stroke="#111827" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>

                <div id="cp_error" class="cp-error"></div>
                <button type="submit" class="cp-btn">Change password</button>
            </form>
        </div>
    </div>
</div>

<script>
        function goBack(){
            try{
                if (document.referrer && document.referrer !== window.location.href) {
                    window.location = document.referrer;
                    return;
                }
            }catch(e){}
            if (history.length > 1) { history.back(); return; }
            window.location = '/prototype';
        }
// Toggle visibility for password fields
document.querySelectorAll('.cp-toggle').forEach(function(btn){
    btn.addEventListener('click', function(){
        var targetId = this.getAttribute('data-target');
        var input = document.getElementById(targetId);
        if (!input) return;
        var isPwd = input.getAttribute('type') === 'password';
        input.setAttribute('type', isPwd ? 'text' : 'password');
        // swap icon: simple eye / eye-off
        this.innerHTML = isPwd
            ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke="#111827" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="#111827" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
            : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6 0-10-7-10-7 .95-1.62 2.47-3.62 4.4-5.16" stroke="#111827" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M1 1l22 22" stroke="#111827" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    });
});

// Real-time validation for new/confirm password
var newInput = document.getElementById('new_password');
var confInput = document.getElementById('confirm_password');
var errBox = document.getElementById('cp_error');
var lblNew = document.getElementById('lbl_new');
var lblConf = document.getElementById('lbl_confirm');

function validateMatch(){
    var a = newInput.value || '';
    var b = confInput.value || '';
    errBox.style.display = 'none';
    lblNew.classList.remove('label-error');
    lblConf.classList.remove('label-error');
    newInput.classList.remove('input-error');
    confInput.classList.remove('input-error');
    if (b.length === 0) return;
    if (a !== b){
        errBox.textContent = 'New password and confirm password do not match.';
        errBox.style.display = 'block';
        lblConf.classList.add('label-error');
        confInput.classList.add('input-error');
        return false;
    }
    if (a.length > 0 && a.length < 8){
        errBox.textContent = 'New password must be at least 8 characters.';
        errBox.style.display = 'block';
        lblNew.classList.add('label-error');
        newInput.classList.add('input-error');
        return false;
    }
    return true;
}

newInput.addEventListener('input', validateMatch);
confInput.addEventListener('input', validateMatch);

document.getElementById('changePasswordForm').addEventListener('submit', function(e){
    if (!validateMatch()) e.preventDefault();
});
</script>