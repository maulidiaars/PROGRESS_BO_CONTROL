<?php
session_start();
session_write_close();
if (!isset($_SESSION["user"])) {
    header("Location: views/login.php");
    exit();
}

$currentUser = $_SESSION['name'] ?? 'Unknown';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'components/header.php'; ?>
</head>

<body class="toggle-sidebar">
  <div id="overlay" style="display: none;"><div class="loader"></div></div>
  
  <!-- ======= Blocker Dashboard ======= -->
  <?php if (isset($_SESSION['force_password_reset']) && $_SESSION['force_password_reset'] === true): ?>
  <div id="dashboardBlocker" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9998; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
      <div class="text-center text-white p-4" style="max-width: 500px;">
          <i class="bi bi-shield-lock-fill" style="font-size: 4rem; color: #ffc107;"></i>
          <h3 class="mt-3 fw-bold">RESET PASSWORD REQUIRED</h3>
          <p class="mb-4">You must reset your password before accessing the dashboard</p>
      </div>
  </div>
  <?php endif; ?>
  
  <!-- ======= Header ======= -->
  <?php include 'components/navigation.php'; ?>

  <main id="main" class="main" style="padding-bottom: 8%; background: linear-gradient(135deg, #384757ff 0%, #003db6ff 100%);">
    
    <!-- Dashboard Title Card -->
    <?php include 'components/dashboard-title.php'; ?>
    
    <!-- Filter Section -->
    <?php include 'components/filters.php'; ?>
    
    <!-- Charts Section -->
    <?php include 'components/charts-section.php'; ?>
    
    <!-- Progress Table Section -->
    <?php include 'components/progress-table.php'; ?>
    
    <!-- Information Section -->
    <?php include 'components/information-section.php'; ?>
    
    <!-- Modal for Add D/S & N/S -->
    <?php include 'components/modals-add-orders.php'; ?>
    
    <!-- All Other Modals -->
    <?php include 'components/modals.php'; ?>
    <?php include 'components/modals-detail.php'; ?>
    <?php include 'components/modals-information.php'; ?>
    
  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <?php include 'components/footer.php'; ?>

  <!-- ======= Password Reset Modal ======= -->
  <?php include 'components/password-reset-modal.php'; ?>

  <!-- Vendor JS Files & Scripts -->
  <?php include 'components/scripts.php'; ?>
  
  <!-- Inline Script untuk Reset Password -->
  <?php if (isset($_SESSION['force_password_reset']) && $_SESSION['force_password_reset'] === true): ?>
  <script>
  // Function untuk show modal (bisa dipanggil dari onclick)
  function showPasswordResetModal() {
      console.log('Showing password reset modal...');
      
      // Pastikan jQuery sudah loaded
      if (typeof $ === 'undefined') {
          console.error('jQuery not loaded!');
          return;
      }
      
      // Show modal dengan options
      $('#passwordResetModal').modal({
          backdrop: 'static',
          keyboard: false
      });
      
      // Hide blocker
      $('#dashboardBlocker').fadeOut(300);
      
      // Block dashboard
      $('#main, #footer, #header').css({
          'filter': 'blur(5px)',
          'pointer-events': 'none',
          'opacity': '0.7'
      });
  }
  
  // Auto show modal setelah page load
  $(document).ready(function() {
      console.log('Page loaded, checking for password reset...');
      
      // Tunggu 1 detik biar semua element siap
      setTimeout(function() {
          if (typeof forcePasswordReset !== 'undefined' && forcePasswordReset) {
              console.log('Auto-showing password reset modal');
              showPasswordResetModal();
          }
      }, 5000);
  });
  </script>
  <?php endif; ?>

  <script>
$(document).ready(function() {
    // Force set today's date on page load
    function forceTodayDate() {
        const now = new Date();
        const today = now.toLocaleDateString('en-CA'); // YYYY-MM-DD format
        
        console.log('📅 Force setting date to:', today, 'Current hour:', now.getHours());
        
        $('#range-date1').val(today);
        $('#range-date2').val(today);
        
        // Update global variables
        if (typeof rangeDate1 !== 'undefined') rangeDate1 = today;
        if (typeof rangeDate2 !== 'undefined') rangeDate2 = today;
        
        // Trigger change to reload data
        setTimeout(() => {
            if (typeof handleDateChange === 'function') {
                handleDateChange();
            }
        }, 100);
    }
    
    // Wait 2 seconds then force set date
    setTimeout(forceTodayDate, 2000);
});
</script>

<!-- Notification Auto-init Script -->
<script>
// Force initialize notification system immediately
$(document).ready(function() {
    console.log('🎯 Main page loaded, ensuring notification system...');
    
    // Double-check initialization setelah 2 detik
    setTimeout(() => {
        if (!window.notificationSystem) {
            console.log('⚠️ NotificationSystem not found, creating...');
            window.notificationSystem = new NotificationSystem();
        } else {
            console.log('✅ NotificationSystem already initialized');
            // Force check sekarang juga
            window.notificationSystem.forceCheck();
        }
    }, 2000);
    
    // Check setiap 5 detik untuk memastikan polling aktif
    setInterval(() => {
        if (window.notificationSystem && !window.notificationSystem.pollingActive) {
            console.log('🔄 Polling seems inactive, forcing check...');
            window.notificationSystem.forceCheck();
        }
    }, 5000);
});
</script>

</body>
</html>
