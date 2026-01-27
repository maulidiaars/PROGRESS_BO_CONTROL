<!-- components/charts-section.php -->
<div class="row">
  <div class="col-lg-12">
    <div class="card analytics-card">
      <div class="card-body">
        <!-- HEADER DENGAN TOGGLE COLLAPSE -->
        <div class="analytics-header">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
              <div class="header-icon-wrapper me-3">
                <i class="bi bi-bar-chart-line-fill"></i>
              </div>
              <div>
                <h4 class="mb-0 analytics-title">
                  <span>Delivery Performance Analytics</span>
                  <i class="bi bi-chevron-down ms-2 collapse-section-icon" id="sectionCollapseIcon" onclick="toggleSectionCollapse(event)"></i>
                </h4>
                <div class="analytics-subtitle small">
                  Real-time monitoring and trend analysis
                </div>
              </div>
            </div>
            <div class="d-flex gap-2 align-items-center">
              <div class="last-updated" id="lastUpdated">
                <i class="bi bi-clock-history me-1"></i>
                <span>Just now</span>
              </div>
              <button class="btn btn-sm btn-refresh-full" onclick="refreshAllCharts()" title="Refresh All">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
              </button>
            </div>
          </div>
        </div>

        <!-- KONTEN UTAMA YANG BISA DI-COLLAPSE -->
        <div class="analytics-content collapsed" id="analyticsContent">
          
          <!-- TABS NAVIGATION -->
          <div class="analytics-tabs">
            <ul class="nav nav-tabs" id="analyticsTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="trend-tab" data-bs-toggle="tab" data-bs-target="#trend-content" type="button" role="tab">
                  <i class="bi bi-graph-up me-2"></i>
                  <span>Performance Trend</span>
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="shift-tab" data-bs-toggle="tab" data-bs-target="#shift-content" type="button" role="tab">
                  <i class="bi bi-pie-chart-fill me-2"></i>
                  <span>Shift Comparison</span>
                </button>
              </li>
            </ul>
          </div>

          <!-- TAB CONTENT -->
          <div class="tab-content mt-4" id="analyticsTabContent">
            
            <!-- TAB 1: PERFORMANCE TREND -->
            <div class="tab-pane fade show active" id="trend-content" role="tabpanel">
              <div class="row">
                <div class="col-lg-12">
                  <div class="trend-controls mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex gap-3">
                        <div class="control-group">
                          <label class="control-label">Time Range:</label>
                          <div class="custom-select-wrapper">
                            <select class="form-select form-select-sm" id="trendRange">
                              <option value="7">Last 7 Days</option>
                              <option value="14">Last 14 Days</option>
                              <option value="30" selected>Last 30 Days</option>
                              <option value="90">Last 90 Days</option>
                            </select>
                            <i class="bi bi-chevron-down select-arrow"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- GRAFIK UTAMA - BAR CHART MEWAH -->
                  <div class="trend-chart-container">
                    <div id="trendChart" style="height: 380px;"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- TAB 2: SHIFT COMPARISON -->
            <div class="tab-pane fade" id="shift-content" role="tabpanel">
              <div class="row">
                <div class="col-lg-12">
                  <!-- FILTER SHIFT -->
                  <div class="shift-controls mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="month-display-large">
                        <i class="bi bi-calendar-month me-2"></i>
                        <span id="currentMonth" class="month-text">Januari 2024</span>
                        <small class="date-range ms-2" id="shiftDateRange">(01 - 31 Jan 2024)</small>
                      </div>
                      <div class="d-flex gap-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="downloadShiftReport()">
                          <i class="bi bi-download me-1"></i> Export
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- 3 GRAFIK SHIFT -->
                  <div class="shift-charts-container">
                    <div class="row g-4">
                      <!-- DAY SHIFT -->
                      <div class="col-lg-4">
                        <div class="shift-card">
                          <div class="shift-header">
                            <div class="d-flex align-items-center">
                              <div class="shift-icon sun">
                                <i class="bi bi-sun-fill"></i>
                              </div>
                              <div class="shift-info">
                                <h5>Day Shift</h5>
                                <div class="shift-time">07:00 - 20:00</div>
                              </div>
                            </div>
                            <div class="shift-percentage" id="dsPercentage">
                              <span class="percentage">0%</span>
                              <div class="percentage-label">Completion</div>
                            </div>
                          </div>
                          <div class="shift-chart-wrapper">
                            <canvas id="dsComparisonChart"></canvas>
                          </div>
                          <div class="shift-stats">
                            <div class="stats-grid">
                              <div class="stat-box ok">
                                <div class="stat-label">OK</div>
                                <div class="stat-value" id="dsOkCount">0</div>
                                <div class="stat-percentage" id="dsOkPercent">0%</div>
                              </div>
                              <div class="stat-box on-progress">
                                <div class="stat-label">ON</div>
                                <div class="stat-value" id="dsOnCount">0</div>
                                <div class="stat-percentage" id="dsOnPercent">0%</div>
                              </div>
                              <div class="stat-box over">
                                <div class="stat-label">OVER</div>
                                <div class="stat-value" id="dsOverCount">0</div>
                                <div class="stat-percentage" id="dsOverPercent">0%</div>
                              </div>
                              <div class="stat-box delay">
                                <div class="stat-label">DELAY</div>
                                <div class="stat-value" id="dsDelayCount">0</div>
                                <div class="stat-percentage" id="dsDelayPercent">0%</div>
                              </div>
                            </div>
                          </div>
                          <div class="shift-footer">
                            <div class="delivery-info">
                              <i class="bi bi-truck me-1"></i>
                              <span id="dsDeliveryCount">0</span> deliveries
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- NIGHT SHIFT -->
                      <div class="col-lg-4">
                        <div class="shift-card">
                          <div class="shift-header">
                            <div class="d-flex align-items-center">
                              <div class="shift-icon moon">
                                <i class="bi bi-moon-fill"></i>
                              </div>
                              <div class="shift-info">
                                <h5>Night Shift</h5>
                                <div class="shift-time">21:00 - 06:00</div>
                              </div>
                            </div>
                            <div class="shift-percentage" id="nsPercentage">
                              <span class="percentage">0%</span>
                              <div class="percentage-label">Completion</div>
                            </div>
                          </div>
                          <div class="shift-chart-wrapper">
                            <canvas id="nsComparisonChart"></canvas>
                          </div>
                          <div class="shift-stats">
                            <div class="stats-grid">
                              <div class="stat-box ok">
                                <div class="stat-label">OK</div>
                                <div class="stat-value" id="nsOkCount">0</div>
                                <div class="stat-percentage" id="nsOkPercent">0%</div>
                              </div>
                              <div class="stat-box on-progress">
                                <div class="stat-label">ON</div>
                                <div class="stat-value" id="nsOnCount">0</div>
                                <div class="stat-percentage" id="nsOnPercent">0%</div>
                              </div>
                              <div class="stat-box over">
                                <div class="stat-label">OVER</div>
                                <div class="stat-value" id="nsOverCount">0</div>
                                <div class="stat-percentage" id="nsOverPercent">0%</div>
                              </div>
                              <div class="stat-box delay">
                                <div class="stat-label">DELAY</div>
                                <div class="stat-value" id="nsDelayCount">0</div>
                                <div class="stat-percentage" id="nsDelayPercent">0%</div>
                              </div>
                            </div>
                          </div>
                          <div class="shift-footer">
                            <div class="delivery-info">
                              <i class="bi bi-truck me-1"></i>
                              <span id="nsDeliveryCount">0</span> deliveries
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- TOTAL PERFORMANCE -->
                      <div class="col-lg-4">
                        <div class="shift-card">
                          <div class="shift-header">
                            <div class="d-flex align-items-center">
                              <div class="shift-icon total">
                                <i class="bi bi-pie-chart-fill"></i>
                              </div>
                              <div class="shift-info">
                                <h5>Total Performance</h5>
                                <div class="shift-time">Combined Analysis</div>
                              </div>
                            </div>
                            <div class="shift-percentage" id="totalPercentage">
                              <span class="percentage">0%</span>
                              <div class="percentage-label">Completion</div>
                            </div>
                          </div>
                          <div class="shift-chart-wrapper">
                            <canvas id="totalComparisonChart"></canvas>
                          </div>
                          <div class="shift-stats">
                            <div class="stats-grid">
                              <div class="stat-box ok">
                                <div class="stat-label">OK</div>
                                <div class="stat-value" id="totalOkCount">0</div>
                                <div class="stat-percentage" id="totalOkPercent">0%</div>
                              </div>
                              <div class="stat-box on-progress">
                                <div class="stat-label">ON</div>
                                <div class="stat-value" id="totalOnCount">0</div>
                                <div class="stat-percentage" id="totalOnPercent">0%</div>
                              </div>
                              <div class="stat-box over">
                                <div class="stat-label">OVER</div>
                                <div class="stat-value" id="totalOverCount">0</div>
                                <div class="stat-percentage" id="totalOverPercent">0%</div>
                              </div>
                              <div class="stat-box delay">
                                <div class="stat-label">DELAY</div>
                                <div class="stat-value" id="totalDelayCount">0</div>
                                <div class="stat-percentage" id="totalDelayPercent">0%</div>
                              </div>
                            </div>
                          </div>
                          <div class="shift-footer">
                            <div class="delivery-info">
                              <i class="bi bi-truck me-1"></i>
                              <span id="totalDeliveryCount">0</span> total deliveries
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- CRITERIA & ANALYSIS -->
                  <div class="criteria-section mt-4">
                    <div class="criteria-header">
                      <h6><i class="bi bi-info-circle me-2"></i>Classification Criteria</h6>
                      <div class="analysis-badge" id="overallStatus">Loading...</div>
                    </div>
                    <div class="criteria-grid mt-3">
                      <div class="criteria-card ok">
                        <div class="criteria-icon">
                          <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="criteria-content">
                          <div class="criteria-title">OK Status</div>
                          <div class="criteria-range">Completion ≥ 90%</div>
                          <div class="criteria-desc">Excellent performance, on schedule</div>
                        </div>
                      </div>
                      <div class="criteria-card on-progress">
                        <div class="criteria-icon">
                          <i class="bi bi-clock-fill"></i>
                        </div>
                        <div class="criteria-content">
                          <div class="criteria-title">ON PROGRESS</div>
                          <div class="criteria-range">Completion 70-89%</div>
                          <div class="criteria-desc">Within acceptable range, needs monitoring</div>
                        </div>
                      </div>
                      <div class="criteria-card over">
                        <div class="criteria-icon">
                          <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div class="criteria-content">
                          <div class="criteria-title">OVER</div>
                          <div class="criteria-range">Completion 50-69%</div>
                          <div class="criteria-desc">Below expectation, requires attention</div>
                        </div>
                      </div>
                      <div class="criteria-card delay">
                        <div class="criteria-icon">
                          <i class="bi bi-x-circle-fill"></i>
                        </div>
                        <div class="criteria-content">
                          <div class="criteria-title">DELAY</div>
                          <div class="criteria-range">Completion < 50%</div>
                          <div class="criteria-desc">Critical, immediate action needed</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- BEAUTIFUL TOOLTIP -->
