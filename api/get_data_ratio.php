<?php  

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../config/paths.php';

// Get PART_NO yang PIC_ORDER = 'ALBERTO'  
$sql = "  
SELECT   
    MP.PART_NO  
FROM   
    M_PART_NO MP  
WHERE   
    MP.PIC_ORDER = 'ALBERTO'  
";  
$stmt = sqlsrv_query($conn, $sql);  
if ($stmt === false) {  
        echo json_encode([
        'data' => [],
        'error' => 'Database error'
    ]);
    exit;
}  
$partNos = [];  
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {  
    $partNos[] = $row['PART_NO'];  
}  
if (count($partNos) == 0) {  
    echo json_encode(['error' => 'No PART_NO found for ALBERTO']);  
    exit;  
}  
$inPartNos = "'" . implode("','", $partNos) . "'";  
  
// Query D/S dan N/S  
$sql2 = "  
SELECT  
    ISNULL(D.ORD_QTY, 0) AS ORD_QTY_DS,  
    ISNULL(D.TRAN_QTY, 0) AS TRAN_QTY_DS,  
    ISNULL(N.ORD_QTY, 0) AS ORD_QTY_NS,  
    ISNULL(N.TRAN_QTY, 0) AS TRAN_QTY_NS  
FROM  
    (  
        SELECT  
            SUM(ORD.ORD_QTY) AS ORD_QTY,  
            SUM(TU.TRAN_QTY) AS TRAN_QTY  
        FROM  
            T_ORDER ORD  
            INNER JOIN T_UPDATE_BO TU ON ORD.PART_NO = TU.PART_NO  
        WHERE  
            ORD.PART_NO IN ($inPartNos)  
            AND ORD.ETA IS NOT NULL  
            AND  
            CAST(LEFT(ORD.ETA, CHARINDEX(':', ORD.ETA)-1) AS INT) BETWEEN 7 AND 20  
            AND TU.HOUR BETWEEN 7 AND 20  
    ) D,  
    (  
        SELECT  
            SUM(ORD.ORD_QTY) AS ORD_QTY,  
            SUM(TU.TRAN_QTY) AS TRAN_QTY  
        FROM  
            T_ORDER ORD  
            INNER JOIN T_UPDATE_BO TU ON ORD.PART_NO = TU.PART_NO  
        WHERE  
            ORD.PART_NO IN ($inPartNos)  
            AND ORD.ETA IS NOT NULL  
            AND (  
                CAST(LEFT(ORD.ETA, CHARINDEX(':', ORD.ETA)-1) AS INT) >= 21  
                OR CAST(LEFT(ORD.ETA, CHARINDEX(':', ORD.ETA)-1) AS INT) <= 6  
            )  
            AND (  
                TU.HOUR >= 21 OR TU.HOUR <= 6  
            )  
    ) N  
";  
  
$stmt2 = sqlsrv_query($conn, $sql2);  
if ($stmt2 === false) {  
        echo json_encode([
        'data' => [],
        'error' => 'Database error'
    ]);
    exit;
}  
$result = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);  
  
$ds_percent = 0;  
$ns_percent = 0;  
if ($result['ORD_QTY_DS'] > 0) {  
    $ds_percent = round($result['TRAN_QTY_DS'] / $result['ORD_QTY_DS'] * 100, 2);  
}  
if ($result['ORD_QTY_NS'] > 0) {  
    $ns_percent = round($result['TRAN_QTY_NS'] / $result['ORD_QTY_NS'] * 100, 2);  
}  
echo json_encode([  
    'D_S' => $ds_percent,  
    'N_S' => $ns_percent,  
]);  
?>  