<!-- components/modals-information.php - VERSION ELEGAN RESPONSIVE LANDSCAPE -->
<!-- Modal Add Information -->
<div class="modal fade modal-add-information" id="modal-add-information" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="background: #f8fafc; color: #1e293b;">
      <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%); border-radius: 8px 8px 0 0;">
        <div class="d-flex align-items-center w-100">
          <div class="icon-container bg-white rounded-circle p-2 me-3">
            <i class="bi bi-chat-left-text-fill text-primary fs-4"></i>
          </div>
          <div class="flex-grow-1">
            <h5 class="modal-title mb-0 fw-bold text-white">Tambah Informasi Baru</h5>
            <span class="small opacity-85 d-block mt-1 text-white">
              <i class="bi bi-calendar-check me-1"></i>
              <span id="txt-date-information"><?php echo date('d F Y'); ?></span>
            </span>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      
      <div class="modal-body p-0">
        <form class="dataInformationForm" method="post" id="addInformationForm">
          <input type="hidden" name="recipients" id="hidden-recipients" value="[]">
          <input type="hidden" name="type" value="input">
          <input type="hidden" name="date" value="<?php echo date('Ymd'); ?>">
          
          <div class="p-4">
            <!-- Sender Info -->
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label fw-medium mb-2" style="color: #475569;">
                    <i class="bi bi-clock me-1"></i>Waktu
                  </label>
                  <input type="text" class="form-control" 
                         name="txt-time1" id="txt-time1" value="<?php echo date('H:i'); ?>" 
                         style="border-left: 4px solid #0d6efd !important;" readonly>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label fw-medium mb-2" style="color: #475569;">
                    <i class="bi bi-person me-1"></i>PIC From
                  </label>
                  <input type="text" class="form-control" 
                         name="txt-picfrom" id="txt-picfrom" 
                         value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" 
                         style="border-left: 4px solid #0d6efd !important;" readonly>
                </div>
              </div>
            </div>
            
            <!-- Recipients Selection -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h6 class="mb-1 fw-semibold text-primary">
                    <i class="bi bi-people-fill me-2"></i>Pilih Penerima
                  </h6>
                  <small class="text-muted">Pilih siapa yang akan menerima informasi ini</small>
                </div>
                <div class="d-flex align-items-center">
                  <span class="badge bg-primary rounded-pill me-2 px-3 py-1" id="selected-count">0</span>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="select-all-recipients">
                    <label class="form-check-label fw-medium" for="select-all-recipients" style="color: #475569;">Pilih Semua</label>
                  </div>
                </div>
              </div>
              
              <div class="recipients-container mb-3" 
                   style="max-height: 200px; overflow-y: auto; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                <div class="text-center py-3">
                  <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                  <span class="ms-2" style="color: #64748b;">Memuat daftar penerima...</span>
                </div>
              </div>
              
              <!-- Selected Recipients Badges -->
              <div class="selected-recipients">
                <div class="selected-header d-flex justify-content-between align-items-center mb-2">
                  <small class="text-muted fw-medium">Penerima Terpilih:</small>
                </div>
                <div id="selected-users-badge" class="d-flex flex-wrap gap-2">
                  <div class="empty-state text-muted small">
                    <i class="bi bi-info-circle me-1"></i>Belum ada penerima terpilih
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Information Content -->
            <div class="mb-3">
              <label for="txtItem" class="form-label fw-semibold" style="color: #1e293b;">
                <i class="bi bi-tag-fill me-1 text-primary"></i>Judul Item
                <span class="text-danger">*</span>
              </label>
              <input type="text" required name="txt-item" id="txtItem" 
                     class="form-control" 
                     style="border-left: 4px solid #0d6efd !important;"
                     placeholder="Contoh: Delay dari Supplier B78, Perawatan Mesin, dll">
            </div>
            
            <div class="mb-3">
              <label for="txtRequest" class="form-label fw-semibold" style="color: #1e293b;">
                <i class="bi bi-chat-left-text-fill me-1 text-primary"></i>Detail Permintaan
                <span class="text-danger">*</span>
              </label>
              <textarea class="form-control" required 
                        name="txt-request" id="txtRequest" rows="4"
                        style="border-left: 4px solid #0d6efd !important; resize: none;"
                        placeholder="Jelaskan permintaan, masalah, atau informasi secara detail..."></textarea>
            </div>
          </div>
        </form>
      </div>
      
      <div class="modal-footer px-4 py-3" style="border-top: 1px solid #e2e8f0; background: #f1f5f9;">
        <div class="w-100 d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <img src="./assets/img/logo-denso.png" width="24" alt="DENSO" class="me-2">
            <div>
              <small class="text-muted d-block">Progress BO Control</small>
              <small class="text-primary fw-medium"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></small>
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-lg me-1"></i>Batal
            </button>
            <button type="submit" form="addInformationForm" class="btn btn-primary">
              <i class="bi bi-send-fill me-1"></i>Kirim
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Update Information (From/Sender) - VERSION ELEGAN -->
<div class="modal fade modal-update-information-from" id="modal-update-information-from" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="background: #f8fafc; color: #1e293b;">
      <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #ffc107 0%, #ffaa00 100%); border-radius: 8px 8px 0 0;">
        <div class="d-flex align-items-center w-100">
          <div class="icon-container bg-white rounded-circle p-2 me-3">
            <i class="bi bi-pencil-fill text-warning fs-4"></i>
          </div>
          <div class="flex-grow-1">
            <h5 class="modal-title mb-0 fw-bold text-dark">Edit Informasi</h5>
            <span class="small opacity-85 d-block mt-1 text-dark">
              <i class="bi bi-calendar-check me-1"></i>
              <span id="txt-date-information-from"><?php echo date('d F Y'); ?></span>
            </span>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      
      <div class="modal-body p-0">
        <form class="updateFromInformationForm" method="post" id="updateFromInformationForm">
          <input type="hidden" name="type" value="update-from">
          <input type="hidden" name="txt-id-information" id="txt-id-information">
          
          <div class="p-4">
            <div class="alert alert-warning border-0 mb-4" style="background: rgba(255,193,7,0.1);">
              <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-5 me-2"></i>
                <div class="small">
                  <strong class="text-warning">Perhatian:</strong> Hanya pengirim yang dapat mengedit informasi ini.
                </div>
              </div>
            </div>
            
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label fw-medium mb-2" style="color: #475569;">
                    <i class="bi bi-clock me-1"></i>Waktu
                  </label>
                  <input type="text" class="form-control" 
                         name="txt-timefrom-update" id="txt-timefrom-update" required
                         style="border-left: 4px solid #ffc107 !important;">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label fw-medium mb-2" style="color: #475569;">
                    <i class="bi bi-person me-1"></i>PIC From
                  </label>
                  <input type="text" class="form-control" 
                         name="txt-picfrom-update" id="txt-picfrom-update" readonly
                         style="border-left: 4px solid #ffc107 !important;">
                </div>
              </div>
            </div>
            
            <div class="mb-4">
              <label for="txt-item-update" class="form-label fw-semibold" style="color: #1e293b;">
                <i class="bi bi-tag-fill me-1 text-warning"></i>Judul Item
                <span class="text-danger">*</span>
              </label>
              <input type="text" required name="txt-item-update" id="txt-item-update" 
                     class="form-control"
                     style="border-left: 4px solid #ffc107 !important;">
            </div>
            
            <div class="mb-4">
              <label for="txt-request-update" class="form-label fw-semibold" style="color: #1e293b;">
                <i class="bi bi-chat-left-text-fill me-1 text-warning"></i>Detail Permintaan
                <span class="text-danger">*</span>
              </label>
              <textarea class="form-control" required 
                        name="txt-request-update" id="txt-request-update" rows="4"
                        style="border-left: 4px solid #ffc107 !important; resize: none;"></textarea>
            </div>
          </div>
        </form>
      </div>
      
      <div class="modal-footer px-4 py-3" style="border-top: 1px solid #e2e8f0; background: #f1f5f9;">
        <div class="w-100 d-flex justify-content-between align-items-center">
          <div>
            <img src="./assets/img/logo-denso.png" width="24" alt="DENSO">
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-lg me-1"></i>Batal
            </button>
            <button type="submit" form="updateFromInformationForm" class="btn btn-warning">
              <i class="bi bi-save-fill me-1"></i>Simpan
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Update Information (To/Recipient) - VERSION ELEGAN RESPONSIVE -->
<div class="modal fade modal-update-information-to" id="modal-update-information-to" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); color: #1e293b;">
      <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px 8px 0 0;">
        <div class="d-flex align-items-center w-100">
          <div class="icon-container bg-white rounded-circle p-2 me-3 shadow-sm">
            <i class="bi bi-reply-fill text-success fs-4"></i>
          </div>
          <div class="flex-grow-1">
            <h5 class="modal-title mb-0 fw-bold text-white">Balas / Update Status</h5>
            <span class="small opacity-85 d-block mt-1 text-white">
              <i class="bi bi-calendar-check me-1"></i>
              <span id="txt-date-information-to"><?php echo date('d F Y'); ?></span>
            </span>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      
      <div class="modal-body p-0" style="max-height: 70vh; overflow-y: auto;">
        <div class="container-fluid p-0">
          <form id="updateToInformationForm" method="post" class="h-100">
            <input type="hidden" name="type" value="update-to">
            <input type="hidden" name="txt-id-information2" id="txt-id-information2">
            <input type="hidden" name="txt-timefrom-to-update" id="txt-timefrom-to-update">
            <input type="hidden" name="txt-picfrom-to-update" id="txt-picfrom-to-update">
            <input type="hidden" name="txt-itemto-update" id="txt-itemto-update">
            <input type="hidden" name="txt-requestto-update" id="txt-requestto-update">
            <input type="hidden" name="txt-picto-update" id="txt-picto-update">
            <input type="hidden" name="txt-timeto-update" id="txt-timeto-update">
            
            <div class="p-4">
              <!-- Original Information Display -->
              <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                  <h6 class="card-title fw-semibold text-success mb-3">
                    <i class="bi bi-info-circle-fill me-2"></i>Informasi Asli
                  </h6>
                  
                  <div class="row g-3">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <small class="text-muted d-block mb-1">
                          <i class="bi bi-person-badge me-1"></i>Pengirim
                        </small>
                        <div class="fw-bold text-dark" id="display-picfrom">-</div>
                        <small class="text-muted">
                          <i class="bi bi-clock me-1"></i>
                          <span id="display-timefrom">-</span>
                        </small>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="mb-3">
                        <small class="text-muted d-block mb-1">
                          <i class="bi bi-people-fill me-1"></i>Penerima
                        </small>
                        <div class="fw-bold text-dark" id="display-picto">-</div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <small class="text-muted d-block mb-1">
                      <i class="bi bi-tag-fill me-1"></i>Judul Item
                    </small>
                    <div class="alert alert-light border py-2 mb-0" id="display-item">-</div>
                  </div>
                  
                  <div class="mb-3">
                    <small class="text-muted d-block mb-1">
                      <i class="bi bi-chat-text me-1"></i>Permintaan
                    </small>
                    <div class="alert alert-light border py-2 mb-0" id="display-request">-</div>
                  </div>
                  
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        <span id="display-date">-</span>
                      </small>
                    </div>
                    <div>
                      <span class="badge bg-secondary" id="display-status-text">-</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Action Selection -->
              <div class="mb-4">
                <h6 class="fw-semibold text-success mb-3">
                  <i class="bi bi-send-check-fill me-2"></i>Pilih Tindakan
                </h6>
                
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="card action-card" data-action="on_progress" style="cursor: pointer;">
                      <div class="card-body">
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-warning p-2 d-flex align-items-center justify-content-center me-3" 
                               style="width: 40px; height: 40px;">
                            <i class="bi bi-clock-history text-white fs-5"></i>
                          </div>
                          <div>
                            <h6 class="card-title mb-1 fw-bold text-dark">ON PROGRESS</h6>
                            <small class="text-muted">Sedang menangani</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="card action-card" data-action="closed" style="cursor: pointer;">
                      <div class="card-body">
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-success p-2 d-flex align-items-center justify-content-center me-3" 
                               style="width: 40px; height: 40px;">
                            <i class="bi bi-check-circle-fill text-white fs-5"></i>
                          </div>
                          <div>
                            <h6 class="card-title mb-1 fw-bold text-dark">CLOSED</h6>
                            <small class="text-muted">Selesaikan & tutup</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Remarks Section -->
              <div class="mb-4">
                <label for="txt-remark-update" class="form-label fw-semibold mb-2 d-flex align-items-center text-dark">
                  <i class="bi bi-chat-square-text-fill me-2 text-success"></i>Catatan / Tindakan
                  <span id="remark-required" class="text-danger ms-1" style="display: none;">*</span>
                </label>
                
                <textarea class="form-control" 
                          name="txt-remark-update" id="txt-remark-update" 
                          rows="4" placeholder="Tulis catatan atau tindakan yang sudah dilakukan..."
                          style="border: 1px solid #d1d5db; border-left: 4px solid #10b981 !important; resize: none;"></textarea>
                
                <div class="form-text mt-2 fw-medium text-muted" id="remark-info-text">
                  <i class="bi bi-info-circle me-1"></i>
                  Tambahkan catatan tentang progress atau update terbaru
                </div>
              </div>
              
              <!-- Time Input -->
              <div class="mb-4">
                <label for="reply-time-input" class="form-label fw-medium mb-2 text-dark">
                  <i class="bi bi-clock-fill me-1 text-success"></i>Waktu Respon
                </label>
                <div class="input-group">
                  <input type="text" class="form-control" 
                         id="reply-time-input" 
                         value="<?php echo date('H:i'); ?>"
                         readonly>
                  <span class="input-group-text bg-light">
                    <i class="bi bi-check-circle text-success"></i>
                  </span>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      
      <div class="modal-footer px-4 py-3 border-top" style="background: #f1f5f9;">
        <div class="w-100 d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <img src="./assets/img/logo-denso.png" width="24" alt="DENSO" class="me-2">
            <div>
              <small class="text-muted d-block">Progress BO Control</small>
              <small class="text-primary fw-medium"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></small>
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-lg me-1"></i>Batal
            </button>
            <button type="submit" form="updateToInformationForm" class="btn btn-success" id="btn-submit-reply">
              <i class="bi bi-send-check-fill me-1"></i>
              <span id="submit-button-text">Kirim Respon</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* Modal Responsive Design */
