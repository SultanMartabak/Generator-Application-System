<?= $this->extend('partials/main_layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-center align-items-center m-3" style="min-height: 80vh;">
    <div class="container" style="max-width: 500px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Ganti Password</h5>
            </div>
            <div class="card-body">
               
                <form id="changePasswordForm" method="post" action="<?= site_url('password/update') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="old_password" class="form-label">Password Lama</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="old_password" id="old_password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleOldPassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="oldPassMsg" class="form-text text-danger mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="new_password" id="new_password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="password-strength-meter" class="mt-2">
                            <div class="progress" style="height: 5px;">
                                <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small id="password-strength-text" class="form-text"></small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="confirmPassMsg" class="form-text text-danger mt-1"></div>
                    </div>
                    <button type="submit" id="submitBtn" class="btn btn-primary w-100" disabled>Ganti Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Element Selectors ---
    const form = document.getElementById('changePasswordForm');
    const oldPasswordInput = document.getElementById('old_password');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submitBtn');

    const oldPassMsg = document.getElementById('oldPassMsg');
    const confirmPassMsg = document.getElementById('confirmPassMsg');
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');

    // --- State Variables ---
    let isOldPasswordValid = false;
    let isNewPasswordStrong = false;
    let doPasswordsMatch = false;

    // --- Helper Function: Toggle Password Visibility ---
    const togglePasswordVisibility = (input, button) => {
        const icon = button.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    };

    // Attach toggle listeners
    document.getElementById('toggleOldPassword').addEventListener('click', () => togglePasswordVisibility(oldPasswordInput, document.getElementById('toggleOldPassword')));
    document.getElementById('toggleNewPassword').addEventListener('click', () => togglePasswordVisibility(newPasswordInput, document.getElementById('toggleNewPassword')));
    document.getElementById('toggleConfirmPassword').addEventListener('click', () => togglePasswordVisibility(confirmPasswordInput, document.getElementById('toggleConfirmPassword')));

    // --- Helper Function: Update Submit Button State ---
    const updateSubmitButtonState = () => {
        submitBtn.disabled = !(isOldPasswordValid && isNewPasswordStrong && doPasswordsMatch);
    };

    // --- Validation Logic ---

    // 1. Check Old Password (AJAX)
    oldPasswordInput.addEventListener('blur', function() {
        const oldPass = oldPasswordInput.value;
        if (oldPass.length === 0) {
            oldPassMsg.textContent = '';
            isOldPasswordValid = false;
            updateSubmitButtonState();
            return;
        }

        // Use Fetch API for a more modern approach
        fetch('<?= site_url('password/checkoldpassword') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            },
            body: 'old_password=' + encodeURIComponent(oldPass)
        })
        .then(response => response.json())
        .then(data => {
            if (data.valid) {
                oldPassMsg.textContent = '';
                oldPasswordInput.classList.remove('is-invalid');
                oldPasswordInput.classList.add('is-valid');
                isOldPasswordValid = true;
            } else {
                oldPassMsg.textContent = 'Password lama salah';
                oldPasswordInput.classList.add('is-invalid');
                oldPasswordInput.classList.remove('is-valid');
                isOldPasswordValid = false;
            }
            updateSubmitButtonState();
        })
        .catch(error => {
            console.error('Error:', error);
            oldPassMsg.textContent = 'Terjadi kesalahan saat validasi.';
            isOldPasswordValid = false;
            updateSubmitButtonState();
        });
    });

    // 2. Check New Password Strength
    newPasswordInput.addEventListener('input', function() {
        const password = newPasswordInput.value;
        let score = 0;
        let feedback = [];

        if (password.length >= 8) { score += 25; } else { feedback.push('minimal 8 karakter'); }
        if (/[A-Z]/.test(password)) { score += 25; } else { feedback.push('huruf kapital'); }
        if (/[a-z]/.test(password)) { score += 25; } else { feedback.push('huruf kecil'); }
        if (/\d/.test(password)) { score += 25; } else { feedback.push('angka'); }

        strengthBar.style.width = score + '%';
        if (score < 50) { strengthBar.className = 'progress-bar bg-danger'; }
        else if (score < 100) { strengthBar.className = 'progress-bar bg-warning'; }
        else { strengthBar.className = 'progress-bar bg-success'; }
        
        strengthText.textContent = (password.length > 0 && feedback.length > 0) ? 'Saran: ' + feedback.join(', ') : '';
        isNewPasswordStrong = (score === 100);
        
        validatePasswordConfirmation();
        updateSubmitButtonState();
    });

    // 3. Check Password Confirmation
    const validatePasswordConfirmation = () => {
        if (newPasswordInput.value && confirmPasswordInput.value) {
            if (newPasswordInput.value === confirmPasswordInput.value) {
                confirmPassMsg.textContent = '';
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
                doPasswordsMatch = true;
            } else {
                confirmPassMsg.textContent = 'Konfirmasi password tidak cocok';
                confirmPasswordInput.classList.add('is-invalid');
                confirmPasswordInput.classList.remove('is-valid');
                doPasswordsMatch = false;
            }
        } else {
            confirmPassMsg.textContent = '';
            confirmPasswordInput.classList.remove('is-invalid', 'is-valid');
            doPasswordsMatch = false;
        }
        updateSubmitButtonState();
    };

    confirmPasswordInput.addEventListener('input', validatePasswordConfirmation);

    // --- Final Form Submission Check (as a fallback) ---
    form.addEventListener('submit', function(e) {
        if (!isOldPasswordValid || !isNewPasswordStrong || !doPasswordsMatch) {
            e.preventDefault();
            alert('Harap perbaiki semua isian sebelum melanjutkan.');
        }
    });
});
</script>
<?= $this->endSection() ?>
