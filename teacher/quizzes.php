<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$page_title = "Kelola Kuis";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();

$level = $_GET["level"] ?? "N5";

$stmt = $pdo->prepare("
    SELECT q.*, l.title AS lesson_title
    FROM quizzes q
    LEFT JOIN lessons l ON q.lesson_id = l.id
    WHERE q.level = ?
    ORDER BY q.id
");
$stmt->execute([$level]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Kelola Kuis (Level <?php echo htmlspecialchars($level); ?>)</div>
    </div>

    <form method="get" style="margin-bottom:12px;">
        <label>Level:
            <select name="level">
                <option value="N5" <?php echo $level === "N5" ? "selected" : ""; ?>>N5</option>
                <option value="N4" <?php echo $level === "N4" ? "selected" : ""; ?>>N4</option>
            </select>
        </label>
        <button type="submit">Terapkan</button>
        <a class="button secondary" href="<?php echo BASE_URL; ?>teacher/quiz-new.php">+ Tambah kuis</a>
    </form>

    <?php if (!$quizzes): ?>
        <p>Belum ada kuis untuk level ini.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Judul</th>
                <th>Lesson</th>
                <th>Waktu</th>
                <th>Passing</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($quizzes as $q): ?>
                <tr>
                    <td><?php echo (int)$q["id"]; ?></td>
                    <td><?php echo htmlspecialchars($q["title"]); ?></td>
                    <td><?php echo htmlspecialchars($q["lesson_title"] ?? "-"); ?></td>
                    <td><?php echo (int)$q["time_limit"]; ?> menit</td>
                    <td><?php echo (int)$q["passing_score"]; ?>%</td>
                    <td>
                        <a class="button secondary"
                           href="<?php echo BASE_URL; ?>teacher/quiz-edit.php?id=<?php echo (int)$q["id"]; ?>">Edit</a>
                        <a class="button"
                           href="<?php echo BASE_URL; ?>teacher/quiz-delete.php?id=<?php echo (int)$q["id"]; ?>"
                           onclick="return confirm('Hapus kuis ini beserta attempt siswa?');">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
