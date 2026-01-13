<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['student']);
$pdo = getPDO();

$choukai_id = (int)($_GET['id'] ?? 0);
if (!$choukai_id) die("Choukai tidak valid");

// Ambil choukai
$stmt = $pdo->prepare("SELECT * FROM choukai WHERE id=?");
$stmt->execute([$choukai_id]);
$choukai = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$choukai) die("Data choukai tidak ditemukan");

// Ambil soal
$q = $pdo->prepare("
    SELECT * FROM choukai_questions 
    WHERE choukai_id=? 
    ORDER BY question_number ASC
");
$q->execute([$choukai_id]);
$questions = $q->fetchAll(PDO::FETCH_ASSOC);

$timeLimit = $choukai['time_limit'] * 60; // menit → detik
?>

<h3>🎧 Tes Choukai</h3>

<p><b><?= htmlspecialchars($choukai['title']) ?></b></p>
<p>Level: <?= $choukai['level'] ?> | Bab: <?= $choukai['bab'] ?></p>

<!-- TIMER -->
<div style="font-size:18px;color:red">
    ⏱ Waktu: <span id="timer"></span>
</div>

<!-- AUDIO -->
<audio controls controlsList="nodownload">
    <source src="<?= BASE_URL ?>uploads/choukai/<?= $choukai['audio_file'] ?>">
</audio>

<hr>

<form id="choukaiForm" method="post" action="choukai-submit.php">
    <input type="hidden" name="choukai_id" value="<?= $choukai_id ?>">

    <?php foreach ($questions as $i => $q): ?>
    <div style="margin-bottom:16px">
        <p><b><?= $i+1 ?>.</b> <?= htmlspecialchars($q['question']) ?></p>

        <?php foreach (['a','b','c','d'] as $opt): ?>
        <label>
            <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt ?>">
            <?= htmlspecialchars($q['option_'.$opt]) ?>
        </label><br>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <button type="submit">Kirim Jawaban</button>
</form>

<!-- TIMER SCRIPT -->
<script>
let timeLeft = <?= $timeLimit ?>;
const timerEl = document.getElementById('timer');

function tick() {
    let m = Math.floor(timeLeft / 60);
    let s = timeLeft % 60;
    timerEl.textContent = m + ':' + String(s).padStart(2, '0');

    if (timeLeft <= 0) {
        alert("Waktu habis! Jawaban dikirim otomatis.");
        document.getElementById('choukaiForm').submit();
    }
    timeLeft--;
}

tick();
setInterval(tick, 1000);

// Anti refresh sederhana
window.onbeforeunload = () => "Tes sedang berlangsung";
</script>