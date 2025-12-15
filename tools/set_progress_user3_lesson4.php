<?php
$pdo = new PDO(
    "mysql:host=127.0.0.1;dbname=japanese_lms;charset=utf8mb4",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$userId   = 3; // ganti jika id siswa lain
$lessonId = 4; // lesson hiragana

$stmt = $pdo->prepare("
    INSERT INTO lesson_progress (user_id, lesson_id, status)
    VALUES (?, ?, 'completed')
    ON DUPLICATE KEY UPDATE status = 'completed'
");
$stmt->execute([$userId, $lessonId]);

echo "User {$userId} lesson {$lessonId} completed\n";
?>
