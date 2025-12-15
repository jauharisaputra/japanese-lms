<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['teacher','admin']);
$page_title = 'Analitik Sederhana';
require __DIR__ . '/../includes/header.php';

global $pdo;

// Rata-rata nilai per level
$stmt = $pdo->query("
    SELECT u.level, AVG(qa.score) AS avg_score, COUNT(*) AS attempts
    FROM quiz_attempts qa
    JOIN users u ON qa.user_id = u.id
    WHERE u.role = 'student'
    GROUP BY u.level
");
$rows = $stmt->fetchAll();
?>
<h1>Analitik Nilai Siswa</h1>
<?php if (!$rows): ?>
    <p>Belum ada data kuis.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Level</th>
            <th>Rata-rata Nilai</th>
            <th>Jumlah Attempt</th>
        </tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo $r['level']; ?></td>
                <td><?php echo round($r['avg_score'], 1); ?></td>
                <td><?php echo $r['attempts']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
