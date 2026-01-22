<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../config/paths.php';

// Default response
$response = [
    'success' => false,
    'data' => [],
    'message' => 'No data',
    'count' => 0,
    'timestamp' => date('Y-m-d H:i:s')
];

if ($conn === false) {
    $response['message'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

if (
    isset($_GET['supplier_code'], $_GET['pic'], $_GET['date1'], $_GET['date2'])
) {
    $supplier_code = trim($_GET['supplier_code']);
    $pic = trim($_GET['pic']);
    $date1Int = date('Ymd', strtotime($_GET['date1']));
    $date2Int = date('Ymd', strtotime($_GET['date2']));

    if ($supplier_code === 'select-all') {
        $sql = "
            SELECT 
                T_ORDER.PART_NO,
                M_PART_NO.PART_NAME,
                T_ORDER.SUPPLIER_CODE,
                M_SUPPLIER.SUPPLIER_NAME,
                SUM(T_ORDER.ORD_QTY) AS total_order
            FROM T_ORDER
            JOIN M_PART_NO ON T_ORDER.PART_NO = M_PART_NO.PART_NO
            LEFT JOIN M_SUPPLIER ON T_ORDER.SUPPLIER_CODE = M_SUPPLIER.SUPPLIER_CODE
            WHERE M_PART_NO.PIC_ORDER = ?
              AND T_ORDER.DELV_DATE BETWEEN ? AND ?
            GROUP BY T_ORDER.PART_NO, M_PART_NO.PART_NAME, 
                     T_ORDER.SUPPLIER_CODE, M_SUPPLIER.SUPPLIER_NAME
            ORDER BY total_order DESC
        ";
        $params = [$pic, $date1Int, $date2Int];
    } else {
        $supplier_codes = explode(',', $supplier_code);
        $placeholders = implode(',', array_fill(0, count($supplier_codes), '?'));

        $sql = "
            SELECT 
                T_ORDER.PART_NO,
                M_PART_NO.PART_NAME,
                T_ORDER.SUPPLIER_CODE,
                M_SUPPLIER.SUPPLIER_NAME,
                SUM(T_ORDER.ORD_QTY) AS total_order
            FROM T_ORDER
            JOIN M_PART_NO ON T_ORDER.PART_NO = M_PART_NO.PART_NO
            LEFT JOIN M_SUPPLIER ON T_ORDER.SUPPLIER_CODE = M_SUPPLIER.SUPPLIER_CODE
            WHERE T_ORDER.SUPPLIER_CODE IN ($placeholders)
              AND M_PART_NO.PIC_ORDER = ?
              AND T_ORDER.DELV_DATE BETWEEN ? AND ?
            GROUP BY T_ORDER.PART_NO, M_PART_NO.PART_NAME, 
                     T_ORDER.SUPPLIER_CODE, M_SUPPLIER.SUPPLIER_NAME
            ORDER BY total_order DESC
        ";
        $params = array_merge($supplier_codes, [$pic, $date1Int, $date2Int]);
    }

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        $response['message'] = 'Query failed: ' . print_r(sqlsrv_errors(), true);
        echo json_encode($response);
        exit;
    }

    $data = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $data[] = $row;
    }
    
    sqlsrv_free_stmt($stmt);
    
    $response['success'] = true;
    $response['data'] = $data;
    $response['message'] = 'Data retrieved successfully';
    $response['count'] = count($data);
}

echo json_encode($response);
exit;
?>