<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$pdo = getPDO();

$page_title = "Kelola Tugas";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title  = trim($_POST["title"] ?? "");
    $type   = $_POST["type"] ?? "daily";
    $level  = $_POST["level"] ?? "N5";
    $cs     = (int)($_POST["chapter_start"] ?? 1);
    $ce     = (int)($_POST["chapter_end"] ?? $cs);
    $desc   = trim($_POST["description"] ?? "");
    $due    = trim($_POST["due_date"] ?? "");
    $quiz   = trim($_POST["quiz_json_path"] ?? "");

    if ($title === "") $errors[] = "Judul wajib diisi.";
    if ($cs <= 0 || $ce <= 0 || $ce < $cs) $errors[] = "Rentang bab tidak valid.";

    if (!$errors) {
        $stmt = $pdo->prepare("
          INSERT INTO assignments
            (title, type, level, chapter_start, chapter_end, description, due_date, quiz_json_path)
          VALUES (?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
          $title, $type, $level, $cs, $ce,
          $desc ?: null,
          $due  ?: null,
          $quiz ?: null
        ]);
    }
}

$assignments = $pdo->query("
  SELECT * FROM assignments
  ORDER BY level, type, chapter_start, created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . "/../includes/header.php";
?>
<div class="card">
  <div class="card-header">
    <div class="card-title">Kelola Tugas (harian/mingguan/fukushuu)</div>
  </div>

  <?php if ($errors): ?>
    <ul style="color:#c62828;">
      <?php foreach ($errors as $e): ?>
        <li><?php echo htmlspecialchars($e); ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form method="post" style="margin-bottom:16px;">
    <p>
      <label>Judul tugas<br>
        <input type="text" name="title" required placeholder="Tugas Harian Bab 23">
      </label>
    </p>
    <p>
      <label>Jenis tugas<br>
        <select name="type">
          <option value="daily">Harian per bab</option>
          <option value="weekly">Mingguan (per 4 bab)</option>
          <option value="review">Fukushuu Test</option>
        </select>
      </label>
    </p>
    <p>
      <label>Level<br>
        <select name="level">
          <option value="N5">N5</option>
          <option value="N4">N4</option>
        </select>
      </label>
    </p>
    <p>
      <label>Bab mulai – Bab akhir<br>
        <input type="number" name="chapter_start" value="23" style="width:80px;"> 〜
        <input type="number" name="chapter_end" value="23" style="width:80px;">
      </label>
    </p>
    <p>
      <label>Deadline (opsional)<br>
        <input type="datetime-local" name="due_date">
      </label>
    </p>
    <p>
      <label>Path JSON kuis (khusus fukushuu, opsional)<br>
        <input type="text" name="quiz_json_path" placeholder="data/quizzes/n5_week1.json">
      </label>
    </p>
    <p>
      <label>Deskripsi / instruksi<br>
        <textarea name="description" rows="3" style="width:100%;"></textarea>
      </label>
    </p>
    <p>
      <button type="submit">Tambah tugas</button>
    </p>
  </form>

  <?php if (!$assignments): ?>
    <p>Belum ada tugas.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>ID</th><th>Judul</th><th>Jenis</th><th>Level</th>
        <th>Bab</th><th>Deadline</th>
      </tr>
      <?php foreach ($assignments as $a): ?>
        <tr>
          <td><?php echo (int)$a["id"]; ?></td>
          <td><?php echo htmlspecialchars($a["title"]); ?></td>
          <td><?php echo htmlspecialchars($a["type"]); ?></td>
          <td><?php echo htmlspecialchars($a["level"]); ?></td>
          <td><?php echo (int)$a["chapter_start"]; ?>〜<?php echo (int)$a["chapter_end"]; ?></td>
          <td><?php echo htmlspecialchars($a["due_date"] ?? "-"); ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
