<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$currentMonth = date('Ym') . '01';
$today = date('Ymd');

try {
    // Get DS performance (7-20)
    $sqlDS = "
    SELECT 
        ISNULL(SUM(o.ORD_QTY), 0) as total_order_ds,
        ISNULL((
            SELECT SUM(t.TRAN_QTY)
            FROM T_UPDATE_BO t
            WHERE t.DATE = o.DELV_DATE
            AND t.PART_NO = o.PART_NO
            AND t.HOUR BETWEEN 7 AND 20
        ), 0) as total_incoming_ds
    FROM T_ORDER o
    WHERE o.DELV_DATE >= ? 
    AND o.DELV_DATE <= ?
    AND o.ETA IS NOT NULL
    AND (
        (TRY_CAST(o.ETA AS TIME) BETWEEN '07:00:00' AND '20:00:00') OR
        (o.ETA LIKE '0[0-9]:%' OR o.ETA LIKE '1[0-9]:%') OR
        (CAST(LEFT(o.ETA, 2) AS INT) BETWEEN 7 AND 20)
    )
    ";
    
    // Get NS performance (21-6)
    $sqlNS = "
    SELECT 
        ISNULL(SUM(o.ORD_QTY), 0) as total_order_ns,
        ISNULL((
            SELECT SUM(t.TRAN_QTY)
            FROM T_UPDATE_BO t
            WHERE t.DATE = o.DELV_DATE
            AND t.PART_NO = o.PART_NO
            AND (t.HOUR BETWEEN 21 AND 23 OR t.HOUR BETWEEN 0 AND 6)
        ), 0) as total_incoming_ns
    FROM T_ORDER o
    WHERE o.DELV_DATE >= ? 
    AND o.DELV_DATE <= ?
    AND o.ETA IS NOT NULL
    AND NOT (
        (TRY_CAST(o.ETA AS TIME) BETWEEN '07:00:00' AND '20:00:00') OR
        (o.ETA LIKE '0[0-9]:%' OR o.ETA LIKE '1[0-9]:%') OR
        (CAST(LEFT(o.ETA, 2) AS INT) BETWEEN 7 AND 20)
    )
    ";
    
    $params = [$currentMonth, $today];
    
    // Execute DS query
    $stmtDS = sqlsrv_query($conn, $sqlDS, $params);
    $rowDS = $stmtDS ? sqlsrv_fetch_array($stmtDS, SQLSRV_FETCH_ASSOC) : ['total_order_ds' => 0, 'total_incoming_ds' => 0];
    
    // Execute NS query
    $stmtNS = sqlsrv_query($conn, $sqlNS, $params);
    $rowNS = $stmtNS ? sqlsrv_fetch_array($stmtNS, SQLSRV_FETCH_ASSOC) : ['total_order_ns' => 0, 'total_incoming_ns' => 0];
    
    // Free statements
    if ($stmtDS) sqlsrv_free_stmt($stmtDS);
    if ($stmtNS) sqlsrv_free_stmt($stmtNS);
    
    $totalOrderDS = (int)($rowDS['total_order_ds'] ?? 0);
    $totalIncomingDS = (int)($rowDS['total_incoming_ds'] ?? 0);
    $totalOrderNS = (int)($rowNS['total_order_ns'] ?? 0);
    $totalIncomingNS = (int)($rowNS['total_incoming_ns'] ?? 0);
    
    $dsCompletion = $totalOrderDS > 0 ? round(($totalIncomingDS / $totalOrderDS) * 100, 1) : 0;
    $nsCompletion = $totalOrderNS > 0 ? round(($totalIncomingNS / $totalOrderNS) * 100, 1) : 0;
    
    $result = [
        'ds_completion' => $dsCompletion,
        'ns_completion' => $nsCompletion,
        'ds_order' => $totalOrderDS,
        'ds_incoming' => $totalIncomingDS,
        'ns_order' => $totalOrderNS,
        'ns_incoming' => $totalIncomingNS,
        'period' => date('M Y'),
        'date_range' => date('d M', strtotime($currentMonth)) . ' - ' . date('d M', strtotime($today))
    ];
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'ds_completion' => 0,
        'ns_completion' => 0,
        'ds_order' => 0,
        'ds_incoming' => 0,
        'ns_order' => 0,
        'ns_incoming' => 0,
        'error' => $e->getMessage()
    ]);
}
?><?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$currentMonth = date('Ym') . '01';
$today = date('Ymd');

