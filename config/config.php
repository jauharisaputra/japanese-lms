<?php
if (php_sapi_name() !== 'cli') {
    session_start();
}


define("BASE_URL", "/japanese-lms/");

// sesuaikan kalau Anda memakai kredensial lain
define("DB_HOST", "localhost");
define("DB_NAME", "nihongo_daichi_online");
define("DB_USER", "root");
define("DB_PASS", "");

function getPDO() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die("DB connection error: " . $e->getMessage());
        }
    }

    return $pdo;
}
