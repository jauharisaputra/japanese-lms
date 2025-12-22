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
        <a href="<?php echo BASE_URL; ?>student/quizzes.php">Lanjut kuis</a> |
        <a href="<?php echo BASE_URL; ?>student/scores.php">Lihat riwayat nilai</a> |
        <a href="<?php echo BASE_URL; ?>student/assignments.php">Tugas &amp; Fukushuu</a> |
        <a href=`"<?php echo BASE_URL; ?>student/rapor_view.php`">📜 Rapor N5</a>
    </p>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>