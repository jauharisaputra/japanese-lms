<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$pdo = getPDO();

$assignment_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$stmt = $pdo->prepare("
  SELECT a.*, u.full_name, s.id AS sub_id, s.score, s.kaiwa_score, s.submitted_at, s.comment
  FROM assignment_submissions s
  JOIN assignments a ON s.assignment_id = a.id
  JOIN users u ON s.user_id = u.id
  WHERE a.id = ?
  ORDER BY s.submitted_at DESC
");
$stmt->execute([$assignment_id]);
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Penilaian Tugas";
require __DIR__ . "/../includes/header.php";
?>
<div class="card">
  <div class="card-header">
    <div class="card-title">Penilaian Tugas ID <?php echo (int)$assignment_id; ?></div>
  </div>

  <?php if (!$subs): ?>
    <p>Belum ada submission.</p>
  <?php else: ?>
    <?php foreach ($subs as $s): ?>
      <div style="border-bottom:1px solid #ddd; padding:8px 0;">
        <p>
          <strong><?php echo htmlspecialchars($s["full_name"]); ?></strong>
          (<?php echo htmlspecialchars($s["submitted_at"]); ?>)
        </p>
        <p>
          Nilai umum:
          <form method="post" action="<?php echo BASE_URL; ?>teacher/grade-assignment.php">
            <input type="hidden" name="submission_id" value="<?php echo (int)$s["sub_id"]; ?>">
            <input type="number" name="score" step="0.1" value="<?php echo (float)$s["score"]; ?>" style="width:80px;">
            Kaiwa:
            <input type="number" name="kaiwa_score" step="0.1" value="<?php echo (float)$s["kaiwa_score"]; ?>" style="width:80px;">
            <br>
            <textarea name="comment" rows="2" style="width:100%;"><?php echo htmlspecialchars($s["comment"]); ?></textarea>
            <button type="submit">Simpan nilai</button>
          </form>
        </p>
        <p>File:</p>
        <ul>
          <?php
          $fs = $pdo->prepare("SELECT * FROM assignment_files WHERE submission_id = ?");
          $fs->execute([$s["sub_id"]]);
          foreach ($fs as $f):
          ?>
            <li>
              <a href="<?php echo BASE_URL . htmlspecialchars($f["file_path"]); ?>" target="_blank">
                <?php echo htmlspecialchars($f["file_type"]); ?> - <?php echo basename($f["file_path"]); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
