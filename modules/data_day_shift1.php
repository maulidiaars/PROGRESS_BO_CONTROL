<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$response = [
    "success" => true,
    "data" => [],
    "message" => "",
    "logic" => "TAMPIL SEMUA ORDER DAY SHIFT (7-20) + ADD ORDER DISTRIBUTION"
];

if (!$conn) {
    $response["success"] = false;
    $response["message"] = "Database belum terkoneksi";
    echo json_encode($response);
    exit;
}

$date1 = isset($_GET['date1']) ? $_GET['date1'] : date('Ymd');
$date2 = isset($_GET['date2']) ? $_GET['date2'] : date('Ymd');
$supplier_code = $_GET['supplier_code'] ?? '';

$sql = "
WITH AllDayShiftOrders AS (
    SELECT 
        DELV_DATE as DATE,
        PART_NO,
        PART_NAME,
        SUPPLIER_CODE,
        -- REGULAR ORDER per jam dari ETA
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
        END) as TOTAL_REGULAR_ORDER,
        
        MAX(ADD_DS) as ADD_DS
    FROM T_ORDER
    WHERE DELV_DATE BETWEEN ? AND ?
    ";

// Add supplier filter if exists
$params = [$date1, $date2];
if (!empty($supplier_code)) {
    $supplier_codes = explode(',', $supplier_code);
    if (count($supplier_codes) > 0) {
        $placeholders = implode(',', array_fill(0, count($supplier_codes), '?'));
        $sql .= " AND SUPPLIER_CODE IN ($placeholders)";
        
        foreach ($supplier_codes as $code) {
            $params[] = trim($code);
        }
    }
}

$sql .= "
    GROUP BY DELV_DATE, PART_NO, PART_NAME, SUPPLIER_CODE
),

