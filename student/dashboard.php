<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);

$user = currentUser();

/* ================= LOG AKTIVITAS DASHBOARD ================= */
logActivity($user['id'], 'open_dashboard');

$page_title = "Dashboard Siswa";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();

$level = $user["level"] ?? "N5";
$lesson_completed = (int)($user["lessons_completed"] ?? 0);

/* ================= KEAKTIFAN SISWA ================= */
$stmt = $pdo->prepare("
    SELECT
        COUNT(CASE WHEN activity_type = 'login' THEN 1 END) AS total_login,
        COUNT(CASE WHEN activity_type = 'open_dashboard' THEN 1 END) AS open_dashboard,
        COUNT(CASE WHEN activity_type = 'open_lesson' THEN 1 END) AS open_lesson,
        COUNT(CASE WHEN activity_type = 'finish_lesson' THEN 1 END) AS finish_lesson,
        COUNT(CASE WHEN activity_type = 'finish_choukai' THEN 1 END) AS finish_choukai,
        COUNT(CASE WHEN activity_type = 'quiz_submit' THEN 1 END) AS quiz_submit,
        COUNT(CASE WHEN activity_type = 'to_exam_submit' THEN 1 END) AS to_exam_submit,
        MAX(created_at) AS last_active
    FROM student_activity_logs
    WHERE user_id = ?
");
$stmt->execute([$user["id"]]);
$activity = $stmt->fetch(PDO::FETCH_ASSOC);

/* ================= HITUNG SKOR ================= */
$activity_score =
    ($activity["total_login"] * 2) +
    ($activity["open_dashboard"] * 1) +
    ($activity["open_lesson"] * 2) +
    ($activity["finish_lesson"] * 5) +
    ($activity["finish_choukai"] * 5) +
    ($activity["quiz_submit"] * 10) +
    ($activity["to_exam_submit"] * 15);

/* ================= STATUS ================= */
if ($activity_score >= 100) {
    $activity_status = "🟢 ⭐ Sangat Aktif";
} elseif ($activity_score >= 60) {
    $activity_status = "🟢 Aktif";
} elseif ($activity_score >= 30) {
    $activity_status = "🟡 Cukup Aktif";
} else {
    $activity_status = "🔴 Kurang Aktif";
}
?>

<!-- ================= DASHBOARD HEADER ================= -->
<div class="row g-4">
    <!-- Kolom Utama -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0">👋 Selamat Datang</h4>
            </div>
            <div class="card-body">
                <h5 class="mb-3">Halo,
                    <strong><?= htmlspecialchars($user["full_name"] ?? $user["username"]); ?></strong>
                </h5>
                <p class="mb-4">Level: <span
                        class="badge bg-danger fs-6 px-3 py-2"><?= htmlspecialchars($level); ?></span></p>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="text-center p-3 border rounded bg-light">
                            <h3 class="text-primary"><?= (int)($user["lessons_completed"] ?? 0); ?></h3>
                            <small class="text-muted">Materi Selesai</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center p-3 border rounded bg-light">
                            <h3 class="text-success"><?= (int)($user["quizzes_passed"] ?? 0); ?></h3>
                            <small class="text-muted">Kuis Lulus</small>
                        </div>
                    </div>
                </div>

                <!-- QUICK MENU -->
                <div class="d-grid gap-2 mb-4">
                    <a href="<?= BASE_URL ?>student/lessons.php" class="btn btn-outline-primary btn-lg">
                        📘 Materi Pelajaran
                    </a>
                    <a href="<?= BASE_URL ?>student/quizzes.php" class="btn btn-outline-success btn-lg">
                        📝 Latihan Kuis
                    </a>
                    <a href="<?= BASE_URL ?>student/to-exams.php" class="btn btn-danger btn-lg fw-bold shadow-sm">
                        📄 Ujian TO (N5/N4)
                    </a>
                    <a href="<?= BASE_URL ?>student/dokkai.php" class="btn btn-outline-info btn-lg">
                        📖 Dokkai
                    </a>
                </div>

                <!-- MENU TAMBAHAN -->
                <div class="row text-center">
                    <div class="col-6">
                        <a href="<?= BASE_URL ?>student/assignments.php" class="btn btn-outline-warning btn-sm">
                            📂 Tugas
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= BASE_URL ?>student/scores.php" class="btn btn-outline-secondary btn-sm">
                            📊 Nilai
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= BASE_URL ?>student/rapor_view.php" class="btn btn-outline-dark btn-sm">
                            📜 Rapor
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= BASE_URL ?>student/profile.php" class="btn btn-outline-info btn-sm">
                            ⚙️ Profil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Sidebar -->
    <div class="col-lg-4">
        <!-- KEAKTIFAN -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-gradient text-white">
                <h6 class="mb-0">🔥 Keaktifan Saya</h6>
            </div>
            <div class="card-body">
                <h5 class="mb-3">
                    <span
                        class="badge <?= strpos($activity_status, 'Sangat') !== false ? 'bg-success' : 
                                         (strpos($activity_status, 'Aktif') !== false ? 'bg-warning' : 'bg-danger') ?>">
                        <?= $activity_status ?>
                    </span>
                </h5>
                <ul class="list-unstyled small">
                    <li><small>Login: <?= (int)$activity["total_login"] ?></small></li>
                    <li><small>Materi: <?= (int)$activity["finish_lesson"] ?></small></li>
                    <li><small>Choukai: <?= (int)$activity["finish_choukai"] ?></small></li>
                    <li><small>Kuis: <?= (int)$activity["quiz_submit"] ?></small></li>
                    <li><small>TO Exam: <?= (int)$activity["to_exam_submit"] ?></small></li>
                </ul>
                <hr>
                <strong>Skor: <?= $activity_score ?>/150</strong>
                <div class="progress mt-2" style="height: 8px;">
                    <div class="progress-bar" style="width: <?= min(100, ($activity_score/150)*100) ?>%"></div>
                </div>
            </div>
        </div>

        <!-- TERAKHIR AKTIF -->
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">📅 Terakhir Aktif</h6>
            </div>
            <div class="card-body text-center py-4">
                <?php if ($activity["last_active"]): ?>
                <i class="fas fa-clock text-success fa-2x mb-2"></i>
                <p class="mb-1"><strong><?= date("d M Y", strtotime($activity["last_active"])) ?></strong></p>
                <small class="text-muted"><?= date("H:i", strtotime($activity["last_active"])) ?> WIB</small>
                <?php else: ?>
                <i class="fas fa-user-clock fa-2x mb-2 text-muted"></i>
                <p class="text-muted">Belum ada aktivitas</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ================= UJIAN TO TERSEDIA ================= -->
<?php
$stmt = $pdo->prepare("
    SELECT id, level, name, max_score, pass_score, attempts_allowed
    FROM placement_exams 
    WHERE level IN ('N5', 'N4')
    ORDER BY FIELD(level, 'N5', 'N4'), id ASC
");
$stmt->execute();
$toExams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($toExams): ?>
<div class="card mt-5 shadow-lg border-0">
    <div class="card-header bg-danger text-white py-3">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>📄 Ujian TO Tersedia
                </h3>
            </div>
            <div class="col-auto">
                <span class="badge bg-light text-danger fs-6"><?= count($toExams) ?> Ujian</span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="row g-3 p-4">
            <?php foreach ($toExams as $index => $exam): ?>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-1 fw-bold text-danger">
                                <?= htmlspecialchars($exam['level']) ?>
                            </h5>
                            <span class="badge bg-danger"><?= $index + 1 ?></span>
                        </div>

                        <h6 class="card-subtitle mb-3 text-muted">
                            <?= htmlspecialchars($exam['name']) ?>
                        </h6>

                        <ul class="list-unstyled small mb-4">
                            <li class="mb-1"><i class="fas fa-tasks text-danger me-1"></i><?= (int)$exam['max_score'] ?>
                                soal</li>
                            <li class="mb-1"><i
                                    class="fas fa-check-circle text-success me-1"></i><?= (int)$exam['pass_score'] ?>
                                passing</li>
                            <li class="mb-1"><i
                                    class="fas fa-redo text-warning me-1"></i><?= (int)$exam['attempts_allowed'] ?>x
                                attempt</li>
                        </ul>

                        <div class="d-grid">
                            <a href="<?= BASE_URL ?>student/to-exam.php?exam_id=<?= $exam['id'] ?>"
                                class="btn btn-danger btn-lg fw-bold shadow-sm">
                                🚀 Mulai Ujian
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ================= DOKKAI & CHOUKAI ================= -->
<?php
$stmt = $pdo->prepare("
    SELECT * FROM dokkai WHERE level = ? AND chapter_start <= ? ORDER BY chapter_start DESC LIMIT 1
");
$stmt->execute([$level, $lesson_completed]);
$activeDokkai = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT * FROM choukai_materials WHERE level = ? ORDER BY bab_start ASC LIMIT 3
");
$stmt->execute([$level]);
$choukaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($activeDokkai || $choukaiList): ?>
<div class="row g-4 mt-4">
    <?php if ($activeDokkai): ?>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">📖 Dokkai Aktif</h6>
            </div>
            <div class="card-body">
                <h6 class="fw-bold"><?= htmlspecialchars($activeDokkai["title"]); ?></h6>
                <a href="<?= BASE_URL ?>student/dokkai.php" class="btn btn-info w-100 mt-2">Buka Dokkai</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($choukaiList): ?>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">🎧 Choukai Tersedia</h6>
            </div>
            <div class="card-body">
                <?php foreach (array_slice($choukaiList, 0, 2) as $c): ?>
                <div class="d-flex justify-content-between mb-2 small">
                    <span><?= htmlspecialchars("Bab {$c['bab_start']}–{$c['bab_end']}"); ?></span>
                    <a href="<?= BASE_URL ?>student/choukai.php?id=<?= $c['id'] ?>"
                        class="btn btn-sm btn-outline-warning">🎧</a>
                </div>
                <?php endforeach; ?>
                <?php if (count($choukaiList) > 2): ?>
                <a href="<?= BASE_URL ?>student/choukai.php" class="btn btn-sm btn-warning w-100 mt-2">Lihat Semua</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<style>
.hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
}

.bg-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

@media (max-width: 768px) {
    .d-grid {
        gap: 0.5rem !important;
    }
}
</style>

<?php require __DIR__ . "/../includes/footer.php"; ?>