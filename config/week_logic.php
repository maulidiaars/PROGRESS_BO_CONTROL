<?php
// config/week_logic.php
/**
 * Fungsi untuk menentukan awal dan akhir minggu (Senin-Minggu)
 * berdasarkan tanggal tertentu
 */
function getWeekRange($date = null) {
    if (!$date) $date = date('Ymd');
    
    // Convert to timestamp
    $timestamp = strtotime($date);
    if (!$timestamp) $timestamp = time();
    
    // Find Monday of this week
    $monday = date('Ymd', strtotime('monday this week', $timestamp));
    
    // Find Sunday of this week
    $sunday = date('Ymd', strtotime('sunday this week', $timestamp));
    
    return [
        'start' => $monday, // Senin
        'end' => $sunday,   // Minggu
        'current_date' => date('Ymd', $timestamp),
        'week_number' => date('W', $timestamp),
        'year' => date('Y', $timestamp)
    ];
}

/**
 * Cek apakah tanggal termasuk dalam minggu saat ini
 */
function isInCurrentWeek($dateString) {
    $weekRange = getWeekRange();
    
    // Convert date to Ymd format
    if (strlen($dateString) === 8 && is_numeric($dateString)) {
        $dateFormatted = $dateString;
    } else {
        $dateFormatted = date('Ymd', strtotime($dateString));
    }
    
    return ($dateFormatted >= $weekRange['start'] && $dateFormatted <= $weekRange['end']);
}

/**
 * Cek apakah hari ini adalah Senin
 */
function isMonday() {
    return date('N') == 1; // 1 = Senin
}

/**
 * Reset tampilan data untuk minggu baru (hanya reset tampilan, bukan hapus data)
 */
function resetWeeklyView() {
    $_SESSION['current_week'] = date('W'); // Minggu ke berapa
    $_SESSION['weekly_reset_done'] = true;
    
    return [
        'week_number' => date('W'),
        'reset_date' => date('Y-m-d'),
        'message' => 'Tampilan direset untuk minggu baru'
    ];
}

/**
 * Get informasi minggu saat ini
 */
function getCurrentWeekInfo() {
    $weekRange = getWeekRange();
    
    return [
        'week_number' => date('W'),
        'start_date' => $weekRange['start'],
        'end_date' => $weekRange['end'],
        'start_formatted' => date('Y-m-d', strtotime($weekRange['start'])),
        'end_formatted' => date('Y-m-d', strtotime($weekRange['end'])),
        'display_text' => 'Minggu ' . date('W') . ' (' . 
                         date('d M', strtotime($weekRange['start'])) . ' - ' . 
                         date('d M Y', strtotime($weekRange['end'])) . ')'
    ];
}

// Session initialization untuk tracking mingguan
if (!isset($_SESSION['current_week'])) {
    $_SESSION['current_week'] = date('W');
    $_SESSION['weekly_reset_done'] = false;
}

// Auto-reset jika hari Senin dan belum direset
if (isMonday() && !$_SESSION['weekly_reset_done']) {
    resetWeeklyView();
}
?>