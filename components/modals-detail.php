<!-- Modal By Cycle -->
<div class="modal fade detail-ds" id="modal-by-cycle" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-table">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <span style="font-size: 25px; font-weight: bold">Process By Cycle</span>
          <br>
          <span style="font-size: 14px; font-weight: bold; color: blue" id="txt-rangedate-cycle"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 14px;" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-drag-container">
          <table class="table-hover w-100" id="table-by-cycle">
            <thead style="text-align: center; background-color: #f8f9fa; position: sticky; top: 0; z-index: 1;"> 
              <tr>
                <th style="width: 40px;">No.</th>
                <th style="min-width: 80px;">Date</th>
                <th style="min-width: 100px;">Supplier Code</th>
                <th style="min-width: 100px;">Part No</th>
                <th style="min-width: 150px;">Part Name</th>
                <th style="min-width: 50px;">C1</th>
                <th style="min-width: 50px;">C2</th>
                <th style="min-width: 50px;">C3</th>
                <th style="min-width: 50px;">C4</th>
                <th style="min-width: 50px;">C5</th>
                <th style="min-width: 50px;">C6</th>
                <th style="min-width: 50px;">C7</th>
                <th style="min-width: 50px;">C8</th>
                <th style="min-width: 50px;">C9</th>
                <th style="min-width: 50px;">C10</th>
                <th style="min-width: 50px;">C11</th>
                <th style="min-width: 50px;">C12</th>
                <th style="min-width: 60px;">Total</th>
              </tr>
            </thead>
            <tbody style="text-align: center;"></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <img src="./assets/img/logo-denso.png" width="90px" height="40px" class="me-auto">
        <button type="button" style="font-size: 15px;" class="btn btn-warning btn-back-component" data-bs-dismiss="modal">Back</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal By Accum -->
<div class="modal fade detail-ds" id="modalByAccum" tabindex="-1">  
  <div class="modal-dialog modal-xl modal-dialog-centered modal-table">  
    <div class="modal-content">  
      <div class="modal-header">  
        <h5 class="modal-title">  
          <span style="font-size: 25px; font-weight: bold">Accum Table</span>  
          <br>  
          <span style="font-size: 14px; font-weight: bold; color: blue" id="txt-selected-month"></span>  
        </h5>  
        <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 14px;" aria-label="Close"></button>  
      </div>  
      <div class="modal-body">  
        <div class="row mb-2"> 
          <div class="col-md-2">  
            <select id="select-month" class="form-select"></select>  
          </div>  
          <div class="col-md-2">  
            <input type="text" id="accum-search" class="form-control" placeholder="Search supplier, part no, or name...">  
          </div>  
          <div class="col-md-2">  
            <select id="accum-sort" class="form-select">  
              <option value="SUPPLIER_CODE">Sort by Supplier Code</option>  
              <option value="SUPPLIER_NAME">Sort by Supplier Name</option>  
              <option value="PART_NO">Sort by Part No</option>  
              <option value="PART_NAME">Sort by Part Name</option>  
            </select>  
          </div>  
          <div class="col-md-2">  
            <select id="accum-pagesize" class="form-select">  
              <option value="10">10 rows/page</option>  
              <option value="20">20 rows/page</option>  
              <option value="50">50 rows/page</option>  
            </select>  
          </div>  
          <div class="col-md-2">  
            <button type="button" style="font-size: 15px;" class="btn btn-success" id="btn-download-accum-excel">  
              <i class="bx bx-download"></i> Download Excel  
            </button>  
          </div>  
        </div>  
        <div class="table-responsive accum-table-container">  
          <div id="accum-table-container" style="position: relative;"></div>  
        </div>  
      </div>  
      <div class="modal-footer">  
        <img src="./assets/img/logo-denso.png" width="90px" height="40px" class="me-auto"> 
        <span id="accum-pagination"></span>  
        <button type="button" style="font-size: 15px;" class="btn btn-warning btn-back-component" data-bs-dismiss="modal">Back</button>  
      </div>  
    </div>  
  </div>  
</div>

