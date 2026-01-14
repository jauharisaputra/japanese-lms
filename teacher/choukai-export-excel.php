<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['teacher','admin']);
$pdo = getPDO();

// Ambil filter
$level = $_GET['level'] ?? '';
$choukai_id = (int)($_GET['choukai_id'] ?? 0);

// Ambil jawaban siswa
$sql = "SELECT ca.*, u.full_name AS student_name, c.title AS choukai_title, c.level
        FROM choukai_answers ca
        JOIN users u ON ca.user_id = u.id
        JOIN choukai c ON ca.choukai_id = c.id
        WHERE 1=1";

$params = [];
if ($level) {
    $sql .= " AND c.level = ?";
    $params[] = $level;
}
if ($choukai_id) {
    $sql .= " AND c.id = ?";
    $params[] = $choukai_id;
}

$sql .= " ORDER BY ca.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set header CSV untuk Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename=choukai_results_excel.csv');

$output = fopen('php://output', 'w');

// Tentukan header awal (No, Siswa, Level, Choukai, Waktu Submit)
$maxQuestions = 0;

// Hitung jumlah soal maksimum di semua jawaban
foreach ($results as $r) {
    $answers = json_decode($r['answers'], true);
    if (count($answers) > $maxQuestions) $maxQuestions = count($answers);
}

// Header kolom
$header = ['No', 'Siswa', 'Level', 'Choukai', 'Waktu Submit', 'Total Jawaban'];
for ($i=1; $i<=$maxQuestions; $i++) {
    $header[] = "Jawaban Q$i";
}
fputcsv($output, $header);

// Data
$no = 1;
foreach ($results as $r) {
    $answers = json_decode($r['answers'], true);
    $row = [
        $no,
        $r['student_name'],
        $r['level'],
        $r['choukai_title'],
        $r['created_at'],
        count($answers)
    ];

    // Masukkan jawaban tiap soal ke kolom terpisah
    for ($i=1; $i<=$maxQuestions; $i++) {
        if (isset($answers[$i])) {
            $a = $answers[$i];
            $parts = [];
            if (!empty($a['number'])) $parts[] = "Angka:".$a['number'];
            if (!empty($a['letter'])) $parts[] = "Huruf:".$a['letter'];
            if (!empty($a['ox'])) $parts[] = "O/X:".$a['ox'];
            if (!empty($a['text'])) $parts[] = "Text:".$a['text'];
            $row[] = implode(" | ", $parts);
        } else {
            $row[] = "";
        }
    }

    fputcsv($output, $row);
    $no++;
}

fclose($output);
exit;