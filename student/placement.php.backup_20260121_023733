<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$pdo = getPDO();
$user = currentUser();
$page_title = "Ujian TO";
require __DIR__ . "/../includes/header.php";

$stmt = $pdo->prepare("SELECT * FROM placement_exams WHERE level = ? ORDER BY id LIMIT 1");
$stmt->execute([$user["level"]]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<div class="card">
  <div class="card-header">
    <div class="card-title">Ujian TO Level <?php echo htmlspecialchars($user["level"]); ?></div>
  </div>

  <?php if (!$exam): ?>
    <p>Belum ada ujian TO untuk level ini.</p>
  <?php else: ?>
    <p>Nama ujian: <?php echo htmlspecialchars($exam["name"]); ?></p>
    <p>Skor maksimum: <?php echo (int)$exam["max_score"]; ?>, Passing score: <?php echo (int)$exam["pass_score"]; ?></p>
    <p>
      <a class="button"
         href="<?php echo BASE_URL; ?>student/placement-do.php?exam_id=<?php echo (int)$exam["id"]; ?>">
         Mulai Ujian TO (demo)
      </a>
    </p>
  <?php endif; ?>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
