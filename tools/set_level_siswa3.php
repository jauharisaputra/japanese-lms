<?php
$pdo = new PDO(
    "mysql:host=127.0.0.1;dbname=japanese_lms;charset=utf8mb4",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// update level untuk username siswa3
$stmt = $pdo->prepare("UPDATE users SET level = 'N5' WHERE username = 'siswa3'");
$stmt->execute();

echo "Level siswa3 diset ke N5\n";
?>
