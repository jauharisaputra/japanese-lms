<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$page_title = "Tambah Materi";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();
$user = currentUser();
$errors = [];
$title = "";
$module = "";
$level = "N5";
$content = "";
$order_num = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $module = trim($_POST["module"] ?? "");
    $level = $_POST["level"] ?? "N5";
    $content = $_POST["content"] ?? "";
    $order_num = (int)($_POST["order_num"] ?? 0);

    if ($title === "") {
        $errors[] = "Judul wajib diisi.";
    }
    if ($module === "") {
        $errors[] = "Unit / modul wajib diisi.";
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
            INSERT INTO lessons (title, level, module, content, order_num, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title,
            $level,
            $module,
            $content,
            $order_num,
            $user["id"]
        ]);

        header("Location: ".BASE_URL."teacher/lessons.php");
        exit;
    }
}
?>
<div class="card" style="max-width:720px;margin:24px auto;">
    <div class="card-header">
        <div class="card-title">Tambah Materi Baru</div>
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
            <label>Urutan (angka kecil tampil dulu)<br>
                <input type="number" name="order_num" value="<?php echo (int)$order_num; ?>">
            </label>
        </p>
        <p>
            <label>Konten materi<br>
                <textarea name="content" rows="8" style="width:100%;"><?php echo htmlspecialchars($content); ?></textarea>
            </label>
        </p>
        <p>
            <button type="submit">Simpan materi</button>
            <a class="button secondary" href="<?php echo BASE_URL; ?>teacher/lessons.php">Batal</a>
        </p>
    </form>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
