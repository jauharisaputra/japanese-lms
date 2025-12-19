<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$pdo = getPDO();
$user = currentUser();
$page_title = "Riwayat Nilai";

// Ringkasan nilai per level (rata-rata)
$stmt = $pdo->prepare("
  SELECT q.level,
         AVG(qa.score) AS avg_quiz
  FROM quiz_attempts qa
  JOIN quizzes q ON qa.quiz_id = q.id
  WHERE qa.user_id = ?
  GROUP BY q.level
");
$stmt->execute([$user["id"]]);
$quiz_summary = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // level => avg_quiz

$stmt = $pdo->prepare("
  SELECT a.level,
         AVG(CASE WHEN a.type = 'daily'  THEN s.score END) AS avg_daily,
         AVG(CASE WHEN a.type = 'weekly' THEN s.score END) AS avg_weekly,
         AVG(CASE WHEN a.type = 'review' THEN s.score END) AS avg_review,
         AVG(s.kaiwa_score) AS avg_kaiwa
  FROM assignment_submissions s
  JOIN assignments a ON s.assignment_id = a.id
  WHERE s.user_id = ?
  GROUP BY a.level
");
$stmt->execute([$user["id"]]);
$assign_summary = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $assign_summary[$row["level"]] = $row;
}

// Detail nilai kuis
$stmt = $pdo->prepare("
  SELECT qa.*, q.title AS quiz_title, q.level
  FROM quiz_attempts qa
  JOIN quizzes q ON qa.quiz_id = q.id
  WHERE qa.user_id = ?
  ORDER BY q.level, qa.id DESC
");
$stmt->execute([$user["id"]]);
$quiz_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Detail nilai tugas
$stmt = $pdo->prepare("
  SELECT s.*, a.title AS assignment_title, a.type, a.level, a.chapter_start, a.chapter_end
  FROM assignment_submissions s
  JOIN assignments a ON s.assignment_id = a.id
  WHERE s.user_id = ?
  ORDER BY a.level, a.chapter_start, s.submitted_at DESC
");
$stmt->execute([$user["id"]]);
$task_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . "/../includes/header.php";
?>
<div class="card">
  <div class="card-header">
    <div class="card-title">Riwayat Nilai</div>
  </div>

  <h3>Ringkasan Nilai per Level</h3>
  <?php if (!$quiz_summary && !$assign_summary): ?>
    <p>Belum ada data nilai untuk diringkas.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>Level</th>
        <th>Rata-rata kuis</th>
        <th>Rata-rata tugas harian</th>
        <th>Rata-rata tugas mingguan</th>
        <th>Rata-rata fukushuu</th>
        <th>Rata-rata kaiwa</th>
      </tr>
      <?php
      $levels = array_unique(array_merge(
        array_keys($quiz_summary),
        array_keys($assign_summary)
      ));
      sort($levels);
      foreach ($levels as $lv):
        $q  = $quiz_summary[$lv] ?? null;
        $as = $assign_summary[$lv] ?? null;
      ?>
        <tr>
          <td><?php echo htmlspecialchars($lv); ?></td>
          <td><?php echo $q !== null ? round($q, 1) : "-"; ?></td>
          <td><?php echo $as && $as["avg_daily"]  !== null ? round($as["avg_daily"], 1)  : "-"; ?></td>
          <td><?php echo $as && $as["avg_weekly"] !== null ? round($as["avg_weekly"], 1) : "-"; ?></td>
          <td><?php echo $as && $as["avg_review"] !== null ? round($as["avg_review"], 1) : "-"; ?></td>
          <td><?php echo $as && $as["avg_kaiwa"]  !== null ? round($as["avg_kaiwa"], 1)  : "-"; ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <h3 style="margin-top:24px;">Nilai Kuis</h3>
  <?php if (!$quiz_rows): ?>
    <p>Belum ada nilai kuis.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>Kuis</th>
        <th>Level</th>
        <th>Skor</th>
      </tr>
      <?php foreach ($quiz_rows as $r): ?>
        <tr>
          <td><?php echo htmlspecialchars($r["quiz_title"]); ?></td>
          <td><?php echo htmlspecialchars($r["level"]); ?></td>
          <td><?php echo (float)$r["score"]; ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <h3 style="margin-top:24px;">Nilai Tugas &amp; Kaiwa</h3>
  <?php if (!$task_rows): ?>
    <p>Belum ada nilai tugas.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>Tugas</th>
        <th>Jenis</th>
        <th>Level</th>
        <th>Bab</th>
        <th>Nilai</th>
        <th>Nilai kaiwa</th>
        <th>Tanggal</th>
      </tr>
      <?php foreach ($task_rows as $r): ?>
        <tr>
          <td><?php echo htmlspecialchars($r["assignment_title"]); ?></td>
          <td><?php echo htmlspecialchars($r["type"]); ?></td>
          <td><?php echo htmlspecialchars($r["level"]); ?></td>
          <td><?php echo (int)$r["chapter_start"]; ?>〜<?php echo (int)$r["chapter_end"]; ?></td>
          <td><?php echo $r["score"] !== null ? (float)$r["score"] : "-"; ?></td>
          <td><?php echo $r["kaiwa_score"] !== null ? (float)$r["kaiwa_score"] : "-"; ?></td>
          <td><?php echo htmlspecialchars($r["submitted_at"]); ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
