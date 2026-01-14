<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['student']);
$user = currentUser();
$page_title = "Choukai";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();
$level = $user["level"] ?? "N5";

// Ambil daftar choukai sesuai level siswa
$stmt = $pdo->prepare("
    SELECT id, title, chapter_start, chapter_end, file_path, audio_path
    FROM choukai
    WHERE level = ?
    ORDER BY chapter_start ASC
");
$stmt->execute([$level]);
$choukaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pilih choukai berdasarkan GET id atau default pertama
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
        <div class="card-title">🎧 Choukai</div>
    </div>

    <?php if (!$choukai): ?>
    <p>Belum ada choukai untuk level Anda.</p>
    <?php else: ?>

    <!-- Dropdown pilih choukai -->
    <form method="get" style="margin-bottom:16px;">
        <label>Pilih Choukai:
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

    <!-- Audio -->
    <?php if (!empty($choukai["audio_path"])): ?>
    <div style="margin-bottom:16px;">
        <audio controls id="audioPlayer">
            <source src="<?= BASE_URL . $choukai["audio_path"] ?>" type="audio/mpeg">
            Browser Anda tidak mendukung pemutar audio.
        </audio>
    </div>
    <?php endif; ?>

    <!-- PDF -->
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

    <!-- Timer -->
    <div id="time" style="font-weight:bold;color:#c00;margin-bottom:12px;">⏱ Waktu: 10:00</div>

    <!-- Form jawaban fleksibel -->
    <form method="post" action="<?= BASE_URL ?>student/choukai-submit.php" id="choukaiForm">
        <input type="hidden" name="choukai_id" value="<?= $choukai["id"] ?>">

        <!-- Untuk audio-only, kita tampilkan 5-10 field jawaban -->
        <?php for($i=1;$i<=10;$i++): ?>
        <div style="margin-bottom:16px;">
            <strong>Jawaban <?= $i ?></strong>
            <div style="margin-top:6px;">
                <!-- Angka 1–10 -->
                <label>Angka 1–10:
                    <select name="answers[<?= $i ?>][number]">
                        <?php for($n=1;$n<=10;$n++): ?>
                        <option value="<?= $n ?>"><?= $n ?></option>
                        <?php endfor; ?>
                    </select>
                </label><br>

                <!-- Huruf A–Z -->
                <label>Huruf A–Z:
                    <select name="answers[<?= $i ?>][letter]">
                        <?php foreach(range('A','Z') as $l): ?>
                        <option value="<?= $l ?>"><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </label><br>

                <!-- O/X -->
                <label>O/X:
                    <select name="answers[<?= $i ?>][ox]">
                        <option value="O">O</option>
                        <option value="X">X</option>
                    </select>
                </label><br>

                <!-- Jawaban singkat -->
                <input type="text" name="answers[<?= $i ?>][text]" style="width:80%;"
                    placeholder="Jawaban singkat / kalimat">
            </div>
        </div>
        <?php endfor; ?>

        <button type="submit">Kumpulkan Jawaban</button>
    </form>

    <!-- Timer JS -->
    <script>
    let timeLeft = 600; // 10 menit
    const el = document.getElementById('time');
    const audio = document.getElementById('audioPlayer');
    let timerStarted = false;

    function startTimer() {
        if (timerStarted) return;
        timerStarted = true;
        const timer = setInterval(() => {
            let m = Math.floor(timeLeft / 60);
            let s = timeLeft % 60;
            el.textContent = `⏱ Waktu: ${m}:${s.toString().padStart(2,'0')}`;
            if (timeLeft <= 0) {
                clearInterval(timer);
                alert("Waktu habis! Jawaban dikirim otomatis.");
                document.getElementById('choukaiForm').submit();
            }
            timeLeft--;
        }, 1000);
    }

    if (audio) audio.addEventListener('play', startTimer);
    else startTimer();
    </script>

    <?php endif; ?>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>