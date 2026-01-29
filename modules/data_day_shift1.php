<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$response = [
    "success" => true,
    "data" => [],
    "message" => "",
    "logic" => "TAMPIL SEMUA ORDER DAY SHIFT (7-20) TERIMA SEMUA ETA"
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
WITH AllDayShiftOrders AS (
    SELECT 
        DELV_DATE as DATE,
        PART_NO,
        PART_NAME,
        SUPPLIER_CODE,
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
                TRY_CAST(LEFT(ETA, 2) AS INT) = 8
                OR 
                TRY_CAST(ETA AS TIME) >= '08:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '08:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_08,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 9
                OR 
                TRY_CAST(ETA AS TIME) >= '09:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '09:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_09,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 10
                OR 
                TRY_CAST(ETA AS TIME) >= '10:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '10:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_10,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 11
                OR 
                TRY_CAST(ETA AS TIME) >= '11:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '11:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_11,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 12
                OR 
                TRY_CAST(ETA AS TIME) >= '12:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '12:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_12,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 13
                OR 
                TRY_CAST(ETA AS TIME) >= '13:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '13:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_13,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 14
                OR 
                TRY_CAST(ETA AS TIME) >= '14:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '14:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_14,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 15
                OR 
                TRY_CAST(ETA AS TIME) >= '15:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '15:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_15,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 16
                OR 
                TRY_CAST(ETA AS TIME) >= '16:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '16:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_16,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 17
                OR 
                TRY_CAST(ETA AS TIME) >= '17:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '17:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_17,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 18
                OR 
                TRY_CAST(ETA AS TIME) >= '18:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '18:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_18,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 19
                OR 
                TRY_CAST(ETA AS TIME) >= '19:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '19:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_19,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) = 20
                OR 
                TRY_CAST(ETA AS TIME) >= '20:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '20:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as ORD_20,
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) BETWEEN 7 AND 20
                OR 
                TRY_CAST(ETA AS TIME) >= '07:00:00' 
                AND TRY_CAST(ETA AS TIME) <= '20:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as TOTAL_ORDER_DS,
        MAX(ADD_DS) as ADD_DS
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
    WHERE ub.HOUR BETWEEN 7 AND 20
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
            WHEN HOUR = 7 THEN TRAN_QTY
            ELSE 
                CASE 
                    WHEN TRAN_QTY > 0 THEN 
                        TRAN_QTY - ISNULL(LAG(TRAN_QTY, 1) OVER (
                            PARTITION BY DATE, PART_NO 
                            ORDER BY HOUR
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
    ISNULL(o.ORD_07, 0) as ORD_07,
    ISNULL(o.ORD_08, 0) as ORD_08,
    ISNULL(o.ORD_09, 0) as ORD_09,
    ISNULL(o.ORD_10, 0) as ORD_10,
    ISNULL(o.ORD_11, 0) as ORD_11,
    ISNULL(o.ORD_12, 0) as ORD_12,
    ISNULL(o.ORD_13, 0) as ORD_13,
    ISNULL(o.ORD_14, 0) as ORD_14,
    ISNULL(o.ORD_15, 0) as ORD_15,
    ISNULL(o.ORD_16, 0) as ORD_16,
    ISNULL(o.ORD_17, 0) as ORD_17,
    ISNULL(o.ORD_18, 0) as ORD_18,
    ISNULL(o.ORD_19, 0) as ORD_19,
    ISNULL(o.ORD_20, 0) as ORD_20,
    ISNULL(SUM(CASE WHEN i.HOUR = 7 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_07,
    ISNULL(SUM(CASE WHEN i.HOUR = 8 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_08,
    ISNULL(SUM(CASE WHEN i.HOUR = 9 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_09,
    ISNULL(SUM(CASE WHEN i.HOUR = 10 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_10,
    ISNULL(SUM(CASE WHEN i.HOUR = 11 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_11,
    ISNULL(SUM(CASE WHEN i.HOUR = 12 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_12,
    ISNULL(SUM(CASE WHEN i.HOUR = 13 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_13,
    ISNULL(SUM(CASE WHEN i.HOUR = 14 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_14,
    ISNULL(SUM(CASE WHEN i.HOUR = 15 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_15,
    ISNULL(SUM(CASE WHEN i.HOUR = 16 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_16,
    ISNULL(SUM(CASE WHEN i.HOUR = 17 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_17,
    ISNULL(SUM(CASE WHEN i.HOUR = 18 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_18,
    ISNULL(SUM(CASE WHEN i.HOUR = 19 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_19,
    ISNULL(SUM(CASE WHEN i.HOUR = 20 THEN i.INCOMING_QTY ELSE 0 END), 0) as TRAN_20,
    ISNULL(o.TOTAL_ORDER_DS, 0) as TOTAL_ORDER,
    ISNULL(SUM(i.INCOMING_QTY), 0) as TOTAL_INCOMING,
    ISNULL(o.ADD_DS, 0) as ADD_DS
FROM AllDayShiftOrders o
LEFT JOIN IncomingPerHour i ON 
    o.DATE = i.DATE 
    AND o.PART_NO = i.PART_NO 
    AND o.SUPPLIER_CODE = i.SUPPLIER_CODE
WHERE o.SUPPLIER_CODE IS NOT NULL
AND o.SUPPLIER_CODE != ''
AND o.TOTAL_ORDER_DS > 0
GROUP BY 
    o.DATE, o.PART_NO, o.PART_NAME, o.SUPPLIER_CODE,
    o.ORD_07, o.ORD_08, o.ORD_09, o.ORD_10, o.ORD_11, o.ORD_12, 
    o.ORD_13, o.ORD_14, o.ORD_15, o.ORD_16, o.ORD_17, o.ORD_18, 
    o.ORD_19, o.ORD_20,
    o.TOTAL_ORDER_DS,
    o.ADD_DS
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
    
    for($hour = 7; $hour <= 20; $hour++) {
        $hourKey = str_pad($hour, 2, '0', STR_PAD_LEFT);
        $row['ORD_' . $hourKey] = intval($row['ORD_' . $hourKey] ?? 0);
        $row['TRAN_' . $hourKey] = intval($row['TRAN_' . $hourKey] ?? 0);
    }
    
    $row['TOTAL_ORDER'] = intval($row['TOTAL_ORDER'] ?? 0);
    $row['TOTAL_INCOMING'] = intval($row['TOTAL_INCOMING'] ?? 0);
    $row['ADD_DS'] = intval($row['ADD_DS'] ?? 0);
    
    if ($row['TOTAL_ORDER'] > 0) {
        $response["data"][] = $row;
    }
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

$response['count'] = count($response["data"]);
$response['message'] = "Tampil " . $response['count'] . " data (semua order DS, terima semua ETA)";

echo json_encode($response);
?>