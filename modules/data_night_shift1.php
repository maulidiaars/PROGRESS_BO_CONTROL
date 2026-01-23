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
        SUM(CASE WHEN ub.HOUR = 21 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_21,
        SUM(CASE WHEN ub.HOUR = 22 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_22,
        SUM(CASE WHEN ub.HOUR = 23 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_23,
        SUM(CASE WHEN ub.HOUR = 0 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_00,
        SUM(CASE WHEN ub.HOUR = 1 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_01,
        SUM(CASE WHEN ub.HOUR = 2 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_02,
        SUM(CASE WHEN ub.HOUR = 3 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_03,
        SUM(CASE WHEN ub.HOUR = 4 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_04,
        SUM(CASE WHEN ub.HOUR = 5 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_05,
        SUM(CASE WHEN ub.HOUR = 6 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_06,
        SUM(CASE WHEN ub.HOUR = 7 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_07
    FROM T_UPDATE_BO ub
    FULL OUTER JOIN T_ORDER o 
        ON ub.PART_NO = o.PART_NO AND ub.DATE = o.DELV_DATE
    WHERE ((ub.HOUR BETWEEN 21 AND 23) OR (ub.HOUR BETWEEN 0 AND 7) OR ub.HOUR IS NULL)
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