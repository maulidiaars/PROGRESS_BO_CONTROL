<!-- Tambahkan di bagian atas navigation.php -->
<?php if (isset($_SESSION['force_password_reset']) && $_SESSION['force_password_reset'] === true): ?>
<style>
    /* Disable all navigation when password reset required */
    .header-nav, .toggle-sidebar-btn, .search-bar-toggle {
        pointer-events: none;
        opacity: 0.5;
    }
</style>
<?php endif; ?>

<header id="header" class="header fixed-top d-flex align-items-center" style="background: linear-gradient(135deg, #000000 0%, #1a1a2e 100%); border-bottom: 2px solid #0066cc;">
  <div class="d-flex align-items-center">
    <a href="index.php" class="logo d-flex align-items-center" style="height: 100%;">
      <img src="./assets/img/logo-denso.png" alt="DENSO Logo" style="height: 40px;">
      <span class="d-none d-lg-block ms-2" style="color: white; font-size: 18px; font-weight: 600;">
        VISUALIZATION BO CONTROL DAILY
      </span>
    </a>
  </div>

  <!-- ============ RIGHT NAVIGATION ============ -->
  <nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">
      
      <!-- Notifications -->
      <li class="nav-item dropdown me-2">
        <?php include 'components/notification-bell.php'; ?>
      </li>
      
      <!-- Upload Button -->
      <li class="nav-item me-2">
        <button type="button" id="btn-upload" class="btn btn-primary btn-sm upload-btn" style="font-size: 13px; font-weight: 600; padding: 6px 12px;">
          <i class="bi bi-cloud-upload me-1"></i> Upload
        </button>
      </li>
      
      <!-- Date Range Container -->
      <li class="nav-item dropdown me-3 date-range-container">
        <div class="d-flex align-items-center gap-2">
          <div class="date-input-group">
            <small class="date-label">Start Date</small>
            <input type="text" id="range-date1" class="form-control form-control-sm datepicker" placeholder="YYYY-MM-DD" style="width: 110px; font-size: 13px;">
          </div>
          <i class="bi bi-arrow-right text-white" style="font-size: 12px;"></i>
          <div class="date-input-group">
            <small class="date-label">End Date</small>
            <input type="text" id="range-date2" class="form-control form-control-sm datepicker" placeholder="YYYY-MM-DD" style="width: 110px; font-size: 13px;">
          </div>
        </div>
      </li>
      
      <!-- Profile Dropdown -->
      <li class="nav-item dropdown pe-2">
        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
          <div class="profile-avatar">
            <img src="assets/img/img-profile.png" alt="Profile" class="rounded-circle" style="width: 36px; height: 36px; border: 2px solid #0066cc;">
          </div>
          <div class="profile-info ms-2 d-none d-md-block">
            <span class="profile-name" style="color: white; font-size: 14px; font-weight: 500;"><?php echo htmlspecialchars($_SESSION["name"]) ?></span>
            <small class="profile-role d-block" style="color: rgba(255, 255, 255, 0.7); font-size: 11px;">Material Control</small>
          </div>
          <i class="bi bi-caret-down-fill text-white ms-1" style="font-size: 12px;"></i>
        </a>
        
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
          <li class="dropdown-header">
            <h6 style="color: #333;"><?php echo htmlspecialchars($_SESSION["name"]) ?></h6>
            <span style="color: #666;">Material Control Department</span>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="auth/logout.php">
              <i class="bi bi-box-arrow-right me-2 text-danger"></i>
              <span>Logout</span>
            </a>
          </li>
        </ul>
      </li>
      
    </ul>
  </nav>
</header>