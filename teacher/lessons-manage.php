<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$page_title = "Kelola Materi";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();

$level = $_GET["level"] ?? "N5";

$stmt = $pdo->prepare("SELECT * FROM lessons WHERE level = ? ORDER BY order_num, id");
$stmt->execute([$level]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Kelola Materi (Level <?php echo htmlspecialchars($level); ?>)</div>
    </div>

    <form method="get" style="margin-bottom:12px;">
        <label>Level:
            <select name="level">
                <option value="N5" <?php echo $level === "N5" ? "selected" : ""; ?>>N5</option>
                <option value="N4" <?php echo $level === "N4" ? "selected" : ""; ?>>N4</option>
            </select>
        </label>
        <button type="submit">Terapkan</button>
        <a class="button secondary" href="lesson-create.php">+ Tambah materi</a>
    </form>

    <?php if (!$lessons): ?>
        <p>Belum ada materi untuk level ini.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Urutan</th>
                <th>Unit</th>
                <th>Judul</th>
                <th>Dibuat oleh</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($lessons as $lesson): ?>
                <tr>
                    <td><?php echo (int)$lesson["order_num"]; ?></td>
                    <td><?php echo htmlspecialchars($lesson["module"]); ?></td>
                    <td><?php echo htmlspecialchars($lesson["title"]); ?></td>
                    <td><?php echo (int)$lesson["created_by"]; ?></td>
                    <td>
                        <a class="button secondary" href="lesson-edit.php?id=<?php echo (int)$lesson["id"]; ?>">Edit</a>
                        <a class="button" href="lesson-delete.php?id=<?php echo (int)$lesson["id"]; ?>"
                           onclick="return confirm('Hapus materi ini?');">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
