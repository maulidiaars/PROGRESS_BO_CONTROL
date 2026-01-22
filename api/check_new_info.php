<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$response = [
    'success' => true,
    'count' => 0,
    'assigned_to_me' => 0,
    'urgent_count' => 0
];

$currentUser = $_SESSION['name'] ?? '';

if (!$conn || !$currentUser) {
    $response['success'] = false;
    echo json_encode($response);
    exit;
}

try {
    $today = date('Ymd');
    
    // 1. Hitung notifikasi informasi yang BELUM DIBACA oleh user ini
    $sql = "SELECT COUNT(DISTINCT ti.ID_INFORMATION) as unread_count
            FROM T_INFORMATION ti
            LEFT JOIN user_notification_read unr ON ti.ID_INFORMATION = unr.notification_id 
                AND unr.user_id = ?
            WHERE ti.PIC_TO = ?
            AND ti.DATE = ?
            AND ti.STATUS = 'Open'
            AND (unr.read_at IS NULL OR unr.id IS NULL)";
    
    $stmt = sqlsrv_query($conn, $sql, [$currentUser, $currentUser, $today]);
    
    if ($stmt) {
        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $response['count'] = $row['unread_count'] ?? 0;
            $response['assigned_to_me'] = $row['unread_count'] ?? 0;
        }
        sqlsrv_free_stmt($stmt);
    }
    
    // 2. Hitung urgent (informasi yang assign ke user dan masih Open)
    $sql_urgent = "SELECT COUNT(*) as urgent_count
                   FROM T_INFORMATION 
                   WHERE PIC_TO = ? 
                   AND STATUS = 'Open'
                   AND DATE = ?";
    
    $stmt2 = sqlsrv_query($conn, $sql_urgent, [$currentUser, $today]);
    if ($stmt2) {
        if ($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
            $response['urgent_count'] = $row['urgent_count'] ?? 0;
        }
        sqlsrv_free_stmt($stmt2);
    }
    
    echo json_encode($response);

} catch (Throwable $e) {
    error_log($e->getMessage());
    $response['success'] = false;
    echo json_encode($response);
}
?>