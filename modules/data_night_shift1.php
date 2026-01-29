<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$response = [
    "success" => true,
    "data" => [],
    "message" => "",
    "logic" => "TAMPIL SEMUA ORDER NIGHT SHIFT (21-7) + ADD ORDER DISTRIBUTION"
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
WITH AllNightShiftOrders AS (
    SELECT 
        DELV_DATE as DATE,
        PART_NO,
        PART_NAME,
        SUPPLIER_CODE,
        -- REGULAR ORDER per jam dari ETA (Night Shift 21-7)
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
        
        -- Jam 0-6 (night shift)
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
        
        -- TOTAL REGULAR ORDER NIGHT SHIFT
        SUM(CASE 
            WHEN ETA IS NOT NULL AND ETA != ''
            AND (
                TRY_CAST(LEFT(ETA, 2) AS INT) >= 21
                OR TRY_CAST(LEFT(ETA, 2) AS INT) BETWEEN 0 AND 7
                OR TRY_CAST(ETA AS TIME) >= '21:00:00'
                OR TRY_CAST(ETA AS TIME) <= '07:59:59'
            )
            THEN ORD_QTY 
            ELSE 0 
        END) as TOTAL_REGULAR_ORDER,
        
        MAX(ADD_NS) as ADD_NS
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
    WHERE TYPE = 'NS'
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
    
    -- REGULAR ORDER
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
    
    -- ADD ORDER DISTRIBUTION
    ISNULL(a21.ADD_QTY, 0) as ADD_ORD_21,
    ISNULL(a22.ADD_QTY, 0) as ADD_ORD_22,
    ISNULL(a23.ADD_QTY, 0) as ADD_ORD_23,
    ISNULL(a00.ADD_QTY, 0) as ADD_ORD_00,
    ISNULL(a01.ADD_QTY, 0) as ADD_ORD_01,
    ISNULL(a02.ADD_QTY, 0) as ADD_ORD_02,
    ISNULL(a03.ADD_QTY, 0) as ADD_ORD_03,
    ISNULL(a04.ADD_QTY, 0) as ADD_ORD_04,
    ISNULL(a05.ADD_QTY, 0) as ADD_ORD_05,
    ISNULL(a06.ADD_QTY, 0) as ADD_ORD_06,
    ISNULL(a07.ADD_QTY, 0) as ADD_ORD_07,
    
    -- TOTAL PER JAM (REGULAR + ADD)
    ISNULL(o.ORD_21, 0) + ISNULL(a21.ADD_QTY, 0) as TOTAL_ORD_21,
    ISNULL(o.ORD_22, 0) + ISNULL(a22.ADD_QTY, 0) as TOTAL_ORD_22,
    ISNULL(o.ORD_23, 0) + ISNULL(a23.ADD_QTY, 0) as TOTAL_ORD_23,
    ISNULL(o.ORD_00, 0) + ISNULL(a00.ADD_QTY, 0) as TOTAL_ORD_00,
    ISNULL(o.ORD_01, 0) + ISNULL(a01.ADD_QTY, 0) as TOTAL_ORD_01,
    ISNULL(o.ORD_02, 0) + ISNULL(a02.ADD_QTY, 0) as TOTAL_ORD_02,
    ISNULL(o.ORD_03, 0) + ISNULL(a03.ADD_QTY, 0) as TOTAL_ORD_03,
    ISNULL(o.ORD_04, 0) + ISNULL(a04.ADD_QTY, 0) as TOTAL_ORD_04,
    ISNULL(o.ORD_05, 0) + ISNULL(a05.ADD_QTY, 0) as TOTAL_ORD_05,
    ISNULL(o.ORD_06, 0) + ISNULL(a06.ADD_QTY, 0) as TOTAL_ORD_06,
    ISNULL(o.ORD_07, 0) + ISNULL(a07.ADD_QTY, 0) as TOTAL_ORD_07,
    
    -- INCOMING
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
    
    -- TOTALS
    ISNULL(o.TOTAL_REGULAR_ORDER, 0) as TOTAL_REGULAR_ORDER,
    ISNULL(o.ADD_NS, 0) as ADD_NS,
    ISNULL(o.TOTAL_REGULAR_ORDER, 0) + ISNULL(o.ADD_NS, 0) as TOTAL_ORDER,
    ISNULL(SUM(i.INCOMING_QTY), 0) as TOTAL_INCOMING
FROM AllNightShiftOrders o

-- JOIN Add Order Distribution untuk setiap jam NS
LEFT JOIN AddOrderDistribution a21 ON o.DATE = a21.DATE AND o.PART_NO = a21.PART_NO AND a21.HOUR = 21
LEFT JOIN AddOrderDistribution a22 ON o.DATE = a22.DATE AND o.PART_NO = a22.PART_NO AND a22.HOUR = 22
LEFT JOIN AddOrderDistribution a23 ON o.DATE = a23.DATE AND o.PART_NO = a23.PART_NO AND a23.HOUR = 23
LEFT JOIN AddOrderDistribution a00 ON o.DATE = a00.DATE AND o.PART_NO = a00.PART_NO AND a00.HOUR = 0
LEFT JOIN AddOrderDistribution a01 ON o.DATE = a01.DATE AND o.PART_NO = a01.PART_NO AND a01.HOUR = 1
LEFT JOIN AddOrderDistribution a02 ON o.DATE = a02.DATE AND o.PART_NO = a02.PART_NO AND a02.HOUR = 2
LEFT JOIN AddOrderDistribution a03 ON o.DATE = a03.DATE AND o.PART_NO = a03.PART_NO AND a03.HOUR = 3
LEFT JOIN AddOrderDistribution a04 ON o.DATE = a04.DATE AND o.PART_NO = a04.PART_NO AND a04.HOUR = 4
LEFT JOIN AddOrderDistribution a05 ON o.DATE = a05.DATE AND o.PART_NO = a05.PART_NO AND a05.HOUR = 5
LEFT JOIN AddOrderDistribution a06 ON o.DATE = a06.DATE AND o.PART_NO = a06.PART_NO AND a06.HOUR = 6
LEFT JOIN AddOrderDistribution a07 ON o.DATE = a07.DATE AND o.PART_NO = a07.PART_NO AND a07.HOUR = 7

LEFT JOIN IncomingPerHour i ON 
    o.DATE = i.DATE 
    AND o.PART_NO = i.PART_NO 
    AND o.SUPPLIER_CODE = i.SUPPLIER_CODE
WHERE o.SUPPLIER_CODE IS NOT NULL
AND o.SUPPLIER_CODE != ''
AND (o.TOTAL_REGULAR_ORDER > 0 OR o.ADD_NS > 0)
GROUP BY 
    o.DATE, o.PART_NO, o.PART_NAME, o.SUPPLIER_CODE,
    o.ORD_21, o.ORD_22, o.ORD_23, o.ORD_00, o.ORD_01, o.ORD_02, 
    o.ORD_03, o.ORD_04, o.ORD_05, o.ORD_06, o.ORD_07,
    o.TOTAL_REGULAR_ORDER,
    o.ADD_NS,
    a21.ADD_QTY, a22.ADD_QTY, a23.ADD_QTY, a00.ADD_QTY, a01.ADD_QTY, a02.ADD_QTY,
    a03.ADD_QTY, a04.ADD_QTY, a05.ADD_QTY, a06.ADD_QTY, a07.ADD_QTY
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
    $ns_hours = [21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7];
    foreach($ns_hours as $hour) {
        $hourKey = str_pad($hour, 2, '0', STR_PAD_LEFT);
        $row['ORD_' . $hourKey] = intval($row['ORD_' . $hourKey] ?? 0);
        $row['ADD_ORD_' . $hourKey] = intval($row['ADD_ORD_' . $hourKey] ?? 0);
        $row['TOTAL_ORD_' . $hourKey] = intval($row['TOTAL_ORD_' . $hourKey] ?? 0);
        $row['TRAN_' . $hourKey] = intval($row['TRAN_' . $hourKey] ?? 0);
    }
    
    $row['TOTAL_REGULAR_ORDER'] = intval($row['TOTAL_REGULAR_ORDER'] ?? 0);
    $row['ADD_NS'] = intval($row['ADD_NS'] ?? 0);
    $row['TOTAL_ORDER'] = intval($row['TOTAL_ORDER'] ?? 0);
    $row['TOTAL_INCOMING'] = intval($row['TOTAL_INCOMING'] ?? 0);
    
    if ($row['TOTAL_ORDER'] > 0) {
        $response["data"][] = $row;
    }
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

$response['count'] = count($response["data"]);
$response['message'] = "Tampil " . $response['count'] . " data NS (termasuk distribusi add order per jam)";

echo json_encode($response);
?>