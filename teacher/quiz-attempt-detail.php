<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['teacher','admin']);
$page_title = 'Detail Attempt Kuis';
require __DIR__ . '/../includes/header.php';

$pdo = getPDO();

$attempt_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($attempt_id <= 0) {
    echo "<p>ID attempt tidak valid.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare('
    SELECT qa.*, u.full_name, u.username, u.level,
           q.title AS quiz_title, q.questions
    FROM quiz_attempts qa
    JOIN users u ON qa.user_id = u.id
    JOIN quizzes q ON qa.quiz_id = q.id
    WHERE qa.id = ?
');
$stmt->execute([$attempt_id]);
$attempt = $stmt->fetch();

if (!$attempt) {
    echo "<p>Attempt tidak ditemukan.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$questions = json_decode($attempt['questions'], true);
$answers   = json_decode($attempt['answers'], true);
?>
<h1>Detail Attempt Kuis</h1>
<p>
    Kuis: <strong><?php echo htmlspecialchars($attempt['quiz_title']); ?></strong><br>
    Siswa: <?php echo htmlspecialchars($attempt['full_name'] . ' (' . $attempt['username'] . ')'); ?><br>
    Level: <?php echo htmlspecialchars($attempt['level']); ?><br>
    Nilai: <?php echo (int)$attempt['score']; ?>,
    Status:
    <?php if ($attempt['is_passed']): ?>
        <span style="color:green;">Lulus</span>
    <?php else: ?>
        <span style="color:red;">Perlu remedial</span>
    <?php endif; ?>
</p>

<?php if (!is_array($questions)): ?>
    <p>Data soal tidak dapat dibaca.</p>
<?php else: ?>
    <ol>
        <?php foreach ($questions as $index => $q): ?>
            <?php
                $correctIndex = $q['correct'] ?? null;
                $studentIndex = isset($answers[$index]) ? (int)$answers[$index] : null;
                $skill = isset($q['skill']) ? $q['skill'] : 'unknown';
            ?>
            <li style="margin-bottom:10px;">
                <strong>[<?php echo htmlspecialchars($skill); ?>]</strong>
                <?php echo htmlspecialchars($q['question']); ?><br>
                <?php foreach ($q['options'] as $optIndex => $opt): ?>
                    <?php
                        $style = '';
                        if ($optIndex === $correctIndex) {
                            $style = 'color:green; font-weight:bold;';
                        }
                        if ($studentIndex === $optIndex && $studentIndex !== $correctIndex) {
                            $style = 'color:red; text-decoration:underline;';
                        }
                    ?>
                    <div style="<?php echo $style; ?>">
                        <?php echo chr(65 + $optIndex) . '. ' . htmlspecialchars($opt); ?>
                        <?php if ($studentIndex === $optIndex): ?> (jawaban siswa)<?php endif; ?>
                        <?php if ($correctIndex === $optIndex): ?> (kunci)<?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>

<p><a href="javascript:history.back()">&laquo; Kembali</a></p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
