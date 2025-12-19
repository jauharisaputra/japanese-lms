<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$pdo = getPDO();

$user_id = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : 0;
$level   = $_GET["level"] ?? "N5";

if ($user_id <= 0) {
    die("User tidak valid.");
}

// rata2 kuis bab di level tsb
$stmt = $pdo->prepare("
  SELECT AVG(score) AS avg_score
  FROM quiz_attempts qa
  JOIN quizzes q ON qa.quiz_id = q.id
  WHERE qa.user_id = ? AND q.level = ?
");
$stmt->execute([$user_id, $level]);
$quiz_score = (float)($stmt->fetchColumn() ?: 0);

// tugas harian
$stmt = $pdo->prepare("
  SELECT AVG(s.score) FROM assignment_submissions s
  JOIN assignments a ON s.assignment_id = a.id
  WHERE s.user_id = ? AND a.level = ? AND a.type = 'daily'
");
$stmt->execute([$user_id, $level]);
$daily_score = (float)($stmt->fetchColumn() ?: 0);

// tugas mingguan
$stmt = $pdo->prepare("
  SELECT AVG(s.score) FROM assignment_submissions s
  JOIN assignments a ON s.assignment_id = a.id
  WHERE s.user_id = ? AND a.level = ? AND a.type = 'weekly'
");
$stmt->execute([$user_id, $level]);
$weekly_score = (float)($stmt->fetchColumn() ?: 0);

// fukushuu (review)
$stmt = $pdo->prepare("
  SELECT AVG(s.score) FROM assignment_submissions s
  JOIN assignments a ON s.assignment_id = a.id
  WHERE s.user_id = ? AND a.level = ? AND a.type = 'review'
");
$stmt->execute([$user_id, $level]);
$review_score = (float)($stmt->fetchColumn() ?: 0);

// kaiwa
$stmt = $pdo->prepare("
  SELECT AVG(s.kaiwa_score) FROM assignment_submissions s
  JOIN assignments a ON s.assignment_id = a.id
  WHERE s.user_id = ? AND a.level = ?
");
$stmt->execute([$user_id, $level]);
$kaiwa_score = (float)($stmt->fetchColumn() ?: 0);

// bobot
$final_score = 0.4 * $quiz_score
             + 0.2 * $daily_score
             + 0.2 * $weekly_score
             + 0.2 * $review_score;

$stmt = $pdo->prepare("
  INSERT INTO final_scores
    (user_id, level, quiz_score, daily_score, weekly_score, review_score, final_score, kaiwa_score)
  VALUES (?,?,?,?,?,?,?,?)
  ON DUPLICATE KEY UPDATE
    quiz_score = VALUES(quiz_score),
    daily_score = VALUES(daily_score),
    weekly_score = VALUES(weekly_score),
    review_score = VALUES(review_score),
    final_score = VALUES(final_score),
    kaiwa_score = VALUES(kaiwa_score)
");
$stmt->execute([
  $user_id, $level,
  $quiz_score, $daily_score, $weekly_score, $review_score,
  $final_score, $kaiwa_score
]);

echo "Rekap nilai selesai untuk user {$user_id}, level {$level}.";
