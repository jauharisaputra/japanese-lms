<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['teacher','admin']);
$pdo = getPDO();

$level = $_GET['level'] ?? '';
$choukai_id = (int)($_GET['choukai_id'] ?? 0);

$sql = "
    SELECT
        u.full_name AS student_name,
        c.title AS choukai_title,
        c.level,
        r.score,
        r.total_questions,
        r.submitted_at
    FROM choukai_results r
    JOIN users u ON r.user_id = u.id
    JOIN choukai c ON r.choukai_id = c.id
    WHERE 1=1
";
$params = [];

if ($level) { $sql .= " AND c.level=?"; $params[] = $level; }
if ($choukai_id) { $sql .= " AND c.id=?"; $params[] = $choukai_id; }

$sql .= " ORDER BY r.submitted_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// CSV header
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=choukai_results.csv');

$out = fopen('php://output', 'w');
fputcsv($out, ['Siswa','Choukai','Level','Score','Total Soal','Tanggal']);

foreach ($rows as $r) {
    fputcsv($out, [
        $r['student_name'],
        $r['choukai_title'],
        $r['level'],
        $r['score'],
        $r['total_questions'],
        $r['submitted_at']
    ]);
}
fclose($out);
exit;
