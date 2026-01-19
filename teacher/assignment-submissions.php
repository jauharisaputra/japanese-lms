<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$pdo = getPDO();

$page_title = "Penilaian Tugas Siswa";
require __DIR__ . "/../includes/header.php";

/*
 Ambil semua tugas + jumlah submission
*/
$stmt = $pdo->query("
    SELECT 
        a.id,
        a.title,
        a.type,
        a.level,
        a.chapter_start,
        a.chapter_end,
        a.due_date,
        COUNT(s.id) AS total_submission
    FROM assignments a
    LEFT JOIN assignment_submissions s
        ON s.assignment_id = a.id
    GROUP BY a.id
    ORDER BY a.created_at DESC
");
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">📝 Penilaian Tugas Siswa</div>
    </div>

    <?php if (!$assignments): ?>
    <p>Belum ada tugas.</p>
    <?php else: ?>
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Jenis</th>
                <th>Level</th>
                <th>Bab</th>
                <th>Deadline</th>
                <th>Submission</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assignments as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a["title"]) ?></td>
                <td><?= htmlspecialchars($a["type"]) ?></td>
                <td><?= htmlspecialchars($a["level"]) ?></td>
                <td>
                    <?= (int)$a["chapter_start"] ?>〜<?= (int)$a["chapter_end"] ?>
                </td>
                <td><?= htmlspecialchars($a["due_date"] ?? "-") ?></td>
                <td>
                    <?= (int)$a["total_submission"] ?> siswa
                </td>
                <td>
                    <a class="btn btn-sm btn-primary"
                        href="<?= BASE_URL ?>teacher/assignment-view.php?id=<?= (int)$a["id"] ?>">
                        Lihat & Nilai
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>