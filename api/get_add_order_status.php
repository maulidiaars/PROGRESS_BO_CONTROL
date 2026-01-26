<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

$response = [
    'success' => false,
    'current_qty' => 0,
    'remark' => '',
    'hours_data' => [],
    'message' => 'Data not found'
];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['date'], $_GET['supplier_code'], $_GET['part_no'], $_GET['type'])) {
    $date = $_GET['date'];
    $supplier_code = $_GET['supplier_code'];
    $part_no = $_GET['part_no'];
    $type = $_GET['type']; // 'ds' atau 'ns'
    
    $conn = Database::getConnection();
    
    if ($conn === false) {
        $response['message'] = 'Database connection failed';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Ambil data dari T_ORDER
        $sql = "SELECT 
                " . ($type === 'ds' ? "ADD_DS" : "ADD_NS") . " AS current_qty,
                " . ($type === 'ds' ? "REMARK_DS" : "REMARK_NS") . " AS remark,
                " . ($type === 'ds' ? "LAST_ADD_DS_BY" : "LAST_ADD_NS_BY") . " AS last_by,
                " . ($type === 'ds' ? "LAST_ADD_DS_AT" : "LAST_ADD_NS_AT") . " AS last_updated
                FROM T_ORDER 
                WHERE DELV_DATE = ? AND SUPPLIER_CODE = ? AND PART_NO = ?";
        
        $params = [$date, $supplier_code, $part_no];
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt !== false && sqlsrv_has_rows($stmt)) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            
            $response['success'] = true;
            $response['current_qty'] = intval($row['current_qty'] ?? 0);
            $response['remark'] = $row['remark'] ?? '';
            $response['last_by'] = $row['last_by'] ?? '';
            $response['last_updated'] = $row['last_updated'] ? 
                (is_string($row['last_updated']) ? $row['last_updated'] : $row['last_updated']->format('Y-m-d H:i:s')) : '';
            
            // Ambil data per jam dari T_UPDATE_BO
            $hour_condition = ($type === 'ds') ? 
                "HOUR BETWEEN 7 AND 20" : 
                "(HOUR BETWEEN 21 AND 23 OR HOUR BETWEEN 0 AND 6)";
            
            $hours_sql = "SELECT HOUR, SUM(TRAN_QTY) as qty
                         FROM T_UPDATE_BO 
                         WHERE DATE = ? 
                         AND PART_NO = ? 
                         AND $hour_condition
                         GROUP BY HOUR
                         ORDER BY HOUR";
            
            $hours_params = [$date, $part_no];
            $hours_stmt = sqlsrv_query($conn, $hours_sql, $hours_params);
            
            $hours_data = [];
            if ($hours_stmt !== false) {
                while ($hour_row = sqlsrv_fetch_array($hours_stmt, SQLSRV_FETCH_ASSOC)) {
                    $hour = intval($hour_row['HOUR']);
                    $qty = intval($hour_row['qty']);
                    if ($qty > 0) {
                        $hours_data[$hour] = $qty;
                    }
                }
            }
            
            $response['hours_data'] = $hours_data;
            $response['message'] = 'Data ditemukan';
            
        } else {
            $response['message'] = 'Tidak ada data add order';
            $response['success'] = true; // tetap success karena memang belum ada data
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}

echo json_encode($response);
exit;
?>