AddOrderDistribution AS (
    SELECT 
        DATE,
        PART_NO,
        HOUR,
        SUM(QUANTITY) as ADD_QTY
    FROM T_ADD_ORDER_DISTRIBUTION
    WHERE TYPE = 'DS'
    AND DATE BETWEEN ? AND ?
    GROUP BY DATE, PART_NO, HOUR
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
    
    -- REGULAR ORDER
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
    
    -- ADD ORDER DISTRIBUTION
    ISNULL(a07.ADD_QTY, 0) as ADD_ORD_07,
    ISNULL(a08.ADD_QTY, 0) as ADD_ORD_08,
    ISNULL(a09.ADD_QTY, 0) as ADD_ORD_09,
    ISNULL(a10.ADD_QTY, 0) as ADD_ORD_10,
    ISNULL(a11.ADD_QTY, 0) as ADD_ORD_11,
    ISNULL(a12.ADD_QTY, 0) as ADD_ORD_12,
    ISNULL(a13.ADD_QTY, 0) as ADD_ORD_13,
    ISNULL(a14.ADD_QTY, 0) as ADD_ORD_14,
    ISNULL(a15.ADD_QTY, 0) as ADD_ORD_15,
    ISNULL(a16.ADD_QTY, 0) as ADD_ORD_16,
    ISNULL(a17.ADD_QTY, 0) as ADD_ORD_17,
    ISNULL(a18.ADD_QTY, 0) as ADD_ORD_18,
    ISNULL(a19.ADD_QTY, 0) as ADD_ORD_19,
    ISNULL(a20.ADD_QTY, 0) as ADD_ORD_20,
    
    -- TOTAL PER JAM (REGULAR + ADD)
    ISNULL(o.ORD_07, 0) + ISNULL(a07.ADD_QTY, 0) as TOTAL_ORD_07,
    ISNULL(o.ORD_08, 0) + ISNULL(a08.ADD_QTY, 0) as TOTAL_ORD_08,
    ISNULL(o.ORD_09, 0) + ISNULL(a09.ADD_QTY, 0) as TOTAL_ORD_09,
    ISNULL(o.ORD_10, 0) + ISNULL(a10.ADD_QTY, 0) as TOTAL_ORD_10,
    ISNULL(o.ORD_11, 0) + ISNULL(a11.ADD_QTY, 0) as TOTAL_ORD_11,
    ISNULL(o.ORD_12, 0) + ISNULL(a12.ADD_QTY, 0) as TOTAL_ORD_12,
    ISNULL(o.ORD_13, 0) + ISNULL(a13.ADD_QTY, 0) as TOTAL_ORD_13,
    ISNULL(o.ORD_14, 0) + ISNULL(a14.ADD_QTY, 0) as TOTAL_ORD_14,
    ISNULL(o.ORD_15, 0) + ISNULL(a15.ADD_QTY, 0) as TOTAL_ORD_15,
    ISNULL(o.ORD_16, 0) + ISNULL(a16.ADD_QTY, 0) as TOTAL_ORD_16,
    ISNULL(o.ORD_17, 0) + ISNULL(a17.ADD_QTY, 0) as TOTAL_ORD_17,
    ISNULL(o.ORD_18, 0) + ISNULL(a18.ADD_QTY, 0) as TOTAL_ORD_18,
    ISNULL(o.ORD_19, 0) + ISNULL(a19.ADD_QTY, 0) as TOTAL_ORD_19,
    ISNULL(o.ORD_20, 0) + ISNULL(a20.ADD_QTY, 0) as TOTAL_ORD_20,
    
    -- INCOMING
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
    
    -- TOTALS
    ISNULL(o.TOTAL_REGULAR_ORDER, 0) as TOTAL_REGULAR_ORDER,
    ISNULL(o.ADD_DS, 0) as ADD_DS,
    ISNULL(o.TOTAL_REGULAR_ORDER, 0) + ISNULL(o.ADD_DS, 0) as TOTAL_ORDER,
    ISNULL(SUM(i.INCOMING_QTY), 0) as TOTAL_INCOMING
FROM AllDayShiftOrders o

-- JOIN Add Order Distribution untuk setiap jam
LEFT JOIN AddOrderDistribution a07 ON o.DATE = a07.DATE AND o.PART_NO = a07.PART_NO AND a07.HOUR = 7
LEFT JOIN AddOrderDistribution a08 ON o.DATE = a08.DATE AND o.PART_NO = a08.PART_NO AND a08.HOUR = 8
LEFT JOIN AddOrderDistribution a09 ON o.DATE = a09.DATE AND o.PART_NO = a09.PART_NO AND a09.HOUR = 9
LEFT JOIN AddOrderDistribution a10 ON o.DATE = a10.DATE AND o.PART_NO = a10.PART_NO AND a10.HOUR = 10
LEFT JOIN AddOrderDistribution a11 ON o.DATE = a11.DATE AND o.PART_NO = a11.PART_NO AND a11.HOUR = 11
LEFT JOIN AddOrderDistribution a12 ON o.DATE = a12.DATE AND o.PART_NO = a12.PART_NO AND a12.HOUR = 12
LEFT JOIN AddOrderDistribution a13 ON o.DATE = a13.DATE AND o.PART_NO = a13.PART_NO AND a13.HOUR = 13
LEFT JOIN AddOrderDistribution a14 ON o.DATE = a14.DATE AND o.PART_NO = a14.PART_NO AND a14.HOUR = 14
LEFT JOIN AddOrderDistribution a15 ON o.DATE = a15.DATE AND o.PART_NO = a15.PART_NO AND a15.HOUR = 15
LEFT JOIN AddOrderDistribution a16 ON o.DATE = a16.DATE AND o.PART_NO = a16.PART_NO AND a16.HOUR = 16
LEFT JOIN AddOrderDistribution a17 ON o.DATE = a17.DATE AND o.PART_NO = a17.PART_NO AND a17.HOUR = 17
LEFT JOIN AddOrderDistribution a18 ON o.DATE = a18.DATE AND o.PART_NO = a18.PART_NO AND a18.HOUR = 18
LEFT JOIN AddOrderDistribution a19 ON o.DATE = a19.DATE AND o.PART_NO = a19.PART_NO AND a19.HOUR = 19
LEFT JOIN AddOrderDistribution a20 ON o.DATE = a20.DATE AND o.PART_NO = a20.PART_NO AND a20.HOUR = 20

LEFT JOIN IncomingPerHour i ON 
    o.DATE = i.DATE 
    AND o.PART_NO = i.PART_NO 
    AND o.SUPPLIER_CODE = i.SUPPLIER_CODE
WHERE o.SUPPLIER_CODE IS NOT NULL
AND o.SUPPLIER_CODE != ''
AND (o.TOTAL_REGULAR_ORDER > 0 OR o.ADD_DS > 0)
GROUP BY 
    o.DATE, o.PART_NO, o.PART_NAME, o.SUPPLIER_CODE,
    o.ORD_07, o.ORD_08, o.ORD_09, o.ORD_10, o.ORD_11, o.ORD_12, 
    o.ORD_13, o.ORD_14, o.ORD_15, o.ORD_16, o.ORD_17, o.ORD_18, 
    o.ORD_19, o.ORD_20,
    o.TOTAL_REGULAR_ORDER,
    o.ADD_DS,
    a07.ADD_QTY, a08.ADD_QTY, a09.ADD_QTY, a10.ADD_QTY, a11.ADD_QTY, a12.ADD_QTY,
    a13.ADD_QTY, a14.ADD_QTY, a15.ADD_QTY, a16.ADD_QTY, a17.ADD_QTY, a18.ADD_QTY,
    a19.ADD_QTY, a20.ADD_QTY
ORDER BY o.DATE DESC, o.SUPPLIER_CODE, o.PART_NO
";

// Add parameters for AddOrderDistribution and IncomingData
array_push($params, $date1, $date2, $date1, $date2);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $response["success"] = false;
    $response["message"] = "Query gagal: " . print_r(sqlsrv_errors(), true);
    echo json_encode($response);
    exit;
}

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Format data
    $row['DATE'] = isset($row['DATE']) ? strval($row['DATE']) : '';
    $row['SUPPLIER_CODE'] = isset($row['SUPPLIER_CODE']) ? strval($row['SUPPLIER_CODE']) : '';
    $row['PART_NO'] = isset($row['PART_NO']) ? strval($row['PART_NO']) : '';
    $row['PART_DESC'] = isset($row['PART_DESC']) ? strval($row['PART_DESC']) : ($row['PART_NAME'] ?? '');
    $row['PART_NAME'] = isset($row['PART_NAME']) ? strval($row['PART_NAME']) : '';
    
    // Convert all numeric values
    for($hour = 7; $hour <= 20; $hour++) {
        $hourKey = str_pad($hour, 2, '0', STR_PAD_LEFT);
        $row['ORD_' . $hourKey] = intval($row['ORD_' . $hourKey] ?? 0);
        $row['ADD_ORD_' . $hourKey] = intval($row['ADD_ORD_' . $hourKey] ?? 0);
        $row['TOTAL_ORD_' . $hourKey] = intval($row['TOTAL_ORD_' . $hourKey] ?? 0);
        $row['TRAN_' . $hourKey] = intval($row['TRAN_' . $hourKey] ?? 0);
    }
    
    $row['TOTAL_REGULAR_ORDER'] = intval($row['TOTAL_REGULAR_ORDER'] ?? 0);
    $row['ADD_DS'] = intval($row['ADD_DS'] ?? 0);
    $row['TOTAL_ORDER'] = intval($row['TOTAL_ORDER'] ?? 0);
    $row['TOTAL_INCOMING'] = intval($row['TOTAL_INCOMING'] ?? 0);
    
    if ($row['TOTAL_ORDER'] > 0) {
        $response["data"][] = $row;
    }
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

$response['count'] = count($response["data"]);
$response['message'] = "Tampil " . $response['count'] . " data (termasuk distribusi add order per jam)";

echo json_encode($response);
?>