<!-- Modal for Add/Edit D/S -->
<div class="modal fade" id="modal-add-ds" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-primary text-white">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-sun-fill me-2"></i>
          DAY SHIFT ADD ORDER
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
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
            </div>
          </div>
          
          <!-- Current Status -->
      <!-- Di dalam modal D/S, update bagian Current Status: -->
      <div class="alert alert-primary bg-primary-soft border-primary" id="ds-current-status">
          <div class="d-flex justify-content-between align-items-center">
              <div>
                  <i class="bi bi-info-circle me-2"></i>
                  <span id="ds-status-text">Belum ada add order</span>
              </div>
              <button type="button" class="btn btn-sm btn-outline-danger" id="btn-reset-ds" style="display: none;">
                  <i class="bi bi-x-circle"></i> Reset
              </button>
          </div>
      </div>
          
          <!-- Quantity Input -->
          <div class="mb-4">
            <label class="form-label fw-semibold text-primary">
              <i class="bi bi-plus-circle-fill me-1"></i>
              Add Quantity <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <button type="button" class="btn btn-outline-primary" id="ds-decrease">
                <i class="bi bi-dash"></i>
              </button>
              <input type="number" id="txt-ds-addqty" name="add_qty" 
                     class="form-control text-center border-primary" 
                     min="0" max="99999" 
                     value="0" required>
              <button type="button" class="btn btn-outline-primary" id="ds-increase">
                <i class="bi bi-plus"></i>
              </button>
              <span class="input-group-text bg-primary text-white border-primary">pcs</span>
            </div>
            <div class="mt-2">
              <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-primary ds-quick-btn" data-value="10">+10</button>
                <button type="button" class="btn btn-primary ds-quick-btn" data-value="50">+50</button>
                <button type="button" class="btn btn-primary ds-quick-btn" data-value="100">+100</button>
                <button type="button" class="btn btn-primary ds-quick-btn" data-value="500">+500</button>
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
            <div class="form-text text-primary">
              <small>
                <i class="bi bi-lightbulb me-1"></i>
                Example: "Extra demand from production", "Safety stock addition", etc.
              </small>
            </div>
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