<!-- Modal Detail D/S (REVISI) -->
<div class="modal fade detail-ds" id="modal-detail-ds" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-table">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <span style="font-size:25px;font-weight:bold">Day Shift</span><br>
          <span style="font-size:14px;font-weight:bold;color:blue" id="txt-rangedate-day"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- SEARCH BAR BARU -->
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-primary text-white">
                <i class="bi bi-search"></i>
              </span>
              <input type="text" class="form-control" id="ds-search-input" 
                     placeholder="Search by Date, Supplier, Part No, Part Name...">
              <button class="btn btn-outline-secondary" type="button" id="ds-clear-search">
                <i class="bi bi-x"></i>
              </button>
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select" id="ds-page-size">
              <option value="10">Show 10</option>
              <option value="25">Show 25</option>
              <option value="50">Show 50</option>
              <option value="100">Show 100</option>
              <option value="500">Show 500</option>
            </select>
          </div>
          <div class="col-md-3 text-end">
            <div class="badge bg-info text-dark p-2" id="ds-result-count">
              Loading...
            </div>
          </div>
        </div>

        <!-- CONTAINER TABEL DENGAN FIXED HEADER -->
        <div class="table-fixed-container" id="ds-table-container">
          <div class="table-fixed-wrapper">
            <table class="table table-hover" id="table-detail-ds">
              <thead class="table-light text-center">
                <tr>
                  <th style="width: 50px;">No</th>
                  <th style="min-width: 100px;">Date</th>
                  <th style="min-width: 120px;">Supplier Code</th>
                  <th style="min-width: 120px;">Part No</th>
                  <th style="min-width: 200px;">Part Name</th>
                  <th style="min-width: 100px;">Item</th>
                  <th style="min-width: 60px;">8:00</th>
                  <th style="min-width: 60px;">9:00</th>
                  <th style="min-width: 60px;">10:00</th>
                  <th style="min-width: 60px;">11:00</th>
                  <th style="min-width: 60px;">12:00</th>
                  <th style="min-width: 60px;">13:00</th>
                  <th style="min-width: 60px;">14:00</th>
                  <th style="min-width: 60px;">15:00</th>
                  <th style="min-width: 60px;">16:00</th>
                  <th style="min-width: 60px;">17:00</th>
                  <th style="min-width: 60px;">18:00</th>
                  <th style="min-width: 60px;">19:00</th>
                  <th style="min-width: 60px;">20:00</th>
                  <th style="min-width: 80px;">Total</th>
                </tr>
              </thead>
              <tbody class="text-center"></tbody>
            </table>
          </div>
        </div>

        <!-- PAGINATION BARU -->
        <div class="row mt-3">
          <div class="col-md-6">
            <nav aria-label="Page navigation">
              <ul class="pagination pagination-sm" id="ds-pagination">
                <li class="page-item disabled" id="ds-prev">
                  <a class="page-link" href="#">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item disabled" id="ds-next">
                  <a class="page-link" href="#">Next</a>
                </li>
              </ul>
            </nav>
          </div>
          <div class="col-md-6 text-end">
            <div class="text-muted small" id="ds-page-info">
              Page 1 of 1
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <img src="./assets/img/logo-denso.png" width="90">
        <div class="ms-auto">
          <button class="btn btn-info btn-sm me-2" id="ds-export-btn">
            <i class="bi bi-download"></i> Export
          </button>
          <button class="btn btn-warning" data-bs-dismiss="modal">Back</button>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Modal Detail N/S (REVISI) -->
