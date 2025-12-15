<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$page_title = "Daftar Materi";
require __DIR__ . "/../includes/header.php";

global $pdo;
$user  = currentUser();
$level = $user["level"] ?? "N5";

$stmt = $pdo->prepare("SELECT * FROM lessons WHERE level = ? ORDER BY order_num, id");
$stmt->execute([$level]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Materi Level <?php echo htmlspecialchars($level); ?></div>
    </div>
    <?php if (!$lessons): ?>
        <p>Belum ada materi untuk level ini.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Urutan</th>
                <th>Unit</th>
                <th>Judul</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($lessons as $lesson): ?>
                <tr>
                    <td><?php echo (int)$lesson["order_num"]; ?></td>
                    <td><?php echo htmlspecialchars($lesson["module"]); ?></td>
                    <td><?php echo htmlspecialchars($lesson["title"]); ?></td>
                    <td>
                        <a class="button" href="lesson-view.php?id=<?php echo (int)$lesson["id"]; ?>">Buka</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
