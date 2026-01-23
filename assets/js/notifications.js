// assets/js/notifications.js - VERSION LENGKAP DENGAN REAL-TIME
class NotificationSystem {
    constructor() {
        this.intervalId = null;
        this.notificationCount = 0;
        this.init();
    }
    
    init() {
        console.log('🔔 NotificationSystem initialized');
        this.setupEventListeners();
        this.startPolling();
    }
    
    setupEventListeners() {
        // Mark all as read
        $(document).on('click', '#markAllRead', (e) => {
            e.preventDefault();
            this.markAllAsRead();
        });
        
        // Notification click
        $(document).on('click', '.notification-item', (e) => {
            if (!$(e.target).closest('a').length) {
                const notificationId = $(e.currentTarget).data('id');
                if (notificationId) {
                    this.markAsRead(notificationId);
                }
            }
        });
        
        // Dropdown show/hide
        $('#notificationDropdown').on('show.bs.dropdown', () => {
            this.loadNotifications();
        });
    }
    
    startPolling() {
        // Check every 5 seconds for new notifications
        this.intervalId = setInterval(() => {
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
            cache: false,
            success: (response) => {
                if (response.success) {
                    const newCount = response.count || 0;
                    
                    if (newCount !== this.notificationCount) {
                        this.notificationCount = newCount;
                        this.updateBadge(newCount);
                        
                        // Play sound if there are new urgent notifications
                        if (response.urgent_count > 0 && newCount > 0) {
                            this.playNotificationSound();
                        }
                        
                        // Auto-refresh notifications if dropdown is open
                        if ($('#notificationDropdown').hasClass('show')) {
                            this.loadNotifications();
                        }
                    }
                }
            },
            error: (xhr) => {
                console.error('❌ Error checking notifications:', xhr.responseText);
            }
        });
    }
    
