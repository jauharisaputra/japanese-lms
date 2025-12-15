<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['teacher','admin']);
$page_title = 'Hasil Kuis & Remedial';
require __DIR__ . '/../includes/header.php';

global $pdo;

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

// daftar kuis
$stmt = $pdo->query('SELECT id, title, level FROM quizzes ORDER BY level, id DESC');
$allQuizzes = $stmt->fetchAll();

if ($quiz_id <= 0 && $allQuizzes) {
    $quiz_id = (int)$allQuizzes[0]['id'];
}

$showRemedialOnly = isset($_GET['remedial']) && $_GET['remedial'] === '1';

$attempts = [];
if ($quiz_id > 0) {
    $sql = "
        SELECT qa.id,
               qa.user_id,
               u.full_name,
               u.username,
               u.level,
               qa.score,
               qa.total_questions,
               qa.is_passed,
               qa.completed_at
        FROM quiz_attempts qa
        JOIN users u ON qa.user_id = u.id
        WHERE qa.quiz_id = :quiz_id
    ";
    if ($showRemedialOnly) {
        $sql .= " AND qa.is_passed = 0 ";
    }
    $sql .= " ORDER BY qa.completed_at DESC, qa.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['quiz_id' => $quiz_id]);
    $attempts = $stmt->fetchAll();
}
?>
<h1>Hasil Kuis & Remedial</h1>

<form method="get" style="margin-bottom:10px;">
    <label>Pilih kuis:
        <select name="quiz_id">
            <?php foreach ($allQuizzes as $q): ?>
                <option value="<?php echo $q['id']; ?>" <?php echo ($quiz_id == $q['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($q['level'] . ' - ' . $q['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    &nbsp;
    <label>
        <input type="checkbox" name="remedial" value="1" <?php echo $showRemedialOnly ? 'checked' : ''; ?>>
        Tampilkan hanya yang belum lulus
    </label>
    <button type="submit">Terapkan</button>
</form>

<?php if ($quiz_id > 0): ?>
<p>
    <a href="quiz-export.php?quiz_id=<?php echo $quiz_id; ?>">Export CSV hasil kuis ini</a>
</p>
<?php endif; ?>

<?php if (!$attempts): ?>
    <p>Belum ada attempt untuk kuis ini.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Nama siswa</th>
            <th>Username</th>
            <th>Level</th>
            <th>Nilai</th>
            <th>Status</th>
            <th>Waktu</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($attempts as $a): ?>
            <tr>
                <td><?php echo $a['id']; ?></td>
                <td><?php echo htmlspecialchars($a['full_name']); ?></td>
                <td><?php echo htmlspecialchars($a['username']); ?></td>
                <td><?php echo htmlspecialchars($a['level']); ?></td>
                <td><?php echo $a['score']; ?></td>
                <td>
                    <?php if ($a['is_passed']): ?>
                        <span style="color:green;">Lulus</span>
                    <?php else: ?>
                        <span style="color:red;">Perlu remedial</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($a['completed_at']); ?></td>
                <td>
                    <a href="quiz-attempt-detail.php?id=<?php echo $a['id']; ?>">Detail</a> |
                    <a href="quiz-reset-attempt.php?id=<?php echo $a['id']; ?>"
                       onclick="return confirm('Hapus attempt ini? Siswa bisa mengulang dari awal.');">
                       Reset
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<p><a href="analytics.php">&laquo; Kembali ke analitik</a></p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
