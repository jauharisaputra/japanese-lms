<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['teacher','admin']);
$user = currentUser();
$pdo  = getPDO();

$page_title = "Upload Choukai";
require __DIR__ . '/../includes/header.php';

/* Mapping bab per level */
$babMap = [
    'N5' => [
        [1,4],[5,8],[9,12],[13,16],[17,20],[21,22]
    ],
    'N4' => [
        [23,26],[27,30],[31,34],[35,38],[39,42]
    ]
];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Validasi awal */
    if (
        empty($_POST['level']) ||
        empty($_POST['bab_range']) ||
        empty($_POST['title']) ||
        empty($_FILES['audio']['name']) ||
        empty($_FILES['pdf']['name'])
    ) {
        $error = "Semua field wajib diisi dan ukuran file tidak boleh melebihi batas server.";
    } else {

        $level = $_POST['level'];
        $title = trim($_POST['title']);

        if (!str_contains($_POST['bab_range'], '-')) {
            $error = "Format bab tidak valid.";
        } else {

            [$bab_start, $bab_end] = explode('-', $_POST['bab_range']);

            /* Folder upload */
            $audioDir = __DIR__ . '/../uploads/choukai/audio/';
            $pdfDir   = __DIR__ . '/../uploads/choukai/pdf/';

            if (!is_dir($audioDir)) mkdir($audioDir, 0777, true);
            if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);

            /* Nama file aman */
            $audioName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['audio']['name']);
            $pdfName   = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['pdf']['name']);

            $audioPath = $audioDir . $audioName;
            $pdfPath   = $pdfDir . $pdfName;

            if (
                move_uploaded_file($_FILES['audio']['tmp_name'], $audioPath) &&
                move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfPath)
            ) {

                $stmt = $pdo->prepare("
                    INSERT INTO choukai_materials
                    (level, bab_start, bab_end, title, audio_path, pdf_path, created_by)
                    VALUES (?,?,?,?,?,?,?)
                ");
                $stmt->execute([
                    $level,
                    (int)$bab_start,
                    (int)$bab_end,
                    $title,
                    'uploads/choukai/audio/' . $audioName,
                    'uploads/choukai/pdf/' . $pdfName,
                    $user['id']
                ]);

                $success = "Choukai berhasil diupload 🎧";
            } else {
                $error = "Gagal upload file. Periksa permission folder uploads.";
            }
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">Upload Choukai</div>
    </div>
    <div class="card-body">

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label">Level</label>
                <select name="level" id="level" class="form-select" onchange="updateBab()" required>
                    <option value="">-- Pilih Level --</option>
                    <option value="N5">N5</option>
                    <option value="N4">N4</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Bab (Per 4 Bab)</label>
                <select name="bab_range" id="bab" class="form-select" required></select>
            </div>

            <div class="mb-3">
                <label class="form-label">Judul Choukai</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Audio Choukai</label>
                <input type="file" name="audio" class="form-control" accept="audio/*" required>
            </div>

            <div class="mb-3">
                <label class="form-label">PDF Script / Soal</label>
                <input type="file" name="pdf" class="form-control" accept="application/pdf" required>
            </div>

            <button class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>

<script>
const babData = <?= json_encode($babMap); ?>;

function updateBab() {
    const level = document.getElementById('level').value;
    const babSelect = document.getElementById('bab');
    babSelect.innerHTML = '';

    if (!babData[level]) return;

    babData[level].forEach(b => {
        const opt = document.createElement('option');
        opt.value = b[0] + '-' + b[1];
        opt.textContent = 'Bab ' + b[0] + ' - ' + b[1];
        babSelect.appendChild(opt);
    });
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>