<?php
// Koneksi manual ke database LMS
$host = '127.0.0.1';
$db   = 'japanese_lms';
$user = 'root';
$pass = '';
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$pdo = new PDO($dsn, $user, $pass, $options);

// pilih kuis mana yang mau ditandai skill-nya
$stmt = $pdo->query("SELECT id, questions FROM quizzes");
$quizzes = $stmt->fetchAll();

foreach ($quizzes as $quiz) {
    $id   = (int)$quiz['id'];
    $json = $quiz['questions'];

    if (!$json) {
        continue;
    }

    $questions = json_decode($json, true);
    if (!is_array($questions)) {
        continue;
    }

    foreach ($questions as $i => $q) {
        if (!isset($q['skill'])) {
            $questions[$i]['skill'] = 'grammar';
        }
    }

    $newJson = json_encode($questions, JSON_UNESCAPED_UNICODE);

    $upd = $pdo->prepare("UPDATE quizzes SET questions = ? WHERE id = ?");
    $upd->execute([$newJson, $id]);
}

echo "Selesai menambah field skill default=grammar.\n";
?>
