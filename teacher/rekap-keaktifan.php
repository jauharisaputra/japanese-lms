<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";
requireRole(["teacher","admin"]);
$page_title = "Rekap Keaktifan Siswa";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();
$stmt = $pdo->query("
    SELECT 
        u.username, u.full_name, u.level,
        COUNT(a.id) AS total_aktivitas
    FROM users u 
    LEFT JOIN student_activity_logs a ON u.id = a.user_id 
    WHERE u.role = 'student'
    GROUP BY u.id 
    ORDER BY total_aktivitas DESC
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
                    <th>Siswa</th><th>Level</th><th>Aktivitas</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): 
                $count = (int)($r["total_aktivitas"] ?? 0);
                $status = $count >= 10 ? "🟢 Aktif" : ($count > 0 ? "🟡 Cukup" : "🔴 Belum");
                $name = $r["full_name"] ?: $r["username"];
            ?>
                <tr>
                    <td><?= htmlspecialchars($name) ?></td>
                    <td><?= htmlspecialchars($r["level"] ?? "N5") ?></td>
                    <td><strong><?= $count ?></strong></td>
                    <td><?= $status ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="4" class="text-center text-muted">Belum ada data siswa.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
