<?php 
if (ob_get_length()) ob_clean();

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/status_logic.php';

if (!$conn) {
    echo json_encode([
        "success" => false,
        "data" => [],
        "message" => "Database belum terkoneksi",
        "count" => 0
    ]);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 0);

$response = [
    "success" => false,
    "data" => [],
    "message" => "Unknown error",
    "count" => 0
];

try {
    $date1 = isset($_GET['date1']) ? trim($_GET['date1']) : '';  
    $date2 = isset($_GET['date2']) ? trim($_GET['date2']) : '';  
    $supplier_code = isset($_GET['supplier_code']) ? trim($_GET['supplier_code']) : '';  
    
    if ($conn === false) {
        throw new Exception('Database connection failed');
    }

    $date1 = ($date1 !== '' && is_numeric($date1)) ? intval($date1) : '';
    $date2 = ($date2 !== '' && is_numeric($date2)) ? intval($date2) : '';

    // QUERY YANG DIPERBAIKI: GROUP BY TANPA ETA, AGREGASI SEMUA CYCLE
    $sql = "SELECT
        CONVERT(varchar, o.DELV_DATE) AS DATE,  
        ISNULL(o.SUPPLIER_CODE, '') AS SUPPLIER_CODE,  
        ISNULL(o.PART_NO, '') AS PART_NO,  
        ISNULL(o.PART_NAME, '') AS PART_NAME,  
        
        -- Total semua order (reguler) tanpa memperhatikan ETA
        SUM(ISNULL(o.ORD_QTY, 0)) AS TOTAL_REGULAR_ORDER,
        
        -- REGULER DS: total order dengan ETA di DAY SHIFT (07:00 - 20:00)
        SUM(CASE 
            WHEN o.ETA IS NOT NULL 
            AND o.ETA != ''
            AND (
                -- Format jam normal: 07:00 - 20:00
                (TRY_CAST(o.ETA AS TIME) >= '07:00:00' AND TRY_CAST(o.ETA AS TIME) <= '20:00:00') OR
                -- Handle format string 24 jam
                (TRY_CAST(LEFT(o.ETA, 2) AS INT) BETWEEN 7 AND 20)
            )
            THEN ISNULL(o.ORD_QTY, 0) 
            ELSE 0 
        END) AS REGULER_DS,  

        -- REGULER NS: total order dengan ETA di NIGHT SHIFT (21:00 - 06:00)
        SUM(CASE 
            WHEN o.ETA IS NOT NULL 
            AND o.ETA != ''
            AND (
                -- Jam 21:00 - 23:59
                (TRY_CAST(o.ETA AS TIME) >= '21:00:00' AND TRY_CAST(o.ETA AS TIME) <= '23:59:59') OR
                -- Jam 00:00 - 06:59
                (TRY_CAST(o.ETA AS TIME) >= '00:00:00' AND TRY_CAST(o.ETA AS TIME) <= '06:59:59') OR
                -- Handle format string
                (TRY_CAST(LEFT(o.ETA, 2) AS INT) >= 21 OR TRY_CAST(LEFT(o.ETA, 2) AS INT) <= 6)
            )
            THEN ISNULL(o.ORD_QTY, 0) 
            ELSE 0 
        END) AS REGULER_NS,
        
        -- ADD orders: ambil MAX karena hanya 1 nilai per part per tanggal
        ISNULL(MAX(o.ADD_DS), 0) AS ADD_DS,
        ISNULL(MAX(o.ADD_NS), 0) AS ADD_NS,
        
        -- Remarks: ambil MAX/pertama
        MAX(ISNULL(o.REMARK_DS, '')) AS REMARK_DS,
        MAX(ISNULL(o.REMARK_NS, '')) AS REMARK_NS,
        
        -- ETA: untuk perhitungan status, ambil ETA pertama
        MAX(o.ETA) AS ETA,
        

        -- Actual incoming (REAL-TIME QUERY) - PAKAI MAX BUKAN SUM!
        ISNULL((
            SELECT MAX(ub.TRAN_QTY)  -- PAKAI MAX, BUKAN SUM!
            FROM T_UPDATE_BO ub 
            WHERE (
                ub.PART_NO = o.PART_NO 
                OR REPLACE(ub.PART_NO, ' ', '') = REPLACE(o.PART_NO, ' ', '')
            )
            AND ub.DATE = o.DELV_DATE
            AND ub.HOUR BETWEEN 7 AND 20
        ), 0) AS DS_ACTUAL,

        ISNULL((
            SELECT MAX(ub.TRAN_QTY)  -- PAKAI MAX, BUKAN SUM!
            FROM T_UPDATE_BO ub 
            WHERE (
                ub.PART_NO = o.PART_NO 
                OR REPLACE(ub.PART_NO, ' ', '') = REPLACE(o.PART_NO, ' ', '')
            )
            AND ub.DATE = o.DELV_DATE
            AND (ub.HOUR BETWEEN 21 AND 23 OR ub.HOUR BETWEEN 0 AND 6)
        ), 0) AS NS_ACTUAL
        
    FROM T_ORDER o  
    WHERE 1=1";

    $params = array();

    if ($date1 !== '' && $date2 !== '') {  
        $sql .= " AND o.DELV_DATE BETWEEN ? AND ?";
        $params[] = $date1;
        $params[] = $date2;
    } else if ($date1 !== '') {  
        $sql .= " AND o.DELV_DATE = ?";
        $params[] = $date1;
    }

    if ($supplier_code !== '' && $supplier_code !== 'select-all') {  
        $supplier_codes = explode(',', $supplier_code);
        $placeholders = implode(',', array_fill(0, count($supplier_codes), '?'));
        $sql .= " AND o.SUPPLIER_CODE IN ($placeholders)";
        
        foreach ($supplier_codes as $code) {
            $params[] = trim($code);
        }
    }

    // PERHATIAN: GROUP BY TANPA ETA!
    $sql .= " GROUP BY o.DELV_DATE, o.SUPPLIER_CODE, o.PART_NO, o.PART_NAME
              ORDER BY o.DELV_DATE DESC, o.SUPPLIER_CODE, o.PART_NO";

    $stmt = sqlsrv_query($conn, $sql, $params);
    $data = [];

    if ($stmt === false) {
        $errors = sqlsrv_errors();
        error_log("SQL Error: " . print_r($errors, true));
        
        $response["message"] = "Database query failed: " . ($errors[0]['message'] ?? 'Unknown error');
        $response["error"] = true;
        echo json_encode($response);
        exit;
    }

    $rowCount = 0;
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Format data dasar
        $row['DATE'] = isset($row['DATE']) ? strval($row['DATE']) : '';
        $row['TOTAL_REGULAR_ORDER'] = intval($row['TOTAL_REGULAR_ORDER'] ?? 0);
        $row['REGULER_DS'] = intval($row['REGULER_DS'] ?? 0);
        $row['REGULER_NS'] = intval($row['REGULER_NS'] ?? 0);
        $row['ADD_DS'] = intval($row['ADD_DS'] ?? 0);
        $row['ADD_NS'] = intval($row['ADD_NS'] ?? 0);
        $row['DS_ACTUAL'] = intval($row['DS_ACTUAL'] ?? 0);
        $row['NS_ACTUAL'] = intval($row['NS_ACTUAL'] ?? 0);
        
        // Hitung total order (reguler + add)
        $row['ORD_QTY_TOTAL'] = $row['TOTAL_REGULAR_ORDER'] + $row['ADD_DS'] + $row['ADD_NS'];
        $row['TOTAL_INCOMING'] = $row['DS_ACTUAL'] + $row['NS_ACTUAL'];
        
        // ========== PAKE FUNGSI STATUS UNIVERSAL ==========
        $row['STATUS'] = calculateOrderStatus(
            $row['DATE'],           // tanggal order
            $row['ETA'],            // ETA (ambil yang pertama)
            $row['REGULER_DS'],     // DS reguler
            $row['ADD_DS'],         // DS add
            $row['REGULER_NS'],     // NS reguler
            $row['ADD_NS'],         // NS add
            $row['DS_ACTUAL'],      // actual DS
            $row['NS_ACTUAL']       // actual NS
        );
        
        $row['REMARK_DS'] = isset($row['REMARK_DS']) ? strval($row['REMARK_DS']) : '';
        $row['REMARK_NS'] = isset($row['REMARK_NS']) ? strval($row['REMARK_NS']) : '';
        
        $data[] = $row;
        $rowCount++;
    }

    sqlsrv_free_stmt($stmt);

    error_log("Data returned: " . $rowCount . " rows (WITH GROUPING FIX - NO DUPLICATE)");
    
    $response["success"] = true;
    $response["data"] = $data;
    $response["message"] = "Data loaded successfully";
    $response["count"] = $rowCount;
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    
    $response["message"] = $e->getMessage();
    $response["error"] = true;
    
    echo json_encode($response);
}

sqlsrv_close($conn);
exit;
?>