@media (max-width: 768px) {
  .modal-dialog {
    margin: 10px !important;
    max-width: calc(100% - 20px) !important;
  }
  
  .modal-content {
    border-radius: 8px !important;
  }
  
  .modal-header {
    padding: 1rem !important;
  }
  
  .modal-title {
    font-size: 1.1rem !important;
  }
  
  .p-4 {
    padding: 1rem !important;
  }
  
  .row.g-3 > [class*="col-"] {
    margin-bottom: 1rem;
  }
  
  .action-card {
    margin-bottom: 10px !important;
  }
}

/* Action Card Selection */
.action-card {
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.action-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.action-card.selected {
  border-color: #10b981;
  background-color: rgba(16, 185, 129, 0.05);
}

/* Form Controls */
.form-control {
  border-radius: 6px !important;
  border: 1px solid #d1d5db;
}

.form-control:focus {
  border-color: #10b981 !important;
  box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25) !important;
}

/* Scrollbar Styling */
.modal-body::-webkit-scrollbar {
  width: 6px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Button Loading State */
.btn-loading {
  position: relative;
  color: transparent !important;
  pointer-events: none;
}

.btn-loading::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 20px;
  height: 20px;
  margin: -10px 0 0 -10px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: button-spinner 0.6s linear infinite;
}

