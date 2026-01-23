<!-- Modal Add Information -->
<div class="modal fade modal-add-information" id="modal-add-information" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-primary text-white py-3">
        <div class="d-flex align-items-center w-100">
          <div class="icon-container bg-white rounded-circle p-2 me-3">
            <i class="bi bi-chat-left-text-fill text-primary fs-4"></i>
          </div>
          <div>
            <h5 class="modal-title mb-0" style="font-weight: 700; font-size: 1.4rem">Add New Information</h5>
            <span class="small opacity-85 d-block mt-1">
              <i class="bi bi-calendar-check me-1"></i>
              <span id="txt-date-information"><?php echo date('d F Y'); ?></span>
            </span>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form class="dataInformationForm" method="post" id="addInformationForm">
          <input type="hidden" name="recipients" id="hidden-recipients" value="[]">
          <input type="hidden" name="type" value="input">
          <input type="hidden" name="date" value="<?php echo date('Ymd'); ?>">
          
          <!-- Sender Info -->
          <div class="card bg-light border-0 mb-4">
            <div class="card-body p-3">
              <h6 class="mb-3 text-primary fw-semibold">
                <i class="bi bi-person-badge me-2"></i>Sender Information
              </h6>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label small text-muted mb-1">
                      <i class="bi bi-clock me-1"></i>Time
                    </label>
                    <input type="text" class="form-control bg-white border-start-3 border-primary" 
                           name="txt-time1" id="txt-time1" value="<?php echo date('H:i'); ?>" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label small text-muted mb-1">
                      <i class="bi bi-person me-1"></i>PIC From
                    </label>
                    <input type="text" class="form-control bg-white border-start-3 border-primary" 
                           name="txt-picfrom" id="txt-picfrom" 
                           value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" readonly>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Recipients Selection -->
          <div class="card border-primary mb-4">
            <div class="card-header bg-primary bg-opacity-10 border-bottom-0 py-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-0 text-primary fw-semibold">
                    <i class="bi bi-people-fill me-2"></i>Select Recipients
                  </h6>
                  <small class="text-muted">Choose who will receive this information</small>
                </div>
                <div class="d-flex align-items-center">
                  <span class="badge bg-primary rounded-pill me-2" id="selected-count">0</span>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="select-all-recipients">
                    <label class="form-check-label small" for="select-all-recipients">All</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body p-3">
              <div class="recipients-container" style="max-height: 250px; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 15px;">
                <!-- Dynamic recipients loaded here -->
                <div class="text-center py-4">
                  <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                  <span class="ms-2">Loading recipients...</span>
                </div>
              </div>
              
              <!-- Selected Recipients Badges -->
              <div class="selected-recipients mt-3">
                <div class="selected-header d-flex justify-content-between align-items-center mb-2">
                  <small class="text-muted">Selected Recipients:</small>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-selection" style="display: none;">
                    <i class="bi bi-x-circle me-1"></i>Clear All
                  </button>
                </div>
                <div id="selected-users-badge" class="d-flex flex-wrap gap-2">
                  <div class="empty-state text-muted small">
                    <i class="bi bi-info-circle me-1"></i>No recipients selected yet
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Information Content -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
              <h6 class="mb-3 text-primary fw-semibold">
                <i class="bi bi-pencil-square me-2"></i>Information Details
              </h6>
              
              <div class="mb-3">
                <label for="txtItem" class="form-label fw-semibold">
                  <i class="bi bi-tag-fill me-1 text-primary"></i>Item Title
                  <span class="text-danger">*</span>
                </label>
                <input type="text" required name="txt-item" id="txtItem" 
                       class="form-control border-start-3 border-primary" 
                       placeholder="e.g., Delay from Supplier B78, Machine Maintenance Required, etc.">
                <div class="form-text text-muted small">
                  Be specific and concise with your information title
                </div>
              </div>
              
              <div class="mb-3">
                <label for="txtRequest" class="form-label fw-semibold">
                  <i class="bi bi-chat-left-text-fill me-1 text-primary"></i>Request Details
                  <span class="text-danger">*</span>
                </label>
                <textarea class="form-control border-start-3 border-primary" required 
                          name="txt-request" id="txtRequest" rows="5" 
                          placeholder="Describe the request, issue, or information in detail..."></textarea>
                <div class="form-text text-muted small">
                  Include all relevant details to help recipients understand the situation
                </div>
              </div>
            </div>
          </div>
          
          <!-- Info Box -->
          <div class="alert alert-info border-0 bg-info bg-opacity-10">
            <div class="d-flex">
              <i class="bi bi-info-circle-fill fs-5 text-info me-3 mt-1"></i>
              <div>
                <strong class="text-info">Information:</strong>
                <ul class="mb-0 mt-1 small">
                  <li>All selected recipients will receive this information</li>
                  <li>Recipients can reply and update the status</li>
                  <li>Only you (sender) can edit or delete this information</li>
                  <li>Information will be visible to all users in the table</li>
                </ul>
              </div>
            </div>
          </div>
          
        </form>
      </div>
      <div class="modal-footer bg-light border-top-0 px-4 py-3">
        <div class="w-100 d-flex justify-content-between align-items-center">
          <div>
            <img src="./assets/img/logo-denso.png" width="90" alt="DENSO" class="me-2">
            <small class="text-muted">Progress BO Control System</small>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-lg btn-outline-secondary px-4" data-bs-dismiss="modal">
              <i class="bi bi-x-circle me-2"></i>Cancel
            </button>
            <button type="submit" form="addInformationForm" class="btn btn-lg btn-primary px-4 shadow-sm">
              <i class="bi bi-send-fill me-2"></i>Send Information
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Update Information (From/Sender) -->
<div class="modal fade modal-update-information-from" id="modal-update-information-from" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-warning text-dark py-3">
        <div class="d-flex align-items-center w-100">
          <div class="icon-container bg-white rounded-circle p-2 me-3">
            <i class="bi bi-pencil-fill text-warning fs-4"></i>
          </div>
          <div>
            <h5 class="modal-title mb-0" style="font-weight: 700; font-size: 1.4rem">Edit Information</h5>
            <span class="small opacity-85 d-block mt-1">
              <i class="bi bi-calendar-check me-1"></i>
              <span id="txt-date-information-from"><?php echo date('d F Y'); ?></span>
            </span>
          </div>
        </div>
        <button type="button" class="btn-close opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form class="updateFromInformationForm" method="post" id="updateFromInformationForm">
          <input type="hidden" name="type" value="update-from">
          <input type="hidden" name="txt-id-information" id="txt-id-information">
          
          <div class="card bg-light border-0 mb-4">
            <div class="card-body p-3">
              <h6 class="mb-3 text-warning fw-semibold">
                <i class="bi bi-pencil-square me-2"></i>Edit Information Details
              </h6>
              
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label small text-muted mb-1">
                      <i class="bi bi-clock me-1"></i>Time From
                    </label>
                    <input type="text" class="form-control bg-white border-start-3 border-warning" 
                           name="txt-timefrom-update" id="txt-timefrom-update" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label small text-muted mb-1">
                      <i class="bi bi-person me-1"></i>PIC From
                    </label>
                    <input type="text" class="form-control bg-white border-start-3 border-warning" 
                           name="txt-picfrom-update" id="txt-picfrom-update" readonly>
                  </div>
                </div>
              </div>
              
              <div class="mt-3">
                <label for="txt-item-update" class="form-label fw-semibold">
                  <i class="bi bi-tag-fill me-1 text-warning"></i>Item Title
                  <span class="text-danger">*</span>
                </label>
                <input type="text" required name="txt-item-update" id="txt-item-update" 
                       class="form-control border-start-3 border-warning">
              </div>
              
              <div class="mt-3">
                <label for="txt-request-update" class="form-label fw-semibold">
                  <i class="bi bi-chat-left-text-fill me-1 text-warning"></i>Request Details
                  <span class="text-danger">*</span>
                </label>
                <textarea class="form-control border-start-3 border-warning" required 
                          name="txt-request-update" id="txt-request-update" rows="4"></textarea>
              </div>
            </div>
          </div>
          
          <div class="alert alert-warning border-0 bg-warning bg-opacity-10">
            <div class="d-flex">
              <i class="bi bi-exclamation-triangle-fill fs-5 text-warning me-3 mt-1"></i>
              <div class="small">
                <strong class="text-warning">Important:</strong> Only the sender can edit or delete this information. 
                Once recipients start replying, editing may be restricted.
              </div>
            </div>
          </div>
          
        </form>
      </div>
      <div class="modal-footer bg-light border-top-0 px-4 py-3">
        <div class="w-100 d-flex justify-content-between align-items-center">
          <div>
            <img src="./assets/img/logo-denso.png" width="70" alt="DENSO">
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-lg btn-outline-secondary px-4" data-bs-dismiss="modal">
              <i class="bi bi-x-circle me-2"></i>Cancel
            </button>
            <button type="submit" form="updateFromInformationForm" class="btn btn-lg btn-warning px-4 shadow-sm">
              <i class="bi bi-save-fill me-2"></i>Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Update Information (To/Recipient) - VERSION IMPROVED -->
