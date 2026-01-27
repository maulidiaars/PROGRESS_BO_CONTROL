<!-- components/modals-information.php - VERSION ELEGAN LANDSCAPE REVISED -->
<!-- Modal Add Information -->
<div class="modal fade modal-add-information" id="modal-add-information" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-landscape">
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
          
          <!-- Container dua kolom -->
          <div class="row g-0">
            <!-- Kolom Kiri: Sender & Recipients -->
            <div class="col-md-6 border-end">
              <!-- Sender Info -->
              <div class="p-4">
                <h6 class="mb-3 fw-semibold text-primary">
                  <i class="bi bi-person-badge me-2"></i>Informasi Pengirim
                </h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label fw-medium mb-2" style="color: #475569;">
                        <i class="bi bi-clock me-1"></i>Waktu
                      </label>
                      <input type="text" class="form-control form-control-lg" 
                             name="txt-time1" id="txt-time1" value="<?php echo date('H:i'); ?>" 
                             style="border-left: 4px solid #0d6efd !important;" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label fw-medium mb-2" style="color: #475569;">
                        <i class="bi bi-person me-1"></i>PIC From
                      </label>
                      <input type="text" class="form-control form-control-lg" 
                             name="txt-picfrom" id="txt-picfrom" 
                             value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" 
                             style="border-left: 4px solid #0d6efd !important;" readonly>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Recipients Selection -->
              <div class="p-4 border-top">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <h6 class="mb-0 fw-semibold text-primary">
                      <i class="bi bi-people-fill me-2"></i>Pilih Penerima
                    </h6>
                    <small class="text-muted">Pilih siapa yang akan menerima informasi ini</small>
                  </div>
                  <div class="d-flex align-items-center">
                    <span class="badge bg-primary rounded-pill me-2 px-3 py-2" id="selected-count">0</span>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="select-all-recipients">
                      <label class="form-check-label fw-medium" for="select-all-recipients" style="color: #475569;">Pilih Semua</label>
                    </div>
                  </div>
                </div>
                
                <div class="recipients-container mb-3" 
                     style="max-height: 250px; overflow-y: auto; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                  <!-- Dynamic recipients loaded here -->
                  <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2" style="color: #64748b;">Memuat daftar penerima...</span>
                  </div>
                </div>
                
                <!-- Selected Recipients Badges -->
                <div class="selected-recipients">
                  <div class="selected-header d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-medium">Penerima Terpilih:</small>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-selection" style="display: none;">
                      <i class="bi bi-x-circle me-1"></i>Hapus Semua
                    </button>
                  </div>
                  <div id="selected-users-badge" class="d-flex flex-wrap gap-2">
                    <div class="empty-state text-muted small">
                      <i class="bi bi-info-circle me-1"></i>Belum ada penerima terpilih
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Kolom Kanan: Information Content -->
            <div class="col-md-6">
              <div class="p-4 h-100 d-flex flex-column">
                <h6 class="mb-3 fw-semibold text-primary">
                  <i class="bi bi-pencil-square me-2"></i>Detail Informasi
                </h6>
                
                <div class="mb-4">
                  <label for="txtItem" class="form-label fw-semibold" style="color: #1e293b;">
                    <i class="bi bi-tag-fill me-1 text-primary"></i>Judul Item
                    <span class="text-danger">*</span>
                  </label>
                  <input type="text" required name="txt-item" id="txtItem" 
                         class="form-control form-control-lg" 
                         style="border-left: 4px solid #0d6efd !important;"
                         placeholder="Contoh: Delay dari Supplier B78, Perawatan Mesin, dll">
                  <div class="form-text mt-2" style="color: #64748b;">
                    <i class="bi bi-lightbulb me-1"></i>Buat judul yang spesifik dan jelas
                  </div>
                </div>
                
                <div class="mb-4 flex-grow-1 d-flex flex-column">
                  <label for="txtRequest" class="form-label fw-semibold" style="color: #1e293b;">
                    <i class="bi bi-chat-left-text-fill me-1 text-primary"></i>Detail Permintaan
                    <span class="text-danger">*</span>
                  </label>
                  <textarea class="form-control flex-grow-1" required 
                            name="txt-request" id="txtRequest" 
                            style="border-left: 4px solid #0d6efd !important; resize: none; min-height: 180px;"
                            placeholder="Jelaskan permintaan, masalah, atau informasi secara detail..."></textarea>
                  <div class="form-text mt-2" style="color: #64748b;">
                    <i class="bi bi-info-circle me-1"></i>Sertakan semua detail yang relevan untuk membantu penerima memahami situasi
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      
      <div class="modal-footer px-4 py-3" style="border-top: 1px solid #e2e8f0; background: #f1f5f9;">
        <div class="w-100 d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <img src="./assets/img/logo-denso.png" width="90" alt="DENSO" class="me-3">
            <div>
              <small class="text-muted d-block">Progress BO Control System</small>
              <small class="text-primary fw-medium"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></small>
            </div>
          </div>
          <div class="d-flex gap-3">
            <button type="button" class="btn btn-lg btn-outline-secondary px-5" data-bs-dismiss="modal"
                    style="border-width: 2px; font-weight: 600;">
              <i class="bi bi-x-lg me-2"></i>Batal
            </button>
            <button type="submit" form="addInformationForm" class="btn btn-lg btn-primary px-5 shadow-sm"
                    style="background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%); border: none; font-weight: 600;">
              <i class="bi bi-send-fill me-2"></i>Kirim Informasi
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Update Information (From/Sender) -->
<div class="modal fade modal-update-information-from" id="modal-update-information-from" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-landscape">
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
          
          <div class="row g-0">
            <div class="col-md-6 border-end">
              <div class="p-4">
                <h6 class="mb-4 fw-semibold text-warning">
                  <i class="bi bi-info-circle-fill me-2"></i>Informasi Pengirim
                </h6>
                
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label fw-medium mb-2" style="color: #475569;">
                        <i class="bi bi-clock me-1"></i>Waktu
                      </label>
                      <input type="text" class="form-control form-control-lg" 
                             name="txt-timefrom-update" id="txt-timefrom-update" required
                             style="border-left: 4px solid #ffc107 !important;">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label fw-medium mb-2" style="color: #475569;">
                        <i class="bi bi-person me-1"></i>PIC From
                      </label>
                      <input type="text" class="form-control form-control-lg" 
                             name="txt-picfrom-update" id="txt-picfrom-update" readonly
                             style="border-left: 4px solid #ffc107 !important;">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="p-4">
                <div class="mb-4">
                  <label for="txt-item-update" class="form-label fw-semibold" style="color: #1e293b;">
                    <i class="bi bi-tag-fill me-1 text-warning"></i>Judul Item
                    <span class="text-danger">*</span>
                  </label>
                  <input type="text" required name="txt-item-update" id="txt-item-update" 
                         class="form-control form-control-lg"
                         style="border-left: 4px solid #ffc107 !important;">
                </div>
                
                <div class="mb-4 flex-grow-1 d-flex flex-column">
                  <label for="txt-request-update" class="form-label fw-semibold" style="color: #1e293b;">
                    <i class="bi bi-chat-left-text-fill me-1 text-warning"></i>Detail Permintaan
                    <span class="text-danger">*</span>
                  </label>
                  <textarea class="form-control flex-grow-1" required 
                            name="txt-request-update" id="txt-request-update" rows="4"
                            style="border-left: 4px solid #ffc107 !important; resize: none; min-height: 150px;"></textarea>
                </div>
              </div>
            </div>
          </div>
          
          <div class="p-4 border-top">
            <div class="alert alert-warning border-0" style="background: rgba(255,193,7,0.1); color: #b45309;">
              <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                <div class="fw-medium">
                  <strong class="text-warning">Perhatian:</strong> Hanya pengirim yang dapat mengedit atau menghapus informasi ini. 
                  Setelah penerima mulai membalas, pengeditan mungkin dibatasi.
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      
      <div class="modal-footer px-4 py-3" style="border-top: 1px solid #e2e8f0; background: #f1f5f9;">
        <div class="w-100 d-flex justify-content-between align-items-center">
          <div>
            <img src="./assets/img/logo-denso.png" width="70" alt="DENSO">
          </div>
          <div class="d-flex gap-3">
            <button type="button" class="btn btn-lg btn-outline-secondary px-5" data-bs-dismiss="modal"
                    style="border-width: 2px; font-weight: 600;">
              <i class="bi bi-x-lg me-2"></i>Batal
            </button>
            <button type="submit" form="updateFromInformationForm" class="btn btn-lg btn-warning px-5 shadow-sm"
                    style="background: linear-gradient(135deg, #ffc107 0%, #ffaa00 100%); border: none; font-weight: 600; color: #000;">
              <i class="bi bi-save-fill me-2"></i>Simpan Perubahan
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Update Information (To/Recipient) - VERSION REVISED TANPA BUTTON ON PROGRESS/CLOSED -->
<div class="modal fade modal-update-information-to" id="modal-update-information-to" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-landscape">
    <div class="modal-content border-0 shadow-lg" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); color: #1e293b; min-height: 550px; max-height: 90vh;">
      <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px 8px 0 0;">
        <div class="d-flex align-items-center w-100">
          <div class="icon-container bg-white rounded-circle p-2 me-3 shadow-sm">
            <i class="bi bi-reply-fill text-success fs-4"></i>
          </div>
          <div class="flex-grow-1">
            <h5 class="modal-title mb-0 fw-bold text-white">Balas / Update Status Informasi</h5>
            <span class="small opacity-85 d-block mt-1 text-white">
              <i class="bi bi-calendar-check me-1"></i>
              <span id="txt-date-information-to"><?php echo date('d F Y'); ?></span>
              • <span id="display-status-badge" class="badge bg-white text-dark ms-1">LOADING...</span>
            </span>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      
      <div class="modal-body p-0" style="overflow-y: auto;">
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
            
            <!-- Status Selection dengan Radio Button Sederhana -->
            <div class="status-selection-section bg-white p-4 border-bottom">
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                      <div class="d-flex align-items-center mb-3">
                        <div class="form-check me-3">
                          <input class="form-check-input status-radio" type="radio" name="status_action" 
                                 id="statusOnProgress" value="on_progress" checked>
                        </div>
                        <div class="rounded-circle bg-warning p-2 d-flex align-items-center justify-content-center me-3" 
                             style="width: 50px; height: 50px;">
                          <i class="bi bi-clock-history text-white fs-4"></i>
                        </div>
                        <div>
                          <h6 class="card-title mb-1 fw-bold text-dark">ON PROGRESS</h6>
                          <small class="text-muted">Anda sedang menangani informasi ini</small>
                        </div>
                      </div>
                      <p class="card-text small text-muted mb-0">
                        <i class="bi bi-info-circle me-1"></i>Tambahkan catatan progress (opsional)
                      </p>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                      <div class="d-flex align-items-center mb-3">
                        <div class="form-check me-3">
                          <input class="form-check-input status-radio" type="radio" name="status_action" 
                                 id="statusClosed" value="closed">
                        </div>
                        <div class="rounded-circle bg-success p-2 d-flex align-items-center justify-content-center me-3" 
                             style="width: 50px; height: 50px;">
                          <i class="bi bi-check-circle-fill text-white fs-4"></i>
                        </div>
                        <div>
                          <h6 class="card-title mb-1 fw-bold text-dark">CLOSED</h6>
                          <small class="text-muted">Selesaikan dan tutup informasi</small>
                        </div>
                      </div>
                      <p class="card-text small text-danger mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i><strong>Catatan wajib diisi</strong>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Three Column Layout for Landscape -->
            <div class="row g-0 h-100">
              <!-- Column 1: Original Information -->
              <div class="col-lg-4 border-end" style="background: #ffffff; min-height: 400px;">
                <div class="p-4 h-100 d-flex flex-column">
                  <h6 class="mb-4 fw-semibold text-success border-bottom pb-3">
                    <i class="bi bi-info-circle-fill me-2"></i>Informasi Asli
                  </h6>
                  
                  <div class="mb-4">
                    <div class="small mb-2 fw-medium text-muted">
                      <i class="bi bi-person-badge me-1"></i>Pengirim
                    </div>
                    <div class="card border-0 shadow-sm">
                      <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                          <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="bi bi-person-fill"></i>
                          </div>
                          <div>
                            <div class="fw-bold fs-5 text-dark" id="display-picfrom">-</div>
                            <small class="text-muted">
                              <i class="bi bi-clock me-1"></i>
                              <span id="display-timefrom">-</span>
                            </small>
                          </div>
                        </div>
                        <div class="text-muted small">
                          <i class="bi bi-calendar me-1"></i>
                          <span id="display-date">-</span>
                          • <span id="days-old-badge" class="badge bg-secondary ms-1">- hari</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-4">
                    <div class="small mb-2 fw-medium text-muted">
                      <i class="bi bi-people-fill me-1"></i>Penerima
                    </div>
                    <div class="card border-0 shadow-sm">
                      <div class="card-body">
                        <div class="fw-bold fs-5 text-dark mb-2" id="display-picto">-</div>
                        <small class="text-muted d-block">
                          <i class="bi bi-person-check me-1"></i>
                          Anda termasuk dalam penerima informasi ini
                        </small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-4">
                    <div class="small mb-2 fw-medium text-muted">
                      <i class="bi bi-info-circle-fill me-1"></i>Status Saat Ini
                    </div>
                    <div class="card border-0 shadow-sm" id="current-status-card">
                      <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                          <div>
                            <div class="fw-bold fs-4 text-dark" id="display-status-text">-</div>
                            <small class="text-muted" id="status-since">-</small>
                          </div>
                          <div id="display-status-icon" class="fs-2">
                            <i class="bi bi-question-circle text-secondary"></i>
                          </div>
                        </div>
                        <div class="progress" style="height: 6px;">
                          <div class="progress-bar" id="status-progress-bar" style="width: 0%"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mt-auto pt-3 border-top">
                    <div class="alert alert-info border-0 py-2" style="background: rgba(59, 130, 246, 0.08);">
                      <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle text-primary me-2"></i>
                        <div class="small">
                          <strong class="text-primary">Retention Policy:</strong> 
                          Informasi aktif selama 7 hari sejak dibuat
                          <div class="mt-1 fw-medium" id="retention-info">-</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Column 2: Content Details -->
              <div class="col-lg-4 border-end" style="background: #fefefe; min-height: 400px;">
                <div class="p-4 h-100 d-flex flex-column">
                  <h6 class="mb-4 fw-semibold text-success border-bottom pb-3">
                    <i class="bi bi-chat-left-text-fill me-2"></i>Detail Konten
                  </h6>
                  
                  <div class="mb-4 flex-grow-1">
                    <div class="small mb-2 fw-medium d-flex align-items-center text-muted">
                      <i class="bi bi-tag-fill me-2 text-success"></i>Judul Item
                      <span class="badge bg-success ms-2">Wajib Dibaca</span>
                    </div>
                    <div class="card border-0 shadow-sm h-100">
                      <div class="card-body">
                        <div class="info-content scrollable-content" id="display-item" 
                             style="white-space: pre-wrap; word-wrap: break-word; color: #1e293b; font-size: 1rem; line-height: 1.5; max-height: 150px; overflow-y: auto;">
                          -
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="flex-grow-1">
                    <div class="small mb-2 fw-medium d-flex align-items-center text-muted">
                      <i class="bi bi-chat-dots-fill me-2 text-success"></i>Permintaan / Pesan
                      <span class="badge bg-success ms-2">Detail</span>
                    </div>
                    <div class="card border-0 shadow-sm h-100">
                      <div class="card-body">
                        <div class="info-content scrollable-content" id="display-request" 
                             style="white-space: pre-wrap; word-wrap: break-word; color: #1e293b; font-size: 1rem; line-height: 1.5; max-height: 200px; overflow-y: auto;">
                          -
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Column 3: Action & Remarks -->
              <div class="col-lg-4" style="background: #ffffff; min-height: 400px;">
                <div class="p-4 h-100 d-flex flex-column">
                  <h6 class="mb-4 fw-semibold text-success border-bottom pb-3">
                    <i class="bi bi-send-check-fill me-2"></i>Tindakan & Respon
                  </h6>
                  
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
                    <small class="form-text text-muted">
                      Waktu saat ini otomatis terisi
                    </small>
                  </div>
                  
                  <!-- Remarks Section -->
                  <div class="flex-grow-1 d-flex flex-column mb-4">
                    <label for="txt-remark-update" class="form-label fw-semibold mb-2 d-flex align-items-center text-dark">
                      <i class="bi bi-chat-square-text-fill me-2 text-success"></i>Catatan / Tindakan Anda
                      <span id="remark-required" class="text-danger ms-1" style="display: none;">*</span>
                    </label>
                    
                    <textarea class="form-control flex-grow-1 shadow-none" 
                              name="txt-remark-update" id="txt-remark-update" 
                              rows="5" placeholder="Tulis catatan atau tindakan yang sudah dilakukan..."
                              style="border: 1px solid #d1d5db; border-left: 4px solid #10b981 !important; resize: none; font-size: 0.95rem; border-radius: 8px;"></textarea>
                    
                    <div class="form-text mt-2 fw-medium text-muted" id="remark-info-text">
                      <i class="bi bi-info-circle me-1"></i>
                      Tambahkan catatan tentang progress atau update terbaru
                    </div>
                  </div>
                  
                  <!-- Submit Button -->
                  <div class="mt-auto pt-3 border-top">
                    <button type="submit" class="btn btn-lg btn-success w-100 py-3 fw-bold"
                            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;"
                            id="btn-submit-reply">
                      <i class="bi bi-send-check-fill me-2"></i>
                      <span id="submit-button-text">Kirim Respon</span>
                    </button>
                    
                    <div class="mt-3 text-center">
                      <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batalkan
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Status Bar -->
      <div class="modal-footer px-4 py-3 border-top" style="background: #f1f5f9;">
        <div class="w-100 d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <img src="./assets/img/logo-denso.png" width="24" alt="DENSO" class="me-2">
            <div>
              <small class="text-muted d-block">Progress BO Control System</small>
              <small class="text-primary fw-medium"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></small>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <span class="badge bg-light text-dark">
              <i class="bi bi-clock me-1"></i>
              <span id="current-time-display"><?php echo date('H:i'); ?></span>
            </span>
            <span class="badge bg-info">
              <i class="bi bi-shield-check me-1"></i>
              Real-time Response
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* Landscape Modal Styles */
.modal-landscape {
  max-width: 1400px !important;
}

