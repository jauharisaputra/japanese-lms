<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["admin","teacher"]);

$pdo = getPDO();
$stmt = $pdo->query("
    SELECT 
        qa.id,
        qa.user_id,
        u.full_name,
        q.title,
        qa.score
    FROM quiz_attempts AS qa
    JOIN (
        SELECT quiz_id, MAX(id) AS last_id
        FROM quiz_attempts
        GROUP BY quiz_id
    ) AS t ON qa.id = t.last_id
    JOIN users   AS u ON qa.user_id = u.id
    JOIN quizzes AS q ON qa.quiz_id = q.id
    WHERE q.title IN ('Kuis Hiragana Dasar','Kuis Katakana Dasar','Kuis Kanji N5','Kuis Kanji N4')
    ORDER BY qa.id DESC
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "rekap_kuis_huruf_kanji_" . date("Ymd_His") . ".csv";
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");

$out = fopen("php://output", "w");
fputcsv($out, ["ID Attempt", "ID Siswa", "Nama Siswa", "Jenis Kuis", "Skor"]);

foreach ($rows as $r) {
    fputcsv($out, [
        $r["id"],
        $r["user_id"],
        $r["full_name"],
        $r["title"],
        $r["score"],
    ]);
}

fclose($out);
exit;
?>
