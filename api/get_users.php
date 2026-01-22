<?php
// api/get_users.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$response = ['success' => false, 'users' => [], 'count' => 0];

if (!isset($_SESSION['name'])) {
    echo json_encode($response);
    exit;
}

try {
    // Ambil semua user yang aktif dari M_USER
    $sql = "SELECT DISTINCT name 
            FROM M_USER 
            WHERE name IS NOT NULL 
            AND name != '' 
            AND name != 'SYSTEM'
            ORDER BY name ASC";
    
    $stmt = sqlsrv_query($conn, $sql);
    
    if ($stmt) {
        $users = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if (!empty($row['name'])) {
                $users[] = ['name' => trim($row['name'])];
            }
        }
        
        $response['success'] = true;
        $response['users'] = $users;
        $response['count'] = count($users);
        
        sqlsrv_free_stmt($stmt);
    } else {
        $response['error'] = sqlsrv_errors();
    }
    
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>