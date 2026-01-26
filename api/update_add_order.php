<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

session_start();
$currentUser = $_SESSION['name'] ?? 'SYSTEM';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $date = $_POST['date'] ?? '';
    $supplier_code = $_POST['supplier_code'] ?? '';
    $part_no = $_POST['part_no'] ?? '';
    $remark = $_POST['remark'] ?? '';
    $action = $_POST['action'] ?? 'add';
    
    // Data per jam
    $hours_data = $_POST['hours_data'] ?? '{}';
    
    // Validasi
    if (empty($type) || empty($date) || empty($supplier_code) || empty($part_no)) {
        $response['message'] = 'Data tidak lengkap';
        echo json_encode($response);
        exit;
    }
    
    $remark = trim($remark);
    
    $conn = Database::getConnection();
    
    if ($conn === false) {
        $response['message'] = 'Database connection failed';
        echo json_encode($response);
        exit;
    }
    
    try {
        if ($action === 'add' || $action === 'update') {
            // Decode hours data
            $hours_array = json_decode($hours_data, true);
            
            if (empty($hours_array) || !is_array($hours_array)) {
                $response['message'] = 'Tidak ada jam yang dipilih';
                echo json_encode($response);
                exit;
            }
            
            // Hitung total quantity
            $total_qty = 0;
            foreach ($hours_array as $hour => $qty) {
                $qty_int = intval($qty);
                if ($qty_int > 0) {
                    $total_qty += $qty_int;
                }
            }
            
            if ($total_qty <= 0) {
                $response['message'] = 'Total quantity harus lebih dari 0';
                echo json_encode($response);
                exit;
            }
            
            // Cek apakah data sudah ada di T_ORDER
            $check_sql = "SELECT ID_UPDATE_BO, PART_NAME FROM T_ORDER 
                         WHERE DELV_DATE = ? AND SUPPLIER_CODE = ? AND PART_NO = ?";
            $check_params = [$date, $supplier_code, $part_no];
            $check_stmt = sqlsrv_query($conn, $check_sql, $check_params);
            
            $exists = ($check_stmt && sqlsrv_has_rows($check_stmt));
            $part_name = '';
            if ($exists) {
                $row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);
                $part_name = $row['PART_NAME'] ?? '';
            }
            
            if ($type === 'ds') {
                if ($exists) {
                    // UPDATE existing
                    $sql = "UPDATE T_ORDER 
                           SET ADD_DS = ?, 
                               REMARK_DS = ?,
                               LAST_ADD_DS_QTY = ?,
                               LAST_ADD_DS_BY = ?,
                               LAST_ADD_DS_AT = GETDATE()
                           WHERE DELV_DATE = ? 
                           AND SUPPLIER_CODE = ? 
                           AND PART_NO = ?";
                } else {
                    // INSERT new
                    $sql = "INSERT INTO T_ORDER 
                           (DELV_DATE, SUPPLIER_CODE, PART_NO, PART_NAME,
                            ADD_DS, REMARK_DS,
                            LAST_ADD_DS_QTY, LAST_ADD_DS_BY, LAST_ADD_DS_AT)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, GETDATE())";
                }
                
                $params = $exists ? 
                    [$total_qty, $remark, $total_qty, $currentUser, $date, $supplier_code, $part_no] :
                    [$date, $supplier_code, $part_no, $part_name, $total_qty, $remark, $total_qty, $currentUser];
                
            } else if ($type === 'ns') {
                if ($exists) {
                    // UPDATE existing
                    $sql = "UPDATE T_ORDER 
                           SET ADD_NS = ?, 
                               REMARK_NS = ?,
                               LAST_ADD_NS_QTY = ?,
                               LAST_ADD_NS_BY = ?,
                               LAST_ADD_NS_AT = GETDATE()
                           WHERE DELV_DATE = ? 
                           AND SUPPLIER_CODE = ? 
                           AND PART_NO = ?";
                } else {
                    // INSERT new
                    $sql = "INSERT INTO T_ORDER 
                           (DELV_DATE, SUPPLIER_CODE, PART_NO, PART_NAME,
                            ADD_NS, REMARK_NS,
                            LAST_ADD_NS_QTY, LAST_ADD_NS_BY, LAST_ADD_NS_AT)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, GETDATE())";
                }
                
                $params = $exists ? 
                    [$total_qty, $remark, $total_qty, $currentUser, $date, $supplier_code, $part_no] :
                    [$date, $supplier_code, $part_no, $part_name, $total_qty, $remark, $total_qty, $currentUser];
            }
            
            $stmt = sqlsrv_query($conn, $sql, $params);
            
            if ($stmt === false) {
                $errors = sqlsrv_errors();
                throw new Exception('Gagal menyimpan data: ' . ($errors[0]['message'] ?? 'Unknown'));
            }
            
            // Simpan ke T_UPDATE_BO untuk recording per jam
            // Hapus dulu data lama untuk shift ini
            $delete_condition = ($type === 'ds') ? 
                "HOUR BETWEEN 7 AND 20" : 
                "(HOUR BETWEEN 21 AND 23 OR HOUR BETWEEN 0 AND 6)";
            
            $delete_sql = "DELETE FROM T_UPDATE_BO 
                          WHERE DATE = ? 
                          AND PART_NO = ? 
                          AND $delete_condition";
            $delete_params = [$date, $part_no];
            sqlsrv_query($conn, $delete_sql, $delete_params);
            
            // Insert data per jam - Coba dengan TYPE dulu, jika error coba tanpa TYPE
            foreach ($hours_array as $hour => $qty) {
                $hour_int = intval($hour);
                $qty_int = intval($qty);
                
                if ($qty_int > 0) {
                    // Coba dengan TYPE='ADD_ORDER' dulu
                    $insert_sql = "INSERT INTO T_UPDATE_BO 
                                  (DATE, PART_NO, PART_DESC, HOUR, TRAN_QTY, CREATED_BY, CREATED_AT, TYPE)
                                  VALUES (?, ?, ?, ?, ?, ?, GETDATE(), 'ADD_ORDER')";
                    $insert_params = [$date, $part_no, $part_name, $hour_int, $qty_int, $currentUser];
                    
                    $insert_stmt = sqlsrv_query($conn, $insert_sql, $insert_params);
                    
                    // Jika error karena kolom TYPE tidak ada, coba tanpa TYPE
                    if ($insert_stmt === false) {
                        $insert_sql = "INSERT INTO T_UPDATE_BO 
                                      (DATE, PART_NO, PART_DESC, HOUR, TRAN_QTY, CREATED_BY, CREATED_AT)
                                      VALUES (?, ?, ?, ?, ?, ?, GETDATE())";
                        $insert_params = [$date, $part_no, $part_name, $hour_int, $qty_int, $currentUser];
                        sqlsrv_query($conn, $insert_sql, $insert_params);
                    }
                }
            }
            
            $response['success'] = true;
            $response['message'] = 'Add order berhasil disimpan! Total: ' . $total_qty . ' pcs';
            $response['total_qty'] = $total_qty;
            
        } else if ($action === 'reset') {
            if ($type === 'ds') {
                $sql = "UPDATE T_ORDER 
                       SET ADD_DS = 0, 
                           REMARK_DS = '',
                           LAST_ADD_DS_QTY = 0,
                           LAST_ADD_DS_BY = ?,
                           LAST_ADD_DS_AT = GETDATE()
                       WHERE DELV_DATE = ? AND SUPPLIER_CODE = ? AND PART_NO = ?";
            } else {
                $sql = "UPDATE T_ORDER 
                       SET ADD_NS = 0, 
                           REMARK_NS = '',
                           LAST_ADD_NS_QTY = 0,
                           LAST_ADD_NS_BY = ?,
                           LAST_ADD_NS_AT = GETDATE()
                       WHERE DELV_DATE = ? AND SUPPLIER_CODE = ? AND PART_NO = ?";
            }
            
            $params = [$currentUser, $date, $supplier_code, $part_no];
            $stmt = sqlsrv_query($conn, $sql, $params);
            
            if ($stmt !== false) {
                // Hapus dari T_UPDATE_BO
                $delete_condition = ($type === 'ds') ? 
                    "HOUR BETWEEN 7 AND 20" : 
                    "(HOUR BETWEEN 21 AND 23 OR HOUR BETWEEN 0 AND 6)";
                
                $delete_sql = "DELETE FROM T_UPDATE_BO 
                              WHERE DATE = ? AND PART_NO = ? 
                              AND $delete_condition";
                $delete_params = [$date, $part_no];
                sqlsrv_query($conn, $delete_sql, $delete_params);
                
                $response['success'] = true;
                $response['message'] = 'Add order berhasil direset';
            }
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
exit;
?>
