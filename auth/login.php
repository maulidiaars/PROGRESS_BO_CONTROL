<?php
session_start();

// DEBUG - Aktifkan untuk troubleshooting
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_clean();
header('Content-Type: application/json');

try {
    include '../config/database.php';
    
    if (!isset($conn) || $conn === false) {
        throw new Exception('Database connection failed');
    }

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode([
            'success' => false,
            'error' => 'Metode request tidak valid'
        ]);
        exit;
    }

    $npk = $_POST['npk'] ?? '';
    $password = $_POST['password'] ?? '';

    // Log untuk debugging (hapus di production)
    error_log("Login attempt - NPK: $npk, Password length: " . strlen($password));

    if (empty($npk)) {
        echo json_encode([
            'success' => false,
            'error' => 'NPK tidak boleh kosong',
            'field' => 'npk'
        ]);
        exit;
    }

    if (empty($password)) {
        echo json_encode([
            'success' => false,
            'error' => 'Password tidak boleh kosong',
            'field' => 'password'
        ]);
        exit;
    }

    $sql = "SELECT * FROM M_USER WHERE npk = ?";
    $stmt = sqlsrv_query($conn, $sql, [$npk]);

    if ($stmt === false) {
        error_log("SQL Error: " . print_r(sqlsrv_errors(), true));
        echo json_encode([
            'success' => false,
            'error' => 'Database error'
        ]);
        exit;
    }

    $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'success' => false,
            'error' => 'NPK tidak ditemukan',
            'field' => 'npk'
        ]);
        exit;
    }

    // DEBUG: Tampilkan info password (hapus di production)
    error_log("User found - NPK: " . $user['npk']);
    error_log("Stored hash: " . substr($user['password'], 0, 20) . "...");
    error_log("Hash length: " . strlen($user['password']));
    error_log("Hash info: " . print_r(password_get_info($user['password']), true));
    
    // 🔐 CEK PASSWORD - DENGAN MULTIPLE OPTIONS
    $storedPassword = $user['password'];
    $loginSuccess = false;
    
    // Option 1: Standard password_verify (untuk password_hash())
    if (password_verify($password, $storedPassword)) {
        $loginSuccess = true;
        error_log("Password verified with password_verify()");
    }
    // Option 2: MD5 hash
    elseif (strlen($storedPassword) === 32 && md5($password) === $storedPassword) {
        $loginSuccess = true;
        error_log("Password verified with MD5");
    }
    // Option 3: Plain text (jika password disimpan tanpa hash)
    elseif ($password === $storedPassword) {
        $loginSuccess = true;
        error_log("Password verified as plain text");
        // Rehash ke password_hash() untuk keamanan
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        // Update database dengan hash baru
        $updateSql = "UPDATE M_USER SET password = ? WHERE npk = ?";
        sqlsrv_query($conn, $updateSql, [$newHash, $npk]);
        error_log("Password rehashed and updated in database");
    }
    // Option 4: SHA1
    elseif (strlen($storedPassword) === 40 && sha1($password) === $storedPassword) {
        $loginSuccess = true;
        error_log("Password verified with SHA1");
    }

    if (!$loginSuccess) {
        error_log("Password verification FAILED for NPK: $npk");
        error_log("Input password: $password");
        error_log("Stored hash: $storedPassword");
        
        echo json_encode([
            'success' => false,
            'error' => 'Password salah',
            'field' => 'password'
        ]);
        exit;
    }

    // ✅ LOGIN SUKSES
    $_SESSION['user'] = $user['npk'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['user_data'] = $user;

    // CEK JIKA PERLU RESET PASSWORD (password masih default)
    $storedPassword = $user['password'];
    $defaultPassword = $user['npk']; // Password default sama dengan NPK

    // Cek apakah password masih default
    $isDefaultPassword = false;

    // Check semua kemungkinan format
    if (password_verify($defaultPassword, $storedPassword)) {
        $isDefaultPassword = true;
    } elseif (md5($defaultPassword) === $storedPassword) {
        $isDefaultPassword = true;
    } elseif ($defaultPassword === $storedPassword) {
        $isDefaultPassword = true;
    } elseif (sha1($defaultPassword) === $storedPassword) {
        $isDefaultPassword = true;
    }

    // Set flag di session
    $_SESSION['force_password_reset'] = $isDefaultPassword;

    // Update status harus reset password di database
    if ($isDefaultPassword) {
        $updateSql = "UPDATE M_USER SET must_reset_password = 1 WHERE npk = ?";
        sqlsrv_query($conn, $updateSql, [$npk]);
    }

    error_log("Login SUCCESS for NPK: " . $user['npk'] . " - Need reset: " . ($isDefaultPassword ? 'YES' : 'NO'));

    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil',
        'redirect' => '../index.php',
        'force_password_reset' => $isDefaultPassword
    ]);
    exit;

} catch (Throwable $e) {
    error_log("Login exception: " . $e->getMessage());
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}
?>