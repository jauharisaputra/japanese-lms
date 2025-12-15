<?php
$pdo = new PDO(
    "mysql:host=127.0.0.1;dbname=japanese_lms;charset=utf8mb4",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$stmt = $pdo->query("SELECT id, title, level, lesson_id FROM quizzes ORDER BY id LIMIT 10");
foreach ($stmt as $row) {
    echo $row["id"] . " | " . $row["title"] .
         " | level=" . $row["level"] .
         " | lesson_id=" . ($row["lesson_id"] ?? "NULL") . "\n";
}
?>
