// assets/js/notifications.js - VERSION REAL-TIME & AUTO-SCROLL
class NotificationSystem {
    constructor() {
        this.pollingInterval = null;
        this.deepCheckInterval = null;
        this.notificationCount = 0;
        this.lastCheckTime = null;
        this.isInitialized = false;
        this.pollingActive = false;
        this.retryCount = 0;
        this.maxRetries = 5;
        this.lastNotificationId = null;
        this.init();
    }
    
    init() {
        console.log('🔔 NotificationSystem initialized at', new Date().toLocaleTimeString());
        
        // Force start polling immediately
        this.startPollingImmediately();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Initial load
        this.loadInitialData();
        
        this.isInitialized = true;
    }
    
    setupEventListeners() {
        console.log('🔔 Setting up event listeners...');
        
        // Mark all as read
        $(document).on('click', '#markAllRead', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.markAllAsRead();
        });
        
        // ==================== REVISI: NOTIFICATION CLICK DENGAN AUTO-SCROLL ====================
        $(document).on('click', '.notification-item', (e) => {
            if (!$(e.target).closest('a').length && !$(e.target).hasClass('btn-close')) {
                const notificationId = $(e.currentTarget).data('id');
                const notificationType = $(e.currentTarget).data('type');
                
                if (notificationId) {
                    // Mark as read first
                    this.markAsRead(notificationId);
                    
                    // Then scroll to related information in table
                    setTimeout(() => {
                        this.scrollToRelatedInformation(notificationId, notificationType);
                    }, 300);
                }
            }
        });
        
        // Dropdown show event
        $('#notificationDropdown').on('show.bs.dropdown', () => {
            console.log('🔔 Dropdown opened, loading notifications...');
            this.loadNotifications();
        });
        
