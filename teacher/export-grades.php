<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$pdo = getPDO();

// optional filter level, kelas
$level = $_GET["level"] ?? null;
$class_id = isset($_GET["class_id"]) ? (int)$_GET["class_id"] : null;

// ambil semua siswa
$sql = "
  SELECT u.id AS user_id,
         u.username,
         u.full_name,
         u.level,
         c.name AS class_name
  FROM users u
  LEFT JOIN classes c ON u.class_id = c.id
  WHERE u.role = 'student'
";
$params = [];
if ($level) {
    $sql .= " AND u.level = ?";
    $params[] = $level;
}
if ($class_id) {
    $sql .= " AND u.class_id = ?";
    $params[] = $class_id;
}
$sql .= " ORDER BY u.level, c.name, u.full_name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// siapkan array data untuk CSV
$data = [];
foreach ($students as $s) {
    $uid = $s["user_id"];

    // rata2 kuis level tersebut
    $qs = $pdo->prepare("
      SELECT AVG(qa.score)
      FROM quiz_attempts qa
      JOIN quizzes q ON qa.quiz_id = q.id
      WHERE qa.user_id = ? AND q.level = ?
    ");
    $qs->execute([$uid, $s["level"]]);
    $avg_quiz = (float)($qs->fetchColumn() ?: 0);

    // ringkasan tugas per type
    $as = $pdo->prepare("
      SELECT
        AVG(CASE WHEN a.type='daily'  THEN s.score END) AS avg_daily,
        AVG(CASE WHEN a.type='weekly' THEN s.score END) AS avg_weekly,
        AVG(CASE WHEN a.type='review' THEN s.score END) AS avg_review,
        AVG(s.kaiwa_score) AS avg_kaiwa
      FROM assignment_submissions s
      JOIN assignments a ON s.assignment_id = a.id
      WHERE s.user_id = ? AND a.level = ?
    ");
    $as->execute([$uid, $s["level"]]);
    $row = $as->fetch(PDO::FETCH_ASSOC) ?: [];
    $avg_daily  = (float)($row["avg_daily"]  ?? 0);
    $avg_weekly = (float)($row["avg_weekly"] ?? 0);
    $avg_review = (float)($row["avg_review"] ?? 0);
    $avg_kaiwa  = (float)($row["avg_kaiwa"]  ?? 0);

    // bobot nilai akhir (bisa diubah)
    $final = 0.4 * $avg_quiz
           + 0.2 * $avg_daily
           + 0.2 * $avg_weekly
           + 0.2 * $avg_review;

    $data[] = [
        "user_id"      => $uid,
        "username"     => $s["username"],
        "full_name"    => $s["full_name"],
        "level"        => $s["level"],
        "class_name"   => $s["class_name"],
        "avg_quiz"     => round($avg_quiz, 2),
        "avg_daily"    => round($avg_daily, 2),
        "avg_weekly"   => round($avg_weekly, 2),
        "avg_review"   => round($avg_review, 2),
        "avg_kaiwa"    => round($avg_kaiwa, 2),
        "final_score"  => round($final, 2)
    ];
}

// header CSV untuk download
$filename = "nihongo_grades_" . date("Ymd_His") . ".csv";
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename={$filename}");

$out = fopen("php://output", "w");
// header kolom
fputcsv($out, array_keys($data[0] ?? [
  "user_id","username","full_name","level","class_name",
  "avg_quiz","avg_daily","avg_weekly","avg_review","avg_kaiwa","final_score"
]));

foreach ($data as $row) {
    fputcsv($out, $row);
}
fclose($out);
exit;
