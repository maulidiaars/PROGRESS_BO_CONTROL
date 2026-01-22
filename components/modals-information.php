<!-- Modal Add Information -->
<div class="modal fade modal-add-information" id="modal-add-information" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="bi bi-chat-left-text-fill me-2"></i>
          <span style="font-size: 20px; font-weight: bold">Add Information</span>
          <br>
          <span style="font-size: 14px; font-weight: normal" id="txt-date-information">
            <?php echo date('d F Y'); ?>
          </span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="font-size: 14px;" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form class="dataInformationForm" method="post" id="addInformationForm">
          <!-- Hidden field untuk recipients -->
          <input type="hidden" name="recipients" id="hidden-recipients" value="[]">
          <input type="hidden" name="type" value="input">
          <input type="hidden" name="date" value="<?php echo date('Ymd'); ?>">
          
          <div class="row mb-3">
            <label for="txt-time1" class="col-sm-2 col-form-label">
              <i class="bi bi-clock-fill text-primary"></i> Time
            </label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="txt-time1" id="txt-time1" 
                     value="<?php echo date('H:i'); ?>" readonly 
                     style="background-color: #e8f4fd;">
            </div>
          </div>
          
          <div class="row mb-3">
            <label for="txt-picfrom" class="col-sm-2 col-form-label">
              <i class="bi bi-person-fill text-primary"></i> PIC From
            </label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="txt-picfrom" id="txt-picfrom" 
                     value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" readonly
                     style="background-color: #e8f4fd;">
            </div>
          </div>
          
          <!-- MULTI-SELECT RECIPIENTS -->
          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">
              <i class="bi bi-people-fill text-primary"></i> To (PIC)
            </label>
            <div class="col-sm-10">
              <div class="card border-primary mb-2">
                <div class="card-header bg-light py-2">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="select-all-recipients">
                      <label class="form-check-label fw-bold" for="select-all-recipients">
                        Select All
                      </label>
                    </div>
                    <span class="badge bg-primary" id="selected-count">0 selected</span>
                  </div>
                  <small class="text-muted d-block mt-1">Select one or multiple recipients</small>
                </div>
                <div class="card-body p-3 recipients-container" style="max-height: 200px; overflow-y: auto;">
                  <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2">Loading users...</span>
                  </div>
                </div>
              </div>
              
              <div class="selected-recipients-box">
                <div id="selected-users-badge" class="mt-2"></div>
              </div>
            </div>
          </div>
          
          <div class="row mb-3">
            <label for="txtItem" class="col-sm-2 col-form-label">
              <i class="bi bi-pin-angle-fill text-primary"></i> Item
            </label>
            <div class="col-sm-10">
              <input type="text" required name="txt-item" id="txtItem" class="form-control" 
                     placeholder="Example: Delay supplier B78, Machine breakdown, etc.">
            </div>
          </div>
          
          <div class="row mb-3">
            <label for="txtRequest" class="col-sm-2 col-form-label">
              <i class="bi bi-chat-dots-fill text-primary"></i> Request
            </label>
            <div class="col-sm-10">
              <textarea class="form-control" required name="txt-request" id="txtRequest" 
                        rows="4" placeholder="Describe the request/issue in detail..."></textarea>
            </div>
          </div>
          
          <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-2"></i>
            Information will be sent to selected users. All users can see it in the table, but only recipients can reply.
          </div>
          
        </div>
        <div class="modal-footer">
          <img src="./assets/img/logo-denso.png" width="90px" height="40px" class="me-auto">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancel
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-send-fill"></i> Send Information
          </button>
        </div>
        </form>
    </div>
  </div>
</div>

