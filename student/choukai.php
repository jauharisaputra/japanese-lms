<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['student']);
$user = currentUser();
$page_title = "choukai";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();
$level = $user["level"] ?? "N5";

// Ambil daftar choukai sesuai level siswa
$stmt = $pdo->prepare("
    SELECT id, title, chapter_start, chapter_end
    FROM choukai
    WHERE level = ?
    ORDER BY chapter_start ASC
");
$stmt->execute([$level]);
$choukaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pilih choukai berdasarkan dropdown atau default pertama
$choukai_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($choukai_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM choukai WHERE id = ?");
    $stmt->execute([$choukai_id]);
} else {
    $stmt = $pdo->prepare("
        SELECT *
        FROM choukai
        WHERE level = ?
        ORDER BY chapter_start ASC
        LIMIT 1
    ");
    $stmt->execute([$level]);
}
$choukai = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">📖 choukai</div>
    </div>

    <?php if (!$choukai): ?>
    <p>Belum ada choukai untuk level Anda.</p>
    <?php else: ?>

    <!-- Dropdown pilih choukai -->
    <form method="get" style="margin-bottom:16px;">
        <label>Pilih choukai:
            <select name="id" onchange="this.form.submit()">
                <?php foreach ($choukaiList as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $d['id'] == $choukai['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars("Bab {$d['chapter_start']}–{$d['chapter_end']} - {$d['title']}") ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <h3><?= htmlspecialchars($choukai["title"]) ?></h3>
    <p>Bab <?= $choukai["chapter_start"] ?> – <?= $choukai["chapter_end"] ?></p>

    <!-- PDF responsive + tombol buka baru -->
    <?php if (!empty($choukai["file_path"])): ?>
    <div style="position:relative; padding-bottom:56.25%; height:0; overflow:hidden; margin-bottom:12px;">
        <iframe src="<?= BASE_URL . $choukai["file_path"] ?>"
            style="position:absolute; top:0; left:0; width:100%; height:100%; border:1px solid #ccc;"
            allowfullscreen></iframe>
    </div>
    <p>
        <a href="<?= BASE_URL . $choukai["file_path"] ?>" target="_blank" class="button">Buka PDF di Tab Baru</a>
    </p>
    <?php endif; ?>

    <?php
    // Ambil soal dari DB
    $stmt = $pdo->prepare("SELECT * FROM choukai_questions WHERE choukai_id = ? ORDER BY id");
    $stmt->execute([$choukai['id']]);
    $dbQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Bagi per section
    $questionsBySection = [];
    foreach ($dbQuestions as $q) {
        $section = $q['section'] ?? 'Bagian 1';
        $questionsBySection[$section][] = [
            'id' => $q['id'],
            'question' => $q['question'],
            'options' => json_decode($q['options'], true)
        ];
    }

    $timePerQuestion = 120; // detik
    $totalQuestions = count($dbQuestions);
    $timeLimit = $totalQuestions * $timePerQuestion;
    ?>

    <?php if (!$dbQuestions): ?>
    <p>Soal choukai belum tersedia.</p>
    <?php else: ?>

    <!-- Timer -->
    <div style="font-weight:bold;color:#c00;margin-bottom:12px;">
        ⏱ Waktu: <span id="time"></span>
    </div>

    <!-- Form soal -->
    <form method="post" action="choukai-submit.php" id="choukaiForm">
        <input type="hidden" name="choukai_id" value="<?= $choukai["id"] ?>">

        <?php $globalIndex=1; foreach ($questionsBySection as $section => $qs): ?>
        <h4 style="margin-top:20px; color:#006;"><?= htmlspecialchars($section) ?></h4>
        <?php foreach ($qs as $q): ?>
        <div style="margin-bottom:18px;">
            <strong><?= $globalIndex ?>. <?= htmlspecialchars($q["question"]) ?></strong>
            <?php foreach ($q["options"] as $idx => $opt): ?>
            <div>
                <label>
                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $idx ?>">
                    <?= htmlspecialchars($opt) ?>
                </label>
            </div>
            <?php endforeach; ?>
        </div>
        <?php $globalIndex++; endforeach; ?>
        <?php endforeach; ?>

        <button type="submit">Kumpulkan Jawaban</button>
    </form>

    <script>
    let timeLeft = <?= $timeLimit ?>;

    function startTimer() {
        const el = document.getElementById("time");
        const timer = setInterval(() => {
            let m = Math.floor(timeLeft / 60);
            let s = timeLeft % 60;
            el.textContent = `${m}:${s.toString().padStart(2,'0')}`;
            if (timeLeft <= 0) {
                clearInterval(timer);
                alert("Waktu habis! Jawaban dikirim.");
                document.getElementById("choukaiForm").submit();
            }
            timeLeft--;
        }, 1000);
    }
    startTimer();
    </script>

    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>
