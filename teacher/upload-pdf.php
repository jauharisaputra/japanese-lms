<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/../includes/placement.php";

requireRole(["teacher","admin"]);
$pdo = getPDO();

$exam_id = (int)($_GET['exam_id'] ?? 0);  // ✅ CAST KE INT
$exam = $pdo->prepare("SELECT * FROM placement_exams WHERE id = ?");
$exam->execute([$exam_id]);
$exam = $exam->fetch();

if (!$exam) {
    header("Location: index.php");
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {  // ✅ GANTI DARI $_POST
    $upload_dir = __DIR__ . '/../uploads/placement_pdfs/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);  // ✅ 0755 bukan 0777
    }
    
    $file = $_FILES['pdf_file'] ?? null;  // ✅ CEK NULL
    
    if ($file && $file['error'] === UPLOAD_ERR_OK) {  // ✅ CEK ERROR UPLOAD
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($file_ext === 'pdf' && $file['size'] < 10*1024*1024) {
            $filename = $exam_id . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $file['name']);  // ✅ SANITIZE
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $stmt = $pdo->prepare("
                    INSERT INTO placement_exam_pdfs (exam_id, filename, filepath, filesize) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$exam_id, $file['name'], $filename, $file['size']]);
                $message = '<div class="alert alert-success">✅ PDF berhasil diupload!<br>Nama file: ' . htmlspecialchars($file['name']) . '</div>';
            } else {
                $message = '<div class="alert alert-danger">❌ Gagal simpan file ke folder!</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">❌ Hanya file PDF < 10MB!</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">❌ Error upload: ' . ($file['error'] ?? 'No file') . '</div>';
    }
}

$page_title = "Upload PDF Soal - " . htmlspecialchars($exam['name']);
require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <h4>📎 Upload PDF Soal - <?php echo htmlspecialchars($exam['name']); ?></h4>
    </div>
    <div class="card-body">
        <?php echo $message ?: '<div class="alert alert-info">Pilih file PDF soal untuk diupload</div>'; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">File PDF Soal (max 10MB)</label>
                <input type="file" class="form-control" name="pdf_file" accept=".pdf" required>
            </div>
            <button type="submit" class="btn btn-primary">🚀 Upload PDF</button>
            <a href="index.php" class="btn btn-secondary">← Kembali</a>
        </form>
    </div>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>