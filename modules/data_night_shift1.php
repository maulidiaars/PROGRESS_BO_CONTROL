<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$response = [
    "success" => true,
    "data" => [],
    "total_incoming" => 0
];

if (!$conn) {
    $response["success"] = false;
    $response["message"] = "Database belum terkoneksi";
    echo json_encode($response);
    exit;
}

$date1 = isset($_GET['date1']) ? $_GET['date1'] : date('Ymd');
$date2 = isset($_GET['date2']) ? $_GET['date2'] : date('Ymd');

// **PAKAI MAX() UNTUK AMBIL DATA TERAKHIR PER JAM**
$sql = "
    SELECT 
        ub.DATE,
        ub.PART_NO,
        ub.PART_DESC,
        o.SUPPLIER_CODE,
        o.PART_NAME,
        -- AMBIL MAX VALUE PER JAM
        MAX(CASE WHEN ub.HOUR = 21 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_21,
        MAX(CASE WHEN ub.HOUR = 22 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_22,
        MAX(CASE WHEN ub.HOUR = 23 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_23,
        MAX(CASE WHEN ub.HOUR = 0 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_00,
        MAX(CASE WHEN ub.HOUR = 1 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_01,
        MAX(CASE WHEN ub.HOUR = 2 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_02,
        MAX(CASE WHEN ub.HOUR = 3 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_03,
        MAX(CASE WHEN ub.HOUR = 4 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_04,
        MAX(CASE WHEN ub.HOUR = 5 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_05,
        MAX(CASE WHEN ub.HOUR = 6 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_06,
        MAX(CASE WHEN ub.HOUR = 7 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_07,
        -- TOTAL: AMBIL NILAI MAKSIMAL DARI SEMUA JAM
        MAX(ub.TRAN_QTY) AS TOTAL_INCOMING
    FROM T_UPDATE_BO ub
    INNER JOIN T_ORDER o ON (
        o.DELV_DATE = ub.DATE
        AND (
            o.PART_NO = ub.PART_NO
            OR REPLACE(o.PART_NO, ' ', '') = REPLACE(ub.PART_NO, ' ', '')
            OR UPPER(RTRIM(LTRIM(o.PART_NO))) = UPPER(RTRIM(LTRIM(ub.PART_NO)))
        )
    )
    WHERE (ub.HOUR BETWEEN 21 AND 23 OR ub.HOUR BETWEEN 0 AND 7)
    AND ub.DATE BETWEEN ? AND ?
    AND o.SUPPLIER_CODE IS NOT NULL
    AND o.SUPPLIER_CODE != ''
    GROUP BY ub.DATE, ub.PART_NO, ub.PART_DESC, o.SUPPLIER_CODE, o.PART_NAME
    ORDER BY ub.DATE DESC, o.SUPPLIER_CODE, ub.PART_NO
";

$params = [$date1, $date2];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $response["success"] = false;
    $response["message"] = "Query gagal: " . print_r(sqlsrv_errors(), true);
    echo json_encode($response);
    exit;
}

$totalIncoming = 0;
$rowCount = 0;

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $row['DATE'] = isset($row['DATE']) ? strval($row['DATE']) : '';
    $row['SUPPLIER_CODE'] = isset($row['SUPPLIER_CODE']) ? strval($row['SUPPLIER_CODE']) : '';
    $row['PART_NO'] = isset($row['PART_NO']) ? strval($row['PART_NO']) : '';
    
    $row['TRAN_21'] = intval($row['TRAN_21'] ?? 0);
    $row['TRAN_22'] = intval($row['TRAN_22'] ?? 0);
    $row['TRAN_23'] = intval($row['TRAN_23'] ?? 0);
    $row['TRAN_00'] = intval($row['TRAN_00'] ?? 0);
    $row['TRAN_01'] = intval($row['TRAN_01'] ?? 0);
    $row['TRAN_02'] = intval($row['TRAN_02'] ?? 0);
    $row['TRAN_03'] = intval($row['TRAN_03'] ?? 0);
    $row['TRAN_04'] = intval($row['TRAN_04'] ?? 0);
    $row['TRAN_05'] = intval($row['TRAN_05'] ?? 0);
    $row['TRAN_06'] = intval($row['TRAN_06'] ?? 0);
    $row['TRAN_07'] = intval($row['TRAN_07'] ?? 0);
    
    $row['TOTAL_INCOMING'] = intval($row['TOTAL_INCOMING'] ?? 0);
    $totalIncoming += $row['TOTAL_INCOMING'];
    
    $response["data"][] = $row;
    $rowCount++;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

$response['total_incoming'] = $totalIncoming;
$response['count'] = $rowCount;

echo json_encode($response);
?>