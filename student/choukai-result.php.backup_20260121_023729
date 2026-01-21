<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$user = currentUser();
$pdo = getPDO();

$id = (int)($_GET["id"] ?? 0);
if (!$id) {
    die("Data tidak ditemukan.");
}

/* Ambil choukai */
$stmt = $pdo->prepare("
    SELECT *
    FROM choukai_materials
    WHERE id = ?
");
$stmt->execute([$id]);
$choukai = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$choukai) {
    die("Choukai tidak ditemukan.");
}

/* Ambil jawaban siswa */
$stmt = $pdo->prepare("
    SELECT *
    FROM choukai_answers
   WHERE choukai_id = ? AND user_id = ?

");
$stmt->execute([$id, $user["id"]]);
$answer = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = "Hasil Choukai";
require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            📊 Hasil Choukai<br>
            <small><?= htmlspecialchars($choukai["title"]) ?></small>
        </div>
    </div>

    <div class="card-body">

        <p>
            <strong>Bab:</strong>
            <?= $choukai["bab_start"] ?>–<?= $choukai["bab_end"] ?>
        </p>

        <?php if ($answer): ?>

        <p><strong>Jawaban Anda:</strong></p>
        <div style="white-space:pre-wrap;border:1px solid #ccc;padding:10px;">
            <?= htmlspecialchars($answer["answers"]) ?>
        </div>

        <p style="margin-top:10px;">
            <small>
                Dikirim pada:
                <?= date("d-m-Y H:i", strtotime($answer["created_at"])) ?>
            </small>
        </p>

        <?php if ($answer["score"] !== null): ?>
        <hr>

        <p>
            <strong>🎯 Nilai:</strong>
            <span style="font-size:20px;font-weight:bold;">
                <?= (int)$answer["score"] ?>
            </span>
        </p>

        <?php if (!empty($answer["feedback"])): ?>
        <p><strong>📝 Catatan Guru:</strong></p>
        <div style="background:#f9f9f9;padding:10px;border-left:4px solid #4caf50;">
            <?= nl2br(htmlspecialchars($answer["feedback"])) ?>
        </div>
        <?php endif; ?>

        <p style="margin-top:10px;">
            <small>
                Dinilai pada:
                <?= date("d-m-Y H:i", strtotime($answer["graded_at"])) ?>
            </small>
        </p>

        <?php else: ?>
        <p style="color:orange;">⏳ Menunggu penilaian guru.</p>
        <?php endif; ?>

        <?php else: ?>
        <p>❌ Anda belum mengirim jawaban.</p>
        <?php endif; ?>


        <br>

        <a href="<?= BASE_URL ?>student/choukai.php?id=<?= $id ?>" class="button">
            🔁 Kembali ke Choukai
        </a>
    </div>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>