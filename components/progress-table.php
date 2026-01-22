<div class="row">
  <div class="col-lg-12">
    <div class="card" style="box-shadow: 3px 3px 15px #ddf6f6;">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0" style="color: black">DETAIL PROGRESS BY PART NUMBER</h5>
          <div class="d-flex gap-2">
            <button class="btn btn-gradient-success" id="btn-modal-ds" style="font-weight: bold">
              <i class="bi bi-sun-fill"></i> D/S
            </button>
            <button class="btn btn-gradient-warning" id="btn-modal-ns" style="font-weight: bold">
              <i class="bi bi-moon-stars-fill"></i> N/S
            </button>
            <button class="btn btn-gradient-danger" id="btn-modal-by-accum" style="font-weight: bold">
              <i class="bx bxs-book-add"></i> Accum
            </button>
            <button id="btn-download-excel" class="btn btn-gradient-excel" style="font-weight: bold">
              <i class="bi bi-file-excel"></i> Download Excel
            </button>
          </div>
        </div>
        
        <hr style="margin-top: -2px">
        
        <div class="table-responsive">
          <table class="table-hover" id="table-detail-progress">  
            <thead style="text-align: center;">  
              <tr>  
                <th rowspan="2">Date</th>  
                <th rowspan="2">Supplier Code</th>  
                <th rowspan="2">Part No</th>  
                <th rowspan="2">Part Name</th>  
                <th colspan="4">D/S Order</th>  
                <th rowspan="2">D/S Actual</th>  
                <th rowspan="2">Status D/S</th>  
                <th colspan="4">N/S Order</th>  
                <th rowspan="2">N/S Actual</th>  
                <th rowspan="2">Status N/S</th>  
                <th rowspan="2">Total Order</th>  
                <th rowspan="2">Total Incoming</th>  
                <th rowspan="2">Status</th>  
                <th rowspan="2">Remark</th>  
              </tr>  
              <tr>  
                <th>Regular</th>  
                <th>Add</th>  
                <th>Update</th>  
                <th>Total</th>  
                <th>Regular</th>  
                <th>Add</th>  
                <th>Update</th>  
                <th>Total</th>  
              </tr>  
            </thead>  
            <tbody style="text-align: center">  
            </tbody>  
          </table>
        </div>
      </div>
    </div>
  </div>
</div>


<style>
  /* ================= BUTTON STYLING ================= */
.btn-gradient-success {
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(40, 167, 69, 0.3);
}

.btn-gradient-success:hover {
    background: linear-gradient(45deg, #218838, #1ba87e);
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(40, 167, 69, 0.4);
}

.btn-gradient-warning {
    background: linear-gradient(45deg, #fd7e14, #ffc107);
    color: white;
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(253, 126, 20, 0.3);
}

.btn-gradient-warning:hover {
    background: linear-gradient(45deg, #e76e00, #e0a800);
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(253, 126, 20, 0.4);
}

.btn-gradient-danger {
    background: linear-gradient(45deg, #dc3545, #e83e8c);
    color: white;
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(220, 53, 69, 0.3);
}

.btn-gradient-danger:hover {
    background: linear-gradient(45deg, #c82333, #d81b60);
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(220, 53, 69, 0.4);
}

.btn-gradient-excel {
    background: linear-gradient(45deg, #1d6f42, #28a745);
    color: white;
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(29, 111, 66, 0.3);
}

.btn-gradient-excel:hover {
    background: linear-gradient(45deg, #165a36, #218838);
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(29, 111, 66, 0.4);
}

.btn-gradient-success i,
.btn-gradient-warning i,
.btn-gradient-danger i,
.btn-gradient-excel i {
    margin-right: 5px;
}


</style>