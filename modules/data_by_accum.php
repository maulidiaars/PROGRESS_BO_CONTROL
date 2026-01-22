<?php  
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/status_logic.php'; // INCLUDE BARU

if (!$conn) {
    echo json_encode([
        "success" => false,
        "data" => [],
        "message" => "Database belum terkoneksi"
    ]);
    exit;
}

try {
    if (isset($_GET['month'])) {  
        $bulan = $_GET['month'];
    } else {  
        $bulan = date('Y-m');
    }  
    
    if (!preg_match('/^\d{4}-\d{2}$/', $bulan)) {
        throw new Exception('Format bulan tidak valid. Gunakan format: YYYY-MM');
    }
    
    list($tahun, $bln) = explode('-', $bulan);  
    $delvDateStart = $tahun . $bln . "01";  
    $delvDateEnd   = $tahun . $bln . "31";  
    
    // **QUERY YANG LEBIH SEDERHANA DAN AMAN**
    $sql = "
    SELECT 
        o.DELV_DATE,
        o.SUPPLIER_CODE,
        o.SUPPLIER_NAME,
        o.PART_NO,
        o.PART_NAME,
        ISNULL(SUM(o.ORD_QTY), 0) AS REGULAR_ORDER,
        ISNULL(MAX(o.ADD_DS), 0) AS ADD_DS,
        ISNULL(MAX(o.ADD_NS), 0) AS ADD_NS
    FROM T_ORDER o
    WHERE o.DELV_DATE >= ? AND o.DELV_DATE <= ?
    GROUP BY 
        o.DELV_DATE,
        o.SUPPLIER_CODE,
        o.SUPPLIER_NAME,
        o.PART_NO,
        o.PART_NAME
    ORDER BY o.DELV_DATE, o.SUPPLIER_CODE, o.PART_NO
    ";
    
    $params = array($delvDateStart, $delvDateEnd);  
    $stmt = sqlsrv_query($conn, $sql, $params);  
    
    if($stmt === false){  
        $errors = sqlsrv_errors();
        error_log("SQL Error in data_by_accum.php: " . print_r($errors, true));
        throw new Exception('Query database gagal: ' . ($errors[0]['message'] ?? 'Unknown error'));
    }  
    
    $data = [];  
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){  
        // Format DELV_DATE
        if (isset($row['DELV_DATE'])) {
            if ($row['DELV_DATE'] instanceof DateTime) {  
                $row['DELV_DATE'] = $row['DELV_DATE']->format('Ymd');  
            } else {
                $row['DELV_DATE'] = strval($row['DELV_DATE']);
            }
        }
        
        // Pastikan tipe data integer
        $row['REGULAR_ORDER'] = intval($row['REGULAR_ORDER'] ?? 0);
        $row['ADD_DS'] = intval($row['ADD_DS'] ?? 0);
        $row['ADD_NS'] = intval($row['ADD_NS'] ?? 0);
        $row['TOTAL_ORDER'] = $row['REGULAR_ORDER'] + $row['ADD_DS'] + $row['ADD_NS'];
        $row['TOTAL_INCOMING'] = 0; // Akan dihitung di client
        
        $data[] = $row;  
    }  
    
    sqlsrv_free_stmt($stmt);
    
    echo json_encode([
        "success" => true,
        "data" => $data,
        "message" => "Data loaded successfully",
        "count" => count($data),
        "month" => $bulan
    ]);  
    
} catch (Exception $e) {
    http_response_code(500);  
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => [],
        'count' => 0
    ]);  
    exit;  
}

sqlsrv_close($conn);
?>