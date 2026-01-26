<!-- Modal for Add/Edit D/S -->
<div class="modal fade" id="modal-add-ds" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-primary text-white">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-sun-fill me-2"></i>
          DAY SHIFT ADD ORDER
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <!-- Debug info (hidden) -->
      <div class="d-none" id="ds-debug-info"></div>
      <form id="form-add-ds" method="post">
        <input type="hidden" id="add-ds-date" name="date">
        <input type="hidden" id="add-ds-supplier" name="supplier_code">
        <input type="hidden" id="add-ds-partno" name="part_no">
        <input type="hidden" name="type" value="ds">
        <input type="hidden" name="action" value="add" id="ds-action">
        
        <div class="modal-body p-4">
          <!-- Current Info Card -->
          <div class="card mb-4 border-primary shadow-sm">
            <div class="card-body p-3">
              <div class="row small">
                <div class="col-6">
                  <div class="mb-2">
                    <strong class="text-primary">Date:</strong>
                    <div id="txt-ds-date" class="text-dark fw-bold"></div>
                  </div>
                  <div class="mb-2">
                    <strong class="text-primary">Supplier:</strong>
                    <div id="txt-ds-supplier" class="text-dark fw-bold"></div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="mb-2">
                    <strong class="text-primary">Part No:</strong>
                    <div id="txt-ds-partno" class="text-dark fw-bold"></div>
                  </div>
                  <div class="mb-2">
                    <strong class="text-primary">Part Name:</strong>
                    <div id="txt-ds-partname" class="text-dark fw-bold"></div>
                  </div>
                </div>
              </div>
              <div class="row mt-2">
                <div class="col-12">
                  <small class="text-muted">
                    <i class="bi bi-clock-history me-1"></i>
                    Current time: <span id="current-time-display"></span>
                  </small>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Current Status -->
          <div class="alert alert-primary bg-primary-soft border-primary" id="ds-current-status">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <i class="bi bi-info-circle me-2"></i>
                <span id="ds-status-text">Belum ada add order</span>
              </div>
              <button type="button" class="btn btn-sm btn-outline-danger" id="btn-reset-ds" style="display: none;">
                <i class="bi bi-x-circle"></i> Reset All
              </button>
            </div>
          </div>
          
          <!-- Pilih Jam D/S (07:00 - 20:00) -->
          <div class="mb-4">
            <label class="form-label fw-semibold text-primary">
              <i class="bi bi-clock-fill me-1"></i>
              Pilih Jam Add Order <span class="text-danger">*</span>
            </label>
            <div class="row g-2" id="ds-hour-selection">
              <!-- Jam akan diisi oleh JavaScript -->
            </div>
            <div class="form-text text-primary">
              <small>
                <i class="bi bi-exclamation-triangle me-1"></i>
                Hanya bisa memilih jam yang belum lewat (tidak mundur)
              </small>
            </div>
          </div>
          
          <!-- Quantity Input per Jam -->
          <div class="mb-4">
            <label class="form-label fw-semibold text-primary">
              <i class="bi bi-plus-circle-fill me-1"></i>
              Quantity per Jam <span class="text-danger">*</span>
            </label>
            <div id="ds-quantity-container">
              <!-- Quantity per jam akan diisi secara dinamis -->
              <div class="alert alert-info" id="ds-no-hour-selected">
                <i class="bi bi-info-circle me-2"></i>
                Pilih jam terlebih dahulu di atas
              </div>
            </div>
          </div>
          
          <!-- Total Quantity -->
          <div class="card mb-4 border-success">
            <div class="card-body py-2">
              <div class="d-flex justify-content-between align-items-center">
                <strong class="text-success">Total Add Order:</strong>
                <span class="badge bg-success fs-5" id="ds-total-qty">0</span>
              </div>
            </div>
          </div>
          
          <!-- Reason Input -->
          <div class="mb-4">
            <label class="form-label fw-semibold text-primary">
              <i class="bi bi-chat-left-text-fill me-1"></i>
              Reason / Remark <span class="text-danger">*</span>
            </label>
            <textarea id="txt-ds-remark" name="remark" 
                      class="form-control border-primary" rows="3" 
                      placeholder="Enter reason for add order..."
                      required></textarea>
          </div>
          
          <!-- Error/Success Alert -->
          <div class="alert alert-danger d-none" id="ds-error-alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <span id="ds-error-message"></span>
          </div>
          
          <div class="alert alert-success d-none" id="ds-success-alert">
            <i class="bi bi-check-circle me-2"></i>
            <span id="ds-success-message"></span>
          </div>
        </div>
        
        <div class="modal-footer bg-light">
          <div class="me-auto">
            <img src="./assets/img/logo-denso.png" alt="DENSO" width="90" height="40">
          </div>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Cancel
          </button>
          <button type="submit" class="btn btn-primary px-4" id="ds-submit-btn">
            <i class="bi bi-check-circle me-2"></i>
            <span>Save Changes</span>
            <span class="spinner-border spinner-border-sm d-none ms-2" id="ds-spinner"></span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal for Add/Edit N/S -->
