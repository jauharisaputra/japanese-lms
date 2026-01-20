<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$page_title = "Rekap Keaktifan Siswa";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();

/*
 |--------------------------------------------------------
 | Query Rekap Keaktifan Siswa
 |--------------------------------------------------------
 */
$stmt = $pdo->query("
    SELECT 
        u.full_name,

        SUM(CASE WHEN a.activity_type = 'login' THEN 1 ELSE 0 END) AS login_count,
        SUM(CASE WHEN a.activity_type = 'lesson_open' THEN 1 ELSE 0 END) AS lesson_count,
        SUM(CASE WHEN a.activity_type = 'quiz_attempt' THEN 1 ELSE 0 END) AS quiz_count,
        SUM(CASE WHEN a.activity_type = 'assignment_submit' THEN 1 ELSE 0 END) AS assignment_count,

        COUNT(a.id) AS total_aktivitas,

        (
            SUM(CASE WHEN a.activity_type = 'login' THEN 1 ELSE 0 END) * 1 +
            SUM(CASE WHEN a.activity_type = 'lesson_open' THEN 1 ELSE 0 END) * 1 +
            SUM(CASE WHEN a.activity_type = 'quiz_attempt' THEN 1 ELSE 0 END) * 3 +
            SUM(CASE WHEN a.activity_type = 'assignment_submit' THEN 1 ELSE 0 END) * 5
        ) AS total_point

    FROM users u
    LEFT JOIN activity_logs a ON u.id = a.user_id
    WHERE u.role = 'student'
    GROUP BY u.id
    ORDER BY total_point DESC
");

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">📊 Rekap Keaktifan Siswa</div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Siswa</th>
                    <th>Total Aktivitas</th>
                    <th>Total Poin</th>
                    <th>Nilai Keaktifan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): 
                    $totalPoint = (int)($r["total_point"] ?? 0);

                    if ($totalPoint >= 80)      $nilai = "A";
                    elseif ($totalPoint >= 60)  $nilai = "B";
                    elseif ($totalPoint >= 40)  $nilai = "C";
                    else                        $nilai = "D";
                ?>
                <tr>
                    <td><?= htmlspecialchars($r["full_name"]) ?></td>
                    <td><?= (int)($r["total_aktivitas"] ?? 0) ?></td>
                    <td><?= $totalPoint ?></td>
                    <td><strong><?= $nilai ?></strong></td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        Belum ada data aktivitas siswa.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>