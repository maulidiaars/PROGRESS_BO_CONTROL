<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$today = date('Ymd');

try {
    // Get suppliers with completion rate < 50%
    $sql = "
    SELECT 
        o.SUPPLIER_CODE,
        m.SUPPLIER_NAME,
        SUM(o.ORD_QTY) as total_order,
        ISNULL((
            SELECT SUM(t.TRAN_QTY) 
            FROM T_UPDATE_BO t 
            WHERE t.DATE = o.DELV_DATE 
            AND t.PART_NO = o.PART_NO
        ), 0) as total_incoming,
        CASE 
            WHEN SUM(o.ORD_QTY) > 0 
            THEN ROUND(
                (ISNULL((
                    SELECT SUM(t.TRAN_QTY) 
                    FROM T_UPDATE_BO t 
                    WHERE t.DATE = o.DELV_DATE 
                    AND t.PART_NO = o.PART_NO
                ), 0) * 100.0 / SUM(o.ORD_QTY)), 
                1
            )
            ELSE 0 
        END as completion_rate
    FROM T_ORDER o
    LEFT JOIN M_SUPPLIER m ON o.SUPPLIER_CODE = m.SUPPLIER_CODE
    WHERE o.DELV_DATE = ?
    GROUP BY o.SUPPLIER_CODE, m.SUPPLIER_NAME
    HAVING SUM(o.ORD_QTY) > 0
    ";
    
    $stmt = sqlsrv_query($conn, $sql, [$today]);
    if ($stmt === false) throw new Exception('Query failed: ' . print_r(sqlsrv_errors(), true));
    
    $alerts = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $completionRate = (float)$row['completion_rate'];
        
        if ($completionRate < 30) {
            // High priority alert
            $alerts[] = [
                'id' => count($alerts) + 1,
                'supplier_code' => $row['SUPPLIER_CODE'],
                'supplier_name' => $row['SUPPLIER_NAME'] ?: 'Unknown',
                'completion_rate' => $completionRate,
                'message' => 'Critical delay! Only ' . $completionRate . '% completed',
                'priority' => 'high',
                'time' => date('H:i')
            ];
        } elseif ($completionRate < 50) {
            // Medium priority alert
            $alerts[] = [
                'id' => count($alerts) + 1,
                'supplier_code' => $row['SUPPLIER_CODE'],
                'supplier_name' => $row['SUPPLIER_NAME'] ?: 'Unknown',
                'completion_rate' => $completionRate,
                'message' => 'Significant delay: ' . $completionRate . '% completed',
                'priority' => 'medium',
                'time' => date('H:i')
            ];
        }
    }
    
    // Add system alerts
    $systemAlerts = [
        [
            'id' => 9991,
            'supplier_code' => 'SYSTEM',
            'supplier_name' => 'System Alert',
            'message' => 'Data refresh successful',
            'priority' => 'low',
            'time' => date('H:i')
        ],
        [
            'id' => 9992,
            'supplier_code' => 'SYSTEM',
            'supplier_name' => 'System Alert',
            'message' => 'Live monitoring active',
            'priority' => 'low',
            'time' => date('H:i')
        ]
    ];
    
    // Combine alerts
    $allAlerts = array_merge($alerts, $systemAlerts);
    
    // Sort by priority (high first)
    usort($allAlerts, function($a, $b) {
        $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
        return $priorityOrder[$a['priority']] - $priorityOrder[$b['priority']];
    });
    
    echo json_encode($allAlerts);
    
} catch (Exception $e) {
    echo json_encode([
        ['id' => 9999, 'supplier_code' => 'SYSTEM', 'supplier_name' => 'System Error', 
         'message' => 'Failed to load alerts: ' . $e->getMessage(), 'priority' => 'high', 'time' => date('H:i')]
    ]);
}
?>