<!-- Modal Update Information (From/Pengirim) -->
<div class="modal fade modal-update-information-from" id="modal-update-information-from" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">
          <i class="bi bi-pencil-fill me-2"></i>
          <span style="font-size: 20px; font-weight: bold">Edit Information</span>
          <br>
          <span style="font-size: 14px; font-weight: normal" id="txt-date-information-from">
            <?php echo date('d F Y'); ?>
          </span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form class="updateFromInformationForm" method="post" id="updateFromInformationForm">
          <input type="hidden" name="type" value="update-from">
          <input type="hidden" name="txt-id-information" id="txt-id-information">
          
          <div class="row mb-3">
            <label for="txt-timefrom-update" class="col-sm-2 col-form-label">
              <i class="bi bi-clock-fill"></i> Time From
            </label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="txt-timefrom-update" id="txt-timefrom-update" required>
            </div>
          </div>
          
          <div class="row mb-3">
            <label for="txt-picfrom-update" class="col-sm-2 col-form-label">
              <i class="bi bi-person-fill"></i> PIC From
            </label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="txt-picfrom-update" id="txt-picfrom-update" readonly
                     style="background-color: #f8f9fa;">
            </div>
          </div>
          
          <div class="row mb-3">
            <label for="txt-item-update" class="col-sm-2 col-form-label">
              <i class="bi bi-pin-angle-fill"></i> Item
            </label>
            <div class="col-sm-10">
              <input type="text" required name="txt-item-update" id="txt-item-update" class="form-control">
            </div>
          </div>
          
          <div class="row mb-3">
            <label for="txt-request-update" class="col-sm-2 col-form-label">
              <i class="bi bi-chat-dots-fill"></i> Request
            </label>
            <div class="col-sm-10">
              <textarea class="form-control" required name="txt-request-update" id="txt-request-update" rows="4"></textarea>
            </div>
          </div>
          
          <div class="alert alert-warning mt-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            You are editing this information as the sender. Only you can edit or delete this information.
          </div>
          
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancel
          </button>
          <button type="submit" class="btn btn-warning">
            <i class="bi bi-save-fill"></i> Update Information
          </button>
        </div>
        </form>
    </div>
  </div>
</div>

<!-- Modal Update Information (To/Penerima) - VERSION BARU -->
<div class="modal fade modal-update-information-to" id="modal-update-information-to" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">
          <i class="bi bi-reply-fill me-2"></i>
          <span style="font-size: 20px; font-weight: bold">Update Information Status</span>
          <span id="display-status"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="updateToInformationForm" method="post">
          <input type="hidden" name="type" value="update-to">
          <input type="hidden" name="txt-id-information2" id="txt-id-information2">
          <input type="hidden" name="txt-timefrom-to-update" id="txt-timefrom-to-update">
          <input type="hidden" name="txt-picfrom-to-update" id="txt-picfrom-to-update">
          <input type="hidden" name="txt-itemto-update" id="txt-itemto-update">
          <input type="hidden" name="txt-requestto-update" id="txt-requestto-update">
          <input type="hidden" name="txt-picto-update" id="txt-picto-update">
          <input type="hidden" name="txt-timeto-update" id="txt-timeto-update">
          
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            You are updating this information as a recipient. Please choose appropriate action.
          </div>
          
          <div class="info-details-box mb-4">
            <h6><i class="bi bi-chat-left-text me-2"></i>Information Details</h6>
            <div class="row mt-3">
              <div class="col-md-6">
                <div class="mb-2">
                  <strong>From:</strong> <span id="display-picfrom"></span>
                </div>
                <div class="mb-2">
                  <strong>Time:</strong> <span id="display-timefrom"></span>
                </div>
                <div class="mb-2">
                  <strong>Date:</strong> <span id="display-date"></span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-2">
                  <strong>To:</strong> <span id="display-picto"></span>
                </div>
                <div class="mb-2">
                  <strong>Current Status:</strong> 
                  <span class="badge bg-warning" id="current-status-badge">Open</span>
                </div>
              </div>
            </div>
            <div class="mt-3">
              <strong>Item:</strong>
              <div class="alert alert-light mt-1" id="display-item" style="white-space: pre-wrap; word-wrap: break-word; max-height: 150px; overflow-y: auto;"></div>
              
              <strong>Request:</strong>
              <div class="alert alert-light mt-1" id="display-request" style="white-space: pre-wrap; word-wrap: break-word; max-height: 200px; overflow-y: auto;"></div>
            </div>
          </div>
          
          <div class="row mb-3">
            <label for="txt-remark-update" class="col-sm-2 col-form-label">
              <i class="bi bi-chat-text-fill"></i> Remark
            </label>
            <div class="col-sm-10">
              <textarea class="form-control" name="txt-remark-update" id="txt-remark-update" 
                        rows="4" placeholder="Add your remark here..."></textarea>
              <div class="form-text">
                <i class="bi bi-info-circle"></i> 
                <strong>On Progress:</strong> Optional remark. <strong>Closed:</strong> Remark is required.
              </div>
            </div>
          </div>
          
          <div class="action-buttons text-center mt-4">
            <div class="d-flex justify-content-center gap-3">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="bi bi-x-circle"></i> Cancel
              </button>
              <button type="button" class="btn btn-warning" id="btn-on-progress">
                <i class="bi bi-clock-history"></i> Mark as On Progress
              </button>
              <button type="button" class="btn btn-success" id="btn-closed">
                <i class="bi bi-check-circle"></i> Mark as Closed
              </button>
            </div>
          </div>
          
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal View Information -->
<div class="modal fade" id="modal-view-information" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">
          <i class="bi bi-info-circle-fill me-2"></i>
          Information Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="view-info-content">
        <!-- Content loaded via AJAX -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>