
<?php
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
    'debug' => []
];

if (!isset($_SESSION['name'])) {
    $response['success'] = false;
    $response['error'] = 'Not authenticated';
    echo json_encode($response);
    exit;
}

$currentUser = $_SESSION['name'];
$today = date('Ymd');

$response['debug']['user'] = $currentUser;
$response['debug']['today'] = $today;

if (!$conn) {
    $response['success'] = false;
    $response['error'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

try {
    // ==================== QUERY YANG DIPERBAIKI ====================
    $sql = "
        SELECT 
            ti.ID_INFORMATION as id,
            'information' as type,
            CASE 
                WHEN ti.PIC_TO = ? AND ti.STATUS = 'Open' THEN 'URGENT: Assigned to You'
                WHEN ti.PIC_FROM = ? THEN 'Your Information'
                ELSE 'New Information'
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
                WHEN ti.STATUS = 'Open' THEN 'OPEN'
                WHEN ti.STATUS = 'Closed' THEN 'CLOSED'
                ELSE UPPER(ti.STATUS)
            END as status_text,
            CASE 
                WHEN ti.STATUS = 'Open' THEN 'danger'
                WHEN ti.STATUS = 'Closed' THEN 'success'
                ELSE 'info'
            END as badge_color,
            CONVERT(varchar(19), CAST(ti.DATE + ' ' + ti.TIME_FROM as datetime), 120) as datetime_full,
            -- Tambahan untuk logic display
            CASE 
                WHEN ti.PIC_TO = ? AND ti.STATUS = 'Open' THEN 'assigned_to_you'
                WHEN ti.PIC_FROM = ? THEN 'your_information'
                ELSE 'other_information'
            END as notification_type
        FROM T_INFORMATION ti
        WHERE ti.DATE = ?
        AND NOT EXISTS (
            SELECT 1 FROM user_notification_read unr 
            WHERE unr.notification_id = ti.ID_INFORMATION 
            AND unr.user_id = ? 
            AND unr.read_at IS NOT NULL
        )
        ORDER BY CAST(ti.DATE as int) DESC, ti.TIME_FROM DESC
    ";

    $params = [$currentUser, $currentUser, $currentUser, $currentUser, $today, $currentUser];
    
    $response['debug']['sql_params'] = $params;
    
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
            
            // Format message berdasarkan tipe notifikasi
            if ($row['notification_type'] === 'your_information') {
                // Untuk "Your Information", format: PIC FROM → PIC TO
                if ($row['to_user'] && $row['to_user'] != '') {
                    $row['display_message'] = $row['from_user'] . ' → ' . $row['to_user'];
                } else {
                    $row['display_message'] = $row['from_user'];
                }
            } else {
                // Untuk tipe lain, format normal
                $row['display_message'] = $row['message'];
            }
            
            $row['is_unread'] = 1;
            $unread_count++;
            $notifications[] = $row;
            
            $response['debug']['found_ids'][] = $row['id'];
        }
        sqlsrv_free_stmt($stmt);
    } else {
        $response['debug']['sql_error'] = sqlsrv_errors();
    }
    
    // ==================== DELAY NOTIFICATIONS ====================
    $supervisors = ['ALBERTO', 'EKO', 'EKA', 'MURSID', 'SATRIO'];
    
    if (in_array($currentUser, $supervisors)) {
        $sql_delay = "
            SELECT DISTINCT TOP 3
                CONCAT('DELAY_', o.PART_NO, '_', o.SUPPLIER_CODE) as id,
                'delay' as type,
                'Delivery Delay' as title,
                CONCAT(
                    'Part ', o.PART_NO, ' (', mp.PART_DESC, ') from ',
                    o.SUPPLIER_CODE, ' - Delivery today!'
                ) as message,
                o.DELV_DATE as DATE,
                CONVERT(varchar(5), GETDATE(), 108) as time,
                'System' as from_user,
                ? as to_user,
                'DELAY' as STATUS,
                'DELAY' as status_text,
                'danger' as badge_color,
                CONVERT(varchar(19), GETDATE(), 120) as datetime_full,
                'delay_notification' as notification_type,
                CONCAT(
                    'Part ', o.PART_NO, ' (', mp.PART_DESC, ') from ',
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
                
                $response['debug']['delay_ids'][] = $row['id'];
            }
            sqlsrv_free_stmt($stmt_delay);
        }
    }
    
    $response['notifications'] = $notifications;
    $response['unread_count'] = $unread_count;
    $response['debug']['total_notifications'] = count($notifications);
    
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