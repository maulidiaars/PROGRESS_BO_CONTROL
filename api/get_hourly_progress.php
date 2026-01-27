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
    
    // STEP 1: Ambil semua data untuk hari ini
    $allData = [];
    $sqlAll = "SELECT PART_NO, HOUR, TRAN_QTY FROM T_UPDATE_BO WHERE DATE = ? ORDER BY PART_NO, HOUR";
    $stmtAll = sqlsrv_query($conn, $sqlAll, [$date]);
    
    if ($stmtAll) {
        while ($row = sqlsrv_fetch_array($stmtAll, SQLSRV_FETCH_ASSOC)) {
            $partNo = $row['PART_NO'];
            $hour = (int)$row['HOUR'];
            $qty = (int)$row['TRAN_QTY'];
            
            if (!isset($allData[$partNo])) {
                $allData[$partNo] = [];
            }
            $allData[$partNo][$hour] = $qty;
        }
        sqlsrv_free_stmt($stmtAll);
    }
    
    if ($shift === 'DS') {
        // Day shift hours: 7-20
        $cumulativeTotal = 0;
        
        for ($hour = 7; $hour <= 20; $hour++) {
            $hourStr = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $hourlyIncrement = 0;
            
            // Untuk tiap part, ambil nilai untuk jam ini (bukan akumulasi)
            foreach ($allData as $partNo => $hourData) {
                if (isset($hourData[$hour])) {
                    // Cari nilai TERBARU untuk jam ini
                    $latestQty = $hourData[$hour];
                    
                    // Cari nilai SEBELUMNYA untuk hitung increment
                    $prevQty = 0;
                    for ($h = $hour - 1; $h >= 7; $h--) {
                        if (isset($hourData[$h])) {
                            $prevQty = $hourData[$h];
                            break;
                        }
                    }
                    
                    $hourlyIncrement += max(0, $latestQty - $prevQty);
                }
            }
            
            // Tambah ke cumulative
            $cumulativeTotal += $hourlyIncrement;
            
            $data[] = [
                'hour' => $hourStr,
                'qty' => $hourlyIncrement, // Increment per jam
                'cumulative' => $cumulativeTotal // Total akumulasi sampai jam ini
            ];
        }
        
        echo json_encode($data);
        
    } else {
        // Night shift hours: 21-23 dan 0-6
        $cumulativeTotal = 0;
        
        // Jam 21-23
        for ($hour = 21; $hour <= 23; $hour++) {
            $hourStr = str_pad($hour, 2, '0', STR_PAD_LEFT);
            
            $sql = "SELECT ISNULL(SUM(TRAN_QTY), 0) as qty 
                    FROM T_UPDATE_BO 
                    WHERE DATE = ? AND HOUR = ?";
            $stmt = sqlsrv_query($conn, $sql, [$date, $hour]);
            $row = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : ['qty' => 0];
            $hourlyQty = (int)$row['qty'];
            
            $cumulativeTotal += $hourlyQty;
            
            $data[] = [
                'hour' => $hourStr,
                'qty' => $hourlyQty,
                'cumulative' => $cumulativeTotal
            ];
        }
        
        // Jam 0-6
        for ($hour = 0; $hour <= 6; $hour++) {
            $hourStr = str_pad($hour, 2, '0', STR_PAD_LEFT);
            
            $sql = "SELECT ISNULL(SUM(TRAN_QTY), 0) as qty 
                    FROM T_UPDATE_BO 
                    WHERE DATE = ? AND HOUR = ?";
            $stmt = sqlsrv_query($conn, $sql, [$date, $hour]);
            $row = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : ['qty' => 0];
            $hourlyQty = (int)$row['qty'];
            
            $cumulativeTotal += $hourlyQty;
            
            $data[] = [
                'hour' => $hourStr,
                'qty' => $hourlyQty,
                'cumulative' => $cumulativeTotal
            ];
        }
        
        echo json_encode($data);
    }
    
} catch (Exception $e) {
    // FALLBACK ke logic sederhana
    $data = [];
    $cumulativeTotal = 0;
    
    if ($shift === 'DS') {
        for ($hour = 7; $hour <= 20; $hour++) {
            $hourStr = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $sql = "SELECT ISNULL(SUM(TRAN_QTY), 0) as qty 
                    FROM T_UPDATE_BO 
                    WHERE DATE = ? AND HOUR = ?";
            $stmt = sqlsrv_query($conn, $sql, [$date, $hour]);
            $row = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : ['qty' => 0];
            $hourlyQty = (int)$row['qty'];
            
            $cumulativeTotal += $hourlyQty;
            
            $data[] = [
                'hour' => $hourStr,
                'qty' => $hourlyQty,
                'cumulative' => $cumulativeTotal
            ];
        }
    } else {
        for ($hour = 21; $hour <= 23; $hour++) {
            $hourStr = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $sql = "SELECT ISNULL(SUM(TRAN_QTY), 0) as qty 
                    FROM T_UPDATE_BO 
                    WHERE DATE = ? AND HOUR = ?";
            $stmt = sqlsrv_query($conn, $sql, [$date, $hour]);
            $row = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : ['qty' => 0];
            $hourlyQty = (int)$row['qty'];
            
            $cumulativeTotal += $hourlyQty;
            
            $data[] = [
                'hour' => $hourStr,
                'qty' => $hourlyQty,
                'cumulative' => $cumulativeTotal
            ];
        }
        for ($hour = 0; $hour <= 6; $hour++) {
            $hourStr = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $sql = "SELECT ISNULL(SUM(TRAN_QTY), 0) as qty 
                    FROM T_UPDATE_BO 
                    WHERE DATE = ? AND HOUR = ?";
            $stmt = sqlsrv_query($conn, $sql, [$date, $hour]);
            $row = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : ['qty' => 0];
            $hourlyQty = (int)$row['qty'];
            
            $cumulativeTotal += $hourlyQty;
            
            $data[] = [
                'hour' => $hourStr,
                'qty' => $hourlyQty,
                'cumulative' => $cumulativeTotal
            ];
        }
    }
    
    echo json_encode($data);
}
?>