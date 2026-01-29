<!-- Modal Add D/S -->
<div class="modal fade" id="modal-add-ds" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Add Order Day Shift
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="form-add-ds">
                <div class="modal-body">
                    <!-- Info Data -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Date:</strong><br>
                                <span id="txt-ds-date" class="fw-bold">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Supplier:</strong><br>
                                <span id="txt-ds-supplier" class="fw-bold">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Part No:</strong><br>
                                <span id="txt-ds-partno" class="fw-bold">-</span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <strong>Part Name:</strong><br>
                                <span id="txt-ds-partname" class="fw-bold">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Status -->
                    <div class="alert alert-light mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Current Status:</strong>
                                <span id="ds-status-text" class="ms-2">Loading...</span>
                            </div>
                            <div id="btn-reset-ds" style="display: none;">
                                <button type="button" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset to 0
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alert Messages -->
                    <div class="alert alert-success d-none" id="ds-success-alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <span id="ds-success-message"></span>
                    </div>
                    
                    <div class="alert alert-danger d-none" id="ds-error-alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="ds-error-message"></span>
                    </div>
                    
                    <!-- Hour Selection -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-clock me-2"></i>Select Hours (7:00 - 20:00)
                                <small class="text-muted ms-2">
                                    Current time: <span id="current-time-display">00:00</span>
                                </small>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row" id="ds-hour-selection">
                                <!-- Hours akan di-generate oleh JavaScript -->
                            </div>
                            <div class="text-muted small mt-2">
                                <i class="bi bi-info-circle"></i> Jam yang sudah lewat akan dinonaktifkan
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quantity Inputs -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-box-seam me-2"></i>Quantity per Hour
                                <span class="badge bg-primary float-end">
                                    Total: <span id="ds-total-qty">0</span> pcs
                                </span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="ds-quantity-container">
                                <div class="alert alert-info" id="ds-no-hour-selected">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Pilih jam terlebih dahulu di atas
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Remark -->
                    <div class="mb-3">
                        <label for="txt-ds-remark" class="form-label">
                            <i class="bi bi-chat-text me-1"></i>Remark
                        </label>
                        <textarea class="form-control" id="txt-ds-remark" rows="3" 
                                  placeholder="Contoh: Ada tambahan order dari purchasing..."></textarea>
                        <div class="form-text">Remark akan tetap tersimpan meskipun quantity direset ke 0</div>
                    </div>
                    
                    <!-- Hidden Fields -->
                    <input type="hidden" id="add-ds-date" name="date">
                    <input type="hidden" id="add-ds-supplier" name="supplier_code">
                    <input type="hidden" id="add-ds-partno" name="part_no">
                    <input type="hidden" id="ds-action" name="action" value="add">
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="ds-submit-btn">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="ds-spinner"></span>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add N/S -->
<div class="modal fade" id="modal-add-ns" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Add Order Night Shift
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="form-add-ns">
                <div class="modal-body">
                    <!-- Info Data -->
                    <div class="alert alert-warning">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Date:</strong><br>
                                <span id="txt-ns-date" class="fw-bold">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Supplier:</strong><br>
                                <span id="txt-ns-supplier" class="fw-bold">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Part No:</strong><br>
                                <span id="txt-ns-partno" class="fw-bold">-</span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <strong>Part Name:</strong><br>
                                <span id="txt-ns-partname" class="fw-bold">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Status -->
                    <div class="alert alert-light mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Current Status:</strong>
                                <span id="ns-status-text" class="ms-2">Loading...</span>
                            </div>
                            <div id="btn-reset-ns" style="display: none;">
                                <button type="button" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset to 0
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alert Messages -->
                    <div class="alert alert-success d-none" id="ns-success-alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <span id="ns-success-message"></span>
                    </div>
                    
                    <div class="alert alert-danger d-none" id="ns-error-alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="ns-error-message"></span>
                    </div>
                    
                    <!-- Hour Selection -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-moon me-2"></i>Select Hours (21:00 - 6:00)
                                <small class="text-muted ms-2">
                                    Current time: <span id="ns-current-time-display">00:00</span>
                                </small>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row" id="ns-hour-selection">
                                <!-- Hours akan di-generate oleh JavaScript -->
                            </div>
                            <div class="text-muted small mt-2">
                                <i class="bi bi-info-circle"></i> Jam 0-6 hanya tersedia setelah jam 21:00
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quantity Inputs -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-box-seam me-2"></i>Quantity per Hour
                                <span class="badge bg-warning float-end">
                                    Total: <span id="ns-total-qty">0</span> pcs
                                </span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="ns-quantity-container">
                                <div class="alert alert-info" id="ns-no-hour-selected">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Pilih jam terlebih dahulu di atas
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Remark -->
                    <div class="mb-3">
                        <label for="txt-ns-remark" class="form-label">
                            <i class="bi bi-chat-text me-1"></i>Remark
                        </label>
                        <textarea class="form-control" id="txt-ns-remark" rows="3" 
                                  placeholder="Contoh: Tambahan order untuk shift malam..."></textarea>
                        <div class="form-text">Remark akan tetap tersimpan meskipun quantity direset ke 0</div>
                    </div>
                    
                    <!-- Hidden Fields -->
                    <input type="hidden" id="add-ns-date" name="date">
                    <input type="hidden" id="add-ns-supplier" name="supplier_code">
                    <input type="hidden" id="add-ns-partno" name="part_no">
                    <input type="hidden" id="ns-action" name="action" value="add">
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning" id="ns-submit-btn">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="ns-spinner"></span>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Style untuk hour buttons */
.hour-btn {
    width: 50px;
    height: 40px;
    font-weight: bold;
}

.hour-btn.selected {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Quantity input groups */
.quantity-input-group {
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background-color: #f8f9fa;
}

.quantity-input-group:hover {
    background-color: #e9ecef;
}
</style>