    loadNotifications() {
        $.ajax({
            url: 'api/get_notifications.php',
            type: 'GET',
            dataType: 'json',
            cache: false,
            beforeSend: () => {
                $('#notificationContainer').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <span class="ms-2">Loading notifications...</span>
                    </div>
                `);
            },
            success: (response) => {
                if (response.success) {
                    this.renderNotifications(response.notifications, response.unread_count);
                } else {
                    this.showEmptyState('Failed to load notifications');
                }
            },
            error: (xhr) => {
                console.error('❌ Error loading notifications:', xhr.responseText);
                this.showEmptyState('Error loading notifications');
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
        
        // Group notifications by type
        const urgentNotifications = notifications.filter(n => n.notification_type === 'assigned_to_you');
        const yourInfoNotifications = notifications.filter(n => n.notification_type === 'your_information');
        const otherNotifications = notifications.filter(n => n.notification_type === 'other_information');
        const delayNotifications = notifications.filter(n => n.type === 'delay');
        
        // Urgent notifications (assigned to you)
        if (urgentNotifications.length > 0) {
            html += `
                <div class="notification-group mb-3">
                    <small class="text-uppercase fw-bold text-warning mb-2 d-block">URGENT</small>
            `;
            
            urgentNotifications.forEach(notification => {
                html += this.createNotificationItem(notification, true);
            });
            
            html += `</div>`;
        }
        
        // Your information
        if (yourInfoNotifications.length > 0) {
            html += `
                <div class="notification-group mb-3">
                    <small class="text-uppercase fw-bold text-info mb-2 d-block">Your Information</small>
            `;
            
            yourInfoNotifications.forEach(notification => {
                html += this.createNotificationItem(notification);
            });
            
            html += `</div>`;
        }
        
        // Delay notifications
        if (delayNotifications.length > 0) {
            html += `
                <div class="notification-group mb-3">
                    <small class="text-uppercase fw-bold text-danger mb-2 d-block">Delivery Delay</small>
            `;
            
            delayNotifications.forEach(notification => {
                html += this.createNotificationItem(notification);
            });
            
            html += `</div>`;
        }
        
        // Other notifications
        if (otherNotifications.length > 0) {
            html += `
                <div class="notification-group">
                    <small class="text-uppercase fw-bold text-muted mb-2 d-block">Other Information</small>
            `;
            
            otherNotifications.forEach(notification => {
                html += this.createNotificationItem(notification);
            });
            
            html += `</div>`;
        }
        
        container.html(html);
        
        // Update badge
        this.updateBadge(unreadCount);
    }
    
    createNotificationItem(notification, isUrgent = false) {
        const iconMap = {
            'information': 'bi-info-circle',
            'delay': 'bi-clock',
            'urgent': 'bi-exclamation-triangle'
        };
        
        const colorMap = {
            'information': 'info',
            'delay': 'danger',
            'urgent': 'danger'
        };
        
        const type = isUrgent ? 'urgent' : notification.type;
        const isUnread = notification.is_unread || false;
        const timeAgo = this.getTimeAgo(notification.datetime_full);
        
        return `
            <div class="notification-item ${isUnread ? 'unread' : ''} ${isUrgent ? 'urgent-blink' : ''}" 
                 data-id="${notification.id}" style="cursor: pointer;">
                <div class="d-flex gap-3">
                    <div class="notification-icon bg-${colorMap[type]} rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi ${iconMap[type]} text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="notification-title mb-0">${notification.title}</h6>
                            <span class="badge bg-${notification.badge_color} status-badge">${notification.status_text}</span>
                        </div>
                        <p class="notification-message mb-2">${notification.display_message || notification.message}</p>
                        <div class="notification-meta d-flex justify-content-between">
                            <span class="notification-time">${timeAgo}</span>
                            <span class="notification-from">${notification.from_user}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    showEmptyState(message = 'No notifications') {
        $('#notificationContainer').html(`
            <div class="empty-notifications text-center py-5">
                <i class="bi bi-bell-slash fs-1 text-muted mb-3"></i>
                <p class="mb-1">${message}</p>
                <small class="text-muted">Everything is up to date</small>
            </div>
        `);
    }
    
    markAsRead(notificationId) {
        $.ajax({
            url: 'api/mark_notification_read.php',
            type: 'POST',
            data: { notification_id: notificationId },
            dataType: 'json',
            success: (response) => {
                if (response.success) {
                    // Remove unread style
                    $(`.notification-item[data-id="${notificationId}"]`).removeClass('unread');
                    
                    // Update badge
                    this.checkNewNotifications();
                }
            }
        });
    }
    
    markAllAsRead() {
        // Get all unread notification IDs
        const unreadIds = [];
        $('.notification-item.unread').each(function() {
            const id = $(this).data('id');
            if (id) unreadIds.push(id);
        });
        
        if (unreadIds.length === 0) return;
        
        // Mark each as read
        let completed = 0;
        unreadIds.forEach(id => {
            $.ajax({
                url: 'api/mark_notification_read.php',
                type: 'POST',
                data: { notification_id: id },
                dataType: 'json',
                success: () => {
                    completed++;
                    if (completed === unreadIds.length) {
                        // Update UI
                        $('.notification-item').removeClass('unread');
                        this.checkNewNotifications();
                        this.showToast('success', 'All notifications marked as read');
                    }
                }
            });
        });
    }
    
    updateBadge(count) {
        const $badge = $('#notificationBadge');
        const $infoBadge = $('#info-badge');
        
        if (count > 0) {
            $badge.text(count).show().addClass('bg-danger');
            $infoBadge.text(count).show().addClass('bg-danger');
            
            // Animate badge for new notifications
            if ($badge.text() !== count.toString()) {
                $badge.addClass('animate__animated animate__pulse');
                setTimeout(() => $badge.removeClass('animate__pulse'), 2000);
            }
        } else {
            $badge.hide();
            $infoBadge.hide();
        }
    }
    
    playNotificationSound() {
        try {
            const audio = new Audio('assets/sound/notification.mp3');
            audio.volume = 0.3;
            audio.play().catch(e => console.log("Audio play failed:", e));
        } catch (e) {
            console.log("Audio not supported");
        }
    }
    
    getTimeAgo(datetime) {
        if (!datetime) return 'Just now';
        
        const now = new Date();
        const past = new Date(datetime);
        const diffMs = now - past;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);
        
        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays < 7) return `${diffDays}d ago`;
        return past.toLocaleDateString();
    }
    
    showToast(type, title, message) {
        const toast = $(`
            <div class="custom-toast toast-${type}">
                <div class="toast-icon">
                    <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-info-circle'}"></i>
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
        }, 3000);
    }
}

// Initialize notification system
$(document).ready(function() {
    console.log('🔔 Initializing NotificationSystem...');
    window.notificationSystem = new NotificationSystem();
});