<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$pdo = getPDO();

$lesson_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($lesson_id <= 0) {
    redirect("teacher/lessons.php");
}

$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$lesson) {
    redirect("teacher/lessons.php");
}

$page_title = "Edit Materi";
require __DIR__ . "/../includes/header.php";

$errors = [];
$title     = $lesson["title"];
$module    = $lesson["module"];
$level     = $lesson["level"];
$content   = $lesson["content"];
$order_num = (int)$lesson["order_num"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title     = trim($_POST["title"] ?? "");
    $module    = trim($_POST["module"] ?? "");
    $level     = $_POST["level"] ?? "N5";
    $content   = $_POST["content"] ?? "";
    $order_num = (int)($_POST["order_num"] ?? 0);

    if ($title === "")  $errors[] = "Judul wajib diisi.";
    if ($module === "") $errors[] = "Unit / modul wajib diisi.";

    if (!$errors) {
        $stmt = $pdo->prepare("
            UPDATE lessons
            SET title = ?, level = ?, module = ?, content = ?, order_num = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $level, $module, $content, $order_num, $lesson_id]);
        redirect("teacher/lessons.php?level={$level}");
    }
}
?>
<div class="card" style="max-width:720px;margin:24px auto;">
    <div class="card-header">
        <div class="card-title">Edit Materi</div>
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
            <label>Judul materi<br>
                <input type="text" name="title" required
                       value="<?php echo htmlspecialchars($title); ?>">
            </label>
        </p>
        <p>
            <label>Unit / Modul<br>
                <input type="text" name="module" required
                       value="<?php echo htmlspecialchars($module); ?>">
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
            <label>Urutan<br>
                <input type="number" name="order_num" value="<?php echo (int)$order_num; ?>">
            </label>
        </p>
        <p>
            <label>Konten materi<br>
                <textarea name="content" rows="8" style="width:100%;"><?php
                    echo htmlspecialchars($content);
                ?></textarea>
            </label>
        </p>
        <p>
            <button type="submit">Simpan perubahan</button>
            <a class="button secondary" href="<?php echo BASE_URL; ?>teacher/lessons.php?level=<?php echo htmlspecialchars($level); ?>">Batal</a>
        </p>
    </form>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