try {
    // Get DS performance (7-20)
    $sqlDS = "
    SELECT 
        ISNULL(SUM(o.ORD_QTY), 0) as total_order_ds,
        ISNULL((
            SELECT SUM(t.TRAN_QTY)
            FROM T_UPDATE_BO t
            WHERE t.DATE = o.DELV_DATE
            AND t.PART_NO = o.PART_NO
            AND t.HOUR BETWEEN 7 AND 20
        ), 0) as total_incoming_ds
    FROM T_ORDER o
    WHERE o.DELV_DATE >= ? 
    AND o.DELV_DATE <= ?
    AND o.ETA IS NOT NULL
    AND (
        (TRY_CAST(o.ETA AS TIME) BETWEEN '07:00:00' AND '20:00:00') OR
        (o.ETA LIKE '0[0-9]:%' OR o.ETA LIKE '1[0-9]:%') OR
        (CAST(LEFT(o.ETA, 2) AS INT) BETWEEN 7 AND 20)
    )
    ";
    
    // Get NS performance (21-6)
    $sqlNS = "
    SELECT 
        ISNULL(SUM(o.ORD_QTY), 0) as total_order_ns,
        ISNULL((
            SELECT SUM(t.TRAN_QTY)
            FROM T_UPDATE_BO t
            WHERE t.DATE = o.DELV_DATE
            AND t.PART_NO = o.PART_NO
            AND (t.HOUR BETWEEN 21 AND 23 OR t.HOUR BETWEEN 0 AND 6)
        ), 0) as total_incoming_ns
    FROM T_ORDER o
    WHERE o.DELV_DATE >= ? 
    AND o.DELV_DATE <= ?
    AND o.ETA IS NOT NULL
    AND NOT (
        (TRY_CAST(o.ETA AS TIME) BETWEEN '07:00:00' AND '20:00:00') OR
        (o.ETA LIKE '0[0-9]:%' OR o.ETA LIKE '1[0-9]:%') OR
        (CAST(LEFT(o.ETA, 2) AS INT) BETWEEN 7 AND 20)
    )
    ";
    
    $params = [$currentMonth, $today];
    
    // Execute DS query
    $stmtDS = sqlsrv_query($conn, $sqlDS, $params);
    $rowDS = $stmtDS ? sqlsrv_fetch_array($stmtDS, SQLSRV_FETCH_ASSOC) : ['total_order_ds' => 0, 'total_incoming_ds' => 0];
    
    // Execute NS query
    $stmtNS = sqlsrv_query($conn, $sqlNS, $params);
    $rowNS = $stmtNS ? sqlsrv_fetch_array($stmtNS, SQLSRV_FETCH_ASSOC) : ['total_order_ns' => 0, 'total_incoming_ns' => 0];
    
    // Free statements
    if ($stmtDS) sqlsrv_free_stmt($stmtDS);
    if ($stmtNS) sqlsrv_free_stmt($stmtNS);
    
    $totalOrderDS = (int)($rowDS['total_order_ds'] ?? 0);
    $totalIncomingDS = (int)($rowDS['total_incoming_ds'] ?? 0);
    $totalOrderNS = (int)($rowNS['total_order_ns'] ?? 0);
    $totalIncomingNS = (int)($rowNS['total_incoming_ns'] ?? 0);
    
    $dsCompletion = $totalOrderDS > 0 ? round(($totalIncomingDS / $totalOrderDS) * 100, 1) : 0;
    $nsCompletion = $totalOrderNS > 0 ? round(($totalIncomingNS / $totalOrderNS) * 100, 1) : 0;
    
    $result = [
        'ds_completion' => $dsCompletion,
        'ns_completion' => $nsCompletion,
        'ds_order' => $totalOrderDS,
        'ds_incoming' => $totalIncomingDS,
        'ns_order' => $totalOrderNS,
        'ns_incoming' => $totalIncomingNS,
        'period' => date('M Y'),
        'date_range' => date('d M', strtotime($currentMonth)) . ' - ' . date('d M', strtotime($today))
    ];
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'ds_completion' => 0,
        'ns_completion' => 0,
        'ds_order' => 0,
        'ds_incoming' => 0,
        'ns_order' => 0,
        'ns_incoming' => 0,
        'error' => $e->getMessage()
    ]);
}
?>