<div id="chartTooltip" class="chart-tooltip">
  <div class="tooltip-header">
    <div class="tooltip-title">
      <span class="tooltip-icon" id="tooltipIcon"></span>
      <span class="tooltip-status" id="tooltipStatus">Status</span>
    </div>
    <div class="tooltip-actions">
      <button class="btn-tooltip-action" onclick="drillDownData()" title="View Details">
        <i class="bi bi-zoom-in"></i>
      </button>
      <button class="btn-tooltip-close" onclick="hideTooltip()" title="Close">
        <i class="bi bi-x"></i>
      </button>
    </div>
  </div>
  <div class="tooltip-body">
    <div class="tooltip-metrics">
      <div class="metric-main">
        <div class="metric-value" id="tooltipValue">0%</div>
        <div class="metric-label">Completion Rate</div>
      </div>
      <div class="metric-grid">
        <div class="metric-item">
          <div class="metric-icon">
            <i class="bi bi-box"></i>
          </div>
          <div class="metric-content">
            <div class="metric-title">Deliveries</div>
            <div class="metric-data" id="tooltipDeliveryCount">0</div>
          </div>
        </div>
        <div class="metric-item">
          <div class="metric-icon">
            <i class="bi bi-percent"></i>
          </div>
          <div class="metric-content">
            <div class="metric-title">Ratio</div>
            <div class="metric-data" id="tooltipRatio">0/0</div>
          </div>
        </div>
        <div class="metric-item">
          <div class="metric-icon">
            <i class="bi bi-clipboard-check"></i>
          </div>
          <div class="metric-content">
            <div class="metric-title">Order Qty</div>
            <div class="metric-data" id="tooltipOrderQty">0</div>
          </div>
        </div>
        <div class="metric-item">
          <div class="metric-icon">
            <i class="bi bi-clipboard-data"></i>
          </div>
          <div class="metric-content">
            <div class="metric-title">Incoming Qty</div>
            <div class="metric-data" id="tooltipIncomingQty">0</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="tooltip-footer">
    <div class="footer-text">Click "View Details" for detailed analysis</div>
  </div>
  <div class="tooltip-arrow"></div>
