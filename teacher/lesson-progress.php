<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['teacher','admin']);
$page_title = 'Progress Lesson Siswa';
require __DIR__ . '/../includes/header.php';

global $pdo;

$level = $_GET['level'] ?? 'N5';

// ambil semua lesson level ini
$stmt = $pdo->prepare('
    SELECT id, title, module, order_num
    FROM lessons
    WHERE level = ?
    ORDER BY order_num, id
');
$stmt->execute([$level]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// hitung jumlah siswa completed per lesson
$progStmt = $pdo->prepare('
    SELECT lp.lesson_id,
           SUM(lp.status = "completed") AS completed_count
    FROM lesson_progress lp
    JOIN users u ON lp.user_id = u.id
    WHERE u.role = "student" AND u.level = ?
    GROUP BY lp.lesson_id
');
$progStmt->execute([$level]);
$progRows = $progStmt->fetchAll(PDO::FETCH_ASSOC);

$completedMap = [];
foreach ($progRows as $row) {
    $completedMap[(int)$row['lesson_id']] = (int)$row['completed_count'];
}

// hitung total siswa level ini
$studentStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role = "student" AND level = ?');
$studentStmt->execute([$level]);
$totalStudents = (int)$studentStmt->fetchColumn();
?>
<h1>Progress Lesson Siswa (Level <?php echo htmlspecialchars($level); ?>)</h1>

<form method="get" style="margin-bottom:10px;">
    <label>Level:
        <select name="level">
            <option value="N5" <?php echo $level==='N5'?'selected':''; ?>>N5</option>
            <option value="N4" <?php echo $level==='N4'?'selected':''; ?>>N4</option>
        </select>
    </label>
    <button type="submit">Terapkan</button>
</form>

<?php if (!$lessons): ?>
    <p>Belum ada lesson untuk level ini.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Urutan</th>
            <th>Materi</th>
            <th>Siswa selesai</th>
            <th>Persentase</th>
        </tr>
        <?php foreach ($lessons as $lesson): ?>
            <?php
                $lid   = (int)$lesson['id'];
                $done  = $completedMap[$lid] ?? 0;
                $pct   = ($totalStudents > 0) ? round($done / $totalStudents * 100, 1) : 0;
            ?>
            <tr>
                <td><?php echo (int)$lesson['order_num']; ?></td>
                <td><?php echo htmlspecialchars($lesson['module'].' - '.$lesson['title']); ?></td>
                <td><?php echo $done . ' / ' . $totalStudents; ?></td>
                <td><?php echo $pct; ?>%</td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<p><a href="analytics.php">&laquo; Kembali ke analitik</a></p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
