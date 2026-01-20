<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$page_title = "Nilai Tugas";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();
$id  = (int)($_GET["id"] ?? 0);

if (!$id) {
    echo "<p>ID submission tidak valid.</p>";
    require __DIR__ . "/../includes/footer.php";
    exit;
}

/* ==========================
   DATA SUBMISSION
========================== */
$stmt = $pdo->prepare("
    SELECT 
        s.*,
        u.full_name,
        a.title AS assignment_title
    FROM assignment_submissions s
    JOIN users u       ON s.user_id = u.id
    JOIN assignments a ON s.assignment_id = a.id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo "<p>Data tidak ditemukan.</p>";
    require __DIR__ . "/../includes/footer.php";
    exit;
}

/* ==========================
   SIMPAN NILAI
========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $score   = $_POST["score"] ?? null;
    $comment = $_POST["comment"] ?? null;

    $up = $pdo->prepare("
        UPDATE assignment_submissions
        SET score = ?, comment = ?
        WHERE id = ?
    ");
    $up->execute([$score, $comment, $id]);

    header("Location: assignment-submissions.php?status=ungraded");
    exit;
}
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">✏️ Penilaian Tugas</div>
    </div>

    <p>
        <strong>Siswa:</strong> <?= htmlspecialchars($data["full_name"]) ?><br>
        <strong>Tugas:</strong> <?= htmlspecialchars($data["assignment_title"]) ?><br>
        <strong>Waktu Submit:</strong> <?= htmlspecialchars($data["submitted_at"]) ?>
    </p>

    <?php if ($data["file_path"]): ?>
    <p>
        <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL . $data["file_path"] ?>" target="_blank">
            📂 Lihat File Tugas
        </a>
    </p>
    <?php endif; ?>

    <form method="post">
        <div class="mb-2">
            <label>Nilai</label>
            <input type="number" name="score" class="form-control" value="<?= htmlspecialchars($data["score"]) ?>"
                step="1" min="0" max="100" required>
        </div>

        <div class="mb-2">
            <label>Komentar Guru</label>
            <textarea name="comment" class="form-control" rows="3"><?= htmlspecialchars($data["comment"]) ?></textarea>
        </div>

        <button class="btn btn-success">💾 Simpan Nilai</button>
        <a class="btn btn-secondary" href="assignment-submissions.php">Kembali</a>
    </form>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>