</div>

<style>
/* ===== GLOBAL STYLES ===== */
.analytics-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border: 1px solid rgba(226, 232, 240, 0.8);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  transition: all 0.3s ease;
}

.analytics-card:hover {
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
  border-color: #dbeafe;
}

/* ===== HEADER STYLES DENGAN COLLAPSE ICON ===== */
.analytics-header {
  padding: 20px 0 10px 0;
  border-bottom: 1px solid #e2e8f0;
  cursor: pointer;
  user-select: none;
  transition: all 0.3s ease;
}

.analytics-header:hover {
  background: rgba(241, 245, 249, 0.5);
  border-radius: 8px 8px 0 0;
}

.header-icon-wrapper {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(26, 54, 93, 0.2);
}

.header-icon-wrapper i {
  color: white;
  font-size: 1.5rem;
}

.analytics-title {
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, #1a365d 0%, #4c51bf 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* ICON COLLAPSE – WARNA LEBIH CAKEP */
.collapse-section-icon {
  color: #4c51bf;
  background: linear-gradient(
    135deg,
    rgba(76, 81, 191, 0.15),
    rgba(76, 81, 191, 0.05)
  );
  border-radius: 6px;
  padding: 4px 6px;
  transition: transform 0.35s ease, background 0.3s ease;
}

.collapse-section-icon:hover {
  background: linear-gradient(
    135deg,
    rgba(76, 81, 191, 0.25),
    rgba(76, 81, 191, 0.1)
  );
}

.collapse-section-icon.collapsed {
  transform: rotate(180deg);
}

.analytics-subtitle {
  color: #718096;
  font-size: 0.85rem;
}

.last-updated {
  background: #f1f5f9;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  color: #64748b;
  border: 1px solid #e2e8f0;
}

.btn-refresh-full {
  background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 6px 16px;
  font-size: 0.85rem;
  font-weight: 500;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(26, 54, 93, 0.2);
}

.btn-refresh-full:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(26, 54, 93, 0.3);
}

/* ===== KONTEN UTAMA YANG BISA DI-COLLAPSE ===== */
.analytics-content {
  max-height: 2000px;
  overflow: hidden;
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  opacity: 1;
  visibility: visible;
}

.analytics-content.collapsed {
  max-height: 0 !important;
  padding: 0 !important;
  margin: 0 !important;
  opacity: 0;
  visibility: hidden;
  border: none;
}

/* ===== TABS STYLES ===== */
.analytics-tabs {
  margin-top: 20px;
}

.analytics-tabs .nav-tabs {
  border-bottom: 2px solid #e2e8f0;
  gap: 8px;
}

.analytics-tabs .nav-link {
  border: none;
  border-radius: 10px 10px 0 0;
  padding: 12px 24px;
  font-weight: 500;
  color: #64748b;
  background: transparent;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  position: relative;
}

.analytics-tabs .nav-link:hover {
  color: #1a365d;
  background: #f1f5f9;
}

.analytics-tabs .nav-link.active {
  background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(26, 54, 93, 0.2);
  border: none;
}

.analytics-tabs .nav-link i {
  font-size: 1rem;
}

/* ===== TAB 1: PERFORMANCE TREND STYLES ===== */
.trend-controls {
  background: #f8fafc;
  padding: 15px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.control-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.control-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #475569;
  white-space: nowrap;
}

.custom-select-wrapper {
  position: relative;
  min-width: 150px;
}

.custom-select-wrapper .form-select {
  border: 1px solid #cbd5e0;
  border-radius: 8px;
  padding: 6px 35px 6px 12px;
  background: white;
  font-size: 0.85rem;
  color: #334155;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.select-arrow {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  pointer-events: none;
  font-size: 0.8rem;
}

/* Chart Container */
.trend-chart-container {
  background: white;
  border-radius: 12px;
  padding: 15px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

/* ===== TAB 2: SHIFT COMPARISON STYLES ===== */
.shift-controls {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  padding: 15px 20px;
  border-radius: 12px;
}

.month-display-large {
  display: flex;
  align-items: center;
  font-size: 1.1rem;
  font-weight: 600;
  color: #1a365d;
}

.month-display-large i {
  font-size: 1.2rem;
  color: #4c51bf;
}

.date-range {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: normal;
}

.btn-group.btn-group-sm .btn {
  padding: 4px 12px;
  font-size: 0.8rem;
}

/* Shift Cards */
.shift-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px;
  height: 100%;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.shift-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  border-color: #dbeafe;
}

.shift-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 15px;
}

.shift-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  font-size: 1.3rem;
}

.shift-icon.sun {
  background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
  color: white;
}

.shift-icon.moon {
  background: linear-gradient(135deg, #805ad5 0%, #6b46c1 100%);
  color: white;
}

.shift-icon.total {
  background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
  color: white;
}

.shift-info h5 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1a365d;
  margin: 0;
}

.shift-time {
  font-size: 0.8rem;
  color: #64748b;
}

.shift-percentage {
  text-align: center;
}

.shift-percentage .percentage {
  font-size: 1.8rem;
  font-weight: 700;
  color: #1a365d;
  display: block;
  line-height: 1;
}

.percentage-label {
  font-size: 0.75rem;
  color: #64748b;
  margin-top: 2px;
}

/* Chart Wrapper */
.shift-chart-wrapper {
  height: 180px;
  position: relative;
  margin: 15px 0;
}

.shift-chart-wrapper canvas {
  width: 100% !important;
  height: 100% !important;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
  margin: 15px 0;
}

.stat-box {
  text-align: center;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.stat-box:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.stat-box.ok {
  background: rgba(56, 161, 105, 0.1);
  border: 1px solid rgba(56, 161, 105, 0.2);
}

.stat-box.on-progress {
  background: rgba(66, 153, 225, 0.1);
  border: 1px solid rgba(66, 153, 225, 0.2);
}

.stat-box.over {
  background: rgba(214, 158, 46, 0.1);
  border: 1px solid rgba(214, 158, 46, 0.2);
}

.stat-box.delay {
  background: rgba(229, 62, 62, 0.1);
  border: 1px solid rgba(229, 62, 62, 0.2);
}

.stat-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a365d;
}

