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
    'last_by' => '',
    'last_updated' => '',
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
            
            // Format datetime
            if ($row['last_updated']) {
                if (is_string($row['last_updated'])) {
                    $response['last_updated'] = $row['last_updated'];
                } else if ($row['last_updated'] instanceof DateTime) {
                    $response['last_updated'] = $row['last_updated']->format('Y-m-d H:i:s');
                }
            }
            
            // Untuk add order, kita tidak menyimpan per jam di T_UPDATE_BO
            // Jadi hours_data dikosongkan atau bisa diisi dengan default
            $response['hours_data'] = [];
            $response['message'] = 'Data add order ditemukan';
            
        } else {
            // Tidak ada data add order (bukan error)
            $response['success'] = true;
            $response['current_qty'] = 0;
            $response['remark'] = '';
            $response['message'] = 'Tidak ada data add order';
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Parameter tidak lengkap';
}

echo json_encode($response);
exit;
?>