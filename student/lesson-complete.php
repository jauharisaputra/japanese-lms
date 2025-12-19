<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);

if (!isset($_GET["lesson_id"])) {
    header("Location: lessons.php");
    exit;
}

$lesson_id = (int)$_GET["lesson_id"];
$redirect  = $_GET["redirect"] ?? "lesson-view.php?id={$lesson_id}";

$pdo = getPDO();
$user = currentUser();
$user_id = $user["id"];

// upsert ke lesson_progress
$stmt = $pdo->prepare("
    INSERT INTO lesson_progress (user_id, lesson_id, status)
    VALUES (?, ?, 'completed')
    ON DUPLICATE KEY UPDATE status = 'completed', updated_at = CURRENT_TIMESTAMP
");
$stmt->execute([$user_id, $lesson_id]);

header("Location: " . $redirect);
exit;
