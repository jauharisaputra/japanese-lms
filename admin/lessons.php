<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['admin', 'teacher']);
$page_title = 'Kelola Pelajaran';
require __DIR__ . '/../includes/header.php';

global $pdo;
$stmt = $pdo->query('SELECT id, title, level, module FROM lessons ORDER BY level, order_num');
$lessons = $stmt->fetchAll();
?>
<h1>Kelola Pelajaran</h1>
<a href="lesson-new.php">+ Tambah Pelajaran</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Judul</th>
        <th>Level</th>
        <th>Modul</th>
    </tr>
    <?php foreach ($lessons as $lesson): ?>
        <tr>
            <td><?php echo $lesson['id']; ?></td>
            <td><?php echo htmlspecialchars($lesson['title']); ?></td>
            <td><?php echo $lesson['level']; ?></td>
            <td><?php echo htmlspecialchars($lesson['module']); ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