<div class="modal fade modal-update-information-to" id="modal-update-information-to" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-info text-white py-3">
        <div class="d-flex align-items-center w-100">
          <div class="icon-container bg-white rounded-circle p-2 me-3">
            <i class="bi bi-reply-fill text-info fs-4"></i>
          </div>
          <div>
            <h5 class="modal-title mb-0" style="font-weight: 700; font-size: 1.4rem">Update Information Status</h5>
            <div id="display-status" class="mt-1"></div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="updateToInformationForm" method="post">
          <input type="hidden" name="type" value="update-to">
          <input type="hidden" name="txt-id-information2" id="txt-id-information2">
          <input type="hidden" name="txt-timefrom-to-update" id="txt-timefrom-to-update">
          <input type="hidden" name="txt-picfrom-to-update" id="txt-picfrom-to-update">
          <input type="hidden" name="txt-itemto-update" id="txt-itemto-update">
          <input type="hidden" name="txt-requestto-update" id="txt-requestto-update">
          <input type="hidden" name="txt-picto-update" id="txt-picto-update">
          <input type="hidden" name="txt-timeto-update" id="txt-timeto-update">
          
          <!-- Information Summary Card -->
          <div class="card border-info mb-4">
            <div class="card-header bg-info bg-opacity-10 border-bottom-0 py-3">
              <h6 class="mb-0 text-info fw-semibold">
                <i class="bi bi-info-circle-fill me-2"></i>Information Summary
              </h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="info-item mb-3">
                    <div class="small text-muted mb-1">
                      <i class="bi bi-person me-1"></i>From
                    </div>
                    <div class="fw-semibold" id="display-picfrom">-</div>
                  </div>
                  <div class="info-item mb-3">
                    <div class="small text-muted mb-1">
                      <i class="bi bi-clock me-1"></i>Time
                    </div>
                    <div class="fw-semibold" id="display-timefrom">-</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-item mb-3">
                    <div class="small text-muted mb-1">
                      <i class="bi bi-people me-1"></i>To
                    </div>
                    <div class="fw-semibold" id="display-picto">-</div>
                  </div>
                  <div class="info-item mb-3">
                    <div class="small text-muted mb-1">
                      <i class="bi bi-calendar me-1"></i>Date
                    </div>
                    <div class="fw-semibold" id="display-date">-</div>
                  </div>
                </div>
              </div>
              
              <div class="mt-3 pt-3 border-top">
                <div class="info-item mb-3">
                  <div class="small text-muted mb-1">
                    <i class="bi bi-tag me-1"></i>Item
                  </div>
                  <div class="bg-light p-3 rounded" id="display-item" 
                       style="white-space: pre-wrap; word-wrap: break-word;">-</div>
                </div>
                
                <div class="info-item mb-3">
                  <div class="small text-muted mb-1">
                    <i class="bi bi-chat-left-text me-1"></i>Request
                  </div>
                  <div class="bg-light p-3 rounded" id="display-request" 
                       style="white-space: pre-wrap; word-wrap: break-word;">-</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Status Selection -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
              <h6 class="mb-3 text-info fw-semibold">
                <i class="bi bi-arrow-clockwise me-2"></i>Update Status
              </h6>
              
              <div class="row g-3">
                <!-- On Progress -->
                <div class="col-md-6">
                  <div class="card status-card border-warning h-100" 
                       data-status="on_progress" 
                       onclick="selectStatus('on_progress')">
                    <div class="card-body text-center p-4">
                      <div class="status-icon mb-3">
                        <i class="bi bi-clock-history text-warning fs-1"></i>
                      </div>
                      <h5 class="card-title text-warning fw-semibold">On Progress</h5>
                      <p class="card-text small text-muted">
                        Mark as in progress. You can add remarks if needed.
                      </p>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="status_radio" 
                               id="statusOnProgress" value="on_progress">
                        <label class="form-check-label small" for="statusOnProgress">
                          Select this option
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Closed -->
                <div class="col-md-6">
                  <div class="card status-card border-success h-100" 
                       data-status="closed" 
                       onclick="selectStatus('closed')">
                    <div class="card-body text-center p-4">
                      <div class="status-icon mb-3">
                        <i class="bi bi-check-circle-fill text-success fs-1"></i>
                      </div>
                      <h5 class="card-title text-success fw-semibold">Closed</h5>
                      <p class="card-text small text-muted">
                        Mark as completed. Remarks are required.
                      </p>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="status_radio" 
                               id="statusClosed" value="closed">
                        <label class="form-check-label small" for="statusClosed">
                          Select this option
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Remarks -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
              <h6 class="mb-3 text-info fw-semibold">
                <i class="bi bi-chat-text-fill me-2"></i>Your Remarks
              </h6>
              
              <div class="mb-3">
                <label for="txt-remark-update" class="form-label fw-semibold">
                  Add your remarks or updates
                  <span class="text-danger">*</span>
                </label>
                <textarea class="form-control border-start-3 border-info" 
                          name="txt-remark-update" id="txt-remark-update" 
                          rows="4" placeholder="Enter your remarks here..."></textarea>
                <div class="form-text text-muted small mt-2">
                  <i class="bi bi-info-circle me-1"></i>
                  <span id="remark-info">Remarks are optional for "On Progress" but required for "Closed" status.</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="alert alert-info border-0 bg-info bg-opacity-10">
            <div class="d-flex">
              <i class="bi bi-lightbulb-fill fs-5 text-info me-3 mt-1"></i>
              <div class="small">
                <strong class="text-info">Tip:</strong> 
                As a recipient, you can update the status of this information. 
                Choose "On Progress" if you're working on it, or "Closed" when completed.
              </div>
            </div>
          </div>
          
        </form>
      </div>
      <div class="modal-footer bg-light border-top-0 px-4 py-3">
        <div class="w-100 d-flex justify-content-between align-items-center">
          <div>
            <img src="./assets/img/logo-denso.png" width="70" alt="DENSO">
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-lg btn-outline-secondary px-4" data-bs-dismiss="modal">
              <i class="bi bi-x-circle me-2"></i>Cancel
            </button>
            <button type="button" class="btn btn-lg btn-warning px-4 shadow-sm" id="btn-on-progress">
              <i class="bi bi-clock-history me-2"></i>On Progress
            </button>
            <button type="button" class="btn btn-lg btn-success px-4 shadow-sm" id="btn-closed">
              <i class="bi bi-check-circle me-2"></i>Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal View Information Details -->
