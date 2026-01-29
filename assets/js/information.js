// assets/js/information.js - VERSION LENGKAP SATU FILE
(function() {
    'use strict';
    
    // Check if already initialized
    if (window.InformationSystem) {
        console.log('⚠️ InformationSystem class already defined');
        return;
    }
    
    class InformationSystem {
        constructor() {
            console.log('📋 InformationSystem initialized');
            this.currentUser = $('.nav-profile span').text() || 
                              $('#user-name').val() || 
                              'Unknown';
            this.selectedRecipients = [];
            this.setupEventListeners();
            this.bindTableEvents();
            this.initRealTimeNotifications();
            this.initDataTable();
        }
        
        initRealTimeNotifications() {
            console.log('🔔 Initializing real-time notifications...');
            
            // Check for new notifications every 5 seconds
            setInterval(() => {
                this.checkNewNotifications();
            }, 5000);
            
            // Initial check
            setTimeout(() => {
                this.checkNewNotifications();
            }, 2000);
        }
        
        checkNewNotifications() {
            $.ajax({
                url: 'api/check_new_info.php',
                type: 'GET',
                dataType: 'json',
                success: (response) => {
                    if (response.success && response.count > 0) {
                        console.log('🔔 New notifications found:', response.count);
                        this.updateNotificationBadge(response.count);
                        
                        // Auto-refresh information table if needed
                        if (response.assigned_to_me > 0) {
                            setTimeout(() => {
                                this.refreshInformationTable();
                            }, 3000);
                        }
                    }
                },
                error: (xhr) => {
                    console.error('❌ Error checking notifications:', xhr.responseText);
                }
            });
        }
        
        updateNotificationBadge(count) {
            const $badge = $('#notificationBadge');
            const $infoBadge = $('#info-badge');
            
            if (count > 0) {
                $badge.text(count).show().addClass('bg-danger animate__animated animate__pulse');
                $infoBadge.text(count).show().addClass('bg-danger');
                
                // Update document title
                document.title = `(${count}) Progress BO Control`;
                
                // Auto-hide animation after 2 seconds
                setTimeout(() => {
                    $badge.removeClass('animate__pulse');
                }, 2000);
            } else {
                $badge.hide();
                $infoBadge.hide();
                document.title = "Progress BO Control";
            }
        }
        
        setupEventListeners() {
            // Modal Add Information
            $('#modal-add-information').on('show.bs.modal', (e) => {
                this.loadRecipients();
                $('#txt-time1').val(new Date().toTimeString().substring(0, 5));
            });
            
            // Checkbox events
            $(document).on('change', '#select-all-recipients', (e) => {
                const isChecked = $(e.target).is(':checked');
                $('.recipient-checkbox').prop('checked', isChecked).trigger('change');
            });
            
            $(document).on('change', '#recipient-all', (e) => {
                const isChecked = $(e.target).is(':checked');
                $('.recipient-checkbox').prop('checked', isChecked).trigger('change');
            });
            
            $(document).on('change', '.recipient-checkbox', (e) => {
                this.updateSelectedRecipients();
            });
            
            // Form submissions
            $('#addInformationForm').submit((e) => {
                e.preventDefault();
                e.stopPropagation();
                this.submitInformation();
                return false;
            });
            
            $('#updateFromInformationForm').submit((e) => {
                e.preventDefault();
                e.stopPropagation();
                this.updateInformation();
                return false;
            });
            
            // Modal hide events
            $('#modal-add-information').on('hidden.bs.modal', () => {
                this.selectedRecipients = [];
                $('#selected-users-badge').empty();
                $('#selected-count').text('0');
                $('#hidden-recipients').val('[]');
                $('#select-all-recipients').prop('checked', false);
                $('#recipient-all').prop('checked', false);
            });
        }
        
        bindTableEvents() {
            console.log('🔗 Binding table events...');
            
            // Hapus event listeners lama
            $(document).off('click', '.btn-edit-info');
            $(document).off('click', '.btn-delete-info');
            $(document).off('click', '.btn-reply-info');
            
            // Bind event untuk Edit button
            $(document).on('click', '.btn-edit-info', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const id = $(e.currentTarget).data('id');
                console.log('✏️ Edit info clicked:', id);
                this.editInformation(id);
            });
            
            // Bind event untuk Delete button
            $(document).on('click', '.btn-delete-info', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const id = $(e.currentTarget).data('id');
                console.log('🗑️ Delete info clicked:', id);
                this.deleteInformation(id);
            });
            
            // Bind event untuk Reply button
            $(document).on('click', '.btn-reply-info', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const id = $(e.currentTarget).data('id');
                console.log('↩️ Reply info clicked:', id);
                this.replyInformationModal(id);
            });
            
            console.log('✅ Table events bound successfully');
        }
        
        initDataTable() {
            // Inisialisasi DataTable dengan konfigurasi lengkap
            if ($.fn.DataTable.isDataTable('#table-information')) {
                window.tableInformation = $('#table-information').DataTable();
            } else {
                window.tableInformation = $('#table-information').DataTable({
                    pageLength: 10,
                    autoWidth: true,
                    aaSorting: [[0, "asc"]],
                    bDestroy: true,
                    scrollX: true,
                    scrollCollapse: true,
                    paging: true,
                    searching: true,
                    language: {
                        processing: '<div class="spinner-border spinner-border-sm" role="status"></div> Loading...',
                        emptyTable: 'No information available',
                        zeroRecords: 'No matching records found',
                        search: 'Search:',
                        paginate: {
                            first: 'First',
                            last: 'Last',
                            next: 'Next',
                            previous: 'Previous'
                        }
                    },
                    columns: [
                        { 
                            title: "No", 
                            data: null, 
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            },
                            className: "text-center"
                        },
                        { 
                            title: "Date", 
                            data: "DATE", 
                            className: "text-center"
                        },
                        { 
                            title: "Time", 
                            data: "TIME_FROM",
                            className: "text-center" 
                        },
                        { 
                            title: "PIC", 
                            data: "PIC_FROM",
                            className: "text-center"
                        },
                        { 
                            title: "Item", 
                            data: "ITEM", 
                            className: "text-center",
                            render: function(data) {
                                return '<div class="table-text-center text-truncate" style="max-width: 200px;">' + (data || '-') + '</div>';
                            }
                        },
                        { 
                            title: "Request", 
                            data: "REQUEST", 
                            className: "text-center",
                            render: function(data) {
                                return '<div class="table-text-center text-truncate" style="max-width: 200px;">' + (data || '-') + '</div>';
                            }
                        },
                        { 
                            title: "Action", 
                            data: null, 
                            orderable: false, 
                            searchable: false,
                            className: "text-center",
                            render: function(data, type, row) {
                                const role = row.user_role || '';
                                const status = row.STATUS || '';
                                
                                // Hanya sender yang bisa edit/delete
                                if (role === 'sender') {
                                    let buttons = '';
                                    // Edit hanya jika status Open
                                    if (status === 'Open') {
                                        buttons += `<button class="btn btn-sm btn-warning btn-edit-info me-1 btn-action-table" 
                                                    data-id="${row.ID_INFORMATION}" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                  </button>`;
                                    }
                                    // Delete button
                                    buttons += `<button class="btn btn-sm btn-danger btn-delete-info btn-action-table" 
                                                    data-id="${row.ID_INFORMATION}" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                  </button>`;
                                    return buttons;
                                }
                                return '-';
                            }
                        },
                        { 
                            title: "Time", 
                            data: "TIME_TO",
                            className: "text-center",
                            render: function(data) {
                                return data || '-';
                            }
                        },
                        { 
                            title: "PIC", 
                            data: "PIC_TO",
                            className: "text-center",
                            render: function(data) {
                                return data || '-';
                            }
                        },
                        { 
                            title: "Status", 
                            data: "STATUS", 
                            className: "text-center",
                            render: function(data) {
                                let badgeClass = 'bg-secondary';
                                let displayText = data || '-';
                                
                                if (data === 'Open') {
                                    badgeClass = 'bg-danger';
                                    displayText = 'OPEN';
                                } else if (data === 'On Progress') {
                                    badgeClass = 'bg-warning';
                                    displayText = 'ON PROGRESS';
                                } else if (data === 'Closed') {
                                    badgeClass = 'bg-success';
                                    displayText = 'CLOSED';
                                }
                                
                                return `<div class="status-container">
                                    <span class="badge ${badgeClass} w-100 py-2">${displayText}</span>
                                </div>`;
                            }
                        },
                        { 
                            title: "Remark", 
                            data: "REMARK", 
                            className: "text-center",
                            render: function(data) {
                                return '<div class="table-text-center">' + (data || '-') + '</div>';
                            }
                        },
                        { 
                            title: "Action", 
                            data: null, 
                            orderable: false, 
                            searchable: false,
                            className: "text-center",
                            render: function(data, type, row) {
                                const role = row.user_role || '';
                                const status = row.STATUS || '';
                                
                                // Hanya recipient yang bisa reply
                                if (role === 'recipient' && status !== 'Closed') {
                                    let buttonText = '';
                                    let buttonClass = '';
                                    
                                    if (status === 'Open') {
                                        buttonText = '<i class="bi bi-reply"></i> Reply';
                                        buttonClass = 'btn-success';
                                    } else if (status === 'On Progress') {
                                        buttonText = '<i class="bi bi-arrow-clockwise"></i> Update';
                                        buttonClass = 'btn-info';
                                    }
                                    
                                    return `<button class="btn btn-sm ${buttonClass} btn-reply-info" 
                                              data-id="${row.ID_INFORMATION}" title="Update Status">
                                              ${buttonText}
                                            </button>`;
                                }
                                return '-';
                            }
                        }
                    ],
                    createdRow: function(row, data, dataIndex) {
                        // Tambah data attributes untuk highlight
                        $(row).attr({
                            'data-id': data.ID_INFORMATION,
                            'data-pic-from': data.PIC_FROM,
                            'data-item': data.ITEM,
                            'data-date': data.DATE
                        });
                        
                        // Tambah class untuk unread
                        if (data.IS_UNREAD == 1) {
                            $(row).addClass('unread-row');
                        }
                    },
                    drawCallback: function(settings) {
                        console.log('🔄 DataTable draw callback');
                        setTimeout(() => {
                            if (window.informationSystem && window.informationSystem.bindTableEvents) {
                                window.informationSystem.bindTableEvents();
                                console.log('✅ Table events re-bound');
                            }
                            
                            // Trigger event bahwa tabel sudah di-load
                            $(document).trigger('informationTableLoaded', [settings.aoData]);
                        }, 300);
                    },
                    initComplete: function() {
                        console.log('✅ Information DataTable initialized with proper columns');
                        if (window.informationSystem) {
                            setTimeout(() => {
                                window.informationSystem.bindTableEvents();
                            }, 500);
                        }
                    }
                });
            }
        }
        
        loadRecipients() {
            $.ajax({
                url: 'modules/data_information.php?type=get-recipients',
                type: 'GET',
                dataType: 'json',
                beforeSend: () => {
                    $('.recipients-container').html(`
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <span class="ms-2">Loading users...</span>
                        </div>
                    `);
                },
                success: (response) => {
                    console.log('👥 Loaded recipients:', response);
                    if (response.success) {
                        this.renderRecipients(response.users);
                    } else {
                        this.showToast('error', 'Failed to load recipients', response.message);
                        this.renderRecipients([]);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('❌ Failed to load users:', xhr.responseText);
                    this.showToast('error', 'Error', 'Failed to load users');
                    this.renderRecipients([]);
                }
            });
        }
        
        renderRecipients(users) {
            const container = $('.recipients-container');
            
            if (!users || users.length === 0) {
                container.html(`
                    <div class="alert alert-warning py-2 mb-0">
                        <i class="bi bi-exclamation-triangle"></i> No users found
                    </div>
                `);
                return;
            }
            
            let html = '';
            
            // Add "ALL" option at the top
            html += `
                <div class="mb-2 border-bottom pb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="recipient-all" value="ALL">
                        <label class="form-check-label fw-bold" for="recipient-all">
                            <i class="bi bi-people-fill me-1"></i>SEMUA USER (Semua Orang)
                        </label>
                    </div>
                </div>
                <div class="recipient-checkbox-group">
            `;
            
            users.forEach(user => {
                if (user.name && user.name !== this.currentUser) {
                    const safeId = user.name.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase();
                    html += `
                    <div class="recipient-item">
                        <div class="form-check">
                            <input class="form-check-input recipient-checkbox" 
                                   type="checkbox" 
                                   value="${user.name.replace(/"/g, '&quot;')}" 
                                   id="recipient-${safeId}">
                            <label class="form-check-label" for="recipient-${safeId}">
                                <i class="bi bi-person-circle me-1"></i>${user.name}
                            </label>
                        </div>
                    </div>
                    `;
                }
            });
            
            html += `</div>`;
            container.html(html);
            
            // Trigger initial update
            this.updateSelectedRecipients();
        }
        
        updateSelectedRecipients() {
            this.selectedRecipients = [];
            
            // Get all checked checkboxes (excluding ALL)
            $('.recipient-checkbox:checked').each((index, element) => {
                const value = $(element).val();
                if (value && value !== 'ALL' && !this.selectedRecipients.includes(value)) {
                    this.selectedRecipients.push(value);
                }
            });
            
            // Jika ALL dicentang, dapatkan semua user
            if ($('#recipient-all').is(':checked')) {
                this.selectedRecipients = [];
                $('.recipient-checkbox').each((i, el) => {
                    const value = $(el).val();
                    if (value && value !== 'ALL') {
                        this.selectedRecipients.push(value);
                    }
                });
            }
            
            // Update UI
            $('#selected-count').text(this.selectedRecipients.length);
            
            // Update badge display
            const badgeContainer = $('#selected-users-badge');
            badgeContainer.empty();
            
            if (this.selectedRecipients.length > 0) {
                let badgeHtml = '';
                const displayCount = Math.min(this.selectedRecipients.length, 5);
                
                for (let i = 0; i < displayCount; i++) {
                    badgeHtml += `<span class="badge bg-primary me-1 mb-1">${this.selectedRecipients[i]}</span>`;
                }
                
                if (this.selectedRecipients.length > 5) {
                    badgeHtml += `<span class="badge bg-secondary">+${this.selectedRecipients.length - 5} more</span>`;
                }
                
                badgeContainer.html(badgeHtml);
            } else {
                badgeContainer.html('<span class="text-muted">No users selected</span>');
            }
            
            // Update hidden field
            $('#hidden-recipients').val(JSON.stringify(this.selectedRecipients));
            
            console.log('📋 Selected recipients:', this.selectedRecipients);
        }
        
        submitInformation() {
            const form = $('#addInformationForm')[0];
            const formData = new FormData(form);
            
            console.log('📤 Submitting form...');
            console.log('Selected recipients:', this.selectedRecipients);
            
            // Validasi
            if (this.selectedRecipients.length === 0) {
                this.showToast('warning', 'Select Recipients', 'Please select at least one recipient');
                return false;
            }
            
            const item = $('#txtItem').val().trim();
            const request = $('#txtRequest').val().trim();
            
            if (!item) {
                this.showToast('warning', 'Item Required', 'Please fill Item field');
                $('#txtItem').focus();
                return false;
            }
            
            if (!request) {
                this.showToast('warning', 'Request Required', 'Please fill Request field');
                $('#txtRequest').focus();
                return false;
            }
            
            // Show loading
            const submitBtn = $(form).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span> Sending...');
            
            $.ajax({
                url: 'modules/data_information.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                timeout: 10000,
                beforeSend: () => {
                    console.log('🚀 Sending request to server...');
                },
                success: (response) => {
                    console.log('✅ Server response:', response);
                    
                    submitBtn.prop('disabled', false).html(originalText);
                    
                    if (response.success) {
                        this.showToast('success', 'Success!', 
                            `Information sent to ${response.recipient_count || 0} recipient(s)`);
                        
                        // Close modal
                        $('#modal-add-information').modal('hide');
                        
                        // Reset form
                        form.reset();
                        this.selectedRecipients = [];
                        $('#selected-users-badge').empty();
                        $('#selected-count').text('0');
                        $('#hidden-recipients').val('[]');
                        $('#select-all-recipients').prop('checked', false);
                        $('#recipient-all').prop('checked', false);
                        
                        // Refresh table and notifications after 2 seconds
                        setTimeout(() => {
                            this.refreshInformationTable();
                            this.checkNewNotifications();
                        }, 2000);
                        
                    } else {
                        if (response.duplicate) {
                            this.showToast('warning', 'Duplicate Information', 
                                'You already sent this information today. Please wait until tomorrow or edit the existing one.');
                        } else {
                            this.showToast('error', 'Failed!', response.message || 'Failed to send information');
                        }
                        console.error('❌ Server error:', response);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('❌ Submit error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                    
                    submitBtn.prop('disabled', false).html(originalText);
                    
                    let errorMsg = 'Server error. Please try again.';
                    try {
                        const err = JSON.parse(xhr.responseText);
                        errorMsg = err.message || errorMsg;
                    } catch (e) {}
                    
                    this.showToast('error', 'Error!', errorMsg);
                }
            });
            
            return false;
        }
        
        editInformation(id) {
            console.log('🔄 Opening edit modal for ID:', id);
            
            $.ajax({
                url: 'modules/data_information.php?type=get-single&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: (response) => {
                    console.log('✅ Edit info data loaded:', response);
                    if (response.success && response.data) {
                        const info = response.data;
                        
                        // Validasi: hanya pengirim yang bisa edit
                        if (info.PIC_FROM !== this.currentUser) {
                            this.showToast('error', 'Access Denied', 'Only the sender can edit this information');
                            console.log('❌ Not sender:', info.PIC_FROM, '!=', this.currentUser);
                            return;
                        }
                        
                        // Validasi: tidak bisa edit jika sudah On Progress atau Closed
                        if (info.STATUS === 'On Progress' || info.STATUS === 'Closed') {
                            this.showToast('warning', 'Cannot Edit', 'Cannot edit information that is already in progress or closed');
                            return;
                        }
                        
                        // Fill modal
                        $('#txt-id-information').val(info.ID_INFORMATION);
                        $('#txt-timefrom-update').val(info.TIME_FROM || '');
                        $('#txt-picfrom-update').val(info.PIC_FROM || '');
                        $('#txt-item-update').val(info.ITEM || '');
                        $('#txt-request-update').val(info.REQUEST || '');
                        
                        const today = new Date().toISOString().split('T')[0];
                        $('#txt-date-information-from').html("(" + today + ")");
                        
                        console.log('🎯 Showing edit modal');
                        $('#modal-update-information-from').modal('show');
                        
                    } else {
                        this.showToast('error', 'Error', 'Failed to load information');
                    }
                },
                error: (xhr) => {
                    console.error('❌ Error loading info:', xhr.responseText);
                    this.showToast('error', 'Error', 'Failed to load information');
                }
            });
        }
        
        updateInformation() {
            const form = $('#updateFromInformationForm')[0];
            const formData = new FormData(form);
            
            console.log('📤 Updating information...');
            
            // Validasi
            const item = $('#txt-item-update').val().trim();
            const request = $('#txt-request-update').val().trim();
            
            if (!item) {
                this.showToast('warning', 'Item Required', 'Please fill Item field');
                $('#txt-item-update').focus();
                return false;
            }
            
            if (!request) {
                this.showToast('warning', 'Request Required', 'Please fill Request field');
                $('#txt-request-update').focus();
                return false;
            }
            
            const submitBtn = $(form).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span> Updating...');
            
            $.ajax({
                url: 'modules/data_information.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: (response) => {
                    console.log('✅ Update response:', response);
                    
                    submitBtn.prop('disabled', false).html(originalText);
                    
                    if (response.success) {
                        this.showToast('success', 'Success!', 'Information updated successfully');
                        
                        $('#modal-update-information-from').modal('hide');
                        
                        setTimeout(() => {
                            this.refreshInformationTable();
                            this.checkNewNotifications();
                        }, 2000);
                        
                    } else {
                        this.showToast('error', 'Failed!', response.message || 'Failed to update information');
                    }
                },
                error: (xhr) => {
                    console.error('❌ Update error:', xhr.responseText);
                    submitBtn.prop('disabled', false).html(originalText);
                    this.showToast('error', 'Error!', 'Server error. Please try again.');
                }
            });
            
            return false;
        }
        
        replyInformationModal(id) {
            console.log('🔄 Opening reply modal for ID:', id);
            
            $.ajax({
                url: 'modules/data_information.php?type=get-single&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: (response) => {
                    console.log('✅ Reply info data loaded:', response);
                    if (response.success && response.data) {
                        const info = response.data;
                        
                        // Cek apakah user adalah salah satu penerima
                        const recipients = info.PIC_TO ? info.PIC_TO.split(', ') : [];
                        const isRecipient = recipients.includes(this.currentUser);
                        
                        if (!isRecipient) {
                            this.showToast('error', 'Access Denied', 'Only recipients can reply to this information');
                            return;
                        }
                        
                        // Validasi: tidak bisa reply jika sudah closed
                        if (info.STATUS === 'Closed') {
                            this.showToast('warning', 'Cannot Reply', 'This information is already closed');
                            return;
                        }
                        
                        // Fill modal data
                        $('#txt-id-information2').val(info.ID_INFORMATION);
                        $('#txt-timefrom-to-update').val(info.TIME_FROM || '');
                        $('#txt-picfrom-to-update').val(info.PIC_FROM || '');
                        $('#txt-itemto-update').val(info.ITEM || '');
                        $('#txt-requestto-update').val(info.REQUEST || '');
                        $('#txt-picto-update').val(this.currentUser);
                        $('#txt-timeto-update').val(new Date().toTimeString().substring(0, 5));
                        
                        // Update tampilan
                        this.updateReplyModalDisplay(info);
                        
                        // Show modal
                        $('#modal-update-information-to').modal('show');
                        
                    } else {
                        this.showToast('error', 'Error', 'Failed to load information');
                    }
                },
                error: (xhr) => {
                    console.error('❌ Error loading info:', xhr.responseText);
                    this.showToast('error', 'Error', 'Failed to load information');
                }
            });
        }
        
        updateReplyModalDisplay(info) {
            // Display basic info
            $('#display-picfrom').html(`
                <span class="fw-bold text-primary">${info.PIC_FROM || '-'}</span>
                <br>
                <small class="text-muted">Pengirim</small>
            `);
            
            $('#display-picto').html(`
                <span class="fw-bold text-success">${info.PIC_TO || '-'}</span>
                <br>
                <small class="text-muted">Penerima</small>
            `);
            
            $('#display-timefrom').html(`
                <i class="bi bi-clock me-1"></i>${info.TIME_FROM || '-'}
            `);
            
            $('#display-date').html(`
                <i class="bi bi-calendar me-1"></i>${info.DATE || '-'}
            `);
            
            // Display item
            $('#display-item').html(info.ITEM || '-');
            
            // Display request
            $('#display-request').html(info.REQUEST || '-');
            
            // Update status display
            let statusBadge = '';
            let statusColor = '';
            let statusText = '';
            
            switch(info.STATUS) {
                case 'Open':
                    statusBadge = 'bg-danger';
                    statusText = 'OPEN';
                    break;
                case 'On Progress':
                    statusBadge = 'bg-warning';
                    statusText = 'ON PROGRESS';
                    break;
                case 'Closed':
                    statusBadge = 'bg-success';
                    statusText = 'CLOSED';
                    break;
                default:
                    statusBadge = 'bg-secondary';
                    statusText = info.STATUS || 'UNKNOWN';
            }
            
            $('#display-status-badge').removeClass().addClass(`badge ${statusBadge} fw-medium px-3 py-1 me-3`);
            $('#display-status-text').text(statusText);
            $('#reply-time-display').text(new Date().toTimeString().substring(0, 5));
            
            // Reset remark field
            $('#txt-remark-update').val('');
            
            // Default select ON PROGRESS
            setTimeout(() => {
                $('.action-card[data-action="on_progress"]').click();
            }, 100);
        }
        
        deleteInformation(id) {
            Swal.fire({
                title: 'Delete Information?',
                text: 'Are you sure you want to delete this information? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: 'modules/data_information.php',
                            type: 'POST',
                            data: {
                                type: 'delete',
                                id_information: id
                            },
                            dataType: 'json',
                            success: (response) => {
                                resolve(response);
                            },
                            error: () => {
                                reject('Network error');
                            }
                        });
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    const response = result.value;
                    
                    if (response && response.success) {
                        this.showToast('success', 'Deleted!', 'Information deleted successfully');
                        
                        setTimeout(() => {
                            this.refreshInformationTable();
                            this.checkNewNotifications();
                        }, 500);
                        
                    } else {
                        this.showToast('error', 'Failed!', response?.message || 'Failed to delete information');
                    }
                }
            });
        }
        
        refreshInformationTable() {
            // Panggil fungsi global refresh
            if (typeof fetchDataInformation === 'function') {
                fetchDataInformation();
            }
        }
        
        showToast(type, title, message, duration = 3000) {
            // Remove existing toasts
            $('.custom-toast').remove();
            
            const iconMap = {
                'success': 'bi-check-circle-fill',
                'error': 'bi-x-circle-fill',
                'warning': 'bi-exclamation-triangle-fill',
                'info': 'bi-info-circle-fill'
            };
            
            const colorMap = {
                'success': '#28a745',
                'error': '#dc3545',
                'warning': '#ffc107',
                'info': '#17a2b8'
            };
            
            const toast = $(`
                <div class="custom-toast" style="border-left-color: ${colorMap[type] || '#6c757d'}">
                    <div class="toast-icon" style="color: ${colorMap[type] || '#6c757d'}">
                        <i class="bi ${iconMap[type] || 'bi-info-circle-fill'}"></i>
                    </div>
                    <div class="toast-content">
                        <div class="toast-title">${title}</div>
                        <div class="toast-message">${message}</div>
                    </div>
                </div>
            `);
            
            $('body').append(toast);
            
            setTimeout(() => toast.addClass('show'), 10);
            setTimeout(() => {
                toast.removeClass('show');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
    }
    
    // Expose to global scope
    window.InformationSystem = InformationSystem;
    
    // Initialize on document ready
    $(document).ready(function() {
        console.log('📋 Initializing InformationSystem instance...');
        
        if (!window.informationSystem) {
            console.log('🚀 Creating new InformationSystem instance...');
            window.informationSystem = new InformationSystem();
            
            // Re-bind events setelah DataTable initialized
            $(document).on('draw.dt', function(e, settings) {
                if (settings.nTable.id === 'table-information') {
                    console.log('🔄 DataTable redrawn, binding events...');
                    setTimeout(() => {
                        if (window.informationSystem && window.informationSystem.bindTableEvents) {
                            window.informationSystem.bindTableEvents();
                        }
                    }, 100);
                }
            });
        } else {
            console.log('✅ InformationSystem instance already exists');
        }
    });
    
})();