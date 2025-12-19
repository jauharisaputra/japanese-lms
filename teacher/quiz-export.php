<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['teacher','admin']);

$pdo = getPDO();

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
if ($quiz_id <= 0) {
    die('quiz_id tidak valid.');
}

// ambil info kuis untuk nama file
$stmt = $pdo->prepare('SELECT title FROM quizzes WHERE id = ?');
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();
if (!$quiz) {
    die('Kuis tidak ditemukan.');
}

$filename = 'hasil_kuis_' . $quiz_id . '_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');

$output = fopen('php://output', 'w');

// header kolom
fputcsv($output, ['Attempt ID','User ID','Nama','Username','Level','Nilai','Status','Tanggal/Waktu']);

$sql = "
    SELECT qa.id,
           qa.user_id,
           u.full_name,
           u.username,
           u.level,
           qa.score,
           qa.is_passed,
           qa.completed_at
    FROM quiz_attempts qa
    JOIN users u ON qa.user_id = u.id
    WHERE qa.quiz_id = :quiz_id
    ORDER BY qa.completed_at DESC, qa.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['quiz_id' => $quiz_id]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $status = $row['is_passed'] ? 'Lulus' : 'Remedial';
    fputcsv($output, [
        $row['id'],
        $row['user_id'],
        $row['full_name'],
        $row['username'],
        $row['level'],
        $row['score'],
        $status,
        $row['completed_at'],
    ]);
}

fclose($output);
exit;
?>
