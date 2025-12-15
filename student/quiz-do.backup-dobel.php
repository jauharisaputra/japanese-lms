<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['student']);
$page_title = 'Kerjakan Kuis';
require __DIR__ . '/../includes/header.php';

global $pdo;
$user    = currentUser();
$user_id = $user['id'];

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id <= 0) {
    echo "<p>ID kuis tidak valid.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

// Ambil data kuis
$stmt = $pdo->prepare('SELECT * FROM quizzes WHERE id = ?');
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    echo "<p>Kuis tidak ditemukan.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

// Cek status attempt & lulus
$stmt = $pdo->prepare('
    SELECT COUNT(*) AS attempts,
           MAX(is_passed) AS already_passed
    FROM quiz_attempts
    WHERE user_id = ? AND quiz_id = ?
');
$stmt->execute([$user_id, $quiz_id]);
$attemptInfo   = $stmt->fetch();
$attempts      = (int)$attemptInfo['attempts'];
$alreadyPassed = (int)$attemptInfo['already_passed'];

$maxAttempts = (int)$quiz['max_attempts']; // dari tabel quizzes
$passing     = (int)$quiz['passing_score'];

if ($alreadyPassed) {
    echo "<p>Anda sudah <strong>lulus</strong> kuis ini. Tidak perlu remedial lagi.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

if ($maxAttempts > 0 && $attempts >= $maxAttempts) {
    echo "<p>Anda sudah mencapai batas attempt (".$maxAttempts.") untuk kuis ini. "
       . "Silakan hubungi guru jika perlu dibuka remedial ulang.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

// Decode soal
$questions = json_decode($quiz['questions'], true);
if (!is_array($questions)) {
    echo "<p>Format soal kuis rusak.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers      = $_POST['answer'] ?? [];
    $correctCount = 0;
    $total        = count($questions);

    foreach ($questions as $index => $q) {
        $correctIndex = $q['correct'] ?? null;
        if (isset($answers[$index]) && (int)$answers[$index] === (int)$correctIndex) {
            $correctCount++;
        }
    }

    $score    = $total > 0 ? round(($correctCount / $total) * 100) : 0;
    $isPassed = $score >= $passing ? 1 : 0;

    // attempt ke-berapa
    $attemptNumber = $attempts + 1;

    // Simpan ke quiz_attempts
    $stmt = $pdo->prepare('
        INSERT INTO quiz_attempts (user_id, quiz_id, score, total_questions, answers, time_taken, is_passed)
        VALUES (?,?,?,?,?,?,?)
    ');
    $stmt->execute([
        $user_id,
        $quiz_id,
        $score,
        $total,
        json_encode($answers),
        0,
        $isPassed
    ]);

    $result = [
        'score'    => $score,
        'correct'  => $correctCount,
        'total'    => $total,
        'is_passed'=> $isPassed,
        'attempt'  => $attemptNumber
    ];
}
?>
<h1><?php echo htmlspecialchars($quiz['title']); ?></h1>

<?php if ($result): ?>
    <p>Nilai Anda: <strong><?php echo $result['score']; ?></strong>
       (<?php echo $result['correct']; ?>/<?php echo $result['total']; ?> benar)</p>
    <p>Status:
        <?php if ($result['is_passed']): ?>
            <span style="color:green;">Lulus</span>
        <?php else: ?>
            <span style="color:red;">Perlu remedial</span>
        <?php endif; ?>
        (Attempt ke-<?php echo $result['attempt']; ?>)
    </p>
    <p><a href="quizzes.php">&laquo; Kembali ke daftar kuis</a></p>
<?php else: ?>
    <p>Waktu tersisa: <span id="timer"></span></p>
    <form method="post">
        <?php foreach ($questions as $index => $q): ?>
            <fieldset style="margin-bottom:15px;">
                <legend><?php echo ($index+1) . '. ' . htmlspecialchars($q['question']); ?></legend>
                <?php foreach ($q['options'] as $optIndex => $opt): ?>
                    <label>
                        <input type="radio" name="answer[<?php echo $index; ?>]" value="<?php echo $optIndex; ?>">
                        <?php echo htmlspecialchars($opt); ?>
                    </label><br>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
        <button type="submit">Kirim Jawaban</button>
    </form>
    <p><a href="quizzes.php">&laquo; Kembali ke daftar kuis</a></p>
<?php endif; ?>
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['student']);
$page_title = 'Kerjakan Kuis';
require __DIR__ . '/../includes/header.php';

global $pdo;
$user    = currentUser();
$user_id = $user['id'];

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id <= 0) {
    echo "<p>ID kuis tidak valid.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

// Ambil data kuis
$stmt = $pdo->prepare('SELECT * FROM quizzes WHERE id = ?');
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    echo "<p>Kuis tidak ditemukan.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

// Cek status attempt & lulus
$stmt = $pdo->prepare('
    SELECT COUNT(*) AS attempts,
           MAX(is_passed) AS already_passed
    FROM quiz_attempts
    WHERE user_id = ? AND quiz_id = ?
');
$stmt->execute([$user_id, $quiz_id]);
$attemptInfo   = $stmt->fetch();
$attempts      = (int)$attemptInfo['attempts'];
$alreadyPassed = (int)$attemptInfo['already_passed'];

$maxAttempts = (int)$quiz['max_attempts']; // dari tabel quizzes
$passing     = (int)$quiz['passing_score'];

if ($alreadyPassed) {
    echo "<p>Anda sudah <strong>lulus</strong> kuis ini. Tidak perlu remedial lagi.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

if ($maxAttempts > 0 && $attempts >= $maxAttempts) {
    echo "<p>Anda sudah mencapai batas attempt (".$maxAttempts.") untuk kuis ini. "
       . "Silakan hubungi guru jika perlu dibuka remedial ulang.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

// Decode soal
$questions = json_decode($quiz['questions'], true);
if (!is_array($questions)) {
    echo "<p>Format soal kuis rusak.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers      = $_POST['answer'] ?? [];
    $correctCount = 0;
    $total        = count($questions);

    foreach ($questions as $index => $q) {
        $correctIndex = $q['correct'] ?? null;
        if (isset($answers[$index]) && (int)$answers[$index] === (int)$correctIndex) {
            $correctCount++;
        }
    }

    $score    = $total > 0 ? round(($correctCount / $total) * 100) : 0;
    $isPassed = $score >= $passing ? 1 : 0;

    // attempt ke-berapa
    $attemptNumber = $attempts + 1;

    // Simpan ke quiz_attempts
    $stmt = $pdo->prepare('
        INSERT INTO quiz_attempts (user_id, quiz_id, score, total_questions, answers, time_taken, is_passed)
        VALUES (?,?,?,?,?,?,?)
    ');
    $stmt->execute([
        $user_id,
        $quiz_id,
        $score,
        $total,
        json_encode($answers),
        0,
        $isPassed
    ]);

    $result = [
        'score'    => $score,
        'correct'  => $correctCount,
        'total'    => $total,
        'is_passed'=> $isPassed,
        'attempt'  => $attemptNumber
    ];
}
?>
<h1><?php echo htmlspecialchars($quiz['title']); ?></h1>

<?php if ($result): ?>
    <p>Nilai Anda: <strong><?php echo $result['score']; ?></strong>
       (<?php echo $result['correct']; ?>/<?php echo $result['total']; ?> benar)</p>
    <p>Status:
        <?php if ($result['is_passed']): ?>
            <span style="color:green;">Lulus</span>
        <?php else: ?>
            <span style="color:red;">Perlu remedial</span>
        <?php endif; ?>
        (Attempt ke-<?php echo $result['attempt']; ?>)
    </p>
    <p><a href="quizzes.php">&laquo; Kembali ke daftar kuis</a></p>
<?php else: ?>
    <form method="post">
        <?php foreach ($questions as $index => $q): ?>
            <fieldset style="margin-bottom:15px;">
                <legend><?php echo ($index+1) . '. ' . htmlspecialchars($q['question']); ?></legend>
                <?php foreach ($q['options'] as $optIndex => $opt): ?>
                    <label>
                        <input type="radio" name="answer[<?php echo $index; ?>]" value="<?php echo $optIndex; ?>">
                        <?php echo htmlspecialchars($opt); ?>
                    </label><br>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
        <button type="submit">Kirim Jawaban</button>
    </form>
    <p><a href="quizzes.php">&laquo; Kembali ke daftar kuis</a></p>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
<script>
(function(){
    var minutes = <?php echo (int)$quiz['time_limit']; ?>;
    var seconds = 0;
    var timerEl = document.getElementById('timer');
    var form    = document.querySelector('form');

    function updateTimer() {
        if (!timerEl) return;
        var m = minutes;
        var s = seconds;
        if (m <= 0 && s <= 0) {
            timerEl.textContent = '00:00';
            if (form) {
                form.submit();
            }
            return;
        }
        var mm = (m < 10 ? '0' + m : m);
        var ss = (s < 10 ? '0' + s : s);
        timerEl.textContent = mm + ':' + ss;

        if (s === 0) {
            minutes--;
            seconds = 59;
        } else {
            seconds--;
        }
        setTimeout(updateTimer, 1000);
    }
    updateTimer();
})();
</script>
<script>
(function(){
    // total detik dihitung di PHP: jumlah_soal * 90
    var totalSeconds = <?php echo (int)$totalSeconds; ?>;
    var timerEl = document.getElementById('timer');
    var form    = document.querySelector('form');

    function updateTimer() {
        if (!timerEl) return;

        if (totalSeconds <= 0) {
            timerEl.textContent = '00:00';
            if (form) {
                form.submit();
            }
            return;
        }

        var m = Math.floor(totalSeconds / 60);
        var s = totalSeconds % 60;

        var mm = (m < 10 ? '0' + m : m);
        var ss = (s < 10 ? '0' + s : s);
        timerEl.textContent = mm + ':' + ss;

        totalSeconds--;
        setTimeout(updateTimer, 1000);
    }
    updateTimer();
})();
</script>
