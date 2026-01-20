<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$pdo = getPDO();

$submission_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($submission_id <= 0) {
    die("Submission tidak valid.");
}

/* ==========================
   AMBIL DATA SUBMISSION
========================== */
$stmt = $pdo->prepare("
    SELECT 
        s.id AS sub_id,
        s.score,
        s.kaiwa_score,
        s.comment,
        s.submitted_at,
        a.title AS assignment_title,
        a.description,
        u.full_name
    FROM assignment_submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN users u       ON s.user_id = u.id
    WHERE s.id = ?
");
$stmt->execute([$submission_id]);
$s = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$s) {
    die('Data submission tidak ditemukan.');
}

$page_title = "Penilaian Tugas";
require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">📝 Penilaian Tugas</div>
    </div>

    <p>
        <strong>Siswa:</strong> <?= htmlspecialchars($s["full_name"]) ?><br>
        <strong>Tugas:</strong> <?= htmlspecialchars($s["assignment_title"]) ?><br>
        <strong>Dikirim:</strong> <?= htmlspecialchars($s["submitted_at"]) ?>
    </p>

    <hr>

    <h5>📎 File Jawaban</h5>
    <ul>
        <?php
        $fs = $pdo->prepare("
            SELECT * 
            FROM assignment_files 
            WHERE submission_id = ?
        ");
        $fs->execute([$submission_id]);
        $files = $fs->fetchAll(PDO::FETCH_ASSOC);

        if (!$files):
        ?>
        <li>Tidak ada file.</li>
        <?php else: ?>
        <?php foreach ($files as $f): ?>
        <li>
            <a href="<?= BASE_URL . htmlspecialchars($f["file_path"]) ?>" target="_blank">
                <?= htmlspecialchars($f["file_type"]) ?> — <?= basename($f["file_path"]) ?>
            </a>
        </li>
        <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <hr>

    <h5>✏️ Input Nilai</h5>
    <form method="post" action="<?= BASE_URL ?>teacher/grade-assignment.php">
        <input type="hidden" name="submission_id" value="<?= (int)$s["sub_id"] ?>">

        <div style="margin-bottom:8px;">
            <label>Nilai Umum</label><br>
            <input type="number" name="score" step="0.1" value="<?= (float)$s["score"] ?>" required>
        </div>

        <div style="margin-bottom:8px;">
            <label>Nilai Kaiwa</label><br>
            <input type="number" name="kaiwa_score" step="0.1" value="<?= (float)$s["kaiwa_score"] ?>">
        </div>

        <div style="margin-bottom:8px;">
            <label>Komentar Guru</label><br>
            <textarea name="comment" rows="3" style="width:100%;"><?= htmlspecialchars($s["comment"]) ?></textarea>
        </div>

        <button class="btn btn-primary">💾 Simpan Nilai</button>
        <a class="btn btn-secondary" href="<?= BASE_URL ?>teacher/assignment-submissions.php">
            ⬅ Kembali
        </a>
    </form>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>