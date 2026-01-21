<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";
requireRole(["teacher","admin"]);

$pdo = getPDO();
$exam_id = (int)($_GET['exam_id'] ?? 0);

$page_title = "Kelola Soal TO";
require __DIR__ . "/../includes/header.php";

// Handle upload PDF
if ($_POST && isset($_FILES['pdf_file'])) {
    $target_dir = __DIR__ . "/../uploads/to_questions/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    
    $file = $_FILES['pdf_file'];
    $filename = "to_{$exam_id}_q" . $_POST['question_number'] . ".pdf";
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        $stmt = $pdo->prepare("
            INSERT INTO placement_questions (exam_id, question_number, pdf_file) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE pdf_file = ?
        ");
        $stmt->execute([$exam_id, $_POST['question_number'], $filename, $filename]);
        $success = "PDF soal berhasil diupload!";
    }
}

// Ambil data exam dan soal existing
$exam = $pdo->prepare("SELECT * FROM placement_exams WHERE id = ?")->execute([$exam_id])->fetch();
$questions = $pdo->prepare("SELECT * FROM placement_questions WHERE exam_id = ? ORDER BY question_number")->execute([$exam_id])->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h5>Upload Soal TO <?= htmlspecialchars($exam['level'] ?? '') ?> - <?= htmlspecialchars($exam['name'] ?? '') ?>
        </h5>
        <a href="placement-exams.php" class="btn btn-secondary btn-sm">← Kembali</a>
    </div>

    <div class="card-body">
        <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <!-- Form Upload PDF per nomor soal -->
        <form method="POST" enctype="multipart/form-data" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label>Nomor Soal</label>
                    <input type="number" name="question_number" class="form-control" required min="1" max="100">
                </div>
                <div class="col-md-6">
                    <label>File PDF Soal</label>
                    <input type="file" name="pdf_file" class="form-control" accept=".pdf" required>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Upload PDF</button>
                </div>
            </div>
        </form>

        <!-- Daftar Soal yang Sudah Diupload -->
        <?php if ($questions): ?>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Nomor Soal</th>
                    <th>File PDF</th>
                    <th>Tanggal Upload</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($questions as $q): ?>
                <tr>
                    <td>#<?= (int)$q['question_number'] ?></td>
                    <td><?= htmlspecialchars($q['pdf_file']) ?></td>
                    <td><?= date('d/m/Y', strtotime($q['created_at'])) ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>uploads/to_questions/<?= htmlspecialchars($q['pdf_file']) ?>"
                            target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                        <a href="to_questions.php?exam_id=<?= $exam_id ?>&delete=<?= $q['id'] ?>"
                            class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Belum ada soal yang diupload.</p>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>