<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['admin']);
$page_title = 'Admin Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<h1>Dashboard Admin</h1>
<p>Selamat datang, <?php echo htmlspecialchars(currentUser()['full_name'] ?? 'Admin'); ?>.</p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
