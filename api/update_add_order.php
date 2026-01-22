<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

session_start();
$currentUser = $_SESSION['name'] ?? 'SYSTEM';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? ''; // 'ds' atau 'ns'
    $date = $_POST['date'] ?? '';
    $supplier_code = $_POST['supplier_code'] ?? '';
    $part_no = $_POST['part_no'] ?? '';
    $add_qty = $_POST['add_qty'] ?? 0;
    $remark = $_POST['remark'] ?? '';
    $action = $_POST['action'] ?? 'add'; // 'add' atau 'reset'
    
    // Validasi
    if (empty($type) || empty($date) || empty($supplier_code) || empty($part_no)) {
        $response['message'] = 'Data tidak lengkap';
        echo json_encode($response);
        exit;
    }
    
    $add_qty = intval($add_qty);
    $remark = trim($remark);
    
    $conn = Database::getConnection();
    
    if ($conn === false) {
        $response['message'] = 'Database connection failed';
        echo json_encode($response);
        exit;
    }
    
    try {
        // ============ SIMPLE VERSION: ADD or RESET ============
        if ($action === 'add' || $action === 'update') {
            // Cek apakah data sudah ada
            $check_sql = "SELECT ID_UPDATE_BO FROM T_ORDER 
                         WHERE DELV_DATE = ? AND SUPPLIER_CODE = ? AND PART_NO = ?";
            $check_params = [$date, $supplier_code, $part_no];
            $check_stmt = sqlsrv_query($conn, $check_sql, $check_params);
            
            $existing_id = null;
            if ($check_stmt && sqlsrv_has_rows($check_stmt)) {
                $row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);
                $existing_id = $row['ID_UPDATE_BO'];
            }
            
            if ($type === 'ds') {
                if ($existing_id) {
                    // UPDATE existing
                    $sql = "UPDATE T_ORDER 
                           SET ADD_DS = ?, 
                               REMARK_DS = ?,
                               LAST_ADD_DS_QTY = ?,
                               LAST_ADD_DS_BY = ?,
                               LAST_ADD_DS_AT = GETDATE()
                           WHERE DELV_DATE = ? AND SUPPLIER_CODE = ? AND PART_NO = ?";
                } else {
                    // INSERT new
                    $sql = "INSERT INTO T_ORDER 
                           (DELV_DATE, SUPPLIER_CODE, PART_NO, 
                            ADD_DS, REMARK_DS,
                            LAST_ADD_DS_QTY, LAST_ADD_DS_BY, LAST_ADD_DS_AT) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, GETDATE())";
                }
                
                $params = [$add_qty, $remark, $add_qty, $currentUser, 
                          $date, $supplier_code, $part_no];
                
            } else if ($type === 'ns') {
                if ($existing_id) {
                    // UPDATE existing
                    $sql = "UPDATE T_ORDER 
                           SET ADD_NS = ?, 
                               REMARK_NS = ?,
                               LAST_ADD_NS_QTY = ?,
                               LAST_ADD_NS_BY = ?,
                               LAST_ADD_NS_AT = GETDATE()
                           WHERE DELV_DATE = ? AND SUPPLIER_CODE = ? AND PART_NO = ?";
                } else {
                    // INSERT new
                    $sql = "INSERT INTO T_ORDER 
                           (DELV_DATE, SUPPLIER_CODE, PART_NO, 
                            ADD_NS, REMARK_NS,
                            LAST_ADD_NS_QTY, LAST_ADD_NS_BY, LAST_ADD_NS_AT) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, GETDATE())";
                }
                
                $params = [$add_qty, $remark, $add_qty, $currentUser,
                          $date, $supplier_code, $part_no];
            }
            
            $stmt = sqlsrv_query($conn, $sql, $params);
            
            if ($stmt !== false) {
                $response['success'] = true;
                $response['message'] = 'Add order berhasil disimpan';
            } else {
                throw new Exception('Gagal menyimpan data');
            }
            
        } 
        // ============ RESET (HAPUS DATA) ============
        else if ($action === 'reset') {
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