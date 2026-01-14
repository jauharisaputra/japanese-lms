<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['teacher','admin']);
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $level = $_POST['level'];
    $bab   = (int)$_POST['bab'];
    $time_limit = (int)$_POST['time_limit'];

    // === AUDIO UPLOAD ===
    if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
        die("Upload audio gagal");
    }

    $ext = strtolower(pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION));
    $allowed = ['mp3','wav','ogg'];

    if (!in_array($ext, $allowed)) {
        die("Format audio tidak didukung");
    }

    $uploadDir = __DIR__ . "/../uploads/choukai/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = uniqid('choukai_') . '.' . $ext;
    move_uploaded_file($_FILES['audio']['tmp_name'], $uploadDir . $filename);

    // === INSERT DATABASE ===
    $stmt = $pdo->prepare("
        INSERT INTO choukai 
        (title, level, bab, audio_file, time_limit, created_by)
        VALUES (?,?,?,?,?,?)
    ");
    $stmt->execute([
        $title,
        $level,
        $bab,
        $filename,
        $time_limit,
        currentUser()['id']
    ]);

    redirect(BASE_URL . "teacher/choukai.php?success=1");
}
?>

<h3>➕ Tambah Choukai</h3>

<form method="post" enctype="multipart/form-data">
    <div>
        <label>Judul</label>
        <input type="text" name="title" required>
    </div>

    <div>
        <label>Level</label>
        <select name="level" required>
            <option value="N5">N5</option>
            <option value="N4">N4</option>
        </select>
    </div>

    <div>
        <label>Bab</label>
        <select name="bab">
            <option value="1">Bab 1</option>
            <option value="2">Bab 2</option>
            <option value="3">Bab 3</option>
            <option value="4">Bab 4</option>
        </select>
    </div>

    <div>
        <label>Audio (mp3/wav)</label>
        <input type="file" name="audio" accept=".mp3,.wav,.ogg" required>
    </div>

    <div>
        <label>Waktu (menit)</label>
        <input type="number" name="time_limit" value="10">
    </div>

    <button type="submit">Simpan</button>
</form>
