<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$page_title = "Mengerjakan Kuis";
require __DIR__ . "/../includes/header.php";

// Di sini biasanya ada logika mengambil data kuis & menampilkan soal.
// Silakan gabungkan kembali dengan versi lama jika ada.
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Mengerjakan Kuis</div>
    </div>
    <p>Silakan kerjakan kuis tanpa membuka tab lain, menyalin soal, atau mengambil screenshot.</p>

    <!-- TODO: tampilkan form kuis Sensei di sini -->
</div>

<script>
// ====== BLOK COPY/PASTE DAN KLIK KANAN ======
document.addEventListener("contextmenu", function (e) {
  e.preventDefault();
});

document.addEventListener("selectstart", function (e) {
  e.preventDefault();
});

document.addEventListener("keydown", function (e) {
  // blok Ctrl+C, Ctrl+X, Ctrl+V
  if (e.ctrlKey && ["c","x","v","C","X","V"].includes(e.key)) {
    e.preventDefault();
  }
});

// ====== DETEKSI PRINTSCREEN & LAPOR KE SERVER ======
document.addEventListener("keydown", function (e) {
  if (e.key === "PrintScreen" || e.code === "PrintScreen") {
    e.preventDefault();
    alert("Terdeteksi percobaan screenshot. Nilai akan dikurangi 10 poin.");

    fetch("quiz-cheat.php", {
      method: "POST",
      headers: {"Content-Type": "application/x-www-form-urlencoded"},
      body: "action=screenshot"
    }).catch(function(err) {
      console.error("Gagal melaporkan kecurangan", err);
    });
  }
});
</script>

<?php require __DIR__ . "/../includes/footer.php"; ?>
