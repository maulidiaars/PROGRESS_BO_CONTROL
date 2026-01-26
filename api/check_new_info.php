<?php
// api/check_new_info.php - PERBAIKAN: TIDAK ADA NOTIF UNTUK USER SENDIRI
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../config/database.php';

$response = [
    'success' => true,
    'count' => 0,
    'assigned_to_me' => 0,
    'urgent_count' => 0,
    'timestamp' => time(),
    'debug' => []
];

$currentUser = $_SESSION['name'] ?? '';

// Debug info
$response['debug']['session_user'] = $currentUser;
$response['debug']['check_id'] = $_GET['check_id'] ?? 'none';
$response['debug']['server_time'] = date('Y-m-d H:i:s');

if (!$conn || !$currentUser) {
    $response['success'] = false;
    $response['error'] = 'No connection or user';
    echo json_encode($response);
    exit;
}

try {
    $today = date('Ymd');
    $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
    
    $response['debug']['today'] = $today;
    $response['debug']['seven_days_ago'] = $sevenDaysAgo;
    
    // ==================== REVISI UTAMA: HANYA INFORMASI DARI USER LAIN ====================
    // QUERY OPTIMIZED: Hitung hanya informasi dari user lain (PIC_FROM != currentUser)
    $sql = "
        SELECT 
            -- Count unread information for current user (HANYA DARI USER LAIN)
            SUM(CASE WHEN ti.PIC_TO LIKE '%' + ? + '%' 
                      AND ti.PIC_FROM != ?  -- FILTER: BUKAN DARI USER SENDIRI
                      AND (unr.read_at IS NULL OR unr.id IS NULL) 
                      AND ti.STATUS = 'Open'
                      AND ti.DATE >= ?  -- FILTER 7 HARI TERAKHIR
                THEN 1 ELSE 0 END) as unread_count,
            
            -- Count assigned to me (HANYA DARI USER LAIN)
            SUM(CASE WHEN ti.PIC_TO LIKE '%' + ? + '%' 
                      AND ti.PIC_FROM != ?  -- FILTER: BUKAN DARI USER SENDIRI
                      AND ti.STATUS = 'Open'
                      AND ti.DATE >= ?
                THEN 1 ELSE 0 END) as assigned_count,
            
            -- Count urgent (assigned to me and open, HANYA DARI USER LAIN)
            SUM(CASE WHEN ti.PIC_TO LIKE '%' + ? + '%' 
                      AND ti.PIC_FROM != ?  -- FILTER: BUKAN DARI USER SENDIRI
                      AND ti.STATUS = 'Open'
                      AND ti.DATE >= ?
                THEN 1 ELSE 0 END) as urgent_count
            
        FROM T_INFORMATION ti
        LEFT JOIN user_notification_read unr ON ti.ID_INFORMATION = unr.notification_id 
            AND unr.user_id = ?
        WHERE ti.DATE >= ?  -- FILTER 7 HARI TERAKHIR
        AND ti.STATUS = 'Open'
    ";
    
    $params = [
        $currentUser, $currentUser, $sevenDaysAgo,  // unread_count
        $currentUser, $currentUser, $sevenDaysAgo,  // assigned_count
        $currentUser, $currentUser, $sevenDaysAgo,  // urgent_count
        $currentUser,  // user_id untuk LEFT JOIN
        $sevenDaysAgo  // WHERE DATE >=
    ];
    
    $response['debug']['sql_params'] = $params;
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt) {
        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $response['count'] = (int)$row['unread_count'] ?? 0;
            $response['assigned_to_me'] = (int)$row['assigned_count'] ?? 0;
            $response['urgent_count'] = (int)$row['urgent_count'] ?? 0;
            
            $response['debug']['query_results'] = [
                'unread_count' => $row['unread_count'],
                'assigned_count' => $row['assigned_count'],
                'urgent_count' => $row['urgent_count']
            ];
        }
        sqlsrv_free_stmt($stmt);
    } else {
        $response['debug']['sql_error'] = sqlsrv_errors();
    }
    
    // ==================== DELAY NOTIFICATIONS ====================
    // Hanya untuk supervisor
    $supervisors = ['ALBERTO', 'EKO', 'EKA', 'MURSID', 'SATRIO'];
    
    if (in_array($currentUser, $supervisors)) {
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
        'urgent_count' => $response['urgent_count']
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