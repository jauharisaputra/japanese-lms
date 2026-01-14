<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['teacher','admin']);
$pdo = getPDO();

// Ambil filter dari URL
$level = $_GET['level'] ?? '';
$choukai_id = (int)($_GET['choukai_id'] ?? 0);

// Ambil semua choukai sesuai filter
$sqlChoukai = "SELECT id, title, chapter_start, chapter_end FROM choukai WHERE 1=1";
$paramsChoukai = [];
if ($level) {
    $sqlChoukai .= " AND level=?";
    $paramsChoukai[] = $level;
}
if ($choukai_id) {
    $sqlChoukai .= " AND id=?";
    $paramsChoukai[] = $choukai_id;
}
$stmt = $pdo->prepare($sqlChoukai);
$stmt->execute($paramsChoukai);
$choukaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua soal per choukai
$questionsMap = [];
foreach ($choukaiList as $c) {
    $stmt = $pdo->prepare("SELECT question_no FROM choukai_questions WHERE choukai_id=? ORDER BY question_no ASC");
    $stmt->execute([$c['id']]);
    $questionsMap[$c['id']] = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Ambil hasil siswa sesuai filter
$sql = "
    SELECT r.*, u.full_name AS student_name, c.title AS choukai_title
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

// Header CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=choukai_results_detailed.csv');

$out = fopen('php://output', 'w');

// Buat header
$header = ['Siswa','Choukai','Score','Total Soal','Tanggal Submit'];
// Tambahkan kolom jawaban per nomor (dynamic)
$maxQuestions = 0;
foreach ($rows as $r) {
    $answers = json_decode($r['answers_json'], true) ?? [];
    $count = count($answers);
    if ($count > $maxQuestions) $maxQuestions = $count;
}
for ($i=1; $i<=$maxQuestions; $i++) {
    $header[] = "No {$i}";
}
fputcsv($out, $header);

// Loop data siswa
foreach ($rows as $r) {
    $line = [
        $r['student_name'],
        $r['choukai_title'],
        $r['score'],
        $r['total_questions'],
        $r['submitted_at']
    ];

    $answers = json_decode($r['answers_json'], true) ?? [];
    // Tambahkan jawaban per nomor
    for ($i=1; $i<=$maxQuestions; $i++) {
        $line[] = $answers[$i] ?? '';
    }

    fputcsv($out, $line);
}

fclose($out);
exit;