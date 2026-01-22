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
        ISNULL([21], 0) AS [TRAN_21],
        ISNULL([22], 0) AS [TRAN_22],
        ISNULL([23], 0) AS [TRAN_23],
        ISNULL([0], 0) AS [TRAN_00],
        ISNULL([1], 0) AS [TRAN_01],
        ISNULL([2], 0) AS [TRAN_02],
        ISNULL([3], 0) AS [TRAN_03],
        ISNULL([4], 0) AS [TRAN_04],
        ISNULL([5], 0) AS [TRAN_05],
        ISNULL([6], 0) AS [TRAN_06],
        ISNULL([7], 0) AS [TRAN_07]
    FROM (
        SELECT 
            ub.DATE,
            ub.PART_NO,
            ub.PART_DESC,
            ub.HOUR,
            SUM(ub.TRAN_QTY) AS TRAN_QTY
        FROM T_UPDATE_BO ub
        WHERE (ub.HOUR BETWEEN 21 AND 23) OR (ub.HOUR BETWEEN 0 AND 7)
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
        FOR HOUR IN ([21], [22], [23], [0], [1], [2], [3], [4], [5], [6], [7])
    ) AS PivotTable
),
ORD_DATA AS (
    SELECT 
        DELV_DATE,
        PART_NO,
        PART_NAME,
        SUPPLIER_CODE,
        ISNULL([21], 0) AS [ORD_21],
        ISNULL([22], 0) AS [ORD_22],
        ISNULL([23], 0) AS [ORD_23],
        ISNULL([0], 0) AS [ORD_00],
        ISNULL([1], 0) AS [ORD_01],
        ISNULL([2], 0) AS [ORD_02],
        ISNULL([3], 0) AS [ORD_03],
        ISNULL([4], 0) AS [ORD_04],
        ISNULL([5], 0) AS [ORD_05],
        ISNULL([6], 0) AS [ORD_06],
        ISNULL([7], 0) AS [ORD_07]
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
        AND (
            TRY_CAST(ETA AS TIME) >= '21:00:00' 
            OR TRY_CAST(ETA AS TIME) <= '07:00:00'
        )
        GROUP BY DELV_DATE, PART_NO, PART_NAME, SUPPLIER_CODE, CAST(LEFT(ETA, 2) AS INT)
    ) AS SourceTable
    PIVOT (
        SUM(ORD_QTY)
        FOR ETA_HOUR IN ([21], [22], [23], [0], [1], [2], [3], [4], [5], [6], [7])
    ) AS PivotTable
)
SELECT 
    COALESCE(T.DATE, O.DELV_DATE) AS DATE,
    COALESCE(T.PART_NO, O.PART_NO) AS PART_NO,
    COALESCE(T.PART_DESC, O.PART_NAME) AS PART_DESC,
    O.SUPPLIER_CODE,
    ISNULL(T.[TRAN_21], 0) AS TRAN_21, ISNULL(O.[ORD_21], 0) AS ORD_21,
    ISNULL(T.[TRAN_22], 0) AS TRAN_22, ISNULL(O.[ORD_22], 0) AS ORD_22,
    ISNULL(T.[TRAN_23], 0) AS TRAN_23, ISNULL(O.[ORD_23], 0) AS ORD_23,
    ISNULL(T.[TRAN_00], 0) AS TRAN_00, ISNULL(O.[ORD_00], 0) AS ORD_00,
    ISNULL(T.[TRAN_01], 0) AS TRAN_01, ISNULL(O.[ORD_01], 0) AS ORD_01,
    ISNULL(T.[TRAN_02], 0) AS TRAN_02, ISNULL(O.[ORD_02], 0) AS ORD_02,
    ISNULL(T.[TRAN_03], 0) AS TRAN_03, ISNULL(O.[ORD_03], 0) AS ORD_03,
    ISNULL(T.[TRAN_04], 0) AS TRAN_04, ISNULL(O.[ORD_04], 0) AS ORD_04,
    ISNULL(T.[TRAN_05], 0) AS TRAN_05, ISNULL(O.[ORD_05], 0) AS ORD_05,
    ISNULL(T.[TRAN_06], 0) AS TRAN_06, ISNULL(O.[ORD_06], 0) AS ORD_06,
    ISNULL(T.[TRAN_07], 0) AS TRAN_07, ISNULL(O.[ORD_07], 0) AS ORD_07
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