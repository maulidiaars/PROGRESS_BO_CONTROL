<?php
/**
 * FUNGSI STATUS UNIVERSAL - TAMENG JAM 16:00 HANYA UNTUK HARI INI
 */

/**
 * Fungsi untuk validasi 3 field matching
 */
function isBODataValid($boSupplierCode, $boPartNo, $boPartDesc, $validOrders) {
    $boSupplierNorm = strtoupper(str_replace(" ", "", trim($boSupplierCode)));
    $boPartNoNorm = strtoupper(str_replace(" ", "", trim($boPartNo)));
    $boPartDescNorm = strtoupper(trim($boPartDesc));
    
    foreach ($validOrders as $orderData) {
        if ($boSupplierNorm !== $orderData['supplier_code']) {
            continue;
        }
        
        if ($boPartNoNorm !== $orderData['part_no']) {
            continue;
        }
        
        $orderPartName = $orderData['part_name'];
        if (empty($boPartDescNorm) || empty($orderPartName)) {
            continue;
        }
        
        $similarity = similar_text($boPartDescNorm, $orderPartName, $percent);
        
        if ($percent > 70 || 
            strpos($boPartDescNorm, $orderPartName) !== false || 
            strpos($orderPartName, $boPartDescNorm) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Fungsi untuk mendapatkan valid orders dari database
 */
function getValidOrdersForDate($conn, $date) {
    $validOrders = [];
    
    $sql = "SELECT 
        DISTINCT 
        TRIM(SUPPLIER_CODE) as SUPPLIER_CODE,
        TRIM(PART_NO) as PART_NO,
        TRIM(PART_NAME) as PART_NAME
    FROM T_ORDER 
    WHERE DELV_DATE = ? 
    AND SUPPLIER_CODE IS NOT NULL 
    AND SUPPLIER_CODE != ''
    AND PART_NO IS NOT NULL 
    AND PART_NO != ''
    AND PART_NAME IS NOT NULL";
    
    $stmt = sqlsrv_prepare($conn, $sql, [$date]);
    
    if ($stmt && sqlsrv_execute($stmt)) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $key = strtoupper(str_replace(" ", "", trim($row['SUPPLIER_CODE']))) . '|' .
                   strtoupper(str_replace(" ", "", trim($row['PART_NO']))) . '|' .
                   strtoupper(trim($row['PART_NAME']));
            
            $validOrders[$key] = [
                'supplier_code' => strtoupper(str_replace(" ", "", trim($row['SUPPLIER_CODE']))),
                'part_no' => strtoupper(str_replace(" ", "", trim($row['PART_NO']))),
                'part_name' => strtoupper(trim($row['PART_NAME']))
            ];
        }
        sqlsrv_free_stmt($stmt);
    }
    
    return $validOrders;
}

/**
 * Helper function: Cek apakah tanggal adalah hari ini
 */
function isToday($date) {
    $currentDate = date('Ymd');
    $orderDate = strval($date);
    
    // Format date ke Ymd jika perlu
    if (strlen($orderDate) === 8) {
        $formattedOrderDate = $orderDate;
    } else {
        $formattedOrderDate = date('Ymd', strtotime($orderDate));
    }
    
    return ($formattedOrderDate == $currentDate);
}

/**
 * Helper function: Cek apakah tanggal adalah hari kemarin
 */
function isYesterday($date) {
    $yesterday = date('Ymd', strtotime('-1 day'));
    $orderDate = strval($date);
    
    if (strlen($orderDate) === 8) {
        $formattedOrderDate = $orderDate;
    } else {
        $formattedOrderDate = date('Ymd', strtotime($orderDate));
    }
    
    return ($formattedOrderDate == $yesterday);
}

/**
 * Helper function: Cek apakah sudah melewati tameng jam 16:00
 */
function isPastTamengTime($currentHour = null) {
    if ($currentHour === null) {
        $currentHour = intval(date('H'));
    }
    
    return $currentHour >= 16;
}

/**
 * FUNGSI UTAMA: Calculate status - TAMENG JAM 16:00 HANYA UNTUK HARI INI
 */
function calculateOrderStatus($date, $eta, $dsOrder, $dsAdd, $nsOrder, $nsAdd, $dsActual, $nsActual, $currentHour = null) {
    if ($currentHour === null) {
        $currentHour = intval(date('H'));
    }
    
    $totalDSOrder = $dsOrder + $dsAdd;
    $totalNSOrder = $nsOrder + $nsAdd;
    $totalOrder = $totalDSOrder + $totalNSOrder;
    $totalIncoming = $dsActual + $nsActual;
    
    $orderDate = $date;
    
    // LOGGING untuk debug
    error_log("=== STATUS CALCULATION START ===");
    error_log("Date: $orderDate | Today: " . date('Ymd'));
    error_log("Is Today: " . (isToday($orderDate) ? 'YES' : 'NO'));
    error_log("Is Yesterday: " . (isYesterday($orderDate) ? 'YES' : 'NO'));
    error_log("ETA: $eta | Current Hour: $currentHour");
    error_log("DS Order: $dsOrder + $dsAdd = $totalDSOrder | DS Actual: $dsActual");
    error_log("NS Order: $nsOrder + $nsAdd = $totalNSOrder | NS Actual: $nsActual");
    error_log("Total Order: $totalOrder | Total Incoming: $totalIncoming");
    
    // ================= RULE 1: OVER =================
    // Jika incoming lebih dari order → OVER (langsung muncul)
    if ($totalIncoming > $totalOrder) {
        error_log("RESULT: OVER (Incoming > Order)");
        return 'OVER';
    }
    
    // ================= RULE 2: COMPLETED (OK) =================
    // Jika incoming sama dengan order → COMPLETED (langsung muncul)
    if ($totalIncoming == $totalOrder && $totalOrder > 0) {
        error_log("RESULT: OK/COMPLETED (Incoming = Order)");
        return 'OK';
    }
    
    // ================= RULE 3: TIDAK ADA ORDER SAMA SEKALI =================
    if ($totalOrder == 0 && $totalIncoming == 0) {
        error_log("RESULT: OK/COMPLETED (No orders at all)");
        return 'OK';
    }
    
    // ================= RULE 4: POTENTIAL DELAY =================
    // Jika incoming kurang dari order
    if ($totalIncoming < $totalOrder) {
        
        // ========== LOGIKA BARU: TAMENG HANYA UNTUK HARI INI ==========
        if (isToday($orderDate)) {
            error_log("Same day: YES");
            
            // TAMENG LOGIC: Sebelum jam 16:00 → ON_PROGRESS (UNTUK SEMUA SHIFT)
            if ($currentHour < 16) {
                error_log("Before 16:00 ($currentHour) → ON_PROGRESS (Tameng ON untuk hari ini)");
                return 'ON_PROGRESS';
            } else {
                error_log("After 16:00 ($currentHour) → DELAY (Tameng OFF untuk hari ini)");
                return 'DELAY';
            }
        } else {
            // BUKAN HARI INI → NO TAMENG SELAMANYA
            error_log("Different day → DELAY (No Tameng forever)");
            return 'DELAY';
        }
    }
    
    // ================= DEFAULT =================
    error_log("RESULT: ON_PROGRESS (Default)");
    return 'ON_PROGRESS';
}

/**
 * Fungsi khusus untuk hitung status D/S saja - TAMENG HANYA UNTUK HARI INI
 */
function calculateDSStatus($date, $eta, $dsOrder, $dsAdd, $dsActual, $currentHour = null) {
    if ($currentHour === null) {
        $currentHour = intval(date('H'));
    }
    
    $totalDSOrder = $dsOrder + $dsAdd;
    $orderDate = $date;
    
    // LOGGING
    error_log("=== DS STATUS CALCULATION (REVISI) ===");
    error_log("DS Order: $dsOrder + $dsAdd = $totalDSOrder | DS Actual: $dsActual");
    error_log("Date: $orderDate | Is Today: " . (isToday($orderDate) ? 'YES' : 'NO'));
    error_log("Current Hour: $currentHour");
    
    // 1. Jika tidak ada order DS sama sekali → OK (COMPLETED) - SAMA DENGAN NS
    if ($totalDSOrder == 0 && $dsActual == 0) {
        error_log("No DS order and no DS actual → OK (COMPLETED)");
        return 'OK';
    }
    
    // 2. Jika ada order DS (regular atau add) - SAMA DENGAN NS
    if ($totalDSOrder > 0) {
        // Sudah COMPLETED (incoming = order)
        if ($dsActual == $totalDSOrder) {
            error_log("DS Actual = Order → OK/COMPLETED");
            return 'OK';
        }
        
        // OVER (incoming > order)
        if ($dsActual > $totalDSOrder) {
            error_log("DS Actual > Order → OVER");
            return 'OVER';
        }
        
        // Masih kurang → cek LOGIKA BARU - SAMA DENGAN NS
        if ($dsActual < $totalDSOrder) {
            if (isToday($orderDate)) {
                if ($currentHour < 16) {
                    error_log("Today, Before 16:00 → ON_PROGRESS (Tameng hari ini untuk DS)");
                    return 'ON_PROGRESS';
                } else {
                    error_log("Today, After 16:00 → DELAY (Tameng OFF hari ini untuk DS)");
                    return 'DELAY';
                }
            } else {
                error_log("Different day → DELAY (No Tameng forever)");
                return 'DELAY';
            }
        }
    }
    
    // 3. PERUBAHAN PENTING: Jika ada order tapi actual = 0, tetap pakai logika tameng
    if ($totalDSOrder > 0 && $dsActual == 0) {
        if (isToday($orderDate)) {
            if ($currentHour < 16) {
                error_log("Today, Before 16:00, Order > 0, Actual = 0 → ON_PROGRESS");
                return 'ON_PROGRESS';
            } else {
                error_log("Today, After 16:00, Order > 0, Actual = 0 → DELAY");
                return 'DELAY';
            }
        } else {
            error_log("Different day, Order > 0, Actual = 0 → DELAY");
            return 'DELAY';
        }
    }
    
    // 4. Default
    error_log("Default case → ON_PROGRESS");
    return 'ON_PROGRESS';
}

/**
 * Fungsi khusus untuk hitung status N/S saja - TAMENG HANYA UNTUK HARI INI
 */
function calculateNSStatus($date, $eta, $nsOrder, $nsAdd, $nsActual, $currentHour = null) {
    if ($currentHour === null) {
        $currentHour = intval(date('H'));
    }
    
    $totalNSOrder = $nsOrder + $nsAdd;
    $orderDate = $date;
    
    // LOGGING
    error_log("=== NS STATUS CALCULATION ===");
    error_log("NS Order: $nsOrder + $nsAdd = $totalNSOrder | NS Actual: $nsActual");
    error_log("Date: $orderDate | Is Today: " . (isToday($orderDate) ? 'YES' : 'NO'));
    error_log("Current Hour: $currentHour");
    
    // 1. Jika tidak ada order NS sama sekali → OK (COMPLETED)
    if ($totalNSOrder == 0 && $nsActual == 0) {
        error_log("No NS order and no NS actual → OK (COMPLETED)");
        return 'OK';
    }
    
    // 2. Jika ada order NS (regular atau add)
    if ($totalNSOrder > 0) {
        // Sudah COMPLETED (incoming = order)
        if ($nsActual == $totalNSOrder) {
            error_log("NS Actual = Order → OK/COMPLETED");
            return 'OK';
        }
        
        // OVER (incoming > order)
        if ($nsActual > $totalNSOrder) {
            error_log("NS Actual > Order → OVER");
            return 'OVER';
        }
        
        // Masih kurang → cek LOGIKA BARU
        if ($nsActual < $totalNSOrder) {
            if (isToday($orderDate)) {
                if ($currentHour < 16) {
                    error_log("Today, Before 16:00 → ON_PROGRESS (Tameng hari ini untuk NS)");
                    return 'ON_PROGRESS';
                } else {
                    error_log("Today, After 16:00 → DELAY (Tameng OFF hari ini untuk NS)");
                    return 'DELAY';
                }
            } else {
                error_log("Different day → DELAY (No Tameng forever)");
                return 'DELAY';
            }
        }
    }
    
    // 3. Default
    error_log("Default case → ON_PROGRESS");
    return 'ON_PROGRESS';
}

/**
 * Helper function untuk cek apakah masih dalam shift yang sama
 */
function isStillInSameShift($eta, $currentHour) {
    $etaHour = 0;
    if (!empty($eta) && preg_match('/^(\d{1,2}):/', $eta, $matches)) {
        $etaHour = intval($matches[1]);
    }
    
    // Determine shift from ETA
    if (($etaHour >= 21 && $etaHour <= 23) || ($etaHour >= 0 && $etaHour <= 6)) {
        $shift = 'NS';
    } else {
        $shift = 'DS';
    }
    
    // Check if current hour is still in the same shift
    if ($shift === 'DS') {
        return ($currentHour >= 7 && $currentHour <= 20);
    } else {
        return (($currentHour >= 21 && $currentHour <= 23) || ($currentHour >= 0 && $currentHour <= 6));
    }
}
?>