<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$user = currentUser();
$pdo = getPDO();

$id = (int)($_GET["id"] ?? 0);
if (!$id) {
    die("Choukai tidak ditemukan.");
}

/* Ambil data choukai */
$stmt = $pdo->prepare("
    SELECT *
    FROM choukai_materials
    WHERE id = ?
");
$stmt->execute([$id]);
$choukai = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$choukai) {
    die("Data choukai tidak ditemukan.");
}

$page_title = "Choukai - " . $choukai["title"];
require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            🎧 <?= htmlspecialchars($choukai["title"]); ?>
            <small>
                (Bab <?= $choukai["bab_start"]; ?>–<?= $choukai["bab_end"]; ?>)
            </small>
        </div>
    </div>

    <div class="card-body">

        <!-- AUDIO -->
        <audio controls style="width:100%; margin-bottom:12px;">
            <source src="<?= BASE_URL . htmlspecialchars($choukai["audio_path"]); ?>" type="audio/mpeg">
            Browser Anda tidak mendukung audio.
        </audio>

        <!-- PDF VIEWER -->
        <iframe src="<?= BASE_URL . htmlspecialchars($choukai["pdf_path"]); ?>" width="100%" height="500"
            style="border:1px solid #ccc; margin-bottom:12px;"></iframe>

        <!-- FORM JAWABAN -->
        <form method="post" action="<?= BASE_URL ?>student/choukai-submit.php">
            <input type="hidden" name="choukai_id" value="<?= $choukai["id"]; ?>">

            <label><strong>✍️ Jawaban Anda:</strong></label>
            <textarea name="answer" rows="6" style="width:100%;" required></textarea>

            <br><br>

            <button type="submit" class="btn btn-primary">
                Kirim Jawaban
            </button>
        </form>

    </div>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>