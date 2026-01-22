<!-- components/information-section.php -->
<div class="row">
  <div class="col-lg-12">
    <div class="card" style="box-shadow: 3px 3px 15px #ddf6f6;">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0" style="color: black">
            INFORMATION 
            <span id="info-badge" class="badge bg-danger ms-2" style="display: none; font-size: 0.7em; vertical-align: middle">0</span>
          </h5>
          <div class="d-flex gap-2">
            <button class="btn btn-dark" id="btn-refresh-information" style="font-weight: bold; background-color: #1c2e4a">
              <i class="bx bx-refresh" style="font-size: 20px"></i>
            </button>
            <button class="btn btn-primary" id="btn-add-information" style="font-weight: bold">
              <i class="bx bx-notepad"></i> ADD INFORMATION
            </button>
          </div>
        </div>
        
        <hr style="margin-top: -2px">
        
        <table class="table table-hover" id="table-information" style="width:100%">
          <thead style="white-space: nowrap; text-align: center">
            <tr>
              <th rowspan="2">No</th>
              <th rowspan="2">Date</th>
              <th colspan="4">From</th>
              <th rowspan="2">Action</th>
              <th colspan="4">To</th>
              <th rowspan="2">Action</th>
            </tr>
            <tr>
              <th>Time</th>
              <th>PIC</th>
              <th>Item</th>
              <th>Request</th>
              <th>Time</th>
              <th>PIC</th>
              <th>Status</th>
              <th>Remark</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>