.stat-percentage {
  font-size: 0.7rem;
  color: #718096;
}

.shift-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 15px;
  border-top: 1px solid #e2e8f0;
  font-size: 0.85rem;
  color: #64748b;
}

.delivery-info, .efficiency {
  display: flex;
  align-items: center;
}

.delivery-info i {
  color: #4c51bf;
}

/* Criteria Section */
.criteria-section {
  background: #f8fafc;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.criteria-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.criteria-header h6 {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1a365d;
  margin: 0;
  display: flex;
  align-items: center;
}

.analysis-badge {
  background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
  color: white;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.criteria-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 15px;
}

.criteria-card {
  background: white;
  border-radius: 10px;
  padding: 15px;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: all 0.3s ease;
  border: 1px solid transparent;
}

.criteria-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
}

.criteria-card.ok {
  border-color: rgba(56, 161, 105, 0.3);
}

.criteria-card.on-progress {
  border-color: rgba(66, 153, 225, 0.3);
}

.criteria-card.over {
  border-color: rgba(214, 158, 46, 0.3);
}

.criteria-card.delay {
  border-color: rgba(229, 62, 62, 0.3);
}

.criteria-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.criteria-card.ok .criteria-icon {
  background: rgba(56, 161, 105, 0.1);
  color: #38a169;
}

.criteria-card.on-progress .criteria-icon {
  background: rgba(66, 153, 225, 0.1);
  color: #4299e1;
}

.criteria-card.over .criteria-icon {
  background: rgba(214, 158, 46, 0.1);
  color: #d69e2e;
}

.criteria-card.delay .criteria-icon {
  background: rgba(229, 62, 62, 0.1);
  color: #e53e3e;
}

.criteria-content {
  flex: 1;
}

.criteria-title {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1a365d;
  margin-bottom: 2px;
}

.criteria-range {
  font-size: 0.8rem;
  font-weight: 600;
  margin-bottom: 4px;
}

.criteria-card.ok .criteria-range {
  color: #38a169;
}

.criteria-card.on-progress .criteria-range {
  color: #4299e1;
}

.criteria-card.over .criteria-range {
  color: #d69e2e;
}

.criteria-card.delay .criteria-range {
  color: #e53e3e;
}

.criteria-desc {
  font-size: 0.75rem;
  color: #64748b;
  line-height: 1.3;
}

/* ===== BEAUTIFUL TOOLTIP STYLES ===== */
.chart-tooltip {
  position: fixed;
  z-index: 9999;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  border: 1px solid #e2e8f0;
  width: 320px;
  overflow: hidden;
  opacity: 0;
  transform: translateY(10px);
  transition: all 0.3s ease;
  pointer-events: none;
  display: none;
}

.chart-tooltip.visible {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}

/* Tooltip Header */
.tooltip-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
  color: white;
}

.tooltip-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.tooltip-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
}

.tooltip-status {
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.tooltip-actions {
  display: flex;
  gap: 8px;
}

.btn-tooltip-action, .btn-tooltip-close {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.btn-tooltip-action:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}

.btn-tooltip-close:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

/* Tooltip Body */
.tooltip-body {
  padding: 20px;
}

.tooltip-metrics {
  text-align: center;
}

.metric-main {
  margin-bottom: 20px;
}

.metric-value {
  font-size: 3rem;
  font-weight: 800;
  color: #1a365d;
  line-height: 1;
  margin-bottom: 5px;
}

.metric-label {
  font-size: 0.9rem;
  color: #64748b;
  font-weight: 500;
}

.metric-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.metric-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.metric-icon {
  width: 32px;
  height: 32px;
  background: white;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4c51bf;
  font-size: 0.9rem;
}

.metric-content {
  flex: 1;
}

.metric-title {
  font-size: 0.75rem;
  color: #64748b;
  margin-bottom: 2px;
}

.metric-data {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1a365d;
}

/* Tooltip Footer */
.tooltip-footer {
  padding: 12px 20px;
  text-align: center;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
}

.footer-text {
  font-size: 0.75rem;
  color: #94a3b8;
  font-style: italic;
}

/* Tooltip Arrow */
.tooltip-arrow {
  position: absolute;
  width: 20px;
  height: 20px;
  background: white;
  transform: rotate(45deg);
  z-index: -1;
  border: 1px solid #e2e8f0;
}

/* Position variations */
.chart-tooltip.top .tooltip-arrow {
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%) rotate(45deg);
  border-top: none;
  border-left: none;
}

.chart-tooltip.bottom .tooltip-arrow {
  top: -10px;
  left: 50%;
  transform: translateX(-50%) rotate(45deg);
  border-bottom: none;
  border-right: none;
}

.chart-tooltip.left .tooltip-arrow {
  right: -10px;
  top: 50%;
  transform: translateY(-50%) rotate(45deg);
  border-left: none;
  border-top: none;
}

.chart-tooltip.right .tooltip-arrow {
  left: -10px;
  top: 50%;
  transform: translateY(-50%) rotate(45deg);
  border-right: none;
  border-bottom: none;
}

/* ===== RESPONSIVE STYLES ===== */
@media (max-width: 1200px) {
  .criteria-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 992px) {
  .analytics-tabs .nav-link {
    padding: 10px 15px;
    font-size: 0.9rem;
  }
  
  .shift-charts-container .row > .col-lg-4 {
    margin-bottom: 20px;
  }
}

