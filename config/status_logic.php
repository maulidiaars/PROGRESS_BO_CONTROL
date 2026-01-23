<?php
/**
 * FUNGSI STATUS UNIVERSAL - GABISA DIUBAH SENDIRI!
 * Logika harus sama di semua tempat: data_progress_by_pn.php, update-status.php, dll
 */

function calculateOrderStatus($date, $eta, $dsOrder, $dsAdd, $nsOrder, $nsAdd, $dsActual, $nsActual) {
    $totalOrder = $dsOrder + $dsAdd + $nsOrder + $nsAdd;
    $totalIncoming = $dsActual + $nsActual;
    
    $currentHour = date('H');
    $currentDate = date('Ymd');
    $orderDate = $date;
    
    // Parse ETA
    $etaHour = 0;
    if (!empty($eta) && strpos($eta, ':') !== false) {
        $etaHour = intval(explode(':', $eta)[0]);
    }
    
    // Tentukan shift
    $isDayShift = ($etaHour >= 7 && $etaHour <= 20);
    $isNightShift = ($etaHour >= 21 || $etaHour <= 6);
    
    // ===== STATUS YANG LANGSUNG SHOW (GA PAKE TAMENG) =====
    if ($totalIncoming > $totalOrder) {
        return 'OVER';  // Langsung show
    }
    
    if ($totalIncoming == $totalOrder) {
        return 'OK';    // Langsung show
    }
    
    // ===== JIKA MASIH KURANG (POTENTIAL DELAY) =====
    if ($totalIncoming < $totalOrder) {
        
        // DAY SHIFT logic
        if ($isDayShift && $currentDate == $orderDate) {
            if ($currentHour >= 16) {
                // Sudah checkpoint, TAMENG DILEPAS
                return 'DELAY';
            } else {
                // Masih sebelum checkpoint, TAMENG ON
                return 'ON_PROGRESS';
            }
        }
        
        // NIGHT SHIFT logic  
        if ($isNightShift) {
            $nextDay = date('Ymd', strtotime($orderDate . ' +1 day'));
            
            if ($currentDate == $nextDay && $currentHour >= 6) {
                // Sudah checkpoint besok pagi, TAMENG DILEPAS
                return 'DELAY';
            } else {
                // Masih malam atau belum jam 6, TAMENG ON
                return 'ON_PROGRESS';
            }
        }
    }
    
    // Fallback
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