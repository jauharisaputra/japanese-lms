<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['teacher', 'admin']);
$page_title = 'Kelola Kuis';
require __DIR__ . '/../includes/header.php';

global $pdo;
$stmt = $pdo->query('SELECT id, title, level FROM quizzes ORDER BY level, id DESC');
$quizzes = $stmt->fetchAll();
?>
<h1>Kelola Kuis</h1>
<p><a href="quiz-new.php">+ Tambah Kuis Baru</a></p>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Judul</th>
        <th>Level</th>
    </tr>
    <?php foreach ($quizzes as $quiz): ?>
        <tr>
            <td><?php echo $quiz['id']; ?></td>
            <td><?php echo htmlspecialchars($quiz['title']); ?></td>
            <td><?php echo $quiz['level']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