@media (max-width: 768px) {
  .analytics-header {
    flex-direction: column;
    gap: 15px;
    text-align: center;
  }
  
  .header-icon-wrapper {
    margin: 0 auto;
  }
  
  .analytics-title {
    font-size: 1.3rem;
    justify-content: center;
  }
  
  .trend-controls {
    flex-direction: column;
    gap: 15px;
  }
  
  .control-group {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .custom-select-wrapper {
    width: 100%;
  }
  
  .shift-controls {
    flex-direction: column;
    gap: 15px;
  }
  
  .btn-group {
    width: 100%;
  }
  
  .criteria-grid {
    grid-template-columns: 1fr;
  }
  
  .chart-tooltip {
    width: 280px;
  }
}

@media (max-width: 576px) {
  .analytics-tabs .nav-link {
    padding: 8px 12px;
    font-size: 0.8rem;
  }
  
  .analytics-tabs .nav-link i.bi {
    display: none;
  }
  
  .shift-card {
    padding: 15px;
  }
  
  .shift-chart-wrapper {
    height: 150px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Animation for card loading */
@keyframes cardLoading {
  0% { background-position: -200px 0; }
  100% { background-position: calc(200px + 100%) 0; }
}

.card.loading {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200px 100%;
  animation: cardLoading 1.5s infinite;
}

/* Smooth transitions */
* {
  transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}

/* Custom scrollbar for chart containers */
.trend-chart-container::-webkit-scrollbar,
.shift-charts-container::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.trend-chart-container::-webkit-scrollbar-track,
.shift-charts-container::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.trend-chart-container::-webkit-scrollbar-thumb,
.shift-charts-container::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
}

.trend-chart-container::-webkit-scrollbar-thumb:hover,
.shift-charts-container::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>

<script>
// Global variables
let trendChart = null;
let dsComparisonChart = null;
let nsComparisonChart = null;
let totalComparisonChart = null;
let currentShiftData = null;
let currentTrendData = null;
let isPercentageView = true;

// State untuk section collapse - Default: TRUE (tertutup)
let isSectionCollapsed = true;

// Toggle seluruh section collapse
function toggleSectionCollapse(event) {
  if (event) {
    event.stopPropagation();
  }
  
  const content = $('#analyticsContent');
  const icon = $('#sectionCollapseIcon');
  const header = $('.analytics-header');
  
  isSectionCollapsed = !isSectionCollapsed;
  
  if (isSectionCollapsed) {
    // Collapse section
    content.addClass('collapsed');
    icon.addClass('collapsed');
    header.css('border-bottom', 'none');
    
    // Hilangkan border radius bawah card
    $('.analytics-card').css('border-radius', '16px 16px 16px 16px');
    
    // Stop auto-refresh jika collapsed
    clearAllIntervals();
    
    // Hapus chart yang aktif untuk hemat memory
    destroyAllCharts();
  } else {
    // Expand section
    content.removeClass('collapsed');
    icon.removeClass('collapsed');
    header.css('border-bottom', '1px solid #e2e8f0');
    
    // Kembalikan border radius
    $('.analytics-card').css('border-radius', '16px');
    
    // Load chart yang aktif setelah delay untuk smooth animation
    setTimeout(() => {
      if ($('#trend-tab').hasClass('active')) {
        loadTrendChart();
      } else {
        loadShiftComparison();
      }
      
      // Mulai auto-refresh lagi
      startAutoRefresh();
    }, 300);
  }
  
  // Simpan state ke localStorage
  localStorage.setItem('chartSectionCollapsed', isSectionCollapsed);
}

// Fungsi untuk menghancurkan semua chart
function destroyAllCharts() {
  if (trendChart) {
    trendChart.destroy();
    trendChart = null;
  }
  if (dsComparisonChart) {
    dsComparisonChart.destroy();
    dsComparisonChart = null;
  }
  if (nsComparisonChart) {
    nsComparisonChart.destroy();
    nsComparisonChart = null;
  }
  if (totalComparisonChart) {
    totalComparisonChart.destroy();
    totalComparisonChart = null;
  }
}

// Handle klik pada header (selain icon)
$(document).ready(function() {
  $('.analytics-header').on('click', function(e) {
    // Jika klik bukan pada icon collapse, toggle section
    if (!$(e.target).closest('.collapse-section-icon').length && 
        !$(e.target).closest('.btn-refresh-full').length) {
      toggleSectionCollapse();
    }
  });
});

// Clear semua interval
function clearAllIntervals() {
  // Clear interval auto-refresh
  const intervalId = window.setInterval(function(){}, 9999);
  for (let i = 1; i < intervalId; i++) {
    window.clearInterval(i);
  }
}

// Start auto refresh
function startAutoRefresh() {
  // Auto-refresh setiap 2 menit
  setInterval(() => {
    if (!isSectionCollapsed) {
      if ($('#trend-tab').hasClass('active')) {
        loadTrendChart();
      } else if ($('#shift-tab').hasClass('active')) {
        loadShiftComparison();
      }
      updateLastUpdated();
    }
  }, 120000);
}

// Format bulan Indonesia
function formatMonthYear(monthYearStr) {
  const parts = monthYearStr.split(' ');
  if (parts.length !== 2) return monthYearStr;
  
  const monthMap = {
    'Jan': 'Januari',
    'Feb': 'Februari',
    'Mar': 'Maret',
    'Apr': 'April',
    'May': 'Mei',
    'Jun': 'Juni',
    'Jul': 'Juli',
    'Aug': 'Agustus',
    'Sep': 'September',
    'Oct': 'Oktober',
    'Nov': 'November',
    'Dec': 'Desember'
  };
  
  const monthAbbr = parts[0];
  const year = parts[1];
  return (monthMap[monthAbbr] || monthAbbr) + ' ' + year;
}

// Update last updated time
function updateLastUpdated() {
  const now = new Date();
  const timeStr = now.toLocaleTimeString('id-ID', { 
    hour: '2-digit', 
    minute: '2-digit',
    second: '2-digit'
  });
  $('#lastUpdated span').text(timeStr);
}

// Show beautiful tooltip
function showTooltip(event, shiftType, segmentIndex) {
  if (!currentShiftData) return;

  const e = event || window.event;
  const mouseX = e.clientX;
  const mouseY = e.clientY;
  
  let shiftData;
  if (shiftType === 'total') {
    shiftData = currentShiftData.total || {};
  } else {
    shiftData = shiftType === 'ds' ? currentShiftData.ds : currentShiftData.ns;
  }
  
  const hasData = shiftData.has_data || (shiftData.total_delivery > 0);
  
  if (!hasData) {
    hideTooltip();
    return;
  }
  
  // 4 Status: OK, ON_PROGRESS, OVER, DELAY
  const labels = ['OK', 'ON_PROGRESS', 'OVER', 'DELAY'];
  const status = labels[segmentIndex];
  const statusLabels = ['OK', 'ON PROGRESS', 'OVER', 'DELAY'];
  const icons = ['bi-check-circle-fill', 'bi-clock-fill', 'bi-exclamation-triangle-fill', 'bi-x-circle-fill'];
  const colors = ['#38a169', '#4299e1', '#d69e2e', '#e53e3e'];
  
  // Get counts and percentages
  const counts = [
    shiftData.ok_count || 0,
    shiftData.on_progress_count || 0,
    shiftData.over_count || 0,
    shiftData.delay_count || 0
  ];
  
  const percentages = [
    shiftData.ok_percentage || 0,
    shiftData.on_progress_percentage || 0,
    shiftData.over_percentage || 0,
    shiftData.delay_percentage || 0
  ];
  
  // Set tooltip content
  $('#tooltipStatus').text(statusLabels[segmentIndex]);
  $('#tooltipIcon').html(`<i class="bi ${icons[segmentIndex]}"></i>`);
  $('#tooltipValue').text(percentages[segmentIndex].toFixed(1) + '%');
  $('#tooltipDeliveryCount').text(counts[segmentIndex].toLocaleString());
  
  // Set ratio
  const totalDelivery = shiftData.total_delivery || 1;
  $('#tooltipRatio').text(`${counts[segmentIndex].toLocaleString()}/${totalDelivery.toLocaleString()}`);
  
  // Set quantities
  $('#tooltipOrderQty').text((shiftData.total_order || 0).toLocaleString());
  $('#tooltipIncomingQty').text((shiftData.total_incoming || 0).toLocaleString());
  
  // Calculate position
  const tooltip = $('#chartTooltip');
  const tooltipWidth = tooltip.outerWidth();
  const tooltipHeight = tooltip.outerHeight();
  const viewportWidth = window.innerWidth;
  const viewportHeight = window.innerHeight;
  
  let left = mouseX + 20;
  let top = mouseY + 20;
  let positionClass = '';
  
  // Check right edge
  if (left + tooltipWidth > viewportWidth - 20) {
    left = mouseX - tooltipWidth - 20;
    positionClass += 'left ';
  } else {
    positionClass += 'right ';
  }
  
  // Check bottom edge
  if (top + tooltipHeight > viewportHeight - 20) {
    top = mouseY - tooltipHeight - 20;
    positionClass += 'top';
  } else {
    positionClass += 'bottom';
  }
  
  // Ensure within viewport
  left = Math.max(20, Math.min(left, viewportWidth - tooltipWidth - 20));
  top = Math.max(20, Math.min(top, viewportHeight - tooltipHeight - 20));
  
  // Position tooltip
  tooltip.css({
    left: left + 'px',
    top: top + 'px'
  });
  
  // Set position class for arrow
  tooltip.removeClass('top bottom left right').addClass(positionClass.trim());
  
  // Show tooltip
  tooltip.show();
  setTimeout(() => {
    tooltip.addClass('visible');
  }, 10);
}

// Hide tooltip
function hideTooltip() {
  const tooltip = $('#chartTooltip');
  tooltip.removeClass('visible');
  setTimeout(() => {
    tooltip.hide();
  }, 300);
}

// Drill down to detailed data
function drillDownData() {
  // This function can be expanded to show detailed modal
  alert('Detailed data view would open here. This feature can be implemented based on your requirements.');
}

// Refresh all charts
function refreshAllCharts() {
  $('.analytics-card').addClass('loading');
  
  // Refresh trend chart
  loadTrendChart();
  
  // Refresh shift comparison
  loadShiftComparison();
  
  // Update timestamp
  updateLastUpdated();
  
  setTimeout(() => {
    $('.analytics-card').removeClass('loading');
  }, 500);
}

// Download shift report
function downloadShiftReport() {
  // This function can be expanded to generate and download reports
  alert('Export feature would generate a report here. This can be implemented based on your requirements.');
}

// Load trend chart langsung tanpa loading
function loadTrendChart() {
    console.log("🔄 Loading trend chart...");
    
    // Jika section collapsed, jangan load chart
    if (isSectionCollapsed) {
        console.log("📁 Section collapsed, skipping chart load");
        return;
    }
    
    const days = $('#trendRange').val();
    
    if (trendChart) {
        trendChart.destroy();
    }
    
    $.ajax({
        url: 'api/get_performance_trend.php',
        type: 'GET',
        data: { days: days },
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
            console.log("📊 Trend API Response:", response);
            
            if (!response || !response.success || !response.data || response.data.length === 0) {
                console.error("❌ No valid data returned from API");
                $('#trendChart').html(`
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        <p class="mt-3">No trend data available</p>
                        <p class="text-muted small">Try selecting a different time range</p>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadTrendChart()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Retry
                        </button>
                    </div>
                `);
                return;
            }
            
            const trendData = response.data;
            console.log(`✅ Loaded ${trendData.length} data points`);
            
            const dates = trendData.map(r => r.date);
            const planOrder = trendData.map(r => parseFloat(r.target_qty) || 0);
            const actualIncoming = trendData.map(r => parseFloat(r.actual_qty) || 0);
            
            // Prepare chart options
            const options = {
                series: [
                    {
                        name: 'Plan Order',
                        data: planOrder,
                        type: 'column',
                        color: '#1a365d'
                    },
                    {
                        name: 'Actual Incoming',
                        data: actualIncoming,
                        type: 'line',
                        color: '#0bc5ea'
                    }
                ],
                chart: {
                    height: 380,
                    type: 'line',
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        }
                    },
                    animations: {
                        enabled: true,
                        speed: 800
                    }
                },
                stroke: {
                    width: [0, 4],
                    curve: 'smooth'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '60%'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                markers: {
                    size: 5
                },
                xaxis: {
                    categories: dates,
                    labels: {
                        style: {
                            colors: '#6b7280',
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Quantity',
                        style: {
                            color: '#6b7280',
                            fontSize: '12px'
                        }
                    },
                    labels: {
                        formatter: function(val) {
                            return new Intl.NumberFormat('id-ID', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }).format(val);
                        },
                        style: {
                            colors: '#6b7280',
                            fontSize: '11px'
                        }
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function(val) {
                            return new Intl.NumberFormat('id-ID', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }).format(val) + ' pcs';
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    fontSize: '14px',
                    fontWeight: 600
                },
                grid: {
                    borderColor: '#e5e7eb',
                    strokeDashArray: 4
                }
            };
            
            // Render chart langsung
            trendChart = new ApexCharts(document.querySelector("#trendChart"), options);
            trendChart.render();
            
            console.log("✅ Trend chart rendered successfully");
            
        },
        error: function(xhr, status, error) {
            console.error("❌ Trend chart AJAX error:", error);
            
            $('#trendChart').html(`
                <div class="text-center py-5 text-danger">
                    <i class="bi bi-exclamation-triangle" style="font-size: 3rem;"></i>
                    <p class="mt-3">Failed to load trend data</p>
                    <p class="text-muted small">Error: ${xhr.status} ${xhr.statusText}</p>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadTrendChart()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Retry
                    </button>
                </div>
            `);
        }
    });
}

// Load shift comparison data
function loadShiftComparison() {
  // Jika section collapsed, jangan load chart
  if (isSectionCollapsed) return;
  
  $.ajax({
    url: 'api/get_shift_comparison_detail.php',
    type: 'GET',
    dataType: 'json',
    success: function(response) {
      if (!response || response.error) {
        console.error('Shift comparison error:', response?.error);
        createErrorCharts();
        return;
      }
      
      currentShiftData = response;
      const ds = response.ds || {};
      const ns = response.ns || {};
      
      // Calculate total data
      const total = {
        ok_count: (ds.ok_count || 0) + (ns.ok_count || 0),
        on_progress_count: (ds.on_progress_count || 0) + (ns.on_progress_count || 0),
        over_count: (ds.over_count || 0) + (ns.over_count || 0),
        delay_count: (ds.delay_count || 0) + (ns.delay_count || 0),
        total_delivery: (ds.total_delivery || 0) + (ns.total_delivery || 0),
        total_order: (ds.total_order || 0) + (ns.total_order || 0),
        total_incoming: (ds.total_incoming || 0) + (ns.total_incoming || 0),
        ok_percentage: 0,
        on_progress_percentage: 0,
        over_percentage: 0,
        delay_percentage: 0,
        has_data: (ds.total_delivery || 0) > 0 || (ns.total_delivery || 0) > 0
      };
      
      // Calculate percentages for total
      if (total.total_delivery > 0) {
        total.ok_percentage = (total.ok_count / total.total_delivery * 100);
        total.on_progress_percentage = (total.on_progress_count / total.total_delivery * 100);
        total.over_percentage = (total.over_count / total.total_delivery * 100);
        total.delay_percentage = (total.delay_count / total.total_delivery * 100);
      }
      
      // Calculate completion rates
      ds.completion_rate = ds.total_order > 0 ? (ds.total_incoming / ds.total_order * 100) : 0;
      ns.completion_rate = ns.total_order > 0 ? (ns.total_incoming / ns.total_order * 100) : 0;
      total.completion_rate = total.total_order > 0 ? (total.total_incoming / total.total_order * 100) : 0;
      
      currentShiftData.total = total;
      
      // Update period display
      const monthYear = formatMonthYear(response.period || 'Jan 2024');
      $('#currentMonth').text(monthYear);
      $('#shiftDateRange').text(response.date_range || '');
      
      // Update overall status
      let overallStatus = 'NO DATA';
      let statusColor = '#718096';
      
      if (total.total_delivery > 0) {
        if (total.completion_rate >= 90) {
          overallStatus = 'EXCELLENT';
          statusColor = '#38a169';
        } else if (total.completion_rate >= 70) {
          overallStatus = 'GOOD';
          statusColor = '#4299e1';
        } else if (total.completion_rate >= 50) {
          overallStatus = 'NEEDS ATTENTION';
          statusColor = '#d69e2e';
        } else {
          overallStatus = 'CRITICAL';
          statusColor = '#e53e3e';
        }
      }
      
      $('#overallStatus').text(overallStatus).css('background', `linear-gradient(135deg, ${statusColor} 0%, ${darkenColor(statusColor, 20)} 100%)`);
      
      // Update Day Shift data
      updateShiftDisplay('ds', ds);
      
      // Update Night Shift data
      updateShiftDisplay('ns', ns);
      
      // Update Total data
      updateShiftDisplay('total', total);
      
      // Create charts
      createShiftChart('ds', ds);
      createShiftChart('ns', ns);
      createShiftChart('total', total);
      
    },
    error: function(xhr, status, error) {
      console.error('Shift comparison AJAX error:', error);
      createErrorCharts();
    }
  });
}

// Update shift display data
function updateShiftDisplay(prefix, data) {
  const hasData = data.has_data || (data.total_delivery > 0);
  
  // Update percentages
  $(`#${prefix}Percentage .percentage`).text(hasData ? data.completion_rate.toFixed(1) + '%' : 'N/A');
  
  // Update counts
  $(`#${prefix}OkCount`).text(data.ok_count || 0);
  $(`#${prefix}OnCount`).text(data.on_progress_count || 0);
  $(`#${prefix}OverCount`).text(data.over_count || 0);
  $(`#${prefix}DelayCount`).text(data.delay_count || 0);
  
  // Update percentages in stats
  if (hasData && data.total_delivery > 0) {
    $(`#${prefix}OkPercent`).text(((data.ok_count / data.total_delivery) * 100).toFixed(1) + '%');
    $(`#${prefix}OnPercent`).text(((data.on_progress_count / data.total_delivery) * 100).toFixed(1) + '%');
    $(`#${prefix}OverPercent`).text(((data.over_count / data.total_delivery) * 100).toFixed(1) + '%');
    $(`#${prefix}DelayPercent`).text(((data.delay_count / data.total_delivery) * 100).toFixed(1) + '%');
  } else {
    $(`#${prefix}OkPercent, #${prefix}OnPercent, #${prefix}OverPercent, #${prefix}DelayPercent`).text('0%');
  }
  
  // Update delivery info
  $(`#${prefix}DeliveryCount`).text(data.total_delivery || 0);
}

// Create shift chart dengan 4 warna yang benar
function createShiftChart(shiftType, data) {
  const ctx = document.getElementById(shiftType + 'ComparisonChart');
  if (!ctx) return;
  
  // Destroy existing chart
  let existingChart;
  if (shiftType === 'ds' && dsComparisonChart) {
    dsComparisonChart.destroy();
  } else if (shiftType === 'ns' && nsComparisonChart) {
    nsComparisonChart.destroy();
  } else if (shiftType === 'total' && totalComparisonChart) {
    totalComparisonChart.destroy();
  }
  
  const hasData = data.has_data || (data.total_delivery > 0);
  
  if (!hasData) {
    // Create empty chart
    const chart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['No Data'],
        datasets: [{
          data: [100],
          backgroundColor: ['#e2e8f0'],
          borderColor: '#ffffff',
          borderWidth: 3,
          hoverBackgroundColor: ['#cbd5e0']
        }]
      },
      options: getChartOptions(false, shiftType)
    });
    
    // Save chart reference
    if (shiftType === 'ds') dsComparisonChart = chart;
    else if (shiftType === 'ns') nsComparisonChart = chart;
    else totalComparisonChart = chart;
    
    return;
  }
  
  // Prepare data based on view mode
  let chartData;
  let labels;
  let colors;
  
  // 4 Status dengan warna yang benar
  const statusColors = {
    ok: 'rgba(56, 161, 105, 0.9)',
    on_progress: 'rgba(66, 153, 225, 0.9)',
    over: 'rgba(214, 158, 46, 0.9)',
    delay: 'rgba(229, 62, 62, 0.9)'
  };
  
  const hoverColors = {
    ok: 'rgba(56, 161, 105, 1)',
    on_progress: 'rgba(66, 153, 225, 1)',
    over: 'rgba(214, 158, 46, 1)',
    delay: 'rgba(229, 62, 62, 1)'
  };
  
  if (isPercentageView) {
    chartData = [
      data.ok_percentage || 0,
      data.on_progress_percentage || 0,
      data.over_percentage || 0,
      data.delay_percentage || 0
    ];
    labels = ['OK', 'ON PROGRESS', 'OVER', 'DELAY'];
    colors = [
      statusColors.ok,
      statusColors.on_progress,
      statusColors.over,
      statusColors.delay
    ];
  } else {
    chartData = [
      data.ok_count || 0,
      data.on_progress_count || 0,
      data.over_count || 0,
      data.delay_count || 0
    ];
    labels = ['OK', 'ON', 'OVER', 'DELAY'];
    colors = [
      statusColors.ok,
      statusColors.on_progress,
      statusColors.over,
      statusColors.delay
    ];
  }
  
  const chart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: chartData,
        backgroundColor: colors,
        borderColor: '#ffffff',
        borderWidth: 3,
        hoverBackgroundColor: [
          hoverColors.ok,
          hoverColors.on_progress,
          hoverColors.over,
          hoverColors.delay
        ],
        hoverBorderWidth: 4,
        hoverOffset: 10
      }]
    },
    options: getChartOptions(true, shiftType)
  });
  
  // Save chart reference
  if (shiftType === 'ds') dsComparisonChart = chart;
  else if (shiftType === 'ns') nsComparisonChart = chart;
  else totalComparisonChart = chart;
}

