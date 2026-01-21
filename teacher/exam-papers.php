<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";
requireRole(["teacher","admin","student"]);

$pdo = getPDO();
$exam_id = (int)($_GET['exam_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT pe.name as exam_name, pep.*
    FROM placement_exam_pdfs pep
    JOIN placement_exams pe ON pep.exam_id = pe.id
    WHERE pep.exam_id = ?
    ORDER BY pep.upload_date DESC
    LIMIT 1
");
$stmt->execute([$exam_id]);
$latest_pdf = $stmt->fetch();

// Jika ada PDF terbaru, langsung tampilkan
if ($latest_pdf) {
    $pdf_path = __DIR__ . '/../uploads/placement_pdfs/' . $latest_pdf['filepath'];
    
    // CEK APAKAH FILE BENAR-BENAR ADA (hilangkan error)
    if (file_exists($pdf_path)) {
        // Langsung tampilkan PDF dengan header yang benar
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $latest_pdf['filename'] . '"');
        header('Content-Length: ' . $latest_pdf['filesize']);
        readfile($pdf_path);
        exit;
    }
}

$page_title = "PDF Soal - Exam ID " . $exam_id;
require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>📄 PDF Soal Ujian</h4>
        <div>
            <strong>Exam ID: <?php echo $exam_id; ?></strong>
            <?php if (hasRole('teacher') || hasRole('admin')): ?>
            <a href="upload-pdf.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-sm btn-success ms-2">📎 Upload
                Baru</a>
            <?php endif; ?>
            <a href="index.php" class="btn btn-sm btn-secondary">← Kembali</a>
        </div>
    </div>
    <div class="card-body text-center">
        <?php if (!$latest_pdf): ?>
        <div class="alert alert-warning">
            <h5>📭 Belum ada PDF soal</h5>
            <?php if (hasRole('teacher') || hasRole('admin')): ?>
            <a href="upload-pdf.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-success">Upload PDF Sekarang</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-danger">
            <h5>❌ PDF tidak ditemukan di server</h5>
            <p>File: <?php echo htmlspecialchars($latest_pdf['filename']); ?></p>
            <p><small>Path: uploads/placement_pdfs/<?php echo htmlspecialchars($latest_pdf['filepath']); ?></small></p>
            <?php if (hasRole('teacher') || hasRole('admin')): ?>
            <a href="upload-pdf.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-warning">Upload Ulang</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>