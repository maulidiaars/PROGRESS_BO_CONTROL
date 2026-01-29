<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$response = [
    "success" => true,
    "data" => [],
    "message" => "",
    "logic" => "TAMPIL SEMUA ORDER NIGHT SHIFT (21-07) TERIMA SEMUA ETA"
];

if (!$conn) {
    $response["success"] = false;
    $response["message"] = "Database belum terkoneksi";
    echo json_encode($response);
    exit;
}

$date1 = isset($_GET['date1']) ? $_GET['date1'] : date('Ymd');
$date2 = isset($_GET['date2']) ? $_GET['date2'] : date('Ymd');

$sql = "
WITH AllNightShiftOrders AS (
    SELECT 
        DELV_DATE as DATE,
        PART_NO,
        PART_NAME,
        SUPPLIER_CODE,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 21
                OR 
                TRY_CAST(ETA AS TIME) >= '21:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '21:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_21,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 22
                OR 
                TRY_CAST(ETA AS TIME) >= '22:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '22:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_22,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 23
                OR 
                TRY_CAST(ETA AS TIME) >= '23:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '23:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_23,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 0
                OR 
                TRY_CAST(ETA AS TIME) >= '00:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '00:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_00,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 1
                OR 
                TRY_CAST(ETA AS TIME) >= '01:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '01:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_01,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 2
                OR 
                TRY_CAST(ETA AS TIME) >= '02:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '02:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_02,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 3
                OR 
                TRY_CAST(ETA AS TIME) >= '03:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '03:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_03,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 4
                OR 
                TRY_CAST(ETA AS TIME) >= '04:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '04:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_04,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 5
                OR 
                TRY_CAST(ETA AS TIME) >= '05:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '05:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_05,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 6
                OR 
                TRY_CAST(ETA AS TIME) >= '06:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '06:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_06,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 7
                OR 
                TRY_CAST(ETA AS TIME) >= '07:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '07:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_07,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) >= 21
                OR 
                TRY_CAST(LEFT(ETA, 2) AS INT) BETWEEN 0 AND 7
                OR
                TRY_CAST(ETA AS TIME) >= '21:00:00'
                OR 
                TRY_CAST(ETA AS TIME) <= '07:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as TOTAL_ORDER_NS,
        MAX(ADD_NS) as ADD_NS
    FROM T_ORDER
    WHERE DELV_DATE BETWEEN ? AND ?
    GROUP BY DELV_DATE, PART_NO, PART_NAME, SUPPLIER_CODE
),
IncomingData AS (
    SELECT 
        ub.DATE,
        ub.PART_NO,
        ub.PART_DESC,
        o.SUPPLIER_CODE,
        o.PART_NAME,
        ub.HOUR,
        ub.TRAN_QTY,
        ROW_NUMBER() OVER (
            PARTITION BY ub.DATE, ub.PART_NO, ub.HOUR 
            ORDER BY ub.ID_ORDER DESC
        ) as rn
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
),
LatestIncoming AS (
    SELECT * FROM IncomingData WHERE rn = 1
),
IncomingPerHour AS (
    SELECT 
        DATE,
        PART_NO,
        PART_DESC,
        SUPPLIER_CODE,
        PART_NAME,
        HOUR,
        TRAN_QTY,
        CASE 
            WHEN HOUR = 21 THEN TRAN_QTY
            ELSE 
                CASE 
                    WHEN TRAN_QTY > 0 THEN 
                        TRAN_QTY - ISNULL(LAG(TRAN_QTY, 1) OVER (
                            PARTITION BY DATE, PART_NO 
                            ORDER BY CASE WHEN HOUR >= 21 THEN HOUR ELSE HOUR + 24 END
                        ), 0)
                    ELSE 0
                END
        END as INCOMING_QTY
    FROM LatestIncoming
)
SELECT 
    o.DATE,
    o.PART_NO,
    o.PART_NAME as PART_DESC,
    o.PART_NAME,
    o.SUPPLIER_CODE,
    ISNULL(o.ORD_21, 0) as ORD_21,
    ISNULL(o.ORD_22, 0) as ORD_22,
    ISNULL(o.ORD_23, 0) as ORD_23,
    ISNULL(o.ORD_00, 0) as ORD_00,
    ISNULL(o.ORD_01, 0) as ORD_01,
    ISNULL(o.ORD_02, 0) as ORD_02,
    ISNULL(o.ORD_03, 0) as ORD_03,
    ISNULL(o.ORD_04, 0) as ORD_04,
    ISNULL(o.ORD_05, 0) as ORD_05,
    ISNULL(o.ORD_06, 0) as ORD_06,
    ISNULL(o.ORD_07, 0) as ORD_07,
    ISNULL(SUM(CASE WHEN i.HOUR = 21 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_21,
    ISNULL(SUM(CASE WHEN i.HOUR = 22 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_22,
    ISNULL(SUM(CASE WHEN i.HOUR = 23 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_23,
    ISNULL(SUM(CASE WHEN i.HOUR = 0 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_00,
    ISNULL(SUM(CASE WHEN i.HOUR = 1 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_01,
    ISNULL(SUM(CASE WHEN i.HOUR = 2 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_02,
    ISNULL(SUM(CASE WHEN i.HOUR = 3 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_03,
    ISNULL(SUM(CASE WHEN i.HOUR = 4 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_04,
    ISNULL(SUM(CASE WHEN i.HOUR = 5 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_05,
    ISNULL(SUM(CASE WHEN i.HOUR = 6 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_06,
    ISNULL(SUM(CASE WHEN i.HOUR = 7 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_07,
    ISNULL(o.TOTAL_ORDER_NS, 0) as TOTAL_ORDER,
    ISNULL(SUM(i.INCOMING_QTY), 0) as TOTAL_INCOMING,
    ISNULL(o.ADD_NS, 0) as ADD_NS
FROM AllNightShiftOrders o
LEFT JOIN IncomingPerHour i ON 
    o.DATE = i.DATE 
    AND o.PART_NO = i.PART_NO 
    AND o.SUPPLIER_CODE = i.SUPPLIER_CODE
WHERE o.SUPPLIER_CODE IS NOT NULL
AND o.SUPPLIER_CODE != ''
AND o.TOTAL_ORDER_NS > 0
GROUP BY 
    o.DATE, o.PART_NO, o.PART_NAME, o.SUPPLIER_CODE,
    o.ORD_21, o.ORD_22, o.ORD_23, o.ORD_00, o.ORD_01, o.ORD_02, 
    o.ORD_03, o.ORD_04, o.ORD_05, o.ORD_06, o.ORD_07,
    o.TOTAL_ORDER_NS,
    o.ADD_NS
ORDER BY o.DATE DESC, o.SUPPLIER_CODE, o.PART_NO
";

$params = [$date1, $date2, $date1, $date2];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $response["success"] = false;
    $response["message"] = "Query gagal: " . print_r(sqlsrv_errors(), true);
    echo json_encode($response);
    exit;
}

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $row['DATE'] = isset($row['DATE']) ? strval($row['DATE']) : '';
    $row['SUPPLIER_CODE'] = isset($row['SUPPLIER_CODE']) ? strval($row['SUPPLIER_CODE']) : '';
    $row['PART_NO'] = isset($row['PART_NO']) ? strval($row['PART_NO']) : '';
    $row['PART_DESC'] = isset($row['PART_DESC']) ? strval($row['PART_DESC']) : ($row['PART_NAME'] ?? '');
    $row['PART_NAME'] = isset($row['PART_NAME']) ? strval($row['PART_NAME']) : '';
    
    $ns_hours = [21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7];
    foreach($ns_hours as $hour) {
        $hourKey = str_pad($hour, 2, '0', STR_PAD_LEFT);
        $row['ORD_' . $hourKey] = intval($row['ORD_' . $hourKey] ?? 0);
        $row['TRAN_' . $hourKey] = intval($row['TRAN_' . $hourKey] ?? 0);
    }
    
    $row['TOTAL_ORDER'] = intval($row['TOTAL_ORDER'] ?? 0);
    $row['TOTAL_INCOMING'] = intval($row['TOTAL_INCOMING'] ?? 0);
    $row['ADD_NS'] = intval($row['ADD_NS'] ?? 0);
    
    if ($row['TOTAL_ORDER'] > 0) {
        $response["data"][] = $row;
    }
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

$response['count'] = count($response["data"]);
$response['message'] = "Tampil " . $response['count'] . " data (semua order NS, terima semua ETA)";

echo json_encode($response);
?>