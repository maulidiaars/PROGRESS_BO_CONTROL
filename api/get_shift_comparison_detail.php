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
$currentHour = date('H');

error_log("=== SHIFT COMPARISON DEBUG ===");
error_log("Month: $currentMonth, Today: $today, Hour: $currentHour");

try {
    // ==================== DAY SHIFT (07:00 - 20:00) ====================
    $sqlDS = "
    SELECT 
        o.DELV_DATE,
        o.PART_NO,
        o.SUPPLIER_CODE,
        o.ETA,
        SUM(ISNULL(o.ORD_QTY, 0)) as total_order_qty,
        ISNULL(MAX(o.ADD_DS), 0) as add_ds,
        ISNULL((
            SELECT MAX(t.TRAN_QTY)
            FROM T_UPDATE_BO t
            WHERE t.DATE = o.DELV_DATE
            AND t.PART_NO = o.PART_NO
            AND t.HOUR BETWEEN 7 AND 20
        ), 0) as ds_actual
    FROM T_ORDER o
    WHERE o.DELV_DATE >= ? 
    AND o.DELV_DATE <= ?
    AND o.ETA IS NOT NULL
    AND o.ETA != ''
    AND (
        -- ETA antara 07:00 - 20:00
        (TRY_CAST(o.ETA AS TIME) BETWEEN '07:00:00' AND '20:00:00') OR
        (CAST(LEFT(o.ETA, 2) AS INT) BETWEEN 7 AND 20)
    )
    AND o.ORD_QTY > 0
    GROUP BY o.DELV_DATE, o.PART_NO, o.SUPPLIER_CODE, o.ETA
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
            $eta = $row['ETA'] ?? '';
            
            $total_order = $order_qty + $add_ds;
            
            $ds_total_order += $total_order;
            $ds_total_incoming += $ds_actual;
            $ds_total_delivery++;
            
            // Hitung status menggunakan fungsi baru
            $ds_status = calculateDSStatus(
                $row['DELV_DATE'],
                $eta,
                $order_qty,
                $add_ds,
                $ds_actual,
                $currentHour
            );
            
            // Kategorikan berdasarkan status
            switch ($ds_status) {
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
            }
        }
        sqlsrv_free_stmt($stmtDS);
    }
    
    // Hitung persentase untuk DS
    $ds_total_delivery = $ds_ok_count + $ds_on_progress_count + $ds_over_count + $ds_delay_count;
    $ds_ok_percentage = $ds_total_delivery > 0 ? round(($ds_ok_count / $ds_total_delivery) * 100, 1) : 0;
    $ds_on_progress_percentage = $ds_total_delivery > 0 ? round(($ds_on_progress_count / $ds_total_delivery) * 100, 1) : 0;
    $ds_over_percentage = $ds_total_delivery > 0 ? round(($ds_over_count / $ds_total_delivery) * 100, 1) : 0;
    $ds_delay_percentage = $ds_total_delivery > 0 ? round(($ds_delay_count / $ds_total_delivery) * 100, 1) : 0;
    
    // Completion rate overall untuk DS
    $ds_completion_rate = $ds_total_order > 0 ? round(($ds_total_incoming / $ds_total_order) * 100, 1) : 0;
    $ds_has_data = $ds_total_delivery > 0;
    
    error_log("DS Stats: OK=$ds_ok_count, ON=$ds_on_progress_count, OVER=$ds_over_count, DELAY=$ds_delay_count");
    error_log("DS Total: Order=$ds_total_order, Incoming=$ds_total_incoming, Rate=$ds_completion_rate%");
    
    // ==================== NIGHT SHIFT (21:00 - 06:00) ====================
    $sqlNS = "
    SELECT 
        o.DELV_DATE,
        o.PART_NO,
        o.SUPPLIER_CODE,
        o.ETA,
        SUM(ISNULL(o.ORD_QTY, 0)) as total_order_qty,
        ISNULL(MAX(o.ADD_NS), 0) as add_ns,
        ISNULL((
            SELECT MAX(t.TRAN_QTY)
            FROM T_UPDATE_BO t
            WHERE t.DATE = o.DELV_DATE
            AND t.PART_NO = o.PART_NO
            AND (t.HOUR BETWEEN 21 AND 23 OR t.HOUR BETWEEN 0 AND 6)
        ), 0) as ns_actual
    FROM T_ORDER o
    WHERE o.DELV_DATE >= ? 
    AND o.DELV_DATE <= ?
    AND o.ETA IS NOT NULL
    AND o.ETA != ''
    AND (
        -- ETA antara 21:00 - 06:00
        (TRY_CAST(o.ETA AS TIME) >= '21:00:00') OR
        (TRY_CAST(o.ETA AS TIME) <= '06:00:00') OR
        (CAST(LEFT(o.ETA, 2) AS INT) >= 21 OR CAST(LEFT(o.ETA, 2) AS INT) <= 6)
    )
    AND o.ORD_QTY > 0
    GROUP BY o.DELV_DATE, o.PART_NO, o.SUPPLIER_CODE, o.ETA
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
            $eta = $row['ETA'] ?? '';
            
            $total_order = $order_qty + $add_ns;
            
            $ns_total_order += $total_order;
            $ns_total_incoming += $ns_actual;
            $ns_total_delivery++;
            
            // Hitung status menggunakan fungsi baru
            $ns_status = calculateNSStatus(
                $row['DELV_DATE'],
                $eta,
                $order_qty,
                $add_ns,
                $ns_actual,
                $currentHour
            );
            
            // Kategorikan berdasarkan status
            switch ($ns_status) {
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
            }
        }
        sqlsrv_free_stmt($stmtNS);
    }
    
    // Hitung persentase untuk NS
    $ns_total_delivery = $ns_ok_count + $ns_on_progress_count + $ns_over_count + $ns_delay_count;
    $ns_ok_percentage = $ns_total_delivery > 0 ? round(($ns_ok_count / $ns_total_delivery) * 100, 1) : 0;
    $ns_on_progress_percentage = $ns_total_delivery > 0 ? round(($ns_on_progress_count / $ns_total_delivery) * 100, 1) : 0;
    $ns_over_percentage = $ns_total_delivery > 0 ? round(($ns_over_count / $ns_total_delivery) * 100, 1) : 0;
    $ns_delay_percentage = $ns_total_delivery > 0 ? round(($ns_delay_count / $ns_total_delivery) * 100, 1) : 0;
    
    // Completion rate overall untuk NS
    $ns_completion_rate = $ns_total_order > 0 ? round(($ns_total_incoming / $ns_total_order) * 100, 1) : 0;
    $ns_has_data = $ns_total_delivery > 0;
    
    error_log("NS Stats: OK=$ns_ok_count, ON=$ns_on_progress_count, OVER=$ns_over_count, DELAY=$ns_delay_count");
    error_log("NS Total: Order=$ns_total_order, Incoming=$ns_total_incoming, Rate=$ns_completion_rate%");
    
    // ==================== TOTAL PERFORMANCE ====================
    $total_ok_count = $ds_ok_count + $ns_ok_count;
    $total_on_progress_count = $ds_on_progress_count + $ns_on_progress_count;
    $total_over_count = $ds_over_count + $ns_over_count;
    $total_delay_count = $ds_delay_count + $ns_delay_count;
    $total_delivery = $total_ok_count + $total_on_progress_count + $total_over_count + $total_delay_count;
    $total_order = $ds_total_order + $ns_total_order;
    $total_incoming = $ds_total_incoming + $ns_total_incoming;
    
    $total_ok_percentage = $total_delivery > 0 ? round(($total_ok_count / $total_delivery) * 100, 1) : 0;
    $total_on_progress_percentage = $total_delivery > 0 ? round(($total_on_progress_count / $total_delivery) * 100, 1) : 0;
    $total_over_percentage = $total_delivery > 0 ? round(($total_over_count / $total_delivery) * 100, 1) : 0;
    $total_delay_percentage = $total_delivery > 0 ? round(($total_delay_count / $total_delivery) * 100, 1) : 0;
    $total_completion_rate = $total_order > 0 ? round(($total_incoming / $total_order) * 100, 1) : 0;
    
    // ==================== RESPONSE FORMAT ====================
    $result = [
        'success' => true,
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
            'total_incoming' => $ds_total_incoming,
            'has_data' => $ds_has_data
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
            'total_incoming' => $ns_total_incoming,
            'has_data' => $ns_has_data
        ],
        'total' => [
            'completion_rate' => $total_completion_rate,
            'ok_count' => $total_ok_count,
            'on_progress_count' => $total_on_progress_count,
            'over_count' => $total_over_count,
            'delay_count' => $total_delay_count,
            'ok_percentage' => $total_ok_percentage,
            'on_progress_percentage' => $total_on_progress_percentage,
            'over_percentage' => $total_over_percentage,
            'delay_percentage' => $total_delay_percentage,
            'total_delivery' => $total_delivery,
            'total_order' => $total_order,
            'total_incoming' => $total_incoming,
            'has_data' => ($ds_has_data || $ns_has_data)
        ],
        'period' => date('M Y'),
        'date_range' => date('d M', strtotime($currentMonth)) . ' - ' . date('d M', strtotime($today)),
        'debug_info' => [
            'current_hour' => $currentHour,
            'is_past_16' => ($currentHour >= 16),
            'today' => $today,
            'month_start' => $currentMonth
        ]
    ];
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("Shift comparison error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
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
            'total_incoming' => 0,
            'has_data' => false
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
            'total_incoming' => 0,
            'has_data' => false
        ],
        'total' => [
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
            'total_incoming' => 0,
            'has_data' => false
        ]
    ]);
}
?>