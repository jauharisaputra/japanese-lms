<?php
// ===============================
// DEFAULT TITLE
// ===============================
if (!isset($page_title)) {
    $page_title = "Nihongo Daichi Online";
}

/* ======================================================
   CENTRAL STUDENT ACTIVITY HOOK (FINAL – CSS SAFE)
   - Tidak session_start
   - Tidak output HTML
   - Tidak ganggu CSS
====================================================== */

if (
    function_exists('isLoggedIn') &&
    isLoggedIn() &&
    function_exists('logActivity')
) {
    $user = currentUser();

    if ($user && ($user['role'] ?? null) === 'student') {

        // Path tanpa query (?id=)
        $path = strtok($_SERVER['REQUEST_URI'], '?');

        // Anti double log per halaman
        if (!isset($_SESSION['__activity_last']) ||
            $_SESSION['__activity_last'] !== $path
        ) {
            $_SESSION['__activity_last'] = $path;

            $activityType = null;
            $referenceId  = null;

            /* ========= URL → ACTIVITY MAP ========= */

            if (str_contains($path, '/student/dashboard')) {
                $activityType = 'open_dashboard';
            }
            elseif (str_contains($path, '/student/lesson')) {
                $activityType = 'open_lesson';
                $referenceId  = $_GET['id'] ?? null;
            }
            elseif (str_contains($path, '/student/choukai')) {
                $activityType = 'open_choukai';
                $referenceId  = $_GET['id'] ?? null;
            }
            elseif (str_contains($path, '/student/quiz')) {
                $activityType = 'open_quiz';
                $referenceId  = $_GET['id'] ?? null;
            }
            elseif (str_contains($path, '/student/dokkai')) {
                $activityType = 'open_dokkai';
            }

            /* ========= EXEC ========= */
            if ($activityType) {
                logActivity(
                    (int)$user['id'],
                    $activityType,
                    $referenceId ? (int)$referenceId : null
                );
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?> - Nihongo Daichi Online</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Nihongo Daichi Online adalah platform belajar bahasa Jepang berbasis buku Daichi 1–2, dirancang khusus untuk mempersiapkan siswa berangkat ke Jepang dengan visa TG melalui latihan JLPT N5–N4 yang terstruktur.">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/japanese-theme.css">
</head>

<body>
    <div class="site-wrapper">
        <header class="site-header">
            <div class="site-header-inner">
                <div class="site-logo">
                    <div class="site-logo-mark">日</div>
                    <div>
                        <div class="site-logo-text-main">Nihongo Daichi Online</div>
                        <div class="site-logo-text-sub">にほんご大地オンライン（Visa TG 対応）</div>
                    </div>
                </div>
                <nav class="site-nav">
                    <a href="<?= BASE_URL ?>index.php">Beranda</a>

                    <?php if (function_exists("isLoggedIn") && isLoggedIn()): ?>
                    <?php $u = currentUser(); ?>

                    <?php if ($u && in_array($u["role"], ["teacher","admin"], true)): ?>
                    <a href="<?= BASE_URL ?>teacher/dashboard.php">Dashboard Guru</a>
                    <?php endif; ?>

                    <?php if ($u && $u["role"] === "student"): ?>
                    <a href="<?= BASE_URL ?>student/dashboard.php">Dashboard Siswa</a>
                    <?php endif; ?>

                    <a href="<?= BASE_URL ?>logout.php">Logout</a>
                    <?php else: ?>
                    <a href="<?= BASE_URL ?>login.php">Login</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <main class="site-main">