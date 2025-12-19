<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$pdo = getPDO();

$quiz_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($quiz_id <= 0) {
    redirect("teacher/quizzes.php");
}

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$quiz) {
    redirect("teacher/quizzes.php");
}

$page_title = "Edit Kuis";
require __DIR__ . "/../includes/header.php";

$errors = [];
$title        = $quiz["title"];
$level        = $quiz["level"];
$lesson_id    = $quiz["lesson_id"];
$time_limit   = (int)$quiz["time_limit"];
$passing      = (int)$quiz["passing_score"];
$max_attempts = (int)$quiz["max_attempts"];
$questions    = $quiz["questions"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title        = trim($_POST["title"] ?? "");
    $level        = $_POST["level"] ?? "N5";
    $lesson_id    = $_POST["lesson_id"] !== "" ? (int)$_POST["lesson_id"] : null;
    $time_limit   = (int)($_POST["time_limit"] ?? 30);
    $passing      = (int)($_POST["passing_score"] ?? 70);
    $max_attempts = (int)($_POST["max_attempts"] ?? 3);
    $questions    = $_POST["questions"] ?? "";

    if ($title === "") $errors[] = "Judul wajib diisi.";
    if ($questions === "") $errors[] = "Format soal (JSON) wajib diisi.";

    // cek JSON valid
    if ($questions !== "") {
        $decoded = json_decode($questions, true);
        if (!is_array($decoded)) {
            $errors[] = "Format JSON tidak valid.";
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
            UPDATE quizzes
               SET title = ?, level = ?, lesson_id = ?, questions = ?, time_limit = ?, passing_score = ?, max_attempts = ?
             WHERE id = ?
        ");
        $stmt->execute([
            $title,
            $level,
            $lesson_id,
            $questions,
            $time_limit,
            $passing,
            $max_attempts,
            $quiz_id
        ]);

        redirect("teacher/quizzes.php?level=" . $level);
    }
}

// ambil daftar lesson utk dropdown (opsional)
$lessonStmt = $pdo->query("SELECT id, title, level FROM lessons ORDER BY level, order_num, id");
$lessons = $lessonStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card" style="max-width:780px;margin:24px auto;">
    <div class="card-header">
        <div class="card-title">Edit Kuis</div>
    </div>

    <?php if ($errors): ?>
        <ul style="color:#c62828;">
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <p>
            <label>Judul kuis<br>
                <input type="text" name="title" required
                       value="<?php echo htmlspecialchars($title); ?>">
            </label>
        </p>
        <p>
            <label>Level<br>
                <select name="level">
                    <option value="N5" <?php echo $level === "N5" ? "selected" : ""; ?>>N5</option>
                    <option value="N4" <?php echo $level === "N4" ? "selected" : ""; ?>>N4</option>
                </select>
            </label>
        </p>
        <p>
            <label>Terhubung ke Lesson<br>
                <select name="lesson_id">
                    <option value="">(Tidak terhubung)</option>
                    <?php foreach ($lessons as $les): ?>
                        <option value="<?php echo (int)$les["id"]; ?>"
                            <?php echo $lesson_id == $les["id"] ? "selected" : ""; ?>>
                            [<?php echo htmlspecialchars($les["level"]); ?>]
                            <?php echo htmlspecialchars($les["title"]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
        <p>
            <label>Waktu (menit)<br>
                <input type="number" name="time_limit" value="<?php echo (int)$time_limit; ?>">
            </label>
        </p>
        <p>
            <label>Passing score (%)<br>
                <input type="number" name="passing_score" value="<?php echo (int)$passing; ?>">
            </label>
        </p>
        <p>
            <label>Maksimum attempt<br>
                <input type="number" name="max_attempts" value="<?php echo (int)$max_attempts; ?>">
            </label>
        </p>
        <p>
            <label>Format soal (JSON)<br>
                <textarea name="questions" rows="12" style="width:100%;"><?php
                    echo htmlspecialchars($questions);
                ?></textarea>
            </label>
        </p>
        <p>
            <button type="submit">Simpan perubahan</button>
            <a class="button secondary" href="<?php echo BASE_URL; ?>teacher/quizzes.php?level=<?php echo htmlspecialchars($level); ?>">Batal</a>
        </p>
    </form>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
