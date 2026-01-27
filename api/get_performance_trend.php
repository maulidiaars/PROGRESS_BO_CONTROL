<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
$data = [];

date_default_timezone_set('Asia/Jakarta');
$today = date('Ymd');

// Get data for each day
for ($i = $days; $i >= 0; $i--) {
    $date = date('Ymd', strtotime("-$i days"));
    $displayDate = date('d M', strtotime($date));
    
    try {
        // Query T_ORDER
        $sqlOrder = "
        SELECT 
            ISNULL(SUM(ORD_QTY), 0) as total_order
        FROM T_ORDER 
        WHERE DELV_DATE = ?
        AND ORD_QTY > 0
        ";
        $stmtOrder = sqlsrv_query($conn, $sqlOrder, [$date]);
        
        if ($stmtOrder === false) {
            throw new Exception('Failed to query order data');
        }
        
        $rowOrder = sqlsrv_fetch_array($stmtOrder, SQLSRV_FETCH_ASSOC);
        $totalOrder = (int)($rowOrder['total_order'] ?? 0);
        sqlsrv_free_stmt($stmtOrder);
        
        // Query T_UPDATE_BO
        $sqlIncoming = "
        SELECT ISNULL(SUM(last_qty), 0) as total_incoming
        FROM (
            SELECT 
                ub.PART_NO,
                MAX(ub.TRAN_QTY) as last_qty
            FROM T_UPDATE_BO ub
            WHERE ub.DATE = ?
            GROUP BY ub.PART_NO
        ) as supplier_data
        ";
        $stmtIncoming = sqlsrv_query($conn, $sqlIncoming, [$date]);
        
        if ($stmtIncoming === false) {
            throw new Exception('Failed to query incoming data');
        }
        
        $rowIncoming = sqlsrv_fetch_array($stmtIncoming, SQLSRV_FETCH_ASSOC);
        $totalIncoming = (int)($rowIncoming['total_incoming'] ?? 0);
        sqlsrv_free_stmt($stmtIncoming);
        
        // Calculate completion rate
        $completionRate = $totalOrder > 0 ? round(($totalIncoming / $totalOrder) * 100, 1) : 0;
        
        $data[] = [
            'date' => $displayDate,
            'full_date' => $date,
            'target_qty' => $totalOrder,
            'actual_qty' => $totalIncoming,
            'completion_rate' => $completionRate
        ];
        
    } catch (Exception $e) {
        $data[] = [
            'date' => $displayDate,
            'full_date' => $date,
            'target_qty' => 0,
            'actual_qty' => 0,
            'completion_rate' => 0
        ];
    }
}

echo json_encode([
    'success' => true,
    'data' => $data,
    'count' => count($data),
    'query_info' => [
        'days' => $days,
        'today' => $today
    ]
]);
?>