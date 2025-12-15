<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['student']);
$page_title = 'Daftar Kuis';
require __DIR__ . '/../includes/header.php';

global $pdo;
$user    = currentUser();
$user_id = $user['id'];
$level   = $user['level'] ?? 'N5';

// Ambil kuis beserta lesson terkait
$stmt = $pdo->prepare('
    SELECT q.id, q.title, q.lesson_id, l.title AS lesson_title
    FROM quizzes q
    LEFT JOIN lessons l ON q.lesson_id = l.id
    WHERE q.level = ?
    ORDER BY q.id DESC
');
$stmt->execute([$level]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil progress lesson siswa
$progressStmt = $pdo->prepare('
    SELECT lesson_id, status
    FROM lesson_progress
    WHERE user_id = ?
');
$progressStmt->execute([$user_id]);
$progressRows = $progressStmt->fetchAll(PDO::FETCH_ASSOC);

$progress = [];
foreach ($progressRows as $row) {
    $progress[(int)$row['lesson_id']] = $row['status'];
}
?>
<h1>Kuis Level <?php echo htmlspecialchars($level); ?></h1>

<?php if (!$quizzes): ?>
    <p>Belum ada kuis untuk level ini.</p>
<?php else: ?>
    <ul>
        <?php foreach ($quizzes as $quiz): ?>
            <?php
                $lessonId    = (int)$quiz['lesson_id'];
                $lessonTitle = $quiz['lesson_title'] ?? '';
                $status      = $lessonId && isset($progress[$lessonId]) ? $progress[$lessonId] : null;
                // aturan: jika tidak ada lesson_id  bebas; jika ada, wajib status completed
                $unlocked    = ($lessonId === 0) || ($status === 'completed');
            ?>
            <li>
                <?php echo htmlspecialchars($quiz['title']); ?>
                <?php if ($lessonTitle): ?>
                    (Materi: <?php echo htmlspecialchars($lessonTitle); ?>)
                <?php endif; ?>
                <?php if ($unlocked): ?>
                    - <a href="quiz-do.php?id=<?php echo $quiz['id']; ?>">Kerjakan</a>
                <?php else: ?>
                    - <span style="color:gray;">Selesaikan materi dulu</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
