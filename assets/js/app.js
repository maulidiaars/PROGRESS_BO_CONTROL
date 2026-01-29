// ================= ADD ORDER DENGAN PILIHAN JAM =================
let currentDSData = null;
let currentNSData = null;
let dsSelectedHours = {}; // {hour: quantity}
let nsSelectedHours = {}; // {hour: quantity}

// ========== HELPER FUNCTIONS ==========
function getDayNameID(dateString) {
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const [year, month, day] = dateString.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    return days[date.getDay()];
}

function getMonthNameID(dateString) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const [year, month] = dateString.split('-').map(Number);
    return months[month - 1];
}

// ========== ELEGANT MINIMAL STYLE ==========
function addElegantModalStyles() {
    if (!$('#elegant-modal-styles').length) {
        $('head').append(`
        <style id="elegant-modal-styles">
            .minimal-date-modal {
                font-family: 'Inter', 'Segoe UI', sans-serif;
                text-align: center;
            }

            .minimal-date-modal .icon {
                width: 64px;
                height: 64px;
                margin: 0 auto 16px;
                border-radius: 50%;
                background: rgba(74,108,247,.08);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .minimal-date-modal.warning .icon {
                background: rgba(255,193,7,.15);
            }

            .minimal-date-modal h4 {
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 6px;
                color: #111827;
            }

            .minimal-date-modal p {
                font-size: 14px;
                color: #6B7280;
                margin-bottom: 20px;
            }

            .minimal-date-modal .date-chip {
                display: inline-block;
                padding: 6px 14px;
                border-radius: 999px;
                background: #F1F5F9;
                font-size: 13px;
                font-weight: 500;
                color: #334155;
            }

            .swal2-popup.date-restriction-popup {
                border-radius: 18px !important;
                padding: 28px 24px !important;
            }
        </style>
        `);
    }
}

// ========== REUSABLE BLOCKED DATE MODAL ==========
function showDateBlockedModal({ rowDateFormatted, color }) {
    addElegantModalStyles();

    Swal.fire({
        html: `
        <div class="minimal-date-modal ${color === 'warning' ? 'warning' : ''}">
            <div class="icon">
                <i class="bi bi-calendar-x text-${color} fs-3"></i>
            </div>

            <h4>Fitur Tidak Tersedia</h4>
            <p>
                Add order hanya bisa dilakukan untuk
                <br><strong>tanggal hari ini</strong>
            </p>

            <div class="date-chip">
                Tanggal dipilih: ${rowDateFormatted}
            </div>
        </div>
        `,
        confirmButtonText: 'Mengerti',
        confirmButtonColor: color === 'warning' ? '#FFC107' : '#4A6CF7',
        showCancelButton: false,
        showCloseButton: true,
        backdrop: 'rgba(0,0,0,0.25)',
        allowOutsideClick: false,
        width: 420,
        customClass: {
            popup: 'date-restriction-popup shadow-lg',
            confirmButton: 'px-5 py-2 rounded-pill fw-medium'
        }
    });
}

// ========== SHOW ADD DS MODAL ==========
function showAddDSModal() {
    var row = tableDetailProgress.row($(this).parents('tr'));
    var data = row.data();
    if (!data) return;

    const today = new Date();
    const todayFormatted = today.toISOString().split('T')[0];
    
    // PERBAIKAN: Format date dengan benar
    const rowDateFormatted = formatDate(data.DATE);
    console.log('📅 Show DS Modal - Date:', {
        rawDate: data.DATE,
        formatted: rowDateFormatted,
        today: todayFormatted
    });

    if (rowDateFormatted.replace(/-/g, '') !== todayFormatted.replace(/-/g, '')) {
        showDateBlockedModal({
            rowDateFormatted: rowDateFormatted,
            color: 'primary'
        });
        return;
    }

    currentDSData = data;
    dsSelectedHours = {};

    $('#add-ds-date').val(data.DATE);
    $('#add-ds-supplier').val(data.SUPPLIER_CODE);
    $('#add-ds-partno').val(data.PART_NO);

    $('#txt-ds-date').text(rowDateFormatted);
    $('#txt-ds-supplier').text(data.SUPPLIER_CODE || '');
    $('#txt-ds-partno').text(data.PART_NO || '');
    $('#txt-ds-partname').text(data.PART_NAME || data.PART_DESC || '');

    $('#txt-ds-remark').val('');
    $('#ds-action').val('add');
    $('#ds-total-qty').text('0');
    $('#ds-no-hour-selected').show();

    const now = new Date();
    $('#current-time-display').text(
        now.getHours().toString().padStart(2, '0') + ':' +
        now.getMinutes().toString().padStart(2, '0')
    );

    $('#ds-error-alert, #ds-success-alert').addClass('d-none');

    generateDSHourSelection();
    loadCurrentDSStatus(data.DATE, data.SUPPLIER_CODE, data.PART_NO);

    $('#modal-add-ds').modal('show');
}

// ========== SHOW ADD NS MODAL ==========
function showAddNSModal() {
    var row = tableDetailProgress.row($(this).parents('tr'));
    var data = row.data();
    if (!data) return;

    const today = new Date();
    const todayFormatted = today.toISOString().split('T')[0];
    
    // PERBAIKAN: Format date dengan benar
    const rowDateFormatted = formatDate(data.DATE);
    console.log('📅 Show NS Modal - Date:', {
        rawDate: data.DATE,
        formatted: rowDateFormatted,
        today: todayFormatted
    });

    if (rowDateFormatted.replace(/-/g, '') !== todayFormatted.replace(/-/g, '')) {
        showDateBlockedModal({
            rowDateFormatted: rowDateFormatted,
            color: 'warning'
        });
        return;
    }

    currentNSData = data;
    nsSelectedHours = {};

    $('#add-ns-date').val(data.DATE);
    $('#add-ns-supplier').val(data.SUPPLIER_CODE);
    $('#add-ns-partno').val(data.PART_NO);

    $('#txt-ns-date').text(rowDateFormatted);
    $('#txt-ns-supplier').text(data.SUPPLIER_CODE || '');
    $('#txt-ns-partno').text(data.PART_NO || '');
    $('#txt-ns-partname').text(data.PART_NAME || data.PART_DESC || '');

    $('#txt-ns-remark').val('');
    $('#ns-action').val('add');
    $('#ns-total-qty').text('0');
    $('#ns-no-hour-selected').show();

    const now = new Date();
    $('#ns-current-time-display').text(
        now.getHours().toString().padStart(2, '0') + ':' +
        now.getMinutes().toString().padStart(2, '0')
    );

    $('#ns-error-alert, #ns-success-alert').addClass('d-none');

    generateNSHourSelection();
    loadCurrentNSStatus(data.DATE, data.SUPPLIER_CODE, data.PART_NO);

    $('#modal-add-ns').modal('show');
}


// ================= FUNGSI GENERATE HOUR SELECTION =================

function generateDSHourSelection() {
    const $container = $('#ds-hour-selection');
    $container.empty();
    
    const currentHour = new Date().getHours();
    const currentDate = new Date();
    
    // PERBAIKAN: Handle orderDateStr dengan lebih aman
    let orderDate = new Date(); // default ke hari ini
    let orderDateRaw = currentDSData?.DATE;
    
    console.log('🔍 Debug generateDSHourSelection:', {
        orderDateRaw: orderDateRaw,
        typeOfOrderDateRaw: typeof orderDateRaw
    });
    
    // Convert orderDateRaw ke Date object dengan aman
    if (orderDateRaw !== null && orderDateRaw !== undefined) {
        const orderDateStr = String(orderDateRaw); // 🔥 PASTIKAN STRING
        
        // Cek format: jika string 8 digit (YYYYMMDD)
        if (/^\d{8}$/.test(orderDateStr)) {
            const year = orderDateStr.slice(0, 4);
            const month = orderDateStr.slice(4, 6);
            const day = orderDateStr.slice(6, 8);
            orderDate = new Date(`${year}-${month}-${day}`);
            console.log('✅ Parsed date from 8-digit string:', orderDate);
        } else if (orderDateRaw instanceof Date) {
            // Jika sudah Date object
            orderDate = orderDateRaw;
            console.log('✅ Already a Date object');
        } else if (typeof orderDateStr === 'string' && orderDateStr.includes('-')) {
            // Format YYYY-MM-DD
            orderDate = new Date(orderDateStr);
            console.log('✅ Parsed date from YYYY-MM-DD:', orderDate);
        } else {
            console.warn('⚠️ Unknown date format:', orderDateStr);
        }
    } else {
        console.warn('⚠️ orderDateRaw is null/undefined, using current date');
    }
    
    // Cek apakah order date sama dengan hari ini
    const isToday = orderDate.toDateString() === currentDate.toDateString();
    
    console.log('📅 Date comparison:', {
        orderDate: orderDate.toDateString(),
        currentDate: currentDate.toDateString(),
        isToday: isToday,
        currentHour: currentHour
    });
    
    for (let hour = 7; hour <= 20; hour++) {
        const $col = $('<div class="col-2 col-md-1 mb-2"></div>');
        const $btn = $('<button type="button" class="btn btn-outline-primary hour-btn"></button>');
        
        $btn.text(hour.toString().padStart(2, '0'));
        $btn.data('hour', hour);
        
        // Disable jika jam sudah lewat (untuk hari ini)
        if (isToday && hour < currentHour) {
            $btn.addClass('disabled');
            $btn.prop('disabled', true);
            $btn.addClass('btn-secondary');
            $btn.removeClass('btn-outline-primary');
            $btn.attr('title', 'Jam sudah lewat');
        } else {
            $btn.on('click', function() {
                toggleDSHourSelection($(this));
            });
            $btn.attr('title', `Klik untuk tambah order jam ${hour}:00`);
        }
        
        $col.append($btn);
        $container.append($col);
    }
}

function generateNSHourSelection() {
    const $container = $('#ns-hour-selection');
    $container.empty();
    
    const currentHour = new Date().getHours();
    const currentDate = new Date();
    
    // PERBAIKAN: Handle orderDateStr dengan lebih aman
    let orderDate = new Date(); // default ke hari ini
    let orderDateRaw = currentNSData?.DATE;
    
    console.log('🔍 Debug generateNSHourSelection:', {
        orderDateRaw: orderDateRaw,
        typeOfOrderDateRaw: typeof orderDateRaw
    });
    
    // Convert orderDateRaw ke Date object dengan aman
    if (orderDateRaw !== null && orderDateRaw !== undefined) {
        const orderDateStr = String(orderDateRaw); // 🔥 PASTIKAN STRING
        
        // Cek format: jika string 8 digit (YYYYMMDD)
        if (/^\d{8}$/.test(orderDateStr)) {
            const year = orderDateStr.slice(0, 4);
            const month = orderDateStr.slice(4, 6);
            const day = orderDateStr.slice(6, 8);
            orderDate = new Date(`${year}-${month}-${day}`);
            console.log('✅ Parsed date from 8-digit string:', orderDate);
        } else if (orderDateRaw instanceof Date) {
            // Jika sudah Date object
            orderDate = orderDateRaw;
            console.log('✅ Already a Date object');
        } else if (typeof orderDateStr === 'string' && orderDateStr.includes('-')) {
            // Format YYYY-MM-DD
            orderDate = new Date(orderDateStr);
            console.log('✅ Parsed date from YYYY-MM-DD:', orderDate);
        } else {
            console.warn('⚠️ Unknown date format:', orderDateStr);
        }
    } else {
        console.warn('⚠️ orderDateRaw is null/undefined, using current date');
    }
    
    // Cek apakah order date sama dengan hari ini
    const isToday = orderDate.toDateString() === currentDate.toDateString();
    
    console.log('📅 Date comparison:', {
        orderDate: orderDate.toDateString(),
        currentDate: currentDate.toDateString(),
        isToday: isToday,
        currentHour: currentHour
    });
    
    // Jam night shift: 21, 22, 23, 0, 1, 2, 3, 4, 5, 6
    const nsHours = [21, 22, 23, 0, 1, 2, 3, 4, 5, 6];
    
    nsHours.forEach(hour => {
        const $col = $('<div class="col-2 col-md-1 mb-2"></div>');
        const $btn = $('<button type="button" class="btn btn-outline-warning hour-btn"></button>');
        
        $btn.text(hour.toString().padStart(2, '0'));
        $btn.data('hour', hour);
        
        // Logic untuk disable jam yang sudah lewat lebih kompleks untuk night shift
        let shouldDisable = false;
        let disableReason = '';
        
        if (isToday) {
            // Untuk night shift, perlu perhitungan khusus
            // Jika order hari ini, jam 21-23 di hari ini
            // Jika order besok, jam 0-6 di hari berikutnya
            
            if (hour >= 21) {
                // Jam 21-23: disable jika < current hour (di hari yang sama)
                shouldDisable = hour < currentHour;
                if (shouldDisable) {
                    disableReason = 'Jam sudah lewat (hari ini)';
                }
            } else {
                // Jam 0-6: disable untuk hari ini karena harusnya untuk besok
                // Kecuali jika currentHour >= 21 (sudah malam, bisa pilih jam 0-6 untuk besok)
                shouldDisable = currentHour < 21;
                if (shouldDisable) {
                    disableReason = 'Jam 0-6 hanya tersedia setelah jam 21:00 untuk hari berikutnya';
                }
            }
        }
        
        if (shouldDisable) {
            $btn.addClass('disabled');
            $btn.prop('disabled', true);
            $btn.addClass('btn-secondary');
            $btn.removeClass('btn-outline-warning');
            if (disableReason) {
                $btn.attr('title', disableReason);
            }
        } else {
            $btn.on('click', function() {
                toggleNSHourSelection($(this));
            });
            $btn.attr('title', `Klik untuk tambah order jam ${hour.toString().padStart(2, '0')}:00`);
        }
        
        $col.append($btn);
        $container.append($col);
    });
}

// ================= FUNGSI TOGGLE SELECTION =================

function toggleDSHourSelection($btn) {
    const hour = $btn.data('hour');
    
    if ($btn.hasClass('selected')) {
        // Unselect
        $btn.removeClass('selected');
        $btn.removeClass('btn-primary');
        $btn.addClass('btn-outline-primary');
        delete dsSelectedHours[hour];
    } else {
        // Select
        $btn.addClass('selected');
        $btn.removeClass('btn-outline-primary');
        $btn.addClass('btn-primary');
        dsSelectedHours[hour] = 0; // Default quantity 0
    }
    
    updateDSQuantityInputs();
}

function toggleNSHourSelection($btn) {
    const hour = $btn.data('hour');
    
    if ($btn.hasClass('selected')) {
        // Unselect
        $btn.removeClass('selected');
        $btn.removeClass('btn-warning');
        $btn.addClass('btn-outline-warning');
        delete nsSelectedHours[hour];
    } else {
        // Select
        $btn.addClass('selected');
        $btn.removeClass('btn-outline-warning');
        $btn.addClass('btn-warning');
        nsSelectedHours[hour] = 0; // Default quantity 0
    }
    
    updateNSQuantityInputs();
}

// ================= FUNGSI UPDATE QUANTITY INPUTS =================

function updateDSQuantityInputs() {
    const $container = $('#ds-quantity-container');
    
    if (Object.keys(dsSelectedHours).length === 0) {
        $container.html('<div class="alert alert-info" id="ds-no-hour-selected">' +
                       '<i class="bi bi-info-circle me-2"></i>' +
                       'Pilih jam terlebih dahulu di atas' +
                       '</div>');
        $('#ds-total-qty').text('0');
        return;
    }
    
    $container.empty();
    let totalQty = 0;
    
    // Sort hours
    const sortedHours = Object.keys(dsSelectedHours).sort((a, b) => a - b);
    
    sortedHours.forEach(hour => {
        const hourStr = hour.toString().padStart(2, '0');
        const currentQty = dsSelectedHours[hour];
        
        const $group = $(`
            <div class="quantity-input-group">
                <div class="row align-items-center">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="form-label mb-0">
                            <i class="bi bi-clock me-1"></i>
                            Jam ${hourStr}:00
                        </label>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary btn-sm ds-hour-decrease" data-hour="${hour}">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" class="form-control text-center ds-hour-qty" 
                                   data-hour="${hour}" value="${currentQty}" min="0" max="9999">
                            <button type="button" class="btn btn-outline-secondary btn-sm ds-hour-increase" data-hour="${hour}">
                                <i class="bi bi-plus"></i>
                            </button>
                            <span class="input-group-text">pcs</span>
                        </div>
                    </div>
                    <div class="col-md-3 mt-2 mt-md-0 text-md-end">
                        <button type="button" class="btn btn-sm btn-outline-danger ds-hour-remove" data-hour="${hour}">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        `);
        
        $container.append($group);
        totalQty += parseInt(currentQty) || 0;
    });
    
    $('#ds-total-qty').text(totalQty);
    
    // Bind events
    $('.ds-hour-increase').on('click', function() {
        const hour = $(this).data('hour');
        dsSelectedHours[hour] = (parseInt(dsSelectedHours[hour]) || 0) + 1;
        updateDSQuantityInputs();
    });
    
    $('.ds-hour-decrease').on('click', function() {
        const hour = $(this).data('hour');
        const current = parseInt(dsSelectedHours[hour]) || 0;
        if (current > 0) {
            dsSelectedHours[hour] = current - 1;
            updateDSQuantityInputs();
        }
    });
    
    $('.ds-hour-qty').on('input', function() {
        const hour = $(this).data('hour');
        const value = parseInt($(this).val()) || 0;
        dsSelectedHours[hour] = Math.max(0, Math.min(9999, value));
        updateDSQuantityInputs();
    });
    
    $('.ds-hour-remove').on('click', function() {
        const hour = $(this).data('hour');
        // Remove from selection
        $(`#ds-hour-selection button[data-hour="${hour}"]`).removeClass('selected btn-primary').addClass('btn-outline-primary');
        delete dsSelectedHours[hour];
        updateDSQuantityInputs();
    });
}

function updateNSQuantityInputs() {
    const $container = $('#ns-quantity-container');
    
    if (Object.keys(nsSelectedHours).length === 0) {
        $container.html('<div class="alert alert-info" id="ns-no-hour-selected">' +
                       '<i class="bi bi-info-circle me-2"></i>' +
                       'Pilih jam terlebih dahulu di atas' +
                       '</div>');
        $('#ns-total-qty').text('0');
        return;
    }
    
    $container.empty();
    let totalQty = 0;
    
    // Sort hours khusus untuk NS (21-23, 0-6)
    const sortedHours = Object.keys(nsSelectedHours).sort((a, b) => {
        const aNum = parseInt(a);
        const bNum = parseInt(b);
        // Urutkan: 21-23 dulu, lalu 0-6
        if (aNum >= 21 && bNum >= 21) return aNum - bNum;
        if (aNum < 21 && bNum < 21) return aNum - bNum;
        return aNum >= 21 ? -1 : 1;
    });
    
    sortedHours.forEach(hour => {
        const hourStr = hour.toString().padStart(2, '0');
        const currentQty = nsSelectedHours[hour];
        
        const $group = $(`
            <div class="quantity-input-group">
                <div class="row align-items-center">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="form-label mb-0">
                            <i class="bi bi-clock me-1"></i>
                            Jam ${hourStr}:00
                        </label>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary btn-sm ns-hour-decrease" data-hour="${hour}">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" class="form-control text-center ns-hour-qty" 
                                   data-hour="${hour}" value="${currentQty}" min="0" max="9999">
                            <button type="button" class="btn btn-outline-secondary btn-sm ns-hour-increase" data-hour="${hour}">
                                <i class="bi bi-plus"></i>
                            </button>
                            <span class="input-group-text">pcs</span>
                        </div>
                    </div>
                    <div class="col-md-3 mt-2 mt-md-0 text-md-end">
                        <button type="button" class="btn btn-sm btn-outline-danger ns-hour-remove" data-hour="${hour}">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        `);
        
        $container.append($group);
        totalQty += parseInt(currentQty) || 0;
    });
    
    $('#ns-total-qty').text(totalQty);
    
    // Bind events
    $('.ns-hour-increase').on('click', function() {
        const hour = $(this).data('hour');
        nsSelectedHours[hour] = (parseInt(nsSelectedHours[hour]) || 0) + 1;
        updateNSQuantityInputs();
    });
    
    $('.ns-hour-decrease').on('click', function() {
        const hour = $(this).data('hour');
        const current = parseInt(nsSelectedHours[hour]) || 0;
        if (current > 0) {
            nsSelectedHours[hour] = current - 1;
            updateNSQuantityInputs();
        }
    });
    
    $('.ns-hour-qty').on('input', function() {
        const hour = $(this).data('hour');
        const value = parseInt($(this).val()) || 0;
        nsSelectedHours[hour] = Math.max(0, Math.min(9999, value));
        updateNSQuantityInputs();
    });
    
    $('.ns-hour-remove').on('click', function() {
        const hour = $(this).data('hour');
        // Remove from selection
        $(`#ns-hour-selection button[data-hour="${hour}"]`).removeClass('selected btn-warning').addClass('btn-outline-warning');
        delete nsSelectedHours[hour];
        updateNSQuantityInputs();
    });
}

