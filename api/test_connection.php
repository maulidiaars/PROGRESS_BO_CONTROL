<?php
// api/test_connection.php - SIMPLE VERSION
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/database.php';

$response = [
    'connected' => false,
    'timestamp' => date('Y-m-d H:i:s'),
    'user' => $_SESSION['name'] ?? 'Unknown'
];

try {
    $conn = Database::getConnection();
    
    if ($conn !== false) {
        // Test dengan query yang sangat simple
        $testQuery = "SELECT 1 as connection_test";
        $stmt = sqlsrv_query($conn, $testQuery);
        
        if ($stmt !== false) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if ($row && isset($row['connection_test'])) {
                $response['connected'] = true;
                $response['message'] = 'Database connected';
            }
            sqlsrv_free_stmt($stmt);
        }
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>