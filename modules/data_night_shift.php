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
$supplier_code = $_GET["supplier_code"] ?? '';

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
        WHERE ((ub.HOUR BETWEEN 21 AND 23) OR (ub.HOUR BETWEEN 0 AND 7))
        AND EXISTS (
            SELECT 1 FROM T_ORDER o 
            WHERE o.DELV_DATE = ub.DATE 
            AND (
                o.PART_NO = ub.PART_NO
                OR REPLACE(o.PART_NO, ' ', '') = REPLACE(ub.PART_NO, ' ', '')
                OR UPPER(RTRIM(LTRIM(o.PART_NO))) = UPPER(RTRIM(LTRIM(ub.PART_NO)))
            )
        )
        AND ub.DATE BETWEEN '$DATE1' AND '$DATE2'
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
            SUM(CASE 
                WHEN TRY_CAST(ETA AS TIME) BETWEEN '21:00:00' AND '21:59:59' THEN ORD_QTY 
                ELSE 0 
            END) AS [21],
            SUM(CASE 
                WHEN TRY_CAST(ETA AS TIME) BETWEEN '22:00:00' AND '22:59:59' THEN ORD_QTY 
                ELSE 0 
            END) AS [22],
            SUM(CASE 
                WHEN TRY_CAST(ETA AS TIME) BETWEEN '23:00:00' AND '23:59:59' THEN ORD_QTY 
                ELSE 0 
            END) AS [23],
            SUM(CASE 
                WHEN TRY_CAST(ETA AS TIME) BETWEEN '00:00:00' AND '00:59:59' THEN ORD_QTY 
                ELSE 0 
            END) AS [0],
            SUM(CASE 
                WHEN TRY_CAST(ETA AS TIME) BETWEEN '01:00:00' AND '01:59:59' THEN ORD_QTY 
                ELSE 0 
            END) AS [1],
            SUM(CASE 
                WHEN TRY_CAST(ETA AS TIME) BETWEEN '02:00:00' AND '02:59:59' THEN ORD_QTY 
                ELSE 0 
            END) AS [2],
            SUM(CASE 
                WHEN TRY_CAST(ETA AS TIME) BETWEEN '03:00:00' AND '03:59:59' THEN ORD_QTY 
                ELSE 0 
            END) AS [3],
            SUM(CASE 
                WHEN TRY_CAST(ETA AS TIME) BETWEEN '04:00:00' AND '04:59:59' THEN ORD_QTY 
                ELSE 0 
            END) AS [4],
            SUM(CASE 
                WHEN TRY_CAST(ETA AS TIME) BETWEEN '05:00:00' AND '05:59:59' THEN ORD_QTY 
                ELSE 0 
            END) AS [5],
            SUM(CASE 
                WHEN TRY_CAST(ETA AS TIME) BETWEEN '06:00:00' AND '06:59:59' THEN ORD_QTY 
                ELSE 0 
            END) AS [6],
            SUM(CASE 
                WHEN TRY_CAST(ETA AS TIME) BETWEEN '07:00:00' AND '07:59:59' THEN ORD_QTY 
                ELSE 0 
            END) AS [7]
        FROM T_ORDER
        WHERE ETA IS NOT NULL 
        AND ETA != ''
        AND TRY_CAST(ETA AS TIME) IS NOT NULL
        AND (
            TRY_CAST(ETA AS TIME) >= '21:00:00' 
            OR TRY_CAST(ETA AS TIME) <= '07:00:00'
        )
        AND DELV_DATE BETWEEN '$DATE1' AND '$DATE2'
        GROUP BY DELV_DATE, PART_NO, PART_NAME, SUPPLIER_CODE
    ) AS SourceTable
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
FROM ORD_DATA O
LEFT JOIN TRAN_DATA T
    ON T.PART_NO = O.PART_NO AND T.DATE = O.DELV_DATE
WHERE O.DELV_DATE BETWEEN '$DATE1' AND '$DATE2'
AND O.SUPPLIER_CODE IS NOT NULL 
AND O.SUPPLIER_CODE != ''";

// Tambahkan filter supplier_code jika ada
if (!empty($supplier_code)) {
    $supplier_codes = explode(',', $supplier_code);
    if (count($supplier_codes) > 0) {
        $placeholders = implode(',', array_fill(0, count($supplier_codes), '?'));
        $sql .= " AND O.SUPPLIER_CODE IN ($placeholders)";
        
        // Tambahkan parameter
        foreach ($supplier_codes as $code) {
            $params[] = trim($code);
        }
    }
}

$sql .= " ORDER BY O.DELV_DATE, O.SUPPLIER_CODE, O.PART_NO";

// Update query execution untuk menggunakan params
if (isset($params) && !empty($params)) {
    $stmt = sqlsrv_query($conn, $sql, $params);
} else {
    $stmt = sqlsrv_query($conn, $sql);
}

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