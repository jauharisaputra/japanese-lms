<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$page_title = "Rekap Choukai";

$pdo = getPDO();

$stmt = $pdo->query("
    SELECT 
        c.id,
        c.title,
        c.bab_start,
        c.bab_end,
        COUNT(a.id) AS total_submit
    FROM choukai_materials c
    LEFT JOIN choukai_answers a ON a.choukai_id = c.id
    GROUP BY c.id
    ORDER BY c.created_at DESC
");

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">📊 Rekap & Nilai Choukai</div>
    </div>

    <div class="card-body">
        <?php if (!$rows): ?>
        <p>Belum ada data choukai.</p>
        <?php else: ?>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Bab</th>
                    <th>Jumlah Submit</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r["title"]) ?></td>
                    <td><?= $r["bab_start"] ?>–<?= $r["bab_end"] ?></td>
                    <td><?= $r["total_submit"] ?></td>
                    <td>
                        <a class="btn btn-sm btn-primary"
                            href="<?= BASE_URL ?>teacher/choukai-review.php?id=<?= $r["id"] ?>">
                            👀 Lihat Jawaban
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>