.modal-landscape .modal-content {
  min-height: 600px;
  border-radius: 12px;
  overflow: hidden;
}

.modal-landscape .modal-body {
  min-height: 450px;
  max-height: calc(90vh - 120px);
  overflow-y: auto;
}

/* Status Radio Button Styling */
.status-radio:checked {
  background-color: #10b981;
  border-color: #10b981;
}

/* Scrollable Content */
.scrollable-content::-webkit-scrollbar {
  width: 6px;
}

.scrollable-content::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.scrollable-content::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.scrollable-content::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Card Styles */
.card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 10px !important;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
}

/* Form Controls */
.form-control {
  padding: 0.70rem 1rem !important;
  font-size: 0.80rem !important;
  border-radius: 8px !important;
  border: 1px solid #d1d5db;
}

textarea.form-control {
  min-height: 100px;
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

/* Badge Styling */
.badge {
  font-weight: 600;
  letter-spacing: 0.3px;
  padding: 0.35em 0.65em;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .modal-landscape {
    max-width: 95% !important;
    margin: 1rem auto !important;
  }
  
  .row.g-0 > [class*="col-lg-"] {
    border: none !important;
    border-bottom: 1px solid #e2e8f0 !important;
    min-height: auto !important;
  }
}

@media (max-width: 992px) {
  .modal-landscape .modal-dialog {
    max-width: 98% !important;
    margin: 0.5rem !important;
  }
  
  .modal-body .p-4 {
    padding: 1.25rem !important;
  }
  
  .btn-lg {
    padding: 0.75rem 1rem !important;
    font-size: 0.9rem !important;
  }
}

@media (max-width: 768px) {
  .modal-content {
    border-radius: 8px !important;
  }
  
  .col-lg-4, .col-lg-6 {
    padding: 0 !important;
  }
  
  .p-4 {
    padding: 1rem !important;
  }
  
  .modal-header {
    padding: 1rem !important;
  }
  
  .modal-title {
    font-size: 1.1rem !important;
  }
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

/* Status Progress Bar */
#status-progress-bar {
  transition: width 0.5s ease;
}

/* Custom Scrollbar for Modal Body */
.modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Smooth Transitions */
.modal-content,
.modal-body,
.card,
.btn {
  transition: all 0.3s ease;
}

/* Focus States */
.form-control:focus,
textarea:focus {
  border-color: #10b981 !important;
  box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25) !important;
}