function loadCurrentDSStatus(date, supplier, partNo) {
    $.ajax({
        url: 'api/get_add_order_status.php',
        type: 'GET',
        data: {
            date: date,
            supplier_code: supplier,
            part_no: partNo,
            type: 'ds'
        },
        success: function(response) {
            console.log('Current DS Status:', response);
            
            if (response.success) {
                const currentQty = response.current_qty || 0;
                const lastUpdated = response.last_updated || '';
                const lastBy = response.last_by || '';
                
                if (currentQty > 0) {
                    $('#ds-status-text').html(`
                        Current add order: <span class="badge bg-primary">${currentQty} pcs</span>
                        ${lastBy ? `<br><small class="text-muted">Last by ${lastBy} on ${lastUpdated}</small>` : ''}
                    `);
                    $('#btn-reset-ds').show();
                    $('#txt-ds-remark').val(response.remark || '');
                    $('#ds-action').val('update');
                    
                    // Load hours data dari response
                    const hoursData = response.hours_data || {};
                    dsSelectedHours = hoursData;
                    
                    // Select the hours in UI
                    Object.keys(hoursData).forEach(hour => {
                        $(`#ds-hour-selection button[data-hour="${hour}"]`).each(function() {
                            if (!$(this).hasClass('disabled')) {
                                $(this).addClass('selected btn-primary').removeClass('btn-outline-primary');
                            }
                        });
                    });
                    
                    updateDSQuantityInputs();
                } else {
                    $('#ds-status-text').text('No add order yet');
                    $('#btn-reset-ds').hide();
                }
            }
        },
        error: function(xhr) {
            console.error('Error loading DS status:', xhr);
        }
    });
}

function loadCurrentNSStatus(date, supplier, partNo) {
    $.ajax({
        url: 'api/get_add_order_status.php',
        type: 'GET',
        data: {
            date: date,
            supplier_code: supplier,
            part_no: partNo,
            type: 'ns'
        },
        success: function(response) {
            console.log('Current NS Status:', response);
            
            if (response.success) {
                const currentQty = response.current_qty || 0;
                const lastUpdated = response.last_updated || '';
                const lastBy = response.last_by || '';
                
                if (currentQty > 0) {
                    $('#ns-status-text').html(`
                        Current add order: <span class="badge bg-warning">${currentQty} pcs</span>
                        ${lastBy ? `<br><small class="text-muted">Last by ${lastBy} on ${lastUpdated}</small>` : ''}
                    `);
                    $('#btn-reset-ns').show();
                    $('#txt-ns-remark').val(response.remark || '');
                    $('#ns-action').val('update');
                    
                    // Load hours data dari response
                    const hoursData = response.hours_data || {};
                    nsSelectedHours = hoursData;
                    
                    // Select the hours in UI
                    Object.keys(hoursData).forEach(hour => {
                        $(`#ns-hour-selection button[data-hour="${hour}"]`).each(function() {
                            if (!$(this).hasClass('disabled')) {
                                $(this).addClass('selected btn-warning').removeClass('btn-outline-warning');
                            }
                        });
                    });
                    
                    updateNSQuantityInputs();
                } else {
                    $('#ns-status-text').text('No add order yet');
                    $('#btn-reset-ns').hide();
                }
            }
        },
        error: function(xhr) {
            console.error('Error loading NS status:', xhr);
        }
    });
}


// ================= CONFIGURATION =================
const picSupplierMap = {
    "SATRIO": ["B78", "C30", "B79", "C60", "B87", "A31", "B54", "B38", "B61", "B77", "A84", "C64", "B98", "A72", "A07"],
    "MURSID": ["B25", "C09", "B59", "C36", "A95", "C38", "C97", "A03", "A39", "C81", "A52", "B14", "B24", "B63", "B65", "C46", "C04"],
    "EKO": ["C87", "B04", "B01", "A20", "C40", "A12", "B91", "A21", "A47", "B08", "A44", "B70", "B12", "B93", "D05", "A57", "B48", "D14", "B13", "A93", "C55", "A63", "A89", "C84", "A53"],
    "EKA": ["B56", "A09", "A96", "A25", "B82", "A27", "A02", "B05", "D07", "B95", "C42", "A14", "B96", "D11", "A15", "C17", "B23", "A48", "C79", "C29", "C24", "B37", "B69", "B45", "A08", "A22", "C25", "C73", "A85", "B32", "B53", "A59"],
    "ALBERTO": ["C92", "B86", "B62", "C41"]
};

const allSupplierCodes = [
    "C87", "C92", "B78", "B56", "B04", "A09", "A96", "A80", "A42", "A25", "C30", "B82", "B86", "B01", "A20", "C40", "B79", "A12", "B91", "A27", "C60", "A02", "B87", "B05", "A31", "A21", "B54", "A47", "B38", "D07", "B25", "B95", "C09", "C42", "B59", "B08", "A54", "C39", "A44", "C36", "B62", "B88", "A19", "B89", "B67", "A14", "B61", "B96", "A90", "A95", "C38", "C97", "A03", "A39", "C41", "B70", "D11", "B77", "B12", "B90", "B93", "D05", "A15", "A57", "C17", "B48", "D14", "B23", "B13", "A48", "A93", "C79", "C29", "C68", "C24", "B37", "C57", "A84", "C55", "B55", "A63", "A89", "C84", "C81", "C64", "B69", "B45", "B98", "A08", "A52", "B14", "B24", "B63", "A22", "B65", "C46", "C04", "A04", "A72", "C25", "C73", "A53", "B94", "A07", "A85", "B32", "B53", "A59", "A73"
];

// ================= GLOBAL VARIABLES UPDATED =================
let dsActualMap = {};  
let nsActualMap = {};  
let tableDetailProgress;
let tableInformation;
let tableByCycle;
let tableDetailDS;
let tableDetailNS;

let rangeDate1, rangeDate2;
let ajaxDone = 0;
let accumDataAll = [];
let accumTableParams = {page: 1, pageSize: 10, search: '', sort: 'SUPPLIER_CODE', daysInMonth: 31, selectedTxt: '', year: '', month: ''};

// VARIABEL FILTER GLOBAL
let globalFilters = {
    pic: [],
    supplierCode: [],
    status: []
};

// ================= UPDATE FILTER FUNCTIONS =================
function updateGlobalFilters() {
    // Ambil nilai dari filter
    globalFilters.pic = $('#select-pic').val() || [];
    globalFilters.supplierCode = $('#select-supplier-code').val() || [];
    globalFilters.status = $('#select-status').val() || [];
    
    // Jika ada "select-all", konversi ke semua nilai
    if (globalFilters.supplierCode.includes('select-all')) {
        globalFilters.supplierCode = allSupplierCodes;
    }
    
    console.log('🔄 Filter global diperbarui:', globalFilters);
}

function getFilteredSupplierCodes() {
    // Jika ada PIC yang dipilih, ambil supplier code dari PIC tersebut
    if (globalFilters.pic.length > 0 && !globalFilters.pic.includes('select-all')) {
        let filteredCodes = [];
        globalFilters.pic.forEach(pic => {
            if (picSupplierMap[pic]) {
                filteredCodes = filteredCodes.concat(picSupplierMap[pic]);
            }
        });
        
        // Jika ada supplier code spesifik yang dipilih, intersect
        if (globalFilters.supplierCode.length > 0) {
            filteredCodes = filteredCodes.filter(code => 
                globalFilters.supplierCode.includes(code)
            );
        }
        
        return [...new Set(filteredCodes)];
    }
    
    // Jika tidak ada PIC yang dipilih, pakai supplier code yang dipilih
    return globalFilters.supplierCode;
}

// ================= VARIABEL BARU UNTUK MODAL D/S & N/S =================
let dsCurrentPage = 1;
let dsPageSize = 10;
let dsFilteredData = [];
let dsIsDragging = false;
let dsStartX = 0;
let dsScrollLeft = 0;

let nsCurrentPage = 1;
let nsPageSize = 10;
let nsFilteredData = [];
let nsIsDragging = false;
let nsStartX = 0;
let nsScrollLeft = 0;

// ================= LOADER MANAGEMENT =================
let loaderTimeout = null;
const LOADER_DELAY = 1500;

function showLoader(force = false) {
    if (loaderTimeout) {
        clearTimeout(loaderTimeout);
        loaderTimeout = null;
    }
    
    if (force) {
        $("#overlay").fadeIn(300);
    } else {
        loaderTimeout = setTimeout(() => {
            if (!$("#overlay").is(":visible")) {
                $("#overlay").fadeIn(300);
            }
        }, LOADER_DELAY);
    }
}

function hideLoader() {
    if (loaderTimeout) {
        clearTimeout(loaderTimeout);
        loaderTimeout = null;
    }
    
    $("#overlay").fadeOut(300);
}

$(document).ready(function() {
    setTimeout(hideLoader, 3000);
    
    $(document).ajaxStop(function() {
        hideLoader();
        clearTimeout(initialLoadTimeout);
    });
});

let initialLoadTimeout = setTimeout(() => {
    hideLoader();
    console.log('Initial load timeout - hiding loader');
}, 5000);

// ================= DATE RANGE VALIDATION FUNCTIONS =================
function validateDateRange() {
    const date1 = $('#range-date1').val();
    const date2 = $('#range-date2').val();
    
    if (date1 && date2) {
        const d1 = new Date(date1);
        const d2 = new Date(date2);
        
        if (d2 < d1) {
            $('#range-date2').val(date1);
            rangeDate2 = date1;
            return false;
        }
    }
    return true;
}

function setDatepickerMinMax() {
    const date1 = $('#range-date1').val();
    const date2 = $('#range-date2').val();
    
    if (date1) {
        $('#range-date2').datepicker('setStartDate', date1);
    }
    
    if (date2) {
        $('#range-date1').datepicker('setEndDate', date2);
    }
}

