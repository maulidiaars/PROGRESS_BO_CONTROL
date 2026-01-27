<?php
// api/check_new_info.php - REVISI UNTUK SISTEM MINGGUAN
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/week_logic.php'; // Tambah ini

$response = [
    'success' => true,
    'count' => 0,
    'assigned_to_me' => 0,
    'urgent_count' => 0,
    'weekly_info_count' => 0,
    'timestamp' => time(),
    'week_info' => getCurrentWeekInfo(),
    'debug' => []
];

$currentUser = $_SESSION['name'] ?? '';

// Debug info
$response['debug']['session_user'] = $currentUser;
$response['debug']['server_time'] = date('Y-m-d H:i:s');
$response['debug']['week_info'] = getCurrentWeekInfo();

if (!$conn || !$currentUser) {
    $response['success'] = false;
    $response['error'] = 'No connection or user';
    echo json_encode($response);
    exit;
}

try {
    // ==================== REVISI UTAMA: FILTER PER MINGGU ====================
    $weekInfo = getCurrentWeekInfo();
    $weekStart = $weekInfo['start_date'];  // Senin
    $weekEnd = $weekInfo['end_date'];      // Minggu
    
    $response['debug']['week_range'] = [
        'start' => $weekStart,
        'end' => $weekEnd,
        'formatted' => $weekInfo['display_text']
    ];
    
    // ==================== HITUNG INFORMASI MINGGU INI ====================
    // QUERY: Hitung informasi dari minggu ini saja (Senin-Minggu)
    $sql = "
        SELECT 
            -- Count unread information for current user (HANYA DARI USER LAIN, HANYA MINGGU INI)
            SUM(CASE WHEN ti.PIC_TO LIKE '%' + ? + '%' 
                      AND ti.PIC_FROM != ?  -- FILTER: BUKAN DARI USER SENDIRI
                      AND (unr.read_at IS NULL OR unr.id IS NULL) 
                      AND ti.STATUS = 'Open'
                      AND ti.DATE >= ?  -- FILTER: MULAI SENIN
                      AND ti.DATE <= ?  -- FILTER: SAMPAI MINGGU
                THEN 1 ELSE 0 END) as unread_count,
            
            -- Count assigned to me (HANYA DARI USER LAIN, HANYA MINGGU INI)
            SUM(CASE WHEN ti.PIC_TO LIKE '%' + ? + '%' 
                      AND ti.PIC_FROM != ?  -- FILTER: BUKAN DARI USER SENDIRI
                      AND ti.STATUS = 'Open'
                      AND ti.DATE >= ?
                      AND ti.DATE <= ?
                THEN 1 ELSE 0 END) as assigned_count,
            
            -- Count urgent (assigned to me and open, HANYA DARI USER LAIN, HANYA MINGGU INI)
            SUM(CASE WHEN ti.PIC_TO LIKE '%' + ? + '%' 
                      AND ti.PIC_FROM != ?  -- FILTER: BUKAN DARI USER SENDIRI
                      AND ti.STATUS = 'Open'
                      AND ti.DATE >= ?
                      AND ti.DATE <= ?
                THEN 1 ELSE 0 END) as urgent_count,
            
            -- Count total informasi untuk minggu ini (tanpa filter user)
            COUNT(CASE WHEN ti.DATE >= ? AND ti.DATE <= ? THEN 1 END) as weekly_total
            
        FROM T_INFORMATION ti
        LEFT JOIN user_notification_read unr ON ti.ID_INFORMATION = unr.notification_id 
            AND unr.user_id = ?
        WHERE ti.DATE >= ?  -- FILTER MULAI SENIN
        AND ti.DATE <= ?    -- FILTER SAMPAI MINGGU
        AND ti.STATUS = 'Open'
    ";
    
    $params = [
        $currentUser, $currentUser, $weekStart, $weekEnd,  // unread_count
        $currentUser, $currentUser, $weekStart, $weekEnd,  // assigned_count
        $currentUser, $currentUser, $weekStart, $weekEnd,  // urgent_count
        $weekStart, $weekEnd,  // weekly_total
        $currentUser,  // user_id untuk LEFT JOIN
        $weekStart, $weekEnd  // WHERE DATE >= AND DATE <=
    ];
    
    $response['debug']['sql_params'] = $params;
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt) {
        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $response['count'] = (int)$row['unread_count'] ?? 0;
            $response['assigned_to_me'] = (int)$row['assigned_count'] ?? 0;
            $response['urgent_count'] = (int)$row['urgent_count'] ?? 0;
            $response['weekly_info_count'] = (int)$row['weekly_total'] ?? 0;
            
            $response['debug']['query_results'] = [
                'unread_count' => $row['unread_count'],
                'assigned_count' => $row['assigned_count'],
                'urgent_count' => $row['urgent_count'],
                'weekly_total' => $row['weekly_total']
            ];
        }
        sqlsrv_free_stmt($stmt);
    } else {
        $response['debug']['sql_error'] = sqlsrv_errors();
    }
    
    // ==================== CEK APAKAH PERLU RESET MINGGUAN ====================
    // Reset jika hari Senin dan belum direset
    if (isMonday() && !($_SESSION['weekly_reset_done'] ?? false)) {
        $resetInfo = resetWeeklyView();
        $response['weekly_reset'] = $resetInfo;
        $response['debug']['weekly_reset_done'] = true;
    }
    
    // ==================== DELAY NOTIFICATIONS ====================
    // Hanya untuk supervisor
    $supervisors = ['ALBERTO', 'EKO', 'EKA', 'MURSID', 'SATRIO'];
    
    if (in_array($currentUser, $supervisors)) {
        $today = date('Ymd');
        $sql_delay = "
            SELECT COUNT(DISTINCT CONCAT(o.PART_NO, '_', o.SUPPLIER_CODE)) as delay_count
            FROM T_ORDER o
            INNER JOIN M_PART_NO mp ON o.PART_NO = mp.PART_NO
            WHERE mp.PIC_ORDER = ?
              AND o.DELV_DATE = ?
              AND o.ORD_QTY > 0
              AND NOT EXISTS (
                SELECT 1 FROM T_UPDATE_BO ub
                WHERE ub.PART_NO = o.PART_NO
                  AND ub.DATE = o.DELV_DATE
                  AND ub.TRAN_QTY >= o.ORD_QTY
              )
              AND CONCAT('DELAY_', o.PART_NO, '_', o.SUPPLIER_CODE) NOT IN (
                SELECT notification_id 
                FROM user_notification_read 
                WHERE user_id = ? 
                AND read_at IS NOT NULL
              )
        ";
        
        $stmt_delay = sqlsrv_query($conn, $sql_delay, [$currentUser, $today, $currentUser]);
        
        if ($stmt_delay) {
            if ($row = sqlsrv_fetch_array($stmt_delay, SQLSRV_FETCH_ASSOC)) {
                $delayCount = (int)$row['delay_count'] ?? 0;
                $response['count'] += $delayCount;
                $response['urgent_count'] += $delayCount;
                $response['debug']['delay_count'] = $delayCount;
            }
            sqlsrv_free_stmt($stmt_delay);
        }
    }
    
    $response['debug']['final_counts'] = [
        'total_count' => $response['count'],
        'assigned_to_me' => $response['assigned_to_me'],
        'urgent_count' => $response['urgent_count'],
        'weekly_info_count' => $response['weekly_info_count']
    ];

    echo json_encode($response);

} catch (Throwable $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
    $response['trace'] = $e->getTraceAsString();
    echo json_encode($response);
}

// Close connection
if ($conn) {
    sqlsrv_close($conn);
}
?>