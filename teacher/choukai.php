<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['teacher','admin']);
$user = currentUser();

$page_title = "Kelola Choukai";
$pdo = getPDO();

// =====================
// Handle form submit
// =====================
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $chapter_start = (int)($_POST['chapter_start'] ?? 0);
    $chapter_end = (int)($_POST['chapter_end'] ?? 0);
    $level = $_POST['level'] ?? '';
    $choukai_id = (int)($_POST['choukai_id'] ?? 0);

    // Validasi
    if (!$title) $errors[] = "Judul wajib diisi";
    if ($chapter_start <= 0 || $chapter_end <= 0) $errors[] = "Bab mulai/akhir harus valid";
    if ($chapter_end < $chapter_start) $errors[] = "Bab akhir harus >= bab mulai";
    if (!in_array($level, ['N5','N4'])) $errors[] = "Level tidak valid";

    // Upload file PDF
    $file_path = "";
    if (!empty($_FILES['file']['name'])) {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        if ($ext !== 'pdf') {
            $errors[] = "File PDF harus berekstensi .pdf";
        } else {
            $dest = "uploads/choukai/" . uniqid() . ".pdf";
            if (!move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . "/../" . $dest)) {
                $errors[] = "Gagal upload file PDF";
            } else {
                $file_path = $dest;
            }
        }
    }

    // Upload audio
    $audio_path = "";
    if (!empty($_FILES['audio']['name'])) {
        $ext = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
        if (!in_array($ext, ['mp3','wav'])) {
            $errors[] = "Audio harus berekstensi .mp3 atau .wav";
        } else {
            $dest = "uploads/choukai/" . uniqid() . "." . $ext;
            if (!move_uploaded_file($_FILES['audio']['tmp_name'], __DIR__ . "/../" . $dest)) {
                $errors[] = "Gagal upload audio";
            } else {
                $audio_path = $dest;
            }
        }
    }

    // Insert atau update DB
    if (!$errors) {
        if ($choukai_id > 0) {
            // Update
            $sql = "UPDATE choukai SET title=?, chapter_start=?, chapter_end=?, level=?";
            $params = [$title, $chapter_start, $chapter_end, $level];

            if ($file_path) { $sql .= ", file_path=?"; $params[] = $file_path; }
            if ($audio_path) { $sql .= ", audio_path=?"; $params[] = $audio_path; }

            $sql .= " WHERE id=?";
            $params[] = $choukai_id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $success = "Choukai berhasil diupdate";
        } else {
            // Insert
            $stmt = $pdo->prepare("
                INSERT INTO choukai (title, chapter_start, chapter_end, level, file_path, audio_path, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$title, $chapter_start, $chapter_end, $level, $file_path, $audio_path, $user['id']]);
            $success = "Choukai berhasil ditambahkan";
        }
    }
}

// Ambil daftar Choukai
$stmt = $pdo->query("SELECT * FROM choukai ORDER BY level, chapter_start");
$choukaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">🎧 Kelola Choukai</div>
    </div>

    <?php if ($errors): ?>
    <div style="color:red;">
        <ul>
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div style="color:green;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Form tambah/edit Choukai -->
    <form method="post" enctype="multipart/form-data" style="margin-top:12px;">
        <input type="hidden" name="choukai_id" value="">
        <div>
            <label>Judul: <input type="text" name="title" required></label>
        </div>
        <div>
            <label>Bab mulai: <input type="number" name="chapter_start" required></label>
            <label>Bab akhir: <input type="number" name="chapter_end" required></label>
        </div>
        <div>
            <label>Level:
                <select name="level" required>
                    <option value="N5">N5</option>
                    <option value="N4">N4</option>
                </select>
            </label>
        </div>
        <div>
            <label>PDF (opsional): <input type="file" name="file" accept="application/pdf"></label>
        </div>
        <div>
            <label>Audio (opsional): <input type="file" name="audio" accept="audio/*"></label>
        </div>
        <div style="margin-top:8px;">
            <button type="submit">Simpan Choukai</button>
        </div>
    </form>

    <!-- Daftar Choukai -->
    <h4 style="margin-top:24px;">Daftar Choukai</h4>
    <table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Judul</th>
                <th>Level</th>
                <th>Bab</th>
                <th>PDF</th>
                <th>Audio</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($choukaiList as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['title']) ?></td>
                <td><?= $c['level'] ?></td>
                <td><?= $c['chapter_start'] ?>–<?= $c['chapter_end'] ?></td>
                <td><?= $c['file_path'] ? '<a href="'.BASE_URL.$c['file_path'].'" target="_blank">Lihat</a>' : '-' ?>
                </td>
                <td><?= $c['audio_path'] ? '<a href="'.BASE_URL.$c['audio_path'].'" target="_blank">Dengar</a>' : '-' ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>