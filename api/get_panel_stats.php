<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$date = $_GET['date'] ?? date('Ymd');

try {
    // Get total suppliers
    $sqlSuppliers = "
    SELECT COUNT(DISTINCT SUPPLIER_CODE) as total_suppliers
    FROM T_ORDER 
    WHERE DELV_DATE = ?
    AND ORD_QTY > 0
    ";
    $stmtSuppliers = sqlsrv_query($conn, $sqlSuppliers, [$date]);
    $rowSuppliers = $stmtSuppliers ? sqlsrv_fetch_array($stmtSuppliers, SQLSRV_FETCH_ASSOC) : ['total_suppliers' => 0];
    $totalSuppliers = (int)($rowSuppliers['total_suppliers'] ?? 0);
    
    // Get supplier completion rates
    $sqlCompletion = "
    SELECT 
        o.SUPPLIER_CODE,
        SUM(o.ORD_QTY) as total_order,
        ISNULL((
            SELECT SUM(t.TRAN_QTY) 
            FROM T_UPDATE_BO t 
            WHERE t.DATE = o.DELV_DATE 
            AND t.PART_NO = o.PART_NO
        ), 0) as total_incoming
    FROM T_ORDER o
    WHERE o.DELV_DATE = ?
    GROUP BY o.SUPPLIER_CODE, o.DELV_DATE, o.PART_NO
    HAVING SUM(o.ORD_QTY) > 0
    ";
    
    $stmtCompletion = sqlsrv_query($conn, $sqlCompletion, [$date]);
    $onTrack = 0;
    $delayed = 0;
    
    if ($stmtCompletion) {
        while ($row = sqlsrv_fetch_array($stmtCompletion, SQLSRV_FETCH_ASSOC)) {
            $totalOrder = (int)$row['total_order'];
            $totalIncoming = (int)$row['total_incoming'];
            $completionRate = $totalOrder > 0 ? ($totalIncoming / $totalOrder) * 100 : 0;
            
            if ($completionRate >= 70) {
                $onTrack++;
            } elseif ($completionRate < 50) {
                $delayed++;
            }
        }
    }
    
    $result = [
        'total_suppliers' => $totalSuppliers,
        'on_track' => $onTrack,
        'delayed' => $delayed,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'total_suppliers' => 0,
        'on_track' => 0,
        'delayed' => 0,
        'error' => $e->getMessage()
    ]);
}
?>
