<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/../includes/placement.php";

requireRole(["teacher","admin"]);
$pdo = getPDO();

$exam_id = isset($_GET["exam_id"]) ? (int)$_GET["exam_id"] : 0;
$stmt = $pdo->prepare("SELECT * FROM placement_exams WHERE id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$exam) die("Ujian TO tidak ditemukan.");

$sections = json_decode($exam["sections_json"], true) ?: [];

$stmt = $pdo->prepare("
  SELECT pa.*, u.full_name, u.username
  FROM placement_attempts pa
  JOIN users u ON pa.user_id = u.id
  WHERE pa.exam_id = ?
  ORDER BY u.full_name, pa.attempt_no
");
$stmt->execute([$exam_id]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Hasil Ujian TO";
require __DIR__ . "/../includes/header.php";
?>
<div class="card">
  <div class="card-header">
    <div class="card-title">Hasil Ujian TO: <?php echo htmlspecialchars($exam["name"]); ?></div>
  </div>

  <?php if (!$attempts): ?>
    <p>Belum ada attempt.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>Nama siswa</th>
        <th>Attempt</th>
        <th>Total</th>
        <th>Status</th>
        <?php foreach ($sections as $key => $max): ?>
          <th><?php echo htmlspecialchars($key); ?> (max <?php echo (int)$max; ?>)</th>
        <?php endforeach; ?>
      </tr>
      <?php foreach ($attempts as $a): 
        $raw = json_decode($a["section_scores_json"], true) ?: [];
      ?>
        <tr>
          <td><?php echo htmlspecialchars($a["full_name"] ?? $a["username"]); ?></td>
          <td><?php echo (int)$a["attempt_no"]; ?></td>
          <td><?php echo (int)$a["total_score"]; ?></td>
          <td><?php echo $a["passed"] ? "Lulus" : "Belum lulus"; ?></td>
          <?php foreach ($sections as $key => $max): ?>
            <td><?php echo isset($raw[$key]) ? (int)$raw[$key] : 0; ?></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