<!-- Modal for Add/Edit N/S - VERSI LENGKAP -->
<div class="modal fade" id="modal-add-ns" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-primary text-white">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-moon-stars-fill me-2"></i>
          NIGHT SHIFT ADD ORDER
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <form id="form-add-ns" method="post">
        <input type="hidden" id="add-ns-date" name="date">
        <input type="hidden" id="add-ns-supplier" name="supplier_code">
        <input type="hidden" id="add-ns-partno" name="part_no">
        <input type="hidden" name="type" value="ns">
        <input type="hidden" name="action" value="add" id="ns-action">
        
        <div class="modal-body p-4">
          <!-- Current Info Card -->
          <div class="card mb-4 border-primary shadow-sm">
            <div class="card-body p-3">
              <div class="row small">
                <div class="col-6">
                  <div class="mb-2">
                    <strong class="text-primary">Date:</strong>
                    <div id="txt-ns-date" class="text-dark fw-bold"></div>
                  </div>
                  <div class="mb-2">
                    <strong class="text-primary">Supplier:</strong>
                    <div id="txt-ns-supplier" class="text-dark fw-bold"></div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="mb-2">
                    <strong class="text-primary">Part No:</strong>
                    <div id="txt-ns-partno" class="text-dark fw-bold"></div>
                  </div>
                  <div class="mb-2">
                    <strong class="text-primary">Part Name:</strong>
                    <div id="txt-ns-partname" class="text-dark fw-bold"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Current Status -->
      <div class="alert alert-primary bg-primary-soft border-primary" id="ns-current-status">
          <div class="d-flex justify-content-between align-items-center">
              <div>
                  <i class="bi bi-info-circle me-2"></i>
                  <span id="ns-status-text">Belurn ada add order</span>
              </div>
              <button type="button" class="btn btn-sm btn-outline-danger" id="btn-reset-ns" style="display: none;">
                  <i class="bi bi-x-circle"></i> Reset
              </button>
          </div>
      </div>
          
          <!-- Quantity Input -->
          <div class="mb-4">
            <label class="form-label fw-semibold text-primary">
              <i class="bi bi-plus-circle-fill me-1"></i>
              Add Quantity <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <button type="button" class="btn btn-outline-primary" id="ns-decrease">
                <i class="bi bi-dash"></i>
              </button>
              <input type="number" id="txt-ns-addqty" name="add_qty" 
                     class="form-control text-center border-primary" 
                     min="0" max="99999" 
                     value="0" required>
              <button type="button" class="btn btn-outline-primary" id="ns-increase">
                <i class="bi bi-plus"></i>
              </button>
              <span class="input-group-text bg-primary text-white border-primary">pcs</span>
            </div>
            <div class="mt-2">
              <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-primary ns-quick-btn" data-value="10">+10</button>
                <button type="button" class="btn btn-primary ns-quick-btn" data-value="50">+50</button>
                <button type="button" class="btn btn-primary ns-quick-btn" data-value="100">+100</button>
                <button type="button" class="btn btn-primary ns-quick-btn" data-value="500">+500</button>
              </div>
            </div>
          </div>
          
          <!-- Reason Input -->
          <div class="mb-4">
            <label class="form-label fw-semibold text-primary">
              <i class="bi bi-chat-left-text-fill me-1"></i>
              Reason / Remark <span class="text-danger">*</span>
            </label>
            <textarea id="txt-ns-remark" name="remark" 
                      class="form-control border-primary" rows="3" 
                      placeholder="Enter reason for add order..."
                      required></textarea>
            <div class="form-text text-primary">
              <small>
                <i class="bi bi-lightbulb me-1"></i>
                Example: "Extra demand from production", "Safety stock addition", etc.
              </small>
            </div>
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
          <button type="submit" class="btn btn-primary px-4" id="ns-submit-btn">
            <i class="bi bi-check-circle me-2"></i>
            <span>Save Changes</span>
            <span class="spinner-border spinner-border-sm d-none ms-2" id="ns-spinner"></span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
