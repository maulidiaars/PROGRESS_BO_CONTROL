<?php
/**
 * FUNGSI STATUS UNIVERSAL - GABISA DIUBAH SENDIRI!
 * Logika harus sama di semua tempat: data_progress_by_pn.php, update-status.php, dll
 */

function calculateOrderStatus($orderDate, $eta, $dsReguler, $dsAdd, $nsReguler, $nsAdd, $dsActual, $nsActual) {
    // 1. Convert to integers
    $orderDateInt = intval($orderDate);
    $today = intval(date('Ymd'));
    $currentHour = intval(date('H'));
    
    // 2. Calculate totals
    $totalOrderDS = intval($dsReguler) + intval($dsAdd);
    $totalOrderNS = intval($nsReguler) + intval($nsAdd);
    $totalOrder = $totalOrderDS + $totalOrderNS;
    
    $totalActual = intval($dsActual) + intval($nsActual);
    
    // ========== LOGIKA UTAMA ==========
    
    // 1. OVER bisa terjadi kapan saja (lebih dari order)
    if ($totalActual > $totalOrder) {
        return 'OVER';
    }
    
    // 2. OK bisa terjadi kapan saja (sesuai atau lebih - tapi lebih sudah ditangani di OVER)
    if ($totalActual >= $totalOrder) {
        return 'OK';
    }
    
    // 3. Data LAMPAU (kemarin/sebelumnya) → DELAY
    if ($orderDateInt < $today) {
        return 'DELAY';
    }
    
    // 4. Data MASA DEPAN (besok/lusa) → ON_PROGRESS
    if ($orderDateInt > $today) {
        return 'ON_PROGRESS';
    }
    
    // 5. Data HARI INI → cek shift dan checkpoint
    if ($orderDateInt == $today) {
        // Parse ETA hour
        $etaHour = 0;
        if (!empty($eta) && preg_match('/^(\d{1,2}):/', $eta, $matches)) {
            $etaHour = intval($matches[1]);
        }
        
        // Tentukan shift berdasarkan ETA
        $shift = 'DS'; // default
        if (($etaHour >= 21 && $etaHour <= 23) || ($etaHour >= 0 && $etaHour <= 6)) {
            $shift = 'NS';
        } elseif ($etaHour >= 7 && $etaHour <= 20) {
            $shift = 'DS';
        }
        
        // DAY SHIFT logic
        if ($shift === 'DS') {
            // Checkpoint: 16:00
            if ($currentHour < 16) {
                return 'ON_PROGRESS'; // MASIH ADA TAMENG
            } else {
                return 'DELAY'; // TAMENG DICABUT!
            }
        }
        // NIGHT SHIFT logic  
        else {
            // Checkpoint: 04:00
            if ($currentHour < 4 || $currentHour >= 21) {
                return 'ON_PROGRESS'; // MASIH ADA TAMENG
            } else {
                return 'DELAY'; // TAMENG DICABUT!
            }
        }
    }
    
    // Default fallback
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