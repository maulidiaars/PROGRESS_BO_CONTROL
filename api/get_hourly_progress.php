<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$date = $_GET['date'] ?? date('Ymd');
$shift = $_GET['shift'] ?? 'DS'; // DS or NS

try {
    $data = [];
    
    if ($shift === 'DS') {
        // Day shift hours: 7-20
        for ($hour = 7; $hour <= 20; $hour++) {
            $hourStr = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $sql = "SELECT ISNULL(SUM(TRAN_QTY), 0) as qty 
                    FROM T_UPDATE_BO 
                    WHERE DATE = ? AND HOUR = ?";
            $stmt = sqlsrv_query($conn, $sql, [$date, $hour]);
            $row = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : ['qty' => 0];
            $data[] = ['hour' => $hourStr, 'qty' => (int)$row['qty']];
        }
    } else {
        // Night shift hours: 21-23 and 0-6
        for ($hour = 21; $hour <= 23; $hour++) {
            $hourStr = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $sql = "SELECT ISNULL(SUM(TRAN_QTY), 0) as qty 
                    FROM T_UPDATE_BO 
                    WHERE DATE = ? AND HOUR = ?";
            $stmt = sqlsrv_query($conn, $sql, [$date, $hour]);
            $row = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : ['qty' => 0];
            $data[] = ['hour' => $hourStr, 'qty' => (int)$row['qty']];
        }
        for ($hour = 0; $hour <= 6; $hour++) {
            $hourStr = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $sql = "SELECT ISNULL(SUM(TRAN_QTY), 0) as qty 
                    FROM T_UPDATE_BO 
                    WHERE DATE = ? AND HOUR = ?";
            $stmt = sqlsrv_query($conn, $sql, [$date, $hour]);
            $row = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : ['qty' => 0];
            $data[] = ['hour' => $hourStr, 'qty' => (int)$row['qty']];
        }
    }
    
    echo json_encode($data);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>