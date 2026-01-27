<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$date = $_GET['date'] ?? date('Ymd');

try {
    // Total Order for today
    $sqlOrder = "
    SELECT ISNULL(SUM(ORD_QTY), 0) as total_order
    FROM T_ORDER 
    WHERE DELV_DATE = ?
    ";
    
    $stmtOrder = sqlsrv_query($conn, $sqlOrder, [$date]);
    if ($stmtOrder === false) throw new Exception('Order query failed');
    $rowOrder = sqlsrv_fetch_array($stmtOrder, SQLSRV_FETCH_ASSOC);
    $totalOrder = (int)($rowOrder['total_order'] ?? 0);
    
    // Total Incoming for today
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
    if ($stmtIncoming === false) throw new Exception('Incoming query failed');
    $rowIncoming = sqlsrv_fetch_array($stmtIncoming, SQLSRV_FETCH_ASSOC);
    $totalIncoming = (int)($rowIncoming['total_incoming'] ?? 0);
    
    // DS Incoming
    $sqlDS = "
    SELECT ISNULL(SUM(TRAN_QTY), 0) as ds_incoming
    FROM T_UPDATE_BO 
    WHERE DATE = ? AND HOUR BETWEEN 7 AND 20
    ";
    $stmtDS = sqlsrv_query($conn, $sqlDS, [$date]);
    $rowDS = $stmtDS ? sqlsrv_fetch_array($stmtDS, SQLSRV_FETCH_ASSOC) : ['ds_incoming' => 0];
    $dsIncoming = (int)($rowDS['ds_incoming'] ?? 0);
    
    // NS Incoming
    $sqlNS = "
    SELECT ISNULL(SUM(TRAN_QTY), 0) as ns_incoming
    FROM T_UPDATE_BO 
    WHERE DATE = ? AND (HOUR BETWEEN 21 AND 23 OR HOUR BETWEEN 0 AND 6)
    ";
    $stmtNS = sqlsrv_query($conn, $sqlNS, [$date]);
    $rowNS = $stmtNS ? sqlsrv_fetch_array($stmtNS, SQLSRV_FETCH_ASSOC) : ['ns_incoming' => 0];
    $nsIncoming = (int)($rowNS['ns_incoming'] ?? 0);
    
    $achievement = $totalOrder > 0 ? round(($totalIncoming / $totalOrder) * 100, 1) : 0;
    $balance = $totalOrder - $totalIncoming;
    
    $result = [
        'date' => $date,
        'total_order' => $totalOrder,
        'total_incoming' => $totalIncoming,
        'ds_incoming' => $dsIncoming,
        'ns_incoming' => $nsIncoming,
        'achievement' => $achievement,
        'balance' => $balance,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'date' => $date,
        'total_order' => 0,
        'total_incoming' => 0,
        'achievement' => 0,
        'balance' => 0
    ]);
}
?>