<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$user = currentUser();
$page_title = "Dokkai";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();
$level = $user["level"] ?? "N5";

/* Ambil daftar dokkai sesuai level siswa */
$stmt = $pdo->prepare("
    SELECT id, title, chapter_start, chapter_end
    FROM dokkai
    WHERE level = ?
    ORDER BY chapter_start ASC
");
$stmt->execute([$level]);
$dokkaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Pilih dokkai berdasarkan dropdown atau default dokkai pertama */
$dokkai_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($dokkai_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM dokkai WHERE id = ?");
    $stmt->execute([$dokkai_id]);
} else {
    $stmt = $pdo->prepare("
        SELECT *
        FROM dokkai
        WHERE level = ?
        ORDER BY chapter_start ASC
        LIMIT 1
    ");
    $stmt->execute([$level]);
}

$dokkai = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">📖 Dokkai</div>
    </div>

    <?php if (!$dokkai): ?>
    <p>Belum ada dokkai untuk level Anda.</p>
    <?php else: ?>

    <!-- Dropdown pilih dokkai -->
    <form method="get" style="margin-bottom:16px;">
        <label>Pilih Dokkai:
            <select name="id" onchange="this.form.submit()">
                <?php foreach ($dokkaiList as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $d['id'] == $dokkai['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars("Bab {$d['chapter_start']}–{$d['chapter_end']} - {$d['title']}") ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <h3><?= htmlspecialchars($dokkai["title"]) ?></h3>
    <p>Bab <?= $dokkai["chapter_start"] ?> – <?= $dokkai["chapter_end"] ?></p>

    <!-- PDF dokkai responsive + tombol buka baru -->
    <?php if (!empty($dokkai["file_path"])): ?>
    <embed src="<?= BASE_URL . $dokkai["file_path"] ?>" type="application/pdf" width="100%" height="600px"
        style="border:1px solid #ccc; margin-bottom:12px;">
    <p>
        <a href="<?= BASE_URL . $dokkai["file_path"] ?>" target="_blank" class="button"
            style="display:inline-block;margin-top:6px;">
            Buka PDF di Tab Baru
        </a>
    </p>
    <?php endif; ?>

    <?php
    /* Ambil soal dari database */
    $stmt = $pdo->prepare("SELECT * FROM dokkai_questions WHERE dokkai_id = ? ORDER BY id");
    $stmt->execute([$dokkai['id']]);
    $dbQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Bagi soal per section
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
    <p>Soal dokkai belum tersedia.</p>
    <?php else: ?>

    <!-- TIMER -->
    <div style="font-weight:bold;color:#c00;margin-bottom:12px;">
        ⏱ Waktu: <span id="time"></span>
    </div>

    <!-- FORM SOAL -->
    <form method="post" action="dokkai-submit.php" id="dokkaiForm">
        <input type="hidden" name="dokkai_id" value="<?= $dokkai["id"] ?>">

        <?php foreach ($questionsBySection as $section => $qs): ?>
        <!-- Judul section -->
        <h4 style="margin-top:20px; color:#006;"><?= htmlspecialchars($section) ?></h4>

        <?php foreach ($qs as $i => $q): ?>
        <div style="margin-bottom:18px;">
            <strong><?= ($i+1) ?>. <?= htmlspecialchars($q["question"]) ?></strong>
            <?php foreach ($q["options"] as $idx => $opt): ?>
            <div>
                <label>
                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $idx ?>">
                    <?= htmlspecialchars($opt) ?>
                </label>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
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
                document.getElementById("dokkaiForm").submit();
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