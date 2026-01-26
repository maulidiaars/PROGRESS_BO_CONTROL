// assets/js/information.js - VERSI DIPERBAIKI
(function() {
    'use strict';
    
    // Cleanup existing instance
    if (window.informationSystem) {
        console.log('⚠️ Cleaning up existing InformationSystem');
        window.informationSystem.destroy();
    }
    
    class InformationSystem {
        constructor() {
            console.log('📋 InformationSystem initialized');
            
            this.currentUser = $('.nav-profile span').text() || 'Unknown';
            this.selectedRecipients = [];
            this.isProcessing = false;
            this.debounceTimer = null;
            this.initialized = false;
            
            // Initialize dengan delay
            setTimeout(() => {
                if (!this.initialized) {
                    this.setupEventListeners();
                    this.bindTableEvents();
                    this.setupToastSystem();
                    this.initialized = true;
                    console.log('✅ InformationSystem fully initialized');
                }
            }, 500);
        }
        
        setupEventListeners() {
            console.log('📋 Setting up event listeners');
            
            // Modal Add Information - Show event
            $('#modal-add-information').on('show.bs.modal', (e) => {
                console.log('📋 Modal add information opening');
                this.loadRecipients();
                $('#txt-time1').val(new Date().toTimeString().substring(0, 5));
            });
            
            // Checkbox events
            $(document).on('change', '#select-all-recipients', (e) => {
                const isChecked = $(e.target).is(':checked');
                console.log('🔘 Select all:', isChecked);
                $('.recipient-checkbox').prop('checked', isChecked);
                this.updateSelectedRecipients();
            });
            
            $(document).on('change', '#recipient-all', (e) => {
                const isChecked = $(e.target).is(':checked');
                console.log('🔘 Recipient ALL:', isChecked);
                $('.recipient-checkbox').prop('checked', isChecked);
                this.updateSelectedRecipients();
            });
            
            $(document).on('change', '.recipient-checkbox', (e) => {
                console.log('🔘 Individual checkbox changed');
                this.updateSelectedRecipients();
            });
            
            // Clear selection
            $(document).on('click', '#clear-selection', (e) => {
                e.preventDefault();
                console.log('🗑️ Clearing all selections');
                $('.recipient-checkbox').prop('checked', false);
                $('#recipient-all').prop('checked', false);
                $('#select-all-recipients').prop('checked', false);
                this.updateSelectedRecipients();
                $(e.target).hide();
            });
            
            // Form submissions - HANYA BIND SEKALI!
            $(document).off('submit', '#addInformationForm');
            $(document).on('submit', '#addInformationForm', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('📝 Submitting information form');
                this.submitInformation();
                return false;
            });
            
            $(document).off('submit', '#updateFromInformationForm');
            $(document).on('submit', '#updateFromInformationForm', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('📝 Submitting update from form');
                this.updateInformation();
                return false;
            });
            
            // Modal hide events
            $('#modal-add-information').on('hidden.bs.modal', () => {
                console.log('📋 Modal closed, resetting selections');
                this.selectedRecipients = [];
                $('#selected-users-badge').empty();
                $('#selected-count').text('0');
                $('#hidden-recipients').val('[]');
                $('#select-all-recipients').prop('checked', false);
                $('#recipient-all').prop('checked', false);
                $('#clear-selection').hide();
                $('#txtItem').val('');
                $('#txtRequest').val('');
            });
            
            console.log('✅ Event listeners setup complete');
        }
        
        loadRecipients() {
            console.log('📋 Loading recipients list');
            
            $.ajax({
                url: 'modules/data_information.php?type=get-recipients&_t=' + new Date().getTime(),
                type: 'GET',
                dataType: 'json',
                timeout: 8000,
                beforeSend: () => {
                    $('.recipients-container').html(`
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <span class="ms-2" style="color: #64748b;">Memuat daftar penerima...</span>
                        </div>
                    `);
                },
                success: (response) => {
                    console.log('📋 Recipients API response:', response);
                    
                    if (response.success) {
                        this.renderRecipients(response.users);
                        console.log('✅ Loaded', response.users.length, 'recipients');
                    } else {
                        console.error('❌ Failed to load recipients:', response.message);
                        $('.recipients-container').html(`
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Gagal memuat daftar penerima: ${response.message || 'Unknown error'}
                            </div>
                        `);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('❌ Error loading recipients:', error);
                    $('.recipients-container').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-wifi-off"></i> 
                            Gagal memuat daftar penerima. Periksa koneksi internet.
                            <br><small>Status: ${xhr.status} - ${error}</small>
                        </div>
                    `);
                }
            });
        }
        
        renderRecipients(users) {
            const container = $('.recipients-container');
            
            if (!users || users.length === 0) {
                container.html(`
                    <div class="alert alert-warning">
                        <i class="bi bi-people"></i> Tidak ada user ditemukan
                    </div>
                `);
                return;
            }
            
            console.log('📋 Rendering', users.length, 'recipients');
            
            let html = `
                <div class="mb-3 border-bottom pb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="recipient-all" value="ALL">
                        <label class="form-check-label fw-bold text-primary" for="recipient-all">
                            <i class="bi bi-people-fill me-2"></i>SEMUA USER
                        </label>
                        <div class="small text-muted mt-1">
                            Pilih semua user sekaligus
                        </div>
                    </div>
                </div>
                <div class="recipient-checkbox-group" style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
            `;
            
            // Group by department if available
            const groupedByDept = {};
            users.forEach(user => {
                if (user.value === 'ALL') return; // Skip ALL option
                
                if (user.name && user.name !== this.currentUser) {
                    const dept = user.department || 'UNKNOWN';
                    if (!groupedByDept[dept]) {
                        groupedByDept[dept] = [];
                    }
                    groupedByDept[dept].push(user);
                }
            });
            
            // Render by department
            Object.keys(groupedByDept).sort().forEach(dept => {
                const deptUsers = groupedByDept[dept];
                
                html += `
                    <div class="department-group mb-3">
                        <div class="small fw-semibold text-uppercase text-muted mb-2 d-flex align-items-center">
                            <i class="bi bi-building me-1"></i>
                            ${dept} (${deptUsers.length} user)
                        </div>
                        <div class="ps-3">
                `;
                
                deptUsers.forEach((user, index) => {
                    const safeId = 'recipient-' + user.name.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase();
                    
                    html += `
                        <div class="recipient-item mb-2">
                            <div class="form-check">
                                <input class="form-check-input recipient-checkbox" 
                                       type="checkbox" 
                                       value="${user.name.replace(/"/g, '&quot;')}" 
                                       id="${safeId}">
                                <label class="form-check-label d-flex align-items-center" for="${safeId}">
                                    <div class="user-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                         style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        ${user.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <div class="fw-medium">${user.name}</div>
                                        <div class="small text-muted">${dept}</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            });
            
            html += `</div>`;
            
            container.html(html);
            this.updateSelectedRecipients();
            
            // Initialize scrollbar styling
            setTimeout(() => {
                container.find('.recipient-checkbox-group').scrollTop(0);
            }, 100);
        }
        
        updateSelectedRecipients() {
            this.selectedRecipients = [];
            
            // Check if "ALL" is selected
            const allSelected = $('#recipient-all').is(':checked');
            
            if (allSelected) {
                // Get all user names from checkboxes (excluding ALL checkbox)
                $('.recipient-checkbox').each((index, element) => {
                    const value = $(element).val();
                    if (value && value !== 'ALL' && !this.selectedRecipients.includes(value)) {
                        this.selectedRecipients.push(value);
                    }
                });
                
                // Also check the "select all" checkbox
                $('#select-all-recipients').prop('checked', true);
            } else {
                // Get only checked individual checkboxes
                $('.recipient-checkbox:checked').each((index, element) => {
                    const value = $(element).val();
                    if (value && value !== 'ALL' && !this.selectedRecipients.includes(value)) {
                        this.selectedRecipients.push(value);
                    }
                });
                
                // Update "select all" checkbox state
                const totalCheckboxes = $('.recipient-checkbox').length - 1; // Exclude ALL
                const checkedCount = $('.recipient-checkbox:checked').length;
                $('#select-all-recipients').prop('checked', checkedCount === totalCheckboxes);
            }
            
            // Update UI
            $('#selected-count').text(this.selectedRecipients.length);
            
            const badgeContainer = $('#selected-users-badge');
            badgeContainer.empty();
            
            if (this.selectedRecipients.length > 0) {
                let badgeHtml = '';
                const displayCount = Math.min(this.selectedRecipients.length, 8);
                
                for (let i = 0; i < displayCount; i++) {
                    badgeHtml += `
                        <span class="badge bg-primary me-1 mb-1 d-inline-flex align-items-center">
                            <i class="bi bi-person-circle me-1" style="font-size: 0.8rem;"></i>
                            ${this.selectedRecipients[i]}
                        </span>
                    `;
                }
                
                if (this.selectedRecipients.length > 8) {
                    badgeHtml += `
                        <span class="badge bg-secondary d-inline-flex align-items-center">
                            <i class="bi bi-plus-circle me-1" style="font-size: 0.8rem;"></i>
                            +${this.selectedRecipients.length - 8} more
                        </span>
                    `;
                }
                
                badgeContainer.html(badgeHtml);
                $('#clear-selection').show();
            } else {
                badgeContainer.html(`
                    <div class="empty-state text-muted small d-flex align-items-center">
                        <i class="bi bi-info-circle me-2"></i>
                        Belum ada penerima terpilih
                    </div>
                `);
                $('#clear-selection').hide();
            }
            
            // Update hidden field
            $('#hidden-recipients').val(JSON.stringify(this.selectedRecipients));
            
            console.log('📋 Selected recipients:', this.selectedRecipients.length, 'users');
        }
        
        submitInformation() {
            console.log('📝 Starting information submission');
            
            // Validation
            if (this.selectedRecipients.length === 0) {
                this.showToast('error', 'Pilih minimal satu penerima');
                $('#recipient-all').focus();
                return false;
            }
            
            const item = $('#txtItem').val().trim();
            const request = $('#txtRequest').val().trim();
            
            if (!item) {
                this.showToast('error', 'Judul Item tidak boleh kosong');
                $('#txtItem').focus();
                return false;
            }
            
            if (!request) {
                this.showToast('error', 'Detail Permintaan tidak boleh kosong');
                $('#txtRequest').focus();
                return false;
            }
            
            console.log('📝 Form data:', {
                item: item,
                request: request,
                recipients: this.selectedRecipients,
                count: this.selectedRecipients.length
            });
            
            const form = $('#addInformationForm')[0];
            const formData = new FormData(form);
            
            // Tambahkan recipients ke FormData
            formData.set('recipients', JSON.stringify(this.selectedRecipients));
            
            const submitBtn = $(form).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            // Disable button dan show loading
            submitBtn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...');
            
            // Show processing indicator
            this.showToast('info', 'Mengirim informasi...', 3000);
            
            $.ajax({
                url: 'modules/data_information.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                timeout: 15000,
                beforeSend: () => {
                    console.log('📤 Sending information to server...');
                },
                success: (response) => {
                    console.log('✅ Server response:', response);
                    submitBtn.prop('disabled', false).html(originalText);
                    
                    if (response.success) {
                        this.showToast('success', response.message || 'Informasi berhasil dikirim!', 5000);
                        
                        // Reset form
                        form.reset();
                        this.selectedRecipients = [];
                        $('#selected-users-badge').empty();
                        $('#selected-count').text('0');
                        $('#hidden-recipients').val('[]');
                        $('#select-all-recipients').prop('checked', false);
                        $('#recipient-all').prop('checked', false);
                        $('#clear-selection').hide();
                        
                        // Close modal after delay
                        setTimeout(() => {
                            $('#modal-add-information').modal('hide');
                        }, 2000);
                        
                        // Refresh information table
                        setTimeout(() => {
                            if (typeof fetchDataInformation === 'function') {
                                fetchDataInformation();
                            }
                            
                            // Trigger notification check
                            if (window.notificationSystem && typeof window.notificationSystem.forceCheck === 'function') {
                                window.notificationSystem.forceCheck();
                            }
                        }, 2500);
                        
                    } else {
                        this.showToast('error', response.message || 'Gagal mengirim informasi');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('❌ Submission error:', error);
                    submitBtn.prop('disabled', false).html(originalText);
                    
                    let errorMsg = 'Network error: ';
                    try {
                        const err = JSON.parse(xhr.responseText);
                        errorMsg += err.message || err.error || error;
                    } catch (e) {
                        errorMsg += xhr.statusText || error;
                    }
                    
                    this.showToast('error', errorMsg);
                }
            });
            
            return false;
        }
        
        // ... (sisa method lainnya tetap sama seperti sebelumnya)
        
        showToast(type, message, duration = 5000) {
            // Remove existing toasts
            $('.custom-toast').remove();
            
            const toastId = 'toast-' + Date.now();
            const icon = type === 'success' ? 'check-circle' : 
                        type === 'error' ? 'error-circle' : 
                        type === 'warning' ? 'exclamation-triangle' : 'info-circle';
            
            const toast = $(`
                <div id="${toastId}" class="custom-toast ${type} animate__animated animate__fadeInRight">
                    <div class="toast-icon">
                        <i class="bx bx-${icon}"></i>
                    </div>
                    <div class="toast-content">
                        <div class="toast-title">${type === 'success' ? 'Sukses' : type === 'error' ? 'Error' : type === 'warning' ? 'Peringatan' : 'Info'}</div>
                        <div class="toast-message">${message}</div>
                    </div>
                    <button type="button" class="toast-close">
                        <i class="bx bx-x"></i>
                    </button>
                    <div class="toast-progress"></div>
                </div>
            `);
            
            $('#toast-container').append(toast);
            
            // Progress bar animation
            toast.find('.toast-progress').css('animation', `toast-progress ${duration}ms linear forwards`);
            
            // Auto remove after duration
            const autoRemove = setTimeout(() => {
                toast.removeClass('animate__fadeInRight').addClass('animate__fadeOutRight');
                setTimeout(() => toast.remove(), 300);
            }, duration);
            
            // Close button
            toast.find('.toast-close').on('click', function() {
                clearTimeout(autoRemove);
                toast.removeClass('animate__fadeInRight').addClass('animate__fadeOutRight');
                setTimeout(() => toast.remove(), 300);
            });
        }
        
        destroy() {
            $(document).off('change', '#select-all-recipients');
            $(document).off('change', '#recipient-all');
            $(document).off('change', '.recipient-checkbox');
            $(document).off('click', '#clear-selection');
            $(document).off('submit', '#addInformationForm');
            $(document).off('submit', '#updateFromInformationForm');
            clearTimeout(this.debounceTimer);
            console.log('📋 InformationSystem destroyed');
        }
    }
    
    // Initialize
    window.InformationSystem = InformationSystem;
    
    
})();