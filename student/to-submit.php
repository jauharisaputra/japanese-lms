<?php
ob_start();
session_start();
require_once __DIR__ . "/../config/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

 = ['user_id'];
 = getPDO();

if (['REQUEST_METHOD'] !== 'POST') {
    header("Location: to-exams.php");
    exit;
}

 = (int)(['exam_id'] ?? 0);
if (!) {
    header("Location: to-exams.php?error=no_exam");
    exit;
}

 = ->prepare("SELECT * FROM placement_exams WHERE id = ?");
->execute([]);
 = ->fetch();

if (!) {
    header("Location: to-exams.php?error=no_exam");
    exit;
}

 = ['answer'] ?? [];
 = (int)['max_score'];
 = count();
 = min(, );
 =  > 0 ? round(( / ) * 100) : 0;
 =  >= (int)['pass_score'];

 = ->prepare("
    INSERT INTO placement_attempts (user_id, exam_id, score, percentage, answered_count, total_questions, passed, answers_json, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
->execute([
    , , , , , , , json_encode()
]);

ob_end_clean();
header("Location: scores.php?success=to_submitted&exam_id=&score=");
exit;
?>