<div class="modal fade" id="modal-add-ns" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-warning text-white">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-moon-stars-fill me-2"></i>
          NIGHT SHIFT ADD ORDER
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Debug info (hidden) -->
      <div class="d-none" id="ds-debug-info"></div>
      <form id="form-add-ns" method="post">
        <input type="hidden" id="add-ns-date" name="date">
        <input type="hidden" id="add-ns-supplier" name="supplier_code">
        <input type="hidden" id="add-ns-partno" name="part_no">
        <input type="hidden" name="type" value="ns">
        <input type="hidden" name="action" value="add" id="ns-action">
        
        <div class="modal-body p-4">
          <!-- Current Info Card -->
          <div class="card mb-4 border-warning shadow-sm">
            <div class="card-body p-3">
              <div class="row small">
                <div class="col-6">
                  <div class="mb-2">
                    <strong class="text-warning">Date:</strong>
                    <div id="txt-ns-date" class="text-dark fw-bold"></div>
                  </div>
                  <div class="mb-2">
                    <strong class="text-warning">Supplier:</strong>
                    <div id="txt-ns-supplier" class="text-dark fw-bold"></div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="mb-2">
                    <strong class="text-warning">Part No:</strong>
                    <div id="txt-ns-partno" class="text-dark fw-bold"></div>
                  </div>
                  <div class="mb-2">
                    <strong class="text-warning">Part Name:</strong>
                    <div id="txt-ns-partname" class="text-dark fw-bold"></div>
                  </div>
                </div>
              </div>
              <div class="row mt-2">
                <div class="col-12">
                  <small class="text-muted">
                    <i class="bi bi-clock-history me-1"></i>
                    Current time: <span id="ns-current-time-display"></span>
                  </small>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Current Status -->
          <div class="alert alert-warning bg-warning-soft border-warning" id="ns-current-status">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <i class="bi bi-info-circle me-2"></i>
                <span id="ns-status-text">Belum ada add order</span>
              </div>
              <button type="button" class="btn btn-sm btn-outline-danger" id="btn-reset-ns" style="display: none;">
                <i class="bi bi-x-circle"></i> Reset All
              </button>
            </div>
          </div>
          
          <!-- Pilih Jam N/S (21:00 - 06:00) -->
          <div class="mb-4">
            <label class="form-label fw-semibold text-warning">
              <i class="bi bi-clock-fill me-1"></i>
              Pilih Jam Add Order <span class="text-danger">*</span>
            </label>
            <div class="row g-2" id="ns-hour-selection">
              <!-- Jam akan diisi oleh JavaScript -->
            </div>
            <div class="form-text text-warning">
              <small>
                <i class="bi bi-exclamation-triangle me-1"></i>
                Hanya bisa memilih jam yang belum lewat (tidak mundur)
              </small>
            </div>
          </div>
          
          <!-- Quantity Input per Jam -->
          <div class="mb-4">
            <label class="form-label fw-semibold text-warning">
              <i class="bi bi-plus-circle-fill me-1"></i>
              Quantity per Jam <span class="text-danger">*</span>
            </label>
            <div id="ns-quantity-container">
              <!-- Quantity per jam akan diisi secara dinamis -->
              <div class="alert alert-info" id="ns-no-hour-selected">
                <i class="bi bi-info-circle me-2"></i>
                Pilih jam terlebih dahulu di atas
              </div>
            </div>
          </div>
          
          <!-- Total Quantity -->
          <div class="card mb-4 border-warning">
            <div class="card-body py-2">
              <div class="d-flex justify-content-between align-items-center">
                <strong class="text-warning">Total Add Order:</strong>
                <span class="badge bg-warning fs-5" id="ns-total-qty">0</span>
              </div>
            </div>
          </div>
          
          <!-- Reason Input -->
          <div class="mb-4">
            <label class="form-label fw-semibold text-warning">
              <i class="bi bi-chat-left-text-fill me-1"></i>
              Reason / Remark <span class="text-danger">*</span>
            </label>
            <textarea id="txt-ns-remark" name="remark" 
                      class="form-control border-warning" rows="3" 
                      placeholder="Enter reason for add order..."
                      required></textarea>
          </div>
          
          <!-- Error/Success Alert -->
          <div class="alert alert-danger d-none" id="ns-error-alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <span id="ns-error-message"></span>
          </div>
          
          <div class="alert alert-success d-none" id="ns-success-alert">
            <i class="bi bi-check-circle me-2"></i>
            <span id="ns-success-message"></span>
          </div>
        </div>
        
        <div class="modal-footer bg-light">
          <div class="me-auto">
            <img src="./assets/img/logo-denso.png" alt="DENSO" width="90" height="40">
          </div>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Cancel
          </button>
          <button type="submit" class="btn btn-warning px-4" id="ns-submit-btn">
            <i class="bi bi-check-circle me-2"></i>
            <span>Save Changes</span>
            <span class="spinner-border spinner-border-sm d-none ms-2" id="ns-spinner"></span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Hidden fields for hour selection -->
