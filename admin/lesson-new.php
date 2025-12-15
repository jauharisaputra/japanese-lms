<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['admin','teacher']);
$page_title = 'Tambah Pelajaran';
require __DIR__ . '/../includes/header.php';

global $pdo;
$errors = [];
$title = $module = $level = $content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = sanitize($_POST['title'] ?? '');
    $module  = sanitize($_POST['module'] ?? '');
    $level   = $_POST['level'] ?? 'N5';
    $content = $_POST['content'] ?? '';

    if ($title === '')  { $errors[] = 'Judul wajib diisi.'; }
    if ($module === '') { $errors[] = 'Modul wajib diisi.'; }
    if (!in_array($level, ['N5','N4'], true)) { $errors[] = 'Level tidak valid.'; }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO lessons (title, level, module, content, order_num, created_by) VALUES (?,?,?,?,?,?)');
        $stmt->execute([
            $title,
            $level,
            $module,
            $content,
            0,
            currentUser()['id'] ?? null
        ]);
        redirect('admin/lessons.php');
    }
}
?>
<h1>Tambah Pelajaran Baru</h1>

<?php if ($errors): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?>
            <li><?php echo $e; ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post">
    <p>
        <label>Judul</label><br>
        <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
    </p>
    <p>
        <label>Modul (misal: Unit 1)</label><br>
        <input type="text" name="module" value="<?php echo htmlspecialchars($module); ?>" required>
    </p>
    <p>
        <label>Level</label><br>
        <select name="level">
            <option value="N5" <?php echo $level==='N5'?'selected':''; ?>>N5</option>
            <option value="N4" <?php echo $level==='N4'?'selected':''; ?>>N4</option>
        </select>
    </p>
    <p>
        <label>Konten (HTML diperbolehkan)</label><br>
        <textarea name="content" rows="8" cols="60"><?php echo htmlspecialchars($content); ?></textarea>
    </p>
    <button type="submit">Simpan</button>
</form>

<p><a href="lessons.php">&laquo; Kembali ke daftar pelajaran</a></p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
