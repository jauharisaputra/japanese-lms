<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$pdo  = getPDO();
$user = currentUser();

// Ringkasan nilai kuis per level
$stmt = $pdo->prepare("
  SELECT q.level,
         AVG(qa.score) AS avg_quiz
  FROM quiz_attempts qa
  JOIN quizzes q ON qa.quiz_id = q.id
  WHERE qa.user_id = ?
  GROUP BY q.level
");
$stmt->execute([$user["id"]]);
$quiz_summary = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Ringkasan nilai tugas per level
$stmt = $pdo->prepare("
  SELECT a.level,
         AVG(CASE WHEN a.type = 'daily'  THEN s.score END) AS avg_daily,
         AVG(CASE WHEN a.type = 'weekly' THEN s.score END) AS avg_weekly,
         AVG(CASE WHEN a.type = 'review' THEN s.score END) AS avg_review,
         AVG(s.kaiwa_score) AS avg_kaiwa
  FROM assignment_submissions s
  JOIN assignments a ON s.assignment_id = a.id
  WHERE s.user_id = ?
  GROUP BY a.level
");
$stmt->execute([$user["id"]]);
$assign_summary = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $assign_summary[$row["level"]] = $row;
}

// Detail nilai kuis
$stmt = $pdo->prepare("
  SELECT qa.id,
         q.title AS quiz_title,
         q.level,
         qa.score,
         qa.completed_at
  FROM quiz_attempts qa
  JOIN quizzes q ON qa.quiz_id = q.id
  WHERE qa.user_id = ?
  ORDER BY q.level, qa.id DESC
");
$stmt->execute([$user["id"]]);
$quiz_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Detail nilai tugas
$stmt = $pdo->prepare("
  SELECT s.id,
         a.title AS assignment_title,
         a.type,
         a.level,
         a.chapter_start,
         a.chapter_end,
         s.score,
         s.kaiwa_score,
         s.submitted_at
  FROM assignment_submissions s
  JOIN assignments a ON s.assignment_id = a.id
  WHERE s.user_id = ?
  ORDER BY a.level, a.chapter_start, s.submitted_at DESC
");
$stmt->execute([$user["id"]]);
$task_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output CSV
$filename = "riwayat_nilai_" . $user["id"] . "_" . date("Ymd_His") . ".csv";
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");

$out = fopen("php://output", "w");
fputs($out, "\xEF\xBB\xBF");

// 1) Ringkasan per level
fputcsv($out, ["Ringkasan nilai per level"]);
fputcsv($out, ["Level","Rata-rata kuis","Rata-rata tugas harian","Rata-rata tugas mingguan","Rata-rata fukushuu","Rata-rata kaiwa"]);

$levels = array_unique(array_merge(array_keys($quiz_summary), array_keys($assign_summary)));
sort($levels);
foreach ($levels as $lv) {
    $q  = $quiz_summary[$lv] ?? null;
    $as = $assign_summary[$lv] ?? null;
    fputcsv($out, [
        $lv,
        $q !== null ? round($q, 1) : "",
        $as && $as["avg_daily"]  !== null ? round($as["avg_daily"], 1)  : "",
        $as && $as["avg_weekly"] !== null ? round($as["avg_weekly"], 1) : "",
        $as && $as["avg_review"] !== null ? round($as["avg_review"], 1) : "",
        $as && $as["avg_kaiwa"]  !== null ? round($as["avg_kaiwa"], 1)  : "",
    ]);
}
fputcsv($out, []);

// 2) Detail nilai kuis
fputcsv($out, ["Detail nilai kuis"]);
fputcsv($out, ["ID attempt","Judul kuis","Level","Skor","Tanggal"]);
foreach ($quiz_rows as $r) {
    fputcsv($out, [
        $r["id"],
        $r["quiz_title"],
        $r["level"],
        $r["score"],
        $r["completed_at"] ?? "",
    ]);
}
fputcsv($out, []);

// 3) Detail nilai tugas & kaiwa
fputcsv($out, ["Detail nilai tugas & kaiwa"]);
fputcsv($out, ["ID","Tugas","Jenis","Level","Bab","Nilai","Nilai kaiwa","Tanggal"]);
foreach ($task_rows as $r) {
    $bab = $r["chapter_start"] . "〜" . $r["chapter_end"];
    fputcsv($out, [
        $r["id"],
        $r["assignment_title"],
        $r["type"],
        $r["level"],
        $bab,
        $r["score"] !== null ? $r["score"] : "",
        $r["kaiwa_score"] !== null ? $r["kaiwa_score"] : "",
        $r["submitted_at"],
    ]);
}

fclose($out);
exit;
?>


