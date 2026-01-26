<?php
// api/check_new_info.php - SUPER OPTIMIZED VERSION
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Accel-Buffering: no');

require_once __DIR__ . '/../config/database.php';

$response = [
    'success' => true,
    'count' => 0,
    'assigned_to_me' => 0,
    'urgent_count' => 0,
    'timestamp' => time(),
    'last_id' => null,
    'performance' => [
        'query_time' => 0,
        'total_time' => 0,
        'memory_usage' => 0
    ]
];

$currentUser = $_SESSION['name'] ?? '';
$checkId = $_GET['check_id'] ?? 'none';
$lastId = $_GET['last_id'] ?? 0;
$isDeepCheck = isset($_GET['deep']) && $_GET['deep'] == 1;

$startTime = microtime(true);
$memoryStart = memory_get_usage();

// Debug info hanya untuk development
$isDebug = isset($_GET['debug']) && $_GET['debug'] == 1;

if ($isDebug) {
    $response['debug'] = [
        'session_user' => $currentUser,
        'check_id' => $checkId,
        'last_id' => $lastId,
        'server_time' => date('Y-m-d H:i:s'),
        'is_deep' => $isDeepCheck,
        'php_memory_limit' => ini_get('memory_limit')
    ];
}

if (!$conn || !$currentUser) {
    $response['success'] = false;
    $response['error'] = 'No connection or user';
    $response['performance']['total_time'] = round(microtime(true) - $startTime, 4);
    $response['performance']['memory_usage'] = memory_get_usage() - $memoryStart;
    echo json_encode($response);
    exit;
}

try {
    $today = date('Ymd');
    $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
    
    // OPTIMIZED QUERY: Gunakan EXISTS dan subquery yang lebih cepat
    $sql = "
        SELECT 
            ti.ID_INFORMATION as id,
            ti.DATE,
            ti.STATUS,
            ti.PIC_FROM,
            ti.PIC_TO
        FROM T_INFORMATION ti
        WHERE ti.DATE >= ?
        AND ti.STATUS = 'Open'
        AND ti.PIC_FROM != ?
        AND EXISTS (
            SELECT 1 
            FROM user_notification_read unr 
            WHERE unr.notification_id = ti.ID_INFORMATION 
            AND unr.user_id = ?
            AND unr.read_at IS NULL
        )
        AND (
            ti.PIC_TO LIKE '%' + ? + '%'
        )
        " . ($lastId > 0 ? "AND ti.ID_INFORMATION > ?" : "") . "
        ORDER BY ti.ID_INFORMATION DESC
    ";
    
    $params = [
        $sevenDaysAgo,
        $currentUser,
        $currentUser,
        $currentUser
    ];
    
    if ($lastId > 0) {
        $params[] = $lastId;
    }
    
    $queryStart = microtime(true);
    $stmt = sqlsrv_query($conn, $sql, $params, array("Scrollable" => SQLSRV_CURSOR_STATIC));
    $response['performance']['query_time'] = round(microtime(true) - $queryStart, 4);
    
    $unreadCount = 0;
    $assignedCount = 0;
    $urgentCount = 0;
    $latestId = $lastId;
    
    if ($stmt) {
        // Process in batches untuk hindari memory overflow
        $batchSize = 100;
        $processed = 0;
        
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $processed++;
            
            // Safety limit
            if ($processed > 1000) {
                break;
            }
            
            $unreadCount++;
            
            // Check if assigned to me
            if (strpos($row['PIC_TO'], $currentUser) !== false) {
                $assignedCount++;
                $urgentCount++;
            }
            
            // Track latest ID
            if ($row['id'] > $latestId) {
                $latestId = $row['id'];
            }
            
            // Process in batches untuk hindari memory overflow
            if ($processed % $batchSize === 0) {
                sqlsrv_next_result($stmt);
            }
        }
        
        sqlsrv_free_stmt($stmt);
        
        $response['count'] = $unreadCount;
        $response['assigned_to_me'] = $assignedCount;
        $response['urgent_count'] = $urgentCount;
        $response['last_id'] = $latestId;
        
        if ($isDebug) {
            $response['debug']['processed_rows'] = $processed;
        }
    } else {
        if ($isDebug) {
            $response['debug']['sql_error'] = sqlsrv_errors();
        }
    }
    
    // ==================== DELAY NOTIFICATIONS ====================
    $supervisors = ['ALBERTO', 'EKO', 'EKA', 'MURSID', 'SATRIO'];
    $currentHour = date('H');
    
    if (in_array($currentUser, $supervisors) && $currentHour >= 8 && $currentHour <= 17) {
        // Optimized delay query dengan EXISTS
        $sql_delay = "
            SELECT TOP 10 o.PART_NO, o.SUPPLIER_CODE
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
              AND NOT EXISTS (
                SELECT 1 FROM user_notification_read unr
                WHERE unr.user_id = ?
                AND unr.notification_id = CONCAT('DELAY_', o.PART_NO, '_', o.SUPPLIER_CODE)
                AND unr.read_at IS NOT NULL
              )
        ";
        
        $stmt_delay = sqlsrv_query($conn, $sql_delay, [$currentUser, $today, $currentUser]);
        
        if ($stmt_delay) {
            $delayCount = 0;
            while (sqlsrv_fetch($stmt_delay)) {
                $delayCount++;
            }
            
            if ($delayCount > 0) {
                $response['count'] += $delayCount;
                $response['urgent_count'] += $delayCount;
                
                if ($isDebug) {
                    $response['debug']['delay_count'] = $delayCount;
                }
            }
            sqlsrv_free_stmt($stmt_delay);
        }
    }
    
    $response['performance']['total_time'] = round(microtime(true) - $startTime, 4);
    $response['performance']['memory_usage'] = memory_get_usage() - $memoryStart;
    
    if ($isDebug) {
        $response['debug']['final_counts'] = [
            'total_count' => $response['count'],
            'assigned_to_me' => $response['assigned_to_me'],
            'urgent_count' => $response['urgent_count']
        ];
        $response['debug']['performance'] = $response['performance'];
    }

    echo json_encode($response);

} catch (Throwable $e) {
    $response['success'] = false;
    $response['error'] = 'Server error';
    $response['performance']['total_time'] = round(microtime(true) - $startTime, 4);
    $response['performance']['memory_usage'] = memory_get_usage() - $memoryStart;
    
    if ($isDebug) {
        $response['debug_error'] = $e->getMessage();
    }
    
    echo json_encode($response);
}

// Clean up connections
if (isset($stmt) && $stmt) sqlsrv_free_stmt($stmt);
if (isset($stmt_delay) && $stmt_delay) sqlsrv_free_stmt($stmt_delay);
if ($conn) sqlsrv_close($conn);
?>