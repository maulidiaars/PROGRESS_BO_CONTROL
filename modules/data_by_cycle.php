<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/status_logic.php'; // INCLUDE BARU

if (!$conn) {
    echo json_encode([
        "success" => false,
        "data" => [],
        "message" => "Database belum terkoneksi"
    ]);
    exit;
}

$DATE1 = isset($_GET["date1"]) ? $_GET["date1"] : '';
$DATE2 = isset($_GET["date2"]) ? $_GET["date2"] : '';

// VALIDASI DATE
if (empty($DATE1) || empty($DATE2)) {
    echo json_encode([
        "success" => false,
        "message" => "Parameter date1 dan date2 diperlukan"
    ]);
    exit;
}

// QUERY YANG BENAR-BENAR FIX
$sql = "SELECT 
    DELV_DATE,
    PART_NO,
    PART_NAME,
    SUPPLIER_CODE,
    -- PERHITUNGAN YANG BENAR: SUM untuk semua cycle yang sama
    SUM(CASE WHEN CYCLE = 1 THEN (ISNULL(ORD_QTY, 0) + ISNULL(ADD_DS, 0) + ISNULL(ADD_NS, 0)) ELSE 0 END) AS C1,
    SUM(CASE WHEN CYCLE = 2 THEN (ISNULL(ORD_QTY, 0) + ISNULL(ADD_DS, 0) + ISNULL(ADD_NS, 0)) ELSE 0 END) AS C2,
    SUM(CASE WHEN CYCLE = 3 THEN (ISNULL(ORD_QTY, 0) + ISNULL(ADD_DS, 0) + ISNULL(ADD_NS, 0)) ELSE 0 END) AS C3,
    SUM(CASE WHEN CYCLE = 4 THEN (ISNULL(ORD_QTY, 0) + ISNULL(ADD_DS, 0) + ISNULL(ADD_NS, 0)) ELSE 0 END) AS C4,
    SUM(CASE WHEN CYCLE = 5 THEN (ISNULL(ORD_QTY, 0) + ISNULL(ADD_DS, 0) + ISNULL(ADD_NS, 0)) ELSE 0 END) AS C5,
    SUM(CASE WHEN CYCLE = 6 THEN (ISNULL(ORD_QTY, 0) + ISNULL(ADD_DS, 0) + ISNULL(ADD_NS, 0)) ELSE 0 END) AS C6,
    SUM(CASE WHEN CYCLE = 7 THEN (ISNULL(ORD_QTY, 0) + ISNULL(ADD_DS, 0) + ISNULL(ADD_NS, 0)) ELSE 0 END) AS C7,
    SUM(CASE WHEN CYCLE = 8 THEN (ISNULL(ORD_QTY, 0) + ISNULL(ADD_DS, 0) + ISNULL(ADD_NS, 0)) ELSE 0 END) AS C8,
    SUM(CASE WHEN CYCLE = 9 THEN (ISNULL(ORD_QTY, 0) + ISNULL(ADD_DS, 0) + ISNULL(ADD_NS, 0)) ELSE 0 END) AS C9,
    SUM(CASE WHEN CYCLE = 10 THEN (ISNULL(ORD_QTY, 0) + ISNULL(ADD_DS, 0) + ISNULL(ADD_NS, 0)) ELSE 0 END) AS C10,
    SUM(CASE WHEN CYCLE = 11 THEN (ISNULL(ORD_QTY, 0) + ISNULL(ADD_DS, 0) + ISNULL(ADD_NS, 0)) ELSE 0 END) AS C11,
    SUM(CASE WHEN CYCLE = 12 THEN (ISNULL(ORD_QTY, 0) + ISNULL(ADD_DS, 0) + ISNULL(ADD_NS, 0)) ELSE 0 END) AS C12
FROM T_ORDER
WHERE CYCLE BETWEEN 1 AND 12
  AND DELV_DATE BETWEEN ? AND ?
GROUP BY DELV_DATE, PART_NO, PART_NAME, SUPPLIER_CODE
ORDER BY DELV_DATE DESC, SUPPLIER_CODE, PART_NO";

$params = array($DATE1, $DATE2);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode([
        "success" => false,
        "error" => print_r(sqlsrv_errors(), true)
    ]);
    exit;
}

$data = array();
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Hitung total
    $row['Total'] = $row['C1'] + $row['C2'] + $row['C3'] + $row['C4'] + 
                    $row['C5'] + $row['C6'] + $row['C7'] + $row['C8'] + 
                    $row['C9'] + $row['C10'] + $row['C11'] + $row['C12'];
    $data[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode($data);
?>