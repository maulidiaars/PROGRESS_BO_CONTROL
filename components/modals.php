<!-- Modal Upload -->
<div class="modal fade" id="modal-upload" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-primary text-white">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-cloud-upload-fill me-2"></i>
          UPLOAD DATA
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <form class="uploadForm" method="post" enctype="multipart/form-data">
        <div class="modal-body p-4">
          <!-- File Type Selection -->
          <div class="mb-4">
            <label class="form-label fw-semibold">
              <i class="bi bi-file-earmark-spreadsheet me-1"></i>
              Select Upload Type
            </label>
            <select class="form-select" name="type" required>
              <option value="" selected disabled>-- Select Data Type --</option>
              <option value="upload_bo" data-icon="📊">Update BO (Back Order)</option>
              <option value="upload_order" data-icon="📦">Order Data</option>
              <option value="upload_part" data-icon="🔧">Master Part Number</option>
            </select>
            <div class="form-text">
              <small>
                <i class="bi bi-info-circle me-1"></i>
                Choose the type of data you want to upload
              </small>
            </div>
          </div>
          
          <!-- File Upload -->
          <div class="mb-4">
            <label class="form-label fw-semibold">
              <i class="bi bi-file-arrow-up me-1"></i>
              Select Excel File
            </label>
            <div class="file-upload-area border rounded p-4 text-center" 
                 id="fileDropArea" 
                 style="border-style: dashed !important; background-color: #f8f9fa;">
              <i class="bi bi-cloud-arrow-up display-4 text-muted mb-3"></i>
              <h6 class="mb-2">Drag & Drop your file here</h6>
              <p class="text-muted small mb-3">or click to browse</p>
              <input class="form-control" type="file" name="file" required 
                     id="fileInput" 
                     accept=".xls,.xlsx,.csv"
                     style="display: none;">
              <button type="button" class="btn btn-outline-primary" id="browseBtn">
                <i class="bi bi-folder2-open me-2"></i>Browse Files
              </button>
              <div class="mt-3" id="fileName"></div>
            </div>
            
            <!-- File Requirements -->
            <div class="alert alert-info mt-3">
              <div class="d-flex">
                <i class="bi bi-info-circle-fill me-2"></i>
                <div>
                  <small class="fw-bold">Supported formats:</small>
                  <ul class="mb-0 ps-3 small">
                    <li>Excel (.xls, .xlsx)</li>
                    <li>CSV (.csv)</li>
                  </ul>
                  <small class="text-muted">Maximum file size: 20MB</small>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Preview Area (Optional) -->
          <div class="collapse" id="filePreview">
            <div class="card">
              <div class="card-header bg-light">
                <h6 class="mb-0">File Preview</h6>
              </div>
              <div class="card-body">
                <div id="previewContent" class="small">
                  <!-- Preview will be loaded here -->
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer bg-light">
          <div class="me-auto">
            <img src="./assets/img/logo-denso.png" alt="DENSO" width="90" height="40">
          </div>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-primary px-4" id="uploadSubmitBtn">
            <i class="bi bi-upload me-2"></i>
            <span>Upload Data</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
/* Upload Modal Styles */
#fileDropArea {
  transition: all 0.3s;
  cursor: pointer;
}

#fileDropArea:hover {
  background-color: #e9ecef !important;
  border-color: #007bff !important;
}

#fileDropArea.dragover {
  background-color: #e3f2fd !important;
  border-color: #2196f3 !important;
  transform: scale(1.01);
}

.file-preview {
  max-height: 200px;
  overflow-y: auto;
}

.file-preview table {
  font-size: 12px;
}

.file-preview th {
  background-color: #f8f9fa;
  position: sticky;
  top: 0;
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #0066cc 0%, #003399 100%) !important;
}

.upload-status {
  display: none;
}

.upload-status.active {
  display: block;
  animation: fadeIn 0.3s;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>

<script>
// Drag & Drop functionality
$(document).ready(function() {
  const fileDropArea = $('#fileDropArea');
  const fileInput = $('#fileInput');
  const browseBtn = $('#browseBtn');
  const fileName = $('#fileName');
  const typeSelect = $('select[name="type"]');
  
  // Browse button click
  browseBtn.on('click', function() {
    fileInput.click();
  });
  
  // File input change
  fileInput.on('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      displayFileInfo(file);
    }
  });
  
  // Drag & drop events
  fileDropArea.on('dragover', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).addClass('dragover');
  });
  
  fileDropArea.on('dragleave', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).removeClass('dragover');
  });
  
  fileDropArea.on('drop', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).removeClass('dragover');
    
    const files = e.originalEvent.dataTransfer.files;
    if (files.length > 0) {
      fileInput[0].files = files;
      displayFileInfo(files[0]);
    }
  });
  
  // Display file info
  function displayFileInfo(file) {
    const fileSize = (file.size / 1024 / 1024).toFixed(2);
    fileName.html(`
      <div class="alert alert-success py-2">
        <div class="d-flex align-items-center">
          <i class="bi bi-file-earmark-excel-fill text-success me-2"></i>
          <div>
            <strong>${file.name}</strong>
            <br>
            <small class="text-muted">${fileSize} MB • ${file.type || 'Unknown type'}</small>
          </div>
        </div>
      </div>
    `);
  }
  
  // Reset form when modal closes
  $('#modal-upload').on('hidden.bs.modal', function() {
    $('.uploadForm')[0].reset();
    fileName.empty();
    $('#filePreview').collapse('hide');
  });
});
</script>