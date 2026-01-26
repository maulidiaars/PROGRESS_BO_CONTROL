// assets/js/notifications.js - VERSION OPTIMIZED DAN STABIL
class NotificationSystem {
    constructor() {
        console.log('🔔 NotificationSystem initialized - OPTIMIZED VERSION');
        
        // OPTIMIZATION: Gunakan Web Worker jika memungkinkan
        this.useWebWorker = typeof Worker !== 'undefined';
        
        // Polling configuration - AGGRESIVE OPTIMIZATION
        this.config = {
            normalPollingInterval: 45000,      // 45 detik (diperpanjang)
            backgroundPollingInterval: 120000,  // 2 menit saat background
            deepCheckInterval: 300000,          // 5 menit untuk deep check
            connectionTimeout: 5000,            // Timeout 5 detik saja
            maxConsecutiveTimeouts: 3,
            backoffMultiplier: 1.5
        };
        
        // State management
        this.state = {
            pollingActive: false,
            lastCheckTime: 0,
            lastSuccessfulCheck: 0,
            consecutiveTimeouts: 0,
            consecutiveErrors: 0,
            isOnline: navigator.onLine,
            pageVisible: !document.hidden,
            connectionQuality: 'good', // good, slow, poor
            retryDelay: 0
        };
        
        // Cache system
        this.cache = {
            notifications: null,
            lastCacheTime: 0,
            cacheDuration: 30000 // 30 detik
        };
        
        // Performance tracking
        this.performance = {
            avgResponseTime: 0,
            totalChecks: 0,
            successfulChecks: 0,
            failedChecks: 0
        };
        
        // Init
        this.init();
    }
    
    init() {
        console.log('🔔 Starting optimized notification system');
        
        this.setupEventListeners();
        this.startAdaptivePolling();
        this.initialCheck();
    }
    
    setupEventListeners() {
        console.log('🔔 Setting up optimized event listeners');
        
        // Mark all as read
        $(document).on('click', '#markAllRead', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.markAllAsRead();
        });
        
        // Notification click dengan debounce
        let clickTimer;
        $(document).on('click', '.notification-item', (e) => {
            if ($(e.target).closest('.btn-close').length) return;
            
            clearTimeout(clickTimer);
            clickTimer = setTimeout(() => {
                const $item = $(e.currentTarget);
                const notificationId = $item.data('id');
                
                if (notificationId) {
                    this.markAsRead(notificationId);
                    this.highlightAndScrollToInformation(notificationId);
                }
            }, 100);
        });
        
        // Dropdown show event
        $('#notificationDropdown').on('show.bs.dropdown', () => {
            console.log('🔔 Dropdown opened - loading from cache');
            this.loadNotificationsFromCache();
            
            // Trigger immediate check dengan delay
            setTimeout(() => {
                this.smartCheck('dropdown_trigger');
            }, 300);
        });
        
        // Network status events
        window.addEventListener('online', () => {
            console.log('🌐 Network online - resuming polling');
            this.state.isOnline = true;
            this.state.consecutiveErrors = 0;
            this.startAdaptivePolling();
            setTimeout(() => this.smartCheck('network_online'), 1000);
        });
        
        window.addEventListener('offline', () => {
            console.log('🌐 Network offline - pausing polling');
            this.state.isOnline = false;
            this.stopPolling();
        });
        
        // Page visibility
        document.addEventListener('visibilitychange', () => {
            this.state.pageVisible = !document.hidden;
            
            if (this.state.pageVisible) {
                console.log('🔔 Page visible - resuming polling');
                this.startAdaptivePolling();
                
                // Trigger check after 1 second
                setTimeout(() => {
                    this.smartCheck('page_visible');
                }, 1000);
            } else {
                console.log('🔔 Page hidden - switching to background mode');
                this.switchToBackgroundMode();
            }
        });
        
        // Window focus/blur
        window.addEventListener('focus', () => {
            setTimeout(() => {
                this.smartCheck('window_focus');
            }, 500);
        });
        
        // Custom event for manual check
        $(document).on('forceCheckNotifications', () => {
            console.log('🔔 Manual force check triggered');
            this.smartCheck('manual_force');
        });
        
