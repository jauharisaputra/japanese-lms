<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['teacher','admin']);
$page_title = 'Analitik Per Skill';
require __DIR__ . '/../includes/header.php';

$pdo = getPDO();

// Pilih level (N5/N4) dan skill
$level = $_GET['level'] ?? '';
$skillFilter = $_GET['skill'] ?? '';

$levels = ['N5','N4'];
$skills = ['grammar','vocab','reading','listening'];

$data = [];

if ($level !== '' && in_array($level, $levels, true)) {
    // Ambil semua attempt siswa level ini
    $sql = "
        SELECT qa.id, qa.quiz_id, qa.score, qa.answers, q.questions, u.id AS user_id, u.full_name
        FROM quiz_attempts qa
        JOIN quizzes q ON qa.quiz_id = q.id
        JOIN users u ON qa.user_id = u.id
        WHERE u.level = :level
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['level' => $level]);
    $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hitung benar/salah per skill per siswa
    foreach ($attempts as $att) {
        $questions = json_decode($att['questions'], true);
        $answers   = json_decode($att['answers'], true);
        if (!is_array($questions) || !is_array($answers)) {
            continue;
        }

        foreach ($questions as $i => $q) {
            $skill = $q['skill'] ?? 'grammar';
            if ($skillFilter !== '' && $skill !== $skillFilter) {
                continue;
            }

            $correctIndex = $q['correct'] ?? null;
            $studentIndex = isset($answers[$i]) ? (int)$answers[$i] : null;
            $isCorrect    = ($studentIndex !== null && $studentIndex === (int)$correctIndex);

            if (!isset($data[$skill])) {
                $data[$skill] = ['correct' => 0, 'total' => 0];
            }
            $data[$skill]['total']++;
            if ($isCorrect) {
                $data[$skill]['correct']++;
            }
        }
    }
}
?>
<h1>Analitik Nilai per Skill</h1>

<form method="get" style="margin-bottom:15px;">
    <label>Level:
        <select name="level">
            <option value="">-- pilih level --</option>
            <?php foreach ($levels as $lv): ?>
                <option value="<?php echo $lv; ?>" <?php echo $level === $lv ? 'selected' : ''; ?>>
                    <?php echo $lv; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    &nbsp;
    <label>Skill:
        <select name="skill">
            <option value="">(semua)</option>
            <?php foreach ($skills as $sk): ?>
                <option value="<?php echo $sk; ?>" <?php echo $skillFilter === $sk ? 'selected' : ''; ?>>
                    <?php echo $sk; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Terapkan</button>
</form>

<?php if ($level === ''): ?>
    <p>Pilih level dulu untuk melihat analitik per skill.</p>
<?php elseif (!$data): ?>
    <p>Belum ada data attempt untuk filter ini.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Skill</th>
            <th>Benar</th>
            <th>Total</th>
            <th>Persentase Benar</th>
        </tr>
        <?php foreach ($data as $skill => $info): ?>
            <?php
                $correct = $info['correct'];
                $total   = $info['total'];
                $percent = $total > 0 ? round($correct / $total * 100, 1) : 0;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($skill); ?></td>
                <td><?php echo $correct; ?></td>
                <td><?php echo $total; ?></td>
                <td><?php echo $percent; ?>%</td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<p><a href="analytics.php">&laquo; Kembali ke analitik</a></p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
