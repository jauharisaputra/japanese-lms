<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher"]);

$pdo = getPDO();

// ambil exam aktif
$exam_id = isset($_GET["exam_id"]) ? (int)$_GET["exam_id"] : 0;
if ($exam_id < 1) {
    $exam = $pdo->query("SELECT * FROM to_exams ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$exam) {
        die("Belum ada data ujian TO (to_exams).");
    }
    $exam_id = (int)$exam["id"];
} else {
    $stmt = $pdo->prepare("SELECT * FROM to_exams WHERE id = ?");
    $stmt->execute([$exam_id]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$exam) {
        die("Ujian TO tidak ditemukan.");
    }
}

$errors = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $section = trim($_POST["section"] ?? "");
    $qtext   = trim($_POST["question_text"] ?? "");
    $choiceA = trim($_POST["choice_a"] ?? "");
    $choiceB = trim($_POST["choice_b"] ?? "");
    $choiceC = trim($_POST["choice_c"] ?? "");
    $choiceD = trim($_POST["choice_d"] ?? "");
    $correct = $_POST["correct_choice"] ?? "";
    $point   = (int)($_POST["point"] ?? 3);

    if ($section === "") $errors[] = "Section wajib diisi.";
    if ($qtext === "")   $errors[] = "Teks soal wajib diisi.";
    if ($choiceA === "" || $choiceB === "" || $choiceC === "" || $choiceD === "") {
        $errors[] = "Semua pilihan (A–D) wajib diisi.";
    }
    if (!in_array($correct, ["A","B","C","D"], true)) {
        $errors[] = "Jawaban benar tidak valid.";
    }

    if (!$errors) {
        $sql = "INSERT INTO to_questions
                (exam_id, section, question_text,
                 choice_a, choice_b, choice_c, choice_d,
                 correct_choice, point)
                VALUES
                (:exam_id, :section, :question_text,
                 :choice_a, :choice_b, :choice_c, :choice_d,
                 :correct_choice, :point)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":exam_id"        => $exam_id,
            ":section"        => $section,
            ":question_text"  => $qtext,
            ":choice_a"       => $choiceA,
            ":choice_b"       => $choiceB,
            ":choice_c"       => $choiceC,
            ":choice_d"       => $choiceD,
            ":correct_choice" => $correct,
            ":point"          => $point,
        ]);

        require_once __DIR__ . "/../admin/to_generate_json.php";
        regenerateToJson($exam_id);

        header("Location: to_questions.php?exam_id=".$exam_id."&saved=1");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM to_questions WHERE exam_id = ? ORDER BY id");
$stmt->execute([$exam_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Kelola Soal TO (" . $exam["level"] . " " . $exam["title"] . ")";
require __DIR__ . "/../includes/header.php";
?>
<div class="page-content">
  <div class="card">
    <div class="card-header">
      <div class="card-title">Kelola Soal TO (<?php echo htmlspecialchars($exam["title"]); ?>)</div>
    </div>
    <div class="card-body">

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $e): ?>
            <div><?php echo htmlspecialchars($e); ?></div>
          <?php endforeach; ?>
        </div>
      <?php elseif (isset($_GET["saved"])): ?>
        <div class="alert alert-success">Soal berhasil disimpan dan file JSON diperbarui.</div>
      <?php endif; ?>

      <h3>Tambah Soal Baru</h3>
      <form method="post" class="form-vertical">
        <label>Section</label>
        <select name="section" class="form-control">
          <option value="bunpo_goi">bunpo_goi</option>
          <option value="dokkai">dokkai</option>
          <option value="kanji">kanji</option>
          <option value="sakubun">sakubun</option>
          <option value="photo">photo</option>
          <option value="list_read">list_read</option>
          <option value="ougo">ougo</option>
          <option value="kaiwa_setsumei">kaiwa_setsumei</option>
        </select>

        <label>Soal</label>
        <textarea name="question_text" class="form-control" rows="3"></textarea>

        <label>Pilihan A</label>
        <input type="text" name="choice_a" class="form-control">

        <label>Pilihan B</label>
        <input type="text" name="choice_b" class="form-control">

        <label>Pilihan C</label>
        <input type="text" name="choice_c" class="form-control">

        <label>Pilihan D</label>
        <input type="text" name="choice_d" class="form-control">

        <label>Jawaban Benar</label>
        <select name="correct_choice" class="form-control">
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
          <option value="D">D</option>
        </select>

        <label>Poin</label>
        <input type="number" name="point" class="form-control" value="3">

        <button type="submit" class="btn btn-primary mt-2">Simpan Soal</button>
      </form>

      <hr>

      <h3>Daftar Soal (exam_id=<?php echo (int)$exam_id; ?>)</h3>
      <?php if (!$questions): ?>
        <p>Belum ada soal.</p>
      <?php else: ?>
        <ol>
          <?php foreach ($questions as $q): ?>
            <li>
              <strong>[<?php echo htmlspecialchars($q["section"]); ?>]</strong>
              <?php echo htmlspecialchars($q["question_text"]); ?>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php endif; ?>

    </div>
  </div>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
