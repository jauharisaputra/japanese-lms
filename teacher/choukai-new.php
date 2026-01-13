<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
$page_title = "Tambah Choukai";
require __DIR__ . "/../includes/header.php";

$pdo   = getPDO();
$user  = currentUser();
$errors = [];

/* =========================
   PROSES SIMPAN CHOUKAI
   ========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $level = $_POST["level"] ?? "";
    $chapter_start = (int)($_POST["chapter_start"] ?? 0);
    $chapter_end   = (int)($_POST["chapter_end"] ?? 0);

    /* --- Validasi level --- */
    if (!in_array($level, ["N5", "N4"])) {
        $errors[] = "Level tidak valid.";
    }

    /* --- Validasi bab per level --- */
    if ($level === "N5" && ($chapter_start < 1 || $chapter_end > 22)) {
        $errors[] = "Bab N5 harus antara 1 sampai 22.";
    }

    if ($level === "N4" && ($chapter_start < 23 || $chapter_end > 42)) {
        $errors[] = "Bab N4 harus antara 23 sampai 42.";
    }

    /* --- Validasi tepat 4 bab --- */
    if (($chapter_end - $chapter_start + 1) !== 4) {
        $errors[] = "Choukai harus mencakup tepat 4 bab.";
    }

    /* --- Validasi file --- */
    if (!isset($_FILES["choukai_file"]) || $_FILES["choukai_file"]["error"] !== UPLOAD_ERR_OK) {
        $errors[] = "File choukai wajib diupload.";
    }

    /* =========================
       JIKA LOLOS VALIDASI
       ========================= */
    if (!$errors) {

        $ext = strtolower(pathinfo($_FILES["choukai_file"]["name"], PATHINFO_EXTENSION));
        $allowed = ["mp3", "wav", "ogg"];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Format file harus MP3 / WAV / OGG.";
        } else {

            /* Folder upload */
            $upload_dir = __DIR__ . "/../uploads/choukai/" . $level;
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            /* Nama file otomatis */
            $filename = "choukai_{$chapter_start}_{$chapter_end}." . $ext;
            $target_path = $upload_dir . "/" . $filename;

            if (!move_uploaded_file($_FILES["choukai_file"]["tmp_name"], $target_path)) {
                $errors[] = "Gagal mengupload file.";
            } else {

                /* Data final */
                $title = "Choukai Bab {$chapter_start}–{$chapter_end}";
                $file_path = "uploads/choukai/{$level}/{$filename}";
                $bab = $chapter_start;

                /* Simpan ke database */
                $stmt = $pdo->prepare("
                    INSERT INTO choukai
                    (title, chapter_start, chapter_end, level, bab, audio_file, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $title,
                    $chapter_start,
                    $chapter_end,
                    $level,
                    $bab,
                    $file_path,
                    $user['id']
                ]);

                header("Location: choukai.php?success=1");
                exit;
            }
        }
    }
}
?>

<!-- =========================
     FORM INPUT CHOUKAI
     ========================= -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Tambah Choukai (Per 4 Bab)</div>
    </div>

    <?php if ($errors): ?>
    <div class="alert error">
        <ul>
            <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

        <label>Level</label>
        <select name="level" required>
            <option value="">-- Pilih Level --</option>
            <option value="N5">N5 (Bab 1–22)</option>
            <option value="N4">N4 (Bab 23–42)</option>
        </select>

        <label>Bab Mulai</label>
        <input type="number" name="chapter_start" required>

        <label>Bab Selesai</label>
        <input type="number" name="chapter_end" required>

        <label>Upload Audio Choukai</label>
        <input type="file" name="choukai_file" accept=".mp3,.wav,.ogg" required>

        <div style="margin-top:12px;">
            <button type="submit">Simpan Choukai</button>
            <a href="choukai.php" class="button secondary">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>