<div class="modal fade detail-ns" id="modal-detail-ns" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-table">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <span style="font-size:25px;font-weight:bold">Night Shift</span><br>
          <span style="font-size:14px;font-weight:bold;color:blue" id="txt-rangedate-night"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- SEARCH BAR BARU -->
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-warning text-dark">
                <i class="bi bi-search"></i>
              </span>
              <input type="text" class="form-control" id="ns-search-input" 
                     placeholder="Search by Date, Supplier, Part No, Part Name...">
              <button class="btn btn-outline-secondary" type="button" id="ns-clear-search">
                <i class="bi bi-x"></i>
              </button>
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select" id="ns-page-size">
              <option value="10">Show 10</option>
              <option value="25">Show 25</option>
              <option value="50">Show 50</option>
              <option value="100">Show 100</option>
              <option value="500">Show 500</option>
            </select>
          </div>
          <div class="col-md-3 text-end">
            <div class="badge bg-info text-dark p-2" id="ns-result-count">
              Loading...
            </div>
          </div>
        </div>

        <!-- CONTAINER TABEL DENGAN FIXED HEADER -->
        <div class="table-fixed-container" id="ns-table-container">
          <div class="table-fixed-wrapper">
            <table class="table table-hover" id="table-detail-ns">
              <thead class="table-light text-center">
                <tr>
                  <th style="width: 50px;">No</th>
                  <th style="min-width: 100px;">Date</th>
                  <th style="min-width: 120px;">Supplier Code</th>
                  <th style="min-width: 120px;">Part No</th>
                  <th style="min-width: 200px;">Part Name</th>
                  <th style="min-width: 100px;">Item</th>
                  <th style="min-width: 60px;">21:00</th>
                  <th style="min-width: 60px;">22:00</th>
                  <th style="min-width: 60px;">23:00</th>
                  <th style="min-width: 60px;">0:00</th>
                  <th style="min-width: 60px;">1:00</th>
                  <th style="min-width: 60px;">2:00</th>
                  <th style="min-width: 60px;">3:00</th>
                  <th style="min-width: 60px;">4:00</th>
                  <th style="min-width: 60px;">5:00</th>
                  <th style="min-width: 60px;">6:00</th>
                  <th style="min-width: 60px;">7:00</th>
                  <th style="min-width: 80px;">Total</th>
                </tr>
              </thead>
              <tbody class="text-center"></tbody>
            </table>
          </div>
        </div>

        <!-- PAGINATION BARU -->
        <div class="row mt-3">
          <div class="col-md-6">
            <nav aria-label="Page navigation">
              <ul class="pagination pagination-sm" id="ns-pagination">
                <li class="page-item disabled" id="ns-prev">
                  <a class="page-link" href="#">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item disabled" id="ns-next">
                  <a class="page-link" href="#">Next</a>
                </li>
              </ul>
            </nav>
          </div>
          <div class="col-md-6 text-end">
            <div class="text-muted small" id="ns-page-info">
              Page 1 of 1
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <img src="./assets/img/logo-denso.png" width="90">
        <div class="ms-auto">
          <button class="btn btn-info btn-sm me-2" id="ns-export-btn">
            <i class="bi bi-download"></i> Export
          </button>
          <button class="btn btn-warning" data-bs-dismiss="modal">Back</button>
        </div>
      </div>

    </div>
  </div>
</div>


<style>
/* STYLE UNTUK TABEL FIXED HEADER DAN DRAG SCROLL */
.table-fixed-container {
    position: relative;
    max-height: 60vh;
    overflow: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
}

.table-fixed-wrapper {
    position: relative;
}

/* HEADER TETAP DI ATAS SAAT SCROLL */
.table-fixed-container thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa !important;
    z-index: 10;
    border-bottom: 2px solid #dee2e6 !important;
}

/* WARNA KONSISTEN UNTUK SEMUA DATA */
.table-fixed-container tbody tr {
    background-color: white !important;
}

.table-fixed-container tbody tr:nth-child(odd) {
    background-color: #f8f9fa !important;
}

.table-fixed-container tbody tr:hover {
    background-color: #e9ecef !important;
}

/* WARNA UNTUK ORDER DAN INCOMING */
.table-order-row td {
    background-color: rgba(13, 110, 253, 0.1) !important;
    color: #000 !important;
    border-color: rgba(13, 110, 253, 0.2) !important;
}

.table-incoming-row td {
    background-color: rgba(25, 135, 84, 0.1) !important;
    color: #000 !important;
    border-color: rgba(25, 135, 84, 0.2) !important;
}

/* STYLE UNTUK DRAG SCROLL */
.table-fixed-container {
    cursor: grab;
}

.table-fixed-container:active {
    cursor: grabbing;
}

/* SCROLLBAR STYLE */
.table-fixed-container::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

.table-fixed-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 5px;
}

.table-fixed-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 5px;
}

.table-fixed-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* BADGE STYLE */
.badge-order {
    background-color: #0d6efd !important;
    color: white !important;
}

.badge-incoming {
    background-color: #198754 !important;
    color: white !important;
}

/* PAGINATION ACTIVE */
.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

/* SEARCH INPUT FOCUS */
#ds-search-input:focus, #ns-search-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* ANIMASI LOADING */
.table-loading-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .table-fixed-container {
        max-height: 50vh;
    }
    
    .table-fixed-container th,
    .table-fixed-container td {
        font-size: 12px;
        padding: 6px !important;
    }
}
</style>