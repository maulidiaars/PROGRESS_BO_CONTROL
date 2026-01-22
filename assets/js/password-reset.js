$(document).ready(function() {
    console.log('🔐 Password reset module loaded');
    
    // Check if user needs to reset password
    function checkPasswordReset() {
        if (typeof forcePasswordReset !== 'undefined' && forcePasswordReset) {
            console.log('⚠️ User needs to reset password');
            $('#passwordResetModal').modal('show');
            blockDashboard();
        }
    }
    
    // Block dashboard access
    function blockDashboard() {
        $('#main, #footer, #header').css({
            'filter': 'blur(5px)',
            'pointer-events': 'none',
            'user-select': 'none'
        });
    }
    
    // Unblock dashboard
    function unblockDashboard() {
        $('#main, #footer, #header').css({
            'filter': 'none',
            'pointer-events': 'auto',
            'user-select': 'auto'
        });
    }
    
    // Elements
    const $modal = $('#passwordResetModal');
    const $form = $('#resetPasswordForm');
    const $currentPassword = $('#currentPassword');
    const $newPassword = $('#newPassword');
    const $confirmPassword = $('#confirmPassword');
    const $submitBtn = $('#submitResetPasswordBtn');
    const $resetBtnText = $('#resetBtnText');
    const $resetBtnSpinner = $('#resetBtnSpinner');
    const $errorAlert = $('#resetErrorAlert');
    const $successAlert = $('#resetSuccessAlert');
    const $strengthBar = $('#passwordStrengthBar');
    const $strengthText = $('#strengthText');
    const $matchIndicator = $('#passwordMatchIndicator');
    
    // Toggle password visibility
    function togglePasswordVisibility($input, $button) {
        const type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        const $icon = $button.find('i');
        $icon.toggleClass('bi-eye bi-eye-slash');
        $input.focus();
    }
    
    $('#toggleCurrentPassword').click(function() {
        togglePasswordVisibility($currentPassword, $(this));
    });
    
    $('#toggleNewPassword').click(function() {
        togglePasswordVisibility($newPassword, $(this));
    });
    
    $('#toggleConfirmPassword').click(function() {
        togglePasswordVisibility($confirmPassword, $(this));
    });
    
    // Password strength checker
    function checkPasswordStrength(password) {
        let score = 0;
        const requirements = {
            length: password.length >= 8,
            upper: /[A-Z]/.test(password),
            lower: /[a-z]/.test(password),
            number: /\d/.test(password),
            special: /[@$!%*?&]/.test(password)
        };
        
        // Calculate score
        if (requirements.length) score += 20;
        if (requirements.upper) score += 20;
        if (requirements.lower) score += 20;
        if (requirements.number) score += 20;
        if (requirements.special) score += 20;
        
        // Update strength meter
        $strengthBar.css('width', score + '%');
        
        // Set color and text based on score
        let strengthClass, strengthText;
        if (score >= 80) {
            strengthClass = 'strength-very-strong';
            strengthText = 'Very Strong';
        } else if (score >= 60) {
            strengthClass = 'strength-strong';
            strengthText = 'Strong';
        } else if (score >= 40) {
            strengthClass = 'strength-good';
            strengthText = 'Good';
        } else if (score >= 20) {
            strengthClass = 'strength-fair';
            strengthText = 'Fair';
        } else {
            strengthClass = 'strength-weak';
            strengthText = 'Weak';
        }
        
        // Update UI
        $strengthBar.removeClass('strength-weak strength-fair strength-good strength-strong strength-very-strong');
        $strengthBar.addClass(strengthClass);
        $strengthText.text(strengthText);
        
        return requirements;
    }
    
    $newPassword.on('input', function() {
        const password = $(this).val();
        checkPasswordStrength(password);
    });
    
    // Password confirmation check
    function checkPasswordMatch() {
        const newPass = $newPassword.val();
        const confirmPass = $confirmPassword.val();
        
        if (!confirmPass) {
            $matchIndicator.html('');
            return false;
        }
        
        if (newPass === confirmPass) {
            $matchIndicator.html(`
                <div class="match-indicator match">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Passwords match
                </div>
            `);
            return true;
        } else {
            $matchIndicator.html(`
                <div class="match-indicator mismatch">
                    <i class="bi bi-x-circle-fill me-1"></i>
                    Passwords do not match
                </div>
            `);
            return false;
        }
    }
    
    $confirmPassword.on('input', checkPasswordMatch);
    
    // Close modal (logout)
    function handleLogout() {
        Swal.fire({
            title: 'Logout?',
            text: 'You must reset your password to access the dashboard.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'auth/logout.php';
            }
        });
    }
    
    $('#closeResetModalBtn, #closeResetModalBtn2').click(handleLogout);
    
    // Form submission
    $submitBtn.click(function(e) {
        e.preventDefault();
        submitResetPassword();
    });
    
    // Enter key support
    $form.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            submitResetPassword();
        }
    });
    
    function submitResetPassword() {
        // Reset alerts
        $errorAlert.addClass('d-none');
        $successAlert.addClass('d-none');
        
        // Get values
        const currentPass = $currentPassword.val();
        const newPass = $newPassword.val();
        const confirmPass = $confirmPassword.val();
        
        // Validation
        let isValid = true;
        let errorMessage = '';
        
        if (!currentPass) {
            errorMessage = 'Please enter your current password';
            isValid = false;
        } else if (!newPass) {
            errorMessage = 'Please enter a new password';
            isValid = false;
        } else if (!confirmPass) {
            errorMessage = 'Please confirm your new password';
            isValid = false;
        } else if (newPass.length < 8) {
            errorMessage = 'Password must be at least 8 characters long';
            isValid = false;
        } else if (!/[A-Z]/.test(newPass)) {
            errorMessage = 'Password must contain at least one uppercase letter';
            isValid = false;
        } else if (!/[a-z]/.test(newPass)) {
            errorMessage = 'Password must contain at least one lowercase letter';
            isValid = false;
        } else if (!/\d/.test(newPass)) {
            errorMessage = 'Password must contain at least one number';
            isValid = false;
        } else if (!/[@$!%*?&]/.test(newPass)) {
            errorMessage = 'Password must contain at least one special character (@$!%*?&)';
            isValid = false;
        } else if (newPass !== confirmPass) {
            errorMessage = 'New passwords do not match';
            isValid = false;
        } else if (currentPass === newPass) {
            errorMessage = 'New password must be different from current password';
            isValid = false;
        }
        
        if (!isValid) {
            showError(errorMessage);
            return;
        }
        
        // Submit to server
        sendResetRequest(currentPass, newPass, confirmPass);
    }
    
    function sendResetRequest(currentPass, newPass, confirmPass) {
        // Show loading state
        $submitBtn.prop('disabled', true);
        $resetBtnText.text('Processing...');
        $resetBtnSpinner.removeClass('d-none');
        
        // AJAX request
        $.ajax({
            url: 'api/reset_password.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                current_password: currentPass,
                new_password: newPass,
                confirm_password: confirmPass
            }),
            success: function(response) {
                console.log('Reset response:', response);
                
                if (response.success) {
                    showSuccess(response.message);
                    
                    // Auto close modal and refresh after 2 seconds
                    setTimeout(function() {
                        $modal.modal('hide');
                        unblockDashboard();
                        
                        // Clear form
                        $form[0].reset();
                        $strengthBar.css('width', '0%');
                        $strengthText.text('Weak');
                        $matchIndicator.html('');
                        
                        // Refresh page to update session
                        window.location.reload();
                        
                    }, 2000);
                    
                } else {
                    showError(response.error || 'Failed to reset password');
                    $submitBtn.prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Reset error:', xhr.responseText);
                
                let errorMsg = 'Network error. Please try again.';
                if (xhr.responseText) {
                    try {
                        const err = JSON.parse(xhr.responseText);
                        errorMsg = err.error || errorMsg;
                    } catch (e) {
                        errorMsg = xhr.statusText || errorMsg;
                    }
                }
                
                showError(errorMsg);
                $submitBtn.prop('disabled', false);
            },
            complete: function() {
                $resetBtnText.text('Reset Password');
                $resetBtnSpinner.addClass('d-none');
            }
        });
    }
    
    function showError(message) {
        $errorAlert.find('#errorMessage').text(message);
        $errorAlert.removeClass('d-none');
        
        // Auto hide after 5 seconds
        setTimeout(function() {
            $errorAlert.addClass('d-none');
        }, 5000);
    }
    
    function showSuccess(message) {
        $successAlert.find('#successMessage').text(message);
        $successAlert.removeClass('d-none');
    }
    
    // Auto-focus on current password field when modal opens
    $modal.on('shown.bs.modal', function() {
        $currentPassword.focus();
        blockDashboard();
    });
    
    $modal.on('hidden.bs.modal', function() {
        // Only unblock if password was successfully reset
        if (!forcePasswordReset) {
            unblockDashboard();
        }
    });
    
    // Initialize
    checkPasswordReset();
});