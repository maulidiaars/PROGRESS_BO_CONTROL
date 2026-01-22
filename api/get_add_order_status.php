<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

$response = [
    'success' => false,
    'current_qty' => 0,
    'remark' => '',
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
        $sql = "SELECT 
                " . ($type === 'ds' ? "ADD_DS" : "ADD_NS") . " AS current_qty,
                " . ($type === 'ds' ? "REMARK_DS" : "REMARK_NS") . " AS remark
                FROM T_ORDER 
                WHERE DELV_DATE = ? AND SUPPLIER_CODE = ? AND PART_NO = ?";
        
        $params = [$date, $supplier_code, $part_no];
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt !== false && sqlsrv_has_rows($stmt)) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            
            $response['success'] = true;
            $response['current_qty'] = intval($row['current_qty'] ?? 0);
            $response['remark'] = $row['remark'] ?? '';
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