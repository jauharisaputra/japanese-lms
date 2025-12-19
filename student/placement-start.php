<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$pdo = getPDO();

$exam_id = isset($_GET["exam_id"]) ? (int)$_GET["exam_id"] : 0;

$page_title = "Ujian TO (coming soon)";
require __DIR__ . "/../includes/header.php";
?>
<div class="card">
  <div class="card-header">
    <div class="card-title">Ujian TO</div>
  </div>
  <p>Halaman pengerjaan Ujian TO belum diaktifkan. Saat ini baru tersedia tampilan hasil dan rekap nilai.</p>
  <p>Silakan hubungi admin jika halaman ini sudah waktunya dibuka untuk siswa.</p>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
