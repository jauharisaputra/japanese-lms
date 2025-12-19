<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$user = currentUser();
$pdo = getPDO();

$exam_id = isset($_GET["exam_id"]) ? (int)$_GET["exam_id"] : 0;

$exam_id = (int)($_GET["exam_id"] ?? 0);
if ($exam_id <= 0) {
    die("exam_id tidak valid.");
}

$jsonFile = __DIR__ . "/../data/to_exam_" . $exam_id . ".json";


$data = json_decode(file_get_contents($jsonFile), true);
if (!$data) {
    die("Format JSON soal TO tidak valid.");
}

$sections   = $data["sections"] ?? [];
$exam_title = $data["title"] ?? "Ujian TO";
$page_title = "Ujian TO " . htmlspecialchars($exam_title);

require __DIR__ . "/../includes/header.php";
?>
<div class="card-header">
  <div class="card-title"><?php echo htmlspecialchars($exam_title); ?></div>
</div>


  <p>Ini adalah tampilan awal Ujian TO (mode demo). Jawaban belum dihitung sebagai nilai resmi.</p>

  <form method="post" action="#">
    <?php foreach ($sections as $sectionKey => $questions): ?>
      <?php if (!$questions) continue; ?>
      <h3><?php echo htmlspecialchars($sectionKey); ?></h3>
      <?php foreach ($questions as $qIndex => $q): ?>
        <div style="margin-bottom:16px;">
          <p>
            <strong>No. <?php echo $qIndex + 1; ?></strong>
            <?php echo htmlspecialchars($q["question"]); ?>
          </p>
          <?php foreach ($q["choices"] as $optKey => $optText): ?>
            <label>
              <input type="radio"
                     name="answer[<?php echo htmlspecialchars($sectionKey); ?>][<?php echo (int)$q["id"]; ?>]"
                     value="<?php echo htmlspecialchars($optKey); ?>">
              <?php echo htmlspecialchars($optKey . ") " . $optText); ?>
            </label><br>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>

    <p>
      <button type="submit" disabled>Submit (akan diaktifkan di tahap berikutnya)</button>
    </p>
  </form>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
