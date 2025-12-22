<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$page_title = "Daftar Rapor N5";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();

$stmt = $pdo->query("SELECT * FROM rapor_n5 ORDER BY tanggal DESC, id DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Daftar Rapor N5</div>
    </div>
    <div class="card-body">
        <?php if (!$rows): ?>
            <p>Belum ada data rapor.</p>
        <?php else: ?>
            <table border="1" cellpadding="4" cellspacing="0">
                <tr>
                    <th>ID</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?php echo (int)$r["id"]; ?></td>
                        <td><?php echo htmlspecialchars($r["student_id"]); ?></td>
                        <td><?php echo htmlspecialchars($r["student_name"]); ?></td>
                        <td><?php echo htmlspecialchars($r["kelas"]); ?></td>
                        <td><?php echo htmlspecialchars($r["tanggal"]); ?></td>
                        <td><?php echo (int)$r["total_nilai"]; ?></td>
                        <td><?php echo htmlspecialchars($r["status_lulus"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
