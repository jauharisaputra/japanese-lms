<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$pdo = getPDO();

$user = currentUser();

$stmt = $pdo->prepare("
  SELECT a.*, s.id AS submission_id, s.submitted_at, s.score, s.kaiwa_score
  FROM assignments a
  LEFT JOIN assignment_submissions s
    ON s.assignment_id = a.id AND s.user_id = ?
  WHERE a.level = ?
  ORDER BY a.chapter_start, a.type
");
$stmt->execute([$user["id"], $user["level"]]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Tugas";
require __DIR__ . "/../includes/header.php";
?>
<div class="card">
  <div class="card-header">
    <div class="card-title">Daftar Tugas</div>
  </div>
  <?php if (!$assignments): ?>
    <p>Belum ada tugas untuk level ini.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>Judul</th><th>Jenis</th><th>Bab</th><th>Deadline</th><th>Status</th><th>Aksi</th>
      </tr>
      <?php foreach ($assignments as $a): ?>
        <tr>
          <td><?php echo htmlspecialchars($a["title"]); ?></td>
          <td><?php echo htmlspecialchars($a["type"]); ?></td>
          <td><?php echo (int)$a["chapter_start"]; ?>〜<?php echo (int)$a["chapter_end"]; ?></td>
          <td><?php echo htmlspecialchars($a["due_date"] ?? "-"); ?></td>
          <td>
            <?php if ($a["submission_id"]): ?>
              Sudah kirim (nilai: <?php echo (float)$a["score"]; ?>, kaiwa: <?php echo (float)$a["kaiwa_score"]; ?>)
            <?php else: ?>
              Belum kirim
            <?php endif; ?>
          </td>
          <td>
            <a href="<?php echo BASE_URL; ?>student/assignment-submit.php?id=<?php echo (int)$a["id"]; ?>">
              <?php echo $a["submission_id"] ? "Kirim ulang" : "Kirim tugas"; ?>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
