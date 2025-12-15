<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["admin","teacher"]);
$page_title = "Dashboard Guru/Admin";
require __DIR__ . "/../includes/header.php";

$u = currentUser();
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Dashboard <?php echo htmlspecialchars(ucfirst($u["role"])); ?></div>
    </div>
    <p>
        Halo, <?php echo htmlspecialchars($u["full_name"] ?? $u["username"]); ?>.
    </p>
    <ul>
        <li><a href="<?php echo BASE_URL; ?>teacher/lessons.php">Kelola materi Daichi</a></li>
        <li><a href="<?php echo BASE_URL; ?>teacher/lesson-create.php">Tambah materi baru</a></li>
        <li><a href="<?php echo BASE_URL; ?>teacher/quizzes.php">Kelola kuis</a></li>
        <li><a href="<?php echo BASE_URL; ?>teacher/students.php">Lihat siswa</a></li>
        <li><a href="<?php echo BASE_URL; ?>teacher/analytics.php">Analitik nilai siswa</a></li>
        <li><a href="<?php echo BASE_URL; ?>teacher/quiz-results.php">Hasil kuis &amp; remedial</a></li>
        <li><a href="<?php echo BASE_URL; ?>teacher/lesson-progress.php">Lihat progres lesson siswa</a></li>
        <?php if ($u["role"] === "admin"): ?>
            <li><a href="<?php echo BASE_URL; ?>teacher/user-create.php">Tambah pengguna (siswa/guru/admin)</a></li>
        <?php endif; ?>
    </ul>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
