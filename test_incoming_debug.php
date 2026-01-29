<?php
require_once 'config/database.php';

echo "<h2>🔍 DEBUG DATA T_UPDATE_BO</h2>";

$date = '20260129';
$part_no = 'JK146667-9240';

// 1. Cek data di T_UPDATE_BO
$sql1 = "SELECT 
    ID_ORDER,
    DATE,
    HOUR,
    PART_NO,
    PART_DESC,
    TRAN_QTY,
    FORMAT(GETDATE(), 'HH:mm:ss') as CHECK_TIME
FROM T_UPDATE_BO 
WHERE DATE = ? AND PART_NO = ?
ORDER BY HOUR, ID_ORDER";

$params = [$date, $part_no];
$stmt = sqlsrv_query($conn, $sql1, $params);

echo "<h3>1. Data di T_UPDATE_BO untuk $part_no tanggal $date</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr style='background:#f0f0f0'>
        <th>ID_ORDER</th>
        <th>DATE</th>
        <th>HOUR</th>
        <th>PART_NO</th>
        <th>PART_DESC</th>
        <th>TRAN_QTY</th>
        <th>Keterangan</th>
      </tr>";

$data = [];
$prev_qty = 0;
while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $hour = $row['HOUR'];
    $qty = $row['TRAN_QTY'];
    $incoming = $qty - $prev_qty;
    
    $data[$hour] = $qty;
    
    echo "<tr>";
    echo "<td>{$row['ID_ORDER']}</td>";
    echo "<td>{$row['DATE']}</td>";
    echo "<td>$hour:00</td>";
    echo "<td>{$row['PART_NO']}</td>";
    echo "<td>{$row['PART_DESC']}</td>";
    echo "<td>$qty</td>";
    echo "<td>";
    if ($prev_qty > 0) {
        echo "Akumulatif: $qty<br>";
        echo "Incoming jam ini: $incoming<br>";
        echo "(Selisih: $qty - $prev_qty)";
    } else {
        echo "Akumulatif: $qty (jam pertama)";
    }
    echo "</td>";
    echo "</tr>";
    
    $prev_qty = $qty;
}

echo "</table>";

// 2. Hitung manual incoming per jam
echo "<h3>2. Perhitungan Manual Incoming Per Jam</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr style='background:#e0f0ff'>
        <th>Jam</th>
        <th>TRAN_QTY (Akumulatif)</th>
        <th>Incoming Per Jam</th>
        <th>Keterangan</th>
      </tr>";

$prev = 0;
for($hour = 7; $hour <= 20; $hour++) {
    $current = isset($data[$hour]) ? $data[$hour] : 0;
    $incoming = $hour == 7 ? $current : ($current - $prev);
    
    echo "<tr>";
    echo "<td>$hour:00</td>";
    echo "<td>$current</td>";
    echo "<td><strong>$incoming</strong></td>";
    echo "<td>";
    if ($hour == 7) {
        echo "Jam pertama";
    } else {
        echo "$current - $prev = $incoming";
    }
    echo "</td>";
    echo "</tr>";
    
    $prev = $current;
}

echo "</table>";

// 3. Test query baru
echo "<h3>3. Test Query Baru (data_day_shift1.php)</h3>";

$sql2 = "
WITH RawData AS (
    SELECT 
        ub.DATE,
        ub.PART_NO,
        ub.PART_DESC,
        ub.HOUR,
        ub.TRAN_QTY,
        ub.ID_ORDER,
        ROW_NUMBER() OVER (
            PARTITION BY ub.DATE, ub.PART_NO, ub.HOUR 
            ORDER BY ub.ID_ORDER DESC
        ) as rn
    FROM T_UPDATE_BO ub
    WHERE ub.HOUR BETWEEN 7 AND 20
    AND ub.DATE = ? AND ub.PART_NO = ?
),
LatestData AS (
    SELECT * FROM RawData WHERE rn = 1
),
HourSeries AS (
    SELECT 7 as HOUR UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION 
    SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION 
    SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION 
    SELECT 19 UNION SELECT 20
),
CombinedData AS (
    SELECT 
        hs.HOUR,
        COALESCE(ld.TRAN_QTY, 0) as TRAN_QTY
    FROM HourSeries hs
    LEFT JOIN LatestData ld ON hs.HOUR = ld.HOUR
),
IncomingCalc AS (
    SELECT 
        HOUR,
        TRAN_QTY,
        CASE 
            WHEN HOUR = 7 THEN TRAN_QTY
            ELSE TRAN_QTY - LAG(TRAN_QTY, 1, 0) OVER (ORDER BY HOUR)
        END as INCOMING_QTY
    FROM CombinedData
)
SELECT * FROM IncomingCalc ORDER BY HOUR";

$stmt2 = sqlsrv_query($conn, $sql2, $params);

echo "<table border='1' cellpadding='5'>";
echo "<tr style='background:#e0ffe0'>
        <th>Jam</th>
        <th>TRAN_QTY (Akumulatif)</th>
        <th>INCOMING_QTY (Hasil Query)</th>
        <th>Status</th>
      </tr>";

while($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
    $hour = $row['HOUR'];
    $qty = $row['TRAN_QTY'];
    $incoming = $row['INCOMING_QTY'];
    
    echo "<tr>";
    echo "<td>$hour:00</td>";
    echo "<td>$qty</td>";
    echo "<td><strong style='color:blue'>$incoming</strong></td>";
    echo "<td>";
    if ($hour == 8 && $incoming == 300) {
        echo "✅ BENAR (Jam 08:00 = 300)";
    } elseif ($hour == 9 && $incoming == 200) {
        echo "✅✅✅ BENAR BANGET! (Jam 09:00 = 200)";
    } else {
        echo "OK";
    }
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h3>🎯 KESIMPULAN:</h3>";
echo "<p>1. Data di T_UPDATE_BO: <strong>Akumulatif</strong> (300, 500, dll)</p>";
echo "<p>2. Query baru harus mengubah ke: <strong>Incoming per jam</strong> (300, 200, dll)</p>";
echo "<p>3. Hasil yang diharapkan di modal D/S:</p>";
echo "<ul>";
echo "<li>Jam 08:00: <strong>300 pcs</strong> ✅</li>";
echo "<li>Jam 09:00: <strong>200 pcs</strong> (500-300) ✅✅✅</li>";
echo "<li>BUKAN 500 pcs ❌</li>";
echo "</ul>";

sqlsrv_close($conn);
?>