<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";
requireRole(["teacher","admin"]);

$pdo = getPDO();

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $kehadiran         = (int)($_POST["kehadiran"] ?? 0);
    $keaktifan         = (int)($_POST["keaktifan"] ?? 0);
    $tugas_keseluruhan = (int)($_POST["tugas_keseluruhan"] ?? 0);
    $bunpou_goi        = (float)($_POST["bunpou_goi"] ?? 0);
    $kanji             = (float)($_POST["kanji"] ?? 0);
    $dokkai            = (float)($_POST["dokkai"] ?? 0);
    $choukai           = (float)($_POST["choukai"] ?? 0);
    $kaiwa             = (float)($_POST["kaiwa"] ?? 0);

    $sikap_nilai         = ($kehadiran + $keaktifan) / 2;
    $tugas_nilai         = $tugas_keseluruhan;
    $kompetensi_to_nilai = ($bunpou_goi + $kanji + $dokkai + $choukai + $kaiwa) / 5;
    $total_nilai         = round(($sikap_nilai * 0.3) + ($tugas_nilai * 0.3) + ($kompetensi_to_nilai * 0.4));
    $status_lulus        = $total_nilai >= 75 ? "LULUS" : "TIDAK LULUS";

    $data = [
        "student_id"        => $_POST["student_id"] ?? "",
        "student_name"      => $_POST["student_name"] ?? "",
        "kelas"             => $_POST["kelas"] ?? "Hoki",
        "tanggal"           => $_POST["tanggal"] ?? date("Y-m-d"),
        "kehadiran"         => $kehadiran,
        "keaktifan"         => $keaktifan,
        "tugas_keseluruhan" => $tugas_keseluruhan,
        "bunpou_goi"        => $bunpou_goi,
        "kanji"             => $kanji,
        "dokkai"            => $dokkai,
        "choukai"           => $choukai,
        "kaiwa"             => $kaiwa,
        "sikap_nilai"       => $sikap_nilai,
        "tugas_nilai"       => $tugas_nilai,
        "kompetensi_to_nilai" => $kompetensi_to_nilai,
        "total_nilai"       => $total_nilai,
        "status_lulus"      => $status_lulus,
        "catatan_sensei"    => trim($_POST["catatan_sensei"] ?? "")
    ];

    $sql = "INSERT INTO rapor_n5
        (student_id, student_name, kelas, tanggal,
         kehadiran, keaktifan, tugas_keseluruhan,
         bunpou_goi, kanji, dokkai, choukai, kaiwa,
         sikap_nilai, tugas_nilai, kompetensi_to_nilai,
         total_nilai, status_lulus, catatan_sensei)
        VALUES
        (:student_id, :student_name, :kelas, :tanggal,
         :kehadiran, :keaktifan, :tugas_keseluruhan,
         :bunpou_goi, :kanji, :dokkai, :choukai, :kaiwa,
         :sikap_nilai, :tugas_nilai, :kompetensi_to_nilai,
         :total_nilai, :status_lulus, :catatan_sensei)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        $success = "Rapor tersimpan. Total nilai: {$total_nilai} ({$status_lulus})";
    } catch (Exception $e) {
        $error = "Gagal menyimpan rapor: " . htmlspecialchars($e->getMessage());
    }
}

$page_title = "Input Rapor N5";
require __DIR__ . "/../includes/header.php";
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Input Rapor N5</div>
    </div>
    <div class="card-body">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post">
            <div>
                <label>NIS / Student ID *</label>
                <input type="text" name="student_id" required>
            </div>
            <div>
                <label>Nama Siswa</label>
                <input type="text" name="student_name">
            </div>
            <div>
                <label>Kelas</label>
                <input type="text" name="kelas" value="Hoki">
            </div>
            <div>
                <label>Tanggal</label>
                <input type="date" name="tanggal" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <h4>A. Sikap (0–100)</h4>
            <div>
                <label>Kehadiran</label>
                <input type="number" name="kehadiran" min="0" max="100" required>
            </div>
            <div>
                <label>Keaktifan</label>
                <input type="number" name="keaktifan" min="0" max="100" required>
            </div>

            <h4>B. Tugas</h4>
            <div>
                <label>Keseluruhan Tugas</label>
                <input type="number" name="tugas_keseluruhan" min="0" max="100" required>
            </div>

            <h4>C. Kompetensi TO</h4>
            <div>
                <label>Bunpou, Goi, Tanbun Sakusei</label>
                <input type="number" name="bunpou_goi" min="0" max="100">
            </div>
            <div>
                <label>Kanji</label>
                <input type="number" name="kanji" min="0" max="100">
            </div>
            <div>
                <label>Dokkai</label>
                <input type="number" name="dokkai" min="0" max="100">
            </div>
            <div>
                <label>Choukai</label>
                <input type="number" name="choukai" min="0" max="100">
            </div>
            <div>
                <label>Kaiwa</label>
                <input type="number" name="kaiwa" min="0" max="100">
            </div>

            <h4>Catatan Sensei</h4>
            <div>
                <textarea name="catatan_sensei" rows="4" cols="60"></textarea>
            </div>

            <div style="margin-top:10px;">
                <button type="submit" class="btn btn-primary">Simpan Rapor</button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
