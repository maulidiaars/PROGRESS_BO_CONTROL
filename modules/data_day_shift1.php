<?php  
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/status_logic.php';

$response = [
    "success" => true,
    "data" => []
];

if (!$conn) {
    $response["success"] = false;
    $response["message"] = "Database belum terkoneksi";
    echo json_encode($response);
    exit;
}

// **PERBAIKAN: TERIMA PARAMETER DATE RANGE**
$date1 = isset($_GET['date1']) ? $_GET['date1'] : date('Ymd');
$date2 = isset($_GET['date2']) ? $_GET['date2'] : date('Ymd');

$sql = "
    SELECT 
        COALESCE(ub.DATE, o.DELV_DATE) AS DATE,
        COALESCE(ub.PART_NO, o.PART_NO) AS PART_NO,
        COALESCE(ub.PART_DESC, o.PART_NAME) AS PART_DESC,
        o.SUPPLIER_CODE,
        SUM(CASE WHEN ub.HOUR = 8 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_08,
        SUM(CASE WHEN ub.HOUR = 9 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_09,
        SUM(CASE WHEN ub.HOUR = 10 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_10,
        SUM(CASE WHEN ub.HOUR = 11 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_11,
        SUM(CASE WHEN ub.HOUR = 12 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_12,
        SUM(CASE WHEN ub.HOUR = 13 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_13,
        SUM(CASE WHEN ub.HOUR = 14 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_14,
        SUM(CASE WHEN ub.HOUR = 15 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_15,
        SUM(CASE WHEN ub.HOUR = 16 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_16,
        SUM(CASE WHEN ub.HOUR = 17 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_17,
        SUM(CASE WHEN ub.HOUR = 18 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_18,
        SUM(CASE WHEN ub.HOUR = 19 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_19,
        SUM(CASE WHEN ub.HOUR = 20 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_20
    FROM T_UPDATE_BO ub
    FULL OUTER JOIN T_ORDER o 
        ON ub.PART_NO = o.PART_NO AND ub.DATE = o.DELV_DATE
    WHERE (ub.HOUR BETWEEN 8 AND 20 OR ub.HOUR IS NULL)
    AND COALESCE(ub.DATE, o.DELV_DATE) BETWEEN ? AND ?
    -- FILTER: Hanya ambil yang ada di order
    AND EXISTS (
        SELECT 1 FROM T_ORDER o2 
        WHERE o2.PART_NO = COALESCE(ub.PART_NO, o.PART_NO)
        AND o2.DELV_DATE = COALESCE(ub.DATE, o.DELV_DATE)
    )
    GROUP BY COALESCE(ub.DATE, o.DELV_DATE), 
             COALESCE(ub.PART_NO, o.PART_NO), 
             COALESCE(ub.PART_DESC, o.PART_NAME),
             o.SUPPLIER_CODE
";  

$params = [$date1, $date2];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $response["success"] = false;
    $response["message"] = "Query gagal: " . print_r(sqlsrv_errors(), true);
    echo json_encode($response);
    exit;
}

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $response["data"][] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode($response);
?>