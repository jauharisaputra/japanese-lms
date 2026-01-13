<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['student']);
$user = currentUser();
$pdo = getPDO();

// Ambil data POST
$choukai_id = (int)($_POST['choukai_id'] ?? 0);
$answers = $_POST['answers'] ?? [];

// Validasi dasar
if ($choukai_id <= 0) {
    die("choukai tidak valid.");
}

// Ambil semua soal dari database untuk choukai ini
$stmt = $pdo->prepare("SELECT id, correct FROM choukai_questions WHERE choukai_id = ?");
$stmt->execute([$choukai_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$questions) {
    die("Soal untuk choukai ini belum tersedia.");
}

// Hitung skor
$score = 0;
foreach ($questions as $q) {
    $qid = $q['id'];
    $correct = (int)$q['correct'];
    if (isset($answers[$qid]) && (int)$answers[$qid] === $correct) {
        $score++;
    }
}

// Total soal
$totalQuestions = count($questions);

// Simpan hasil ke tabel choukai_results
$stmt = $pdo->prepare("
    INSERT INTO choukai_results (choukai_id, user_id, score, total_questions, answers, submitted_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");
$stmt->execute([
    $choukai_id,
    $user['id'],
    $score,
    $totalQuestions,
    json_encode($answers, JSON_UNESCAPED_UNICODE)
]);

// Redirect ke halaman hasil
// Gunakan relative path agar tidak duplikasi folder
header("Location: choukai-result.php?id={$choukai_id}");
exit;
