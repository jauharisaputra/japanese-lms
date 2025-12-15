<?php
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/includes/functions.php";

$page_title = "Beranda";
require __DIR__ . "/includes/header.php";
?>
<div class="card" style="max-width:780px;margin:32px auto;">
    <div class="card-header">
        <div class="card-title">Selamat datang di Nihongo Daichi Online</div>
    </div>
    <p>
        Nihongo Daichi Online adalah platform belajar bahasa Jepang berbasis buku Daichi 1–2,
        dirancang khusus untuk mempersiapkan siswa berangkat ke Jepang dengan visa TG
        melalui latihan JLPT N5–N4 yang terstruktur.
    </p>
    <p>
        Materi mengikuti 22 bab Daichi 1 (N5) dan 20 bab Daichi 2 (N4), dilengkapi kuis interaktif
        serta pemantauan progres otomatis.
    </p>
    <p>
        Silakan login untuk mulai belajar:
    </p>
    <p>
        <a class="button" href="<?php echo BASE_URL; ?>login.php">Masuk sebagai siswa/guru</a>
    </p>
</div>
<?php require __DIR__ . "/includes/footer.php"; ?>