        // Listen for page visibility change
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                console.log('🔔 Page became visible, checking notifications...');
                this.checkNewNotifications(true);
            }
        });
        
        // Listen for focus event
        window.addEventListener('focus', () => {
            console.log('🔔 Window focused, checking notifications...');
            this.checkNewNotifications(true);
        });
        
        // Custom event untuk trigger manual
        $(document).on('forceCheckNotifications', () => {
            console.log('🔔 Force check triggered');
            this.checkNewNotifications(true);
        });
        
        // Event ketika informasi baru ditambahkan (real-time)
        $(document).on('informationAdded', (e, data) => {
            console.log('🔔 New information added event:', data);
            setTimeout(() => {
                this.checkNewNotifications(true);
            }, 2000);
        });
    }
    
    startPollingImmediately() {
        console.log('🔔 Starting immediate polling...');
        
        // Clear any existing intervals
        if (this.pollingInterval) clearInterval(this.pollingInterval);
        if (this.deepCheckInterval) clearInterval(this.deepCheckInterval);
        
        // IMMEDIATE CHECK - lakukan sekarang juga
        setTimeout(() => {
            console.log('🔔 Executing immediate check...');
            this.checkNewNotifications(true);
        }, 500);
        
        // Start regular polling every 3 seconds untuk real-time
        this.pollingInterval = setInterval(() => {
            if (!this.pollingActive) {
                this.pollingActive = true;
                this.checkNewNotifications(false);
            }
        }, 3000);
        
        // Deep check every 10 seconds
        this.deepCheckInterval = setInterval(() => {
            if (!this.pollingActive) {
                this.pollingActive = true;
                this.checkNewNotifications(true);
            }
        }, 10000);
        
        console.log('🔔 Polling started successfully');
    }
    
    loadInitialData() {
        console.log('🔔 Loading initial data...');
        
        // Check immediately
        setTimeout(() => {
            this.checkNewNotifications(true);
        }, 1000);
        
        // Also check after 3 seconds to be sure
        setTimeout(() => {
            this.checkNewNotifications(true);
        }, 3000);
    }
    
    checkNewNotifications(isDeepCheck = false) {
        if (this.pollingActive) {
            console.log('🔔 Polling already active, skipping...');
            return;
        }
        
        this.pollingActive = true;
        const timestamp = new Date().getTime();
        const checkId = Math.random().toString(36).substr(2, 9);
        
        console.log(`🔔 [${checkId}] Checking notifications (deep: ${isDeepCheck}) at`, new Date().toLocaleTimeString());
        
        $.ajax({
            url: 'api/check_new_info.php',
            type: 'GET',
            data: { 
                _t: timestamp,
                deep: isDeepCheck ? 1 : 0,
                check_id: checkId
            },
            dataType: 'json',
            cache: false,
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'X-Requested-With': 'XMLHttpRequest'
            },
            timeout: 8000,
            beforeSend: () => {
                this.lastCheckTime = new Date();
                console.log(`🔔 [${checkId}] Request sent at`, this.lastCheckTime.toLocaleTimeString());
            },
            success: (response) => {
                console.log(`🔔 [${checkId}] Response received:`, {
                    success: response.success,
                    count: response.count,
                    assigned: response.assigned_to_me,
                    urgent: response.urgent_count,
                    timestamp: response.timestamp
                });
                
                if (response.success) {
                    const newCount = parseInt(response.count) || 0;
                    const assignedCount = parseInt(response.assigned_to_me) || 0;
                    const urgentCount = parseInt(response.urgent_count) || 0;
                    
                    // Reset retry count on success
                    this.retryCount = 0;
                    
                    // ==================== REVISI: REAL-TIME NOTIFICATION ====================
                    // Jika ada notifikasi baru yang urgent, beri alert langsung
                    if (urgentCount > 0 && newCount > 0 && newCount > this.notificationCount) {
                        console.log(`🔔 [${checkId}] New urgent notification detected!`);
                        this.showUrgentNotification(urgentCount, newCount);
                    }
                    
                    // ALWAYS update badge if count changed
                    if (newCount !== this.notificationCount || isDeepCheck) {
                        console.log(`🔔 [${checkId}] Count changed: ${this.notificationCount} → ${newCount}`);
                        
                        this.notificationCount = newCount;
                        this.updateBadge(newCount);
                        
                        // Play sound and show indicator for new notifications
                        if (urgentCount > 0 && newCount > 0) {
                            console.log(`🔔 [${checkId}] Urgent notifications found: ${urgentCount}`);
                            this.playNotificationSound();
                            this.showNewNotificationIndicator();
                        }
                        
                        // Auto-refresh notifications if dropdown is open
                        if ($('#notificationDropdown').hasClass('show')) {
                            console.log(`🔔 [${checkId}] Dropdown is open, refreshing...`);
                            this.loadNotifications();
                        }
                        
                        // Trigger custom event
                        $(document).trigger('notificationsUpdated', [newCount, urgentCount]);
                    }
                    
                    // Update title badge juga
                    this.updateTitleBadge(newCount);
                    
                } else {
                    console.error(`🔔 [${checkId}] API returned error:`, response);
                }
                
                this.pollingActive = false;
            },
            error: (xhr, status, error) => {
                console.error(`🔔 [${checkId}] Polling error:`, {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error
                });
                
                this.retryCount++;
                
                if (this.retryCount <= this.maxRetries) {
                    console.log(`🔔 [${checkId}] Retrying... (${this.retryCount}/${this.maxRetries})`);
                    
                    const retryDelay = Math.min(1000 * Math.pow(2, this.retryCount), 30000);
                    
                    setTimeout(() => {
                        this.pollingActive = false;
                        this.checkNewNotifications(true);
                    }, retryDelay);
                } else {
                    console.error(`🔔 [${checkId}] Max retries reached, stopping polling`);
                    this.pollingActive = false;
                }
            },
            complete: () => {
                console.log(`🔔 [${checkId}] Check completed at`, new Date().toLocaleTimeString());
                setTimeout(() => {
                    this.pollingActive = false;
                }, 100);
            }
        });
    }
    
    // ==================== FUNGSI BARU: SHOW URGENT NOTIFICATION ====================
    showUrgentNotification(urgentCount, totalCount) {
        // Show toast notification
        this.showToast('warning', 'Peringatan!', 
            `Anda memiliki ${urgentCount} notifikasi penting dari ${totalCount} notifikasi baru`);
        
        // Show desktop notification if supported
        this.showDesktopNotification(urgentCount);
    }
    
    showNewNotificationIndicator() {
        console.log('🔔 Showing new notification indicator...');
        
        // Add animation to bell icon
        const $bell = $('.notification-bell');
        $bell.addClass('animate__animated animate__tada');
        
        // Add pulse animation to badge
        const $badge = $('#notificationBadge');
        $badge.addClass('animate__animated animate__heartBeat');
        
        // Remove animations after 2 seconds
        setTimeout(() => {
            $bell.removeClass('animate__animated animate__tada');
            $badge.removeClass('animate__animated animate__heartBeat');
        }, 2000);
    }
    
    showDesktopNotification(count) {
        if (!("Notification" in window)) {
            console.log("🔔 Desktop notifications not supported");
            return;
        }
        
        if (Notification.permission === "granted") {
            const notification = new Notification("Notifikasi Baru - Progress BO", {
                body: `Anda memiliki ${count} notifikasi baru`,
                icon: "./assets/img/logo-denso.png",
                tag: "progress-bo-notification"
            });
            
            notification.onclick = function() {
                window.focus();
                this.close();
            };
            
            setTimeout(notification.close.bind(notification), 5000);
            
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    this.showDesktopNotification(count);
                }
            });
        }
    }
    
    loadNotifications() {
        console.log('🔔 Loading notification list...');
        
        $.ajax({
            url: 'api/get_notifications.php',
            type: 'GET',
            dataType: 'json',
            cache: false,
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            },
            timeout: 5000,
            beforeSend: () => {
                $('#notificationContainer').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <span class="ms-2" style="color: #adb5bd;">Memuat notifikasi...</span>
                    </div>
                `);
            },
            success: (response) => {
                console.log('🔔 Notifications loaded:', {
                    count: response.notifications?.length,
                    unread: response.unread_count
                });
                
                if (response.success) {
                    this.renderNotifications(response.notifications, response.unread_count);
                    
                    // Update badge with actual unread count
                    this.updateBadge(response.unread_count);
                    
                } else {
                    this.showEmptyState('Gagal memuat notifikasi');
                }
            },
            error: (xhr) => {
                console.error('🔔 Error loading notifications:', xhr.responseText);
                this.showEmptyState('Error memuat notifikasi');
            }
        });
    }
    
    renderNotifications(notifications, unreadCount) {
        const container = $('#notificationContainer');
        
        if (!notifications || notifications.length === 0) {
            this.showEmptyState();
            return;
        }
        
        let html = '';
        
        // Group by type
        const groups = {
            urgent: notifications.filter(n => n.badge_color === 'danger'),
            assigned: notifications.filter(n => n.notification_type === 'assigned_to_you'),
            delay: notifications.filter(n => n.type === 'delay'),
            other: notifications.filter(n => 
                !groups.urgent?.includes(n) && 
                !groups.assigned?.includes(n) && 
                !groups.delay?.includes(n)
            )
        };
        
        // Urgent first
        if (groups.urgent && groups.urgent.length > 0) {
            html += this.createNotificationGroup('PENTING', groups.urgent, 'danger');
        }
        
        // Assigned to you
        if (groups.assigned && groups.assigned.length > 0) {
            html += this.createNotificationGroup('DITUGASKAN KE ANDA', groups.assigned, 'warning');
        }
        
        // Delay notifications
        if (groups.delay && groups.delay.length > 0) {
            html += this.createNotificationGroup('KETERLAMBATAN PENGIRIMAN', groups.delay, 'danger');
        }
        
        // Other notifications
        if (groups.other && groups.other.length > 0) {
            html += this.createNotificationGroup('LAINNYA', groups.other, 'secondary');
        }
        
        container.html(html);
    }
    
    createNotificationGroup(title, notifications, color) {
        let html = `
            <div class="notification-group mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-uppercase fw-bold d-block text-${color}">
                        <i class="bi ${this.getGroupIcon(color)} me-1"></i>${title}
                    </small>
                    <span class="badge bg-${color} rounded-pill">${notifications.length}</span>
                </div>
        `;
        
        notifications.forEach(notification => {
            html += this.createNotificationItem(notification, color === 'danger');
        });
        
        html += `</div><hr class="my-3 opacity-25">`;
        
        return html;
    }
    
    getGroupIcon(color) {
        switch(color) {
            case 'danger': return 'bi-exclamation-triangle-fill';
            case 'warning': return 'bi-person-check';
            case 'secondary': return 'bi-bell';
            default: return 'bi-info-circle';
        }
    }
    
    createNotificationItem(notification, isUrgent = false) {
        const iconMap = {
            'information': 'bi-info-circle',
            'delay': 'bi-clock',
            'urgent': 'bi-exclamation-triangle',
            'assigned_to_you': 'bi-person-check'
        };
        
        const isUnread = notification.is_unread || false;
        const timeAgo = this.getTimeAgo(notification.datetime_full);
        const displayMessage = notification.display_message || notification.message || '';
        
        return `
            <div class="notification-item ${isUnread ? 'unread' : ''} ${isUrgent ? 'urgent-blink' : ''}" 
                 data-id="${notification.id}" 
                 data-type="${notification.type}"
                 style="cursor: pointer;"
                 title="Klik untuk melihat di tabel informasi">
                <div class="d-flex gap-3 align-items-start">
                    <div class="notification-icon bg-${notification.badge_color} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 40px; height: 40px;">
                        <i class="bi ${iconMap[notification.type] || 'bi-info-circle'} text-white"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="notification-title mb-0 text-truncate" style="font-size: 0.9rem; color: ${isUrgent ? '#dc3545' : '#1e293b'}">
                                ${notification.title}
                            </h6>
                            <span class="badge bg-${notification.badge_color} status-badge" 
                                  style="font-size: 0.65rem; padding: 2px 6px;">
                                ${notification.status_text}
                            </span>
                        </div>
                        <p class="notification-message mb-2" style="font-size: 0.85rem; line-height: 1.3; color: #475569;">
                            ${displayMessage.substring(0, 80)}${displayMessage.length > 80 ? '...' : ''}
                        </p>
                        <div class="notification-meta d-flex justify-content-between align-items-center">
                            <span class="notification-time" style="font-size: 0.75rem; color: #6c757d;">
                                <i class="bi bi-clock me-1"></i>${timeAgo}
                            </span>
                            <span class="notification-from fw-semibold" style="font-size: 0.75rem; color: #0066cc;">
                                <i class="bi bi-person me-1"></i>${notification.from_user}
                            </span>
                        </div>
                    </div>
                    ${isUnread ? `
                    <button type="button" class="btn-close btn-close-sm ms-2" aria-label="Tandai sudah dibaca" 
                            style="font-size: 0.6rem; opacity: 0.5;" 
                            onclick="event.stopPropagation(); window.notificationSystem.markAsRead('${notification.id}')">
                    </button>
                    ` : ''}
                </div>
            </div>
        `;
    }
    
    showEmptyState(message = 'Tidak ada notifikasi') {
        $('#notificationContainer').html(`
            <div class="empty-notifications text-center py-5">
                <i class="bi bi-bell-slash fs-1" style="color: #6c757d;"></i>
                <p class="mb-1 mt-3" style="color: #adb5bd;">${message}</p>
                <small class="text-muted">Semuanya sudah up to date</small>
            </div>
        `);
    }
    
    markAsRead(notificationId) {
        console.log('🔔 Marking as read:', notificationId);
        
        $.ajax({
            url: 'api/mark_notification_read.php',
            type: 'POST',
            data: { notification_id: notificationId },
            dataType: 'json',
            success: (response) => {
                if (response.success) {
                    // Remove unread style and close button
                    const $item = $(`.notification-item[data-id="${notificationId}"]`);
                    $item.removeClass('unread').find('.btn-close').remove();
                    
                    // Update count
                    if (this.notificationCount > 0) {
                        this.notificationCount--;
                        this.updateBadge(this.notificationCount);
                    }
                    
                    // Reload if dropdown open
                    if ($('#notificationDropdown').hasClass('show')) {
                        setTimeout(() => this.loadNotifications(), 300);
                    }
                }
            }
        });
    }
    
    markAllAsRead() {
        const unreadItems = $('.notification-item.unread');
        if (unreadItems.length === 0) {
            this.showToast('info', 'Tidak ada notifikasi yang belum dibaca');
            return;
        }
        
        const unreadIds = [];
        unreadItems.each(function() {
            const id = $(this).data('id');
            if (id) unreadIds.push(id);
        });
        
        Swal.fire({
            title: 'Tandai semua sudah dibaca?',
            text: `Ini akan menandai ${unreadIds.length} notifikasi sebagai sudah dibaca`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0066cc',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, tandai semua',
            cancelButtonText: 'Batal',
            background: '#ffffff',
            color: '#1e293b'
        }).then((result) => {
            if (result.isConfirmed) {
                let completed = 0;
                unreadIds.forEach(id => {
                    $.ajax({
                        url: 'api/mark_notification_read.php',
                        type: 'POST',
                        data: { notification_id: id },
                        success: () => {
                            completed++;
                            if (completed === unreadIds.length) {
                                // Update UI
                                $('.notification-item').removeClass('unread');
                                $('.notification-item .btn-close').remove();
                                this.notificationCount = 0;
                                this.updateBadge(0);
                                this.showToast('success', 'Semua notifikasi ditandai sudah dibaca');
                            }
                        }
                    });
                });
            }
        });
    }
    
    // ==================== FUNGSI BARU: AUTO-SCROLL KE INFORMASI TERKAIT ====================
    scrollToRelatedInformation(notificationId, notificationType) {
        console.log('🔔 Scrolling to related information:', notificationId, notificationType);
        
        if (notificationType === 'delay') {
            // Untuk delay notifications, scroll ke tabel progress
            this.scrollToProgressTable();
            return;
        }
        
        // Untuk information notifications, cari di tabel informasi
        if (typeof tableInformation !== 'undefined') {
            // Cari row yang sesuai dengan notificationId
            const data = tableInformation.rows().data();
            let foundRow = null;
            let rowIndex = -1;
            
            $.each(data, function(index, row) {
                if (row.ID_INFORMATION == notificationId) {
                    foundRow = row;
                    rowIndex = index;
                    return false;
                }
            });
            
            if (foundRow && rowIndex >= 0) {
                // Close notification dropdown
                $('#notificationDropdown').dropdown('hide');
                
                // Scroll ke tabel informasi section
                $('html, body').animate({
                    scrollTop: $('#table-information').offset().top - 100
                }, 800);
                
                // Highlight row dan scroll ke row tersebut
                setTimeout(() => {
                    const table = $('#table-information').DataTable();
                    const node = table.row(rowIndex).node();
                    
                    if (node) {
                        // Add highlight class
                        $(node).addClass('highlight-row');
                        
                        // Scroll table to row
                        const tableContainer = $('#table-information_wrapper .dataTables_scrollBody');
                        if (tableContainer.length > 0) {
                            const rowTop = $(node).position().top;
                            const containerHeight = tableContainer.height();
                            tableContainer.animate({
                                scrollTop: tableContainer.scrollTop() + rowTop - (containerHeight / 2)
                            }, 1000);
                        }
                        
                        // Remove highlight after 3 seconds
                        setTimeout(() => {
                            $(node).removeClass('highlight-row');
                        }, 3000);
                    }
                }, 1000);
            } else {
                // Jika tidak ditemukan, refresh tabel informasi
                if (typeof fetchDataInformation === 'function') {
                    fetchDataInformation();
                    this.showToast('info', 'Memuat ulang data informasi...');
                }
            }
        }
    }
    
    scrollToProgressTable() {
        // Scroll ke tabel progress
        $('html, body').animate({
            scrollTop: $('#table-detail-progress').offset().top - 100
        }, 800);
        
        this.showToast('info', 'Scroll ke tabel progress...');
    }
    
    updateBadge(count) {
        const $badge = $('#notificationBadge');
        const $infoBadge = $('#info-badge');
        
        console.log('🔔 Updating badge to:', count);
        
        if (count > 0) {
            const displayCount = count > 99 ? '99+' : count;
            $badge.text(displayCount).show().addClass('bg-danger');
            $infoBadge.text(count > 9 ? '9+' : count).show().addClass('bg-danger');
            
            // Add animation for new notifications
            if ($badge.text() !== count.toString()) {
                $badge.addClass('animate__animated animate__bounceIn');
                setTimeout(() => $badge.removeClass('animate__animated animate__bounceIn'), 1000);
            }
            
        } else {
            $badge.hide();
            $infoBadge.hide();
        }
    }
    
    updateTitleBadge(count) {
        if (count > 0) {
            document.title = `(${count}) Progress BO Control`;
        } else {
            document.title = "Progress BO Control";
        }
    }
    
    playNotificationSound() {
        try {
            const audio = new Audio('assets/sound/notification.mp3');
            audio.volume = 0.4;
            audio.play().catch(e => console.log("🔔 Audio play failed:", e));
        } catch (e) {
            console.log("🔔 Audio not supported");
        }
    }
    
    getTimeAgo(datetime) {
        if (!datetime) return 'Baru saja';
        
        const now = new Date();
        const past = new Date(datetime);
        const diffMs = now - past;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);
        
        if (diffMins < 1) return 'Baru saja';
        if (diffMins < 60) return `${diffMins}m lalu`;
        if (diffHours < 24) return `${diffHours}j lalu`;
        if (diffDays < 7) return `${diffDays}h lalu`;
        return past.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
    }
    
    showToast(type, title, message = '', duration = 3000) {
        const toast = $(`
            <div class="custom-toast toast-${type}">
                <div class="toast-icon">
                    <i class="bi ${type === 'success' ? 'bi-check-circle' : 
                                 type === 'error' ? 'bi-x-circle' : 
                                 type === 'warning' ? 'bi-exclamation-triangle' : 
                                 'bi-info-circle'}"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    ${message ? `<div class="toast-message">${message}</div>` : ''}
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
    
    // Public method untuk manual trigger
    forceCheck() {
        console.log('🔔 Manual force check triggered');
        this.checkNewNotifications(true);
    }
    
    destroy() {
        if (this.pollingInterval) clearInterval(this.pollingInterval);
        if (this.deepCheckInterval) clearInterval(this.deepCheckInterval);
        this.pollingActive = false;
        console.log('🔔 NotificationSystem destroyed');
    }
}

// GLOBAL INITIALIZATION
$(document).ready(function() {
    console.log('🔔 Document ready, initializing NotificationSystem...');
    
    // Pastikan hanya satu instance
    if (window.notificationSystem) {
        console.log('🔔 Instance already exists, destroying old...');
        window.notificationSystem.destroy();
    }
    
    // Create new instance
    window.notificationSystem = new NotificationSystem();
    
    // Expose forceCheck globally
    window.forceCheckNotifications = function() {
        if (window.notificationSystem) {
            window.notificationSystem.forceCheck();
        }
    };
    
    // Event untuk refresh dari system lain
    $(document).on('refreshNotifications', function() {
        console.log('🔔 Refresh event received');
        if (window.notificationSystem) {
            window.notificationSystem.forceCheck();
        }
    });
    
    // Auto-check ketika ada informasi baru dari information system
    $(document).ajaxSuccess(function(event, xhr, settings) {
        if (settings.url && settings.url.includes('data_information.php')) {
            console.log('🔔 Information system update detected, refreshing notifications...');
            setTimeout(() => {
                if (window.notificationSystem) {
                    window.notificationSystem.forceCheck();
                }
            }, 1500);
        }
    });
    
    console.log('🔔 NotificationSystem initialization complete');
});