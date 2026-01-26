<?php
// api/get_users.php - PERBAIKAN VERSION
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../config/database.php';

$response = ['success' => false, 'users' => [], 'count' => 0, 'error' => null];

try {
    // Cek session
    if (!isset($_SESSION['name'])) {
        $response['error'] = 'Not authenticated';
        echo json_encode($response);
        exit;
    }

    $currentUser = $_SESSION['name'];
    
    if (!$conn) {
        $response['error'] = 'Database connection failed';
        echo json_encode($response);
        exit;
    }

    // Optimized query dengan LIMIT untuk mencegah timeout
    $sql = "SELECT TOP 100 
                   UPPER(LTRIM(RTRIM(name))) as name,
                   UPPER(LTRIM(RTRIM(name))) as value,
                   CASE 
                       WHEN department IS NOT NULL AND LTRIM(RTRIM(department)) != '' 
                       THEN UPPER(LTRIM(RTRIM(department)))
                       ELSE 'UNKNOWN'
                   END as department
            FROM M_USER 
            WHERE name IS NOT NULL 
            AND LTRIM(RTRIM(name)) != ''
            AND UPPER(LTRIM(RTRIM(name))) NOT IN ('SYSTEM', 'ADMIN', '')
            AND UPPER(LTRIM(RTRIM(name))) != UPPER(LTRIM(RTRIM(?)))
            ORDER BY name ASC";

    $params = [$currentUser];
    $stmt = sqlsrv_query($conn, $sql, $params, array("Scrollable" => SQLSRV_CURSOR_STATIC));
    
    if (!$stmt) {
        $response['error'] = 'Query failed: ' . print_r(sqlsrv_errors(), true);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $users = [];
    $userCount = 0;
    
    // Fetch dengan batas waktu
    $startTime = microtime(true);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $userCount++;
        
        // Safety timeout - jangan lebih dari 3 detik
        if ((microtime(true) - $startTime) > 3) {
            break;
        }
        
        if (!empty($row['name'])) {
            $users[] = [
                'name' => trim($row['name']),
                'value' => trim($row['name']),
                'department' => $row['department'] ?? 'UNKNOWN'
            ];
        }
    }
    
    sqlsrv_free_stmt($stmt);
    
    // Add "ALL" option di depan
    array_unshift($users, [
        'name' => 'SEMUA USER (Semua Orang)',
        'value' => 'ALL',
        'department' => 'ALL_USERS'
    ]);

    $response['success'] = true;
    $response['users'] = $users;
    $response['count'] = count($users);
    $response['execution_time'] = round(microtime(true) - $startTime, 3);
    
} catch (Exception $e) {
    $response['error'] = 'Exception: ' . $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);

// Close connection
if ($conn) {
    sqlsrv_close($conn);
}
?>