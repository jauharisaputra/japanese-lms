<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['student']);
$page_title = 'Progress & Nilai';
require __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$user    = currentUser();
$user_id = $user['id'];

$stmt = $pdo->prepare('
    SELECT qa.id, q.title, qa.score, qa.total_questions,
           qa.is_passed, qa.completed_at
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    WHERE qa.user_id = ?
    ORDER BY qa.completed_at DESC, qa.id DESC
');
$stmt->execute([$user_id]);
$attempts = $stmt->fetchAll();
?>
<h1>Progress & Nilai Kuis</h1>

<?php if (!$attempts): ?>
    <p>Belum ada kuis yang dikerjakan.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Waktu</th>
            <th>Judul Kuis</th>
            <th>Nilai</th>
            <th>Status</th>
        </tr>
        <?php foreach ($attempts as $a): ?>
            <tr>
                <td><?php echo htmlspecialchars($a['completed_at']); ?></td>
                <td><?php echo htmlspecialchars($a['title']); ?></td>
                <td><?php echo (int)$a['score']; ?></td>
                <td>
                    <?php if ($a['is_passed']): ?>
                        <span style="color:green;">Lulus</span>
                    <?php else: ?>
                        <span style="color:red;">Perlu remedial</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
