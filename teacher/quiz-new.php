<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['teacher','admin']);
$page_title = 'Tambah Kuis';
require __DIR__ . '/../includes/header.php';

global $pdo;

$errors = [];
$title = '';
$level = 'N5';
$lesson_id = '';
$questions_json = '';

$stmt = $pdo->query('SELECT id, title, module, level FROM lessons ORDER BY level, order_num, id');
$lessons = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = sanitize($_POST['title'] ?? '');
    $level   = $_POST['level'] ?? 'N5';
    $lesson_id = (int)($_POST['lesson_id'] ?? 0);
    $questions_json = trim($_POST['questions'] ?? '');

    if ($title === '')  { $errors[] = 'Judul kuis wajib diisi.'; }
    if (!in_array($level, ['N5','N4'], true)) { $errors[] = 'Level tidak valid.'; }
    if ($lesson_id <= 0) { $errors[] = 'Pelajaran harus dipilih.'; }
    if ($questions_json === '') { $errors[] = 'Soal (JSON) wajib diisi.'; }

    $decoded = json_decode($questions_json, true);
    if ($questions_json !== '' && !is_array($decoded)) {
        $errors[] = 'Format JSON soal tidak valid.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO quizzes (title, level, lesson_id, questions, time_limit, passing_score, max_attempts, created_by) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([
            $title,
            $level,
            $lesson_id,
            $questions_json,
            15,   // time_limit menit
            70,   // passing_score
            3,    // max_attempts
            currentUser()['id'] ?? null
        ]);
        redirect('teacher/quizzes.php');
    }
}
?>
<h1>Tambah Kuis Baru</h1>

<p>Format soal (JSON) contoh:</p>
<pre>[
  {
    "question": "Halo dalam bahasa Jepang?",
    "options": ["?????", "?????", "?????", "????"],
    "correct": 0
  },
  {
    "question": "Arti ??????",
    "options": ["Halo", "Terima kasih", "Selamat tinggal", "Selamat pagi"],
    "correct": 1
  }
]</pre>

<?php if ($errors): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post">
    <p>
        <label>Judul Kuis</label><br>
        <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
    </p>
    <p>
        <label>Level</label><br>
        <select name="level">
            <option value="N5" <?php echo $level==='N5'?'selected':''; ?>>N5</option>
            <option value="N4" <?php echo $level==='N4'?'selected':''; ?>>N4</option>
        </select>
    </p>
    <p>
        <label>Pelajaran</label><br>
        <select name="lesson_id" required>
            <option value="">-- pilih pelajaran --</option>
            <?php foreach ($lessons as $l): ?>
                <option value="<?php echo $l['id']; ?>" <?php echo ($lesson_id==$l['id'])?'selected':''; ?>>
                    <?php echo $l['level'] . ' - ' . htmlspecialchars($l['module'] . ' - ' . $l['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label>Soal (JSON)</label><br>
        <textarea name="questions" rows="12" cols="70"><?php echo htmlspecialchars($questions_json); ?></textarea>
    </p>
    <button type="submit">Simpan Kuis</button>
</form>

<p><a href="quizzes.php">&laquo; Kembali ke daftar kuis</a></p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