// Get chart options
function getChartOptions(hasData, shiftType) {
  return {
    cutout: '70%',
    responsive: true,
    maintainAspectRatio: false,
    layout: {
      padding: 0
    },
    plugins: {
      tooltip: {
        enabled: false,
        external: function(context) {
          // Custom tooltip handling
        }
      },
      legend: {
        display: false
      }
    },
    animation: {
      animateScale: true,
      animateRotate: true,
      duration: 800,
      easing: 'easeOutQuart'
    },
    onHover: function(event, elements) {
      const canvas = event.native?.target;
      if (elements.length > 0 && hasData) {
        const segmentIndex = elements[0].index;
        canvas.style.cursor = 'pointer';
        showTooltip(event, shiftType, segmentIndex);
      } else {
        canvas.style.cursor = 'default';
        hideTooltip();
      }
    },
    onClick: function(event, elements) {
      if (elements.length > 0 && hasData) {
        const segmentIndex = elements[0].index;
        // You can add click functionality here
        console.log(`Clicked on ${shiftType} segment ${segmentIndex}`);
      }
    }
  };
}

// Create error charts
function createErrorCharts() {
  $('#currentMonth').text('Error');
  $('#shiftDateRange').text('Failed to load data');
  $('#overallStatus').text('ERROR').css('background', 'linear-gradient(135deg, #e53e3e 0%, #c53030 100%)');
  
  // Reset all displays
  ['ds', 'ns', 'total'].forEach(prefix => {
    $(`#${prefix}Percentage .percentage`).text('N/A');
    $(`#${prefix}OkCount, #${prefix}OnCount, #${prefix}OverCount, #${prefix}DelayCount`).text('0');
    $(`#${prefix}OkPercent, #${prefix}OnPercent, #${prefix}OverPercent, #${prefix}DelayPercent`).text('0%');
    $(`#${prefix}DeliveryCount`).text('0');
  });
  
  // Create error charts
  createShiftChart('ds', { has_data: false });
  createShiftChart('ns', { has_data: false });
  createShiftChart('total', { has_data: false });
}