        // Listen to AJAX complete events
        $(document).ajaxComplete((event, xhr, settings) => {
            if (settings.url && (
                settings.url.includes('data_information.php') ||
                settings.url.includes('update_add_order.php')
            )) {
                console.log('📡 Data updated - scheduling check');
                setTimeout(() => {
                    this.smartCheck('data_updated');
                }, 2000);
            }
        });
    }
    
    startAdaptivePolling() {
        console.log('🔔 Starting adaptive polling');
        
        // Clear existing intervals
        this.stopPolling();
        
        // Calculate polling interval based on connection quality
        let interval = this.config.normalPollingInterval;
        
        if (this.state.connectionQuality === 'slow') {
            interval *= 1.5;
        } else if (this.state.connectionQuality === 'poor') {
            interval *= 2;
        }
        
        // Start polling
        this.pollingInterval = setInterval(() => {
            this.smartCheck('scheduled_poll');
        }, interval);
        
        // Deep check interval
        this.deepCheckInterval = setInterval(() => {
            this.checkNewNotifications(true, 'scheduled_deep');
        }, this.config.deepCheckInterval);
        
        console.log(`🔔 Polling started (${interval/1000}s interval, quality: ${this.state.connectionQuality})`);
    }
    
    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
        if (this.deepCheckInterval) {
            clearInterval(this.deepCheckInterval);
            this.deepCheckInterval = null;
        }
    }
    
    switchToBackgroundMode() {
        this.stopPolling();
        
        // Slower polling when in background
        this.pollingInterval = setInterval(() => {
            this.checkNewNotifications(false, 'background_poll');
        }, this.config.backgroundPollingInterval);
        
        console.log(`🔔 Background polling (${this.config.backgroundPollingInterval/1000}s)`);
    }
    
    smartCheck(trigger = 'unknown') {
        // Skip jika sedang aktif atau offline
        if (this.state.pollingActive || !this.state.isOnline) {
            console.log(`🔔 Skipping check (${trigger}): ${this.state.pollingActive ? 'polling active' : 'offline'}`);
            return;
        }
        
        // Throttling: minimal 2 detik antara checks
        const now = Date.now();
        if (now - this.state.lastCheckTime < 2000) {
            console.log(`🔔 Throttled check (${trigger}): too soon`);
            return;
        }
        
        // Skip jika terlalu banyak error
        if (this.state.consecutiveErrors >= this.config.maxConsecutiveTimeouts * 2) {
            console.warn(`🔔 Skipping due to too many errors: ${this.state.consecutiveErrors}`);
            return;
        }
        
        // Apply retry delay jika ada
        if (this.state.retryDelay > 0 && now - this.state.lastCheckTime < this.state.retryDelay) {
            return;
        }
        
        this.state.lastCheckTime = now;
        this.state.pollingActive = true;
        
        // Determine jika perlu deep check
        const needDeepCheck = (
            trigger === 'dropdown_trigger' ||
            trigger === 'manual_force' ||
            trigger === 'page_visible' ||
            (now - this.state.lastSuccessfulCheck > 300000) // 5 menit
        );
        
        console.log(`🔔 Smart check (${trigger}): ${needDeepCheck ? 'DEEP' : 'LIGHT'}`);
        
        this.checkNewNotifications(needDeepCheck, trigger);
    }
    
    checkNewNotifications(isDeepCheck = false, trigger = 'unknown') {
        const checkId = Math.random().toString(36).substr(2, 9);
        const startTime = performance.now();
        
        console.log(`🔔 [${checkId}] ${isDeepCheck ? 'Deep' : 'Light'} check started (${trigger})`);
        
        // Optimized URL dengan parameter minimal
        let url = 'api/check_new_info.php?';
        const params = new URLSearchParams();
        
        if (isDeepCheck) {
            params.append('deep', '1');
            params.append('_t', Date.now());
        }
        
        if (this.lastNotificationId) {
            params.append('last_id', this.lastNotificationId);
        }
        
        params.append('check_id', checkId);
        url += params.toString();
        
        // Gunakan fetch API dengan timeout yang lebih baik
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.config.connectionTimeout);
        
        fetch(url, {
            method: 'GET',
            signal: controller.signal,
            headers: {
                'Cache-Control': isDeepCheck ? 'no-cache' : 'max-age=30'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            return response.json();
        })
        .then(response => {
            const duration = performance.now() - startTime;
            this.recordPerformance(duration, true);
            
            console.log(`🔔 [${checkId}] Response in ${Math.round(duration)}ms:`, response.count);
            
            if (response.success) {
                this.handleSuccessfulResponse(response, duration);
            } else {
                this.handleErrorResponse(checkId, duration, response.message);
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            const duration = performance.now() - startTime;
            this.recordPerformance(duration, false);
            
            console.error(`🔔 [${checkId}] Error in ${Math.round(duration)}ms:`, error.name, error.message);
            
            this.handleNetworkError(checkId, error, duration);
        })
        .finally(() => {
            this.state.pollingActive = false;
            
            // Schedule next check dengan backoff jika perlu
            this.scheduleNextCheck();
        });
    }
    
    handleSuccessfulResponse(response, duration) {
        // Reset error counters
        this.state.consecutiveTimeouts = 0;
        this.state.consecutiveErrors = 0;
        this.state.retryDelay = 0;
        this.state.lastSuccessfulCheck = Date.now();
        
        // Update connection quality berdasarkan response time
        if (duration < 1000) {
            this.state.connectionQuality = 'good';
        } else if (duration < 3000) {
            this.state.connectionQuality = 'slow';
        } else {
            this.state.connectionQuality = 'poor';
        }
        
        // Track last notification ID
        if (response.last_id) {
            this.lastNotificationId = response.last_id;
        }
        
        const newCount = parseInt(response.count) || 0;
        const urgentCount = parseInt(response.urgent_count) || 0;
        
        // Update badge hanya jika count berubah
        if (newCount !== this.notificationCount) {
            this.notificationCount = newCount;
            this.updateBadge(newCount);
            
            // Cache the response
            this.cacheNotifications(response);
            
            // Update dropdown jika terbuka
            if ($('#notificationDropdown').hasClass('show')) {
                this.loadNotifications();
            }
            
            // Play sound jika ada notifikasi baru
            if (newCount > 0 && this.notificationCount < newCount) {
                this.playGentleNotificationSound();
            }
            
            // Show urgent indicator
            if (urgentCount > 0) {
                this.showUrgentNotificationIndicator(urgentCount);
            }
        }
    }
    
    handleNetworkError(checkId, error, duration) {
        this.state.consecutiveErrors++;
        
        if (error.name === 'AbortError') {
            this.state.consecutiveTimeouts++;
            console.warn(`🔔 [${checkId}] Timeout (consecutive: ${this.state.consecutiveTimeouts})`);
        }
        
        // Adjust connection quality
        if (this.state.connectionQuality === 'good') {
            this.state.connectionQuality = 'slow';
        } else if (this.state.connectionQuality === 'slow') {
            this.state.connectionQuality = 'poor';
        }
        
        // Jika terlalu banyak timeout, pause polling sementara
        if (this.state.consecutiveTimeouts >= this.config.maxConsecutiveTimeouts) {
            console.error('🔔 Too many timeouts - pausing polling for 30 seconds');
            this.stopPolling();
            
            setTimeout(() => {
                this.state.consecutiveTimeouts = 0;
                this.startAdaptivePolling();
                console.log('🔔 Polling resumed after timeout pause');
            }, 30000);
        }
    }
    
    scheduleNextCheck() {
        // Hitung backoff delay berdasarkan error count
        if (this.state.consecutiveErrors > 0) {
            const baseDelay = 5000; // 5 detik
            const backoffDelay = baseDelay * Math.pow(this.config.backoffMultiplier, this.state.consecutiveErrors - 1);
            this.state.retryDelay = Math.min(backoffDelay, 60000); // Max 60 detik
            
            console.log(`🔔 Backoff delay: ${this.state.retryDelay}ms (errors: ${this.state.consecutiveErrors})`);
            
            setTimeout(() => {
                this.smartCheck('backoff_retry');
            }, this.state.retryDelay);
        }
    }
    
    recordPerformance(duration, success) {
        this.performance.totalChecks++;
        
        if (success) {
            this.performance.successfulChecks++;
            // Update rolling average
            this.performance.avgResponseTime = 
                (this.performance.avgResponseTime * 0.7) + (duration * 0.3);
        } else {
            this.performance.failedChecks++;
        }
        
        // Log performance setiap 10 checks
        if (this.performance.totalChecks % 10 === 0) {
            console.log(`📊 Performance: ${this.performance.successfulChecks}/${this.performance.totalChecks} successful, ` +
                       `avg: ${Math.round(this.performance.avgResponseTime)}ms`);
        }
    }
    
    cacheNotifications(response) {
        this.cache.notifications = response;
        this.cache.lastCacheTime = Date.now();
    }
    
    loadNotificationsFromCache() {
        if (this.cache.notifications && 
            (Date.now() - this.cache.lastCacheTime) < this.cache.cacheDuration) {
            console.log('🔔 Loading notifications from cache');
            this.renderNotifications(
                this.cache.notifications.notifications || [],
                this.cache.notifications.unread_count || 0
            );
            return true;
        }
        return false;
    }
    
    initialCheck() {
        // Delay initial check untuk hindari race condition
        setTimeout(() => {
            this.smartCheck('initial');
        }, 3000);
    }
    
    loadNotifications() {
        // Coba load dari cache dulu
        if (this.loadNotificationsFromCache()) {
            return;
        }
        
        console.log('🔔 Loading notifications from server');
        
        // Gunakan fetch dengan timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 3000);
        
        fetch('api/get_notifications.php?_t=' + Date.now(), {
            method: 'GET',
            signal: controller.signal,
            headers: {
                'Cache-Control': 'no-cache'
            }
        })
        .then(response => {
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            return response.json();
        })
        .then(response => {
            if (response.success) {
                this.cacheNotifications(response);
                this.renderNotifications(response.notifications, response.unread_count);
                this.updateBadge(response.unread_count);
            } else {
                this.showEmptyState('Failed to load notifications');
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            console.error('🔔 Error loading notifications:', error);
            this.showEmptyState('Network error - please try again');
        });
    }
    
    // ... (method renderNotifications, markAsRead, dll tetap sama seperti sebelumnya)
    // ... (tapi dengan error handling yang lebih baik)
    
    playGentleNotificationSound() {
        // Gunakan Web Audio API yang lebih ringan
        try {
            if (window.AudioContext || window.webkitAudioContext) {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                
                // Create a simple, gentle sound
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 800;
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.05, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.3);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            }
        } catch (e) {
            console.log('🔔 Audio not supported:', e.message);
        }
    }
    
    showUrgentNotificationIndicator(count) {
        const $bell = $('.notification-bell');
        
        // Hanya animasi jika belum dianimasikan baru-baru ini
        if (!$bell.data('animating')) {
            $bell.data('animating', true);
            $bell.addClass('animate__animated animate__tada');
            
            setTimeout(() => {
                $bell.removeClass('animate__animated animate__tada');
                $bell.data('animating', false);
            }, 2000);
        }
    }
    
    updateBadge(count) {
        const $badge = $('#notificationBadge');
        const $infoBadge = $('#info-badge');
        
        if (count > 0) {
            const displayCount = count > 99 ? '99+' : count;
            $badge.text(displayCount).show().addClass('bg-danger');
            $infoBadge.text(displayCount).show().addClass('bg-danger');
            
            // Update page title dengan debounce
            clearTimeout(this.titleUpdateTimer);
            this.titleUpdateTimer = setTimeout(() => {
                document.title = `(${displayCount}) Progress BO Control`;
            }, 100);
        } else {
            $badge.hide().removeClass('animate__animated animate__pulse');
            $infoBadge.hide();
            
            // Reset title
            clearTimeout(this.titleUpdateTimer);
            document.title = "Progress BO Control";
        }
    }
    
    destroy() {
        this.stopPolling();
        clearTimeout(this.titleUpdateTimer);
        $(document).off('click', '#markAllRead');
        $(document).off('click', '.notification-item');
        $(document).off('show.bs.dropdown', '#notificationDropdown');
        $(document).off('forceCheckNotifications');
        console.log('🔔 NotificationSystem destroyed');
    }
}

// Initialize with protection
function initializeNotificationSystem() {
    if (!window.notificationSystem) {
        try {
            // Wait for jQuery to be ready
            if (typeof $ === 'undefined') {
                console.warn('🔔 jQuery not loaded yet, delaying notification system');
                setTimeout(initializeNotificationSystem, 1000);
                return;
            }
            
            window.notificationSystem = new NotificationSystem();
            
            // Global functions
            window.forceCheckNotifications = () => {
                if (window.notificationSystem) {
                    window.notificationSystem.smartCheck('global_force');
                }
            };
            
            window.markNotificationAsRead = (id) => {
                if (window.notificationSystem) {
                    window.notificationSystem.markAsRead(id);
                }
            };
            
            console.log('✅ NotificationSystem initialized successfully');
        } catch (error) {
            console.error('❌ Failed to initialize NotificationSystem:', error);
            
            // Fallback: try again in 5 seconds
            setTimeout(initializeNotificationSystem, 5000);
        }
    }
}

// Start after page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeNotificationSystem);
} else {
    initializeNotificationSystem();
}