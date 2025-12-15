<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['student']);
$page_title = 'Detail Materi';
require __DIR__ . '/../includes/header.php';

global $pdo;
$user    = currentUser();
$user_id = $user['id'];

$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($lesson_id <= 0) {
    echo "<p>ID materi tidak valid.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM lessons WHERE id = ?');
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    echo "<p>Materi tidak ditemukan.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}
?>
<h1><?php echo htmlspecialchars($lesson['title']); ?></h1>

<p><?php echo nl2br(htmlspecialchars($lesson['content'] ?? '')); ?></p>

<p>
    <a href="lesson-complete.php?lesson_id=<?php echo $lesson_id; ?>&redirect=lesson-view.php?id=<?php echo $lesson_id; ?>">
        Tandai materi selesai
    </a>
</p>

<p><a href="lessons.php">&laquo; Kembali ke daftar materi</a></p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
