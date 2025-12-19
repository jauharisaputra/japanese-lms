<?php
$pdo = new PDO(
    "mysql:host=127.0.0.1;dbname=japanese_lms;charset=utf8mb4",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$userId = 3; // ganti dengan id siswa yang dipakai uji

$pdo->prepare("DELETE FROM lesson_progress WHERE user_id = ?")->execute([$userId]);

echo "Progress user {$userId} dikosongkan\n";
?>
