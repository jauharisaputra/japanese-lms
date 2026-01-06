<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$page_title = "Detail Materi";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();
$user    = currentUser();
$user_id = $user["id"];

$lesson_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($lesson_id <= 0) {
    echo "<p>ID materi tidak valid.</p>";
    require __DIR__ . "/../includes/footer.php";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    echo "<p>Materi tidak ditemukan.</p>";
    require __DIR__ . "/../includes/footer.php";
    exit;
}
// Cek progres materi ini
$stmt = $pdo->prepare("
    SELECT status
    FROM lesson_progress
    WHERE user_id = ? AND lesson_id = ?
    LIMIT 1
");
$stmt->execute([$user_id, $lesson_id]);
$progress    = $stmt->fetch(PDO::FETCH_ASSOC);
$isCompleted = $progress && $progress["status"] === "completed";
?>
<div class="card">
    <div class="card-header">
        <div class="card-title"><?php echo htmlspecialchars($lesson["title"]); ?></div>
    </div>
    <div>
        <p><?php echo nl2br(htmlspecialchars($lesson["content"] ?? "")); ?></p>

        <?php if ($isCompleted): ?>
        <p>
            <button type="button" class="button" disabled>
                ✔ Materi sudah selesai
            </button>
        </p>
        <?php else: ?>
        <p>
            <a class="button"
                href="lesson-complete.php?lesson_id=<?php echo $lesson_id; ?>&redirect=lesson-view.php?id=<?php echo $lesson_id; ?>">
                Tandai materi selesai
            </a>
        </p>
        <?php endif; ?>


        <p><a href="lessons.php">&laquo; Kembali ke daftar materi</a></p>
    </div>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>