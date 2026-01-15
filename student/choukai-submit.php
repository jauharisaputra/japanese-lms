<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$user = currentUser();
$pdo = getPDO();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Akses tidak valid.");
}

$choukai_id = (int)($_POST["choukai_id"] ?? 0);
$answers = trim($_POST["answer"] ?? "");

if (!$choukai_id || $answers === "") {
    die("Data tidak lengkap.");
}

/* Cek apakah sudah pernah submit */
$stmt = $pdo->prepare("
    SELECT id FROM choukai_answers
    WHERE choukai_id = ? AND user_id = ?
");
$stmt->execute([$choukai_id, $user["id"]]);

if ($stmt->fetch()) {
    /* Update jawaban */
    $stmt = $pdo->prepare("
        UPDATE choukai_answers
        SET answers = ?, created_at = NOW()
        WHERE choukai_id = ? AND user_id = ?
    ");
    $stmt->execute([$answers, $choukai_id, $user["id"]]);
} else {
    /* Insert jawaban baru */
    $stmt = $pdo->prepare("
        INSERT INTO choukai_answers (user_id, choukai_id, answers)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user["id"], $choukai_id, $answers]);
}

header("Location: " . BASE_URL . "student/choukai-result.php?id=" . $choukai_id);
exit;