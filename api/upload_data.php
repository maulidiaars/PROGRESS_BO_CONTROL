<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database dengan class baru
require_once __DIR__ . '/../config/database.php';

// Cek vendor autoload
if (!file_exists("../vendor/autoload.php")) {
    echo json_encode([
        'success' => false,
        'message' => 'Vendor autoload tidak ditemukan',
        'error_code' => 'VENDOR_MISSING'
    ]);
    exit;
}

require "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

date_default_timezone_set("Asia/Jakarta");

$response = [
    'success' => false, 
    'message' => '', 
    'count' => 0, 
    'processing_time' => 0,
    'type' => '',
    'timestamp' => date('Y-m-d H:i:s'),
    'debug' => []
];

$startTime = microtime(true);

try {
    // ============ VALIDASI FILE ============
    if (!isset($_FILES["file"]) || $_FILES["file"]["error"] != 0) {
        $errorCode = $_FILES["file"]["error"] ?? 'UNKNOWN';
        $errorMessages = [
            0 => 'No error',
            1 => 'File exceeds upload_max_filesize',
            2 => 'File exceeds MAX_FILE_SIZE',
            3 => 'File partially uploaded',
            4 => 'No file uploaded',
            6 => 'Missing temporary folder',
            7 => 'Failed to write to disk',
            8 => 'PHP extension stopped the upload'
        ];
        
        throw new Exception("File upload error (" . $errorCode . "): " . 
                          ($errorMessages[$errorCode] ?? 'Unknown error'));
    }
    
    $type = $_POST["type"] ?? '';
    $file_name = $_FILES["file"]["name"];
    $file_tmp = $_FILES["file"]["tmp_name"];
    $file_size = $_FILES["file"]["size"];
    
    $response['type'] = $type;
    $response['file_name'] = $file_name;
    $response['file_size'] = $file_size;
    
    // Validasi type
    if (!in_array($type, ["upload_bo", "upload_order", "upload_part"])) {
        throw new Exception("Invalid upload type. Must be: upload_bo, upload_order, or upload_part");
    }
    
    // Validasi ekstensi
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); 
    $allowed_extension = ["xls", "xlsx", "csv"]; 
    
    if (!in_array($extension, $allowed_extension)) {
        throw new Exception("Invalid file format. Please use .xls, .xlsx, or .csv");
    }
    
    // Ukuran file (max 20MB)
    $maxSize = 20 * 1024 * 1024;
    if ($file_size > $maxSize) {
        throw new Exception("File size too large (" . round($file_size/1024/1024, 2) . 
                          "MB). Maximum 20MB");
    }
    
    // ============ DATABASE CONNECTION ============
    $conn = Database::getConnection();
    if ($conn === false) {
        throw new Exception("Database connection failed. Please try again.");
    }
    
    // ============ FILE PROCESSING ============
    $reader = null;
    
    switch($extension) {
        case 'csv':
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            break;
        case 'xls':
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            break;
        case 'xlsx':
        default:
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            break;
    }
    
    // Set reader options
    $reader->setReadDataOnly(true);
    $reader->setIncludeCharts(false);
    
    // Load spreadsheet
    $spreadsheet = $reader->load($file_tmp);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestDataRow();
    
    $response['total_rows'] = $highestRow;
    
    // ============ START TRANSACTION ============
    if (!sqlsrv_begin_transaction($conn)) {
        throw new Exception("Failed to start database transaction");
    }
    
    $successCount = 0;
    $batchCount = 0;
    
    // ============ FUNCTION FORMAT EXCEL TIME ============
    function formatExcelTime($timeValue) {
        if (empty($timeValue)) {
            return '';
        }
        
        // Jika sudah string dengan format HH:MM
        if (is_string($timeValue) && strpos($timeValue, ':') !== false) {
            $timeValue = trim($timeValue);
            
            // Pastikan format HH:MM
            $parts = explode(':', $timeValue);
            if (count($parts) >= 2) {
                $hour = str_pad(trim($parts[0]), 2, '0', STR_PAD_LEFT);
                $minute = str_pad(trim($parts[1]), 2, '0', STR_PAD_LEFT);
                
                // Handle 24:00 -> 00:00
                if ($hour == '24') {
                    $hour = '00';
                }
                
                return $hour . ':' . $minute;
            }
            return $timeValue;
        }
        
        // Jika numeric (Excel time format)
        if (is_numeric($timeValue)) {
            // Excel time: 1 = 24 hours, 0.5 = 12 hours
            $hours = $timeValue * 24;
            $hour = floor($hours);
            $minutes = ($hours - $hour) * 60;
            $minute = round($minutes);
            
            // Handle 24:00
            if ($hour == 24) {
                $hour = 0;
            }
            
            return sprintf("%02d:%02d", $hour, $minute);
        }
        
        // Coba parse sebagai string biasa
        $timeValue = trim((string)$timeValue);
        
        // Remove any non-time characters
        $timeValue = preg_replace('/[^0-9:\.]/', '', $timeValue);
        
        // Jika ada format seperti "0.:95" (error parsing)
        if (strpos($timeValue, '.:') !== false) {
            $timeValue = str_replace('.:', ':', $timeValue);
        }
        
        // Coba parse sebagai HH:MM
        if (preg_match('/(\d{1,2})[:\.](\d{1,2})/', $timeValue, $matches)) {
            $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $minute = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            
            if ($hour == '24') {
                $hour = '00';
            }
            
            return $hour . ':' . $minute;
        }
        
        // Default: return as is
        return $timeValue;
    }
        
    // ============ UPLOAD BO (SIMPLE INSERT) - DENGAN FILTER ORDER ============
    if ($type == "upload_bo") {
        // TENTUKAN JAM DARI WAKTU UPLOAD
        $uploadHour = date('H'); // Jam saat upload
        $currentDate = date('Ymd'); // Tanggal hari ini
        
        // LOGIKA SHIFT
        if ($uploadHour >= 8 && $uploadHour <= 20) {
            $HOUR = $uploadHour;
            $SHIFT = 1; // Day Shift
        } else {
            $HOUR = $uploadHour;
            $SHIFT = 2; // Night Shift
            
            if ($uploadHour >= 0 && $uploadHour <= 7) {
                $currentDate = date('Ymd'); // Tetap tanggal hari ini
            }
        }
        
        $response['debug']['upload_hour'] = $uploadHour;
        $response['debug']['assigned_hour'] = $HOUR;
        $response['debug']['assigned_shift'] = $SHIFT;
        $response['debug']['assigned_date'] = $currentDate;
        
        // 1. AMBIL SEMUA PART YANG ADA ORDER HARI INI
        $orderCheckSql = "SELECT DISTINCT PART_NO FROM T_ORDER WHERE DELV_DATE = ?";
        $orderStmt = sqlsrv_prepare($conn, $orderCheckSql, [$currentDate]);
        $validParts = [];
        
        if ($orderStmt && sqlsrv_execute($orderStmt)) {
            while ($orderRow = sqlsrv_fetch_array($orderStmt, SQLSRV_FETCH_ASSOC)) {
                $validParts[] = str_replace(" ", "", trim($orderRow['PART_NO']));
            }
        }
        sqlsrv_free_stmt($orderStmt);
        
        $response['debug']['valid_parts_count'] = count($validParts);
        $response['debug']['valid_parts_sample'] = array_slice($validParts, 0, 5);
        
        // 2. HAPUS HANYA DATA UNTUK JAM YANG SAMA
        $delete_sql = "DELETE FROM T_UPDATE_BO WHERE DATE = ? AND HOUR = ?";
        $delete_stmt = sqlsrv_prepare($conn, $delete_sql, [$currentDate, $HOUR]);
        if ($delete_stmt) {
            sqlsrv_execute($delete_stmt);
            $deletedRows = sqlsrv_rows_affected($delete_stmt);
            $response['debug']['deleted_rows'] = $deletedRows;
            sqlsrv_free_stmt($delete_stmt);
        }
        
        // 3. INSERT DATA HANYA YANG ADA DI ORDER
        $insertQuery = "INSERT INTO T_UPDATE_BO (DATE, HOUR, SHIFT, CODE, PART_NO, PART_DESC, CLS, TRAN_QTY, LOT, WH_CODE) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $validCount = 0;
        $invalidCount = 0;
        $invalidParts = [];
        
        for ($row = 2; $row <= $highestRow; $row++) {
            // Skip row kosong
            $cellA = $worksheet->getCell("A" . $row)->getValue();
            if (empty(trim($cellA))) {
                continue;
            }
            
            $tanggalMentah = $cellA;
            $tanggalObjek = DateTime::createFromFormat("d/m/y", $tanggalMentah);
            
            if (!$tanggalObjek) {
                $tanggalObjek = DateTime::createFromFormat("d-m-y", $tanggalMentah);
                if (!$tanggalObjek) {
                    error_log("Invalid date format at row $row: $tanggalMentah");
                    continue;
                }
            }
            
            $DATE = $tanggalObjek->format("Ymd");
            
            // Untuk Night Shift jam 0-7, date harus sama dengan file
            if ($SHIFT == 2 && ($HOUR >= 0 && $HOUR <= 7)) {
                $DATE = $tanggalObjek->format("Ymd");
            }
            
            // AMBIL DATA DARI KOLOM FILE BO
            $SHIFT_FILE = (int)trim($worksheet->getCell("B" . $row)->getValue()) ?: 1;
            $CODE = trim($worksheet->getCell("C" . $row)->getValue());
            $PART_NO_RAW = $worksheet->getCell("D" . $row)->getValue();
            $PART_DESC = trim($worksheet->getCell("E" . $row)->getValue());
            $CLS = trim($worksheet->getCell("F" . $row)->getValue());
            $TRAN_QTY = (int)$worksheet->getCell("G" . $row)->getValue();
            $LOT = (int)$worksheet->getCell("H" . $row)->getValue();
            $WH_CODE = trim($worksheet->getCell("I" . $row)->getValue());
            
            // Process PART_NO - hapus spasi
            $PART_NO = '';
            if (!empty($PART_NO_RAW)) {
                $PART_NO = str_replace(" ", "", trim($PART_NO_RAW));
            }
            
            // Validasi data penting
            if (empty($PART_NO) || empty($DATE) || $TRAN_QTY <= 0) {
                error_log("Invalid data at row $row: PART_NO=$PART_NO, DATE=$DATE, QTY=$TRAN_QTY");
                continue;
            }
            
            // ========== FILTER: CEK APAKAH PART ADA DI ORDER ==========
            if (!in_array($PART_NO, $validParts)) {
                $invalidCount++;
                $invalidParts[] = [
                    'part_no' => $PART_NO,
                    'part_desc' => $PART_DESC,
                    'qty' => $TRAN_QTY
                ];
                error_log("SKIPPED: Part $PART_NO not found in orders for date $DATE");
                continue; // SKIP DATA INI!
            }
            
            // JIKA VALID, INSERT KE DATABASE
            $params = [
                $DATE, 
                $HOUR,
                $SHIFT,
                $CODE, 
                $PART_NO, 
                $PART_DESC, 
                $CLS, 
                $TRAN_QTY, 
                $LOT, 
                $WH_CODE
            ];
            
            $stmt = sqlsrv_prepare($conn, $insertQuery, $params);
            
            if ($stmt && sqlsrv_execute($stmt)) {
                $validCount++;
            } else {
                $errors = sqlsrv_errors();
                error_log("Failed to insert BO data at row $row: " . print_r($errors, true));
                $response['debug']["row_$row_error"] = $errors;
            }
            
            if ($stmt) sqlsrv_free_stmt($stmt);
        }
        
        $response['debug']['valid_inserted'] = $validCount;
        $response['debug']['invalid_skipped'] = $invalidCount;
        $response['debug']['invalid_parts'] = $invalidParts;
        
        $response['message'] = "✅ Successfully uploaded $validCount BO records (Skipped $invalidCount without order) for hour $HOUR (Shift: $SHIFT)";
        $successCount = $validCount;
    }
    
    // ============ UPLOAD ORDER ============
    else if ($type == "upload_order") {
        // TENTUKAN START ROW BERDASARKAN HEADER
        $startRow = 1;
        
        // Cari header "NO" atau "SUGGESTION BY CYCLE"
        for ($checkRow = 1; $checkRow <= 10; $checkRow++) {
            $cellA = $worksheet->getCell("A" . $checkRow)->getValue();
            if (stripos($cellA, "NO") !== false || stripos($cellA, "SUGGESTION BY CYCLE") !== false) {
                $startRow = $checkRow + 1; // Mulai dari baris setelah header
                break;
            }
        }
        
        $response['debug']['start_row'] = $startRow;
        
        // Hapus temporary table jika ada
        $truncate_sql = "IF OBJECT_ID('T_ORDER_TMP', 'U') IS NOT NULL TRUNCATE TABLE T_ORDER_TMP";
        sqlsrv_query($conn, $truncate_sql);
        
        // Buat temporary table jika tidak ada
        $create_temp_sql = "
            IF OBJECT_ID('T_ORDER_TMP', 'U') IS NULL
            BEGIN
                CREATE TABLE T_ORDER_TMP (
                    ID INT IDENTITY(1,1) PRIMARY KEY,
                    SUPPLIER_CODE VARCHAR(20),
                    SUPPLIER_NAME VARCHAR(100),
                    PLANT VARCHAR(10),
                    PART_NO VARCHAR(50),
                    PART_NAME VARCHAR(200),
                    CLASS VARCHAR(10),
                    LOT_SIZE INT,
                    DELV_DATE INT,
                    CYCLE INT,
                    ETD VARCHAR(10),
                    ETA VARCHAR(10),
                    ORD_QTY INT,
                    UM VARCHAR(10),
                    BOX_NO VARCHAR(20)
                )
            END
        ";
        sqlsrv_query($conn, $create_temp_sql);
        
        $insertTempQuery = "
            INSERT INTO T_ORDER_TMP 
            (SUPPLIER_CODE, SUPPLIER_NAME, PLANT, PART_NO, PART_NAME, CLASS, LOT_SIZE, 
             DELV_DATE, CYCLE, ETD, ETA, ORD_QTY, UM, BOX_NO) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $batchData = [];
        
        for ($row = $startRow; $row <= $highestRow; $row++) {
            // Cek jika ini baris kosong (semua kolom kosong)
            $allEmpty = true;
            for ($col = 1; $col <= 15; $col++) {
                $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                if (!empty(trim($cellValue))) {
                    $allEmpty = false;
                    break;
                }
            }
            
            if ($allEmpty) {
                continue;
            }
            
            // Ambil data dari kolom yang benar
            // Kolom Excel: A=NO, B=Supplier Code, C=Supplier Name, D=Plant, E=Part Number, 
            // F=Part Name, G=Class, H=LOT Size, I=Delivery Date, J=Cycle, 
            // K=ETD, L=ETA, M=Order QTY, N=UM, O=Box No
            
            $NO = trim($worksheet->getCellByColumnAndRow(1, $row)->getValue() ?? ''); // Kolom 1 (NO)
            $SUPPLIER_CODE = trim($worksheet->getCellByColumnAndRow(2, $row)->getValue() ?? ''); // Kolom 2
            $SUPPLIER_NAME = trim($worksheet->getCellByColumnAndRow(3, $row)->getValue() ?? ''); // Kolom 3
            $PLANT = trim($worksheet->getCellByColumnAndRow(4, $row)->getValue() ?? ''); // Kolom 4
            $PART_NO_RAW = $worksheet->getCellByColumnAndRow(5, $row)->getValue(); // Kolom 5
            $PART_NAME = trim($worksheet->getCellByColumnAndRow(6, $row)->getValue() ?? ''); // Kolom 6
            $CLASS = trim($worksheet->getCellByColumnAndRow(7, $row)->getValue() ?? ''); // Kolom 7
            $LOT_SIZE = (int)($worksheet->getCellByColumnAndRow(8, $row)->getValue() ?? 0); // Kolom 8
            $DELV_DATE_RAW = $worksheet->getCellByColumnAndRow(9, $row)->getValue(); // Kolom 9
            $CYCLE = (int)($worksheet->getCellByColumnAndRow(10, $row)->getValue() ?? 0); // Kolom 10
            $ETD_RAW = $worksheet->getCellByColumnAndRow(11, $row)->getValue(); // Kolom 11 (ETD)
            $ETA_RAW = $worksheet->getCellByColumnAndRow(12, $row)->getValue(); // Kolom 12 (ETA)
            $ORD_QTY = (int)($worksheet->getCellByColumnAndRow(13, $row)->getValue() ?? 0); // Kolom 13
            $UM = trim($worksheet->getCellByColumnAndRow(14, $row)->getValue() ?? ''); // Kolom 14
            $BOX_NO = trim($worksheet->getCellByColumnAndRow(15, $row)->getValue() ?? ''); // Kolom 15
            
            // Process PART_NO - handle berbagai format
            $PART_NO = '';
            if (!empty($PART_NO_RAW)) {
                if (is_numeric($PART_NO_RAW)) {
                    $PART_NO = (string)$PART_NO_RAW;
                } else {
                    $PART_NO = str_replace(" ", "", trim($PART_NO_RAW));
                }
            }
            
            // Validasi data minimal - JANGAN TERLALU KETAT
            if (empty($SUPPLIER_CODE) || empty($PART_NO)) {
                error_log("Skipping row $row: EMPTY SUPPLIER_CODE or PART_NO");
                continue;
            }
            
            // Format tanggal DELV_DATE
            $DELV_DATE = '';
            if (!empty($DELV_DATE_RAW)) {
                if (is_numeric($DELV_DATE_RAW)) {
                    $DELV_DATE = (string)$DELV_DATE_RAW;
                } else {
                    // Coba berbagai format
                    $dateStr = trim((string)$DELV_DATE_RAW);
                    if (preg_match('/\d{8}/', $dateStr, $matches)) {
                        $DELV_DATE = $matches[0];
                    } else {
                        // Coba parse sebagai Excel date
                        if (is_numeric($dateStr)) {
                            $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateStr);
                            $DELV_DATE = $excelDate->format('Ymd');
                        } else {
                            // Ambil hanya angka
                            $DELV_DATE = preg_replace('/[^0-9]/', '', $dateStr);
                        }
                    }
                }
            }
            
            // Format waktu ETD dan ETA
            $ETD = formatExcelTime($ETD_RAW);
            $ETA = formatExcelTime($ETA_RAW);
            
            // Debug untuk beberapa row pertama
            if ($row <= $startRow + 5) {
                $response['debug']["row_$row"] = [
                    'SUPPLIER_CODE' => $SUPPLIER_CODE,
                    'PART_NO' => $PART_NO,
                    'DELV_DATE_RAW' => $DELV_DATE_RAW,
                    'DELV_DATE' => $DELV_DATE,
                    'ETD_RAW' => $ETD_RAW,
                    'ETD' => $ETD,
                    'ETA_RAW' => $ETA_RAW,
                    'ETA' => $ETA,
                    'ORD_QTY' => $ORD_QTY,
                    'CYCLE' => $CYCLE
                ];
            }
            
            $batchData[] = [
                $SUPPLIER_CODE, $SUPPLIER_NAME, $PLANT, $PART_NO, $PART_NAME, 
                $CLASS, $LOT_SIZE, $DELV_DATE, $CYCLE, $ETD, $ETA, 
                $ORD_QTY, $UM, $BOX_NO
            ];
            $batchCount++;
            
            // Batch insert ke temp table
            if (count($batchData) >= 100) {
                foreach ($batchData as $params) {
                    $stmt = sqlsrv_prepare($conn, $insertTempQuery, $params);
                    if ($stmt && sqlsrv_execute($stmt)) {
                        $successCount++;
                    } else {
                        error_log("Failed to insert temp data: " . print_r(sqlsrv_errors(), true));
                    }
                    sqlsrv_free_stmt($stmt);
                }
                $batchData = [];
            }
        }
        
        // Insert remaining batch
        if (!empty($batchData)) {
            foreach ($batchData as $params) {
                $stmt = sqlsrv_prepare($conn, $insertTempQuery, $params);
                if ($stmt && sqlsrv_execute($stmt)) {
                    $successCount++;
                } else {
                    error_log("Failed to insert temp data (remaining): " . print_r(sqlsrv_errors(), true));
                }
                sqlsrv_free_stmt($stmt);
            }
        }
        
        // Insert to main table - HAPUS DUPLIKAT DULU
        $deleteDuplicatesQuery = "
            DELETE FROM T_ORDER 
            WHERE EXISTS (
                SELECT 1 FROM T_ORDER_TMP t 
                WHERE T_ORDER.PART_NO = t.PART_NO 
                AND T_ORDER.DELV_DATE = t.DELV_DATE 
                AND T_ORDER.CYCLE = t.CYCLE
                AND T_ORDER.SUPPLIER_CODE = t.SUPPLIER_CODE
            )
        ";
        
        $stmt = sqlsrv_query($conn, $deleteDuplicatesQuery);
        if ($stmt === false) {
            error_log("Warning: Failed to delete duplicates: " . print_r(sqlsrv_errors(), true));
        } else {
            $deletedRows = sqlsrv_rows_affected($stmt);
            $response['debug']['deleted_duplicates'] = $deletedRows;
            sqlsrv_free_stmt($stmt);
        }
        
        // Insert semua data dari temp table
        $insertFinalQuery = "
            INSERT INTO T_ORDER 
            (SUPPLIER_CODE, SUPPLIER_NAME, PLANT, PART_NO, PART_NAME, CLASS, 
             LOT_SIZE, DELV_DATE, CYCLE, ETD, ETA, ORD_QTY, UM, BOX_NO)
            SELECT 
                t.SUPPLIER_CODE, t.SUPPLIER_NAME, t.PLANT, t.PART_NO, t.PART_NAME, t.CLASS, 
                t.LOT_SIZE, t.DELV_DATE, t.CYCLE, t.ETD, t.ETA, t.ORD_QTY, t.UM, t.BOX_NO
            FROM T_ORDER_TMP t
        ";
        
        $stmt = sqlsrv_query($conn, $insertFinalQuery);
        if ($stmt === false) {
            $error = print_r(sqlsrv_errors(), true);
            error_log("Failed to insert data to main table: " . $error);
            throw new Exception("Failed to insert data to main table: " . $error);
        }
        
        $rowsAffected = sqlsrv_rows_affected($stmt);
        sqlsrv_free_stmt($stmt);
        
        // Clear temp table
        sqlsrv_query($conn, $truncate_sql);
        
        $response['message'] = "✅ Successfully uploaded order data ($rowsAffected new records)";
        $successCount = $rowsAffected;
        $response['debug']['temp_rows'] = $batchCount;
        $response['debug']['final_rows'] = $rowsAffected;
    }
    
    // ============ UPLOAD PART ============
    else if ($type == "upload_part") {
        // Tentukan start row (biasanya dari row 3)
        $startRow = 3;
        
        // Cari header
        for ($checkRow = 1; $checkRow <= 5; $checkRow++) {
            $cellC = $worksheet->getCell("C" . $checkRow)->getValue();
            if (stripos($cellC, "PART_NO") !== false || stripos($cellC, "PART NO") !== false) {
                $startRow = $checkRow + 1;
                break;
            }
        }
        
        $response['debug']['part_start_row'] = $startRow;
        
        // Clear table dulu
        $clear_sql = "DELETE FROM M_PART_NO";
        sqlsrv_query($conn, $clear_sql);
        
        $insertQuery = "INSERT INTO M_PART_NO (PART_NO, PART_NAME, LOT, LOCATION, SUPPLIER_CODE, SUPPLIER_NAME, PIC_ORDER, DOCK) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        for ($row = $startRow; $row <= $highestRow; $row++) {
            $cellC = $worksheet->getCell("C" . $row)->getValue();
            if (empty(trim($cellC))) {
                continue;
            }
            
            $PART_NO = str_replace(" ", "", trim($cellC));
            $PART_NAME = trim($worksheet->getCell("D" . $row)->getValue() ?? '');
            $LOT = (int)($worksheet->getCell("E" . $row)->getValue() ?? 0);
            $LOCATION = trim($worksheet->getCell("F" . $row)->getValue() ?? '');
            $SUPPLIER_CODE = trim($worksheet->getCell("G" . $row)->getValue() ?? '');
            $SUPPLIER_NAME = trim($worksheet->getCell("H" . $row)->getValue() ?? '');
            $PIC_ORDER = trim($worksheet->getCell("I" . $row)->getValue() ?? '');
            $DOCK = trim($worksheet->getCell("J" . $row)->getValue() ?? '');
            
            // Validasi minimal
            if (empty($PART_NO)) {
                continue;
            }
            
            $params = [$PART_NO, $PART_NAME, $LOT, $LOCATION, $SUPPLIER_CODE, 
                      $SUPPLIER_NAME, $PIC_ORDER, $DOCK];
            
            $stmt = sqlsrv_prepare($conn, $insertQuery, $params);
            if ($stmt && sqlsrv_execute($stmt)) {
                $successCount++;
            } else {
                error_log("Failed to insert part data at row $row: " . print_r(sqlsrv_errors(), true));
            }
            sqlsrv_free_stmt($stmt);
        }
        
        $response['message'] = "✅ Successfully uploaded $successCount part records";
    }
    
    // ============ COMMIT TRANSACTION ============
    if (!sqlsrv_commit($conn)) {
        throw new Exception("Failed to commit transaction");
    }
    
    $response['success'] = true;
    $response['count'] = $successCount;
    $response['processing_time'] = round(microtime(true) - $startTime, 2);
    
} catch (Exception $e) {
    // ============ ROLLBACK ON ERROR ============
    if (isset($conn)) {
        sqlsrv_rollback($conn);
    }
    
    $response['success'] = false;
    $response['message'] = "❌ Upload failed: " . $e->getMessage();
    $response['error'] = [
        'type' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
    
    error_log("UPLOAD ERROR [" . date('Y-m-d H:i:s') . "]: " . $e->getMessage());
}

// Tutup koneksi
Database::close();

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
?>