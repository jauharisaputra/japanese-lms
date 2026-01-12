<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['teacher','admin']);
$page_title = 'Tambah Soal Dokkai (JSON)';
require __DIR__ . '/../includes/header.php';

$pdo = getPDO();

$errors = [];
$dokkai_id = '';
$level = 'N5';
$questions_json = '';

// Ambil daftar dokkai
$stmt = $pdo->query('SELECT id, title, chapter_start, chapter_end, level FROM dokkai ORDER BY level, chapter_start');
$dokkai_list = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dokkai_id = (int)($_POST['dokkai_id'] ?? 0);
    $level = $_POST['level'] ?? 'N5';
    $questions_json = trim($_POST['questions'] ?? '');

    if ($dokkai_id <= 0) { $errors[] = 'Dokkai harus dipilih.'; }
    if (!in_array($level, ['N5','N4'], true)) { $errors[] = 'Level tidak valid.'; }
    if ($questions_json === '') { $errors[] = 'Soal (JSON) wajib diisi.'; }

    $decoded = json_decode($questions_json, true);
    if ($questions_json !== '' && !is_array($decoded)) {
        $errors[] = 'Format JSON soal tidak valid.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO dokkai_questions (dokkai_id, question, options, correct) VALUES (?,?,?,?)');

        foreach ($decoded as $q) {
            if (!isset($q['question'], $q['options'], $q['correct'])) continue;

            $options = array_values($q['options']);
            $correct = (int)$q['correct'];

            $stmt->execute([
                $dokkai_id,
                $q['question'],
                json_encode($options, JSON_UNESCAPED_UNICODE),
                $correct
            ]);
        }

        redirect('teacher/dokkai.php?level=' . urlencode($level));
    }
}
?>

<h1>Tambah Soal Dokkai (JSON)</h1>

<p>Format JSON contoh:</p>
<pre>
[
  {
    "question": "Halo dalam bahasa Jepang?",
    "options": ["こんにちは", "さようなら", "ありがとう", "おはよう"],
    "correct": 0
  },
  {
    "question": "Arti ありがとう",
    "options": ["Halo", "Terima kasih", "Selamat tinggal", "Selamat pagi"],
    "correct": 1
  }
]
</pre>

<?php if ($errors): ?>
<ul style="color:red;">
    <?php foreach ($errors as $e): ?>
    <li><?php echo htmlspecialchars($e); ?></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

<form method="post">
    <p>
        <label>Level</label><br>
        <select name="level">
            <option value="N5" <?php echo $level==='N5'?'selected':''; ?>>N5</option>
            <option value="N4" <?php echo $level==='N4'?'selected':''; ?>>N4</option>
        </select>
    </p>
    <p>
        <label>Pilih Dokkai</label><br>
        <select name="dokkai_id" required>
            <option value="">-- pilih dokkai --</option>
            <?php foreach ($dokkai_list as $d): ?>
            <option value="<?= (int)$d['id']; ?>" <?= $dokkai_id==$d['id']?'selected':''; ?>>
                <?= htmlspecialchars($d['level'] . ' Bab ' . $d['chapter_start'] . '-' . $d['chapter_end'] . ' - ' . $d['title']); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label>Soal (JSON)</label><br>
        <textarea name="questions" rows="12" cols="70"><?= htmlspecialchars($questions_json); ?></textarea>
    </p>
    <button type="submit">Simpan Soal</button>
</form>

<p><a href="dokkai.php">&laquo; Kembali ke daftar dokkai</a></p>

<?php require __DIR__ . '/../includes/footer.php'; ?>