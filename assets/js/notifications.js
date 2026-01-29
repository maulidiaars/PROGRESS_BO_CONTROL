// assets/js/notifications.js - VERSION LENGKAP SATU FILE
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
        this.highlightActive = false;
        this.currentHighlightId = null;
        this.pendingHighlightData = null;
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
        
        // Check URL parameter for notification highlight
        this.checkUrlForHighlight();
        
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
        
        // Notification click - REVISED FOR HIGHLIGHT
        $(document).on('click', '.notification-item', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Skip jika klik di close button atau link
            if ($(e.target).closest('.btn-close').length || $(e.target).closest('a').length) {
                return;
            }
            
            const notificationId = $(e.currentTarget).data('id');
            const notificationType = $(e.currentTarget).data('type');
            
            if (notificationId) {
                console.log(`🔔 Notification clicked: ${notificationId}, type: ${notificationType}`);
                
                // Mark as read first
                this.markAsRead(notificationId);
                
                // Remove unread styling
                $(e.currentTarget).removeClass('unread');
                
                // FORCE close dropdown sebelum scroll
                $('#notificationDropdown').dropdown('hide');
                
                // Delay dikit biar dropdown tertutup dulu
                setTimeout(() => {
                    this.scrollToRelatedInformation(notificationId, notificationType);
                }, 300);
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
            this.removeAllHighlights();
        });
        
        // Custom event untuk trigger manual
        $(document).on('forceCheckNotifications', () => {
            console.log('🔔 Force check triggered');
            this.checkNewNotifications(true);
        });
        
        // Custom event ketika data tabel selesai di-load
        $(document).on('informationTableLoaded', (event, data) => {
            console.log('📋 Information table loaded, checking for pending highlights...');
            if (this.currentHighlightId) {
                setTimeout(() => {
                    this.highlightInformationRow(this.currentHighlightId);
                }, 500);
            }
        });
        
        // Close highlight ketika klik di luar
        $(document).on('click', (e) => {
            if (!$(e.target).closest('.highlighted-row').length && !$(e.target).closest('.notification-item').length) {
                this.removeAllHighlights();
            }
        });
    }
    
    checkUrlForHighlight() {
        const urlParams = new URLSearchParams(window.location.search);
        const highlightId = urlParams.get('highlight');
        const notificationId = urlParams.get('notification_id');
        
        if (highlightId || notificationId) {
            const idToHighlight = highlightId || notificationId;
            console.log(`🔗 URL contains highlight parameter: ${idToHighlight}`);
            
            // Set timeout untuk menunggu halaman selesai load
            setTimeout(() => {
                this.highlightInformationRow(idToHighlight);
                this.markAsRead(idToHighlight);
                
                // Clean URL tanpa refresh
                const cleanUrl = window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }, 1500);
        }
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
                        <br><small class="text-muted">Klik notifikasi untuk melihat detail</small>
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
        
        // Group notifications by type untuk visual yang lebih baik
        const informationNotifs = notifications.filter(n => n.type === 'information');
        const delayNotifs = notifications.filter(n => n.type === 'delay');
        
        // Group information notifications by user role
        const assignedNotifs = informationNotifs.filter(n => n.user_role === 'recipient');
        const viewerNotifs = informationNotifs.filter(n => n.user_role === 'viewer');
        
        // Render assigned notifications first (most important)
        if (assignedNotifs.length > 0) {
            html += this.createNotificationGroup('DITUGASKAN UNTUK ANDA', assignedNotifs, 'warning');
        }
        
        // Render other information notifications
        if (viewerNotifs.length > 0) {
            html += this.createNotificationGroup('INFORMASI UMUM', viewerNotifs, 'info');
        }
        
        // Render delay notifications
        if (delayNotifs.length > 0) {
            html += this.createNotificationGroup('KETERLAMBATAN PENGIRIMAN', delayNotifs, 'danger');
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
        const canReply = notification.can_reply || false;
        const userRole = notification.user_role || 'viewer';
        
        // Icon warna berdasarkan role
        let iconColor = 'primary';
        let iconType = 'bi-info-circle';
        
        if (userRole === 'recipient') {
            iconColor = notification.badge_color === 'danger' ? 'danger' : 'warning';
            iconType = 'bi-person-check';
        } else if (notification.type === 'delay') {
            iconColor = 'danger';
            iconType = 'bi-clock';
        }
        
        // Badge untuk role
        let roleBadge = '';
        if (userRole === 'recipient') {
            roleBadge = `<span class="badge bg-warning ms-2" style="font-size: 0.6rem;">UNTUK ANDA</span>`;
        } else if (notification.type === 'delay') {
            roleBadge = `<span class="badge bg-danger ms-2" style="font-size: 0.6rem;">TERLAMBAT</span>`;
        } else {
            roleBadge = `<span class="badge bg-secondary ms-2" style="font-size: 0.6rem;">INFO</span>`;
        }
        
        // Badge untuk status reply
        let replyBadge = '';
        if (canReply) {
            replyBadge = `<span class="badge bg-success ms-1" style="font-size: 0.5rem;">BISA REPLY</span>`;
        }
        
        return `
            <div class="notification-item ${isUnread ? 'unread' : ''} ${isUrgent ? 'urgent-blink' : ''}" 
                data-id="${notification.id}" 
                data-type="${notification.type}"
                data-can-reply="${canReply}"
                data-user-role="${userRole}"
                style="cursor: pointer; border-left: 3px solid ${userRole === 'recipient' ? '#ffc107' : '#0d6efd'};"
                title="Klik untuk melihat detail di tabel informasi">
                <div class="d-flex gap-3 align-items-start py-2">
                    <div class="notification-icon bg-${iconColor} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                        style="width: 40px; height: 40px;">
                        <i class="bi ${iconType} text-white"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="notification-title mb-0 text-truncate" style="font-size: 0.9rem; color: ${isUrgent ? '#dc3545' : '#1e293b'}">
                                ${notification.title} ${roleBadge} ${replyBadge}
                            </h6>
                            <span class="badge bg-${notification.badge_color} status-badge" 
                                style="font-size: 0.65rem; padding: 2px 6px;">
                                ${notification.status_text}
                            </span>
                        </div>
                        <p class="notification-message mb-2" style="font-size: 0.85rem; color: #64748b;">
                            ${displayMessage}
                        </p>
                        <div class="notification-meta d-flex justify-content-between align-items-center">
                            <span class="notification-time" style="font-size: 0.75rem; color: #94a3b8;">
                                <i class="bi bi-clock me-1"></i>${timeAgo}
                            </span>
                            <span class="notification-from" style="font-size: 0.75rem; color: #64748b;">
                                <i class="bi bi-person me-1"></i>${notification.from_user}
                                ${userRole === 'recipient' ? ' → <i class="bi bi-arrow-right me-1"></i>ANDA' : ''}
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
                    console.log(`✅ Marked notification ${notificationId} as read`);
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
        
        // Clear all unread styling
        $unreadItems.removeClass('unread');
        
        // Show success message
        this.showToast('success', `Marked ${notificationIds.length} notifications as read`);
    }
    
scrollToRelatedInformation(notificationId, notificationType) {
    console.log(`🎯 INSTANT Scroll to information: ${notificationId}, type: ${notificationType}`);
    
    if (notificationType === 'information') {
        // 1. TUTUP DROPDOWN SEKARANG JUGA (tanpa nunggu)
        $('#notificationDropdown').dropdown('hide');
        
        // 2. LANGSUNG SCROLL KE TABEL INFORMASI (tanpa delay)
        const $informationSection = $('#information-section');
        if ($informationSection.length) {
            $('html, body').stop().animate({
                scrollTop: $informationSection.offset().top - 80
            }, 300); // SUPER FAST 300ms aja!
        }
        
        // 3. LANGSUNG HIGHLIGHT ROW (tanpa nunggu)
        setTimeout(() => {
            this.instantHighlightRow(notificationId);
        }, 50); // Cuma nunggu 50ms biar scroll dulu dikit
        
    } else if (notificationType === 'delay') {
        // Untuk delay notifications - langsung tutup dropdown aja
        $('#notificationDropdown').dropdown('hide');
        this.showToast('info', 'Notification keterlambatan pengiriman');
    }
}

instantHighlightRow(notificationId) {
    console.log(`⚡ INSTANT Highlight row for: ${notificationId}`);
    
    // HAPUS SEMUA HIGHLIGHT LAMA
    $('.highlighted-row').removeClass('highlighted-row');
    
    // CARI ROW DENGAN ID YANG COCOK
    let foundRow = null;
    
    // Method 1: Cari berdasarkan data-id langsung
    foundRow = $(`tr[data-id="${notificationId}"]`);
    
    // Method 2: Kalau ga ketemu, cari di semua row
    if (!foundRow.length) {
        $('#table-information tbody tr').each(function() {
            const $row = $(this);
            const rowId = $row.data('id');
            const rowText = $row.text();
            
            // Cek apakah notificationId ada di dalam row
            if (rowId == notificationId || 
                rowText.includes(notificationId) ||
                $row.find('.btn-edit-info[data-id="' + notificationId + '"]').length ||
                $row.find('.btn-reply-info[data-id="' + notificationId + '"]').length) {
                foundRow = $row;
                return false; // Break loop
            }
        });
    }
    
    // Method 3: Kalau masih ga ketemu, coba cari dari ID numerik aja
    if (!foundRow.length && typeof notificationId === 'string') {
        const numericId = notificationId.replace(/\D/g, '');
        if (numericId) {
            foundRow = $(`tr[data-id="${numericId}"]`);
        }
    }
    
    // JIKA KETEMU, APPLY HIGHLIGHT SUPER FAST
    if (foundRow && foundRow.length) {
        console.log(`✅ Found row instantly!`);
        
        // Apply highlight langsung
        foundRow.addClass('highlighted-row animate__animated animate__pulse');
        foundRow.css({
            'border-left': '4px solid #ff0000',
            'border-right': '4px solid #ff0000',
            'background-color': 'rgba(255, 0, 0, 0.15)',
            'box-shadow': '0 0 20px rgba(255, 0, 0, 0.4)'
        });
        
        // SCROLL KE ROW TERSEBUT (lagi buat lebih akurat)
        setTimeout(() => {
            const rowPosition = foundRow.offset().top;
            $('html, body').stop().animate({
                scrollTop: rowPosition - 150
            }, 200);
            
            // TAMBAH EFFECT BLINK UNTUK PERHATIAN EXTRA
            foundRow.addClass('animate__flash');
            setTimeout(() => {
                foundRow.removeClass('animate__flash');
            }, 1000);
            
        }, 100);
        
        // AUTO REMOVE HIGHLIGHT SETELAH 10 DETIK
        setTimeout(() => {
            foundRow.removeClass('highlighted-row animate__animated animate__pulse');
            foundRow.css({
                'border-left': '',
                'border-right': '',
                'background-color': '',
                'box-shadow': ''
            });
        }, 10000);
        
    } else {
        console.log(`❌ Row not found instantly, trying AJAX lookup...`);
        // Fallback ke method lama kalau ga ketemu
        this.lookupAndHighlight(notificationId);
    }
}

lookupAndHighlight(notificationId) {
    // AJAX cepat untuk dapetin data
    $.ajax({
        url: 'modules/data_information.php',
        type: 'GET',
        data: { type: 'get-single', id: notificationId },
        dataType: 'json',
        timeout: 2000, // Cuma 2 detik timeout
        success: (response) => {
            if (response.success && response.data) {
                const info = response.data;
                console.log('✅ Data found via AJAX:', info);
                
                // Cari row berdasarkan data yang didapat
                this.findRowByInfoData(info);
            } else {
                this.showToast('warning', 'Data tidak ditemukan di sistem');
            }
        },
        error: () => {
            this.showToast('error', 'Gagal mengambil data');
        }
    });
}

findRowByInfoData(infoData) {
    // Cari row berdasarkan PIC_FROM + ITEM + DATE (kombinasi unik)
    $('#table-information tbody tr').each(function() {
        const $row = $(this);
        const rowPicFrom = $row.find('td').eq(3).text().trim();
        const rowItem = $row.find('td').eq(4).text().trim();
        const rowDate = $row.find('td').eq(1).text().trim();
        
        if (rowPicFrom === infoData.PIC_FROM && 
            rowItem === infoData.ITEM && 
            rowDate === infoData.DATE) {
            
            console.log(`✅ Found matching row by data!`);
            
            // Apply highlight
            $row.addClass('highlighted-row animate__animated animate__pulse');
            $row.css({
                'border-left': '4px solid #ff0000',
                'border-right': '4px solid #ff0000',
                'background-color': 'rgba(255, 0, 0, 0.15)'
            });
            
            // Scroll ke row
            setTimeout(() => {
                const rowPosition = $row.offset().top;
                $('html, body').stop().animate({
                    scrollTop: rowPosition - 150
                }, 200);
            }, 50);
            
            return false; // Break loop
        }
    });
}
    
    highlightInformationRow(notificationId) {
        console.log(`🎨 Highlighting row for notification ID: ${notificationId}`);
        
        // Remove previous highlights
        this.removeAllHighlights();
        
        // Reset current highlight ID
        this.currentHighlightId = null;
        
        // Tunggu sebentar untuk memastikan DataTable sudah render
        setTimeout(() => {
            // Cari semua row di tabel informasi
            const $allRows = $('#table-information tbody tr');
            let found = false;
            
            console.log(`🔍 Searching in ${$allRows.length} rows for ID: ${notificationId}`);
            
            // Extract numeric ID dari notificationId
            let searchId = notificationId;
            if (typeof notificationId === 'string') {
                const idMatch = notificationId.match(/\d+/);
                if (idMatch) {
                    searchId = idMatch[0];
                }
            }
            
            // Method 1: Cari berdasarkan data-id
            $allRows.each((index, row) => {
                const $row = $(row);
                
                // Dapatkan ID_INFORMATION dari row
                const rowId = $row.data('id');
                const infoId = $row.find('td').eq(1).text(); // Kolom Date
                const picFrom = $row.find('td').eq(3).text(); // Kolom PIC FROM
                const item = $row.find('td').eq(4).text(); // Kolom Item
                
                // Debug log
                console.log(`Row ${index}:`, {
                    rowId: rowId,
                    infoId: infoId,
                    picFrom: picFrom,
                    item: item
                });
                
                // Check dengan beberapa kemungkinan
                // Method 1: Cek apakah notificationId ada di data attribute
                if (rowId == searchId || 
                    $row.attr('id') === 'info-' + searchId ||
                    infoId.toString().includes(searchId.toString())) {
                    
                    console.log(`✅ Found match at row ${index} by infoId`);
                    found = true;
                    this.applyRowHighlight($row);
                    return false; // Break loop
                }
                
                // Method 2: Coba cari berdasarkan PIC_FROM dan Item (jika notificationId adalah ID database)
                if (picFrom && item) {
                    const rowSignature = picFrom.toLowerCase() + '_' + item.toLowerCase().substring(0, 30);
                    const notificationSignature = notificationId.toString().toLowerCase();
                    
                    if (rowSignature.includes(notificationSignature) || 
                        notificationSignature.includes(rowSignature)) {
                        
                        console.log(`✅ Found match at row ${index} by signature`);
                        found = true;
                        this.applyRowHighlight($row);
                        return false;
                    }
                }
            });
            
            // Method 3: Jika tidak ditemukan, coba cari dengan AJAX untuk data spesifik
            if (!found) {
                console.log(`⚠️ Row not found, trying AJAX lookup...`);
                this.lookupInformationById(notificationId);
            }
        }, 500); // Delay untuk memastikan DataTable sudah selesai render
    }
    
    applyRowHighlight($row) {
        // Add highlight class
        $row.addClass('highlighted-row animate__animated animate__pulse');
        
        // Add styling
        $row.css({
            'border-left': '4px solid #ffc107',
            'border-right': '4px solid #ffc107',
            'background-color': 'rgba(255, 193, 7, 0.15)',
            'box-shadow': '0 0 15px rgba(255, 193, 7, 0.3)',
            'position': 'relative'
        });
        
        // Scroll ke row dengan smooth animation
        setTimeout(() => {
            const rowTop = $row.offset().top;
            const windowHeight = $(window).height();
            const scrollPosition = rowTop - (windowHeight / 3);
            
            $('html, body').stop().animate({
                scrollTop: scrollPosition
            }, 1000);
            
            // Tambah efek shake untuk perhatian
            setTimeout(() => {
                $row.addClass('animate__shakeX');
                setTimeout(() => {
                    $row.removeClass('animate__shakeX');
                }, 1000);
            }, 500);
            
        }, 300);
        
        // Auto remove highlight after 15 seconds
        setTimeout(() => {
            this.removeAllHighlights();
        }, 15000);
    }
    
    lookupInformationById(notificationId) {
        // AJAX request untuk mendapatkan data spesifik
        $.ajax({
            url: 'modules/data_information.php',
            type: 'GET',
            data: {
                type: 'get-single',
                id: notificationId
            },
            dataType: 'json',
            success: (response) => {
                if (response.success && response.data) {
                    const info = response.data;
                    console.log('✅ Information found:', info);
                    
                    // Cari row berdasarkan data yang didapat
                    this.findAndHighlightByInfoData(info);
                } else {
                    this.showToast('warning', 'Informasi tidak ditemukan');
                }
            },
            error: () => {
                this.showToast('error', 'Gagal mencari informasi');
            }
        });
    }
    
    findAndHighlightByInfoData(infoData) {
        const $allRows = $('#table-information tbody tr');
        let found = false;
        
        $allRows.each((index, row) => {
            const $row = $(row);
            
            // Bandingkan data row dengan infoData
            const rowPicFrom = $row.find('td').eq(3).text().trim(); // PIC FROM
            const rowItem = $row.find('td').eq(4).text().trim(); // ITEM
            const rowDate = $row.find('td').eq(1).text().trim(); // DATE
            
            if (rowPicFrom === infoData.PIC_FROM && 
                rowItem === infoData.ITEM && 
                rowDate === infoData.DATE) {
                
                console.log(`✅ Found matching row by data comparison`);
                found = true;
                this.applyRowHighlight($row);
                return false;
            }
        });
        
        if (!found) {
            // Refresh table dan coba lagi
            if (typeof fetchDataInformation === 'function') {
                this.showToast('info', 'Memuat ulang data untuk menemukan informasi...');
                fetchDataInformation();
                
                // Simpan data untuk highlight nanti
                this.pendingHighlightData = infoData;
                
                // Listen untuk DataTable draw
                $('#table-information').on('draw.dt', () => {
                    setTimeout(() => {
                        this.findAndHighlightByInfoData(this.pendingHighlightData);
                        delete this.pendingHighlightData;
                    }, 500);
                });
            }
        }
    }
    
    removeAllHighlights() {
        console.log('🗑️ Removing all highlights...');
        
        $('.highlighted-row').each((index, row) => {
            $(row).removeClass('highlighted-row animate__animated animate__pulse animate__shakeX');
            $(row).css({
                'border-left': '',
                'border-right': '',
                'background-color': '',
                'box-shadow': ''
            });
        });
        
        this.highlightActive = false;
        this.currentHighlightId = null;
    }
    
    showToast(type, message) {
        $('.custom-toast').remove();
        
        const iconMap = {
            'success': 'bi-check-circle-fill',
            'error': 'bi-exclamation-triangle-fill',
            'warning': 'bi-exclamation-triangle-fill',
            'info': 'bi-info-circle-fill'
        };
        
        const colorMap = {
            'success': '#10b981',
            'error': '#dc3545',
            'warning': '#f59e0b',
            'info': '#3b82f6'
        };
        
        const icon = iconMap[type] || 'bi-info-circle-fill';
        const color = colorMap[type] || '#3b82f6';
        const title = type === 'success' ? 'Success' : 
                     type === 'error' ? 'Error' : 
                     type === 'warning' ? 'Warning' : 'Info';
        
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
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.removeClass('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
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
    
    // Expose methods untuk global access
    window.forceNotificationCheck = function() {
        if (window.notificationSystem) {
            window.notificationSystem.forceCheck();
        }
    };
    
    window.highlightNotificationRow = function(notificationId) {
        if (window.notificationSystem) {
            window.notificationSystem.highlightInformationRow(notificationId);
        }
    };
    
    window.removeAllHighlights = function() {
        if (window.notificationSystem) {
            window.notificationSystem.removeAllHighlights();
        }
    };
});