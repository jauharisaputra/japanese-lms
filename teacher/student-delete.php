<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
global $pdo;

$student_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($student_id <= 0) {
    redirect("teacher/students.php");
}

// hapus data terkait jika belum pakai ON DELETE CASCADE
$pdo->prepare("DELETE FROM quiz_attempts WHERE user_id = ?")->execute([$student_id]);
$pdo->prepare("DELETE FROM lesson_progress WHERE user_id = ?")->execute([$student_id]);

// hapus akun siswa
$pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'")->execute([$student_id]);

redirect("teacher/students.php");
