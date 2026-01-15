<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$pdo = getPDO();

/* ===================== SIMPAN NILAI ===================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $answer_id = (int)($_POST["answer_id"] ?? 0);
    $score     = $_POST["score"] ?? null;
    $feedback  = trim($_POST["feedback"] ?? "");
    $choukai_id = (int)($_POST["choukai_id"] ?? 0);

    if ($answer_id && $score !== null) {
        $stmt = $pdo->prepare("
            UPDATE choukai_answers
            SET
                score = ?,
                feedback = ?,
                graded_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$score, $feedback, $answer_id]);
    }

    header("Location: choukai-review.php?id=".$choukai_id);
    exit;
}

/* ===================== DATA CHOUKAI ===================== */
$choukai_id = (int)($_GET["id"] ?? 0);
if (!$choukai_id) {
    die("Choukai tidak valid.");
}

$stmt = $pdo->prepare("
    SELECT *
    FROM choukai_materials
    WHERE id = ?
");
$stmt->execute([$choukai_id]);
$choukai = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$choukai) {
    die("Data choukai tidak ditemukan.");
}

/* ===================== JAWABAN SISWA ===================== */
$stmt = $pdo->prepare("
    SELECT 
        ca.id,
        ca.user_id,
        u.full_name,
        ca.answers,
        ca.created_at,
        ca.score,
        ca.feedback
    FROM choukai_answers ca
    JOIN users u ON ca.user_id = u.id
    WHERE ca.choukai_id = ?
    ORDER BY ca.created_at ASC
");
$stmt->execute([$choukai_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Penilaian Choukai";
require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            🎧 Penilaian Choukai<br>
            <small><?= htmlspecialchars($choukai["title"]) ?></small>
        </div>
    </div>

    <div class="card-body">
        <p>
            <strong>Bab:</strong>
            <?= $choukai["bab_start"] ?>–<?= $choukai["bab_end"] ?>
        </p>

        <?php if (!$rows): ?>
        <p>Belum ada jawaban siswa.</p>
        <?php else: ?>

        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Jawaban</th>
                    <th>Nilai</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <form method="post">
                        <td><?= htmlspecialchars($r["full_name"]) ?></td>

                        <td style="white-space:pre-wrap;max-width:400px;">
                            <?= htmlspecialchars($r["answers"]) ?>
                        </td>

                        <td>
                            <input type="number" name="score" value="<?= htmlspecialchars($r["score"] ?? "") ?>" min="0"
                                max="100" required style="width:70px;">
                        </td>

                        <td>
                            <textarea name="feedback" rows="2"
                                style="width:220px;"><?= htmlspecialchars($r["feedback"] ?? "") ?></textarea>
                        </td>

                        <td>
                            <input type="hidden" name="answer_id" value="<?= $r["id"] ?>">
                            <input type="hidden" name="choukai_id" value="<?= $choukai_id ?>">
                            <button type="submit" class="btn btn-sm btn-primary">
                                Simpan
                            </button>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>