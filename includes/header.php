<?php
if (!isset($page_title)) {
    $page_title = "Japanese LMS";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?> - Japanese LMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/japanese-theme.css">
</head>
<body>
<div class="site-wrapper">
    <header class="site-header">
        <div class="site-header-inner">
            <div class="site-logo">
                <div class="site-logo-mark">?</div>
                <div>
                    <div class="site-logo-text-main">Japanese LMS</div>
                    <div class="site-logo-text-sub">??????????????</div>
                </div>
            </div>
            <nav class="site-nav">
                <a href="<?php echo BASE_URL; ?>index.php">Beranda</a>
                <?php if (function_exists("isLoggedIn") && isLoggedIn()): ?>
                    <?php $u = currentUser(); ?>
                    <?php if ($u && ($u["role"] === "teacher" || $u["role"] === "admin")): ?>
                        <a href="<?php echo BASE_URL; ?>teacher/dashboard.php">Dashboard Guru</a>
                    <?php endif; ?>
                    <?php if ($u && $u["role"] === "student"): ?>
                        <a href="<?php echo BASE_URL; ?>student/dashboard.php">Dashboard Siswa</a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>logout.php">Logout</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>login.php">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="site-main">
