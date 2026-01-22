<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
$data = [];

// Get current date in Indonesia timezone
date_default_timezone_set('Asia/Jakarta');
$today = date('Ymd');

// Get data for each day
for ($i = $days; $i >= 0; $i--) {
    $date = date('Ymd', strtotime("-$i days"));
    $displayDate = date('d M', strtotime($date));
    
    try {
        // Total Order for the date (include both regular and add orders)
        $sqlOrder = "
        SELECT 
            ISNULL(SUM(
                CASE 
                    WHEN CYCLE BETWEEN 1 AND 12 
                    AND ETA IS NOT NULL 
                    AND ETA != ''
                    THEN ORD_QTY 
                    ELSE 0 
                END
            ), 0) as total_order
        FROM T_ORDER 
        WHERE DELV_DATE = ?
        ";
        $stmtOrder = sqlsrv_query($conn, $sqlOrder, [$date]);
        
        if ($stmtOrder === false) {
            throw new Exception('Failed to query order data for date: ' . $date);
        }
        
        $rowOrder = sqlsrv_fetch_array($stmtOrder, SQLSRV_FETCH_ASSOC);
        $totalOrder = (int)($rowOrder['total_order'] ?? 0);
        sqlsrv_free_stmt($stmtOrder);
        
        // Total Incoming for the date (all hours)
        $sqlIncoming = "
        SELECT ISNULL(SUM(TRAN_QTY), 0) as total_incoming 
        FROM T_UPDATE_BO 
        WHERE DATE = ?
        ";
        $stmtIncoming = sqlsrv_query($conn, $sqlIncoming, [$date]);
        
        if ($stmtIncoming === false) {
            throw new Exception('Failed to query incoming data for date: ' . $date);
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
        // If error, use zero values but still include the date
        $data[] = [
            'date' => $displayDate,
            'full_date' => $date,
            'target_qty' => 0,
            'actual_qty' => 0,
            'completion_rate' => 0
        ];
        error_log("Error processing date $date: " . $e->getMessage());
    }
}

echo json_encode($data);
?>