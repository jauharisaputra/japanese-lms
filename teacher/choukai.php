<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$page_title = "Kelola choukai";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();

/* =========================
   FILTER LEVEL
   ========================= */
$level = $_GET["level"] ?? "N5";

$stmt = $pdo->prepare("
    SELECT *
    FROM choukai
    WHERE level = ?
    ORDER BY chapter_start
");
$stmt->execute([$level]);
$choukaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">Kelola choukai (Per 4 Bab)</div>
    </div>

    <!-- FILTER LEVEL -->
    <form method="get" style="margin-bottom:12px;">
        <label>Level:
            <select name="level">
                <option value="N5" <?= $level === "N5" ? "selected" : "" ?>>N5</option>
                <option value="N4" <?= $level === "N4" ? "selected" : "" ?>>N4</option>
            </select>
        </label>
        <button type="submit">Terapkan</button>

        <a class="button secondary" href="<?= BASE_URL ?>teacher/choukai-new.php">
            + Tambah choukai
        </a>
    </form>

    <?php if (!$choukaiList): ?>
    <p>Belum ada choukai untuk level ini.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Level</th>
                <th>Bab</th>
                <th>Judul</th>
                <th>File</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($choukaiList as $d): ?>
            <tr>
                <td><?= (int)$d["id"] ?></td>
                <td><?= htmlspecialchars($d["level"]) ?></td>
                <td>
                    <?= (int)$d["chapter_start"] ?>
                    –
                    <?= (int)$d["chapter_end"] ?>
                </td>
                <td><?= htmlspecialchars($d["title"]) ?></td>
                <td>
                    <?php if (!empty($d["file_path"])): ?>
                    <a href="<?= BASE_URL . htmlspecialchars($d["file_path"]) ?>" target="_blank">
                        Lihat File
                    </a>
                    <?php else: ?>
                    -
                    <?php endif; ?>
                </td>
                <td>
                    <a class="button secondary" href="<?= BASE_URL ?>teacher/choukai-edit.php?id=<?= (int)$d["id"] ?>">
                        Edit
                    </a>
                    <a class="button" href="<?= BASE_URL ?>teacher/choukai-delete.php?id=<?= (int)$d["id"] ?>"
                        onclick="return confirm('Hapus choukai ini?');">
                        Hapus
                    </a>
                    <a class="button primary"
                        href="<?= BASE_URL ?>teacher/choukai-add-questions.php?id=<?= (int)$d["id"] ?>">Tambah Soal</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>
