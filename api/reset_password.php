<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// DEBUG
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    $npk = $_SESSION['user'] ?? $data['npk'] ?? '';
    $currentPassword = $data['current_password'] ?? '';
    $newPassword = $data['new_password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';

    if (empty($npk) || empty($currentPassword) || empty($newPassword)) {
        throw new Exception('All fields are required');
    }

    if ($newPassword !== $confirmPassword) {
        throw new Exception('New passwords do not match');
    }

    // Password strength validation
    if (strlen($newPassword) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }
    
    if (!preg_match('/[A-Z]/', $newPassword)) {
        throw new Exception('Password must contain at least one uppercase letter');
    }
    
    if (!preg_match('/[a-z]/', $newPassword)) {
        throw new Exception('Password must contain at least one lowercase letter');
    }
    
    if (!preg_match('/[0-9]/', $newPassword)) {
        throw new Exception('Password must contain at least one number');
    }
    
    if (!preg_match('/[@$!%*?&]/', $newPassword)) {
        throw new Exception('Password must contain at least one special character (@$!%*?&)');
    }

    // Get user data
    $sql = "SELECT * FROM M_USER WHERE npk = ?";
    $stmt = sqlsrv_query($conn, $sql, [$npk]);
    
    if ($stmt === false) {
        throw new Exception('Database error');
    }

    $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }

    // Verify current password
    $storedPassword = $user['password'];
    $passwordVerified = false;
    
    // Check all possible formats
    if (password_verify($currentPassword, $storedPassword)) {
        $passwordVerified = true;
    } elseif (md5($currentPassword) === $storedPassword) {
        $passwordVerified = true;
    } elseif ($currentPassword === $storedPassword) {
        $passwordVerified = true;
    } elseif (sha1($currentPassword) === $storedPassword) {
        $passwordVerified = true;
    }

    if (!$passwordVerified) {
        throw new Exception('Current password is incorrect');
    }

    // Check if new password is same as old password
    if (password_verify($newPassword, $storedPassword) || 
        md5($newPassword) === $storedPassword || 
        $newPassword === $storedPassword ||
        sha1($newPassword) === $storedPassword) {
        throw new Exception('New password must be different from current password');
    }

    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password in database
    $updateSql = "UPDATE M_USER SET 
                  password = ?, 
                  must_reset_password = 0, 
                  password_changed = 1,
                  last_password_change = GETDATE()
                  WHERE npk = ?";
    
    $params = [$hashedPassword, $npk];
    $updateStmt = sqlsrv_query($conn, $updateSql, $params);
    
    if ($updateStmt === false) {
        throw new Exception('Failed to update password');
    }

    // Clear force reset flag from session
    unset($_SESSION['force_password_reset']);

    echo json_encode([
        'success' => true,
        'message' => 'Password has been successfully changed'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>