<div class="modal fade" id="modal-view-information" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-dark text-white py-3">
        <div class="d-flex align-items-center w-100">
          <div class="icon-container bg-white rounded-circle p-2 me-3">
            <i class="bi bi-info-circle-fill text-dark fs-4"></i>
          </div>
          <div>
            <h5 class="modal-title mb-0" style="font-weight: 700; font-size: 1.4rem">Information Details</h5>
            <span class="small opacity-85 d-block mt-1">
              <i class="bi bi-eye-fill me-1"></i>View Full Information
            </span>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="view-info-content">
        <!-- Content loaded via AJAX -->
      </div>
      <div class="modal-footer bg-light border-top-0 px-4 py-3">
        <button type="button" class="btn btn-lg btn-dark px-4" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-2"></i>Close
        </button>
      </div>
    </div>
  </div>
</div>

<style>
/* Modal Custom Styles */
.modal-content {
  border-radius: 12px !important;
  overflow: hidden;
}

.modal-header .icon-container {
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%) !important;
}

.bg-gradient-warning {
  background: linear-gradient(135deg, #ffc107 0%, #ffdb6d 100%) !important;
}

.bg-gradient-info {
  background: linear-gradient(135deg, #17a2b8 0%, #6fdaef 100%) !important;
}

.bg-gradient-dark {
  background: linear-gradient(135deg, #343a40 0%, #6c757d 100%) !important;
}

.border-start-3 {
  border-left: 3px solid !important;
}

/* Status Cards */
.status-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.status-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.status-card.selected {
  border-color: currentColor;
  background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.status-card .status-icon i {
  transition: transform 0.3s ease;
}

.status-card:hover .status-icon i {
  transform: scale(1.1);
}

/* Recipients Container */
.recipients-container::-webkit-scrollbar {
  width: 6px;
}

.recipients-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.recipients-container::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 10px;
}

.recipients-container::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}

/* Form Elements */
.form-control:focus {
  box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
  border-color: var(--bs-primary);
}

/* Badges */
.badge {
  padding: 0.35em 0.65em;
  font-weight: 600;
}

/* Empty States */
.empty-state {
  padding: 2rem;
  text-align: center;
  color: #6c757d;
}

/* Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.modal-content {
  animation: fadeIn 0.3s ease-out;
}
</style>

<script>
// Status selection function
function selectStatus(status) {
  // Update UI
  $('.status-card').removeClass('selected');
  $(`.status-card[data-status="${status}"]`).addClass('selected');
  
  // Update radio button
  $(`input[name="status_radio"][value="${status}"]`).prop('checked', true);
  
  // Update remark info text
  if (status === 'closed') {
    $('#remark-info').html('<strong>Required:</strong> Please provide remarks before closing this information.');
    $('#txt-remark-update').attr('required', 'required');
  } else {
    $('#remark-info').html('Optional: Add remarks about the progress or updates.');
    $('#txt-remark-update').removeAttr('required');
  }
  
  // Update button states
  if (status === 'on_progress') {
    $('#btn-on-progress').addClass('active').removeClass('btn-outline-warning').addClass('btn-warning');
    $('#btn-closed').removeClass('active btn-success').addClass('btn-outline-success');
  } else {
    $('#btn-closed').addClass('active').removeClass('btn-outline-success').addClass('btn-success');
    $('#btn-on-progress').removeClass('active btn-warning').addClass('btn-outline-warning');
  }
}

// Initialize status selection
$(document).ready(function() {
  // Default selection
  selectStatus('on_progress');
  
  // Clear selection button
  $('#clear-selection').on('click', function() {
    $('.recipient-checkbox').prop('checked', false).trigger('change');
    $(this).hide();
  });
});
</script>