<input type="hidden" id="selected-ds-hours" name="selected_hours" value="">
<input type="hidden" id="selected-ns-hours" name="selected_hours" value="">

<style>
/* Modal Styles */
.bg-gradient-primary {
  background: linear-gradient(135deg, #0066cc 0%, #0047ab 100%) !important;
}

.bg-gradient-warning {
  background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
}

.bg-primary-soft {
  background-color: rgba(13, 110, 253, 0.1) !important;
}

.bg-warning-soft {
  background-color: rgba(255, 193, 7, 0.1) !important;
}

/* Hour Selection Styling */
.hour-btn {
  width: 60px;
  height: 40px;
  margin: 2px;
  transition: all 0.2s ease;
}

.hour-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.hour-btn.selected {
  transform: scale(1.05);
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.3);
}

.hour-btn.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Quantity Input Styling */
.quantity-input-group {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 10px;
  margin-bottom: 8px;
  background: #f8f9fa;
  transition: all 0.3s ease;
}

.quantity-input-group:hover {
  background: #fff;
  border-color: #0066cc;
}

.quantity-input-group label {
  font-size: 0.875rem;
  color: #495057;
  font-weight: 500;
}

.quantity-input-group .form-control {
  font-weight: bold;
  font-size: 1.1rem;
  text-align: center;
}

/* Custom colors */
.text-primary {
  color: #0066cc !important;
}

.text-warning {
  color: #ffc107 !important;
}

.border-primary {
  border-color: #0066cc !important;
}

.border-warning {
  border-color: #ffc107 !important;
}

/* Responsive */
@media (max-width: 768px) {
  .hour-btn {
    width: 50px;
    height: 35px;
    font-size: 0.8rem;
  }
}
</style>