/* Hover Effects */
.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Success Toast Override */
.custom-toast {
  border-left: 4px solid #10b981 !important;
}

.custom-toast .toast-icon {
  color: #10b981 !important;
}

/* Error Toast Override */
.custom-toast.error {
  border-left: 4px solid #dc3545 !important;
}

.custom-toast.error .toast-icon {
  color: #dc3545 !important;
}

.week-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    font-size: 0.8em;
    padding: 4px 10px;
    border-radius: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Weekly reset notification */
.weekly-reset-notification {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border: none;
    color: white;
    border-radius: 10px;
    margin-bottom: 15px;
}

/* Week info in modal */
.week-info-panel {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px 15px;
    margin-bottom: 15px;
    border-left: 4px solid #28a745;
}

.week-info-panel .week-title {
    font-weight: 600;
    color: #28a745;
    margin-bottom: 5px;
}

.week-info-panel .week-dates {
    font-size: 0.9em;
    color: #6c757d;
}

/* Highlight untuk data minggu ini */
.this-week-row {
    background-color: rgba(40, 167, 69, 0.05) !important;
    border-left: 3px solid #28a745 !important;
}

.previous-week-row {
    background-color: rgba(108, 117, 125, 0.05) !important;
    opacity: 0.7;
}

/* Notification dengan week info */
.notification-week-tag {
    font-size: 0.7em;
    background: #e9ecef;
    color: #495057;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: 5px;
}

