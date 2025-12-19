<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$pdo = getPDO();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("teacher/assignments.php");
}

$sub_id = (int)($_POST["submission_id"] ?? 0);
$score  = $_POST["score"] !== "" ? (float)$_POST["score"] : null;
$kaiwa  = $_POST["kaiwa_score"] !== "" ? (float)$_POST["kaiwa_score"] : null;
$comment = trim($_POST["comment"] ?? "");

$stmt = $pdo->prepare("
  UPDATE assignment_submissions
     SET score = ?, kaiwa_score = ?, comment = ?, graded_at = NOW()
   WHERE id = ?
");
$stmt->execute([$score, $kaiwa, $comment ?: null, $sub_id]);

redirect($_SERVER["HTTP_REFERER"] ?? "assignments.php");
