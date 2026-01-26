<?php
// modules/data_information.php - VERSION LENGKAP DENGAN 7 HARI RETENTION & NO NOTIF UNTUK USER SENDIRI
session_start();
ob_clean();
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database belum terkoneksi"]);
    exit;
}

$type = isset($_POST["type"]) ? strtolower(trim($_POST["type"])) : 
       (isset($_GET["type"]) ? strtolower(trim($_GET["type"])) : '');

$response = ["success" => false, "message" => "Aksi tidak dikenal"];
$currentUser = $_SESSION['name'] ?? '';

try {
    // ========================= INPUT DATA INFORMATION (SINGLE ROW) =========================
    if ($type === "input") {
        
        $DATE      = $_POST["date"] ?? date('Ymd');
        $TIME_FROM = $_POST["txt-time1"] ?? date('H:i');
        $PIC_FROM  = $currentUser;
        $ITEM      = trim($_POST["txt-item"] ?? '');
        $REQUEST   = trim($_POST["txt-request"] ?? '');
        $recipients = $_POST["recipients"] ?? ''; // Format: JSON string array
        
        // Validasi
        if (empty($ITEM) || empty($REQUEST)) {
            $response["message"] = 'Item dan Request tidak boleh kosong';
            echo json_encode($response);
            exit;
        }
        
        if (empty($recipients)) {
            $response["message"] = 'Pilih minimal satu penerima';
            echo json_encode($response);
            exit;
        }
        
        // Parse recipients
        $recipientArray = [];
        if (is_string($recipients)) {
            if (strtoupper($recipients) === 'ALL') {
                // Get all users from database (kecuali user sendiri)
                $sqlUsers = "SELECT DISTINCT name FROM M_USER 
                            WHERE name IS NOT NULL 
                            AND LTRIM(RTRIM(name)) != ''
                            AND name != ?
                            ORDER BY name";
                $stmtUsers = sqlsrv_query($conn, $sqlUsers, [$currentUser]);
                if ($stmtUsers) {
                    while ($row = sqlsrv_fetch_array($stmtUsers, SQLSRV_FETCH_ASSOC)) {
                        if (!empty($row['name'])) {
                            $recipientArray[] = trim($row['name']);
                        }
                    }
                    sqlsrv_free_stmt($stmtUsers);
                }
            } else {
                // Try to decode JSON
                $decoded = json_decode($recipients, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $recipientArray = $decoded;
                } else {
                    // Fallback: assume comma-separated string
                    $recipientArray = array_filter(
                        array_map('trim', explode(',', $recipients)),
                        function($val) { return !empty($val); }
                    );
                }
            }
        } elseif (is_array($recipients)) {
            $recipientArray = array_filter(
                array_map('trim', $recipients),
                function($val) { return !empty($val); }
            );
        }
        
        // Remove current user from recipients
        $recipientArray = array_filter($recipientArray, function($recipient) use ($currentUser) {
            return $recipient !== $currentUser && !empty($recipient);
        });
        
        // Remove duplicates
        $recipientArray = array_unique($recipientArray);
        
        if (empty($recipientArray)) {
            $response["message"] = 'Tidak ada penerima yang valid';
            echo json_encode($response);
            exit;
        }
        
        // Sort recipients
        sort($recipientArray);
        $PIC_TO_COMBINED = implode(', ', $recipientArray);
            
        // CEK DUPLIKASI: Hanya cek untuk 7 hari terakhir
        $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
        $checkSql = "SELECT COUNT(*) as count FROM T_INFORMATION 
                     WHERE DATE >= ? 
                     AND PIC_FROM = ? 
                     AND ITEM = ? 
                     AND PIC_TO = ?";
        
        $checkStmt = sqlsrv_query($conn, $checkSql, [$sevenDaysAgo, $PIC_FROM, $ITEM, $PIC_TO_COMBINED]);
        $duplicateCount = 0;
        
        if ($checkStmt) {
            $row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
            $duplicateCount = (int)$row['count'];
            sqlsrv_free_stmt($checkStmt);
        }
        
        // Jika sudah ada data sama dalam 7 hari terakhir, beri warning
        if ($duplicateCount > 0) {
            $response["success"] = false;
            $response["message"] = 'Anda sudah mengirim informasi ini dalam 7 hari terakhir.';
            $response["duplicate"] = true;
            echo json_encode($response);
            exit;
        }
        
        // Simpan ke T_INFORMATION (HANYA SATU BARIS)
        $sql = "INSERT INTO T_INFORMATION 
                (DATE, TIME_FROM, PIC_FROM, PIC_TO, ITEM, REQUEST, STATUS) 
                VALUES (?, ?, ?, ?, ?, ?, 'Open')";
        
        $params = [$DATE, $TIME_FROM, $PIC_FROM, $PIC_TO_COMBINED, $ITEM, $REQUEST];
        
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt) {
            // Get inserted ID
            $idSql = "SELECT @@IDENTITY AS id";
            $idStmt = sqlsrv_query($conn, $idSql);
            $new_id = 0;
            
            if ($idStmt) {
                $idRow = sqlsrv_fetch_array($idStmt, SQLSRV_FETCH_ASSOC);
                $new_id = (int)($idRow['id'] ?? 0);
                sqlsrv_free_stmt($idStmt);
            }
            
            // Insert ke user_notification_read untuk setiap recipient (untuk notifikasi)
            foreach ($recipientArray as $recipient) {
                if (empty($recipient)) continue;
                
                $notifSql = "INSERT INTO user_notification_read (user_id, notification_id, created_at) 
                             VALUES (?, ?, GETDATE())";
                sqlsrv_query($conn, $notifSql, [$recipient, $new_id]);
            }
            
            $response["success"] = true;
            $response["message"] = 'Data berhasil dikirim ke ' . count($recipientArray) . ' penerima';
            $response["id"] = $new_id;
            $response["recipient_count"] = count($recipientArray);
            $response["recipients"] = $recipientArray;
            $response["retention_days"] = 7;
            $response["visible_until"] = date('Y-m-d', strtotime('+7 days'));
            
        } else {
            $errors = sqlsrv_errors();
            $response["message"] = "SQL Error: " . print_r($errors, true);
        }
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= UPDATE FROM (PENGIRIM) =========================
    else if ($type === "update-from") {
        
        $ID_INFORMATION = (int)($_POST["txt-id-information"] ?? 0);
        $TIME_FROM = $_POST["txt-timefrom-update"] ?? date('H:i');
        $PIC_FROM = $_POST["txt-picfrom-update"] ?? $currentUser;
        $ITEM = trim($_POST["txt-item-update"] ?? '');
        $REQUEST = trim($_POST["txt-request-update"] ?? '');
        
        // Validasi
        if ($ID_INFORMATION <= 0) {
            $response["message"] = 'ID Information tidak valid';
            echo json_encode($response);
            exit;
        }
        
        if (empty($ITEM) || empty($REQUEST)) {
            $response["message"] = 'Item dan Request tidak boleh kosong';
            echo json_encode($response);
            exit;
        }
        
        // Cek apakah user adalah pengirim
        $checkSql = "SELECT PIC_FROM, STATUS FROM T_INFORMATION WHERE ID_INFORMATION = ?";
        $checkStmt = sqlsrv_query($conn, $checkSql, [$ID_INFORMATION]);
        
        if (!$checkStmt) {
            $response["message"] = 'Data tidak ditemukan';
            echo json_encode($response);
            exit;
        }
        
        $info = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
        if (!$info) {
            $response["message"] = 'Data tidak ditemukan';
            echo json_encode($response);
            exit;
        }
        
        // Validasi: hanya PIC_FROM yang bisa update
        if ($info['PIC_FROM'] !== $currentUser) {
            $response["message"] = 'Anda tidak berhak mengedit informasi ini';
            echo json_encode($response);
            exit;
        }
        
        // Validasi: tidak bisa edit jika status sudah On Progress atau Closed
        if ($info['STATUS'] === 'On Progress' || $info['STATUS'] === 'Closed') {
            $response["message"] = 'Tidak bisa mengedit informasi yang sudah diproses atau ditutup';
            echo json_encode($response);
            exit;
        }
        
        // Update informasi
        $updateSql = "UPDATE T_INFORMATION 
                      SET TIME_FROM = ?, 
                          ITEM = ?, 
                          REQUEST = ?
                      WHERE ID_INFORMATION = ?";
        
        $params = [$TIME_FROM, $ITEM, $REQUEST, $ID_INFORMATION];
        $updateStmt = sqlsrv_query($conn, $updateSql, $params);
        
        if ($updateStmt) {
            $response["success"] = true;
            $response["message"] = 'Informasi berhasil diupdate';
        } else {
            $response["message"] = 'Gagal update informasi';
        }
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= UPDATE TO (PENERIMA) - DENGAN STATUS ON_PROGRESS/CLOSED =========================
    else if ($type === "update-to") {
        
        $ID_INFORMATION = (int)($_POST["txt-id-information2"] ?? 0);
        $TIME_TO = $_POST["txt-timeto-update"] ?? date('H:i');
        $PIC_TO = $_POST["txt-picto-update"] ?? $currentUser;
        $REMARK = trim($_POST["txt-remark-update"] ?? '');
        $ACTION_TYPE = $_POST["action_type"] ?? 'on_progress'; // 'on_progress' atau 'closed'
        
        // Validasi
        if ($ID_INFORMATION <= 0) {
            $response["message"] = 'ID Information tidak valid';
            echo json_encode($response);
            exit;
        }
        
        // Cek data informasi (HANYA 7 HARI TERAKHIR)
        $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
        $checkSql = "SELECT PIC_TO, STATUS, ITEM, REQUEST, PIC_FROM, DATE 
                     FROM T_INFORMATION 
                     WHERE ID_INFORMATION = ? 
                     AND DATE >= ?";
        $checkStmt = sqlsrv_query($conn, $checkSql, [$ID_INFORMATION, $sevenDaysAgo]);
        
        if (!$checkStmt) {
            $response["message"] = 'Data tidak ditemukan atau sudah tidak aktif (lebih dari 7 hari)';
            echo json_encode($response);
            exit;
        }
        
        $info = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
        if (!$info) {
            $response["message"] = 'Data tidak ditemukan atau sudah tidak aktif (lebih dari 7 hari)';
            echo json_encode($response);
            exit;
        }
        
        // Cek apakah user adalah salah satu penerima
        $recipients = explode(', ', $info['PIC_TO']);
        $isRecipient = in_array($currentUser, $recipients);
        
        if (!$isRecipient) {
            $response["message"] = 'Anda tidak berhak mengupdate informasi ini';
            echo json_encode($response);
            exit;
        }
        
        // Cek status saat ini - tidak bisa update jika sudah Closed
        if ($info['STATUS'] === 'Closed') {
            $response["message"] = 'Informasi ini sudah ditutup';
            echo json_encode($response);
            exit;
        }
        
        // Tentukan status baru
        $new_status = ($ACTION_TYPE === 'closed') ? 'Closed' : 'On Progress';
        
        // Validasi: untuk Closed, remark wajib diisi
        if ($ACTION_TYPE === 'closed' && empty($REMARK)) {
            $response["message"] = 'Remark wajib diisi untuk menutup informasi';
            echo json_encode($response);
            exit;
        }
        
        // Update informasi
        $updateSql = "UPDATE T_INFORMATION 
                      SET TIME_TO = ?, 
                          REMARK = ?, 
                          STATUS = ?
                      WHERE ID_INFORMATION = ?";
        
        $params = [$TIME_TO, $REMARK, $new_status, $ID_INFORMATION];
        $updateStmt = sqlsrv_query($conn, $updateSql, $params);
        
        if ($updateStmt) {
            // Update notifikasi untuk user ini sebagai sudah dibaca
            $notifSql = "UPDATE user_notification_read SET read_at = GETDATE() 
                         WHERE user_id = ? AND notification_id = ?";
            sqlsrv_query($conn, $notifSql, [$currentUser, $ID_INFORMATION]);
            
            // Jika status Closed, update notifikasi untuk semua recipient lainnya
            if ($new_status === 'Closed') {
                foreach ($recipients as $recipient) {
                    if ($recipient !== $currentUser) {
                        $notifAllSql = "UPDATE user_notification_read SET read_at = GETDATE() 
                                       WHERE user_id = ? AND notification_id = ? AND read_at IS NULL";
                        sqlsrv_query($conn, $notifAllSql, [$recipient, $ID_INFORMATION]);
                    }
                }
            }
            
            $response["success"] = true;
            $response["message"] = "Status berhasil diupdate ke " . $new_status;
            $response["new_status"] = $new_status;
        } else {
            $response["message"] = 'Gagal update status';
        }
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= DELETE INFORMATION =========================
    else if ($type === "delete") {
        
        $ID_INFORMATION = (int)($_POST["id_information"] ?? 0);
        
        if ($ID_INFORMATION <= 0) {
            $response["message"] = 'ID Information tidak valid';
            echo json_encode($response);
            exit;
        }
        
        // Cek apakah user adalah pengirim (HANYA 7 HARI TERAKHIR)
        $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
        $checkSql = "SELECT PIC_FROM, DATE FROM T_INFORMATION 
                     WHERE ID_INFORMATION = ? 
                     AND DATE >= ?";
        $checkStmt = sqlsrv_query($conn, $checkSql, [$ID_INFORMATION, $sevenDaysAgo]);
        
        if (!$checkStmt) {
            $response["message"] = 'Data tidak ditemukan atau sudah tidak aktif (lebih dari 7 hari)';
            echo json_encode($response);
            exit;
        }
        
        $info = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
        if (!$info) {
            $response["message"] = 'Data tidak ditemukan atau sudah tidak aktif (lebih dari 7 hari)';
            echo json_encode($response);
            exit;
        }
        
        // Validasi: hanya PIC_FROM yang bisa delete
        if ($info['PIC_FROM'] !== $currentUser) {
            $response["message"] = 'Anda tidak berhak menghapus informasi ini';
            echo json_encode($response);
            exit;
        }
        
        // Delete dari user_notification_read dulu (foreign key constraint)
        $deleteNotifSql = "DELETE FROM user_notification_read WHERE notification_id = ?";
        sqlsrv_query($conn, $deleteNotifSql, [$ID_INFORMATION]);
        
        // Delete informasi
        $deleteSql = "DELETE FROM T_INFORMATION WHERE ID_INFORMATION = ?";
        $deleteStmt = sqlsrv_query($conn, $deleteSql, [$ID_INFORMATION]);
        
        if ($deleteStmt) {
            $response["success"] = true;
            $response["message"] = 'Informasi berhasil dihapus';
        } else {
            $response["message"] = 'Gagal menghapus informasi';
        }
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= FETCH DATA - HANYA 7 HARI TERAKHIR & NO NOTIF USER SENDIRI =========================
    else if ($type === "fetch") {
        
        $DATE1 = $_GET["date1"] ?? date('Y-m-d');
        $DATE2 = $_GET["date2"] ?? date('Y-m-d');
        $date1_sql = str_replace('-', '', $DATE1);
        $date2_sql = str_replace('-', '', $DATE2);
        
        // ==================== REVISI: HANYA TAMPILKAN 7 HARI TERAKHIR ====================
        $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
        
        // Jika date range yang diminta lebih dari 7 hari, batasi ke 7 hari terakhir
        if ($date1_sql < $sevenDaysAgo) {
            $date1_sql = $sevenDaysAgo;
        }
        
        $sql = "SELECT
                    ID_INFORMATION, 
                    DATE, 
                    TIME_FROM, 
                    PIC_FROM,
                    PIC_TO, 
                    ITEM, 
                    REQUEST, 
                    TIME_TO, 
                    STATUS, 
                    REMARK,
                    -- Tentukan user role
                    CASE
                        WHEN CHARINDEX(?, PIC_TO) > 0 THEN 'recipient'
                        WHEN PIC_FROM = ? THEN 'sender'
                        ELSE 'viewer'
                    END as user_role,
                    -- Cek apakah sudah dibaca oleh user ini
                    (SELECT TOP 1 read_at FROM user_notification_read 
                     WHERE user_id = ? AND notification_id = ID_INFORMATION) as read_at
                FROM T_INFORMATION
                WHERE DATE BETWEEN ? AND ?
                AND DATE >= ?  -- FILTER TAMBAHAN: HANYA 7 HARI TERAKHIR
                ORDER BY DATE DESC, TIME_FROM DESC";
        
        $params = [$currentUser, $currentUser, $currentUser, $date1_sql, $date2_sql, $sevenDaysAgo];
        $stmt = sqlsrv_prepare($conn, $sql, $params);
        
        $data = [];
        if ($stmt && sqlsrv_execute($stmt)) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                // Format date
                if (isset($row['DATE']) && is_numeric($row['DATE'])) {
                    $d = (string)$row['DATE'];
                    if (strlen($d) === 8) {
                        $row['DATE'] = substr($d,0,4).'-'.substr($d,4,2).'-'.substr($d,6,2);
                    }
                }
                
                // Set defaults
                $row['TIME_TO'] = $row['TIME_TO'] ?: '-';
                $row['REMARK'] = $row['REMARK'] ?: '-';
                
                // Is unread? - Hanya untuk penerima DAN BUKAN dari user sendiri
                $isRecipient = ($row['user_role'] === 'recipient');
                $isFromSelf = ($row['PIC_FROM'] === $currentUser);
                
                // REVISI: Tidak ada notifikasi untuk informasi dari user sendiri
                $row['IS_UNREAD'] = ($row['read_at'] === null && $isRecipient && !$isFromSelf && $row['STATUS'] !== 'Closed') ? 1 : 0;
                
                // Tambah info 7 hari
                $rowDate = isset($row['DATE']) ? str_replace('-', '', $row['DATE']) : '';
                $row['IS_ACTIVE'] = ($rowDate >= $sevenDaysAgo) ? 1 : 0;
                $row['DAYS_SINCE'] = $rowDate ? floor((time() - strtotime($row['DATE'])) / (60 * 60 * 24)) : 0;
                
                $data[] = $row;
            }
        }
        
        $response["success"] = true;
        $response["data"] = $data;
        $response["count"] = count($data);
        $response["current_user"] = $currentUser;
        $response["date_range"] = [
            "from" => $DATE1,
            "to" => $DATE2,
            "seven_days_ago" => date('Y-m-d', strtotime('-7 days')),
            "filter_applied" => ($date1_sql < $sevenDaysAgo) ? '7_days_limit' : 'normal'
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ========================= GET RECIPIENTS (UNTUK DROPDOWN) =========================
    else if ($type === "get-recipients") {
        
        $sql = "SELECT DISTINCT name FROM M_USER 
                WHERE name IS NOT NULL 
                AND LTRIM(RTRIM(name)) != ''
                AND name != ?
                ORDER BY name";
        
        $stmt = sqlsrv_query($conn, $sql, [$currentUser]);
        
        $users = [];
        if ($stmt) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $users[] = [
                    'name' => trim($row['name']),
                    'value' => trim($row['name'])
                ];
            }
            sqlsrv_free_stmt($stmt);
        }
        
        // Add "ALL" option
        array_unshift($users, [
            'name' => 'SEMUA USER (Semua Orang)',
            'value' => 'ALL'
        ]);
        
        $response["success"] = true;
        $response["users"] = $users;
        $response["count"] = count($users);
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= GET SINGLE INFORMATION (7 HARI SAJA) =========================
    else if ($type === "get-single") {
        
        $id = (int)($_GET["id"] ?? 0);
        
        if ($id <= 0) {
            $response["message"] = 'ID tidak valid';
            echo json_encode($response);
            exit;
        }
        
        // Hanya ambil data 7 hari terakhir
        $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
        $sql = "SELECT * FROM T_INFORMATION 
                WHERE ID_INFORMATION = ? 
                AND DATE >= ?";
        $stmt = sqlsrv_query($conn, $sql, [$id, $sevenDaysAgo]);
        
        $info = null;
        if ($stmt) {
            $info = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($stmt);
        }
        
        if (!$info) {
            $response["message"] = 'Data tidak ditemukan atau sudah tidak aktif (lebih dari 7 hari)';
            echo json_encode($response);
            exit;
        }
        
        // Format date
        if (isset($info['DATE']) && is_numeric($info['DATE'])) {
            $d = (string)$info['DATE'];
            if (strlen($d) === 8) {
                $info['DATE'] = substr($d,0,4).'-'.substr($d,4,2).'-'.substr($d,6,2);
            }
        }
        
        // Cek user role
        $recipients = explode(', ', $info['PIC_TO']);
        $info['user_role'] = in_array($currentUser, $recipients) ? 'recipient' : 
                           ($info['PIC_FROM'] === $currentUser ? 'sender' : 'viewer');
        
        // Tambah info aktivasi
        $info['IS_ACTIVE'] = 1;
        $info['ACTIVE_UNTIL'] = date('Y-m-d', strtotime($info['DATE'] . ' +7 days'));
        $info['DAYS_REMAINING'] = floor((strtotime($info['ACTIVE_UNTIL']) - time()) / (60 * 60 * 24));
        
        $response["success"] = true;
        $response["data"] = $info;
        $response["retention_info"] = [
            "active_days" => 7,
            "remaining_days" => max(0, $info['DAYS_REMAINING']),
            "status" => $info['DAYS_REMAINING'] > 0 ? "active" : "expired"
        ];
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= GET ALL INFORMATION FOR ADMIN (7 HARI) =========================
    else if ($type === "get-all") {
        
        $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
        
        $sql = "SELECT * FROM T_INFORMATION 
                WHERE DATE >= ? 
                ORDER BY DATE DESC, TIME_FROM DESC";
        $stmt = sqlsrv_query($conn, $sql, [$sevenDaysAgo]);
        
        $data = [];
        if ($stmt) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                // Format date
                if (isset($row['DATE']) && is_numeric($row['DATE'])) {
                    $d = (string)$row['DATE'];
                    if (strlen($d) === 8) {
                        $row['DATE'] = substr($d,0,4).'-'.substr($d,4,2).'-'.substr($d,6,2);
                    }
                }
                
                // Tambah info aktivasi
                $rowDate = isset($row['DATE']) ? str_replace('-', '', $row['DATE']) : '';
                $row['IS_ACTIVE'] = ($rowDate >= $sevenDaysAgo) ? 1 : 0;
                $row['ACTIVE_UNTIL'] = date('Y-m-d', strtotime($row['DATE'] . ' +7 days'));
                
                $data[] = $row;
            }
            sqlsrv_free_stmt($stmt);
        }
        
        $response["success"] = true;
        $response["data"] = $data;
        $response["count"] = count($data);
        $response["retention_info"] = [
            "seven_days_ago" => $sevenDaysAgo,
            "active_count" => count(array_filter($data, function($item) { return $item['IS_ACTIVE'] == 1; })),
            "expired_count" => count(array_filter($data, function($item) { return $item['IS_ACTIVE'] == 0; }))
        ];
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= GET NOTIFICATION COUNT FOR CURRENT USER =========================
    else if ($type === "get-notification-count") {
        
        $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
        
        // Hitung hanya informasi dari user lain (PIC_FROM != currentUser)
        $sql = "SELECT COUNT(*) as unread_count
                FROM T_INFORMATION ti
                LEFT JOIN user_notification_read unr ON ti.ID_INFORMATION = unr.notification_id 
                    AND unr.user_id = ?
                WHERE ti.DATE >= ?
                AND ti.PIC_FROM != ?  -- FILTER: BUKAN DARI USER SENDIRI
                AND ti.PIC_TO LIKE '%' + ? + '%'
                AND ti.STATUS = 'Open'
                AND unr.read_at IS NULL";
        
        $params = [$currentUser, $sevenDaysAgo, $currentUser, $currentUser];
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        $unread_count = 0;
        if ($stmt) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $unread_count = (int)($row['unread_count'] ?? 0);
            sqlsrv_free_stmt($stmt);
        }
        
        $response["success"] = true;
        $response["unread_count"] = $unread_count;
        $response["current_user"] = $currentUser;
        $response["retention_days"] = 7;
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= CLEAN OLD DATA (lebih dari 7 hari) =========================
    else if ($type === "clean-old-data") {
        // Hanya untuk admin
        if ($currentUser !== 'ADMIN' && !in_array($currentUser, ['ALBERTO', 'EKO', 'EKA'])) {
            $response["message"] = 'Akses ditolak';
            echo json_encode($response);
            exit;
        }
        
        $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
        
        // Hapus notifikasi untuk data lama
        $deleteNotifSql = "DELETE FROM user_notification_read 
                          WHERE notification_id IN (
                              SELECT ID_INFORMATION FROM T_INFORMATION 
                              WHERE DATE < ?
                          )";
        sqlsrv_query($conn, $deleteNotifSql, [$sevenDaysAgo]);
        
        // Hapus data informasi lama (lebih dari 7 hari)
        $deleteInfoSql = "DELETE FROM T_INFORMATION WHERE DATE < ?";
        $deleteStmt = sqlsrv_query($conn, $deleteInfoSql, [$sevenDaysAgo]);
        
        if ($deleteStmt) {
            $response["success"] = true;
            $response["message"] = 'Data lama (lebih dari 7 hari) berhasil dibersihkan';
            $response["cutoff_date"] = $sevenDaysAgo;
        } else {
            $response["message"] = 'Gagal membersihkan data lama';
        }
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= GET STATISTICS =========================
    else if ($type === "get-stats") {
        
        $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN STATUS = 'Open' THEN 1 ELSE 0 END) as open_count,
                    SUM(CASE WHEN STATUS = 'On Progress' THEN 1 ELSE 0 END) as progress_count,
                    SUM(CASE WHEN STATUS = 'Closed' THEN 1 ELSE 0 END) as closed_count,
                    SUM(CASE WHEN PIC_FROM = ? THEN 1 ELSE 0 END) as sent_by_me,
                    SUM(CASE WHEN PIC_TO LIKE '%' + ? + '%' THEN 1 ELSE 0 END) as received_by_me
                FROM T_INFORMATION
                WHERE DATE >= ?";
        
        $params = [$currentUser, $currentUser, $sevenDaysAgo];
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        $stats = [
            'total' => 0,
            'open_count' => 0,
            'progress_count' => 0,
            'closed_count' => 0,
            'sent_by_me' => 0,
            'received_by_me' => 0
        ];
        
        if ($stmt) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $stats = [
                'total' => (int)($row['total'] ?? 0),
                'open_count' => (int)($row['open_count'] ?? 0),
                'progress_count' => (int)($row['progress_count'] ?? 0),
                'closed_count' => (int)($row['closed_count'] ?? 0),
                'sent_by_me' => (int)($row['sent_by_me'] ?? 0),
                'received_by_me' => (int)($row['received_by_me'] ?? 0)
            ];
            sqlsrv_free_stmt($stmt);
        }
        
        // Hitung unread notifications (hanya dari user lain)
        $unreadSql = "SELECT COUNT(*) as unread_count
                     FROM T_INFORMATION ti
                     LEFT JOIN user_notification_read unr ON ti.ID_INFORMATION = unr.notification_id 
                         AND unr.user_id = ?
                     WHERE ti.DATE >= ?
                     AND ti.PIC_FROM != ?
                     AND ti.PIC_TO LIKE '%' + ? + '%'
                     AND ti.STATUS = 'Open'
                     AND unr.read_at IS NULL";
        
        $unreadStmt = sqlsrv_query($conn, $unreadSql, [$currentUser, $sevenDaysAgo, $currentUser, $currentUser]);
        $unread_count = 0;
        if ($unreadStmt) {
            $row = sqlsrv_fetch_array($unreadStmt, SQLSRV_FETCH_ASSOC);
            $unread_count = (int)($row['unread_count'] ?? 0);
            sqlsrv_free_stmt($unreadStmt);
        }
        
        $stats['unread_count'] = $unread_count;
        
        $response["success"] = true;
        $response["stats"] = $stats;
        $response["retention_days"] = 7;
        $response["date_range"] = [
            "from" => date('Y-m-d', strtotime('-7 days')),
            "to" => date('Y-m-d')
        ];
        
        echo json_encode($response);
        exit;
    }
    
    else {
        $response["message"] = "Tipe aksi tidak dikenal: $type";
        echo json_encode($response);
        exit;
    }

} catch (Exception $e) {
    $response["message"] = 'Server error: ' . $e->getMessage();
    $response["trace"] = $e->getTraceAsString();
    echo json_encode($response);
    exit;
}

// Close connection
if ($conn) {
    sqlsrv_close($conn);                                        
}
?>