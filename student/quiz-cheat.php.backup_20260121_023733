<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid method"]);
    exit;
}

// sementara hanya mencatat di log; nanti bisa dihubungkan dengan quiz_attempts
$user = currentUser();
$uid  = $user["id"] ?? 0;

// TODO: Sensei bisa tambahkan logika di sini:
// - kurangi 10 poin dari attempt yang sedang berjalan
// - tambah counter kecurangan di tabel quiz_sessions/tab_switches, dll.

echo json_encode(["status" => "ok", "message" => "cheat_detected", "user_id" => $uid]);
exit;
