<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/status_logic.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Ambil bulan berjalan (dari tanggal 1 sampai hari ini)
$currentMonth = date('Ym') . '01';
$today = date('Ymd');

try {
    // ==================== DAY SHIFT (07:00 - 20:00) ====================
    $sqlDS = "
    SELECT 
        o.DELV_DATE,
        o.PART_NO,
        o.SUPPLIER_CODE,
        SUM(ISNULL(o.ORD_QTY, 0)) as total_order_qty,
        ISNULL(MAX(o.ADD_DS), 0) as add_ds,
        ISNULL((
            SELECT SUM(t.TRAN_QTY)
            FROM T_UPDATE_BO t
            WHERE t.DATE = o.DELV_DATE
            AND t.PART_NO = o.PART_NO
            AND t.HOUR BETWEEN 7 AND 20
        ), 0) as ds_actual,
        o.CURRENT_STATUS as status
    FROM T_ORDER o
    WHERE o.DELV_DATE >= ? 
    AND o.DELV_DATE <= ?
    AND o.ETA IS NOT NULL
    AND o.ETA != ''
    AND (
        (TRY_CAST(o.ETA AS TIME) BETWEEN '07:00:00' AND '20:00:00') OR
        (o.ETA LIKE '0[0-9]:%' OR o.ETA LIKE '1[0-9]:%') OR
        (CAST(LEFT(o.ETA, 2) AS INT) BETWEEN 7 AND 20)
    )
    GROUP BY o.DELV_DATE, o.PART_NO, o.SUPPLIER_CODE, o.CURRENT_STATUS
    HAVING SUM(ISNULL(o.ORD_QTY, 0)) > 0
    ";
    
    $params = [$currentMonth, $today];
    $stmtDS = sqlsrv_query($conn, $sqlDS, $params);
    
    // Inisialisasi counters
    $ds_ok_count = 0;
    $ds_on_progress_count = 0;
    $ds_over_count = 0;
    $ds_delay_count = 0;
    $ds_total_order = 0;
    $ds_total_incoming = 0;
    $ds_total_delivery = 0;
    
    if ($stmtDS) {
        while ($row = sqlsrv_fetch_array($stmtDS, SQLSRV_FETCH_ASSOC)) {
            $order_qty = (int)$row['total_order_qty'];
            $add_ds = (int)$row['add_ds'];
            $ds_actual = (int)$row['ds_actual'];
            $status = $row['status'] ?? '';
            
            $total_order = $order_qty + $add_ds;
            
            $ds_total_order += $total_order;
            $ds_total_incoming += $ds_actual;
            $ds_total_delivery++;
            
            // Klasifikasi berdasarkan status
            switch ($status) {
                case 'OK':
                    $ds_ok_count++;
                    break;
                case 'ON_PROGRESS':
                    $ds_on_progress_count++;
                    break;
                case 'OVER':
                    $ds_over_count++;
                    break;
                case 'DELAY':
                    $ds_delay_count++;
                    break;
                default:
                    // Jika status tidak valid, hitung berdasarkan logika
                    if ($ds_actual > $total_order) {
                        $ds_over_count++;
                    } else if ($ds_actual >= $total_order) {
                        $ds_ok_count++;
                    } else {
                        $currentHour = date('H');
                        $delvDate = intval($row['DELV_DATE']);
                        
                        if ($delvDate < intval($today)) {
                            $ds_delay_count++;
                        } else if ($delvDate > intval($today)) {
                            $ds_on_progress_count++;
                        } else {
                            // Hari ini, cek checkpoint
                            if ($currentHour >= 16) {
                                $ds_delay_count++;
                            } else {
                                $ds_on_progress_count++;
                            }
                        }
                    }
            }
        }
        sqlsrv_free_stmt($stmtDS);
    }
    
    // Hitung persentase untuk DS
    $ds_ok_percentage = $ds_total_delivery > 0 ? round(($ds_ok_count / $ds_total_delivery) * 100, 1) : 0;
    $ds_on_progress_percentage = $ds_total_delivery > 0 ? round(($ds_on_progress_count / $ds_total_delivery) * 100, 1) : 0;
    $ds_over_percentage = $ds_total_delivery > 0 ? round(($ds_over_count / $ds_total_delivery) * 100, 1) : 0;
    $ds_delay_percentage = $ds_total_delivery > 0 ? round(($ds_delay_count / $ds_total_delivery) * 100, 1) : 0;
    
    // Completion rate overall untuk DS
    $ds_completion_rate = $ds_total_order > 0 ? round(($ds_total_incoming / $ds_total_order) * 100, 1) : 0;
    
    // ==================== NIGHT SHIFT (21:00 - 06:00) ====================
    $sqlNS = "
    SELECT 
        o.DELV_DATE,
        o.PART_NO,
        o.SUPPLIER_CODE,
        SUM(ISNULL(o.ORD_QTY, 0)) as total_order_qty,
        ISNULL(MAX(o.ADD_NS), 0) as add_ns,
        ISNULL((
            SELECT SUM(t.TRAN_QTY)
            FROM T_UPDATE_BO t
            WHERE t.DATE = o.DELV_DATE
            AND t.PART_NO = o.PART_NO
            AND (t.HOUR BETWEEN 21 AND 23 OR t.HOUR BETWEEN 0 AND 6)
        ), 0) as ns_actual,
        o.CURRENT_STATUS as status
    FROM T_ORDER o
    WHERE o.DELV_DATE >= ? 
    AND o.DELV_DATE <= ?
    AND o.ETA IS NOT NULL
    AND o.ETA != ''
    AND NOT (
        (TRY_CAST(o.ETA AS TIME) BETWEEN '07:00:00' AND '20:00:00') OR
        (o.ETA LIKE '0[0-9]:%' OR o.ETA LIKE '1[0-9]:%') OR
        (CAST(LEFT(o.ETA, 2) AS INT) BETWEEN 7 AND 20)
    )
    GROUP BY o.DELV_DATE, o.PART_NO, o.SUPPLIER_CODE, o.CURRENT_STATUS
    HAVING SUM(ISNULL(o.ORD_QTY, 0)) > 0
    ";
    
    $stmtNS = sqlsrv_query($conn, $sqlNS, $params);
    
    // Inisialisasi counters untuk NS
    $ns_ok_count = 0;
    $ns_on_progress_count = 0;
    $ns_over_count = 0;
    $ns_delay_count = 0;
    $ns_total_order = 0;
    $ns_total_incoming = 0;
    $ns_total_delivery = 0;
    
    if ($stmtNS) {
        while ($row = sqlsrv_fetch_array($stmtNS, SQLSRV_FETCH_ASSOC)) {
            $order_qty = (int)$row['total_order_qty'];
            $add_ns = (int)$row['add_ns'];
            $ns_actual = (int)$row['ns_actual'];
            $status = $row['status'] ?? '';
            
            $total_order = $order_qty + $add_ns;
            
            $ns_total_order += $total_order;
            $ns_total_incoming += $ns_actual;
            $ns_total_delivery++;
            
            // Klasifikasi berdasarkan status
            switch ($status) {
                case 'OK':
                    $ns_ok_count++;
                    break;
                case 'ON_PROGRESS':
                    $ns_on_progress_count++;
                    break;
                case 'OVER':
                    $ns_over_count++;
                    break;
                case 'DELAY':
                    $ns_delay_count++;
                    break;
                default:
                    // Jika status tidak valid, hitung berdasarkan logika
                    if ($ns_actual > $total_order) {
                        $ns_over_count++;
                    } else if ($ns_actual >= $total_order) {
                        $ns_ok_count++;
                    } else {
                        $currentHour = date('H');
                        $delvDate = intval($row['DELV_DATE']);
                        
                        if ($delvDate < intval($today)) {
                            $ns_delay_count++;
                        } else if ($delvDate > intval($today)) {
                            $ns_on_progress_count++;
                        } else {
                            // Hari ini, cek checkpoint untuk NS (04:00)
                            if ($currentHour >= 4 && $currentHour < 21) {
                                $ns_delay_count++;
                            } else {
                                $ns_on_progress_count++;
                            }
                        }
                    }
            }
        }
        sqlsrv_free_stmt($stmtNS);
    }
    
    // Hitung persentase untuk NS
    $ns_ok_percentage = $ns_total_delivery > 0 ? round(($ns_ok_count / $ns_total_delivery) * 100, 1) : 0;
    $ns_on_progress_percentage = $ns_total_delivery > 0 ? round(($ns_on_progress_count / $ns_total_delivery) * 100, 1) : 0;
    $ns_over_percentage = $ns_total_delivery > 0 ? round(($ns_over_count / $ns_total_delivery) * 100, 1) : 0;
    $ns_delay_percentage = $ns_total_delivery > 0 ? round(($ns_delay_count / $ns_total_delivery) * 100, 1) : 0;
    
    // Completion rate overall untuk NS
    $ns_completion_rate = $ns_total_order > 0 ? round(($ns_total_incoming / $ns_total_order) * 100, 1) : 0;
    
    // ==================== RESPONSE FORMAT ====================
    $result = [
        'ds' => [
            'completion_rate' => $ds_completion_rate,
            'ok_count' => $ds_ok_count,
            'on_progress_count' => $ds_on_progress_count,
            'over_count' => $ds_over_count,
            'delay_count' => $ds_delay_count,
            'ok_percentage' => $ds_ok_percentage,
            'on_progress_percentage' => $ds_on_progress_percentage,
            'over_percentage' => $ds_over_percentage,
            'delay_percentage' => $ds_delay_percentage,
            'total_delivery' => $ds_total_delivery,
            'total_order' => $ds_total_order,
            'total_incoming' => $ds_total_incoming
        ],
        'ns' => [
            'completion_rate' => $ns_completion_rate,
            'ok_count' => $ns_ok_count,
            'on_progress_count' => $ns_on_progress_count,
            'over_count' => $ns_over_count,
            'delay_count' => $ns_delay_count,
            'ok_percentage' => $ns_ok_percentage,
            'on_progress_percentage' => $ns_on_progress_percentage,
            'over_percentage' => $ns_over_percentage,
            'delay_percentage' => $ns_delay_percentage,
            'total_delivery' => $ns_total_delivery,
            'total_order' => $ns_total_order,
            'total_incoming' => $ns_total_incoming
        ],
        'period' => date('M Y'),
        'date_range' => date('d M', strtotime($currentMonth)) . ' - ' . date('d M', strtotime($today))
    ];
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'ds' => [
            'completion_rate' => 0,
            'ok_count' => 0,
            'on_progress_count' => 0,
            'over_count' => 0,
            'delay_count' => 0,
            'ok_percentage' => 0,
            'on_progress_percentage' => 0,
            'over_percentage' => 0,
            'delay_percentage' => 0,
            'total_delivery' => 0,
            'total_order' => 0,
            'total_incoming' => 0
        ],
        'ns' => [
            'completion_rate' => 0,
            'ok_count' => 0,
            'on_progress_count' => 0,
            'over_count' => 0,
            'delay_count' => 0,
            'ok_percentage' => 0,
            'on_progress_percentage' => 0,
            'over_percentage' => 0,
            'delay_percentage' => 0,
            'total_delivery' => 0,
            'total_order' => 0,
            'total_incoming' => 0
        ]
    ]);
}
?>