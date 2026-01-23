<?php
// api/check_new_info.php - OPTIMIZED VERSION
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
    'timestamp' => time()
];

$currentUser = $_SESSION['name'] ?? '';

if (!$conn || !$currentUser) {
    $response['success'] = false;
    echo json_encode($response);
    exit;
}

try {
    $today = date('Ymd');
    
    // QUERY OPTIMIZED: Gunakan single query untuk semua hitungan
    $sql = "
        SELECT 
            -- Count unread information for current user
            SUM(CASE WHEN ti.PIC_TO LIKE '%' + ? + '%' 
                      AND (unr.read_at IS NULL OR unr.id IS NULL) 
                      AND ti.STATUS = 'Open'
                THEN 1 ELSE 0 END) as unread_count,
            
            -- Count assigned to me
            SUM(CASE WHEN ti.PIC_TO LIKE '%' + ? + '%' 
                      AND ti.STATUS = 'Open'
                THEN 1 ELSE 0 END) as assigned_count,
            
            -- Count urgent (assigned to me and open)
            SUM(CASE WHEN ti.PIC_TO LIKE '%' + ? + '%' 
                      AND ti.STATUS = 'Open'
                THEN 1 ELSE 0 END) as urgent_count
            
        FROM T_INFORMATION ti
        LEFT JOIN user_notification_read unr ON ti.ID_INFORMATION = unr.notification_id 
            AND unr.user_id = ?
        WHERE ti.DATE = ?
        AND ti.STATUS = 'Open'
    ";
    
    $params = [$currentUser, $currentUser, $currentUser, $currentUser, $today];
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt) {
        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $response['count'] = (int)$row['unread_count'] ?? 0;
            $response['assigned_to_me'] = (int)$row['assigned_count'] ?? 0;
            $response['urgent_count'] = (int)$row['urgent_count'] ?? 0;
        }
        sqlsrv_free_stmt($stmt);
    }
    
    // Also check for delay notifications for supervisors
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
            }
            sqlsrv_free_stmt($stmt_delay);
        }
    }
    
    echo json_encode($response);

} catch (Throwable $e) {
    error_log($e->getMessage());
    $response['success'] = false;
    echo json_encode($response);
}
?>