<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['teacher','admin']);
$pdo = getPDO();

$level = $_GET['level'] ?? '';
$dokkai_id = (int)($_GET['dokkai_id'] ?? 0);

$sql = "SELECT
            u.full_name AS student_name,
            d.title AS dokkai_title,
            d.level,
            r.score,
            r.total_questions,
            r.submitted_at
        FROM dokkai_results r
        JOIN users u ON r.user_id = u.id
        JOIN dokkai d ON r.dokkai_id = d.id
        WHERE 1=1";


$params = [];
if ($level) { $sql .= " AND d.level = ?"; $params[] = $level; }
if ($dokkai_id) { $sql .= " AND d.id = ?"; $params[] = $dokkai_id; }
$sql .= " ORDER BY r.submitted_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=dokkai_results.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Siswa', 'Dokkai', 'Level', 'Score', 'Total Soal', 'Tanggal Submit']);

foreach ($results as $r) {
    fputcsv($output, [
        $r['student_name'],
        $r['dokkai_title'],
        $r['level'],
        $r['score'],
        $r['total_questions'],
        $r['submitted_at']
    ]);
}
fclose($output);
exit;