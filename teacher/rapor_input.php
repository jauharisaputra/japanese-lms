<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
global $pdo;

// Ambil daftar hasil TO dari to_results (silakan sesuaikan nama kolom/tabel)
$results = [];
$stmt = $pdo->query("SELECT * FROM to_results ORDER BY completed_at DESC");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = "";
$error   = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id       = $_POST["student_id"] ?? "";
    $kelas            = $_POST["kelas"] ?? "";
    $tanggal          = $_POST["tanggal"] ?? date("Y-m-d");
    $kehadiran        = (int)($_POST["kehadiran"] ?? 0);
    $keaktifan        = (int)($_POST["keaktifan"] ?? 0);
    $tugas_keseluruhan= (int)($_POST["tugas_keseluruhan"] ?? 0);

    // Nilai TO (0–100) diambil dari POST, tapi idealnya auto-fill dari to_results
    $bunpou  = (float)($_POST["bunpou"]  ?? 0);
    $kanji   = (float)($_POST["kanji"]   ?? 0);
    $dokkai  = (float)($_POST["dokkai"]  ?? 0);
    $choukai = (float)($_POST["choukai"] ?? 0);
    $kaiwa   = (float)($_POST["kaiwa"]   ?? 0);

    $catatan_sensei = trim($_POST["catatan_sensei"] ?? "");

    // Logika Perhitungan Nilai
    $sikap = ($kehadiran + $keaktifan) / 2;
    $tugas = $tugas_keseluruhan;
    $kompetensi_to = ($bunpou + $kanji + $dokkai + $choukai + $kaiwa) / 5;

    $total_nilai = round(($sikap * 0.3) + ($tugas * 0.3) + ($kompetensi_to * 0.4));

    $status_lulus = ($total_nilai >= 75) ? "LULUS" : "TIDAK LULUS";

    try {
        $sql = "INSERT INTO rapor_n5 
            (student_id, kelas, tanggal, kehadiran, keaktifan, tugas_keseluruhan,
             bunpou_goi, kanji, dokkai, choukai, kaiwa,
             sikap_nilai, tugas_nilai, kompetensi_to_nilai,
             total_nilai, status_lulus, catatan_sensei)
            VALUES
            (:student_id, :kelas, :tanggal, :kehadiran, :keaktifan, :tugas_keseluruhan,
             :bunpou, :kanji, :dokkai, :choukai, :kaiwa,
             :sikap, :tugas, :kompetensi_to,
             :total_nilai, :status_lulus, :catatan_sensei)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":student_id"        => $student_id,
            ":kelas"             => $kelas,
            ":tanggal"           => $tanggal,
            ":kehadiran"         => $kehadiran,
            ":keaktifan"         => $keaktifan,
            ":tugas_keseluruhan" => $tugas_keseluruhan,
            ":bunpou"            => $bunpou,
            ":kanji"             => $kanji,
            ":dokkai"            => $dokkai,
            ":choukai"           => $choukai,
            ":kaiwa"             => $kaiwa,
            ":sikap"             => $sikap,
            ":tugas"             => $tugas,
            ":kompetensi_to"     => $kompetensi_to,
            ":total_nilai"       => $total_nilai,
            ":status_lulus"      => $status_lulus,
            ":catatan_sensei"    => $catatan_sensei,
        ]);

        $success = "Rapor berhasil disimpan. Total nilai: {$total_nilai} ({$status_lulus})";
    } catch (Exception $e) {
        $error = "Gagal menyimpan rapor: " . htmlspecialchars($e->getMessage());
    }
}
?>
<?php require __DIR__ . "/../includes/header.php"; ?>
<div class="container">
    <h2>Input Rapor N5</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <div>
            <label>Student ID (NIS)</label>
            <input type="text" name="student_id" required>
        </div>
        <div>
            <label>Kelas</label>
            <input type="text" name="kelas" value="Hoki">
        </div>
        <div>
            <label>Tanggal</label>
            <input type="date" name="tanggal" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <h3>A. Sikap</h3>
        <div>
            <label>Kehadiran (0–100)</label>
            <input type="number" name="kehadiran" min="0" max="100" required>
        </div>
        <div>
            <label>Keaktifan (0–100)</label>
            <input type="number" name="keaktifan" min="0" max="100" required>
        </div>

        <h3>B. Tugas</h3>
        <div>
            <label>Keseluruhan Tugas (0–100)</label>
            <input type="number" name="tugas_keseluruhan" min="0" max="100" required>
        </div>

        <h3>C. Kompetensi TO (0–100)</h3>
        <div>
            <label>Bunpou, Goi, Tanbun Sakusei</label>
            <input type="number" name="bunpou" min="0" max="100" required>
        </div>
        <div>
            <label>Kanji</label>
            <input type="number" name="kanji" min="0" max="100" required>
        </div>
        <div>
            <label>Dokkai</label>
            <input type="number" name="dokkai" min="0" max="100" required>
        </div>
        <div>
            <label>Choukai</label>
            <input type="number" name="choukai" min="0" max="100" required>
        </div>
        <div>
            <label>Kaiwa</label>
            <input type="number" name="kaiwa" min="0" max="100" required>
        </div>

        <h3>E. Catatan Sensei</h3>
        <div>
            <textarea name="catatan_sensei" rows="4" cols="60"></textarea>
        </div>

        <div style="margin-top:10px;">
            <button type="submit">Simpan Rapor</button>
        </div>
    </form>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
