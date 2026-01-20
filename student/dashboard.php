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
    ($activity["quiz_submit"] * 10);

/* ================= STATUS ================= */
if ($activity_score >= 80) {
    $activity_status = "🟢 Sangat Aktif";
} elseif ($activity_score >= 40) {
    $activity_status = "🟡 Cukup Aktif";
} else {
    $activity_status = "🔴 Kurang Aktif";
}
?>

<!-- ================= DASHBOARD HEADER ================= -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Dashboard Siswa</div>
    </div>

    <div class="card-body">
        <p>
            Halo,
            <strong><?= htmlspecialchars($user["full_name"] ?? $user["username"]); ?></strong>
            (Level <?= htmlspecialchars($level); ?>)
        </p>

        <ul>
            <li>
                Materi selesai:
                <?= (int)($user["lessons_completed"] ?? 0); ?> /
                <?= (int)($user["lessons_total"] ?? 0); ?>
            </li>
            <li>
                Kuis lulus:
                <?= (int)($user["quizzes_passed"] ?? 0); ?> /
                <?= (int)($user["quizzes_total"] ?? 0); ?>
            </li>
        </ul>

        <p style="line-height:2;">
            <a href="<?= BASE_URL ?>student/lessons.php">📘 Materi</a> |
            <a href="<?= BASE_URL ?>student/quizzes.php">📝 Kuis</a> |
            <a href="<?= BASE_URL ?>student/dokkai.php">📖 Dokkai</a> |
            <a href="<?= BASE_URL ?>student/assignments.php">📂 Tugas</a> |
            <a href="<?= BASE_URL ?>student/scores.php">📊 Nilai</a> |
            <a href="<?= BASE_URL ?>student/rapor_view.php">📜 Rapor</a>
        </p>
    </div>
</div>

<!-- ================= KEAKTIFAN SAYA ================= -->
<div class="card mt-3">
    <div class="card-header">
        <div class="card-title">🔥 Keaktifan Saya</div>
    </div>

    <div class="card-body">
        <p>
            Status Keaktifan:
            <strong><?= $activity_status ?></strong>
        </p>

        <ul>
            <li>Total Login: <?= (int)$activity["total_login"] ?></li>
            <li>Dashboard Dibuka: <?= (int)$activity["open_dashboard"] ?></li>
            <li>Materi Dibuka: <?= (int)$activity["open_lesson"] ?></li>
            <li>Materi Diselesaikan: <?= (int)$activity["finish_lesson"] ?></li>
            <li>Choukai Diselesaikan: <?= (int)$activity["finish_choukai"] ?></li>
            <li>Kuis Dikerjakan: <?= (int)$activity["quiz_submit"] ?></li>
        </ul>

        <p>
            <strong>Skor Keaktifan:</strong> <?= $activity_score ?>
        </p>

        <p>
            <small>
                Terakhir aktif:
                <?= $activity["last_active"]
                    ? date("d M Y H:i", strtotime($activity["last_active"]))
                    : "Belum ada aktivitas"
                ?>
            </small>
        </p>
    </div>
</div>

<!-- ================= DOKKAI AKTIF ================= -->
<?php
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
<div class="card mt-3">
    <div class="card-header">
        <div class="card-title">📖 Dokkai Aktif</div>
    </div>
    <div class="card-body">
        <p>
            Materi yang sedang Anda kerjakan:
            <strong><?= htmlspecialchars($activeDokkai["title"]); ?></strong>
        </p>
        <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>student/dokkai.php">
            Buka Dokkai
        </a>
    </div>
</div>
<?php endif; ?>

<!-- ================= CHOUKAI TERSEDIA ================= -->
<?php
$stmt = $pdo->prepare("
    SELECT *
    FROM choukai_materials
    WHERE level = ?
    ORDER BY bab_start ASC
");
$stmt->execute([$level]);
$choukaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($choukaiList): ?>
<div class="card mt-3">
    <div class="card-header">
        <div class="card-title">🎧 Choukai Tersedia</div>
    </div>
    <div class="card-body">
        <ul>
            <?php foreach ($choukaiList as $c): ?>
            <li style="margin-bottom:6px;">
                <?= htmlspecialchars("Bab {$c['bab_start']}–{$c['bab_end']} - {$c['title']}") ?>
                |
                <a href="<?= BASE_URL ?>student/choukai.php?id=<?= $c['id'] ?>">🎧 Mulai</a>
                |
                <a href="<?= BASE_URL ?>student/choukai-result.php?id=<?= $c['id'] ?>">📊 Hasil</a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . "/../includes/footer.php"; ?>