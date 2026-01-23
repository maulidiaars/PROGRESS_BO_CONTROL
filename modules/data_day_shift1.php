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
        -- AMBIL MAX VALUE PER JAM (karena data akumulasi)
        MAX(CASE WHEN ub.HOUR = 8 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_08,
        MAX(CASE WHEN ub.HOUR = 9 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_09,
        MAX(CASE WHEN ub.HOUR = 10 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_10,
        MAX(CASE WHEN ub.HOUR = 11 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_11,
        MAX(CASE WHEN ub.HOUR = 12 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_12,
        MAX(CASE WHEN ub.HOUR = 13 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_13,
        MAX(CASE WHEN ub.HOUR = 14 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_14,
        MAX(CASE WHEN ub.HOUR = 15 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_15,
        MAX(CASE WHEN ub.HOUR = 16 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_16,
        MAX(CASE WHEN ub.HOUR = 17 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_17,
        MAX(CASE WHEN ub.HOUR = 18 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_18,
        MAX(CASE WHEN ub.HOUR = 19 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_19,
        MAX(CASE WHEN ub.HOUR = 20 THEN ub.TRAN_QTY ELSE 0 END) AS TRAN_20,
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
    WHERE ub.HOUR BETWEEN 8 AND 20
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
    
    $row['TRAN_08'] = intval($row['TRAN_08'] ?? 0);
    $row['TRAN_09'] = intval($row['TRAN_09'] ?? 0);
    $row['TRAN_10'] = intval($row['TRAN_10'] ?? 0);
    $row['TRAN_11'] = intval($row['TRAN_11'] ?? 0);
    $row['TRAN_12'] = intval($row['TRAN_12'] ?? 0);
    $row['TRAN_13'] = intval($row['TRAN_13'] ?? 0);
    $row['TRAN_14'] = intval($row['TRAN_14'] ?? 0);
    $row['TRAN_15'] = intval($row['TRAN_15'] ?? 0);
    $row['TRAN_16'] = intval($row['TRAN_16'] ?? 0);
    $row['TRAN_17'] = intval($row['TRAN_17'] ?? 0);
    $row['TRAN_18'] = intval($row['TRAN_18'] ?? 0);
    $row['TRAN_19'] = intval($row['TRAN_19'] ?? 0);
    $row['TRAN_20'] = intval($row['TRAN_20'] ?? 0);
    
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