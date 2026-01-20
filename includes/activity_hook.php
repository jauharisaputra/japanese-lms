<?php
function activity(string $type, $ref_id = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user']['id'])) {
        return;
    }

    if ($type === '__INVALID__' || trim($type) === '') {
        return;
    }

    require_once __DIR__ . '/../config/config.php';
    $pdo = getPDO();

    $stmt = $pdo->prepare("
        INSERT INTO student_activity_logs
        (user_id, activity_type, reference_id)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        (int)$_SESSION['user']['id'],
        $type,
        $ref_id
    ]);
}