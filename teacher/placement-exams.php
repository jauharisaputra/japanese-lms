<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/../includes/placement.php";

requireRole(["teacher","admin"]);

$pdo = getPDO();

$page_title = "Kelola Ujian TO";
require __DIR__ . "/../includes/header.php";

// daftar exam
$exams = $pdo->query("
  SELECT * FROM placement_exams ORDER BY level
")->fetchAll(PDO::FETCH_ASSOC);

// ringkasan attempt per exam
$summary = [];
$stmt = $pdo->query("
  SELECT exam_id,
         COUNT(*) AS total_attempts,
         SUM(passed) AS total_passed
  FROM placement_attempts
  GROUP BY exam_id
");
foreach ($stmt as $r) {
  $summary[$r["exam_id"]] = $r;
}

// ringkasan PDF per exam
$exam_pdfs = [];
$stmt = $pdo->query("
  SELECT exam_id, COUNT(*) as pdf_count 
  FROM placement_exam_pdfs 
  GROUP BY exam_id
");
foreach ($stmt as $r) {
  $exam_pdfs[$r['exam_id']] = $r['pdf_count'];
}
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Kelola Ujian TO (N5 / N4)</div>
    </div>

    <div class="card-body">
        <?php if (!$exams): ?>
        <p>Belum ada definisi ujian TO di tabel placement_exams.</p>
        <?php else: ?>
        <table class="table">
            <tr>
                <th>ID</th>
                <th>Level</th>
                <th>Nama ujian</th>
                <th>Skor maksimal</th>
                <th>Passing score</th>
                <th>Jatah attempt</th>
                <th>Total attempt</th>
                <th>Lulus</th>
                <th>PDF</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($exams as $e): 
            $sid = $e["id"];
            $s   = $summary[$sid] ?? ["total_attempts" => 0, "total_passed" => 0];
            $pdf_count = $exam_pdfs[$sid] ?? 0;
            ?>
            <tr>
                <td><?php echo (int)$e["id"]; ?></td>
                <td><?php echo htmlspecialchars($e["level"]); ?></td>
                <td><?php echo htmlspecialchars($e["name"]); ?></td>
                <td><?php echo (int)$e["max_score"]; ?></td>
                <td><?php echo (int)$e["pass_score"]; ?></td>
                <td><?php echo (int)$e["attempts_allowed"]; ?></td>
                <td><?php echo (int)$s["total_attempts"]; ?></td>
                <td><?php echo (int)$s["total_passed"]; ?></td>
                <td><?php echo (int)$pdf_count; ?></td>
                <td>
                    <a href="placement-results.php?exam_id=<?php echo (int)$e["id"]; ?>"
                        class="btn btn-sm btn-outline-primary mb-1 d-block">
                        Lihat hasil detail
                    </a>
                    <!-- ❌ LINK "Kelola soal TO" DIHILANGKAN -->

                    <a href="upload-pdf.php?exam_id=<?php echo (int)$e["id"]; ?>"
                        class="btn btn-sm btn-success mb-1 d-block">
                        📎 Upload PDF
                    </a>
                    <a href="exam-papers.php?exam_id=<?php echo (int)$e["id"]; ?>"
                        class="btn btn-sm btn-info mb-1 d-block">
                        📄 Lihat PDF
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>