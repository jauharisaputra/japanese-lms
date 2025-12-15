<?php
require_once __DIR__ . "/config/config.php";

// pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// kosongkan semua data session
$_SESSION = [];

// hancurkan session
session_destroy();

// redirect ke halaman login
header("Location: login.php");
exit;
