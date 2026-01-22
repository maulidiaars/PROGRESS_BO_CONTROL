<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$response = ['success' => false, 'message' => ''];

// HARUS ada session user
if (!isset($_SESSION['name'])) {
    $response['message'] = 'Not authenticated';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['name']; // User yang sedang login
$notificationId = $_POST['notification_id'] ?? '';

if (empty($notificationId)) {
    $response['message'] = 'Notification ID required';
    echo json_encode($response);
    exit;
}

if (!$conn) {
    $response['message'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

try {
    // Cek apakah sudah ada record untuk user ini
    $check_sql = "SELECT id FROM user_notification_read 
                  WHERE user_id = ? AND notification_id = ?";
    $check_stmt = sqlsrv_query($conn, $check_sql, [$userId, $notificationId]);
    
    if ($check_stmt) {
        if (sqlsrv_fetch($check_stmt)) {
            // Update existing
            $update_sql = "UPDATE user_notification_read 
                           SET read_at = GETDATE() 
                           WHERE user_id = ? AND notification_id = ?";
            $update_stmt = sqlsrv_query($conn, $update_sql, [$userId, $notificationId]);
            
            if ($update_stmt) {
                $response['success'] = true;
                $response['message'] = 'Updated existing record';
            }
        } else {
            // Insert new
            $insert_sql = "INSERT INTO user_notification_read 
                           (user_id, notification_id, created_at, read_at) 
                           VALUES (?, ?, GETDATE(), GETDATE())";
            $insert_stmt = sqlsrv_query($conn, $insert_sql, [$userId, $notificationId]);
            
            if ($insert_stmt) {
                $response['success'] = true;
                $response['message'] = 'Created new record';
            }
        }
        
        sqlsrv_free_stmt($check_stmt);
    }
    
} catch (Exception $e) {
    $response['message'] = 'Exception: ' . $e->getMessage();
}

echo json_encode($response);
?>