<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
global $pdo;

$quiz_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($quiz_id <= 0) {
    redirect("teacher/quizzes.php");
}

// ambil level untuk redirect
$stmt = $pdo->prepare("SELECT level FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);
$level = $quiz ? $quiz["level"] : "N5";

// hapus attempt siswa dulu (supaya rapi)
$delAttempts = $pdo->prepare("DELETE FROM quiz_attempts WHERE quiz_id = ?");
$delAttempts->execute([$quiz_id]);

// hapus kuis
$delQuiz = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
$delQuiz->execute([$quiz_id]);

redirect("teacher/quizzes.php?level=" . $level);
