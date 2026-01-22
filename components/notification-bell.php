<!-- components/notification-bell.php -->
<li class="nav-item dropdown me-2">
    <a class="nav-link notification-bell position-relative" href="#" id="notificationDropdown" 
       role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" 
              id="notificationBadge" style="display: none;">
            0
        </span>
    </a>
    
    <ul class="dropdown-menu dropdown-menu-end dropdown-notifications" 
        aria-labelledby="notificationDropdown" style="min-width: 420px; max-height: 550px; overflow-y: auto;">
        <li class="dropdown-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">NOTIFICATIONS</h6>
                <button class="btn btn-sm btn-outline-secondary" id="markAllRead" style="
                    background: rgba(255,255,255,0.2);
                    border: 1px solid rgba(255,255,255,0.3);
                    color: white;
                    border-radius: 8px;
                    padding: 4px 12px;
                    font-size: 0.8rem;
                    transition: all 0.3s;
                ">
                    <i class="bi bi-check2-all me-1"></i> Mark All Read
                </button>
            </div>
        </li>
        
        <li><hr class="dropdown-divider my-2" style="border-color: rgba(255,255,255,0.1);"></li>
        
        <!-- CONTAINER UNTUK NOTIFIKASI -->
        <div id="notificationContainer" style="padding: 10px 20px;">
            <div class="empty-notifications">
                <i class="bi bi-bell-slash"></i>
                <p class="mb-1">No notifications</p>
                <small>Everything is up to date</small>
            </div>
        </div>
        
        <li><hr class="dropdown-divider my-2" style="border-color: rgba(255,255,255,0.1);"></li>
    
    </ul>
</li>