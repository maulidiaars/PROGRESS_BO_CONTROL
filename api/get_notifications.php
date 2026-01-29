<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/week_logic.php';

$response = [
    'success' => true,
    'notifications' => [],
    'unread_count' => 0,
    'weekly_info_count' => 0,
    'week_info' => getCurrentWeekInfo(),
    'debug' => []
];

if (!isset($_SESSION['name'])) {
    $response['success'] = false;
    $response['error'] = 'Not authenticated';
    echo json_encode($response);
    exit;
}

$currentUser = $_SESSION['name'];
$weekInfo = getCurrentWeekInfo();
$weekStart = $weekInfo['start_date'];
$weekEnd = $weekInfo['end_date'];

$response['debug']['user'] = $currentUser;
$response['debug']['week_info'] = $weekInfo;

if (!$conn) {
    $response['success'] = false;
    $response['error'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

try {
    // ==================== REVISI SIMPLE: SEMUA USER BISA LIHAT ====================
    $sql = "
        SELECT 
            ti.ID_INFORMATION as id,
            'information' as type,
            -- TITLE BERBEDA BERDASARKAN USER
            CASE 
                WHEN ti.PIC_TO LIKE '%' + ? + '%' THEN 'DITUGASKAN UNTUK ANDA'
                ELSE 'INFORMASI BARU'
            END as title,
            -- MESSAGE BERBEDA BERDASARKAN USER
            CASE 
                WHEN ti.PIC_TO LIKE '%' + ? + '%' THEN 
                    CONCAT('Anda ditugaskan: ', ti.ITEM, 
                           CASE WHEN LEN(ti.REQUEST) > 0 THEN ' - ' + LEFT(ti.REQUEST, 100) ELSE '' END)
                ELSE 
                    CONCAT(ti.PIC_FROM, ' → ', ti.PIC_TO, ': ', ti.ITEM)
            END as message,
            ti.DATE,
            ti.TIME_FROM as time,
            ti.PIC_FROM as from_user,
            ti.PIC_TO as to_user,
            ti.STATUS,
            CASE 
                WHEN ti.STATUS = 'Open' THEN 'BUKA'
                WHEN ti.STATUS = 'On Progress' THEN 'SEDANG DIPROSES'
                WHEN ti.STATUS = 'Closed' THEN 'SELESAI'
                ELSE UPPER(ti.STATUS)
            END as status_text,
            CASE 
                WHEN ti.PIC_TO LIKE '%' + ? + '%' AND ti.STATUS = 'Open' THEN 'danger'
                WHEN ti.STATUS = 'Open' THEN 'warning'
                WHEN ti.STATUS = 'On Progress' THEN 'primary'
                WHEN ti.STATUS = 'Closed' THEN 'success'
                ELSE 'info'
            END as badge_color,
            CONVERT(varchar(19), CAST(ti.DATE + ' ' + ti.TIME_FROM as datetime), 120) as datetime_full,
            -- FLAG: apakah user ini penerima?
            CASE 
                WHEN ti.PIC_TO LIKE '%' + ? + '%' THEN 'recipient'
                ELSE 'viewer'
            END as user_role,
            -- FLAG: apakah bisa reply? (hanya penerima dan status Open)
            CASE 
                WHEN ti.PIC_TO LIKE '%' + ? + '%' AND ti.STATUS = 'Open' THEN 1
                ELSE 0
            END as can_reply
            
        FROM T_INFORMATION ti
        WHERE ti.DATE >= ?  -- FILTER: MULAI SENIN MINGGU INI
        AND ti.DATE <= ?    -- FILTER: SAMPAI MINGGU MINGGU INI
        AND ti.PIC_FROM != ?  -- FILTER: BUKAN DARI USER SENDIRI
        AND NOT EXISTS (
            SELECT 1 FROM user_notification_read unr 
            WHERE unr.notification_id = ti.ID_INFORMATION 
            AND unr.user_id = ? 
            AND unr.read_at IS NOT NULL
        )
        ORDER BY CAST(ti.DATE as int) DESC, ti.TIME_FROM DESC
    ";

    $params = [
        $currentUser,  // title (cek penerima)
        $currentUser,  // message (cek penerima)  
        $currentUser,  // badge_color
        $currentUser,  // user_role
        $currentUser,  // can_reply
        $weekStart, $weekEnd,        // WHERE DATE
        $currentUser,                // AND PIC_FROM != currentUser
        $currentUser                 // NOT EXISTS user_notification_read
    ];
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    $notifications = [];
    $unread_count = 0;
    $weekly_total = 0;
    
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
            
            // Filter: Hanya notifikasi dari minggu ini
            if ($row['DATE'] < $weekStart || $row['DATE'] > $weekEnd) {
                continue; // SKIP notifikasi dari minggu sebelumnya
            }
            
            // Set display message
            $row['display_message'] = $row['message'];
            
            // Set unread status
            $row['is_unread'] = 1;
            
            $unread_count++;
            $weekly_total++;
            $notifications[] = $row;
        }
        sqlsrv_free_stmt($stmt);
    } else {
        $response['debug']['sql_error'] = sqlsrv_errors();
    }
    
    // ==================== DELAY NOTIFICATIONS ====================
    $supervisors = ['ALBERTO', 'EKO', 'EKA', 'MURSID', 'SATRIO'];
    
    if (in_array($currentUser, $supervisors)) {
        $today = date('Ymd');
        $sql_delay = "
            SELECT DISTINCT TOP 3
                CONCAT('DELAY_', o.PART_NO, '_', o.SUPPLIER_CODE) as id,
                'delay' as type,
                '⏰ KETERLAMBATAN PENGIRIMAN' as title,
                CONCAT(
                    'Part ', o.PART_NO, ' dari ', o.SUPPLIER_CODE
                ) as message,
                o.DELV_DATE as DATE,
                CONVERT(varchar(5), GETDATE(), 108) as time,
                'System' as from_user,
                ? as to_user,
                'DELAY' as STATUS,
                'TERLAMBAT' as status_text,
                'danger' as badge_color,
                CONVERT(varchar(19), GETDATE(), 120) as datetime_full,
                'viewer' as user_role,
                0 as can_reply
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
                $row['display_message'] = $row['message'];
                
                $unread_count++;
                $notifications[] = $row;
            }
            sqlsrv_free_stmt($stmt_delay);
        }
    }
    
    $response['notifications'] = $notifications;
    $response['unread_count'] = $unread_count;
    $response['weekly_info_count'] = $weekly_total;
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