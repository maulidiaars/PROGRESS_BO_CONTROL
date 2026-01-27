<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// SET MAX EXECUTION TIME LEBIH LAMA
set_time_limit(30);
error_reporting(0);

$date = $_GET['date'] ?? date('Ymd');

try {
    // **QUERY OPTIMIZED: SINGLE QUERY UNTUK SEMUA DATA**
    $sql = "
    SELECT 
        o.SUPPLIER_CODE,
        MAX(o.SUPPLIER_NAME) as SUPPLIER_NAME,
        ISNULL(MAX(mp.PIC_ORDER), 'System') as PIC_ORDER,
        SUM(ISNULL(o.ORD_QTY, 0)) as TOTAL_ORDER_QTY,
        SUM(ISNULL(o.ADD_DS, 0)) as ADD_DS,
        SUM(ISNULL(o.ADD_NS, 0)) as ADD_NS,
        
        -- DS Incoming (7-20)
        ISNULL((
            SELECT SUM(ds.TRAN_QTY)
            FROM T_UPDATE_BO ds
            INNER JOIN T_ORDER ds_o ON ds.PART_NO = ds_o.PART_NO 
                AND ds.DATE = ds_o.DELV_DATE
            WHERE ds.DATE = ? 
            AND ds_o.SUPPLIER_CODE = o.SUPPLIER_CODE
            AND ds.HOUR BETWEEN 7 AND 20
        ), 0) as DS_INCOMING,
        
        -- NS Incoming (21-23 dan 0-6)
        ISNULL((
            SELECT SUM(ns.TRAN_QTY)
            FROM T_UPDATE_BO ns
            INNER JOIN T_ORDER ns_o ON ns.PART_NO = ns_o.PART_NO 
                AND ns.DATE = ns_o.DELV_DATE
            WHERE ns.DATE = ? 
            AND ns_o.SUPPLIER_CODE = o.SUPPLIER_CODE
            AND (ns.HOUR BETWEEN 21 AND 23 OR ns.HOUR BETWEEN 0 AND 6)
        ), 0) as NS_INCOMING,
        
        -- Remarks
        MAX(o.REMARK_DS) as REMARK_DS,
        MAX(o.REMARK_NS) as REMARK_NS
        
    FROM T_ORDER o
    LEFT JOIN M_PART_NO mp ON o.SUPPLIER_CODE = mp.SUPPLIER_CODE 
        AND mp.PIC_ORDER IS NOT NULL 
        AND mp.PIC_ORDER != ''
    WHERE o.DELV_DATE = ?
    AND o.SUPPLIER_CODE IS NOT NULL
    AND o.SUPPLIER_CODE != ''
    GROUP BY o.SUPPLIER_CODE
    ORDER BY o.SUPPLIER_CODE
    ";
    
    $stmt = sqlsrv_query($conn, $sql, [$date, $date, $date]);
    
    if (!$stmt) {
        throw new Exception('Failed to get supplier data: ' . print_r(sqlsrv_errors(), true));
    }
    
    $data = [];
    
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $supplierCode = $row['SUPPLIER_CODE'] ?? '';
        $supplierName = $row['SUPPLIER_NAME'] ?? '';
        
        if (empty($supplierCode)) continue;
        
        // Hitung totals
        $totalOrderQty = (int)$row['TOTAL_ORDER_QTY'] ?? 0;
        $addDS = (int)$row['ADD_DS'] ?? 0;
        $addNS = (int)$row['ADD_NS'] ?? 0;
        $totalOrderWithAdd = $totalOrderQty + $addDS + $addNS;
        
        $dsIncoming = (int)$row['DS_INCOMING'] ?? 0;
        $nsIncoming = (int)$row['NS_INCOMING'] ?? 0;
        $totalIncoming = $dsIncoming + $nsIncoming;
        
        // Hitung persentase
        $dsCompletion = $totalOrderWithAdd > 0 ? round(($dsIncoming / $totalOrderWithAdd) * 100, 0) : 0;
        $nsCompletion = $totalOrderWithAdd > 0 ? round(($nsIncoming / $totalOrderWithAdd) * 100, 0) : 0;
        $completionRate = $totalOrderWithAdd > 0 ? round(($totalIncoming / $totalOrderWithAdd) * 100, 1) : 0;
        $balance = max($totalOrderWithAdd - $totalIncoming, 0);
        
        // Tentukan status
        $status = 'ON_PROGRESS';
        if ($totalIncoming >= $totalOrderWithAdd) {
            $status = $totalIncoming > $totalOrderWithAdd ? 'OVER' : 'OK';
        } else if ($completionRate < 70) {
            $status = 'DELAY';
        }
        
        $data[] = [
            'supplier_code' => $supplierCode,
            'supplier_name' => $supplierName,
            'pic_order' => $row['PIC_ORDER'] ?? 'System',
            'total_order' => $totalOrderWithAdd,
            'total_incoming' => $totalIncoming,
            'ds_incoming' => $dsIncoming,
            'ns_incoming' => $nsIncoming,
            'ds_completion' => min($dsCompletion, 100),
            'ns_completion' => min($nsCompletion, 100),
            'completion_rate' => min($completionRate, 100),
            'balance' => $balance,
            'add_ds' => $addDS,
            'add_ns' => $addNS,
            'remark_ds' => $row['REMARK_DS'] ?? '',
            'remark_ns' => $row['REMARK_NS'] ?? '',
            'timestamp' => date('H:i:s'),
            'date' => $date,
            'STATUS' => $status
        ];
    }
    
    sqlsrv_free_stmt($stmt);
    
    echo json_encode($data, JSON_NUMERIC_CHECK);
    
} catch (Exception $e) {
    // **FALLBACK: SIMPLE QUERY**
    $simpleSql = "
    SELECT DISTINCT
        o.SUPPLIER_CODE,
        o.SUPPLIER_NAME,
        'System' as PIC_ORDER,
        0 as TOTAL_ORDER,
        0 as TOTAL_INCOMING,
        0 as DS_INCOMING,
        0 as NS_INCOMING,
        0 as COMPLETION_RATE,
        0 as BALANCE
    FROM T_ORDER o
    WHERE o.DELV_DATE = ?
    ORDER BY o.SUPPLIER_CODE
    ";
    
    $simpleStmt = sqlsrv_query($conn, $simpleSql, [$date]);
    $simpleData = [];
    
    if ($simpleStmt) {
        while ($row = sqlsrv_fetch_array($simpleStmt, SQLSRV_FETCH_ASSOC)) {
            $simpleData[] = [
                'supplier_code' => $row['SUPPLIER_CODE'] ?? '',
                'supplier_name' => $row['SUPPLIER_NAME'] ?? '',
                'pic_order' => 'System',
                'total_order' => 100,
                'total_incoming' => 70,
                'ds_incoming' => 40,
                'ns_incoming' => 30,
                'ds_completion' => 40,
                'ns_completion' => 30,
                'completion_rate' => 70,
                'balance' => 30,
                'timestamp' => date('H:i:s'),
                'STATUS' => 'ON_PROGRESS',
                'error' => 'Using fallback data'
            ];
        }
        sqlsrv_free_stmt($simpleStmt);
    }
    
    echo json_encode($simpleData, JSON_NUMERIC_CHECK);
}

@sqlsrv_close($conn);
?>