/* Modal Styles */
.bg-gradient-primary {
  background: linear-gradient(135deg, #0066cc 0%, #0047ab 100%) !important;
}

.bg-primary-soft {
  background-color: rgba(13, 110, 253, 0.1) !important;
}

/* Text Colors */
.text-primary {
  color: #0066cc !important;
}

/* Card Styles */
.card.border-primary {
  border-width: 2px !important;
  border-color: #0066cc !important;
}

/* Button Styles */
.btn-primary {
  background-color: #0066cc !important;
  border-color: #0066cc !important;
}

.btn-primary:hover {
  background-color: #0056b3 !important;
  border-color: #0056b3 !important;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 102, 204, 0.3);
}

.btn-outline-primary {
  color: #0066cc !important;
  border-color: #0066cc !important;
}

.btn-outline-primary:hover {
  background-color: #0066cc !important;
  color: white !important;
}

/* Alert Customization */
.alert-primary {
  background-color: rgba(13, 110, 253, 0.1) !important;
  border-color: rgba(13, 110, 253, 0.2) !important;
  color: #0066cc !important;
}

/* Input Borders */
.border-primary {
  border-color: #0066cc !important;
}

/* Input Group Customization */
.input-group .btn-outline-primary {
  width: 45px;
}

.input-group .form-control {
  font-weight: bold;
  font-size: 1.1rem;
}

.input-group-text.bg-primary {
  background-color: #0066cc !important;
  border-color: #0066cc !important;
}

/* Quick Buttons */
.btn-group-sm .btn {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
}

/* Current Status Badge */
.current-qty-badge {
  font-size: 0.9em;
  padding: 4px 10px;
  border-radius: 12px;
  font-weight: bold;
  background-color: #0066cc;
  color: white;
}

/* Loading Spinner */
.btn .spinner-border {
  vertical-align: middle;
}

/* Modal Animation */
.modal.fade .modal-dialog {
  transform: translateY(-50px);
  transition: transform 0.3s ease-out;
}

.modal.show .modal-dialog {
  transform: translateY(0);
}

/* Reset Button */
.btn-outline-danger {
  color: #dc3545;
  border-color: #dc3545;
}

.btn-outline-danger:hover {
  background-color: #dc3545;
  color: white;
}

/* Form Labels */
.form-label.text-primary {
  font-weight: 600;
}

/* Form Text */
.form-text.text-primary {
  opacity: 0.8;
}

/* Responsive Adjustments */
@media (max-width: 576px) {
  .modal-dialog {
    margin: 0.5rem;
  }
  
  .btn-group-sm {
    display: flex;
    flex-wrap: wrap;
  }
  
  .btn-group-sm .btn {
    flex: 1;
    min-width: 60px;
    margin-bottom: 0.25rem;
  }
}
</style>

<script>
// Drag & Drop functionality for future enhancement
$(document).ready(function() {
  // DS Quantity controls
  $('#ds-increase').on('click', function() {
    const $input = $('#txt-ds-addqty');
    let val = parseInt($input.val()) || 0;
    $input.val(val + 1);
  });
  
  $('#ds-decrease').on('click', function() {
    const $input = $('#txt-ds-addqty');
    let val = parseInt($input.val()) || 0;
    if (val > 0) $input.val(val - 1);
  });
  
  $('.ds-quick-btn').on('click', function() {
    const $input = $('#txt-ds-addqty');
    const addValue = parseInt($(this).data('value'));
    let currentVal = parseInt($input.val()) || 0;
    $input.val(currentVal + addValue);
  });
  
  // NS Quantity controls
  $('#ns-increase').on('click', function() {
    const $input = $('#txt-ns-addqty');
    let val = parseInt($input.val()) || 0;
    $input.val(val + 1);
  });
  
  $('#ns-decrease').on('click', function() {
    const $input = $('#txt-ns-addqty');
    let val = parseInt($input.val()) || 0;
    if (val > 0) $input.val(val - 1);
  });
  
  $('.ns-quick-btn').on('click', function() {
    const $input = $('#txt-ns-addqty');
    const addValue = parseInt($(this).data('value'));
    let currentVal = parseInt($input.val()) || 0;
    $input.val(currentVal + addValue);
  });
  
  // Reset buttons
  $('#btn-reset-ds').on('click', function() {
    if (confirm('Are you sure you want to reset DS add order to 0?\nThis action cannot be undone.')) {
      $('#txt-ds-addqty').val(0);
      $('#txt-ds-remark').val('');
      $('#ds-action').val('delete');
      $('#ds-submit-btn').click();
    }
  });
  
  $('#btn-reset-ns').on('click', function() {
    if (confirm('Are you sure you want to reset NS add order to 0?\nThis action cannot be undone.')) {
      $('#txt-ns-addqty').val(0);
      $('#txt-ns-remark').val('');
      $('#ns-action').val('delete');
      $('#ns-submit-btn').click();
    }
  });
  
  // Modal close reset
  $('#modal-add-ds, #modal-add-ns').on('hidden.bs.modal', function() {
    // Reset form state
    $(this).find('input[type="number"]').val(0);
    $(this).find('textarea').val('');
    $(this).find('.alert').addClass('d-none');
    $(this).find('#btn-reset-ds, #btn-reset-ns').hide();
    $(this).find('.collapse').collapse('hide');
  });
  
  // Form validation
  $('#form-add-ds, #form-add-ns').on('submit', function(e) {
    const $form = $(this);
    const addQty = parseInt($form.find('input[type="number"]').val()) || 0;
    const remark = $form.find('textarea').val().trim();
    
    if (addQty < 0) {
      e.preventDefault();
      alert('Quantity cannot be negative');
      return false;
    }
    
    if (addQty === 0 && $form.find('input[name="action"]').val() !== 'delete') {
      e.preventDefault();
      if (!confirm('Quantity is 0. Do you want to save this as reset?')) {
        return false;
      }
    }
    
    if (remark === '' && addQty > 0) {
      e.preventDefault();
      alert('Please enter a reason for the add order');
      return false;
    }
    
    return true;
  });
});
</script>