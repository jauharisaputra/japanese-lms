<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$user = currentUser();
$page_title = "Dashboard Siswa";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();
$level = $user["level"] ?? "N5";
$lesson_completed = (int)($user["lessons_completed"] ?? 0);
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

        <!-- ================= MENU SISWA ================= -->
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
                <a href="<?= BASE_URL ?>student/choukai.php?id=<?= $c['id'] ?>">
                    🎧 Mulai
                </a>
                |
                <a href="<?= BASE_URL ?>student/choukai-result.php?id=<?= $c['id'] ?>">
                    📊 Hasil
                </a>

            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . "/../includes/footer.php"; ?>