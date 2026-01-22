<!-- Reset Password Modal -->
<div class="modal fade" id="passwordResetModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header -->
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-shield-lock-fill me-2"></i>
                    RESET PASSWORD REQUIRED
                </h5>
                <button type="button" class="btn-close btn-close-white" id="closeResetModalBtn"></button>
            </div>
            
            <!-- Body -->
            <div class="modal-body p-4">
                <!-- Info Alert -->
                <div class="alert alert-info border-0 bg-info-soft">
                    <i class="bi bi-info-circle me-2"></i>
                    For security reasons, you must change your default password before accessing the dashboard.
                </div>
                
                <!-- Form -->
                <form id="resetPasswordForm" autocomplete="off">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-key me-1"></i> Current Password
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control border-end-0" 
                                   id="currentPassword" 
                                   placeholder="Enter current password"
                                   required>
                            <button class="btn btn-outline-secondary border-start-0" 
                                    type="button" 
                                    id="toggleCurrentPassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-lock-fill me-1"></i> New Password
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control border-end-0" 
                                   id="newPassword" 
                                   placeholder="Enter new password"
                                   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                                   required>
                            <button class="btn btn-outline-secondary border-start-0" 
                                    type="button" 
                                    id="toggleNewPassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        
                        <!-- Password Strength Meter -->
                        <div class="mt-2">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Password strength:</span>
                                <span id="strengthText">Weak</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-danger" id="passwordStrengthBar" 
                                     role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <!-- Password Requirements -->
                        <div class="mt-3">
                            <small class="text-muted d-block">
                                <i class="bi bi-check-circle text-success me-1"></i>
                                Minimum 8 characters
                            </small>
                            <small class="text-muted d-block">
                                <i class="bi bi-check-circle text-success me-1"></i>
                                At least one uppercase letter
                            </small>
                            <small class="text-muted d-block">
                                <i class="bi bi-check-circle text-success me-1"></i>
                                At least one lowercase letter
                            </small>
                            <small class="text-muted d-block">
                                <i class="bi bi-check-circle text-success me-1"></i>
                                At least one number
                            </small>
                            <small class="text-muted d-block">
                                <i class="bi bi-check-circle text-success me-1"></i>
                                At least one special character (@$!%*?&)
                            </small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-lock-fill me-1"></i> Confirm New Password
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control border-end-0" 
                                   id="confirmPassword" 
                                   placeholder="Confirm new password"
                                   required>
                            <button class="btn btn-outline-secondary border-start-0" 
                                    type="button" 
                                    id="toggleConfirmPassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <div class="mt-2" id="passwordMatchIndicator">
                            <!-- Match indicator will appear here -->
                        </div>
                    </div>
                    
                    <!-- Alerts -->
                    <div class="alert alert-danger d-none" id="resetErrorAlert" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="errorMessage"></span>
                    </div>
                    
                    <div class="alert alert-success d-none" id="resetSuccessAlert" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <span id="successMessage"></span>
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="modal-footer bg-light">
                <div class="me-auto">
                    <img src="./assets/img/logo-denso.png" alt="DENSO" width="90" height="40">
                </div>
                <button type="button" class="btn btn-outline-danger" id="closeResetModalBtn2">
                    <i class="bi bi-box-arrow-left me-1"></i> Logout
                </button>
                <button type="button" class="btn btn-primary px-4" id="submitResetPasswordBtn">
                    <span id="resetBtnText">Reset Password</span>
                    <div class="spinner-border spinner-border-sm d-none ms-2" 
                         id="resetBtnSpinner" role="status"></div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CSS Tambahan untuk Modal -->
<style>
#passwordResetModal .modal-content {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

#passwordResetModal .modal-header {
    background: linear-gradient(135deg, #0066cc 0%, #003399 100%);
    padding: 1.2rem 1.5rem;
}

#passwordResetModal .modal-body {
    background: #f8f9fa;
}

#passwordResetModal .bg-info-soft {
    background-color: rgba(13, 110, 253, 0.1);
    border-color: rgba(13, 110, 253, 0.2);
}

/* Password strength indicator */
.strength-weak { background-color: #dc3545 !important; }
.strength-fair { background-color: #fd7e14 !important; }
.strength-good { background-color: #ffc107 !important; }
.strength-strong { background-color: #20c997 !important; }
.strength-very-strong { background-color: #28a745 !important; }

/* Password match indicator */
.match-indicator {
    font-size: 0.875rem;
    font-weight: 500;
}

.match-indicator.match {
    color: #198754;
}

.match-indicator.mismatch {
    color: #dc3545;
}

/* Input focus effects */
#passwordResetModal .form-control:focus {
    border-color: #0066cc;
    box-shadow: 0 0 0 0.25rem rgba(0, 102, 204, 0.25);
}

/* Button hover effects */
#passwordResetModal .btn-primary {
    background: linear-gradient(135deg, #0066cc 0%, #0047ab 100%);
    border: none;
    padding: 0.5rem 1.5rem;
    transition: all 0.3s;
}

#passwordResetModal .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 102, 204, 0.4);
}
</style>