// Helper function to darken color
function darkenColor(color, percent) {
  const num = parseInt(color.replace('#', ''), 16);
  const amt = Math.round(2.55 * percent);
  const R = (num >> 16) - amt;
  const G = (num >> 8 & 0x00FF) - amt;
  const B = (num & 0x0000FF) - amt;
  return '#' + (
    0x1000000 +
    (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
    (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
    (B < 255 ? B < 1 ? 0 : B : 255)
  ).toString(16).slice(1);
}

// Initialize when document is ready
$(document).ready(function() {
  // Load state dari localStorage
  const savedState = localStorage.getItem('chartSectionCollapsed');
  if (savedState !== null) {
    isSectionCollapsed = savedState === 'true';
  }
  
  // Apply initial state
  const content = $('#analyticsContent');
  const icon = $('#sectionCollapseIcon');
  
  if (isSectionCollapsed) {
    content.addClass('collapsed');
    icon.addClass('collapsed');
    $('.analytics-card').css('border-radius', '16px 16px 16px 16px');
    $('.analytics-header').css('border-bottom', 'none');
  } else {
    // Load initial charts jika expanded
    setTimeout(() => {
      loadTrendChart();
      loadShiftComparison();
      updateLastUpdated();
      startAutoRefresh();
    }, 1000);
  }
  
  // Event listeners
  $('#trendRange').on('change', function() {
    if (!isSectionCollapsed) {
      loadTrendChart();
    }
  });
  
  $('#viewPercentage, #viewCount').on('change', function() {
    isPercentageView = $('#viewPercentage').is(':checked');
    if (!isSectionCollapsed) {
      loadShiftComparison();
    }
  });
  
  // Update time every minute
  setInterval(updateLastUpdated, 60000);
  
  // Hide tooltip on scroll
  $(window).on('scroll', hideTooltip);
  
  // Hide tooltip when clicking outside
  $(document).on('click', function(e) {
    if (!$(e.target).closest('#chartTooltip').length && 
        !$(e.target).closest('.shift-chart-wrapper canvas').length) {
      hideTooltip();
    }
  });
  
  // Prevent tooltip close when clicking inside
  $(document).on('click', '#chartTooltip', function(e) {
    e.stopPropagation();
  });
  
  // Bootstrap tab change event
  $('#analyticsTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
    const target = $(e.target).attr('data-bs-target');
    
    // Load chart untuk tab yang aktif
    setTimeout(() => {
      if (!isSectionCollapsed) {
        if (target === '#trend-content') {
          loadTrendChart();
        } else {
          loadShiftComparison();
        }
      }
    }, 300);
  });
});
</script>