function showDateRangeError(message) {
    $('#dateRangeError').remove();
    
    const errorHtml = `
        <div id="dateRangeError" class="position-fixed top-0 start-50 translate-middle-x mt-5" 
             style="z-index: 9999; animation: slideDown 0.3s ease-out;">
            <div class="alert alert-danger alert-dismissible fade show shadow-lg" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Date Range Error:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    $('body').append(errorHtml);
    
    setTimeout(() => {
        $('#dateRangeError').fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);
}

function updateDateInputStyles(isValid) {
    const $date1 = $('#range-date1');
    const $date2 = $('#range-date2');
    
    if (isValid) {
        $date1.removeClass('invalid');
        $date2.removeClass('invalid');
    } else {
        $date1.addClass('invalid');
        $date2.addClass('invalid');
    }
}

// ================= DATE RANGE FUNCTIONS =================
function setDefaultDateRange() {
    const now = new Date();
    const offset = 7;
    const localDate = new Date(now.getTime() + (offset * 60 * 60 * 1000));
    const today = localDate.toISOString().split('T')[0];
    
    console.log('🕐 Setting default date range:', today, 'Local time:', now.getHours());
    
    if (!$('#range-date1').val()) {
        $('#range-date1').val(today);
        rangeDate1 = today;
    }
    if (!$('#range-date2').val()) {
        $('#range-date2').val(today);
        rangeDate2 = today;
    }
}

function clearDateRange() {
    $('#range-date1').val('');
    $('#range-date2').val('');
    rangeDate1 = '';
    rangeDate2 = '';
    
    updateDateInputStyles(true);
    
    loadTableDetailProgress();
    fetchDataInformation();
}

// ================= EVENT HANDLER FOR DATE CHANGES =================
function handleDateChange() {
    const date1 = $('#range-date1').val();
    const date2 = $('#range-date2').val();
    
    if (!date1 && !date2) {
        updateDateInputStyles(true);
        return;
    }
    
    if (date1 && date2) {
        const isValid = validateDateRange(date1, date2);
        
        if (!isValid) {
            showDateRangeError('End date cannot be earlier than start date');
            updateDateInputStyles(false);
            return;
        } else {
            updateDateInputStyles(true);
        }
    }
    
    rangeDate1 = date1;
    rangeDate2 = date2;
    
    if (date1 && date2 && validateDateRange(date1, date2)) {
        loadTableDetailProgress();
        fetchDataInformation();
    }
}

// ================= UTILITY FUNCTIONS =================
function safeTrim(val) {
    return (val ?? '').toString().trim();
}

function safeParseInt(val) {
    return parseInt(val) || 0;
}

function pad(n) {
    return n < 10 ? '0' + n : n;
}

// ================= FUNGSI BARU UNTUK PARSE ETA =================
function parseETAHour(eta) {
    if (!eta) return null;
    
    try {
        const timeStr = String(eta).trim();
        
        // Format "13:30", "07:45", dll
        if (timeStr.includes(':')) {
            const parts = timeStr.split(':');
            if (parts.length >= 1) {
                const hour = parseInt(parts[0]);
                if (!isNaN(hour) && hour >= 0 && hour <= 23) {
                    return hour;
                }
            }
        }
        
        // Format "0730", "1330" (4 digit)
        if (/^\d{4}$/.test(timeStr)) {
            const hour = parseInt(timeStr.substring(0, 2));
            if (!isNaN(hour) && hour >= 0 && hour <= 23) {
                return hour;
            }
        }
        
        // Format "730", "1330" (3-4 digit tanpa leading zero)
        if (/^\d{3,4}$/.test(timeStr)) {
            if (timeStr.length === 3) {
                const hour = parseInt(timeStr.substring(0, 1));
                if (!isNaN(hour) && hour >= 0 && hour <= 23) {
                    return hour;
                }
            } else if (timeStr.length === 4) {
                const hour = parseInt(timeStr.substring(0, 2));
                if (!isNaN(hour) && hour >= 0 && hour <= 23) {
                    return hour;
                }
            }
        }
        
        // Coba parse sebagai number langsung
        const hourNum = parseInt(timeStr);
        if (!isNaN(hourNum) && hourNum >= 0 && hourNum <= 23) {
            return hourNum;
        }
        
    } catch (e) {
        console.warn('Error parsing ETA:', eta, e);
    }
    
    return null;
}

function getCurrentDateTime() {
    const now = new Date();
    const hours = pad(now.getHours());
    const minutes = pad(now.getMinutes());
    return `${hours}:${minutes}`;
}

function formatDate(data) {
    if (!data) return '';  
    var dateStr = String(data);  
    if (/^\d{8}$/.test(dateStr)) {  
        return dateStr.slice(0,4) + '-' + dateStr.slice(4,6) + '-' + dateStr.slice(6,8);  
    }  
    return dateStr;  
}

function handleAjaxError(xhr, errorMsg = "Terjadi kesalahan") {
    console.error("AJAX Error:", xhr.status, xhr.statusText, xhr.responseText);
    
    let message = errorMsg;
    if (xhr.status === 0) {
        message = "Network error - periksa koneksi internet";
    } else if (xhr.status === 500) {
        message = "Server error (500)";
    } else if (xhr.responseText) {
        try {
            const err = JSON.parse(xhr.responseText);
            message = err.message || err.error || message;
        } catch (e) {
            message = xhr.responseText.substring(0, 100);
        }
    }
    
    swal({
        title: "Error!",
        text: message,
        type: "error",
        button: "OK"
    });
}

// ================= DATA RENDER FUNCTIONS =================
function renderAddDSButton(data, type, row, meta) {  
    return '<button class="btn btn-dark btn-update-add1"><i class="bi bi-pencil"></i></button>';  
}

function renderAddNSButton(data, type, row, meta) {  
    return '<button class="btn btn-dark btn-update-add2"><i class="bi bi-pencil"></i></button>';  
}

function calculateDSTotal(data, type, row) {  
    var regular = parseInt(row.REGULER_DS) || 0;  
    var add = parseInt(row.ADD_DS) || 0;  
    return regular + add;  
}

function calculateNSTotal(data, type, row) {  
    var regular = parseInt(row.REGULER_NS) || 0;  
    var add = parseInt(row.ADD_NS) || 0;  
    return regular + add;  
}

// ================= FUNGSI STATUS BARU - PAKE LOGIKA SERVER =================
function renderStatusBadge(status) {
    const statusMap = {
        'OK': '<span class="badge bg-success">✅ COMPLETED</span>',
        'ON_PROGRESS': '<span class="badge bg-primary">🔄 ON PROGRESS</span>',
        'DELAY': '<span class="badge bg-danger">⚠️ DELAY</span>',
        'OVER': '<span class="badge bg-warning">📈 OVER</span>'
    };
    
    return statusMap[status] || '<span class="badge bg-secondary">-</span>';
}

// Fungsi render status D/S (pakai DS_STATUS dari server)
function getDSStatus(data, type, row) {
    var status = row.DS_STATUS || row.STATUS || '';
    
    return renderStatusBadge(status);
}

// Fungsi render status N/S (pakai NS_STATUS dari server)
function getNSStatus(data, type, row) {
    var status = row.NS_STATUS || row.STATUS || '';
    
    return renderStatusBadge(status);
}

// Fungsi render status total
function getTotalStatus(data, type, row) {
    var status = row.STATUS || '';
    
    return renderStatusBadge(status);
}

function getRemarks(data, type, row) {
    var remarks = [];
    if (row.REMARK_DS && safeTrim(row.REMARK_DS) !== '') {
        remarks.push('D/S: ' + row.REMARK_DS);
    }
    if (row.REMARK_NS && safeTrim(row.REMARK_NS) !== '') {
        remarks.push('N/S: ' + row.REMARK_NS);
    }
    return remarks.join('<br>');
}

// ================= SELECTIZE FUNCTIONS =================
function updateSupplierCodes(selectedPic) {
    var supplierSelectize = $('#select-supplier-code')[0].selectize;
    
    if (!supplierSelectize) {
        console.warn('Selectize not initialized yet, delaying...');
        setTimeout(function() {
            updateSupplierCodes(selectedPic);
        }, 100);
        return;
    }
    
    supplierSelectize.clearOptions();
    supplierSelectize.addOption({value: '', text: ''});
    supplierSelectize.addOption({value: 'select-all', text: 'Select All'});

    let codesToShow = [];
    if (selectedPic === 'select-all' || selectedPic === '' || !selectedPic.length) {
        codesToShow = allSupplierCodes;
    } else {
        selectedPic.forEach(pic => {
            if (picSupplierMap[pic]) {
                codesToShow = codesToShow.concat(picSupplierMap[pic]);
            }
        });
        codesToShow = [...new Set(codesToShow)];
    }

    codesToShow.forEach(code => {
        supplierSelectize.addOption({value: code, text: code});
    });

    supplierSelectize.setValue([]);
}

function refreshActualMaps() {
    dsActualMap = {};  
    nsActualMap = {};  
    
    let date1 = $('#range-date1').val();  
    let date2 = $('#range-date2').val();  
    
    if (!date1 || !date2) {
        console.log('⚠️ Date range belum dipilih');
        return;
    }
    
    let sendDate1 = date1.replace(/-/g, '');
    let sendDate2 = date2.replace(/-/g, '');
    
    console.log('🔄 Refreshing actual maps dengan LOGIC BARU (SELISIH PER JAM)');
    
    // DAY SHIFT - PAKAI QUERY BARU YANG SUDAH PERHITUNGAN INCOMING PER JAM
    $.ajax({  
        url: 'modules/data_day_shift1.php',  
        type: 'GET',
        data: {
            date1: sendDate1,
            date2: sendDate2
        },
        dataType: 'json',  
        success: function(response) {
            console.log('✅ DS Actual Response (LOGIC BARU - SELISIH):', {
                count: response.count,
                total_incoming: response.total_incoming,
                logic: response.logic || 'NEW: incoming_per_hour = current - previous'
            });
            
            if (!response.success) {
                console.error('❌ DS API Error:', response.message);
                return;
            }
            
            const data = response.data || [];
            const totalIncoming = response.total_incoming || 0;
            
            console.log('📊 DS Data loaded:', data.length, 'records');
            
            // Reset map
            dsActualMap = {};
            
            $.each(data, function(index, item) {  
                var key = safeTrim(item.DATE) + '|' + safeTrim(item.SUPPLIER_CODE) + '|' + safeTrim(item.PART_NO);  
                
                // 🔥 SEKARANG TOTAL_INCOMING SUDAH BENAR (SUM INCOMING PER JAM, BUKAN NILAI AKUMULATIF)
                var total_incoming = parseInt(item.TOTAL_INCOMING) || 0;
                
                // Debug: cek apakah ini SUM incoming per jam
                let sumFromHourly = 0;
                for(let hour = 8; hour <= 20; hour++) {
                    let hourKey = hour < 10 ? 'TRAN_0' + hour : 'TRAN_' + hour;
                    sumFromHourly += parseInt(item[hourKey] || 0);
                }
                
                console.log(`🔍 DS Item ${index+1}:`, {
                    key: key,
                    total_incoming_api: total_incoming,
                    sum_from_hourly: sumFromHourly,
                    incoming_08: item.TRAN_08,
                    incoming_09: item.TRAN_09,
                    incoming_10: item.TRAN_10
                });
                
                dsActualMap[key] = total_incoming;  
            });  
            
            console.log('📊 DS Actual Map updated:', Object.keys(dsActualMap).length, 'keys');
            
            // Sample beberapa key untuk debugging
            const sampleKeys = Object.keys(dsActualMap).slice(0, 2);
            sampleKeys.forEach(key => {
                console.log('🔑 Sample DS Key:', key, '=', dsActualMap[key]);
            });
            
            // Refresh table progress jika ada
            if (typeof tableDetailProgress !== 'undefined' && tableDetailProgress) {
                console.log('🔄 Refreshing progress table...');
                tableDetailProgress.draw();
            }
        },  
        error: function(xhr, status, error) {  
            console.error("❌ Error refreshing DS actual map:", {
                status: xhr.status,
                statusText: xhr.statusText,
                error: error,
                response: xhr.responseText
            });  
        }  
    });  
    
    // NIGHT SHIFT - PAKAI QUERY BARU
    $.ajax({  
        url: 'modules/data_night_shift1.php',  
        type: 'GET',
        data: {
            date1: sendDate1,
            date2: sendDate2
        },
        dataType: 'json',  
        success: function(response) {
            console.log('✅ NS Actual Response (LOGIC BARU - SELISIH):', {
                count: response.count,
                total_incoming: response.total_incoming,
                logic: response.logic || 'NEW: incoming_per_hour = current - previous'
            });
            
            if (!response.success) {
                console.error('❌ NS API Error:', response.message);
                return;
            }
            
            const data = response.data || [];
            const totalIncoming = response.total_incoming || 0;
            
            console.log('📊 NS Data loaded:', data.length, 'records');
            
            // Reset map
            nsActualMap = {};
            
            $.each(data, function(index, item) {  
                var key = safeTrim(item.DATE) + '|' + safeTrim(item.SUPPLIER_CODE) + '|' + safeTrim(item.PART_NO);  
                
                // 🔥 TOTAL_INCOMING SUDAH BENAR
                var total_incoming = parseInt(item.TOTAL_INCOMING) || 0;
                
                nsActualMap[key] = total_incoming;  
            });  
            
            console.log('📊 NS Actual Map updated:', Object.keys(nsActualMap).length, 'keys');
            
            // Sample beberapa key untuk debugging
            const sampleKeys = Object.keys(nsActualMap).slice(0, 2);
            sampleKeys.forEach(key => {
                console.log('🔑 Sample NS Key:', key, '=', nsActualMap[key]);
            });
            
            // Refresh table progress jika ada
            if (typeof tableDetailProgress !== 'undefined' && tableDetailProgress) {
                console.log('🔄 Refreshing progress table...');
                tableDetailProgress.draw();
            }
        },  
        error: function(xhr, status, error) {  
            console.error("❌ Error refreshing NS actual map:", {
                status: xhr.status,
                statusText: xhr.statusText,
                error: error,
                response: xhr.responseText
            });  
        }  
    });  
}

function loadTableDetailProgress() {  
    let date1 = $('#range-date1').val();  
    let date2 = $('#range-date2').val();  
    let sendDate1 = date1 ? date1.replace(/-/g, '') : '';  
    let sendDate2 = date2 ? date2.replace(/-/g, '') : '';  
    
    // PAKAI FILTER GLOBAL
    let supplierCodeArr = getFilteredSupplierCodes();
    let supplierCode = supplierCodeArr ? supplierCodeArr.join(',') : '';  
    
    console.log('🔄 Loading progress dengan filter:', {
        date1: sendDate1,
        date2: sendDate2,
        supplierCodes: supplierCodeArr,
        status: globalFilters.status
    });
    
    showTableSkeleton('#table-detail-progress tbody', 10);
    
    $('#table-detail-progress_wrapper .alert-warning').remove();
    
    $.ajax({  
        url: 'modules/data_progress_by_pn.php',  
        method: 'GET',  
        dataType: 'json',  
        data: {  
            date1: sendDate1,  
            date2: sendDate2,  
            supplier_code: supplierCode,  
        },  
        success: function(response) {
            const data = Array.isArray(response)
                ? response
                : (response?.data || []);

            if (!Array.isArray(data)) {
                console.warn("Format response data_progress_by_pn salah:", response);
                hideTableSkeleton('#table-detail-progress tbody');
                return;
            }
            
            console.log("Data progress loaded:", data.length, "rows");
            
            // Filter tambahan berdasarkan status di client side
            let filteredData = data;
            if (globalFilters.status.length > 0 && !globalFilters.status.includes('select-all')) {
                filteredData = filteredData.filter(row => 
                    globalFilters.status.includes(row.STATUS)
                );
                console.log("Setelah filter status:", filteredData.length, "rows");
            }
            
            let displayData = filteredData;
            if (filteredData.length > 500) {
                console.warn("Data terlalu banyak (" + filteredData.length + " rows), limiting to 500 rows");
                displayData = filteredData.slice(0, 500);
                
                if ($('#table-detail-progress_wrapper .alert-warning').length === 0) {
                    $('#table-detail-progress_wrapper').prepend(
                        '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
                        '<i class="bi bi-exclamation-triangle me-2"></i>' +
                        'Data terlalu besar (' + filteredData.length + ' rows). Menampilkan 500 rows pertama. Gunakan filter yang lebih spesifik.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>'
                    );
                }
            }
            else {
                $('#table-detail-progress_wrapper .alert-warning').remove();
            }
            
            tableDetailProgress.clear().rows.add(displayData).draw();
            hideTableSkeleton('#table-detail-progress tbody');
        },  
        error: function(xhr) {  
            console.log("Error request modules/data_progress_by_pn.php", xhr.status);  
            hideTableSkeleton('#table-detail-progress tbody');
        }  
    });  
}

function showTableSkeleton(selector, rows = 5) {
    const $tbody = $(selector);
    let skeletonHTML = '';
    
    for (let i = 0; i < rows; i++) {
        skeletonHTML += `
            <tr class="skeleton-row">
                <td><div class="skeleton"></div></td>
                <td><div class="skeleton"></div></td>
                <td><div class="skeleton"></div></td>
                <td><div class="skeleton"></div></td>
                <td><div class="skeleton"></div></td>
                <td><div class="skeleton"></div></td>
                <td><div class="skeleton"></div></td>
                <td><div class="skeleton"></div></td>
                <td><div class="skeleton"></div></td>
                <td><div class="skeleton"></div></td>
            </tr>
        `;
    }
    
    $tbody.html(skeletonHTML).addClass('table-loading');
}

function hideTableSkeleton(selector) {
    $(selector).removeClass('table-loading');
}

// ================= FUNGSI HELPER UNTUK LOADING/ERROR/EMPTY STATE =================
function showDSLoadingSkeleton() {
    const $tbody = $('#table-detail-ds tbody');
    let skeletonHTML = '';
    
    for (let i = 0; i < 5; i++) {
        skeletonHTML += `
            <tr class="table-loading-skeleton">
                <td colspan="21">
                    <div class="placeholder-glow">
                        <span class="placeholder col-12"></span>
                    </div>
                </td>
            </tr>
        `;
    }
    
    $tbody.html(skeletonHTML);
}

function showDSEmptyState() {
    const $tbody = $('#table-detail-ds tbody');
    $tbody.html(`
        <tr>
            <td colspan="21" class="text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                <h5 class="mt-3">No day shift data available</h5>
                <p class="mb-0">No data found for the selected date range</p>
            </td>
        </tr>
    `);
}

function showDSErrorState() {
    const $tbody = $('#table-detail-ds tbody');
    $tbody.html(`
        <tr>
            <td colspan="21" class="text-center text-danger py-4">
                <i class="bi bi-exclamation-triangle" style="font-size: 48px;"></i>
                <h5 class="mt-3">Failed to load data</h5>
                <p class="mb-0">Please try again later</p>
                <button class="btn btn-sm btn-outline-danger mt-2" onclick="loadDSData()">
                    <i class="bi bi-arrow-clockwise"></i> Retry
                </button>
            </td>
        </tr>
    `);
}

function showNSLoadingSkeleton() {
    const $tbody = $('#table-detail-ns tbody');
    let skeletonHTML = '';
    
    for (let i = 0; i < 5; i++) {
        skeletonHTML += `
            <tr class="table-loading-skeleton">
                <td colspan="18">
                    <div class="placeholder-glow">
                        <span class="placeholder col-12"></span>
                    </div>
                </td>
            </tr>
        `;
    }
    
    $tbody.html(skeletonHTML);
}

function showNSEmptyState() {
    const $tbody = $('#table-detail-ns tbody');
    $tbody.html(`
        <tr>
            <td colspan="18" class="text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                <h5 class="mt-3">No night shift data available</h5>
                <p class="mb-0">No data found for the selected date range</p>
            </td>
        </tr>
    `);
}

function showNSErrorState() {
    const $tbody = $('#table-detail-ns tbody');
    $tbody.html(`
        <tr>
            <td colspan="18" class="text-center text-danger py-4">
                <i class="bi bi-exclamation-triangle" style="font-size: 48px;"></i>
                <h5 class="mt-3">Failed to load data</h5>
                <p class="mb-0">Please try again later</p>
                <button class="btn btn-sm btn-outline-danger mt-2" onclick="loadNSData()">
                    <i class="bi bi-arrow-clockwise"></i> Retry
                </button>
            </td>
        </tr>
    `);
}

// ================= FUNGSI LOAD DS DATA DENGAN PAGINATION =================
function loadDSData() {
    let date1 = $('#range-date1').val();
    let date2 = $('#range-date2').val();
    
    if (!date1 || !date2) {
        alert("Pilih tanggal terlebih dahulu");
        return;
    }
    
    let sendDate1 = date1.replace(/-/g, '');
    let sendDate2 = date2.replace(/-/g, '');
    
    dsCurrentPage = 1;
    dsPageSize = parseInt($('#ds-page-size').val()) || 10;
    
    // AMBIL FILTER SUPPLIER CODE
    let filteredSupplierCodes = getFilteredSupplierCodes();
    let supplierParam = '';
    if (filteredSupplierCodes && filteredSupplierCodes.length > 0) {
        supplierParam = filteredSupplierCodes.join(',');
    }
    
    console.log("🔄 Loading DS Data dengan filter:", {
        date1: sendDate1,
        date2: sendDate2,
        supplierCodes: filteredSupplierCodes,
        supplierParam: supplierParam
    });
    
    showDSLoadingSkeleton();
    
    $.ajax({
        url: 'modules/data_day_shift1.php',
        type: 'GET',
        data: {
            date1: sendDate1,
            date2: sendDate2,
            supplier_code: supplierParam // TAMBAH PARAMETER INI
        },
        dataType: 'json',
        success: function(response) {
            console.log("Day Shift Data Response dengan filter:", {
                supplierCodes: filteredSupplierCodes,
                count: filteredSupplierCodes ? filteredSupplierCodes.length : 0,
                responseData: response
            });
            
            const data = Array.isArray(response)
                ? response
                : (response?.data || []);
            
            if (!Array.isArray(data) || data.length === 0) {
                showDSEmptyState();
                return;
            }
            
            processDSData(data);
            
        },
        error: function(xhr, status, error) {
            console.error("Error loading DS data:", error);
            showDSErrorState();
        }
    });
}


// ========== FUNGSI PROSES DS DATA - TAMPIL SEMUA ORDER ==========
function processDSData(rawData) {
    console.log("🔍 Processing DS Data - WITH ADD ORDER DISTRIBUTION");
    
    let groupedData = {};
    
    rawData.forEach(function(item, index) {
        let dateFormat = String(item.DATE || '');
        if (/^\d{8}$/.test(dateFormat)) {
            dateFormat = dateFormat.slice(0,4) + '-' + 
                        dateFormat.slice(4,6) + '-' + 
                        dateFormat.slice(6,8);
        }
        
        const key = dateFormat + '|' + (item.SUPPLIER_CODE || '') + '|' + (item.PART_NO || '');
        
        if (!groupedData[key]) {
            groupedData[key] = {
                date: dateFormat,
                supplier: item.SUPPLIER_CODE,
                partNo: item.PART_NO,
                partName: item.PART_DESC || item.PART_NAME,
                orderData: {},
                addOrderData: {},
                totalOrderData: {},
                incomingData: {},
                totalOrder: parseInt(item.TOTAL_ORDER || 0),
                totalIncoming: parseInt(item.TOTAL_INCOMING || 0),
                addDS: parseInt(item.ADD_DS || 0),
                totalRegularOrder: parseInt(item.TOTAL_REGULAR_ORDER || 0)
            };
            
            // Initialize data per jam (7-20)
            for(let hour = 7; hour <= 20; hour++) {
                let hourKey = hour < 10 ? '0' + hour : hour.toString();
                
                // Regular order dari ETA
                groupedData[key].orderData[hour] = parseInt(item['ORD_' + hourKey] || 0);
                
                // Add order distribution
                groupedData[key].addOrderData[hour] = parseInt(item['ADD_ORD_' + hourKey] || 0);
                
                // Total per jam (regular + add)
                groupedData[key].totalOrderData[hour] = parseInt(item['TOTAL_ORD_' + hourKey] || 0);
                
                // Incoming
                groupedData[key].incomingData[hour] = parseInt(item['TRAN_' + hourKey] || 0);
            }
        }
        
        // Debug untuk 2 data pertama
        if (index < 2) {
            console.log(`📊 DS Item ${index+1}:`, {
                key: key,
                partNo: item.PART_NO,
                addDS: item.ADD_DS,
                regular_14: item.ORD_14,
                add_14: item.ADD_ORD_14,
                total_14: item.TOTAL_ORD_14
            });
        }
    });
    
    let rows = [];
    let dataIndex = 0;
    
    Object.keys(groupedData).forEach(key => {
        const item = groupedData[key];
        
        const searchText = [
            item.date,
            item.supplier,
            item.partNo,
            item.partName
        ].join(' ').toLowerCase();
        
        // Add order row (pakai TOTAL per jam)
        rows.push({
            id: dataIndex * 2,
            type: 'order',
            item: item,
            searchText: searchText,
            totalOrder: item.totalOrder,
            totalIncoming: item.totalIncoming,
            addDS: item.addDS,
            totalRegularOrder: item.totalRegularOrder,
            orderDistribution: item.totalOrderData // Pakai TOTAL (regular + add)
        });
        
        // Add incoming row
        rows.push({
            id: dataIndex * 2 + 1,
            type: 'incoming',
            item: item,
            searchText: searchText,
            totalOrder: item.totalOrder,
            totalIncoming: item.totalIncoming,
            addDS: item.addDS,
            orderDistribution: item.totalOrderData
        });
        
        dataIndex++;
        
        // Log untuk debugging
        console.log(`✅ ${item.date} - ${item.supplier} - ${item.partNo}:`, {
            total_order: item.totalOrder,
            add_ds: item.addDS,
            distribution_14: item.totalOrderData[14],
            distribution_15: item.totalOrderData[15],
            has_add_15: item.addOrderData[15] > 0
        });
    });
    
    dsFilteredData = rows;
    console.log(`📊 DS Data: ${Object.keys(groupedData).length} items (WITH ADD ORDER DISTRIBUTION)`);
    
    renderDSTable();
}


// ================= FUNGSI RENDER DS TABLE DENGAN PAGINATION - PERBAIKAN =================
function renderDSTable() {
    const $tbody = $('#table-detail-ds tbody');
    const searchKeyword = $('#ds-search-input').val().toLowerCase().trim();
    
    let filteredRows = dsFilteredData;
    
    if (searchKeyword) {
        filteredRows = dsFilteredData.filter(row => 
            row.searchText.includes(searchKeyword)
        );
    }
    
    const uniqueDataCount = Math.ceil(filteredRows.length / 2);
    
    $('#ds-result-count').html(`
        <i class="bi bi-list-check"></i>
        Showing ${uniqueDataCount} data items (${filteredRows.length} rows)
    `);
    
    const totalRows = filteredRows.length;
    const totalPages = Math.ceil(totalRows / dsPageSize);
    
    if (dsCurrentPage > totalPages && totalPages > 0) {
        dsCurrentPage = totalPages;
    }
    
    const startIndex = (dsCurrentPage - 1) * dsPageSize;
    const endIndex = startIndex + dsPageSize;
    const pageData = filteredRows.slice(startIndex, endIndex);
    
    $tbody.empty();
    
    let displayNumber = startIndex + 1;
    
    for (let i = 0; i < pageData.length; i += 2) {
        const orderRow = pageData[i];
        const incomingRow = pageData[i + 1];
        
        if (orderRow && orderRow.type === 'order') {
            const item = orderRow.item;
            const orderDistribution = orderRow.orderDistribution || {};
            
            // Order row - PAKAI TOTAL DISTRIBUTION (regular + add)
            const $orderTr = $(`
                <tr class="table-order-row">
                    <td>${displayNumber}</td>
                    <td>${item.date}</td>
                    <td>${item.supplier || ''}</td>
                    <td>${item.partNo || ''}</td>
                    <td class="text-start">${item.partName || ''}</td>
                    <td>
                        <span class="badge badge-order">Order</span>
                        ${orderRow.addDS > 0 ? `<br><small class="text-primary">(+${orderRow.addDS} add)</small>` : ''}
                    </td>
                    <td>${orderDistribution[8] || 0}</td>
                    <td>${orderDistribution[9] || 0}</td>
                    <td>${orderDistribution[10] || 0}</td>
                    <td>${orderDistribution[11] || 0}</td>
                    <td>${orderDistribution[12] || 0}</td>
                    <td>${orderDistribution[13] || 0}</td>
                    <td>${orderDistribution[14] || 0}</td>
                    <td>${orderDistribution[15] || 0}</td>
                    <td>${orderDistribution[16] || 0}</td>
                    <td>${orderDistribution[17] || 0}</td>
                    <td>${orderDistribution[18] || 0}</td>
                    <td>${orderDistribution[19] || 0}</td>
                    <td>${orderDistribution[20] || 0}</td>
                    <td><strong>${orderRow.totalOrder}</strong></td>
                </tr>
            `);
            $tbody.append($orderTr);
        }
        
        if (incomingRow && incomingRow.type === 'incoming') {
            const item = incomingRow.item;
            
            // Incoming row
            const $incomingTr = $(`
                <tr class="table-incoming-row">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-start">${item.partName || ''}</td>
                    <td><span class="badge badge-incoming">Incoming</span></td>
                    <td>${item.incomingData[8] || 0}</td>
                    <td>${item.incomingData[9] || 0}</td>
                    <td>${item.incomingData[10] || 0}</td>
                    <td>${item.incomingData[11] || 0}</td>
                    <td>${item.incomingData[12] || 0}</td>
                    <td>${item.incomingData[13] || 0}</td>
                    <td>${item.incomingData[14] || 0}</td>
                    <td>${item.incomingData[15] || 0}</td>
                    <td>${item.incomingData[16] || 0}</td>
                    <td>${item.incomingData[17] || 0}</td>
                    <td>${item.incomingData[18] || 0}</td>
                    <td>${item.incomingData[19] || 0}</td>
                    <td>${item.incomingData[20] || 0}</td>
                    <td><strong>${incomingRow.totalIncoming}</strong></td>
                </tr>
            `);
            $tbody.append($incomingTr);
        }
        
        displayNumber++;
    }
    
    const uniqueDataInPage = Math.ceil(pageData.length / 2);
    const totalUniqueData = Math.ceil(filteredRows.length / 2);
    updateDSPagination(totalUniqueData, totalPages);
    initDSDragScroll();
}

// ================= FUNGSI UPDATE DS PAGINATION - PERBAIKAN =================
function updateDSPagination(totalUniqueData, totalPages) {
    const $pagination = $('#ds-pagination');
    const $pageInfo = $('#ds-page-info');
    
    $pageInfo.html(`
        Page ${dsCurrentPage} of ${totalPages} 
        <span class="text-muted">(${totalUniqueData} data items)</span>
    `);
    
    $pagination.empty();
    
    const $prev = $('<li>').addClass('page-item');
    if (dsCurrentPage <= 1) $prev.addClass('disabled');
    
    $prev.html(`
        <a class="page-link" href="#" id="ds-prev-btn">
            <i class="bi bi-chevron-left"></i> Previous
        </a>
    `);
    $pagination.append($prev);
    
    const maxPagesToShow = 5;
    let startPage = Math.max(1, dsCurrentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
    
    if (endPage - startPage + 1 < maxPagesToShow) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const $page = $('<li>').addClass('page-item');
        if (i === dsCurrentPage) $page.addClass('active');
        
        $page.html(`
            <a class="page-link" href="#" data-page="${i}">${i}</a>
        `);
        $pagination.append($page);
    }
    
    const $next = $('<li>').addClass('page-item');
    if (dsCurrentPage >= totalPages) $next.addClass('disabled');
    
    $next.html(`
        <a class="page-link" href="#" id="ds-next-btn">
            Next <i class="bi bi-chevron-right"></i>
        </a>
    `);
    $pagination.append($next);
}

// ================= FUNGSI INITIALIZE DS DRAG SCROLL =================
function initDSDragScroll() {
    const $container = $('#ds-table-container');
    
    $container.off('mousedown touchstart');
    $container.off('mousemove touchmove');
    $container.off('mouseup touchend mouseleave');
    
    $container.on('mousedown touchstart', function(e) {
        dsIsDragging = true;
        $container.css('cursor', 'grabbing');
        
        dsStartX = e.pageX || e.originalEvent.touches[0].pageX;
        dsScrollLeft = $container.scrollLeft();
        
        e.preventDefault();
    });
    
    $container.on('mousemove touchmove', function(e) {
        if (!dsIsDragging) return;
        
        const x = e.pageX || e.originalEvent.touches[0].pageX;
        const walk = (x - dsStartX) * 1.5;
        
        $container.scrollLeft(dsScrollLeft - walk);
        
        e.preventDefault();
    });
    
    $container.on('mouseup touchend mouseleave', function() {
        dsIsDragging = false;
        $container.css('cursor', 'grab');
    });
}

// ================= FUNGSI UNTUK NS DATA (MIRIP DENGAN DS) - PERBAIKAN =================
function loadNSData() {
    let date1 = $('#range-date1').val();
    let date2 = $('#range-date2').val();
    
    if (!date1 || !date2) {
        alert("Pilih tanggal terlebih dahulu");
        return;
    }
    
    let sendDate1 = date1.replace(/-/g, '');
    let sendDate2 = date2.replace(/-/g, '');
    
    nsCurrentPage = 1;
    nsPageSize = parseInt($('#ns-page-size').val()) || 10;
    
    // AMBIL FILTER SUPPLIER CODE
    let filteredSupplierCodes = getFilteredSupplierCodes();
    let supplierParam = '';
    if (filteredSupplierCodes && filteredSupplierCodes.length > 0) {
        supplierParam = filteredSupplierCodes.join(',');
    }
    
    console.log("🔄 Loading NS Data dengan filter:", {
        date1: sendDate1,
        date2: sendDate2,
        supplierCodes: filteredSupplierCodes,
        supplierParam: supplierParam
    });
    
    showNSLoadingSkeleton();
    
    $.ajax({
        url: 'modules/data_night_shift1.php',
        type: 'GET',
        data: {
            date1: sendDate1,
            date2: sendDate2,
            supplier_code: supplierParam
        },
        dataType: 'json',
        success: function(response) {
            console.log("🌙 Night Shift Data Response dengan ADD ORDER DISTRIBUTION:", {
                count: response.count,
                data_sample: response.data ? response.data[0] : 'No data'
            });
            
            const data = Array.isArray(response)
                ? response
                : (response?.data || []);
            
            if (!Array.isArray(data) || data.length === 0) {
                showNSEmptyState();
                return;
            }
            
            processNSData(data);
            
        },
        error: function(xhr, status, error) {
            console.error("Error loading NS data:", error);
            showNSErrorState();
        }
    });
}

// ========== FUNGSI PROSES NS DATA - TAMPIL SEMUA ORDER ==========
function processNSData(rawData) {
    console.log("🔍 Processing NS Data - WITH ADD ORDER DISTRIBUTION");
    
    let groupedData = {};
    const nsHours = [21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7];
    
    rawData.forEach(function(item, index) {
        let dateFormat = String(item.DATE || '');
        if (/^\d{8}$/.test(dateFormat)) {
            dateFormat = dateFormat.slice(0,4) + '-' + 
                        dateFormat.slice(4,6) + '-' + 
                        dateFormat.slice(6,8);
        }
        
        const key = dateFormat + '|' + (item.SUPPLIER_CODE || '') + '|' + (item.PART_NO || '');
        
        if (!groupedData[key]) {
            groupedData[key] = {
                date: dateFormat,
                supplier: item.SUPPLIER_CODE,
                partNo: item.PART_NO,
                partName: item.PART_DESC || item.PART_NAME,
                orderData: {},
                addOrderData: {},
                totalOrderData: {},
                incomingData: {},
                totalOrder: parseInt(item.TOTAL_ORDER || 0),
                totalIncoming: parseInt(item.TOTAL_INCOMING || 0),
                addNS: parseInt(item.ADD_NS || 0),
                totalRegularOrder: parseInt(item.TOTAL_REGULAR_ORDER || 0)
            };
            
            // Initialize data per jam NS
            nsHours.forEach(hour => {
                let hourKey = hour < 10 ? '0' + hour : hour.toString();
                
                // Regular order dari ETA
                groupedData[key].orderData[hour] = parseInt(item['ORD_' + hourKey] || 0);
                
                // Add order distribution
                groupedData[key].addOrderData[hour] = parseInt(item['ADD_ORD_' + hourKey] || 0);
                
                // Total per jam (regular + add)
                groupedData[key].totalOrderData[hour] = parseInt(item['TOTAL_ORD_' + hourKey] || 0);
                
                // Incoming
                groupedData[key].incomingData[hour] = parseInt(item['TRAN_' + hourKey] || 0);
            });
        }
        
        // Debug untuk 2 data pertama
        if (index < 2) {
            console.log(`📊 NS Item ${index+1}:`, {
                key: key,
                partNo: item.PART_NO,
                addNS: item.ADD_NS,
                regular_21: item.ORD_21,
                add_21: item.ADD_ORD_21,
                total_21: item.TOTAL_ORD_21
            });
        }
    });
    
    let rows = [];
    let dataIndex = 0;
    
    Object.keys(groupedData).forEach(key => {
        const item = groupedData[key];
        
        const searchText = [
            item.date,
            item.supplier,
            item.partNo,
            item.partName
        ].join(' ').toLowerCase();
        
        // Add order row (pakai TOTAL per jam)
        rows.push({
            id: dataIndex * 2,
            type: 'order',
            item: item,
            searchText: searchText,
            totalOrder: item.totalOrder,
            totalIncoming: item.totalIncoming,
            addNS: item.addNS,
            totalRegularOrder: item.totalRegularOrder,
            orderDistribution: item.totalOrderData // Pakai TOTAL (regular + add)
        });
        
        // Add incoming row
        rows.push({
            id: dataIndex * 2 + 1,
            type: 'incoming',
            item: item,
            searchText: searchText,
            totalOrder: item.totalOrder,
            totalIncoming: item.totalIncoming,
            addNS: item.addNS,
            orderDistribution: item.totalOrderData
        });
        
        dataIndex++;
        
        // Log untuk debugging
        console.log(`🌙 ${item.date} - ${item.supplier} - ${item.partNo}:`, {
            total_order: item.totalOrder,
            add_ns: item.addNS,
            distribution_21: item.totalOrderData[21],
            distribution_0: item.totalOrderData[0],
            has_add: item.addNS > 0
        });
    });
    
    nsFilteredData = rows;
    console.log(`📊 NS Data: ${Object.keys(groupedData).length} items (WITH ADD ORDER DISTRIBUTION)`);
    
    renderNSTable();
}

function renderNSTable() {
    const $tbody = $('#table-detail-ns tbody');
    const searchKeyword = $('#ns-search-input').val().toLowerCase().trim();
    const nsHours = [21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7];
    
    let filteredRows = nsFilteredData;
    
    if (searchKeyword) {
        filteredRows = nsFilteredData.filter(row => 
            row.searchText.includes(searchKeyword)
        );
    }
    
    const uniqueDataCount = Math.ceil(filteredRows.length / 2);
    
    $('#ns-result-count').html(`
        <i class="bi bi-list-check"></i>
        Showing ${uniqueDataCount} data items (${filteredRows.length} rows)
    `);
    
    const totalRows = filteredRows.length;
    const totalPages = Math.ceil(totalRows / nsPageSize);
    
    if (nsCurrentPage > totalPages && totalPages > 0) {
        nsCurrentPage = totalPages;
    }
    
    const startIndex = (nsCurrentPage - 1) * nsPageSize;
    const endIndex = startIndex + nsPageSize;
    const pageData = filteredRows.slice(startIndex, endIndex);
    
    $tbody.empty();
    
    let displayNumber = startIndex + 1;
    
    for (let i = 0; i < pageData.length; i += 2) {
        const orderRow = pageData[i];
        const incomingRow = pageData[i + 1];
        
        if (orderRow && orderRow.type === 'order') {
            const item = orderRow.item;
            const orderDistribution = orderRow.orderDistribution || {};
            
            // Order row - PAKAI TOTAL DISTRIBUTION (regular + add)
            const $orderTr = $(`
                <tr class="table-order-row">
                    <td>${displayNumber}</td>
                    <td>${item.date}</td>
                    <td>${item.supplier || ''}</td>
                    <td>${item.partNo || ''}</td>
                    <td class="text-start">${item.partName || ''}</td>
                    <td>
                        <span class="badge badge-order">Order</span>
                        ${orderRow.addNS > 0 ? `<br><small class="text-warning">(+${orderRow.addNS} add)</small>` : ''}
                    </td>
                    ${nsHours.map(hour => 
                        `<td>${orderDistribution[hour] || 0}</td>`
                    ).join('')}
                    <td><strong>${orderRow.totalOrder}</strong></td>
                </tr>
            `);
            $tbody.append($orderTr);
        }
        
        if (incomingRow && incomingRow.type === 'incoming') {
            const item = incomingRow.item;
            
            // Incoming row
            const $incomingTr = $(`
                <tr class="table-incoming-row">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-start">${item.partName || ''}</td>
                    <td><span class="badge badge-incoming">Incoming</span></td>
                    ${nsHours.map(hour => 
                        `<td>${item.incomingData[hour] || 0}</td>`
                    ).join('')}
                    <td><strong>${incomingRow.totalIncoming}</strong></td>
                </tr>
            `);
            $tbody.append($incomingTr);
        }
        
        displayNumber++;
    }
    
    const uniqueDataInPage = Math.ceil(pageData.length / 2);
    const totalUniqueData = Math.ceil(filteredRows.length / 2);
    updateNSPagination(totalUniqueData, totalPages);
    initNSDragScroll();
}

function updateNSPagination(totalUniqueData, totalPages) {
    const $pagination = $('#ns-pagination');
    const $pageInfo = $('#ns-page-info');
    
    $pageInfo.html(`
        Page ${nsCurrentPage} of ${totalPages} 
        <span class="text-muted">(${totalUniqueData} data items)</span>
    `);
    
    $pagination.empty();
    
    const $prev = $('<li>').addClass('page-item');
    if (nsCurrentPage <= 1) $prev.addClass('disabled');
    
    $prev.html(`
        <a class="page-link" href="#" id="ns-prev-btn">
            <i class="bi bi-chevron-left"></i> Previous
        </a>
    `);
    $pagination.append($prev);
    
    const maxPagesToShow = 5;
    let startPage = Math.max(1, nsCurrentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
    
    if (endPage - startPage + 1 < maxPagesToShow) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const $page = $('<li>').addClass('page-item');
        if (i === nsCurrentPage) $page.addClass('active');
        
        $page.html(`
            <a class="page-link" href="#" data-page="${i}">${i}</a>
        `);
        $pagination.append($page);
    }
    
    const $next = $('<li>').addClass('page-item');
    if (nsCurrentPage >= totalPages) $next.addClass('disabled');
    
    $next.html(`
        <a class="page-link" href="#" id="ns-next-btn">
            Next <i class="bi bi-chevron-right"></i>
        </a>
    `);
    $pagination.append($next);
}

function initNSDragScroll() {
    const $container = $('#ns-table-container');
    
    $container.off('mousedown touchstart');
    $container.off('mousemove touchmove');
    $container.off('mouseup touchend mouseleave');
    
    $container.on('mousedown touchstart', function(e) {
        nsIsDragging = true;
        $container.css('cursor', 'grabbing');
        
        nsStartX = e.pageX || e.originalEvent.touches[0].pageX;
        nsScrollLeft = $container.scrollLeft();
        
        e.preventDefault();
    });
    
    $container.on('mousemove touchmove', function(e) {
        if (!nsIsDragging) return;
        
        const x = e.pageX || e.originalEvent.touches[0].pageX;
        const walk = (x - nsStartX) * 1.5;
        
        $container.scrollLeft(nsScrollLeft - walk);
        
        e.preventDefault();
    });
    
    $container.on('mouseup touchend mouseleave', function() {
        nsIsDragging = false;
        $container.css('cursor', 'grab');
    });
}

// ================= MODAL FUNCTIONS =================
function showAddInformationModal() {
    $("#modal-add-information").modal('show');
    $('#txt-time1').val(getCurrentDateTime());
    $('#txt-date-information').html("(" + new Date().toISOString().split('T')[0] + ")");
}

function showCycleModal() {
    $("#modal-by-cycle").modal('show');
    $("#txt-rangedate-cycle").html("(" + rangeDate1 + ") s/d (" + rangeDate2 + ")");
    loadCycleData();
}

function showDSModal() {
    $("#modal-detail-ds").modal('show');
    
    // Tampilkan info filter
    const filteredCount = getFilteredSupplierCodes().length;
    const filterInfo = filteredCount > 0 ? 
        ` (Filtered: ${filteredCount} suppliers)` : 
        '';
    
    $("#txt-rangedate-day").html("(" + rangeDate1 + ") s/d (" + rangeDate2 + ")" + filterInfo);
    loadDSData();
}

function showNSModal() {
    $("#modal-detail-ns").modal('show');
    
    // Tampilkan info filter
    const filteredCount = getFilteredSupplierCodes().length;
    const filterInfo = filteredCount > 0 ? 
        ` (Filtered: ${filteredCount} suppliers)` : 
        '';
    
    $("#txt-rangedate-night").html("(" + rangeDate1 + ") s/d (" + rangeDate2 + ")" + filterInfo);
    loadNSData();
}

function showAccumModal() {
    loadMonthOptions();
    const currentMonth = new Date().getFullYear() + '-' + pad(new Date().getMonth() + 1);
    $('#select-month').val(currentMonth);
    
    var modal = new bootstrap.Modal(document.getElementById('modalByAccum'));
    modal.show();
    
    // Tampilkan info filter
    const filteredCount = getFilteredSupplierCodes().length;
    const filterInfo = filteredCount > 0 ? 
        ` - Filtered: ${filteredCount} suppliers` : 
        '';
    
    setTimeout(() => {
        loadAccumTable(currentMonth);
    }, 500);
}

// Tambahkan fungsi ini di app.js atau update yang existing

function fetchDataInformation() {
    var date1 = $('#range-date1').val() || new Date().toISOString().split('T')[0];
    var date2 = $('#range-date2').val() || date1;
    var currentUser = '<?php echo $_SESSION["name"] ?? ""; ?>';
    
    console.log("📡 Fetching information data for user:", currentUser);
    
    $.ajax({
        type: 'GET',
        url: 'modules/data_information.php',
        data: { 
            type: "fetch",
            date1: date1,
            date2: date2
        },
        dataType: 'json',
        success: function(response) {
            console.log("📨 Data information response:", response);
            
            if (response.success) {
                // DataTable akan otomatis merender karena sudah ada konfigurasi columns
                tableInformation.clear();
                
                if (response.data && response.data.length > 0) {
                    tableInformation.rows.add(response.data).draw();
                    
                    // Debug: log data untuk memastikan field ada
                    console.log("🔍 Sample data:", response.data[0]);
                    console.log("🔍 Request field:", response.data[0].REQUEST);
                    console.log("🔍 Remark field:", response.data[0].REMARK);
                    
                    // Update badge
                    const openCount = response.data.filter(item => 
                        item.STATUS === 'Open' && item.user_role === 'recipient'
                    ).length;
                    
                    updateInfoBadge(openCount);
                    
                } else {
                    // Tampilkan pesan jika tidak ada data
                    tableInformation.clear().draw();
                }
                
            } else {
                console.error("❌ Failed to fetch data:", response.message);
                tableInformation.clear().draw();
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Error fetching data information:", xhr.responseText);
            tableInformation.clear().draw();
        }
    });
}

function updateInfoBadge() {
    var openCount = 0;
    
    tableInformation.rows().every(function(rowIdx, tableLoop, rowLoop) {
        var data = this.data();
        
        if (data[9] && typeof data[9] === 'string') {
            var statusHtml = data[9];
            if (statusHtml.includes('bg-danger') || statusHtml.includes('OPEN')) {
                openCount++;
            }
        }
    });
    
    var $badge = $('#info-badge');
    
    if (openCount > 0) {
        $badge.text(openCount).show().addClass('bg-danger');
        document.title = `(${openCount}) Progress BO Control`;
    } else {
        $badge.hide();
        document.title = "Progress BO Control";
    }
}

// ================= CYCLE DATA FUNCTIONS =================
function loadCycleData() {
    if (!rangeDate1 || !rangeDate2) {
        alert("Pilih tanggal terlebih dahulu");
        return;
    }
    
    tableByCycle.clear().draw();
    $('#modal-by-cycle .modal-body').append('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading data...</div>');
    
    showLoader(true);
    
    $.ajax({
        url: 'modules/data_by_cycle.php',
        type: 'GET',
        data: { 
            date1: rangeDate1.replace(/-/g, ''),
            date2: rangeDate2.replace(/-/g, ''),
        },
        dataType: 'json',
        success: function(response) {
            $('#modal-by-cycle .modal-body .text-center').remove();
            hideLoader();
            
            console.log("Cycle Data Response:", response);
            
            if (response && response.success === false) {
                swal("Error", response.message || "Gagal memuat data", "error");
                return;
            }
            
            const data = response || [];
            
            if (!Array.isArray(data) || data.length === 0) {
                tableByCycle.clear().draw();
                swal("Info", "Tidak ada data untuk periode ini", "info");
                return;
            }
            
            var no_urut = 1;
            var rows = [];
            
            $.each(data, function(index, item) {
                var c1 = parseInt(item.C1) || 0;
                var c2 = parseInt(item.C2) || 0;
                var c3 = parseInt(item.C3) || 0;
                var c4 = parseInt(item.C4) || 0;
                var c5 = parseInt(item.C5) || 0;
                var c6 = parseInt(item.C6) || 0;
                var c7 = parseInt(item.C7) || 0;
                var c8 = parseInt(item.C8) || 0;
                var c9 = parseInt(item.C9) || 0;
                var c10 = parseInt(item.C10) || 0;
                var c11 = parseInt(item.C11) || 0;
                var c12 = parseInt(item.C12) || 0;
                
                var total_ord = c1 + c2 + c3 + c4 + c5 + c6 + c7 + c8 + c9 + c10 + c11 + c12;

                var dateFormat = String(item.DELV_DATE || '');
                if (/^\d{8}$/.test(dateFormat)) {
                    dateFormat = dateFormat.slice(0,4) + '-' + 
                                dateFormat.slice(4,6) + '-' + 
                                dateFormat.slice(6,8);
                }

                var row = [
                    no_urut,
                    dateFormat,
                    item.SUPPLIER_CODE || '',
                    item.PART_NO || '',
                    item.PART_NAME || '',
                    c1,
                    c2,
                    c3,
                    c4,
                    c5,
                    c6,
                    c7,
                    c8,
                    c9,
                    c10,
                    c11,
                    c12,
                    total_ord,
                ];
                rows.push(row);
                no_urut++;
            });

            tableByCycle.clear().rows.add(rows).draw();
            
            console.log("Loaded " + rows.length + " rows of cycle data");
        },
        error: function(xhr, status, error) {
            $('#modal-by-cycle .modal-body .text-center').remove();
            hideLoader();
            console.error("Error loading cycle data:", xhr.responseText);
            swal("Error", "Gagal memuat data cycle: " + error, "error");
        }
    });
}

// ================= ACCUM TABLE FUNCTIONS =================
function loadMonthOptions(callback) {  
    let sel = $('#select-month');  
    let now = new Date();  
    sel.empty();  
    for (let i = 0; i < 12; i++) {  
        let d = new Date(now.getFullYear(), now.getMonth()-i, 1);  
        let val = d.getFullYear() + '-' + pad(d.getMonth()+1);
        let txt = d.toLocaleString('default', { month: 'long', year: 'numeric' });  
        sel.append(`<option value="${val}">${txt}</option>`);  
    }  
    if (callback) callback();  
}

function loadAccumTable(monthYear) {
    console.log("🔍 loadAccumTable called dengan filter supplier");
    
    if (!monthYear) {
        $('#accum-table-container').html('<div class="alert alert-warning">Pilih bulan terlebih dahulu</div>');
        return;
    }
    
    // AMBIL FILTER SUPPLIER CODE
    let filteredSupplierCodes = getFilteredSupplierCodes();
    let supplierParam = '';
    if (filteredSupplierCodes && filteredSupplierCodes.length > 0) {
        supplierParam = filteredSupplierCodes.join(',');
    }
    
    console.log("🔍 loadAccumTable called with:", monthYear);
    
    let [year, month] = monthYear.split('-');
    if (!year || !month) return;
    
    console.log("📅 Processing:", year, month);
    console.log("🎯 Supplier filter:", {
        codes: filteredSupplierCodes,
        param: supplierParam,
        count: filteredSupplierCodes ? filteredSupplierCodes.length : 0
    });
    
    year = String(year);
    month = pad(parseInt(month));
    let daysInMonth = new Date(year, month, 0).getDate();
    let selectedTxt = new Date(year, month-1, 1).toLocaleString('default', { month: 'long', year: 'numeric' });
    
    $('#txt-selected-month').text(selectedTxt);
    $('#accum-table-container').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading data...</div>');
    
    showLoader(true);
    
    $.ajax({
        url: 'modules/data_by_accum.php',
        method: 'GET',
        data: { 
            month: monthYear,
            supplier_code: supplierParam // TAMBAH INI
        },
        dataType: 'json',
        success: function(response) {
            console.log("📦 Accum API Response:", response);
            hideLoader();
            
            if (!response) {
                $('#accum-table-container').html('<div class="alert alert-danger">Invalid response from server</div>');
                return;
            }
            
            if (response.success === false) {
                $('#accum-table-container').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        ${response.message || 'Error loading data'}
                    </div>
                `);
                console.error("Server error:", response);
                return;
            }
            
            const rawData = response.data || response || [];
            
            console.log("📊 Raw data length:", rawData.length);
            
            if (!Array.isArray(rawData) || rawData.length === 0) {
                $('#accum-table-container').html(`
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Tidak ada data untuk bulan ${selectedTxt}
                    </div>
                `);
                accumDataAll = [];
                return;
            }
            
            let grouped = {};
            rawData.forEach(function(row){
                let delvDateStr = row.DELV_DATE ? row.DELV_DATE.toString() : '';
                
                if (delvDateStr.length >= 8) {
                    let delvYear = delvDateStr.substr(0,4);
                    let delvMonth = delvDateStr.substr(4,2);
                    let delvDay = parseInt(delvDateStr.substr(6,2));
                    
                    let key = (row.SUPPLIER_CODE || '') + '|' + 
                            (row.SUPPLIER_NAME || '') + '|' + 
                            (row.PART_NO || '') + '|' + 
                            (row.PART_NAME || '');
                    
                    if(delvYear == year && delvMonth == month) {  
                        if(!grouped[key]) {  
                            grouped[key] = {  
                                SUPPLIER_CODE: safeTrim(row.SUPPLIER_CODE),  
                                SUPPLIER_NAME: safeTrim(row.SUPPLIER_NAME),  
                                PART_NO: safeTrim(row.PART_NO),  
                                PART_NAME: safeTrim(row.PART_NAME),  
                                DATA_ARRAY: []
                            };  
                        }  
                        
                        if(delvDay >= 1 && delvDay <= daysInMonth) {  
                            grouped[key].DATA_ARRAY.push({
                                DAY: delvDay,
                                REGULAR_ORDER: parseInt(row.REGULAR_ORDER) || 0,
                                ADD_DS: parseInt(row.ADD_DS) || 0,
                                ADD_NS: parseInt(row.ADD_NS) || 0,
                                TOTAL_ORDER: parseInt(row.TOTAL_ORDER) || 0
                            });
                        }  
                    }
                }
            });
            
            console.log("📈 Grouped data keys:", Object.keys(grouped));
            
            accumDataAll = Object.values(grouped);
            accumTableParams.daysInMonth = daysInMonth;
            accumTableParams.selectedTxt = selectedTxt;
            accumTableParams.page = 1;
            accumTableParams.year = year;
            accumTableParams.month = month;
            
            console.log("✅ Final accumDataAll:", accumDataAll.length, "items");
            
            if (accumDataAll.length === 0) {
                $('#accum-table-container').html(`
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Tidak ada data untuk bulan ${selectedTxt}
                    </div>
                `);
            } else {
                renderAccumTable();
            }
        },
        error: function(xhr, status, error) {
            hideLoader();
            console.error("❌ Accum load error:", xhr.responseText);
            
            let errorMsg = "Gagal mengambil data! ";
            try {
                const err = JSON.parse(xhr.responseText);
                errorMsg += err.message || err.error || error;
            } catch (e) {
                errorMsg += xhr.statusText || error;
            }
            
            $('#accum-table-container').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    ${errorMsg}
                </div>
            `);
        }
    });
}

// ================= ACCUM TABLE FUNCTIONS - PERBAIKAN HEADER =================
function renderAccumTable() {  
    let {page, pageSize, search, sort, daysInMonth, selectedTxt, year, month} = accumTableParams;   
    let data = accumDataAll.slice();  
    
    if(search && search.trim()) {  
        let val = search.trim().toLowerCase();  
        data = data.filter(d =>  
            d.SUPPLIER_CODE.toLowerCase().includes(val) ||  
            d.SUPPLIER_NAME.toLowerCase().includes(val) ||  
            d.PART_NO.toLowerCase().includes(val) ||  
            d.PART_NAME.toLowerCase().includes(val)  
        );  
    }  
    
    data = data.sort((a,b) => {  
        let x = (a[sort]||'').toLowerCase(), y = (b[sort]||'').toLowerCase();  
        return x.localeCompare(y, 'en', {numeric:true});  
    });  
    
    let totalRows = data.length;  
    let totalPages = Math.ceil(totalRows/pageSize) || 1;  
    if(page > totalPages) page = totalPages;  
    let startIdx = (page-1)*pageSize;  
    let endIdx = startIdx+pageSize;  
    let paged = data.slice(startIdx, endIdx);  
    
    let html = '';  
    html += '<div class="table-fixed-container" id="accum-fixed-container">';
    html += '<table class="table table-bordered table-hover" style="margin-bottom: 0 !important;">';  
    
    html += '<thead style="position: sticky; top: 0; z-index: 100; background: white;">';  
    html += '<tr>';  
    html += '<th rowspan="2" style="vertical-align: middle; min-width: 100px;">Supplier Code</th>';  
    html += '<th rowspan="2" style="vertical-align: middle; min-width: 150px;">Supplier Name</th>';  
    html += '<th rowspan="2" style="vertical-align: middle; min-width: 100px;">Part No</th>';  
    html += '<th rowspan="2" style="vertical-align: middle; min-width: 150px;">Part Name</th>';  
    html += '<th rowspan="2" style="vertical-align: middle; min-width: 80px;">Item</th>';  
    html += `<th colspan="${daysInMonth}" class="text-center" style="border-left: 2px solid #dee2e6;">Bulan ${selectedTxt}</th>`;  
    html += '</tr>';  
    
    html += '<tr>';  
    for(let i=1; i<=daysInMonth; i++) {
        html += `<th class="text-center" style="min-width: 60px; ${i===1?'border-left: 2px solid #dee2e6;':''}">${i}</th>`;  
    }
    html += '</tr>';  
    html += '</thead>';  
    
    html += '<tbody>';  
    
    let itemNumber = startIdx + 1;
    
    paged.forEach(function(item, index){  
        let ordQtyPerDay = Array(daysInMonth).fill(0);
        let addDsPerDay = Array(daysInMonth).fill(0);
        let addNsPerDay = Array(daysInMonth).fill(0);
        let totalOrderPerDay = Array(daysInMonth).fill(0);
        let totalIncomingPerDay = Array(daysInMonth).fill(0);
        
        if (item.DATA_ARRAY && Array.isArray(item.DATA_ARRAY)) {
            item.DATA_ARRAY.forEach(dayData => {
                let dayIndex = parseInt(dayData.DAY) - 1;
                if (dayIndex >= 0 && dayIndex < daysInMonth) {
                    ordQtyPerDay[dayIndex] += parseInt(dayData.REGULAR_ORDER) || 0;
                    addDsPerDay[dayIndex] += parseInt(dayData.ADD_DS) || 0;
                    addNsPerDay[dayIndex] += parseInt(dayData.ADD_NS) || 0;
                    totalOrderPerDay[dayIndex] += parseInt(dayData.TOTAL_ORDER) || 0;
                }
            });
        }
        
        for(let i=0; i<daysInMonth; i++) {  
            totalIncomingPerDay[i] = 0;  
        }
        
        if (tableDetailProgress) {
            const progressData = tableDetailProgress.rows().data().toArray();
            
            progressData.forEach(function(row) {
                const rowDate = String(row.DATE || '');
                if (rowDate.length === 8) {
                    const rowYear = rowDate.substr(0,4);
                    const rowMonth = rowDate.substr(4,2);
                    const rowDay = parseInt(rowDate.substr(6,2));
                    
                    if (rowYear === year && 
                        rowMonth === month && 
                        rowDay >= 1 && rowDay <= daysInMonth &&
                        row.SUPPLIER_CODE === item.SUPPLIER_CODE &&
                        row.PART_NO === item.PART_NO) {
                        
                        const dayIndex = rowDay - 1;
                        const dsActual = parseInt(row.DS_ACTUAL) || 0;
                        const nsActual = parseInt(row.NS_ACTUAL) || 0;
                        
                        totalIncomingPerDay[dayIndex] = dsActual + nsActual;
                    }
                }
            });
        }
        
        let resultPerDay = totalIncomingPerDay.map((incoming, idx) => 
            incoming - totalOrderPerDay[idx]
        );
        
        let balancePerDay = [];  
        for(let i=0; i<daysInMonth; i++) {  
            if(i==0) balancePerDay[i] = resultPerDay[0];  
            else balancePerDay[i] = balancePerDay[i-1] + resultPerDay[i];  
        }  
        
        // Regular Order - PERUBAHAN DI SINI!
        html += '<tr class="data-group-start">';  
        html += `<td rowspan="7" style="vertical-align: middle;">${item.SUPPLIER_CODE||''}</td>`;  
        html += `<td rowspan="7" style="vertical-align: middle;">${item.SUPPLIER_NAME||''}</td>`;  
        html += `<td rowspan="7" style="vertical-align: middle;">${item.PART_NO||''}</td>`;  
        html += `<td rowspan="7" style="vertical-align: middle;">${item.PART_NAME||''}</td>`;  
        html += '<td><strong>Order</strong></td>';  // <== GANTI JADI ORDER!
        
        for(let i=0;i<daysInMonth;i++) {
            html += `<td class="text-center">${ordQtyPerDay[i] > 0 ? ordQtyPerDay[i] : 0}</td>`;
        }  
        html += '</tr>';  

        // Add D/S
        html += '<tr>';  
        html += '<td>Add D/S</td>';  
        for(let i=0;i<daysInMonth;i++) {
            html += `<td class="text-center text-primary fw-bold">${addDsPerDay[i]}</td>`;
        }  
        html += '</tr>';  

        // Add N/S
        html += '<tr>';  
        html += '<td>Add N/S</td>';  
        for(let i=0;i<daysInMonth;i++) {
            html += `<td class="text-center text-warning fw-bold">${addNsPerDay[i]}</td>`;
        }  
        html += '</tr>';    

        // Total Order
        html += '<tr>';  
        html += '<td><strong>Total Order</strong></td>';  
        for(let i=0;i<daysInMonth;i++) {
            html += `<td class="text-center"><strong>${totalOrderPerDay[i]}</strong></td>`;
        }  
        html += '</tr>';
        
        // Total Incoming
        html += '<tr>';  
        html += '<td>Total Incoming</td>';  
        for(let i=0;i<daysInMonth;i++) {
            html += `<td class="text-center">${totalIncomingPerDay[i]}</td>`;
        }  
        html += '</tr>';  
        
        // Result (Incoming - Order)
        html += '<tr>';  
        html += '<td>Result</td>';  
        for(let i=0;i<daysInMonth;i++) {
            let result = resultPerDay[i];
            if (result > 0) {
                html += `<td class="text-center text-success fw-bold">${result}</td>`;
            } else if (result < 0) {
                html += `<td class="text-center text-danger fw-bold">${result}</td>`;
            } else {
                html += `<td class="text-center">${result}</td>`;
            }
        }  
        html += '</tr>';  
        
        // Total Akumulasi
        html += '<tr style="border-bottom: 2px solid #dee2e6 !important;">';  
        html += '<td>Total Akumulasi</td>';  
        for(let i=0;i<daysInMonth;i++) {
            let balance = balancePerDay[i];
            if (balance > 0) {
                html += `<td class="text-center text-success fw-bold">${balance}</td>`;
            } else if (balance < 0) {
                html += `<td class="text-center text-danger fw-bold">${balance}</td>`;
            } else {
                html += `<td class="text-center">${balance}</td>`;
            }
        }  
        html += '</tr>';  
        
        itemNumber++;
    });  
    
    html += '</tbody></table>';  
    html += '</div>'; 
    
    $('#accum-table-container').html(html);  
    
    initAccumDragScroll();
    
    let pagHTML = '';  
    if (totalPages > 1) {  
        pagHTML += `<button class="btn btn-sm btn-outline-secondary" id="accum-prev" ${page<=1?'disabled':''}>&lt;</button> `;  
        pagHTML += ` Page ${page} of ${totalPages} `;  
        pagHTML += `<button class="btn btn-sm btn-outline-secondary" id="accum-next" ${page>=totalPages?'disabled':''}>&gt;</button>`;  
    } else {  
        pagHTML = `Total: ${totalRows} item(s)`;  
    }  
    $('#accum-pagination').html(pagHTML);  
    
    accumTableParams.page = page;  
}

function initAccumDragScroll() {
    const $container = $('#accum-fixed-container');
    
    if ($container.length === 0) return;
    
    $container.off('mousedown touchstart');
    $container.off('mousemove touchmove');
    $container.off('mouseup touchend mouseleave');
    
    $container.on('mousedown touchstart', function(e) {
        $container.css('cursor', 'grabbing');
        
        const startX = e.pageX || e.originalEvent.touches[0].pageX;
        const scrollLeft = $container.scrollLeft();
        
        const onMouseMove = function(e) {
            const x = e.pageX || e.originalEvent.touches[0].pageX;
            const walk = (x - startX) * 1.5;
            $container.scrollLeft(scrollLeft - walk);
            e.preventDefault();
        };
        
        const onMouseUp = function() {
            $container.css('cursor', 'grab');
            $(document).off('mousemove', onMouseMove);
            $(document).off('touchmove', onMouseMove);
            $(document).off('mouseup', onMouseUp);
            $(document).off('touchend', onMouseUp);
        };
        
        $(document).on('mousemove', onMouseMove);
        $(document).on('touchmove', onMouseMove);
        $(document).on('mouseup', onMouseUp);
        $(document).on('touchend', onMouseUp);
        
        e.preventDefault();
    });
    
    $container.css('cursor', 'grab');
}

function handleAddDS(e) {
    e.preventDefault();
    
    const $form = $(this);
    const $submitBtn = $('#ds-submit-btn');
    const $spinner = $('#ds-spinner');
    
    console.log('=== DS ADD ORDER START ===');
    
    // Validasi jam
    if (Object.keys(dsSelectedHours).length === 0) {
        showDSAlert('error', 'Pilih minimal 1 jam untuk add order');
        return false;
    }
    
    // Filter hanya jam yang quantity > 0
    let totalQty = 0;
    let validHours = {};
    
    Object.keys(dsSelectedHours).forEach(hour => {
        const qty = parseInt(dsSelectedHours[hour]) || 0;
        if (qty > 0) {
            validHours[hour] = qty;
            totalQty += qty;
        }
    });
    
    if (totalQty <= 0) {
        showDSAlert('error', 'Total quantity harus lebih dari 0');
        return false;
    }
    
    console.log('Valid hours:', validHours);
    console.log('Total qty:', totalQty);
    
    $submitBtn.prop('disabled', true);
    $spinner.removeClass('d-none');
    $submitBtn.find('span:not(.spinner-border)').text('Saving...');
    
    // Buat FormData
    const formData = new FormData();
    formData.append('date', $('#add-ds-date').val());
    formData.append('supplier_code', $('#add-ds-supplier').val());
    formData.append('part_no', $('#add-ds-partno').val());
    formData.append('type', 'ds');
    formData.append('action', $('#ds-action').val());
    formData.append('remark', $('#txt-ds-remark').val().trim());
    formData.append('hours_data', JSON.stringify(validHours));
    
    console.log('FormData:', {
        date: $('#add-ds-date').val(),
        supplier: $('#add-ds-supplier').val(),
        part_no: $('#add-ds-partno').val(),
        hours_data: JSON.stringify(validHours),
        total_qty: totalQty,
        remark: $('#txt-ds-remark').val().trim()
    });
    
    $.ajax({
        url: 'api/update_add_order.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
            console.log('DS Add Response:', response);
            
            $submitBtn.prop('disabled', false);
            $spinner.addClass('d-none');
            $submitBtn.find('span:not(.spinner-border)').text('Save Changes');
            
            if (response.success) {
                showDSAlert('success', response.message || 'Data berhasil disimpan!');
                
                // Auto close modal setelah 2 detik
                setTimeout(() => {
                    $('#modal-add-ds').modal('hide');
                    
                    // Refresh data setelah modal tutup
                    setTimeout(() => {
                        // Refresh progress table
                        loadTableDetailProgress();
                        
                        // Show success toast
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'DS Add Order berhasil disimpan: ' + (response.total_qty || totalQty) + ' pcs',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }, 500);
                    
                }, 2000);
                
            } else {
                showDSAlert('error', response.message || 'Gagal menyimpan data');
                console.error('Error response:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                error: error,
                response: xhr.responseText
            });
            
            $submitBtn.prop('disabled', false);
            $spinner.addClass('d-none');
            $submitBtn.find('span:not(.spinner-border)').text('Save Changes');
            
            let errorMsg = 'Server error: ';
            try {
                const err = JSON.parse(xhr.responseText);
                errorMsg += err.message || err.error || error;
            } catch (e) {
                errorMsg += xhr.statusText || error;
            }
            
            showDSAlert('error', errorMsg);
        }
    });
    
    return false;
}

// Helper untuk show alert di modal DS
function showDSAlert(type, message) {
    if (type === 'success') {
        $('#ds-error-alert').addClass('d-none');
        $('#ds-success-alert').removeClass('d-none');
        $('#ds-success-message').text(message);
    } else {
        $('#ds-success-alert').addClass('d-none');
        $('#ds-error-alert').removeClass('d-none');
        $('#ds-error-message').html('<i class="bi bi-exclamation-triangle me-2"></i>' + message);
    }
}

// Update handleAddNS untuk include hours data
function handleAddNS(e) {
    e.preventDefault();
    
    const $form = $(this);
    const $submitBtn = $('#ns-submit-btn');
    const $spinner = $('#ns-spinner');
    
    console.log('=== NS ADD ORDER START ===');
    
    // Validasi jam
    if (Object.keys(nsSelectedHours).length === 0) {
        showNSAlert('error', 'Pilih minimal 1 jam untuk add order');
        return false;
    }
    
    // Filter hanya jam yang quantity > 0
    let totalQty = 0;
    let validHours = {};
    
    Object.keys(nsSelectedHours).forEach(hour => {
        const qty = parseInt(nsSelectedHours[hour]) || 0;
        if (qty > 0) {
            validHours[hour] = qty;
            totalQty += qty;
        }
    });
    
    if (totalQty <= 0) {
        showNSAlert('error', 'Total quantity harus lebih dari 0');
        return false;
    }
    
    console.log('Valid hours:', validHours);
    console.log('Total qty:', totalQty);
    
    $submitBtn.prop('disabled', true);
    $spinner.removeClass('d-none');
    $submitBtn.find('span:not(.spinner-border)').text('Saving...');
    
    // Buat FormData
    const formData = new FormData();
    formData.append('date', $('#add-ns-date').val());
    formData.append('supplier_code', $('#add-ns-supplier').val());
    formData.append('part_no', $('#add-ns-partno').val());
    formData.append('type', 'ns');
    formData.append('action', $('#ns-action').val());
    formData.append('remark', $('#txt-ns-remark').val().trim());
    formData.append('hours_data', JSON.stringify(validHours));
    
    console.log('FormData:', {
        date: $('#add-ns-date').val(),
        supplier: $('#add-ns-supplier').val(),
        part_no: $('#add-ns-partno').val(),
        hours_data: JSON.stringify(validHours),
        total_qty: totalQty,
        remark: $('#txt-ns-remark').val().trim()
    });
    
    $.ajax({
        url: 'api/update_add_order.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
            console.log('NS Add Response:', response);
            
            $submitBtn.prop('disabled', false);
            $spinner.addClass('d-none');
            $submitBtn.find('span:not(.spinner-border)').text('Save Changes');
            
            if (response.success) {
                showNSAlert('success', response.message || 'Data berhasil disimpan!');
                
                // Auto close modal setelah 2 detik
                setTimeout(() => {
                    $('#modal-add-ns').modal('hide');
                    
                    // Refresh data setelah modal tutup
                    setTimeout(() => {
                        // Refresh progress table
                        loadTableDetailProgress();
                        
                        // Show success toast
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'NS Add Order berhasil disimpan: ' + (response.total_qty || totalQty) + ' pcs',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }, 500);
                    
                }, 2000);
                
            } else {
                showNSAlert('error', response.message || 'Gagal menyimpan data');
                console.error('Error response:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                error: error,
                response: xhr.responseText
            });
            
            $submitBtn.prop('disabled', false);
            $spinner.addClass('d-none');
            $submitBtn.find('span:not(.spinner-border)').text('Save Changes');
            
            let errorMsg = 'Server error: ';
            try {
                const err = JSON.parse(xhr.responseText);
                errorMsg += err.message || err.error || error;
            } catch (e) {
                errorMsg += xhr.statusText || error;
            }
            
            showNSAlert('error', errorMsg);
        }
    });
    
    return false;
}

// Helper untuk show alert di modal NS
function showNSAlert(type, message) {
    if (type === 'success') {
        $('#ns-error-alert').addClass('d-none');
        $('#ns-success-alert').removeClass('d-none');
        $('#ns-success-message').text(message);
    } else {
        $('#ns-success-alert').addClass('d-none');
        $('#ns-error-alert').removeClass('d-none');
        $('#ns-error-message').html('<i class="bi bi-exclamation-triangle me-2"></i>' + message);
    }
}

// ================= INFORMATION FORM SUBMISSION =================
function handleAddInformation(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    
    console.log("📝 Starting add information (single execution)...");
    
    const $form = $(this);
    const $submitBtn = $form.find('button[type="submit"]');
    const originalText = $submitBtn.html();
    
    $form.find('input, textarea, button').prop('disabled', true);
    $submitBtn.html('<span class="spinner-border spinner-border-sm"></span> Sending...');
    
    const today = new Date();
    const dateStr = today.getFullYear() + 
                    String(today.getMonth() + 1).padStart(2, '0') + 
                    String(today.getDate()).padStart(2, '0');
    
    const picFrom = $('input[name="txt-picfrom"]').val() || '<?php echo $_SESSION["name"] ?? "Unknown"; ?>';
    
    const formData = new FormData();
    formData.append('type', 'input');
    formData.append('date', dateStr);
    formData.append('txt-time1', $('#txt-time1').val());
    formData.append('txt-picfrom', picFrom);
    formData.append('txt-item', $('#txtItem').val());
    formData.append('txt-request', $('#txtRequest').val());
    
    $.ajax({
        url: 'modules/data_information.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            console.log("✅ Response received:", response);
            
            $form.find('input, textarea, button').prop('disabled', false);
            $submitBtn.html(originalText);
            
            if (response && response.success) {
                toastSuccess('Information added successfully!', 2000);
                
                $form[0].reset();
                $('#txt-time1').val(getCurrentDateTime());
                
                setTimeout(() => {
                    $('#modal-add-information').modal('hide');
                }, 1000);
                
                setTimeout(() => {
                    fetchDataInformation();
                }, 1500);
                
            } else {
                const errorMsg = response ? response.message : 'Invalid server response';
                toastError(errorMsg || 'Failed to add information');
                console.error("❌ Server error:", response);
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ AJAX Error:", xhr.responseText);
            
            $form.find('input, textarea, button').prop('disabled', false);
            $submitBtn.html(originalText);
            
            toastError('Network error. Please try again.');
        }
    });
    
    return false;
}

// ================= UPDATE FROM FORM =================
function handleUpdateFrom(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    
    console.log("📝 Starting update from (single execution)...");
    
    const $form = $(this);
    const formData = $form.serialize();
    
    console.log("📤 Sending data:", formData);
    
    const $submitBtn = $form.find('button[type="submit"]');
    const originalText = $submitBtn.html();
    
    $form.find('input, textarea, button').prop('disabled', true);
    $submitBtn.html('<span class="spinner-border spinner-border-sm"></span> Updating...');
    
    toastInfo('Updating information...', 2000);
    
    $.ajax({
        url: 'modules/data_information.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            console.log("🚀 AJAX request sending...");
        },
        success: function(response) {
            console.log("✅ Update response:", response);
            
            $form.find('input, textarea, button').prop('disabled', false);
            $submitBtn.html(originalText);
            
            if (response && response.success) {
                toastSuccess('Information updated successfully!', 2000);
                
                setTimeout(() => {
                    $('#modal-update-information-from').modal('hide');
                }, 1000);
                
                setTimeout(() => {
                    fetchDataInformation();
                }, 1500);
                
            } else {
                const errorMsg = response ? response.message : 'Invalid server response';
                console.error("❌ Server error:", response);
                toastError(errorMsg || 'Update failed');
                
                if (response && response.debug) {
                    console.error("Debug info:", response.debug);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Update error:", {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            
            $form.find('input, textarea, button').prop('disabled', false);
            $submitBtn.html(originalText);
            
            toastError('Network error. Please try again.');
        }
    });
    
    return false;
}

// ================= UPDATE TO FORM - PERBAIKAN =================
function handleUpdateTo(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    
    console.log("🔄 UPDATE-TO: Starting with DEBUG...");
    
    const $form = $(this);
    const formId = $form.attr('id') || 'unknown-form';
    console.log("📋 Form ID:", formId);
    
    const remark = $('#txt-remark-update').val().trim();
    if (!remark) {
        alert('Remark tidak boleh kosong!');
        $('#txt-remark-update').focus();
        return false;
    }
    
    const formData = new FormData();
    
    formData.append('type', 'update-to');
    formData.append('txt-id-information2', $('#txt-id-information2').val());
    formData.append('txt-timefrom-to-update', $('#txt-timefrom-to-update').val());
    formData.append('txt-picfrom-to-update', $('#txt-picfrom-to-update').val());
    formData.append('txt-itemto-update', $('#txt-itemto-update').val());
    formData.append('txt-requestto-update', $('#txt-requestto-update').val());
    formData.append('txt-timeto-update', $('#txt-timeto-update').val());
    formData.append('txt-picto-update', $('#txt-picto-update').val());
    formData.append('txt-remark-update', $('#txt-remark-update').val());
    
    console.log("📤 DATA YANG AKAN DIKIRIM KE SERVER:");
    console.log("Type:", 'update-to');
    console.log("ID:", $('#txt-id-information2').val());
    console.log("Time To:", $('#txt-timeto-update').val());
    console.log("PIC To:", $('#txt-picto-update').val());
    console.log("Remark:", $('#txt-remark-update').val());
    
    const $submitBtn = $form.find('button[type="submit"]');
    const originalText = $submitBtn.html();
    $form.find('input, textarea, button').prop('disabled', true);
    $submitBtn.html('<span class="spinner-border spinner-border-sm"></span> Processing...');
    
    $.ajax({
        url: 'modules/data_information.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        timeout: 10000,
        beforeSend: function() {
            console.log("🚀 Sending to modules/data_information.php...");
        },
        success: function(response) {
            console.log("✅ SERVER RESPONSE:", response);
            
            $form.find('input, textarea, button').prop('disabled', false);
            $submitBtn.html(originalText);
            
            if (response && response.success) {
                toastSuccess('✅ Information closed successfully!');
                
                setTimeout(() => {
                    $('#modal-update-information-to').modal('hide');
                }, 1000);
                
                setTimeout(() => {
                    fetchDataInformation();
                }, 1500);
                
            } else {
                const errorMsg = response?.message || 'Update failed';
                toastError('❌ ' + errorMsg);
                console.error("❌ SERVER ERROR DETAILS:", response);
                
                if (response?.debug) {
                    console.error("Debug info:", response.debug);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ AJAX ERROR:", {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            
            $form.find('input, textarea, button').prop('disabled', false);
            $submitBtn.html(originalText);
            
            toastError('❌ Network error: ' + error);
        },
        complete: function() {
            console.log("✅ AJAX Request completed");
        }
    });
    
    return false;
}

// ================= DELETE INFORMATION =================
function handleDeleteInformation(idInformation) {
    console.log("🗑️ Deleting info:", idInformation);
    
    Swal.fire({
        title: 'Delete Information?',
        text: 'Are you sure you want to delete this information? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: 'modules/data_information.php',
                    type: 'POST',
                    data: {
                        type: 'delete',
                        id_information: idInformation
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log("✅ Delete response:", response);
                        resolve(response);
                    },
                    error: function(xhr) {
                        console.error("❌ Delete error:", xhr.responseText);
                        reject('Network error');
                    }
                });
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            const response = result.value;
            
            if (response && response.success) {
                toastSuccess('Information deleted successfully!', 2000);
                
                setTimeout(() => {
                    fetchDataInformation();
                }, 500);
                
            } else {
                toastError(response?.message || 'Failed to delete information');
            }
        }
    });
}

// ================= TOAST FUNCTIONS =================
function toastSuccess(message, duration = 3000) {
    $('.custom-toast').remove();
    
    const toast = $(`
        <div class="custom-toast toast-success">
            <div class="toast-icon">
                <i class="bx bx-check-circle"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">Success</div>
                <div class="toast-message">${message}</div>
            </div>
        </div>
    `);
    
    $('body').append(toast);
    
    setTimeout(() => toast.addClass('show'), 10);
    setTimeout(() => {
        toast.removeClass('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function toastError(message, duration = 5000) {
    $('.custom-toast').remove();
    
    const toast = $(`
        <div class="custom-toast toast-error">
            <div class="toast-icon">
                <i class="bx bx-error-circle"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">Error</div>
                <div class="toast-message">${message}</div>
            </div>
        </div>
    `);
    
    $('body').append(toast);
    
    setTimeout(() => toast.addClass('show'), 10);
    setTimeout(() => {
        toast.removeClass('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function toastInfo(message, duration = 3000) {
    $('.custom-toast').remove();
    
    const toast = $(`
        <div class="custom-toast toast-info">
            <div class="toast-icon">
                <i class="bx bx-info-circle"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">Info</div>
                <div class="toast-message">${message}</div>
            </div>
        </div>
    `);
    
    $('body').append(toast);
    
    setTimeout(() => toast.addClass('show'), 10);
    setTimeout(() => {
        toast.removeClass('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// ================= DOWNLOAD EXCEL FUNCTIONS =================
function downloadExcel() {  
    console.log("📥 Starting Excel download...");
    
    var data = tableDetailProgress.rows({search:'applied'}).data().toArray();  
    
    if (data.length === 0) {
        alert("Tidak ada data untuk di-download");
        return;
    }
    
    if (Object.keys(dsActualMap).length === 0 || Object.keys(nsActualMap).length === 0) {
        console.warn("Actual maps belum terisi, mencoba refresh...");
        refreshActualMaps();
        
        setTimeout(() => {
            downloadExcel();
        }, 1000);
        return;
    }
    
    var header = [  
        "Date",  
        "Supplier Code",  
        "Part No",  
        "Part Name",  
        "D/S Regular Order",  
        "D/S Add Order",  
        "D/S Order Total",  
        "D/S Actual",  
        "Status D/S",  
        "N/S Regular Order",  
        "N/S Add Order",  
        "N/S Order Total",  
        "N/S Actual",  
        "Status N/S",  
        "Total Order",  
        "Total Incoming",  
        "Status Incoming",  
        "Remark DS",  
        "Remark NS"  
    ];  
    
    var excelData = [header];  
    
    data.forEach(function(row) {  
        var key = safeTrim(row.DATE) + '|' + safeTrim(row.SUPPLIER_CODE) + '|' + safeTrim(row.PART_NO);  
    
        var dsRegular = parseInt(row.REGULER_DS) || 0;  
        var dsAdd = parseInt(row.ADD_DS) || 0;  
        var dsOrderTotal = dsRegular + dsAdd;  
        var dsActual = dsActualMap[key] !== undefined ? dsActualMap[key] : 0;  
        var dsActualInt = parseInt(dsActual) || 0;  
        var statusDS = '';  
        if (dsOrderTotal > dsActualInt) statusDS = 'Delay';  
        else if (dsOrderTotal === dsActualInt) statusDS = 'OK';  
        else if (dsOrderTotal < dsActualInt) statusDS = 'Over';  
    
        var nsRegular = parseInt(row.REGULER_NS) || 0;  
        var nsAdd = parseInt(row.ADD_NS) || 0;  
        var nsOrderTotal = nsRegular + nsAdd;  
        var nsActual = nsActualMap[key] !== undefined ? nsActualMap[key] : 0;  
        var nsActualInt = parseInt(nsActual) || 0;  
        var statusNS = '';  
        if (nsOrderTotal > nsActualInt) statusNS = 'Delay';  
        else if (nsOrderTotal === nsActualInt) statusNS = 'OK';  
        else if (nsOrderTotal < nsActualInt) statusNS = 'Over';  

        var totalOrder = dsOrderTotal + nsOrderTotal;  
        var totalIncoming = dsActualInt + nsActualInt;  
        var statusIncoming = '';  
        if (totalOrder > totalIncoming) statusIncoming = 'Delay';  
        else if (totalOrder === totalIncoming) statusIncoming = 'OK';  
        else if (totalOrder < totalIncoming) statusIncoming = 'Over';  
    
        var dateStr = String(row.DATE);  
        if (/^\d{8}$/.test(dateStr)) {  
            dateStr = dateStr.slice(0,4) + '-' + dateStr.slice(4,6) + '-' + dateStr.slice(6,8);  
        }  
    
        excelData.push([  
            dateStr,  
            row.SUPPLIER_CODE || '',  
            row.PART_NO || '',  
            row.PART_NAME || '',  
            dsRegular,  
            dsAdd,  
            dsOrderTotal,  
            dsActualInt,  
            statusDS,  
            nsRegular,  
            nsAdd,  
            nsOrderTotal,  
            nsActualInt,  
            statusNS,  
            totalOrder,  
            totalIncoming,  
            statusIncoming,  
            row.REMARK_DS || '',
            row.REMARK_NS || ''
        ]);  
    });  
    
    try {
        var wb = XLSX.utils.book_new();  
        var ws = XLSX.utils.aoa_to_sheet(excelData);  
        XLSX.utils.book_append_sheet(wb, ws, "Progress Detail");  
        
        var date1 = $('#range-date1').val() || new Date().toISOString().slice(0,10);
        var date2 = $('#range-date2').val() || date1;
        
        XLSX.writeFile(wb, `Progress_Detail_${date1}_to_${date2}.xlsx`);
        
        console.log("✅ Excel downloaded successfully");
    } catch (error) {
        console.error("❌ Error downloading Excel:", error);
        alert("Error downloading file: " + error.message);
    }
}

function downloadAccumExcel() {  
    console.log("📥 Starting Accum Excel download...");
    
    let {daysInMonth, selectedTxt, year, month} = accumTableParams;  
    let data = accumDataAll.slice();  
    
    if (data.length === 0) {
        alert("Tidak ada data accum untuk di-download");
        return;
    }
    
    let val = (accumTableParams.search||'').trim().toLowerCase();  
    if(val) {  
        data = data.filter(d =>  
            d.SUPPLIER_CODE.toLowerCase().includes(val) ||  
            d.SUPPLIER_NAME.toLowerCase().includes(val) ||  
            d.PART_NO.toLowerCase().includes(val) ||  
            d.PART_NAME.toLowerCase().includes(val)  
        );  
    }  
    
    data = data.sort((a,b) => {  
        let x = (a[accumTableParams.sort]||'').toLowerCase(), y = (b[accumTableParams.sort]||'').toLowerCase();  
        return x.localeCompare(y, 'en', {numeric:true});  
    });  
    
    let header1 = ["Supplier Code","Supplier Name","Part No","Part Name","Item",`Bulan ${selectedTxt}`];  
    let header2 = ["", "", "", "", ""];  
    for(let i=1;i<=daysInMonth;i++) header2.push(i);  
    
    let rows = [];  
    data.forEach(function(d){  
        let ordQtyPerDay = Array(daysInMonth).fill(0);
        let addDsPerDay = Array(daysInMonth).fill(0);
        let addNsPerDay = Array(daysInMonth).fill(0);
        let totalOrderPerDay = Array(daysInMonth).fill(0);
        
        if (d.DATA_ARRAY && Array.isArray(d.DATA_ARRAY)) {
            d.DATA_ARRAY.forEach(dayData => {
                let dayIndex = parseInt(dayData.DAY) - 1;
                if (dayIndex >= 0 && dayIndex < daysInMonth) {
                    ordQtyPerDay[dayIndex] += parseInt(dayData.REGULAR_ORDER) || 0;
                    addDsPerDay[dayIndex] += parseInt(dayData.ADD_DS) || 0;
                    addNsPerDay[dayIndex] += parseInt(dayData.ADD_NS) || 0;
                    totalOrderPerDay[dayIndex] += parseInt(dayData.TOTAL_ORDER) || 0;
                }
            });
        }
        
        let incomingPerDay = [];  
        for(let i=0; i<daysInMonth; i++) {  
            let tanggal = year + month + pad(i+1);
            let key = tanggal + '|' + safeTrim(d.SUPPLIER_CODE) + '|' + safeTrim(d.PART_NO);  
            let ds = parseInt(dsActualMap[key]) || 0;  
            let ns = parseInt(nsActualMap[key]) || 0;  
            incomingPerDay[i] = ds + ns;  
        }  
        
        let resultPerDay = totalOrderPerDay.map((qty, idx) => incomingPerDay[idx] - qty);  
        let balancePerDay = [];  
        for(let i=0;i<daysInMonth;i++) {  
            if(i==0) balancePerDay[i] = resultPerDay[0];  
            else balancePerDay[i] = balancePerDay[i-1] + resultPerDay[i];  
        }  
        
        rows.push([  
            d.SUPPLIER_CODE, d.SUPPLIER_NAME, d.PART_NO, d.PART_NAME, "Regular Order", ...ordQtyPerDay  
        ]);  
        rows.push([  
            "", "", "", "", "Add D/S", ...addDsPerDay  
        ]);  
        rows.push([  
            "", "", "", "", "Add N/S", ...addNsPerDay  
        ]);  
        rows.push([  
            "", "", "", "", "Total Order", ...totalOrderPerDay  
        ]);  
        rows.push([  
            "", "", "", "", "Total Incoming", ...incomingPerDay  
        ]);  
        rows.push([  
            "", "", "", "", "Result (Incoming - Order)", ...resultPerDay  
        ]);  
        rows.push([  
            "", "", "", "", "Total Akumulasi", ...balancePerDay  
        ]);  
        rows.push([  
            "", "", "", "", "", ...Array(daysInMonth).fill("")  
        ]);  
    });  
    
    let excelData = [header1, header2, ...rows];  
    
    try {
        let ws = XLSX.utils.aoa_to_sheet(excelData);  
        let wb = XLSX.utils.book_new();  
        XLSX.utils.book_append_sheet(wb, ws, "Accum");  
        
        XLSX.writeFile(wb, `Accum_${selectedTxt.replace(' ','_')}.xlsx`);
        
        console.log("✅ Accum Excel downloaded successfully");
    } catch (error) {
        console.error("❌ Error downloading Accum Excel:", error);
        alert("Error downloading file: " + error.message);
    }
}

// ================= EVENT HANDLER UPLOAD =================
function handleUpload(e) {
    e.preventDefault();
    
    console.log("🚀 Starting upload process...");
    
    var $form = $(this);
    var $uploadBtn = $form.find('button[type="submit"]');
    var originalText = $uploadBtn.html();
    var formData = new FormData(this);
    
    $uploadBtn.html('<span class="spinner-border spinner-border-sm"></span> Uploading...');
    $uploadBtn.prop('disabled', true);
    $uploadBtn.addClass('btn-loading');
    
    Swal.fire({
        title: '<div class="text-center"><i class="bx bx-cloud-upload bx-lg text-primary mb-3"></i></div>',
        html: `
            <div class="text-center">
                <h5 class="mb-2">Uploading Data</h5>
                <p class="text-muted mb-3">Please wait while we process your file...</p>
                
                <div class="upload-progress-container">
                    <div class="upload-progress-bar">
                        <div class="upload-progress-fill" id="uploadProgressFill"></div>
                    </div>
                    <div class="upload-progress-text" id="uploadProgressText">Preparing upload...</div>
                </div>
                
                <div class="mt-3" id="uploadStats">
                    <small class="text-muted">
                        <i class="bx bx-time"></i> Processing...
                    </small>
                </div>
            </div>
        `,
        showConfirmButton: false,
        showCancelButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        backdrop: 'rgba(0,0,0,0.7)',
        width: 450,
        customClass: {
            popup: 'upload-modal'
        }
    });
    
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 10;
        if (progress > 90) progress = 90;
        
        $('#uploadProgressFill').css('width', progress + '%');
        $('#uploadProgressText').text(`Uploading... ${Math.round(progress)}%`);
    }, 500);
    
    $.ajax({
        url: './api/upload_data.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    var percentComplete = (e.loaded / e.total) * 100;
                    $('#uploadProgressFill').css('width', percentComplete + '%');
                    $('#uploadProgressText').text(`Uploading... ${Math.round(percentComplete)}%`);
                }
            }, false);
            
            return xhr;
        },
        beforeSend: function() {
            console.log("📤 Starting file upload...");
            $('#uploadProgressText').text('Starting upload...');
        },
        success: function(response) {
            clearInterval(progressInterval);
            
            console.log("✅ Upload response:", response);
            
            $('#uploadProgressFill').css('width', '100%');
            $('#uploadProgressText').text('Processing data...');
            
            setTimeout(() => {
                $uploadBtn.html(originalText);
                $uploadBtn.prop('disabled', false);
                $uploadBtn.removeClass('btn-loading');
            }, 1000);
            
            $form[0].reset();
            
            if (response.success) {
                Swal.fire({
                    title: '<div class="text-center"><i class="bx bx-check-circle bx-lg text-success mb-3"></i></div>',
                    html: `
                        <div class="text-center">
                            <h5 class="text-success mb-2">Upload Successful!</h5>
                            <p class="mb-3">${response.message}</p>
                            
                            <div class="alert alert-success bg-success-soft border-success">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-data bx-sm me-2"></i>
                                    <div>
                                        <strong>${response.count}</strong> records processed
                                        <br>
                                        <small class="text-muted">Time: ${response.processing_time}s</small>
                                    </div>
                                </div>
                            </div>
                            
                            <p class="text-muted small mt-3">
                                Data will be refreshed automatically...
                            </p>
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#28a745',
                    timer: 3000,
                    timerProgressBar: true,
                    willClose: () => {
                        $("#modal-upload").modal('hide');
                        refreshDataAfterUpload();
                    }
                });
                
            } else {
                Swal.fire({
                    title: '<div class="text-center"><i class="bx bx-error-circle bx-lg text-danger mb-3"></i></div>',
                    html: `
                        <div class="text-center">
                            <h5 class="text-danger mb-2">Upload Failed!</h5>
                            <p class="mb-3">${response.message}</p>
                            
                            ${response.error ? `
                            <div class="alert alert-danger text-start small">
                                <strong>Technical Details:</strong>
                                <div class="mt-2">
                                    <code>${response.error.type || 'Unknown error'}</code>
                                    <br>
                                    <small>File: ${response.error.file || ''}</small>
                                </div>
                            </div>
                            ` : ''}
                            
                            <p class="text-muted small mt-3">
                                Please check your file format and try again.
                            </p>
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonText: 'Try Again',
                    confirmButtonColor: '#dc3545'
                });
            }
        },
        error: function(xhr, status, error) {
            clearInterval(progressInterval);
            
            console.error("❌ Upload error:", xhr.responseText);
            
            $uploadBtn.html(originalText);
            $uploadBtn.prop('disabled', false);
            $uploadBtn.removeClass('btn-loading');
            
            let errorMsg = "Server error occurred";
            try {
                const err = JSON.parse(xhr.responseText);
                errorMsg = err.message || err.error || errorMsg;
            } catch (e) {
                errorMsg = xhr.statusText || errorMsg;
            }
            
            Swal.fire({
                title: '<div class="text-center"><i class="bx bx-wifi-off bx-lg text-danger mb-3"></i></div>',
                html: `
                    <div class="text-center">
                        <h5 class="text-danger mb-2">Network Error!</h5>
                        <p class="mb-3">${errorMsg}</p>
                        
                        <div class="alert alert-warning text-start small">
                            <strong>Possible causes:</strong>
                            <ul class="mb-0 mt-2">
                                <li>File size too large</li>
                                <li>Network connection lost</li>
                                <li>Server timeout</li>
                                <li>Invalid file format</li>
                            </ul>
                        </div>
                        
                        <p class="text-muted small mt-3">
                            Please try again with a smaller file.
                        </p>
                    </div>
                `,
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545',
                allowOutsideClick: false
            });
        },
        complete: function() {
            clearInterval(progressInterval);
        }
    });
}

function refreshDataAfterUpload() {
    console.log("🔄 Refreshing data after upload...");
    
    showTableSkeleton('#table-detail-progress tbody', 10);
    
    toastInfo('Refreshing data...', 2000);
    
    setTimeout(() => {
        refreshActualMaps();
        
        setTimeout(() => {
            loadTableDetailProgress();
            
            setTimeout(() => {
                fetchDataInformation();
                
                setTimeout(() => {
                    toastSuccess('All data refreshed successfully!', 3000);
                }, 1000);
                
            }, 500);
            
        }, 500);
        
    }, 1000);
}

function addUploadStyles() {
    if (!$('#upload-styles').length) {
        $('head').append(`
            <style id="upload-styles">
                .upload-modal .swal2-popup {
                    border-radius: 15px;
                    padding: 2rem;
                }
                
                .upload-progress-container {
                    margin: 20px 0;
                }
                
                .upload-progress-bar {
                    height: 10px;
                    background: #e9ecef;
                    border-radius: 5px;
                    overflow: hidden;
                    margin-bottom: 8px;
                }
                
                .upload-progress-fill {
                    height: 100%;
                    background: linear-gradient(90deg, #007bff, #00c6ff);
                    border-radius: 5px;
                    transition: width 0.3s ease;
                    width: 0%;
                }
                
                .upload-progress-text {
                    font-size: 13px;
                    color: #666;
                    text-align: center;
                }
                
                .bg-success-soft {
                    background-color: rgba(40, 167, 69, 0.1) !important;
                }
                
                .btn-loading {
                    position: relative;
                    pointer-events: none;
                }
                
                .btn-loading:after {
                    content: '';
                    position: absolute;
                    width: 20px;
                    height: 20px;
                    top: 50%;
                    left: 50%;
                    margin: -10px 0 0 -10px;
                    border: 2px solid rgba(255,255,255,0.3);
                    border-top-color: #fff;
                    border-radius: 50%;
                    animation: button-spinner 0.6s linear infinite;
                }
                
                @keyframes button-spinner {
                    to { transform: rotate(360deg); }
                }
                
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
                
                .toast-success {
                    border-left: 4px solid #28a745;
                }
                
                .toast-error {
                    border-left: 4px solid #dc3545;
                }
                
                .toast-info {
                    border-left: 4px solid #17a2b8;
                }
                
                .toast-icon {
                    font-size: 24px;
                    margin-right: 15px;
                }
                
                .toast-success .toast-icon { color: #28a745; }
                .toast-error .toast-icon { color: #dc3545; }
                .toast-info .toast-icon { color: #17a2b8; }
            </style>
        `);
    }
}

// ================= INITIALIZATION =================
$(document).ready(function() {
    console.log("🚀 Document ready, initializing application...");
    
    showLoader(true);
    
    setDefaultDateRange();
    
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        calendarWeeks: true,
        clearBtn: true,
        disableTouchKeyboard: true,
        todayHighlight: true,
        orientation: "bottom auto",
        templates: {
            leftArrow: '<i class="bi bi-chevron-left"></i>',
            rightArrow: '<i class="bi bi-chevron-right"></i>'
        },
        beforeShowDay: function(date) {
            const today = new Date();
            if (date.getDate() === today.getDate() && 
                date.getMonth() === today.getMonth() && 
                date.getFullYear() === today.getFullYear()) {
                return {
                    classes: 'today',
                    tooltip: 'Today'
                };
            }
            return {};
        }
    }).on('changeDate', function(e) {
        console.log("📅 Date changed:", $(this).attr('id'), e.format());
        setTimeout(() => {
            handleDateChange();
        }, 100);
    });
    
    $('#btn-clear-dates').on('click', clearDateRange);
    
    $('#range-date1, #range-date2').on('blur', function() {
        setTimeout(() => {
            handleDateChange();
        }, 100);
    });
    
    var $selectSupplier = $('#select-supplier-code').selectize({
        plugins: ['remove_button'], 
        maxItems: null,
        searchField: 'text',
        placeholder: 'Select Supplier Code',
        onChange: function(value) {
            if (value.includes('select-all')) {
                var selectize = this;
                var allValues = [];
                var optionsArray = Object.values(selectize.options);
                optionsArray.forEach(function(option) {
                    if (option.value && option.value !== 'select-all') {
                        allValues.push(option.value);
                    }
                });
                selectize.setValue(allValues);
            }
        }
    });
    
    var $selectPic = $('#select-pic').selectize({
        plugins: ['remove_button'], 
        maxItems: null,
        searchField: 'text',
        placeholder: 'Select PIC',
        onChange: function(value) {
            if (value.includes('select-all')) {
                var selectize = this;
                var allValues = [];
                var optionsArray = Object.values(selectize.options);
                optionsArray.forEach(function(option) {
                    if (option.value && option.value !== 'select-all') {
                        allValues.push(option.value);
                    }
                });
                selectize.setValue(allValues);
            }
            updateSupplierCodes(value);
        }
    });
    
    $('#select-status').selectize({
        plugins: ['remove_button'], 
        maxItems: null,
        searchField: 'text',
        placeholder: 'Select Status',
        options: [
            {value: '', text: ''},
            {value: 'select-all', text: 'Select All'},
            {value: 'OK', text: 'OK'},
            {value: 'ON_PROGRESS', text: 'ON PROGRESS'},
            {value: 'DELAY', text: 'DELAY'},
            {value: 'OVER', text: 'OVER'}
        ],
        onChange: function(value) {
            if (value.includes('select-all')) {
                var selectize = this;
                var allValues = [];
                var optionsArray = Object.values(selectize.options);
                optionsArray.forEach(function(option) {
                    if (option.value && option.value !== 'select-all') {
                        allValues.push(option.value);
                    }
                });
                selectize.setValue(allValues);
            }
            if (tableDetailProgress) tableDetailProgress.draw();
        }
    });
    
    // Initialize DataTables
    tableDetailProgress = $('#table-detail-progress').DataTable({  
        pageLength: 10,  
        autoWidth: true,  
        aaSorting: [[0, "desc"]],  
        bDestroy: true,  
        scrollX: true,  
        scrollCollapse: true,  
        paging: true,  
        language: {
            processing: '<div class="spinner-border spinner-border-sm" role="status"></div> Loading...',
            emptyTable: 'NO DATA available in table',
            zeroRecords: 'No matching records found'
        },
        columns: [
            { data: 'DATE', render: formatDate },
            { data: 'SUPPLIER_CODE', defaultContent: '' },
            { data: 'PART_NO', defaultContent: '' },
            { data: 'PART_NAME', defaultContent: '', render: function(data, type, row) {
                return data || row.PART_DESC || '';
            }},
            
            // D/S SECTION
            { data: 'REGULER_DS', defaultContent: 0 },
            { data: 'ADD_DS', defaultContent: 0 },
            { data: null, render: renderAddDSButton },
            { data: null, render: calculateDSTotal },
            { data: 'DS_ACTUAL', defaultContent: 0 },
            { data: null, render: getDSStatus },
            
            // N/S SECTION  
            { data: 'REGULER_NS', defaultContent: 0 },
            { data: 'ADD_NS', defaultContent: 0 },
            { data: null, render: renderAddNSButton },
            { data: null, render: calculateNSTotal },
            { data: 'NS_ACTUAL', defaultContent: 0 },
            { data: null, render: getNSStatus },
            
            // TOTALS SECTION
            { data: 'ORD_QTY_TOTAL', defaultContent: 0 },
            { data: null, render: function(data, type, row) {
                var dsActual = parseInt(row.DS_ACTUAL) || 0;
                var nsActual = parseInt(row.NS_ACTUAL) || 0;
                return dsActual + nsActual;
            }},
            { data: null, render: getTotalStatus },
            
            { data: null, render: getRemarks }
        ], 
        columnDefs: [  
            {  
                targets: [5, 7, 8, 10, 12, 13, 14, 15, 16, 17, 18, 19],  
                defaultContent: ''  
            }  
        ],
        initComplete: function() {
            console.log("✅ DataTable initialized with NEW 4-STATUS SYSTEM");
        }
    });
    
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'table-detail-progress') return true;
        
        let selectedStatuses = $('#select-status').val();
        if (!selectedStatuses || selectedStatuses.length === 0 || selectedStatuses.includes('select-all')) {
            return true;
        }
        
        var row = tableDetailProgress.row(dataIndex).data();
        if (!row) {
            return true;
        }

        var status = row.STATUS || '';
        var show = selectedStatuses.includes(status);
        return show;
    });
    
    // CARI KODE INI (sekitar line 2050-2100) dan GANTI:
    tableInformation = $('#table-information').DataTable({
        pageLength: 10,
        autoWidth: true,
        aaSorting: [[0, "asc"]],
        bDestroy: true,
        scrollX: true,
        scrollCollapse: true,
        paging: true,
        searching: true,
        language: {
            processing: '<div class="spinner-border spinner-border-sm" role="status"></div> Loading...',
            emptyTable: 'No information available',
            zeroRecords: 'No matching records found',
            search: 'Search:',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        },
        columns: [
            { 
                title: "No", 
                data: null, 
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                },
                className: "text-center"
            },
            { 
                title: "Date", 
                data: "DATE", 
                render: formatDate,
                className: "text-center"
            },
            { 
                title: "Time", 
                data: "TIME_FROM",
                className: "text-center" 
            },
            { 
                title: "PIC", 
                data: "PIC_FROM",
                className: "text-center"
            },
            { 
                title: "Item", 
                data: "ITEM", 
                className: "text-center",
                render: function(data) {
                    return '<div class="table-text-center">' + (data || '-') + '</div>';
                }
            },
            { 
                title: "Request", 
                data: "REQUEST", 
                className: "text-center",
                render: function(data) {
                    return '<div class="table-text-center">' + (data || '-') + '</div>';
                }
            },
            { 
                title: "Action", 
                data: null, 
                orderable: false, 
                searchable: false,
                className: "text-center",
                render: function(data, type, row) {
                    const role = row.user_role || '';
                    const status = row.STATUS || '';
                    
                    // Hanya sender yang bisa edit/delete
                    if (role === 'sender') {
                        let buttons = '';
                        // Edit hanya jika status Open
                        if (status === 'Open') {
                            buttons += `<button class="btn btn-sm btn-warning btn-edit-info me-1 btn-action-table" 
                                        data-id="${row.ID_INFORMATION}" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                      </button>`;
                        }
                        // Delete button
                        buttons += `<button class="btn btn-sm btn-danger btn-delete-info btn-action-table" 
                                        data-id="${row.ID_INFORMATION}" title="Delete">
                                        <i class="bi bi-trash"></i>
                                      </button>`;
                        return buttons;
                    }
                    return '-';
                }
            },
            { 
                title: "Time", 
                data: "TIME_TO",
                className: "text-center",
                render: function(data) {
                    return data || '-';
                }
            },
            { 
                title: "PIC", 
                data: "PIC_TO",
                className: "text-center",
                render: function(data) {
                    return data || '-';
                }
            },
            { 
                title: "Status", 
                data: "STATUS", 
                className: "text-center",
                render: function(data) {
                    let badgeClass = 'bg-secondary';
                    let displayText = data || '-';
                    
                    if (data === 'Open') {
                        badgeClass = 'bg-danger';
                        displayText = 'OPEN';
                    } else if (data === 'On Progress') {
                        badgeClass = 'bg-warning';
                        displayText = 'ON PROGRESS';
                    } else if (data === 'Closed') {
                        badgeClass = 'bg-success';
                        displayText = 'CLOSED';
                    }
                    
                    return `<div class="status-container">
                        <span class="badge ${badgeClass} w-100 py-2">${displayText}</span>
                    </div>`;
                }
            },
            { 
                title: "Remark", 
                data: "REMARK", 
                className: "text-center",
                render: function(data) {
                    return '<div class="table-text-center">' + (data || '-') + '</div>';
                }
            },
            { 
                title: "Action", 
                data: null, 
                orderable: false, 
                searchable: false,
                className: "text-center",
                render: function(data, type, row) {
                    const role = row.user_role || '';
                    const status = row.STATUS || '';
                    
                    // Hanya recipient yang bisa reply
                    if (role === 'recipient' && status !== 'Closed') {
                        let buttonText = '';
                        let buttonClass = '';
                        
                        if (status === 'Open') {
                            buttonText = '<i class="bi bi-reply"></i> Reply';
                            buttonClass = 'btn-success';
                        } else if (status === 'On Progress') {
                            buttonText = '<i class="bi bi-arrow-clockwise"></i> Update';
                            buttonClass = 'btn-info';
                        }
                        
                        return `<button class="btn btn-sm ${buttonClass} btn-reply-info" 
                                  data-id="${row.ID_INFORMATION}" title="Update Status">
                                  ${buttonText}
                                </button>`;
                    }
                    return '-';
                }
            }
        ],
        drawCallback: function(settings) {
            console.log('🔄 DataTable draw callback');
            setTimeout(() => {
                if (window.informationSystem && window.informationSystem.bindTableEvents) {
                    window.informationSystem.bindTableEvents();
                    console.log('✅ Table events re-bound');
                }
            }, 300);
        },
        initComplete: function() {
            console.log('✅ Information DataTable initialized with proper columns');
            if (window.informationSystem) {
                setTimeout(() => {
                    window.informationSystem.bindTableEvents();
                }, 500);
            }
        }
    });
    
    tableByCycle = $('#table-by-cycle').DataTable({
        pageLength: 10,
        autoWidth: true,
        aaSorting: [],
        "bDestroy": true,
        scrollX: true,
        scrollCollapse: true,
        paging: true,
        language: {
            processing: '<div class="spinner-border spinner-border-sm" role="status"></div> Loading...',
            emptyTable: 'No cycle data available',
            zeroRecords: 'No matching records found'
        }
    });
    
    tableDetailDS = $('#table-detail-ds').DataTable({
        pageLength: 10,
        autoWidth: false,
        aaSorting: [],
        "bDestroy": true,
        scrollX: false,
        scrollCollapse: false,
        paging: false,
        searching: false,
        info: false,
        language: {
            emptyTable: 'No day shift data available',
            zeroRecords: 'No matching records found'
        },
        columnDefs: [
            { searchable: false, targets: '_all' }
        ]
    });

    tableDetailNS = $('#table-detail-ns').DataTable({
        pageLength: 10,
        autoWidth: false,
        aaSorting: [],
        "bDestroy": true,
        scrollX: false,
        scrollCollapse: false,
        paging: false,
        searching: false,
        info: false,
        language: {
            emptyTable: 'No night shift data available',
            zeroRecords: 'No matching records found'
        },
        columnDefs: [
            { searchable: false, targets: '_all' }
        ]
    });
    
    updateSupplierCodes('');
    
    if (!$('#select-pic').val()) {
        $selectPic[0].selectize.setValue(['ALBERTO']);
    }
    
    $("#range-date1, #range-date2").on("change", function() {
        handleDateChange();
    });
    
    // Update event handler untuk filter changes
    $('#select-pic, #select-supplier-code, #select-status').on('change', function() {
        // Update filter global
        updateGlobalFilters();
        
        // Refresh progress table
        loadTableDetailProgress();
        
        // Refresh modal yang sedang terbuka
        refreshOpenModals();
    });
    
    $('#btn-upload').on('click', function() {
        $("#modal-upload").modal('show');
    });
    
    $('#btn-refresh-information').on('click', fetchDataInformation);
    $('#btn-add-information').on('click', showAddInformationModal);
    $('#btn-modal-by-cycle').on('click', showCycleModal);
    $('#btn-modal-by-accum').on('click', showAccumModal);
    $('#btn-modal-ds').on('click', showDSModal);
    $('#btn-modal-ns').on('click', showNSModal);
    $('#btn-download-excel').on('click', downloadExcel);
    
    addUploadStyles();

    // Form submissions
    $(".uploadForm").on("submit", handleUpload);
    
    // Event delegation untuk form
    $(document).on('submit', '.dataInformationForm', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        console.log("📝 Form submit triggered (delegation)");
        handleAddInformation.call(this, e);
    });

    $(document).on('submit', '.updateFromInformationForm', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        console.log("📝 Update from form submitted");
        handleUpdateFrom.call(this, e);
    });

    $(document).on('submit', '.updateToInformationForm', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        console.log("📝 Update to form submitted");
        handleUpdateTo.call(this, e);
    });
    
    $('#form-add-ds').on('submit', handleAddDS);
    $('#form-add-ns').on('submit', handleAddNS);
    

        // Reset buttons
    $('#btn-reset-ds').on('click', function() {
        // Function sudah didefinisikan di atas
    });
    
    $('#btn-reset-ns').on('click', function() {
        // Function sudah didefinisikan di atas
    });

    // DS Quantity controls
    $('#ds-increase').on('click', function() {
        const $input = $('#txt-ds-addqty');
        let val = parseInt($input.val()) || 0;
        $input.val(val + 1);
    });
    
    $('#ds-decrease').on('click', function() {
        const $input = $('#txt-ds-addqty');
        let val = parseInt($input.val()) || 0;
        if (val > 0) $input.val(val - 1);
    });
    
    $('.ds-quick-btn').on('click', function() {
        const $input = $('#txt-ds-addqty');
        const addValue = parseInt($(this).data('value'));
        let currentVal = parseInt($input.val()) || 0;
        $input.val(currentVal + addValue);
    });
    
    // NS Quantity controls
    $('#ns-increase').on('click', function() {
        const $input = $('#txt-ns-addqty');
        let val = parseInt($input.val()) || 0;
        $input.val(val + 1);
    });
    
    $('#ns-decrease').on('click', function() {
        const $input = $('#txt-ns-addqty');
        let val = parseInt($input.val()) || 0;
        if (val > 0) $input.val(val - 1);
    });
    
    $('.ns-quick-btn').on('click', function() {
        const $input = $('#txt-ns-addqty');
        const addValue = parseInt($(this).data('value'));
        let currentVal = parseInt($input.val()) || 0;
        $input.val(currentVal + addValue);
    });
    
// ================= FUNGSI RESET UNTUK D/S & N/S =================

$('#btn-reset-ds').on('click', function() {
    const remark = $('#txt-ds-remark').val().trim();
    
    Swal.fire({
        title: 'Reset Add Order D/S?',
        html: `
            <div class="text-start">
                <p>Anda yakin ingin reset add order D/S ke 0?</p>
                <p><strong>Remark akan tetap tersimpan:</strong><br>${remark || '(kosong)'}</p>
                <p class="text-warning"><i class="bi bi-exclamation-triangle"></i> Total order akan berkurang!</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, reset ke 0!',
        cancelButtonText: 'Batal',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('date', $('#add-ds-date').val());
                formData.append('supplier_code', $('#add-ds-supplier').val());
                formData.append('part_no', $('#add-ds-partno').val());
                formData.append('type', 'ds');
                formData.append('action', 'reset');
                formData.append('remark', remark);
                
                $.ajax({
                    url: 'api/update_add_order.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        console.log('Reset DS Response:', response);
                        resolve(response);
                    },
                    error: function(xhr) {
                        console.error('Reset error:', xhr.responseText);
                        reject('Network error');
                    }
                });
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            const response = result.value;
            
            if (response && response.success) {
                showDSAlert('success', response.message || 'Add order berhasil direset!');
                
                // Reset UI
                dsSelectedHours = {};
                $('#ds-action').val('add');
                $('#ds-status-text').text('No add order yet');
                $('#btn-reset-ds').hide();
                updateDSQuantityInputs();
                
                // Refresh setelah 2 detik
                setTimeout(() => {
                    $('#modal-add-ds').modal('hide');
                    setTimeout(() => {
                        loadTableDetailProgress();
                    }, 500);
                }, 2000);
                
            } else {
                showDSAlert('error', response?.message || 'Gagal reset add order');
            }
        }
    });
});

$('#btn-reset-ns').on('click', function() {
    const remark = $('#txt-ns-remark').val().trim();
    
    Swal.fire({
        title: 'Reset Add Order N/S?',
        html: `
            <div class="text-start">
                <p>Anda yakin ingin reset add order N/S ke 0?</p>
                <p><strong>Remark akan tetap tersimpan:</strong><br>${remark || '(kosong)'}</p>
                <p class="text-warning"><i class="bi bi-exclamation-triangle"></i> Total order akan berkurang!</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, reset ke 0!',
        cancelButtonText: 'Batal',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('date', $('#add-ns-date').val());
                formData.append('supplier_code', $('#add-ns-supplier').val());
                formData.append('part_no', $('#add-ns-partno').val());
                formData.append('type', 'ns');
                formData.append('action', 'reset');
                formData.append('remark', remark);
                
                $.ajax({
                    url: 'api/update_add_order.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        console.log('Reset NS Response:', response);
                        resolve(response);
                    },
                    error: function(xhr) {
                        console.error('Reset error:', xhr.responseText);
                        reject('Network error');
                    }
                });
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            const response = result.value;
            
            if (response && response.success) {
                showNSAlert('success', response.message || 'Add order berhasil direset!');
                
                // Reset UI
                nsSelectedHours = {};
                $('#ns-action').val('add');
                $('#ns-status-text').text('No add order yet');
                $('#btn-reset-ns').hide();
                updateNSQuantityInputs();
                
                // Refresh setelah 2 detik
                setTimeout(() => {
                    $('#modal-add-ns').modal('hide');
                    setTimeout(() => {
                        loadTableDetailProgress();
                    }, 500);
                }, 2000);
                
            } else {
                showNSAlert('error', response?.message || 'Gagal reset add order');
            }
        }
    });
});

    // Modal close reset
    $('#modal-add-ds').on('hidden.bs.modal', function() {
        dsSelectedHours = {};
        currentDSData = null;
        $('#ds-action').val('add');
        $('#txt-ds-remark').val('');
        $('#ds-status-text').text('No add order yet');
        $('#btn-reset-ds').hide();
    });

    $('#modal-add-ns').on('hidden.bs.modal', function() {
        nsSelectedHours = {};
        currentNSData = null;
        $('#ns-action').val('add');
        $('#txt-ns-remark').val('');
        $('#ns-status-text').text('No add order yet');
        $('#btn-reset-ns').hide();
    });
    
    
    // ================= EVENT HANDLERS BARU UNTUK DS/NS =================
    
    // DS Search input dengan debounce
    let dsSearchTimeout;
    $('#ds-search-input').on('input', function() {
        clearTimeout(dsSearchTimeout);
        dsSearchTimeout = setTimeout(() => {
            dsCurrentPage = 1;
            renderDSTable();
        }, 300);
    });
    
    $('#ds-clear-search').on('click', function() {
        $('#ds-search-input').val('');
        dsCurrentPage = 1;
        renderDSTable();
    });
    
    // DS Page size change
    $('#ds-page-size').on('change', function() {
        dsPageSize = parseInt($(this).val());
        dsCurrentPage = 1;
        renderDSTable();
    });
    
    // DS Pagination click
    $(document).on('click', '#ds-pagination .page-link', function(e) {
        e.preventDefault();
        
        const $btn = $(this);
        if ($btn.parent().hasClass('disabled')) return;
        
        if ($btn.attr('id') === 'ds-prev-btn') {
            if (dsCurrentPage > 1) {
                dsCurrentPage--;
                renderDSTable();
            }
        } else if ($btn.attr('id') === 'ds-next-btn') {
            dsCurrentPage++;
            renderDSTable();
        } else if ($btn.data('page')) {
            dsCurrentPage = parseInt($btn.data('page'));
            renderDSTable();
        }
    });
    
    // NS Event handlers
    let nsSearchTimeout;
    $('#ns-search-input').on('input', function() {
        clearTimeout(nsSearchTimeout);
        nsSearchTimeout = setTimeout(() => {
            nsCurrentPage = 1;
            renderNSTable();
        }, 300);
    });
    
    $('#ns-clear-search').on('click', function() {
        $('#ns-search-input').val('');
        nsCurrentPage = 1;
        renderNSTable();
    });
    
    $('#ns-page-size').on('change', function() {
        nsPageSize = parseInt($(this).val());
        nsCurrentPage = 1;
        renderNSTable();
    });
    
    $(document).on('click', '#ns-pagination .page-link', function(e) {
        e.preventDefault();
        
        const $btn = $(this);
        if ($btn.parent().hasClass('disabled')) return;
        
        if ($btn.attr('id') === 'ns-prev-btn') {
            if (nsCurrentPage > 1) {
                nsCurrentPage--;
                renderNSTable();
            }
        } else if ($btn.attr('id') === 'ns-next-btn') {
            nsCurrentPage++;
            renderNSTable();
        } else if ($btn.data('page')) {
            nsCurrentPage = parseInt($btn.data('page'));
            renderNSTable();
        }
    });
    
    // Modal show event
    $('#modal-detail-ds').on('shown.bs.modal', function() {
        $('#ds-search-input').val('');
        dsCurrentPage = 1;
        
        if (dsFilteredData.length === 0) {
            loadDSData();
        } else {
            renderDSTable();
        }
    });
    
    $('#modal-detail-ns').on('shown.bs.modal', function() {
        $('#ns-search-input').val('');
        nsCurrentPage = 1;
        
        if (nsFilteredData.length === 0) {
            loadNSData();
        } else {
            renderNSTable();
        }
    });
    
    // Modal hide event
    $('#modal-detail-ds, #modal-detail-ns').on('hidden.bs.modal', function() {
        if ($(this).attr('id') === 'modal-detail-ds') {
            dsFilteredData = [];
        } else {
            nsFilteredData = [];
        }
    });
    
    // ================= UPDATE FORM HANDLERS =================
    $(document).on('click', '#table-information .btn-update-from', function(){
        const id = $(this).data('idinformation');
        const time_from = $(this).data('timefrom') || getCurrentDateTime();
        const pic_from = $(this).data('picfrom') || '<?php echo $_SESSION["name"] ?? "Unknown"; ?>';
        const item = $(this).data('item') || '';
        const request = $(this).data('request') || '';
        
        console.log("📋 Opening update from modal for ID:", id);
        
        $("#modal-update-information-from").modal('show');
        $("#txt-timefrom-update").val(time_from);
        $("#txt-picfrom-update").val(pic_from);
        $("#txt-item-update").val(item);
        $("#txt-request-update").val(request);
        $("#txt-id-information").val(id);
        
        const today = new Date().toISOString().split('T')[0];
        $('#txt-date-information-from').html("(" + today + ")");
    });

    $(document).on('click', '#table-information .btn-update-to', function(){
        const id = $(this).data('idinformation');
        const time_from = $(this).data('timefrom') || getCurrentDateTime();
        const pic_from = $(this).data('picfrom') || '<?php echo $_SESSION["name"] ?? "Unknown"; ?>';
        const item = $(this).data('item') || '';
        const request = $(this).data('request') || '';
        const pic_to = $(this).data('picto') || '<?php echo $_SESSION["name"] ?? "Unknown"; ?>';
        
        console.log("📋 Opening update to modal for ID:", id);
        
        $("#modal-update-information-to").modal('show');
        
        setTimeout(() => {
            $("#txt-id-information2").val(id || '');
            $("#txt-timefrom-to-update").val(time_from || getCurrentDateTime());
            $("#txt-picfrom-to-update").val(pic_from || 'System');
            $("#txt-itemto-update").val(item || '');
            $("#txt-requestto-update").val(request || '');
            $("#txt-picto-update").val(pic_to || '<?php echo $_SESSION["name"] ?? "Unknown"; ?>');
            
            $("#txt-timeto-update").val(getCurrentDateTime());
            
            const today = new Date().toISOString().split('T')[0];
            $('#txt-date-information-to').html("(" + today + ")");
            
            $("#txt-remark-update").val('');
        }, 300);
    });

    $(document).on('click', '#table-information .btn-delete', function(){
        const idInformation = $(this).data('idinformation');
        console.log("🗑️ Delete button clicked for ID:", idInformation);
        
        if (idInformation) {
            handleDeleteInformation(idInformation);
        } else {
            toastError('Invalid information ID');
        }
    });
    
    // Add D/S and N/S buttons
    $(document).on('click', '.btn-update-add1', showAddDSModal);
    $(document).on('click', '.btn-update-add2', showAddNSModal);
    
    // Accum table events
    $(document).on('input', '#accum-search', function(){  
        accumTableParams.search = $(this).val();  
        accumTableParams.page = 1;  
        renderAccumTable();  
    });  
    
    $(document).on('change', '#accum-sort', function(){  
        accumTableParams.sort = $(this).val();  
        accumTableParams.page = 1;  
        renderAccumTable();  
    });  
    
    $(document).on('change', '#accum-pagesize', function(){  
        accumTableParams.pageSize = parseInt($(this).val());  
        accumTableParams.page = 1;  
        renderAccumTable();  
    });  
    
    $(document).on('click', '#accum-prev', function(){  
        if(accumTableParams.page > 1) {  
            accumTableParams.page--;  
            renderAccumTable();  
        }  
    });  
    
    $(document).on('click', '#accum-next', function(){  
        let totalRows = accumDataAll.filter(function(d){  
            let val = (accumTableParams.search||'').trim().toLowerCase();  
            if(!val) return true;  
            return (  
                d.SUPPLIER_CODE.toLowerCase().includes(val) ||  
                d.SUPPLIER_NAME.toLowerCase().includes(val) ||  
                d.PART_NO.toLowerCase().includes(val) ||  
                d.PART_NAME.toLowerCase().includes(val)  
            );  
        }).length;  
        let totalPages = Math.ceil(totalRows/accumTableParams.pageSize) || 1;  
        if(accumTableParams.page < totalPages) {  
            accumTableParams.page++;  
            renderAccumTable();  
        }  
    });  
    
    $(document).on('change','#select-month',function(){  
        let selected = $(this).val();  
        loadAccumTable(selected);  
    });  

    $('#btn-download-accum-excel').on('click', downloadAccumExcel);
    
    // Handle AJAX errors globally
    $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
        if (jqxhr.status === 0 || jqxhr.statusText === 'error') {
            console.warn('🌐 Network error detected');
            
            if (!$('#networkErrorModal').length) {
                Swal.fire({
                    title: 'Connection Issue',
                    html: `
                        <div class="text-center">
                            <i class="bi bi-wifi-off text-warning" style="font-size: 3rem;"></i>
                            <p class="mt-3">Network connection issue detected.</p>
                            <p class="text-muted small">Please check your internet connection.</p>
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonText: 'Try Again',
                    confirmButtonColor: '#0066cc',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            }
        }
    });
    
    // Load initial data
    setTimeout(() => {
        console.log("📥 Loading initial data...");
        refreshActualMaps();
        fetchDataInformation();
        loadTableDetailProgress();
        
        setTimeout(() => {
            hideLoader();
            console.log('✅ All initial data loaded');
        }, 1500);
    }, 1000);
    
    // Auto-check new info every 30 seconds
    setInterval(function() {
        if (!$('.modal-add-information').is(':visible') && 
            !$('.modal-update-information-from').is(':visible') && 
            !$('.modal-update-information-to').is(':visible')) {
            
            $.ajax({
                url: 'api/check_new_info.php',
                type: 'GET',
                success: function(response) {
                    console.log('🔔 Notif check:', response);
                    if (response.success && (response.assigned_to_me > 0 || response.urgent_count > 0)) {
                        // Play sound notification
                        try {
                            var audio = new Audio('assets/sound/notification.mp3');
                            audio.volume = 0.3;
                            audio.play().catch(e => console.log("Audio error:", e));
                        } catch (e) {
                            console.log("Audio not supported");
                        }
                        
                        // Refresh information table if there are urgent items
                        if (response.urgent_count > 0) {
                            console.log('🔄 Refreshing info table due to new urgent items');
                            fetchDataInformation();
                        }
                        
                        // Update notification badge
                        if (typeof updateNotificationBadge === 'function') {
                            updateNotificationBadge(response.count);
                        }
                    }
                }
            });
        }
    }, 30000); // 30 seconds

    // Function to update notification badge
    function updateNotificationBadge(count) {
        const $badge = $('#notificationBadge');
        if (count > 0) {
            $badge.text(count).show().addClass('bg-danger');
        } else {
            $badge.hide();
        }
    }

    // Trigger notification check ketika ada update informasi
    $(document).ajaxSuccess(function(event, xhr, settings) {
        if (settings.url && (
            settings.url.includes('data_information.php') ||
            settings.url.includes('update_add_order.php') ||
            settings.url.includes('upload_data.php')
        )) {
            console.log('📡 Data updated, triggering notification check...');
            
            // Trigger notification check setelah 2 detik
            setTimeout(() => {
                if (window.notificationSystem && typeof window.notificationSystem.forceCheck === 'function') {
                    window.notificationSystem.forceCheck();
                }
            }, 2000);
        }
    });

    // Check notifications saat page menjadi visible
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            console.log('👀 Page visible, checking notifications...');
            if (window.notificationSystem) {
                window.notificationSystem.forceCheck();
            }
        }
    });

    // Initial check setelah semua load
    window.addEventListener('load', function() {
        console.log('🚀 Page fully loaded, initial notification check...');
        setTimeout(() => {
            if (window.notificationSystem) {
                window.notificationSystem.forceCheck();
            }
        }, 3000);
    });
});

// Fungsi untuk refresh modal yang sedang terbuka
function refreshOpenModals() {
    // Jika modal D/S terbuka, refresh data
    if ($('#modal-detail-ds').is(':visible')) {
        console.log('🔄 Refreshing D/S modal dengan filter baru');
        loadDSData();
    }
    
    // Jika modal N/S terbuka, refresh data
    if ($('#modal-detail-ns').is(':visible')) {
        console.log('🔄 Refreshing N/S modal dengan filter baru');
        loadNSData();
    }
    
    // Jika modal Accum terbuka, refresh data
    if ($('#modalByAccum').is(':visible')) {
        const currentMonth = $('#select-month').val();
        console.log('🔄 Refreshing Accum modal dengan filter baru');
        loadAccumTable(currentMonth);
    }
}