<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/status_logic.php';

if (!$conn) {
    die(json_encode(['error' => 'Database connection failed']));
}

try {
    $currentHour = intval(date('H'));
    $today = date('Ymd');
    $yesterday = date('Ymd', strtotime('-1 day'));
    
    // Ambil semua order hari ini dan kemarin
    $sql = "SELECT 
        o.DELV_DATE,
        o.SUPPLIER_CODE,
        o.PART_NO,
        o.ETA,
        o.REGULER_DS,
        o.ADD_DS,
        o.REGULER_NS,
        o.ADD_NS,
        o.CURRENT_STATUS,
        ISNULL((
            SELECT MAX(ub.TRAN_QTY) 
            FROM T_UPDATE_BO ub 
            WHERE ub.PART_NO = o.PART_NO 
            AND ub.DATE = o.DELV_DATE
            AND ub.HOUR BETWEEN 7 AND 20
        ), 0) AS DS_ACTUAL,
        ISNULL((
            SELECT MAX(ub.TRAN_QTY) 
            FROM T_UPDATE_BO ub 
            WHERE ub.PART_NO = o.PART_NO 
            AND ub.DATE = o.DELV_DATE
            AND (ub.HOUR BETWEEN 21 AND 23 OR ub.HOUR BETWEEN 0 AND 6)
        ), 0) AS NS_ACTUAL
    FROM T_ORDER o
    WHERE (o.DELV_DATE = ? OR o.DELV_DATE = ?)
    AND o.CURRENT_STATUS NOT IN ('OK', 'OVER')
    GROUP BY o.DELV_DATE, o.SUPPLIER_CODE, o.PART_NO, o.ETA,
             o.REGULER_DS, o.ADD_DS, o.REGULER_NS, o.ADD_NS, o.CURRENT_STATUS";
    
    $stmt = sqlsrv_query($conn, $sql, [$today, $yesterday]);
    $updates = [];
    
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $orderDate = strval($row['DELV_DATE']);
        $isToday = isToday($orderDate);
        
        // ========== PAKE FUNGSI STATUS BARU ==========
        // Status total
        $newStatus = calculateOrderStatus(
            $row['DELV_DATE'],
            $row['ETA'],
            $row['REGULER_DS'],
            $row['ADD_DS'],
            $row['REGULER_NS'],
            $row['ADD_NS'],
            $row['DS_ACTUAL'],
            $row['NS_ACTUAL'],
            $currentHour
        );
        
        // Update jika status berubah
        if ($newStatus !== $row['CURRENT_STATUS']) {
            $updateSql = "UPDATE T_ORDER SET 
                         CURRENT_STATUS = ?,
                         LAST_STATUS_UPDATE = GETDATE()
                         WHERE DELV_DATE = ? 
                         AND PART_NO = ? 
                         AND SUPPLIER_CODE = ?";
            
            $updateParams = [$newStatus, $row['DELV_DATE'], $row['PART_NO'], $row['SUPPLIER_CODE']];
            sqlsrv_query($conn, $updateSql, $updateParams);
            
            $updates[] = [
                'part_no' => $row['PART_NO'],
                'date' => $orderDate,
                'is_today' => $isToday,
                'old_status' => $row['CURRENT_STATUS'],
                'new_status' => $newStatus
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Status auto-updated successfully',
        'updates' => $updates,
        'count' => count($updates),
        'timestamp' => date('Y-m-d H:i:s'),
        'current_hour' => $currentHour,
        'today' => $today,
        'yesterday' => $yesterday
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>