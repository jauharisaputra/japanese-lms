<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['teacher', 'admin']);
$page_title = 'Daftar Siswa';
require __DIR__ . '/../includes/header.php';

global $pdo;
$stmt = $pdo->query("SELECT id, username, full_name, level FROM users WHERE role = 'student' ORDER BY level, id");
$students = $stmt->fetchAll();
?>
<h1>Daftar Siswa</h1>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Nama</th>
        <th>Level</th>
    </tr>
    <?php foreach ($students as $s): ?>
        <tr>
            <td><?php echo $s['id']; ?></td>
            <td><?php echo htmlspecialchars($s['username']); ?></td>
            <td><?php echo htmlspecialchars($s['full_name']); ?></td>
            <td><?php echo $s['level']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
