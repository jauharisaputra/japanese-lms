<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$pdo = getPDO();

$choukai_id = (int)($_POST["choukai_id"] ?? 0);
$user_id    = (int)($_POST["user_id"] ?? 0);
$score      = (int)($_POST["score"] ?? 0);
$note       = trim($_POST["note"] ?? "");

if (!$choukai_id || !$user_id) {
    die("Data tidak valid.");
}

/* Cek apakah sudah dinilai */
$stmt = $pdo->prepare("
    SELECT id FROM choukai_scores
    WHERE choukai_id = ? AND user_id = ?
");
$stmt->execute([$choukai_id, $user_id]);

if ($stmt->fetch()) {
    $stmt = $pdo->prepare("
        UPDATE choukai_scores
        SET score = ?, note = ?, graded_at = NOW()
        WHERE choukai_id = ? AND user_id = ?
    ");
    $stmt->execute([$score, $note, $choukai_id, $user_id]);
} else {
    $stmt = $pdo->prepare("
        INSERT INTO choukai_scores (choukai_id, user_id, score, note)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$choukai_id, $user_id, $score, $note]);
}

header("Location: " . BASE_URL . "teacher/choukai-review.php?id=" . $choukai_id);
exit;