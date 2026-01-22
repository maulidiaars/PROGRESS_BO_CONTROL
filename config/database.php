<?php
// config/database.php - OPTIMIZED VERSION WITH RETRY LOGIC
error_reporting(0);
ini_set('display_errors', 0);

class Database {
    private static $connection = null;
    private static $lastError = null;
    private static $retryCount = 0;
    private static $maxRetries = 3;
    
    public static function getConnection() {
        // Jika sudah ada connection yang valid, return
        if (self::$connection !== null) {
            // Test if connection is still alive
            if (self::testConnection()) {
                return self::$connection;
            } else {
                // Connection is dead, close it
                self::close();
            }
        }
        
        // Reset retry count for new attempt
        self::$retryCount = 0;
        
        // Try to connect with retry logic
        while (self::$retryCount < self::$maxRetries) {
            try {
                $serverName = "localhost\SQLEXPRESS";
                $connectionInfo = array(
                    "Database" => "DB_MATCO_DEV",
                    "UID" => "sa", 
                    "PWD" => "Denso@123",
                    "ConnectionPooling" => 1,
                    "ConnectRetryCount" => 2,
                    "ConnectRetryInterval" => 2,
                    "LoginTimeout" => 10,  // Reduced from 15
                    "Encrypt" => 0,
                    "TrustServerCertificate" => 1,
                    "CharacterSet" => "UTF-8"
                );
                
                self::$connection = sqlsrv_connect($serverName, $connectionInfo);
                
                if (self::$connection !== false) {
                    self::$lastError = null;
                    return self::$connection;
                } else {
                    self::$lastError = sqlsrv_errors();
                    self::$retryCount++;
                    
                    // Wait before retrying (exponential backoff)
                    $waitTime = pow(2, self::$retryCount) * 100000; // microseconds
                    usleep($waitTime);
                }
            } catch (Exception $e) {
                self::$lastError = $e->getMessage();
                self::$retryCount++;
                usleep(200000); // 0.2 seconds
            }
        }
        
        // If we get here, all retries failed
        error_log("Database connection failed after " . self::$maxRetries . " attempts");
        return false;
    }
    
    private static function testConnection() {
        if (self::$connection === null) {
            return false;
        }
        
        // Simple query to test connection
        $testQuery = "SELECT 1 as test";
        $stmt = @sqlsrv_query(self::$connection, $testQuery);
        
        if ($stmt === false) {
            return false;
        }
        
        sqlsrv_free_stmt($stmt);
        return true;
    }
    
    public static function getLastError() {
        return self::$lastError;
    }
    
    public static function isConnected() {
        return (self::$connection !== false && self::testConnection());
    }
    
    public static function close() {
        if (self::$connection !== null) {
            @sqlsrv_close(self::$connection);
            self::$connection = null;
        }
    }
    
    // Cache methods remain the same
    public static function cacheGet($key, $ttl = 60) {
        $file = __DIR__ . '/../cache/' . md5($key) . '.cache';
        if (file_exists($file) && time() - filemtime($file) < $ttl) {
            return unserialize(file_get_contents($file));
        }
        return null;
    }
    
    public static function cacheSet($key, $data) {
        $dir = __DIR__ . '/../cache/';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        
        $file = $dir . md5($key) . '.cache';
        @file_put_contents($file, serialize($data));
    }
}

// SIMPLE CONNECTION (BACKWARD COMPATIBLE)
global $conn;
$conn = Database::getConnection();

// Jika connection gagal, coba sekali lagi setelah delay
if ($conn === false) {
    sleep(1);
    $conn = Database::getConnection();
}
?>