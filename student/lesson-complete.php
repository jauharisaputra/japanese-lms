<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['student']);

global $pdo;
$user    = currentUser();
$user_id = $user['id'];

$lesson_id = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : 0;
if ($lesson_id <= 0) {
    die('lesson_id tidak valid');
}

$stmt = $pdo->prepare('
    INSERT INTO lesson_progress (user_id, lesson_id, status)
    VALUES (?, ?, "completed")
    ON DUPLICATE KEY UPDATE status = "completed"
');
$stmt->execute([$user_id, $lesson_id]);

$redirect = $_GET['redirect'] ?? '../student/lessons.php';
redirect($redirect);
?>
