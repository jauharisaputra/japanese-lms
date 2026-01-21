<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["admin","teacher"]);
$page_title = "Dashboard Teacher";
require __DIR__ . "/../includes/header.php";

$u   = currentUser();
$pdo = getPDO();

/* Rekap kuis terbaru */
$stmt = $pdo->query("
    SELECT 
        qa.id,
        u.full_name,
        q.title,
        qa.score
    FROM quiz_attempts qa
    JOIN users u   ON qa.user_id = u.id
    JOIN quizzes q ON qa.quiz_id = q.id
    ORDER BY qa.id DESC
    LIMIT 20
");
$quizRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">Dashboard Teacher</div>
    </div>

    <p>
        Halo, <strong><?= htmlspecialchars($u["full_name"] ?? $u["username"]) ?></strong>
    </p>

    <h5>📘 Materi & Pembelajaran</h5>
    <ul>
        <li><a href="<?= BASE_URL ?>teacher/lessons.php">Kelola Materi Daichi</a></li>
        <li><a href="<?= BASE_URL ?>teacher/lesson-create.php">Tambah Materi Baru</a></li>
        <li><a href="<?= BASE_URL ?>teacher/dokkai.php">📖 Kelola Dokkai (per 4 Bab)</a></li>
        <li><a href="<?= BASE_URL ?>teacher/dokkai-results.php">📊 Hasil Dokkai Siswa</a></li>
        <li><a href="<?= BASE_URL ?>teacher/choukai-upload.php">🎧 Upload Choukai (Audio & PDF)</a></li>
        <li><a href="<?= BASE_URL ?>teacher/choukai-recap.php">📊 Rekap & Nilai Choukai</a></li>
    </ul>

    <h5>📝 Tugas & Evaluasi</h5>
    <ul>
        <li><a href="<?= BASE_URL ?>teacher/assignments.php">Kelola Tugas</a></li>
        <li><a href="<?= BASE_URL ?>teacher/assignment-submissions.php">📝 Penilaian Tugas Siswa</a></li>
        <li><a href="<?= BASE_URL ?>teacher/quizzes.php">Kelola Kuis</a></li>
        <li><a href="<?= BASE_URL ?>teacher/quiz-results.php">Hasil Kuis & Remedial</a></li>
    </ul>

    <h5>👥 Kelas & Siswa</h5>
    <ul>
        <li><a href="<?= BASE_URL ?>teacher/students.php">Lihat Siswa</a></li>
        <li><a href="<?= BASE_URL ?>teacher/classes.php">Kelola Kelas</a></li>
        <li><a href="<?= BASE_URL ?>teacher/lesson-progress.php">Progres Lesson Siswa</a></li>
        <li><a href="<?= BASE_URL ?>teacher/rekap-keaktifan.php">🔥 Rekap Keaktifan Siswa</a></li>
        <li><a href="<?= BASE_URL ?>teacher/analytics.php">Analitik Nilai Siswa</a></li>
    </ul>

    <h5>📂 Administrasi</h5>
    <ul>
        <li><a href="<?= BASE_URL ?>teacher/export-grades.php">Export Semua Nilai (CSV)</a></li>
        <li><a href="<?= BASE_URL ?>teacher/placement-exams.php">Kelola Ujian TO (N5/N4)</a></li>
        <li><a href="<?= BASE_URL ?>teacher/rapor_input.php">📚 Input Rapor N5</a></li>
        <li><a href="<?= BASE_URL ?>teacher/rapor_list.php">📋 Daftar Rapor</a></li>
    </ul>
</div>

<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <div class="card-title">📊 Rekap Kuis Terbaru</div>
    </div>

    <div class="card-body">
        <a class="btn btn-sm btn-outline-primary mb-2" href="<?= BASE_URL ?>teacher/quiz-recap-export.php">
            Export CSV Rekap Kuis
        </a>

        <?php if (!$quizRows): ?>
        <p>Belum ada data kuis.</p>
        <?php else: ?>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Siswa</th>
                    <th>Kuis</th>
                    <th>Skor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizRows as $r): ?>
                <tr>
                    <td><?= (int)$r["id"] ?></td>
                    <td><?= htmlspecialchars($r["full_name"]) ?></td>
                    <td><?= htmlspecialchars($r["title"]) ?></td>
                    <td><?= htmlspecialchars($r["score"]) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>