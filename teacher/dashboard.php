<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['teacher']);
$page_title = 'Dashboard Guru';
require __DIR__ . '/../includes/header.php';
?>
<h1>Dashboard Guru</h1>
<p>Selamat datang, <?php echo htmlspecialchars(currentUser()['full_name'] ?? 'Sensei'); ?>.</p>
<ul>
    <li><a href="../teacher/lessons.php">Kelola materi</a></li>
    <li><a href="../teacher/quizzes.php">Kelola kuis</a></li>
    <li><a href="../teacher/students.php">Lihat siswa</a></li>
    <li><a href="../teacher/analytics.php">Analitik nilai siswa</a></li>
    <li><a href="../teacher/quiz-results.php">Hasil kuis & remedial</a></li>

</ul>
<?php require __DIR__ . '/../includes/footer.php'; ?><li><a href="../teacher/quiz-results.php">Hasil kuis & remedial</a></li>
<li><a href="../teacher/analytics.php">Analitik nilai siswa</a></li>
<li><a href="../teacher/skill-report.php">Analitik per skill</a></li>
<li><a href="../teacher/lesson-progress.php">Progress lesson siswa</a></li>
