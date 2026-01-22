<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/status_logic.php';

if (!$conn) {
    echo json_encode([
        "success" => false,
        "data" => [],
        "message" => "Database belum terkoneksi"
    ]);
    exit;
}

$DATE1 = $_GET["date1"] ?? date('Ymd');
$DATE2 = $_GET["date2"] ?? date('Ymd');

$sql = "
WITH TRAN_DATA AS (
    SELECT 
        DATE,
        PART_NO,
        PART_DESC,
        ISNULL([8], 0) AS [TRAN_08],
        ISNULL([9], 0) AS [TRAN_09],
        ISNULL([10], 0) AS [TRAN_10],
        ISNULL([11], 0) AS [TRAN_11],
        ISNULL([12], 0) AS [TRAN_12],
        ISNULL([13], 0) AS [TRAN_13],
        ISNULL([14], 0) AS [TRAN_14],
        ISNULL([15], 0) AS [TRAN_15],
        ISNULL([16], 0) AS [TRAN_16],
        ISNULL([17], 0) AS [TRAN_17],
        ISNULL([18], 0) AS [TRAN_18],
        ISNULL([19], 0) AS [TRAN_19],
        ISNULL([20], 0) AS [TRAN_20]
    FROM (
        SELECT 
            ub.DATE,
            ub.PART_NO,
            ub.PART_DESC,
            ub.HOUR,
            SUM(ub.TRAN_QTY) AS TRAN_QTY
        FROM T_UPDATE_BO ub
        WHERE ub.HOUR BETWEEN 8 AND 20
        -- FILTER: HANYA PART YANG ADA DI ORDER
        AND EXISTS (
            SELECT 1 FROM T_ORDER o 
            WHERE o.PART_NO = ub.PART_NO 
            AND o.DELV_DATE = ub.DATE
        )
        GROUP BY ub.DATE, ub.PART_NO, ub.PART_DESC, ub.HOUR
    ) AS SourceTable
    PIVOT (
        SUM(TRAN_QTY)
        FOR HOUR IN ([8], [9], [10], [11], [12], [13], [14], [15], [16], [17], [18], [19], [20])
    ) AS PivotTable
),
ORD_DATA AS (
    SELECT 
        DELV_DATE,
        PART_NO,
        PART_NAME,
        SUPPLIER_CODE,
        ISNULL([8], 0) AS [ORD_08],
        ISNULL([9], 0) AS [ORD_09],
        ISNULL([10], 0) AS [ORD_10],
        ISNULL([11], 0) AS [ORD_11],
        ISNULL([12], 0) AS [ORD_12],
        ISNULL([13], 0) AS [ORD_13],
        ISNULL([14], 0) AS [ORD_14],
        ISNULL([15], 0) AS [ORD_15],
        ISNULL([16], 0) AS [ORD_16],
        ISNULL([17], 0) AS [ORD_17],
        ISNULL([18], 0) AS [ORD_18],
        ISNULL([19], 0) AS [ORD_19],
        ISNULL([20], 0) AS [ORD_20]
    FROM (
        SELECT 
            DELV_DATE,
            PART_NO,
            PART_NAME,
            SUPPLIER_CODE,
            CAST(LEFT(ETA, 2) AS INT) AS ETA_HOUR,
            SUM(ORD_QTY) AS ORD_QTY
        FROM T_ORDER
        WHERE ETA IS NOT NULL 
        AND ETA != ''
        AND TRY_CAST(ETA AS TIME) IS NOT NULL
        AND TRY_CAST(ETA AS TIME) BETWEEN '08:00:00' AND '20:00:00'
        GROUP BY DELV_DATE, PART_NO, PART_NAME, SUPPLIER_CODE, CAST(LEFT(ETA, 2) AS INT)
    ) AS SourceTable
    PIVOT (
        SUM(ORD_QTY)
        FOR ETA_HOUR IN ([8], [9], [10], [11], [12], [13], [14], [15], [16], [17], [18], [19], [20])
    ) AS PivotTable
)
SELECT 
    COALESCE(T.DATE, O.DELV_DATE) AS DATE,
    COALESCE(T.PART_NO, O.PART_NO) AS PART_NO,
    COALESCE(T.PART_DESC, O.PART_NAME) AS PART_DESC,
    O.SUPPLIER_CODE,
    ISNULL(T.[TRAN_08], 0) AS TRAN_08, ISNULL(O.[ORD_08], 0) AS ORD_08,
    ISNULL(T.[TRAN_09], 0) AS TRAN_09, ISNULL(O.[ORD_09], 0) AS ORD_09,
    ISNULL(T.[TRAN_10], 0) AS TRAN_10, ISNULL(O.[ORD_10], 0) AS ORD_10,
    ISNULL(T.[TRAN_11], 0) AS TRAN_11, ISNULL(O.[ORD_11], 0) AS ORD_11,
    ISNULL(T.[TRAN_12], 0) AS TRAN_12, ISNULL(O.[ORD_12], 0) AS ORD_12,
    ISNULL(T.[TRAN_13], 0) AS TRAN_13, ISNULL(O.[ORD_13], 0) AS ORD_13,
    ISNULL(T.[TRAN_14], 0) AS TRAN_14, ISNULL(O.[ORD_14], 0) AS ORD_14,
    ISNULL(T.[TRAN_15], 0) AS TRAN_15, ISNULL(O.[ORD_15], 0) AS ORD_15,
    ISNULL(T.[TRAN_16], 0) AS TRAN_16, ISNULL(O.[ORD_16], 0) AS ORD_16,
    ISNULL(T.[TRAN_17], 0) AS TRAN_17, ISNULL(O.[ORD_17], 0) AS ORD_17,
    ISNULL(T.[TRAN_18], 0) AS TRAN_18, ISNULL(O.[ORD_18], 0) AS ORD_18,
    ISNULL(T.[TRAN_19], 0) AS TRAN_19, ISNULL(O.[ORD_19], 0) AS ORD_19,
    ISNULL(T.[TRAN_20], 0) AS TRAN_20, ISNULL(O.[ORD_20], 0) AS ORD_20
FROM TRAN_DATA T
FULL OUTER JOIN ORD_DATA O
    ON T.PART_NO = O.PART_NO AND T.DATE = O.DELV_DATE
WHERE COALESCE(T.DATE, O.DELV_DATE) BETWEEN '$DATE1' AND '$DATE2'
ORDER BY COALESCE(T.DATE, O.DELV_DATE), O.SUPPLIER_CODE, COALESCE(T.PART_NO, O.PART_NO)";

$stmt = sqlsrv_query($conn, $sql);
$data = array();

if ($stmt === false) {
    echo json_encode([
        "success" => false,
        "data" => [],
        "message" => "Query error: " . print_r(sqlsrv_errors(), true)
    ]);
    exit;
}

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $data[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode([
    "success" => true,
    "data" => $data,
    "count" => count($data)
]);
?>