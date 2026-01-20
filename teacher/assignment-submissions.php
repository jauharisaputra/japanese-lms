<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$page_title = "Penilaian Tugas Siswa";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();

/* ==========================
   FILTER
========================== */
$status = $_GET["status"] ?? "all";
$where  = "";

if ($status === "ungraded") {
    $where = "AND s.score IS NULL";
} elseif ($status === "graded") {
    $where = "AND s.score IS NOT NULL";
} elseif ($status === "late") {
    $where = "AND a.due_date IS NOT NULL AND s.submitted_at > a.due_date";
}

/* ==========================
   DATA SUBMISSION
========================== */
$stmt = $pdo->prepare("
    SELECT 
        s.id AS submission_id,
        u.full_name,
        a.title AS assignment_title,
        s.submitted_at,
        a.due_date,
        s.score,
        (
            SELECT COUNT(*) 
            FROM assignment_files af 
            WHERE af.submission_id = s.id
        ) AS file_count
    FROM assignment_submissions s
    JOIN users u       ON s.user_id = u.id
    JOIN assignments a ON s.assignment_id = a.id
    WHERE 1=1 $where
    ORDER BY s.submitted_at DESC
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">📝 Penilaian Tugas Siswa</div>
    </div>

    <!-- FILTER -->
    <div style="margin-bottom:12px;">
        <a class="btn btn-sm <?= $status=='all'?'btn-primary':'btn-outline-primary' ?>" href="?status=all">Semua</a>
        <a class="btn btn-sm <?= $status=='ungraded'?'btn-danger':'btn-outline-danger' ?>" href="?status=ungraded">Belum
            Dinilai</a>
        <a class="btn btn-sm <?= $status=='graded'?'btn-success':'btn-outline-success' ?>" href="?status=graded">Sudah
            Dinilai</a>
        <a class="btn btn-sm <?= $status=='late'?'btn-warning':'btn-outline-warning' ?>"
            href="?status=late">Terlambat</a>
    </div>

    <?php if (!$rows): ?>
    <p>Tidak ada data tugas.</p>
    <?php else: ?>
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Siswa</th>
                <th>Tugas</th>
                <th>File</th>
                <th>Submit</th>
                <th>Deadline</th>
                <th>Nilai</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r):
            if ($r["score"] === null) {
                $rowStyle = "background:#fdecea";
            } elseif ($r["due_date"] && $r["submitted_at"] > $r["due_date"]) {
                $rowStyle = "background:#fff8e1";
            } else {
                $rowStyle = "background:#e8f5e9";
            }
        ?>
            <tr style="<?= $rowStyle ?>">
                <td><?= htmlspecialchars($r["full_name"]) ?></td>
                <td><?= htmlspecialchars($r["assignment_title"]) ?></td>
                <td>
                    <?= $r["file_count"] > 0 ? "📎 {$r['file_count']} file" : "-" ?>
                </td>
                <td><?= htmlspecialchars($r["submitted_at"]) ?></td>
                <td><?= htmlspecialchars($r["due_date"] ?? "-") ?></td>
                <td><?= $r["score"] !== null ? (float)$r["score"] : "—" ?></td>
                <td>
                    <a class="btn btn-sm btn-outline-primary"
                        href="<?= BASE_URL ?>teacher/assignment-view.php?id=<?= (int)$r["submission_id"] ?>">
                        Nilai
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>