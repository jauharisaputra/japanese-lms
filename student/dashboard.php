<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['student']);
$page_title = 'Dashboard Siswa';
require __DIR__ . '/../includes/header.php';

global $pdo;
$user    = currentUser();
$user_id = $user['id'];
$level   = $user['level'] ?? 'N5';

// hitung lesson level ini
$stmt = $pdo->prepare('SELECT COUNT(*) FROM lessons WHERE level = ?');
$stmt->execute([$level]);
$totalLessons = (int)$stmt->fetchColumn();

// lesson completed
$stmt = $pdo->prepare('
    SELECT COUNT(*) FROM lesson_progress lp
    JOIN lessons l ON lp.lesson_id = l.id
    WHERE lp.user_id = ? AND lp.status = "completed" AND l.level = ?
');
$stmt->execute([$user_id, $level]);
$completedLessons = (int)$stmt->fetchColumn();

// kuis lulus
$stmt = $pdo->prepare('
    SELECT COUNT(DISTINCT qa.quiz_id)
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    WHERE qa.user_id = ? AND qa.is_passed = 1 AND q.level = ?
');
$stmt->execute([$user_id, $level]);
$passedQuizzes = (int)$stmt->fetchColumn();

// total kuis level ini
$stmt = $pdo->prepare('SELECT COUNT(*) FROM quizzes WHERE level = ?');
$stmt->execute([$level]);
$totalQuizzes = (int)$stmt->fetchColumn();

$lessonPct = $totalLessons > 0 ? round($completedLessons / $totalLessons * 100, 1) : 0;
$quizPct   = $totalQuizzes > 0 ? round($passedQuizzes / $totalQuizzes * 100, 1) : 0;
?>
<h1>Dashboard Siswa</h1>

<p>Halo, <?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?> (Level <?php echo htmlspecialchars($level); ?>)</p>

<ul>
    <li>Materi selesai: <?php echo $completedLessons . ' / ' . $totalLessons; ?> (<?php echo $lessonPct; ?>%)</li>
    <li>Kuis lulus: <?php echo $passedQuizzes . ' / ' . $totalQuizzes; ?> (<?php echo $quizPct; ?>%)</li>
</ul>

<p>
    <a href="lessons.php">Lanjut materi</a> |
    <a href="quizzes.php">Lanjut kuis</a> |
    <a href="progress.php">Lihat riwayat nilai</a>
</p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
