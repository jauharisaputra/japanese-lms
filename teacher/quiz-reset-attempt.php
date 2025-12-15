<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['teacher','admin']);

global $pdo;

$attempt_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($attempt_id <= 0) {
    die('ID attempt tidak valid.');
}

// ambil info attempt untuk redirect kembali ke kuis yg benar
$stmt = $pdo->prepare('SELECT quiz_id FROM quiz_attempts WHERE id = ?');
$stmt->execute([$attempt_id]);
$attempt = $stmt->fetch();

if ($attempt) {
    $quiz_id = (int)$attempt['quiz_id'];

    $del = $pdo->prepare('DELETE FROM quiz_attempts WHERE id = ?');
    $del->execute([$attempt_id]);

    redirect('teacher/quiz-results.php?quiz_id='.$quiz_id);
} else {
    die('Attempt tidak ditemukan.');
}
?>
