<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$page_title = "Tambah Soal Choukai";
require __DIR__ . "/../includes/header.php";

$pdo  = getPDO();
$user = currentUser();
$errors = [];

/* =========================
   AMBIL CHOUKAI
   ========================= */
$choukai_id = (int)($_GET['choukai_id'] ?? 0);
if (!$choukai_id) {
    die("Choukai tidak valid.");
}

$stmt = $pdo->prepare("SELECT * FROM choukai WHERE id = ?");
$stmt->execute([$choukai_id]);
$choukai = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$choukai) {
    die("Data choukai tidak ditemukan.");
}

/* =========================
   PROSES SIMPAN SOAL
   ========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $question = trim($_POST['question'] ?? '');
    $option_a = trim($_POST['option_a'] ?? '');
    $option_b = trim($_POST['option_b'] ?? '');
    $option_c = trim($_POST['option_c'] ?? '');
    $option_d = trim($_POST['option_d'] ?? '');
    $correct_answer = $_POST['correct_answer'] ?? '';
    $question_order = (int)($_POST['question_order'] ?? 0);

    /* --- Validasi --- */
    if ($question === '') {
        $errors[] = "Pertanyaan wajib diisi.";
    }

    if (!in_array($correct_answer, ['A','B','C','D'])) {
        $errors[] = "Jawaban benar harus A / B / C / D.";
    }

    if (!$errors) {

        /* Simpan soal */
        $stmt = $pdo->prepare("
            INSERT INTO choukai_questions
            (choukai_id, question, option_a, option_b, option_c, option_d, correct_answer, question_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $choukai_id,
            $question,
            $option_a,
            $option_b,
            $option_c,
            $option_d,
            $correct_answer,
            $question_order
        ]);

        header("Location: choukai-add-questions.php?choukai_id={$choukai_id}&success=1");
        exit;
    }
}

/* =========================
   AMBIL LIST SOAL
   ========================= */
$stmt = $pdo->prepare("
    SELECT *
    FROM choukai_questions
    WHERE choukai_id = ?
    ORDER BY question_order, id
");
$stmt->execute([$choukai_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            Tambah Soal – <?= htmlspecialchars($choukai['title']) ?>
        </div>
    </div>

    <?php if (!empty($_GET['success'])): ?>
    <div class="alert success">Soal berhasil ditambahkan.</div>
    <?php endif; ?>

    <?php if ($errors): ?>
    <div class="alert error">
        <ul>
            <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="post">

        <label>Pertanyaan</label>
        <textarea name="question" rows="3" required></textarea>

        <label>Opsi A</label>
        <input type="text" name="option_a" required>

        <label>Opsi B</label>
        <input type="text" name="option_b" required>

        <label>Opsi C</label>
        <input type="text" name="option_c" required>

        <label>Opsi D</label>
        <input type="text" name="option_d" required>

        <label>Jawaban Benar</label>
        <select name="correct_answer" required>
            <option value="">-- Pilih --</option>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
        </select>

        <label>Urutan Soal</label>
        <input type="number" name="question_order" value="<?= count($questions)+1 ?>">

        <div style="margin-top:12px;">
            <button type="submit">Tambah Soal</button>
            <a href="choukai.php" class="button secondary">Kembali</a>
        </div>
    </form>
</div>

<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <div class="card-title">Daftar Soal</div>
    </div>

    <?php if (!$questions): ?>
    <p>Belum ada soal.</p>
    <?php else: ?>
    <ol>
        <?php foreach ($questions as $q): ?>
        <li>
            <?= htmlspecialchars($q['question']) ?>
            <small>(<?= $q['correct_answer'] ?>)</small>
        </li>
        <?php endforeach; ?>
    </ol>
    <?php endif; ?>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>