@keyframes button-spinner {
  to { transform: rotate(360deg); }
}

/* Toast Styling */
.custom-toast {
  position: fixed;
  top: 20px;
  right: 20px;
  min-width: 300px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  padding: 15px;
  transform: translateX(120%);
  transition: transform 0.3s ease;
  z-index: 9999;
  display: flex;
  align-items: center;
}

.custom-toast.show {
  transform: translateX(0);
}

.custom-toast.success {
  border-left: 4px solid #28a745;
}

.custom-toast.error {
  border-left: 4px solid #dc3545;
}

.custom-toast.info {
  border-left: 4px solid #17a2b8;
}

.toast-icon {
  font-size: 24px;
  margin-right: 15px;
}

.custom-toast.success .toast-icon { color: #28a745; }
.custom-toast.error .toast-icon { color: #dc3545; }
.custom-toast.info .toast-icon { color: #17a2b8; }

/* Highlight untuk table row */
.highlighted-row {
  background-color: rgba(255, 193, 7, 0.15) !important;
  border-left: 4px solid #ffc107 !important;
  border-right: 4px solid #ffc107 !important;
  animation: highlightPulse 2s infinite;
}

@keyframes highlightPulse {
  0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.3); }
  50% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
  100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}
</style>

<script>
$(document).ready(function() {
  // Action card selection
  $(document).on('click', '.action-card', function() {
    const action = $(this).data('action');
    
    // Remove selected class from all
    $('.action-card').removeClass('selected');
    
    // Add to clicked
    $(this).addClass('selected');
    
    // Update hidden input
    $('#action-type').val(action);
    
    // Update remark requirements
    updateRemarkField(action);
  });
  
  function updateRemarkField(action) {
    const $remarkField = $('#txt-remark-update');
    const $requiredStar = $('#remark-required');
    const $infoText = $('#remark-info-text');
    const $submitButton = $('#btn-submit-reply');
    const $submitText = $('#submit-button-text');
    
    if (action === 'closed') {
      // CLOSED - Remark wajib
      $requiredStar.show();
      $remarkField.attr('required', 'required');
      $remarkField.attr('placeholder', 'Contoh: Sudah ditindaklanjuti, masalah sudah selesai, hasilnya...');
      $infoText.html('<i class="bi bi-exclamation-triangle me-1 text-danger"></i><strong class="text-danger">Wajib diisi!</strong> Harap berikan catatan detail sebelum menutup informasi.');
      $submitText.text('Tutup Informasi');
      $submitButton.removeClass('btn-warning').addClass('btn-success');
    } else {
      // ON PROGRESS - Remark opsional
      $requiredStar.hide();
      $remarkField.removeAttr('required');
      $remarkField.attr('placeholder', 'Tulis catatan atau tindakan yang sudah dilakukan...');
      $infoText.html('<i class="bi bi-info-circle me-1"></i>Opsional: Tambahkan catatan tentang progress atau update terbaru.');
      $submitText.text('Simpan sebagai On Progress');
      $submitButton.removeClass('btn-success').addClass('btn-warning');
    }
  }
  
  // Form submission
  $('#updateToInformationForm').on('submit', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const selectedAction = $('.action-card.selected').data('action');
    const remark = $('#txt-remark-update').val().trim();
    
    // Validation
    if (!selectedAction) {
      showToast('error', 'Pilih tindakan terlebih dahulu');
      return false;
    }
    
    if (selectedAction === 'closed' && !remark) {
      showToast('error', 'Catatan wajib diisi untuk menutup informasi');
      $('#txt-remark-update').focus();
      return false;
    }
    
    // Show loading
    const $submitBtn = $('#btn-submit-reply');
    const originalText = $submitBtn.html();
    $submitBtn.prop('disabled', true)
              .addClass('btn-loading')
              .html('<span class="spinner-border spinner-border-sm"></span> Processing...');
    
    // Prepare form data
    const formData = new FormData(this);
    formData.append('action_type', selectedAction);
    
    // AJAX submit
    $.ajax({
      url: 'modules/data_information.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(response) {
        $submitBtn.prop('disabled', false)
                  .removeClass('btn-loading')
                  .html(originalText);
        
        if (response.success) {
          showToast('success', response.message);
          
          // Close modal after 1.5 seconds
          setTimeout(() => {
            $('#modal-update-information-to').modal('hide');
          }, 1500);
          
          // Refresh data after 2 seconds
          setTimeout(() => {
            if (typeof fetchDataInformation === 'function') {
              fetchDataInformation();
            }
          }, 2000);
          
        } else {
          showToast('error', response.message);
        }
      },
      error: function(xhr, status, error) {
        $submitBtn.prop('disabled', false)
                  .removeClass('btn-loading')
                  .html(originalText);
        showToast('error', 'Network error: ' + error);
      }
    });
    
    return false;
  });
  
  // Toast function
  function showToast(type, message) {
    $('.custom-toast').remove();
    
    const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    const title = type === 'success' ? 'Success' : 'Error';
    const color = type === 'success' ? '#28a745' : '#dc3545';
    
    const toast = $(`
      <div class="custom-toast ${type}" style="border-left-color: ${color}">
        <div class="toast-icon">
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
  
  // Initialize when modal shows
  $('#modal-update-information-to').on('shown.bs.modal', function() {
    // Default select ON PROGRESS
    $('.action-card[data-action="on_progress"]').click();
    
    // Auto-set current time
    const now = new Date();
    const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                      now.getMinutes().toString().padStart(2, '0');
    $('#reply-time-input').val(timeString);
    $('#txt-timeto-update').val(timeString);
  });
  
  // Reset when modal hides
  $('#modal-update-information-to').on('hidden.bs.modal', function() {
    $('.action-card').removeClass('selected');
    $('#txt-remark-update').val('');
    $('#btn-submit-reply').prop('disabled', false).removeClass('btn-loading');
  });
});
</script>