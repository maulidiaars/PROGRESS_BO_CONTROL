<?php
// api/get_notifications.php - OPTIMIZED VERSION
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../config/database.php';

$response = [
    'success' => true,
    'notifications' => [],
    'unread_count' => 0,
    'has_more' => false,
    'timestamp' => time()
];

if (!isset($_SESSION['name'])) {
    $response['success'] = false;
    $response['error'] = 'Not authenticated';
    echo json_encode($response);
    exit;
}

$currentUser = $_SESSION['name'];
$today = date('Ymd');
$sevenDaysAgo = date('Ymd', strtotime('-7 days'));
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

if (!$conn) {
    $response['success'] = false;
    $response['error'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

try {
    // ==================== OPTIMIZED QUERY WITH LIMIT ====================
    $sql = "
        SELECT TOP {$limit}
            ti.ID_INFORMATION as id,
            'information' as type,
            CASE 
                WHEN ti.PIC_TO LIKE '%' + ? + '%' AND ti.STATUS = 'Open' THEN 'PENTING: Ditugaskan ke Anda'
                WHEN ti.PIC_FROM = ? THEN 'Informasi Anda'
                ELSE 'Informasi Baru'
            END as title,
            CASE 
                WHEN ti.PIC_TO IS NOT NULL AND ti.PIC_TO != '' THEN 
                    CONCAT(
                        ti.ITEM,
                        CASE WHEN LEN(ti.REQUEST) > 0 THEN ': ' + LEFT(ti.REQUEST, 100) ELSE '' END,
                        ' → ',
                        ti.PIC_TO
                    )
                ELSE 
                    CONCAT(
                        ti.ITEM,
                        CASE WHEN LEN(ti.REQUEST) > 0 THEN ': ' + LEFT(ti.REQUEST, 100) ELSE '' END
                    )
            END as message,
            ti.DATE,
            ti.TIME_FROM as time,
            ti.PIC_FROM as from_user,
            ti.PIC_TO as to_user,
            ti.STATUS,
            CASE 
                WHEN ti.STATUS = 'Open' THEN 'BUKA'
                WHEN ti.STATUS = 'Closed' THEN 'SELESAI'
                ELSE UPPER(ti.STATUS)
            END as status_text,
            CASE 
                WHEN ti.STATUS = 'Open' THEN 'danger'
                WHEN ti.STATUS = 'Closed' THEN 'success'
                ELSE 'info'
            END as badge_color,
            CONVERT(varchar(19), CAST(ti.DATE + ' ' + ti.TIME_FROM as datetime), 120) as datetime_full,
            CASE 
                WHEN ti.PIC_TO LIKE '%' + ? + '%' AND ti.STATUS = 'Open' THEN 'assigned_to_you'
                WHEN ti.PIC_FROM = ? THEN 'your_information'
                ELSE 'other_information'
            END as notification_type
        FROM T_INFORMATION ti
        WHERE ti.DATE >= ?
        AND ti.PIC_FROM != ?  -- FILTER: NOT FROM SELF
        AND NOT EXISTS (
            SELECT 1 FROM user_notification_read unr 
            WHERE unr.notification_id = ti.ID_INFORMATION 
            AND unr.user_id = ? 
            AND unr.read_at IS NOT NULL
        )
        AND (
            ti.PIC_TO LIKE '%' + ? + '%'
            OR ti.PIC_FROM = ?
        )
        ORDER BY CAST(ti.DATE as int) DESC, ti.TIME_FROM DESC
    ";

    $params = [
        $currentUser, $currentUser,  // title
        $currentUser, $currentUser,  // notification_type
        $sevenDaysAgo,  // WHERE DATE >=
        $currentUser,   // AND PIC_FROM != currentUser
        $currentUser,   // NOT EXISTS user_notification_read
        $currentUser,   // PIC_TO LIKE currentUser
        $currentUser    // PIC_FROM = currentUser
    ];
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    $notifications = [];
    $unread_count = 0;
    
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Format date
            if (!empty($row['DATE']) && is_numeric($row['DATE'])) {
                $d = (string)$row['DATE'];
                if (strlen($d) === 8) {
                    $row['date_formatted'] = 
                        substr($d, 0, 4) . '-' .
                        substr($d, 4, 2) . '-' .
                        substr($d, 6, 2);
                }
            }
            
            // Skip notifications from self
            if ($row['notification_type'] === 'your_information') {
                continue;
            }
            
            // Format message based on notification type
            if ($row['notification_type'] === 'assigned_to_you') {
                $row['display_message'] = 'Ditugaskan kepada Anda: ' . $row['ITEM'];
            } else {
                $row['display_message'] = $row['message'];
            }
            
            $row['is_unread'] = 1;
            $unread_count++;
            $notifications[] = $row;
        }
        sqlsrv_free_stmt($stmt);
    }
    
    // ==================== DELAY NOTIFICATIONS ====================
    $supervisors = ['ALBERTO', 'EKO', 'EKA', 'MURSID', 'SATRIO'];
    
    if (in_array($currentUser, $supervisors)) {
        $sql_delay = "
            SELECT DISTINCT TOP 5
                CONCAT('DELAY_', o.PART_NO, '_', o.SUPPLIER_CODE) as id,
                'delay' as type,
                'Keterlambatan Pengiriman' as title,
                CONCAT(
                    'Part ', o.PART_NO, ' (', mp.PART_DESC, ') dari ',
                    o.SUPPLIER_CODE, ' - Pengiriman hari ini!'
                ) as message,
                o.DELV_DATE as DATE,
                CONVERT(varchar(5), GETDATE(), 108) as time,
                'System' as from_user,
                ? as to_user,
                'DELAY' as STATUS,
                'TERLAMBAT' as status_text,
                'danger' as badge_color,
                CONVERT(varchar(19), GETDATE(), 120) as datetime_full,
                'delay_notification' as notification_type,
                CONCAT(
                    'Part ', o.PART_NO, ' (', mp.PART_DESC, ') dari ',
                    o.SUPPLIER_CODE
                ) as display_message
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
        
        $stmt_delay = sqlsrv_query(
            $conn,
            $sql_delay,
            [$currentUser, $currentUser, $today, $currentUser]
        );
        
        if ($stmt_delay) {
            while ($row = sqlsrv_fetch_array($stmt_delay, SQLSRV_FETCH_ASSOC)) {
                $row['date_formatted'] = date('Y-m-d');
                $row['is_unread'] = 1;
                $unread_count++;
                $notifications[] = $row;
            }
            sqlsrv_free_stmt($stmt_delay);
        }
    }
    
    // Sort notifications by date (newest first)
    usort($notifications, function($a, $b) {
        return strtotime($b['datetime_full']) - strtotime($a['datetime_full']);
    });
    
    // Apply offset if needed
    if ($offset > 0 && $offset < count($notifications)) {
        $notifications = array_slice($notifications, $offset);
    }
    
    // Check if there are more notifications
    $total_count_sql = "
        SELECT COUNT(*) as total
        FROM T_INFORMATION ti
        WHERE ti.DATE >= ?
        AND ti.PIC_FROM != ?
        AND NOT EXISTS (
            SELECT 1 FROM user_notification_read unr 
            WHERE unr.notification_id = ti.ID_INFORMATION 
            AND unr.user_id = ? 
            AND unr.read_at IS NOT NULL
        )
        AND (
            ti.PIC_TO LIKE '%' + ? + '%'
            OR ti.PIC_FROM = ?
        )
    ";
    
    $total_stmt = sqlsrv_query($conn, $total_count_sql, [
        $sevenDaysAgo, $currentUser, $currentUser, $currentUser, $currentUser
    ]);
    
    $total_count = 0;
    if ($total_stmt) {
        if ($row = sqlsrv_fetch_array($total_stmt, SQLSRV_FETCH_ASSOC)) {
            $total_count = (int)$row['total'];
        }
        sqlsrv_free_stmt($total_stmt);
    }
    
    $response['notifications'] = $notifications;
    $response['unread_count'] = $unread_count;
    $response['has_more'] = ($offset + count($notifications)) < $total_count;
    $response['total_count'] = $total_count;
    $response['offset'] = $offset;
    $response['limit'] = $limit;
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
    echo json_encode($response);
}

if ($conn) {
    sqlsrv_close($conn);
}
?>