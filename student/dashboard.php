<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$user = currentUser();
$page_title = "Dashboard Siswa";
require __DIR__ . "/../includes/header.php";
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Dashboard Siswa</div>
    </div>
    <p>Halo, <?php echo htmlspecialchars($user["full_name"] ?? $user["username"]); ?> (Level
        <?php echo htmlspecialchars($user["level"]); ?>).</p>
    <ul>
        <li>Materi selesai: <?php echo (int)($user["lessons_completed"] ?? 0); ?> /
            <?php echo (int)($user["lessons_total"] ?? 0); ?></li>
        <li>Kuis lulus: <?php echo (int)($user["quizzes_passed"] ?? 0); ?> /
            <?php echo (int)($user["quizzes_total"] ?? 0); ?></li>
    </ul>
    <p>
        <a href="<?php echo BASE_URL; ?>student/lessons.php">Lanjut materi</a> |
        <a href="<?php echo BASE_URL; ?>student/quizzes.php">Lanjut kuis</a>
        | <a href="<?php echo BASE_URL; ?>student/dokkai.php">📖 Dokkai</a>
        |
        <a href="<?php echo BASE_URL; ?>student/scores.php">Lihat riwayat nilai</a> |
        <a href="<?php echo BASE_URL; ?>student/assignments.php">Tugas &amp; Fukushuu</a> |
        <a href="<?php echo BASE_URL; ?>student/rapor_view.php">📜 Rapor N5</a>
        <a href="<?php echo BASE_URL; ?>student/kanji_write.php">Belajar Menulis</a>
        <a href="<?php echo BASE_URL; ?>student/kanji_write.php?tab=kanji&level=N5">Kanji N5</a> |
        <a href="<?php echo BASE_URL; ?>student/kanji_write.php?tab=kanji&level=N4">Kanji N4</a>
        <a href="<?php echo BASE_URL; ?>student/quiz_hiragana.php">Kuis Hiragana</a> |
        <a href="<?php echo BASE_URL; ?>student/quiz_katakana.php">Kuis Katakana</a> |
        <a href="<?php echo BASE_URL; ?>student/quiz_kanji_n5.php">Kuis Kanji N5</a> |
        <a href="<?php echo BASE_URL; ?>student/quiz_kanji_n4.php">Kuis Kanji N4</a>
    </p>
</div>
<?php
$pdo = getPDO();

/* Ambil progres lesson siswa */
$lesson_completed = (int)($user["lessons_completed"] ?? 0);
$level = $user["level"] ?? "N5";

/* Cari dokkai yang sesuai */
$stmt = $pdo->prepare("
    SELECT *
    FROM dokkai
    WHERE level = ?
      AND chapter_start <= ?
    ORDER BY chapter_start DESC
    LIMIT 1
");
$stmt->execute([$level, $lesson_completed]);
$activeDokkai = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php if ($activeDokkai): ?>
<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <div class="card-title">📖 Dokkai Aktif</div>
    </div>
    <p>
        Anda telah membuka:
        <strong><?= htmlspecialchars($activeDokkai["title"]) ?></strong>
    </p>
    <a class="button" href="<?= BASE_URL ?>student/dokkai.php">
        Buka Dokkai
    </a>
</div>
<?php endif; ?>

<?php require __DIR__ . "/../includes/footer.php"; ?>