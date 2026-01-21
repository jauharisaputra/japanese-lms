<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);

// gunakan fungsi yang sudah ada di proyek
$user = currentUser();
$pdo  = getPDO();

// asumsi kolom identitas siswa sama seperti yang dipakai fitur lain
$student_id = $user["student_id"] ?? $user["username"];

$stmt = $pdo->prepare("SELECT * FROM rapor_n5 WHERE student_id = ? ORDER BY tanggal DESC, id DESC");
$stmt->execute([$student_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Rapor N5";
require __DIR__ . "/../includes/header.php";
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Rapor N5</div>
    </div>
    <div class="card-body">
        <?php if (!$rows): ?>
            <p>Belum ada rapor.</p>
        <?php else: ?>
            <table border="1" cellpadding="4" cellspacing="0">
                <tr>
                    <th>Tanggal</th>
                    <th>Kelas</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r["tanggal"]); ?></td>
                        <td><?php echo htmlspecialchars($r["kelas"]); ?></td>
                        <td><?php echo (int)$r["total_nilai"]; ?></td>
                        <td><?php echo htmlspecialchars($r["status_lulus"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
