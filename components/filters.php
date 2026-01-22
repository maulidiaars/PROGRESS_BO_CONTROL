<section class="section" style="position: relative; z-index: 100;">
  <div class="row align-items-stretch" style="position: relative; z-index: 100;">
    
    <!-- PIC Filter -->
    <div class="col-lg-4 d-flex">
      <div class="card flex-fill" style="box-shadow: 3px 3px 15px #ddf6f6; position: relative; z-index: 100; overflow: visible !important;">
        <div class="card-body" style="position: relative; z-index: 100; overflow: visible !important;">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0" style="color: black">PIC</h5>
          </div>
          <hr style="margin-top: -2px">
          <div class="col-12" style="position: relative; z-index: 100;">
            <div class="selectize-wrapper" style="position: relative; min-height: 46px;">
              <select id="select-pic" class="form-control">
                <option value=""></option>
                <option value="select-all">Select All</option>
                <option value="SATRIO">SATRIO</option>
                <option value="EKO">EKO</option>
                <option value="EKA">EKA</option>
                <option value="MURSID">MURSID</option>
                <option value="ALBERTO">ALBERTO</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- SUPPLIER CODE Filter -->
    <div class="col-lg-4 d-flex">
      <div class="card flex-fill" style="box-shadow: 3px 3px 15px #ddf6f6; position: relative; z-index: 100; overflow: visible !important;">
        <div class="card-body" style="position: relative; z-index: 100; overflow: visible !important;">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0" style="color: black">SUPPLIER CODE</h5>
          </div>
          <hr style="margin-top: -2px">
          <div class="col-12" style="position: relative; z-index: 100;">
            <div class="selectize-wrapper" style="position: relative; min-height: 46px;">
              <select id="select-supplier-code" class="form-control">
                <option value=""></option>
                <option value="select-all">Select All</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- STATUS Filter -->
    <!-- STATUS Filter -->
    <div class="col-lg-4 d-flex">
      <div class="card flex-fill" style="box-shadow: 3px 3px 15px #ddf6f6; position: relative; z-index: 100; overflow: visible !important;">
        <div class="card-body" style="position: relative; z-index: 100; overflow: visible !important;">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0" style="color: black">STATUS</h5>
          </div>
          <hr style="margin-top: -2px">
          <div class="col-12" style="position: relative; z-index: 100;">
            <div class="selectize-wrapper" style="position: relative; min-height: 46px;">
              <select id="select-status" class="form-control">
                <option value=""></option>
                <option value="select-all">Select All</option>
                <option value="OK">✅ OK</option>
                <option value="ON_PROGRESS">🔄 ON PROGRESS</option>
                <option value="DELAY">⚠️ DELAY</option>
                <option value="OVER">📈 OVER</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    
  </div>
</section>

<style>
/* Additional inline CSS for filters */
.selectize-wrapper {
    overflow: visible !important;
}

.selectize-wrapper .selectize-control {
    overflow: visible !important;
}

.selectize-wrapper .selectize-input {
    z-index: 1000;
    position: relative;
}

.selectize-dropdown {
    z-index: 99999 !important;
}
</style>