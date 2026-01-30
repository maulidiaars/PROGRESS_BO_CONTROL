<?php
// api/get_live_information.php - VERSI AUTO SCROLL KE ATAS
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/week_logic.php';

$response = [
    'success' => false,
    'informations' => [],
    'count' => 0,
    'week_info' => [],
    'timestamp' => date('H:i:s')
];

if (!$conn) {
    $response['error'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

try {
    // PAKAI FILTER MINGGUAN
    $weekInfo = getCurrentWeekInfo();
    $weekStart = $weekInfo['start_date'];
    $weekEnd = $weekInfo['end_date'];
    
    $response['week_info'] = $weekInfo;
    
    // QUERY UNTUK MINGGU INI SAJA - HANYA OPEN & ON PROGRESS
    $sql = "
        SELECT 
            ti.ID_INFORMATION as id,
            'information' as type,
            -- DATA UTAMA
            ti.PIC_FROM,
            ti.PIC_TO,
            ti.ITEM,
            ti.REQUEST,
            ti.DATE,
            ti.TIME_FROM as time,
            ti.STATUS,
            -- Status text untuk display
            CASE 
                WHEN ti.STATUS = 'Open' THEN 'OPEN'
                WHEN ti.STATUS = 'On Progress' THEN 'ON PROGRESS'
                WHEN ti.STATUS = 'Closed' THEN 'CLOSED'
                ELSE UPPER(ti.STATUS)
            END as status_text,
            -- WARNA BADGE
            CASE 
                WHEN ti.STATUS = 'Open' THEN 'danger'
                WHEN ti.STATUS = 'On Progress' THEN 'warning'
                ELSE 'secondary'
            END as badge_color,
            ti.REMARK
            
        FROM T_INFORMATION ti
        WHERE ti.DATE >= ?  -- MULAI SENIN MINGGU INI
        AND ti.DATE <= ?    -- SAMPAI MINGGU MINGGU INI
        AND ti.STATUS IN ('Open', 'On Progress')  -- HANYA TAMPILKAN OPEN & ON PROGRESS
        AND ti.PIC_FROM IS NOT NULL
        AND ti.PIC_FROM != ''
        ORDER BY 
            ti.DATE DESC, 
            ti.TIME_FROM DESC
    ";
    
    $params = [$weekStart, $weekEnd];
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if (!$stmt) {
        $errors = sqlsrv_errors();
        $response['error'] = 'SQL Error: ' . print_r($errors, true);
        echo json_encode($response);
        exit;
    }
    
    $informations = [];
    
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // FORMAT DATE: DD/MM/YY
        if (!empty($row['DATE']) && is_numeric($row['DATE'])) {
            $dateStr = (string)$row['DATE'];
            if (strlen($dateStr) === 8) {
                $row['date_formatted'] = 
                    substr($dateStr, 6, 2) . '/' . 
                    substr($dateStr, 4, 2) . '/' . 
                    substr($dateStr, 2, 2);
            }
        }
        
        // FORMAT TIME: HH:MM
        if (!empty($row['time']) && strlen($row['time']) === 5) {
            $row['time_formatted'] = $row['time'];
        } else {
            $row['time_formatted'] = $row['time'] ?? '00:00';
        }
        
        // SIMPLE TIME DISPLAY (tanpa time_ago yang ribet)
        $row['time_display'] = $row['time_formatted'];
        
        // FORMAT PENGIRIM → PENERIMA
        $row['from_to_display'] = $row['PIC_FROM'] . ' → ' . $row['PIC_TO'];
        
        // ISI INFORMASI (Item atau Request)
        if (!empty($row['REQUEST'])) {
            $row['content_display'] = $row['REQUEST'];
        } elseif (!empty($row['ITEM'])) {
            $row['content_display'] = $row['ITEM'];
        } else {
            $row['content_display'] = 'Tidak ada isi';
        }
        
        // Potong jika terlalu panjang
        if (strlen($row['content_display']) > 150) {
            $row['content_display'] = substr($row['content_display'], 0, 150) . '...';
        }
        
        $informations[] = $row;
    }
    
    sqlsrv_free_stmt($stmt);
    
    // SUCCESS RESPONSE
    $response['success'] = true;
    $response['informations'] = $informations;
    $response['count'] = count($informations);
    $response['timestamp'] = time();
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    $response['error'] = 'Exception: ' . $e->getMessage();
    echo json_encode($response);
}

if ($conn) {
    sqlsrv_close($conn);
}
?>