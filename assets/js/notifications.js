// assets/js/notifications.js - VERSION FINAL TANPA AUDIO
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
        
        // Notification click
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
                    
                    // ==================== REVISI: REAL-TIME NOTIFICATION TANPA AUDIO ====================
                    // Jika ada notifikasi baru yang urgent, beri visual alert saja
                    if (urgentCount > 0 && newCount > 0 && newCount > this.notificationCount) {
                        console.log(`🔔 [${checkId}] New urgent notification detected!`);
                        this.showUrgentNotification(urgentCount, newCount);
                    }
                    
                    // ALWAYS update badge if count changed
                    if (newCount !== this.notificationCount || isDeepCheck) {
                        console.log(`🔔 [${checkId}] Count changed: ${this.notificationCount} → ${newCount}`);
                        
                        this.notificationCount = newCount;
                        this.updateBadge(newCount);
                        
                        // Show visual indicator untuk new notifications (TANPA AUDIO)
                        if (urgentCount > 0 && newCount > 0) {
                            console.log(`🔔 [${checkId}] Urgent notifications found: ${urgentCount}`);
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
    
    showNewNotificationIndicator() {
        console.log('🔔 Showing new notification indicator (visual only)...');
        
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
    
    showUrgentNotification(urgentCount, totalCount) {
        // Visual indicator saja, tanpa audio
        const $badge = $('#notificationBadge');
        
        // Add urgent animation
        $badge.addClass('animate__animated animate__pulse animate__infinite');
        
        // Change badge color to red
        $badge.removeClass('bg-danger bg-warning bg-primary')
               .addClass('bg-danger');
        
        // Update document title
        document.title = `(${totalCount}) Progress BO Control - ${urgentCount} URGENT!`;
        
        // Show visual toast
        this.showVisualToast(urgentCount, totalCount);
    }
    
    showVisualToast(urgentCount, totalCount) {
        // Remove existing toasts
        $('.urgent-toast').remove();
        
        const toast = $(`
            <div class="custom-toast urgent-toast" style="border-left-color: #dc3545">
                <div class="toast-icon" style="color: #dc3545">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">Notifikasi Baru!</div>
                    <div class="toast-message">
                        ${urgentCount} notifikasi urgent, total ${totalCount} notifikasi baru
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        
        setTimeout(() => toast.addClass('show'), 10);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.removeClass('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
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
                        <p class="notification-message mb-2" style="font-size: 0.85rem; color: #64748b;">
                            ${displayMessage}
                        </p>
                        <div class="notification-meta">
                            <span class="notification-time">
                                <i class="bi bi-clock me-1"></i>${timeAgo}
                            </span>
                            <span class="notification-from">
                                <i class="bi bi-person me-1"></i>${notification.from_user}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    getTimeAgo(dateTimeString) {
        if (!dateTimeString) return 'Baru saja';
        
        const now = new Date();
        const past = new Date(dateTimeString);
        const diffMs = now - past;
        const diffMins = Math.floor(diffMs / (1000 * 60));
        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        
        if (diffMins < 1) return 'Baru saja';
        if (diffMins < 60) return `${diffMins} menit lalu`;
        if (diffHours < 24) return `${diffHours} jam lalu`;
        if (diffDays < 7) return `${diffDays} hari lalu`;
        
        return new Date(dateTimeString).toLocaleDateString('id-ID');
    }
    
    showEmptyState(message = 'Tidak ada notifikasi') {
        $('#notificationContainer').html(`
            <div class="empty-notifications text-center py-5">
                <i class="bi bi-bell-slash" style="font-size: 3rem; color: #94a3b8;"></i>
                <p class="mt-3 mb-1" style="color: #64748b;">${message}</p>
                <small style="color: #94a3b8;">Notifikasi baru akan muncul di sini</small>
            </div>
        `);
    }
    
    updateBadge(count) {
        const $badge = $('#notificationBadge');
        const $infoBadge = $('#info-badge');
        
        if (count > 0) {
            $badge.text(count).show().addClass('bg-danger');
            $infoBadge.text(count).show().addClass('bg-danger');
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
    
    markAsRead(notificationId) {
        if (!notificationId) return;
        
        $.ajax({
            url: 'api/mark_notification_read.php',
            type: 'POST',
            data: { notification_id: notificationId },
            dataType: 'json',
            success: (response) => {
                if (response.success) {
                    // Remove unread class
                    $(`.notification-item[data-id="${notificationId}"]`).removeClass('unread');
                    
                    // Update badge count
                    this.checkNewNotifications(true);
                }
            },
            error: (xhr) => {
                console.error('Error marking as read:', xhr.responseText);
            }
        });
    }
    
    markAllAsRead() {
        const $unreadItems = $('.notification-item.unread');
        const notificationIds = [];
        
        $unreadItems.each(function() {
            const id = $(this).data('id');
            if (id) notificationIds.push(id);
        });
        
        if (notificationIds.length === 0) return;
        
        // Mark each as read
        notificationIds.forEach(id => {
            this.markAsRead(id);
        });
        
        // Show success message
        this.showToast('success', `Marked ${notificationIds.length} notifications as read`);
    }
    
    scrollToRelatedInformation(notificationId, notificationType) {
        if (notificationType === 'information' || notificationType === 'delay') {
            // Scroll to information table
            if (typeof fetchDataInformation === 'function') {
                fetchDataInformation();
                
                // Highlight row after data loads
                setTimeout(() => {
                    const $row = $(`#table-information tr[data-id="${notificationId}"]`);
                    if ($row.length) {
                        $row.addClass('highlight-row');
                        $('html, body').animate({
                            scrollTop: $row.offset().top - 100
                        }, 1000);
                        
                        // Remove highlight after 3 seconds
                        setTimeout(() => {
                            $row.removeClass('highlight-row');
                        }, 3000);
                    }
                }, 1000);
            }
        }
    }
    
    showToast(type, message) {
        $('.custom-toast').remove();
        
        const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill';
        const title = type === 'success' ? 'Success' : 'Info';
        const color = type === 'success' ? '#10b981' : '#3b82f6';
        
        const toast = $(`
            <div class="custom-toast" style="border-left-color: ${color}">
                <div class="toast-icon" style="color: ${color}">
                    <i class="bi ${icon}"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        
        setTimeout(() => toast.addClass('show'), 10);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.removeClass('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Public method untuk force check
    forceCheck() {
        console.log('🔔 Force check called');
        this.checkNewNotifications(true);
    }
}

// Initialize on document ready
$(document).ready(function() {
    console.log('🔔 Initializing NotificationSystem...');
    
    if (!window.notificationSystem) {
        window.notificationSystem = new NotificationSystem();
        console.log('✅ NotificationSystem initialized');
    }
    
    // Expose forceCheck untuk global access
    window.forceNotificationCheck = function() {
        if (window.notificationSystem) {
            window.notificationSystem.forceCheck();
        }
    };
});