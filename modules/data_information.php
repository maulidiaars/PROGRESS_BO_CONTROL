// modules/data_information.php - PERBAIKAN BAGIAN GET RECIPIENTS
<?php
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

// Helper function for success response
function successResponse($message, $data = []) {
    $response = [
        "success" => true,
        "message" => $message,
        "timestamp" => date('Y-m-d H:i:s'),
        "data" => $data
    ];
    return $response;
}

// Helper function for error response
function errorResponse($message, $errorData = null) {
    $response = [
        "success" => false,
        "message" => $message,
        "timestamp" => date('Y-m-d H:i:s')
    ];
    
    if ($errorData) {
        $response['error'] = $errorData;
    }
    
    return $response;
}

try {
    // ========================= GET RECIPIENTS (UNTUK DROPDOWN) =========================
    if ($type === "get-recipients") {
        
        // QUERY YANG LEBIH AMAN DAN LENGKAP
        $sql = "SELECT DISTINCT 
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
        
        $stmt = sqlsrv_query($conn, $sql, [$currentUser]);
        
        $users = [];
        if ($stmt) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                if (!empty($row['name'])) {
                    $users[] = [
                        'name' => trim($row['name']),
                        'value' => trim($row['name']),
                        'department' => $row['department'] ?? 'UNKNOWN'
                    ];
                }
            }
            sqlsrv_free_stmt($stmt);
        }
        
        // Debug log untuk melihat user yang ditemukan
        error_log("Found " . count($users) . " users for recipient selection");
        
        // Add "ALL" option
        array_unshift($users, [
            'name' => 'SEMUA USER (Semua Orang)',
            'value' => 'ALL',
            'department' => 'ALL_USERS'
        ]);
        
        $response = [
            "success" => true,
            "message" => 'Daftar penerima berhasil diambil',
            "users" => $users,
            "count" => count($users),
            "current_user" => $currentUser,
            "timestamp" => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ========================= INPUT DATA INFORMATION =========================
    if ($type === "input") {
        
        $DATE      = $_POST["date"] ?? date('Ymd');
        $TIME_FROM = $_POST["txt-time1"] ?? date('H:i');
        $PIC_FROM  = $currentUser;
        $ITEM      = trim($_POST["txt-item"] ?? '');
        $REQUEST   = trim($_POST["txt-request"] ?? '');
        $recipients = $_POST["recipients"] ?? '';
        
        // Validasi
        if (empty($ITEM) || empty($REQUEST)) {
            echo json_encode(errorResponse('Item dan Request tidak boleh kosong'));
            exit;
        }
        
        if (empty($recipients)) {
            echo json_encode(errorResponse('Pilih minimal satu penerima'));
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
                            AND name != 'SYSTEM'
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
            echo json_encode(errorResponse('Tidak ada penerima yang valid'));
            exit;
        }
        
        // Sort recipients
        sort($recipientArray);
        $PIC_TO_COMBINED = implode(', ', $recipientArray);
            
        // CEK DUPLIKASI: Only check for 7 days
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
        
        // If duplicate exists within 7 days, show warning
        if ($duplicateCount > 0) {
            echo json_encode(errorResponse('Anda sudah mengirim informasi ini dalam 7 hari terakhir.'));
            exit;
        }
        
        // Insert into T_INFORMATION
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
            
            // Insert into user_notification_read for each recipient
            foreach ($recipientArray as $recipient) {
                if (empty($recipient)) continue;
                
                $notifSql = "INSERT INTO user_notification_read (user_id, notification_id, created_at) 
                             VALUES (?, ?, GETDATE())";
                sqlsrv_query($conn, $notifSql, [$recipient, $new_id]);
            }
            
            // Trigger custom event for frontend
            $response = successResponse(
                'Informasi berhasil dikirim ke ' . count($recipientArray) . ' penerima',
                [
                    "id" => $new_id,
                    "recipient_count" => count($recipientArray),
                    "recipients" => $recipientArray,
                    "retention_days" => 7,
                    "visible_until" => date('Y-m-d', strtotime('+7 days'))
                ]
            );
            
            // Add event trigger
            $response['trigger_event'] = 'informationAdded';
            
        } else {
            $errors = sqlsrv_errors();
            $response = errorResponse(
                "Gagal menyimpan informasi",
                ["sql_error" => print_r($errors, true)]
            );
        }
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= UPDATE FROM (SENDER) =========================
    else if ($type === "update-from") {
        
        $ID_INFORMATION = (int)($_POST["txt-id-information"] ?? 0);
        $TIME_FROM = $_POST["txt-timefrom-update"] ?? date('H:i');
        $PIC_FROM = $_POST["txt-picfrom-update"] ?? $currentUser;
        $ITEM = trim($_POST["txt-item-update"] ?? '');
        $REQUEST = trim($_POST["txt-request-update"] ?? '');
        
        // Validation
        if ($ID_INFORMATION <= 0) {
            echo json_encode(errorResponse('ID Information tidak valid'));
            exit;
        }
        
        if (empty($ITEM) || empty($REQUEST)) {
            echo json_encode(errorResponse('Item dan Request tidak boleh kosong'));
            exit;
        }
        
        // Check if user is the sender
        $checkSql = "SELECT PIC_FROM, STATUS FROM T_INFORMATION WHERE ID_INFORMATION = ?";
        $checkStmt = sqlsrv_query($conn, $checkSql, [$ID_INFORMATION]);
        
        if (!$checkStmt) {
            echo json_encode(errorResponse('Data tidak ditemukan'));
            exit;
        }
        
        $info = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
        if (!$info) {
            echo json_encode(errorResponse('Data tidak ditemukan'));
            exit;
        }
        
        // Validate: only PIC_FROM can update
        if ($info['PIC_FROM'] !== $currentUser) {
            echo json_encode(errorResponse('Anda tidak berhak mengedit informasi ini'));
            exit;
        }
        
        // Validate: cannot edit if status is On Progress or Closed
        if ($info['STATUS'] === 'On Progress' || $info['STATUS'] === 'Closed') {
            echo json_encode(errorResponse('Tidak bisa mengedit informasi yang sudah diproses atau ditutup'));
            exit;
        }
        
        // Update information
        $updateSql = "UPDATE T_INFORMATION 
                      SET TIME_FROM = ?, 
                          ITEM = ?, 
                          REQUEST = ?
                      WHERE ID_INFORMATION = ?";
        
        $params = [$TIME_FROM, $ITEM, $REQUEST, $ID_INFORMATION];
        $updateStmt = sqlsrv_query($conn, $updateSql, $params);
        
        if ($updateStmt) {
            $response = successResponse('Informasi berhasil diupdate', [
                "id" => $ID_INFORMATION,
                "trigger_event" => "informationUpdated"
            ]);
        } else {
            $response = errorResponse('Gagal update informasi');
        }
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= UPDATE TO (RECIPIENT) =========================
    else if ($type === "update-to") {
        
        $ID_INFORMATION = (int)($_POST["txt-id-information2"] ?? 0);
        $TIME_TO = $_POST["txt-timeto-update"] ?? date('H:i');
        $PIC_TO = $_POST["txt-picto-update"] ?? $currentUser;
        $REMARK = trim($_POST["txt-remark-update"] ?? '');
        $ACTION_TYPE = $_POST["action_type"] ?? 'on_progress';
        
        // Validation
        if ($ID_INFORMATION <= 0) {
            echo json_encode(errorResponse('ID Information tidak valid'));
            exit;
        }
        
        // Check data (ONLY LAST 7 DAYS)
        $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
        $checkSql = "SELECT PIC_TO, STATUS, ITEM, REQUEST, PIC_FROM, DATE 
                     FROM T_INFORMATION 
                     WHERE ID_INFORMATION = ? 
                     AND DATE >= ?";
        $checkStmt = sqlsrv_query($conn, $checkSql, [$ID_INFORMATION, $sevenDaysAgo]);
        
        if (!$checkStmt) {
            echo json_encode(errorResponse('Data tidak ditemukan atau sudah tidak aktif (lebih dari 7 hari)'));
            exit;
        }
        
        $info = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
        if (!$info) {
            echo json_encode(errorResponse('Data tidak ditemukan atau sudah tidak aktif (lebih dari 7 hari)'));
            exit;
        }
        
        // Check if user is a recipient
        $recipients = explode(', ', $info['PIC_TO']);
        $isRecipient = in_array($currentUser, $recipients);
        
        if (!$isRecipient) {
            echo json_encode(errorResponse('Anda tidak berhak mengupdate informasi ini'));
            exit;
        }
        
        // Check current status - cannot update if already Closed
        if ($info['STATUS'] === 'Closed') {
            echo json_encode(errorResponse('Informasi ini sudah ditutup'));
            exit;
        }
        
        // Determine new status
        $new_status = ($ACTION_TYPE === 'closed') ? 'Closed' : 'On Progress';
        
        // Validation: for Closed, remark is required
        if ($ACTION_TYPE === 'closed' && empty($REMARK)) {
            echo json_encode(errorResponse('Remark wajib diisi untuk menutup informasi'));
            exit;
        }
        
        // Update information
        $updateSql = "UPDATE T_INFORMATION 
                      SET TIME_TO = ?, 
                          REMARK = ?, 
                          STATUS = ?
                      WHERE ID_INFORMATION = ?";
        
        $params = [$TIME_TO, $REMARK, $new_status, $ID_INFORMATION];
        $updateStmt = sqlsrv_query($conn, $updateSql, $params);
        
        if ($updateStmt) {
            // Update notification for this user as read
            $notifSql = "UPDATE user_notification_read SET read_at = GETDATE() 
                         WHERE user_id = ? AND notification_id = ?";
            sqlsrv_query($conn, $notifSql, [$currentUser, $ID_INFORMATION]);
            
            // If status Closed, update notifications for all other recipients
            if ($new_status === 'Closed') {
                foreach ($recipients as $recipient) {
                    if ($recipient !== $currentUser) {
                        $notifAllSql = "UPDATE user_notification_read SET read_at = GETDATE() 
                                       WHERE user_id = ? AND notification_id = ? AND read_at IS NULL";
                        sqlsrv_query($conn, $notifAllSql, [$recipient, $ID_INFORMATION]);
                    }
                }
            }
            
            $response = successResponse("Status berhasil diupdate ke " . $new_status, [
                "id" => $ID_INFORMATION,
                "new_status" => $new_status,
                "trigger_event" => "informationStatusChanged"
            ]);
        } else {
            $response = errorResponse('Gagal update status');
        }
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= DELETE INFORMATION =========================
    else if ($type === "delete") {
        
        $ID_INFORMATION = (int)($_POST["id_information"] ?? 0);
        
        if ($ID_INFORMATION <= 0) {
            echo json_encode(errorResponse('ID Information tidak valid'));
            exit;
        }
        
        // Check if user is the sender (ONLY LAST 7 DAYS)
        $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
        $checkSql = "SELECT PIC_FROM, DATE FROM T_INFORMATION 
                     WHERE ID_INFORMATION = ? 
                     AND DATE >= ?";
        $checkStmt = sqlsrv_query($conn, $checkSql, [$ID_INFORMATION, $sevenDaysAgo]);
        
        if (!$checkStmt) {
            echo json_encode(errorResponse('Data tidak ditemukan atau sudah tidak aktif (lebih dari 7 hari)'));
            exit;
        }
        
        $info = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
        if (!$info) {
            echo json_encode(errorResponse('Data tidak ditemukan atau sudah tidak aktif (lebih dari 7 hari)'));
            exit;
        }
        
        // Validate: only PIC_FROM can delete
        if ($info['PIC_FROM'] !== $currentUser) {
            echo json_encode(errorResponse('Anda tidak berhak menghapus informasi ini'));
            exit;
        }
        
        // Delete from user_notification_read first (foreign key constraint)
        $deleteNotifSql = "DELETE FROM user_notification_read WHERE notification_id = ?";
        sqlsrv_query($conn, $deleteNotifSql, [$ID_INFORMATION]);
        
        // Delete information
        $deleteSql = "DELETE FROM T_INFORMATION WHERE ID_INFORMATION = ?";
        $deleteStmt = sqlsrv_query($conn, $deleteSql, [$ID_INFORMATION]);
        
        if ($deleteStmt) {
            $response = successResponse('Informasi berhasil dihapus', [
                "id" => $ID_INFORMATION,
                "trigger_event" => "informationDeleted"
            ]);
        } else {
            $response = errorResponse('Gagal menghapus informasi');
        }
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= FETCH DATA =========================
    else if ($type === "fetch") {
        
        $DATE1 = $_GET["date1"] ?? date('Y-m-d');
        $DATE2 = $_GET["date2"] ?? date('Y-m-d');
        $date1_sql = str_replace('-', '', $DATE1);
        $date2_sql = str_replace('-', '', $DATE2);
        
        // Only show last 7 days
        $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
        
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
                    -- Determine user role
                    CASE
                        WHEN CHARINDEX(?, PIC_TO) > 0 THEN 'recipient'
                        WHEN PIC_FROM = ? THEN 'sender'
                        ELSE 'viewer'
                    END as user_role,
                    -- Check if read by this user
                    (SELECT TOP 1 read_at FROM user_notification_read 
                     WHERE user_id = ? AND notification_id = ID_INFORMATION) as read_at,
                    -- Calculate days since created
                    DATEDIFF(day, CONVERT(date, CONVERT(varchar, DATE)), GETDATE()) as days_old
                FROM T_INFORMATION
                WHERE DATE BETWEEN ? AND ?
                AND DATE >= ?
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
                
                // Calculate retention status
                $row['DAYS_OLD'] = $row['days_old'] ?? 0;
                $row['IS_WITHIN_7_DAYS'] = ($row['DAYS_OLD'] <= 7) ? 1 : 0;
                
                // Determine expired status
                if ($row['DAYS_OLD'] > 7) {
                    $row['EXPIRED'] = true;
                    $row['EXPIRED_SINCE'] = $row['DAYS_OLD'] - 7;
                } else {
                    $row['EXPIRED'] = false;
                    $row['REMAINING_DAYS'] = 7 - $row['DAYS_OLD'];
                }
                
                // Is unread? - Only for recipients AND NOT from user self
                $isRecipient = ($row['user_role'] === 'recipient');
                $isFromSelf = ($row['PIC_FROM'] === $currentUser);
                $row['IS_UNREAD'] = ($row['read_at'] === null && $isRecipient && !$isFromSelf && $row['STATUS'] !== 'Closed') ? 1 : 0;
                
                $data[] = $row;
            }
        }
        
        $response = successResponse('Data berhasil diambil', [
            "data" => $data,
            "count" => count($data),
            "current_user" => $currentUser,
            "retention" => [
                "policy" => "7_days_display_only",
                "display_range" => [
                    "from" => date('Y-m-d', strtotime('-7 days')),
                    "to" => date('Y-m-d'),
                    "message" => "Menampilkan data 7 hari terakhir saja"
                ]
            ]
        ]);
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ========================= GET SINGLE INFORMATION =========================
    else if ($type === "get-single") {
        
        $id = (int)($_GET["id"] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(errorResponse('ID tidak valid'));
            exit;
        }
        
        // Only get data from last 7 days
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
            echo json_encode(errorResponse('Data tidak ditemukan atau sudah tidak aktif (lebih dari 7 hari)'));
            exit;
        }
        
        // Format date
        if (isset($info['DATE']) && is_numeric($info['DATE'])) {
            $d = (string)$info['DATE'];
            if (strlen($d) === 8) {
                $info['DATE'] = substr($d,0,4).'-'.substr($d,4,2).'-'.substr($d,6,2);
            }
        }
        
        // Check user role
        $recipients = explode(', ', $info['PIC_TO']);
        $info['user_role'] = in_array($currentUser, $recipients) ? 'recipient' : 
                           ($info['PIC_FROM'] === $currentUser ? 'sender' : 'viewer');
        
        // Add activation info
        $info['IS_ACTIVE'] = 1;
        $info['ACTIVE_UNTIL'] = date('Y-m-d', strtotime($info['DATE'] . ' +7 days'));
        $info['DAYS_REMAINING'] = floor((strtotime($info['ACTIVE_UNTIL']) - time()) / (60 * 60 * 24));
        
        $response = successResponse('Data berhasil diambil', [
            "data" => $info,
            "retention_info" => [
                "active_days" => 7,
                "remaining_days" => max(0, $info['DAYS_REMAINING']),
                "status" => $info['DAYS_REMAINING'] > 0 ? "active" : "expired"
            ]
        ]);
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= GET ALL INFORMATION FOR ADMIN =========================
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
                
                // Add activation info
                $rowDate = isset($row['DATE']) ? str_replace('-', '', $row['DATE']) : '';
                $row['IS_ACTIVE'] = ($rowDate >= $sevenDaysAgo) ? 1 : 0;
                $row['ACTIVE_UNTIL'] = date('Y-m-d', strtotime($row['DATE'] . ' +7 days'));
                
                $data[] = $row;
            }
            sqlsrv_free_stmt($stmt);
        }
        
        $response = successResponse('Data berhasil diambil', [
            "data" => $data,
            "count" => count($data),
            "retention_info" => [
                "seven_days_ago" => $sevenDaysAgo,
                "active_count" => count(array_filter($data, function($item) { return $item['IS_ACTIVE'] == 1; })),
                "expired_count" => count(array_filter($data, function($item) { return $item['IS_ACTIVE'] == 0; }))
            ]
        ]);
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= GET NOTIFICATION COUNT =========================
    else if ($type === "get-notification-count") {
        
        $sevenDaysAgo = date('Ymd', strtotime('-7 days'));
        
        // Hitung hanya informasi dari user lain (PIC_FROM != currentUser)
        $sql = "SELECT COUNT(*) as unread_count
                FROM T_INFORMATION ti
                LEFT JOIN user_notification_read unr ON ti.ID_INFORMATION = unr.notification_id 
                    AND unr.user_id = ?
                WHERE ti.DATE >= ?
                AND ti.PIC_FROM != ?
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
        
        $response = successResponse('Count berhasil diambil', [
            "unread_count" => $unread_count,
            "current_user" => $currentUser,
            "retention_days" => 7
        ]);
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= CLEAN OLD DATA =========================
    else if ($type === "clean-old-data") {
        // Hanya untuk admin
        if ($currentUser !== 'ADMIN' && !in_array($currentUser, ['ALBERTO', 'EKO', 'EKA'])) {
            echo json_encode(errorResponse('Akses ditolak'));
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
            $response = successResponse('Data lama (lebih dari 7 hari) berhasil dibersihkan', [
                "cutoff_date" => $sevenDaysAgo
            ]);
        } else {
            $response = errorResponse('Gagal membersihkan data lama');
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
        
        $response = successResponse('Stats berhasil diambil', [
            "stats" => $stats,
            "retention_days" => 7,
            "date_range" => [
                "from" => date('Y-m-d', strtotime('-7 days')),
                "to" => date('Y-m-d')
            ]
        ]);
        
        echo json_encode($response);
        exit;
    }
    
    // ========================= DEFAULT RESPONSE =========================
    else {
        echo json_encode(errorResponse("Tipe aksi tidak dikenal: $type"));
        exit;
    }

} catch (Exception $e) {
    $response = errorResponse(
        'Server error: ' . $e->getMessage(),
        ["trace" => $e->getTraceAsString()]
    );
    echo json_encode($response);
    exit;
}

// Close connection
if ($conn) {
    sqlsrv_close($conn);
}
?>