/* Responsive design untuk week info */
@media (max-width: 768px) {
    .week-badge {
        font-size: 0.7em;
        padding: 3px 8px;
    }
    
    .week-info-panel {
        padding: 10px;
        font-size: 0.9em;
    }
}
</style>

<script>
$(document).ready(function() {
  // Handle status radio change
  $('.status-radio').on('change', function() {
    const selectedStatus = $(this).val();
    updateRemarkField(selectedStatus);
  });
  
  function updateRemarkField(status) {
    const $remarkField = $('#txt-remark-update');
    const $requiredStar = $('#remark-required');
    const $infoText = $('#remark-info-text');
    const $submitButton = $('#btn-submit-reply');
    const $submitText = $('#submit-button-text');
    
    if (status === 'closed') {
      // CLOSED - Remark wajib
      $requiredStar.show();
      $remarkField.attr('required', 'required');
      $remarkField.attr('placeholder', 'Contoh: Sudah ditindaklanjuti, masalah sudah selesai, hasilnya...');
      $infoText.html('<i class="bi bi-exclamation-triangle me-1 text-danger"></i><strong class="text-danger">Wajib diisi!</strong> Harap berikan catatan detail sebelum menutup informasi ini.');
      $submitText.text('Tutup Informasi');
      $submitButton.removeClass('btn-warning').addClass('btn-success');
    } else {
      // ON PROGRESS - Remark opsional
      $requiredStar.hide();
      $remarkField.removeAttr('required');
      $remarkField.attr('placeholder', 'Tulis catatan atau tindakan yang sudah dilakukan...');
      $infoText.html('<i class="bi bi-info-circle me-1"></i>Opsional: Tambahkan catatan tentang progress atau update terbaru. Contoh: "Sedang dikonfirmasi ke supplier", "Menunggu respon dari bagian QC"');
      $submitText.text('Simpan sebagai On Progress');
      $submitButton.removeClass('btn-success').addClass('btn-warning');
    }
  }
  
  // Handle form submission
  $('#updateToInformationForm').on('submit', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const selectedStatus = $('input[name="status_action"]:checked').val();
    const remark = $('#txt-remark-update').val().trim();
    
    // Validation
    if (selectedStatus === 'closed' && !remark) {
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
    formData.append('action_type', selectedStatus);
    
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
    const color = type === 'success' ? '#10b981' : '#dc3545';
    
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
  
  // Initialize when modal shows
  $('#modal-update-information-to').on('shown.bs.modal', function() {
    // Default to ON PROGRESS
    $('#statusOnProgress').prop('checked', true).trigger('change');
    updateRemarkField('on_progress');
    
    // Auto-set current time
    const now = new Date();
    const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                      now.getMinutes().toString().padStart(2, '0');
    $('#reply-time-input').val(timeString);
    $('#txt-timeto-update').val(timeString);
  });
  
  // Reset when modal hides
  $('#modal-update-information-to').on('hidden.bs.modal', function() {
    $('.status-radio').prop('checked', false);
    $('#statusOnProgress').prop('checked', true);
    $('#txt-remark-update').val('');
    $('#btn-submit-reply').prop('disabled', false).removeClass('btn-loading');
  });
  
  // Load notification data when modal opens via button click
  $(document).on('click', '.btn-reply-info', function() {
    const infoId = $(this).data('id');
    if (infoId && window.notificationSystem) {
      window.notificationSystem.loadNotificationData(infoId);
    }
  });
});
</script>