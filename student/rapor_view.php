<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
global $pdo;

$user = getCurrentUser(); // asumsi sudah ada helper
$student_id = $user["student_id"] ?? $user["id"];

$stmt = $pdo->prepare("SELECT * FROM rapor_n5 WHERE student_id = :sid ORDER BY tanggal DESC");
$stmt->execute([":sid" => $student_id]);
$rapors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require __DIR__ . "/../includes/header.php"; ?>
<div class="container">
    <h2>Rapor N5</h2>

    <?php if (!$rapors): ?>
        <p>Belum ada rapor.</p>
    <?php else: ?>
        <table border="1" cellpadding="4" cellspacing="0">
            <tr>
                <th>Tanggal</th>
                <th>Kelas</th>
                <th>Total Nilai</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($rapors as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r["tanggal"]); ?></td>
                    <td><?php echo htmlspecialchars($r["kelas"]); ?></td>
                    <td><?php echo (int)$r["total_nilai"]; ?></td>
                    <td><?php echo htmlspecialchars($r["status_lulus"]); ?></td>
                    <td>
                        <a href="../teacher/rapor_pdf.php?id=<?php echo (int)$r["id"]; ?>" target="_blank">
                            Download PDF
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
