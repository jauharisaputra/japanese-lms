<?php
// =====================
// DEBUG (boleh hapus jika sudah stabil)
// =====================
ini_set('display_errors', 1);
error_reporting(E_ALL);

// =====================
// CONFIG & AUTH
// =====================
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['teacher','admin']);
$user = currentUser();
$pdo  = getPDO();

$page_title = "Kelola Choukai";

// =====================
// HAPUS CHOUKAI
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];

    $stmt = $pdo->prepare("SELECT * FROM choukai WHERE id=?");
    $stmt->execute([$delete_id]);
    $c = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($c) {
        if ($c['file_path'] && file_exists(__DIR__."/../".$c['file_path'])) {
            unlink(__DIR__."/../".$c['file_path']);
        }
        if ($c['audio_path'] && file_exists(__DIR__."/../".$c['audio_path'])) {
            unlink(__DIR__."/../".$c['audio_path']);
        }

        $pdo->prepare("DELETE FROM choukai_answers WHERE choukai_id=?")->execute([$delete_id]);
        $pdo->prepare("DELETE FROM choukai WHERE id=?")->execute([$delete_id]);
    }

    $_SESSION['success'] = "Choukai berhasil dihapus";
    header("Location: choukai.php");
    exit;
}

// =====================
// FORM SUBMIT (TAMBAH / EDIT)
// =====================
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id'])) {

    $title         = trim($_POST['title'] ?? '');
    $chapter_start = (int)($_POST['chapter_start'] ?? 0);
    $chapter_end   = (int)($_POST['chapter_end'] ?? 0);
    $level         = $_POST['level'] ?? '';
    $choukai_id    = (int)($_POST['choukai_id'] ?? 0);

    // =====================
    // VALIDASI DASAR
    // =====================
    if ($title === '') $errors[] = "Judul wajib diisi";
    if ($chapter_start <= 0 || $chapter_end <= 0) $errors[] = "Bab harus valid";
    if ($chapter_end < $chapter_start) $errors[] = "Bab akhir harus ≥ bab mulai";
    if (!in_array($level, ['N5','N4'])) $errors[] = "Level tidak valid";

    // =====================
    // KUNCI JAWABAN
    // =====================
    $numbers = $_POST['number'] ?? [];
    $letters = $_POST['letter'] ?? [];
    $oxs     = $_POST['ox'] ?? [];
    $texts   = $_POST['text'] ?? [];

    if (count($numbers) === 0) {
        $errors[] = "Minimal harus ada 1 soal kunci jawaban";
    }

    $answers_correct = [];
    for ($i=0; $i<count($numbers); $i++) {
        $answers_correct[] = [
            'number' => trim($numbers[$i]),
            'letter' => trim($letters[$i]),
            'ox'     => trim($oxs[$i]),
            'text'   => trim($texts[$i]),
        ];
    }

    $answers_json = json_encode($answers_correct, JSON_UNESCAPED_UNICODE);

    // =====================
    // LOAD DATA LAMA (UNTUK EDIT)
    // =====================
    $old = null;
    if ($choukai_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM choukai WHERE id=?");
        $stmt->execute([$choukai_id]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $file_path  = $old['file_path']  ?? '';
    $audio_path = $old['audio_path'] ?? '';

    // =====================
    // UPLOAD PDF
    // =====================
    if (!empty($_FILES['file']['name'])) {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        if ($ext !== 'pdf') {
            $errors[] = "File PDF harus .pdf";
        } else {
            $dest = "uploads/choukai/".uniqid().".pdf";
            move_uploaded_file($_FILES['file']['tmp_name'], __DIR__."/../".$dest);
            $file_path = $dest;
        }
    }

    // =====================
    // UPLOAD AUDIO
    // =====================
    if (!empty($_FILES['audio']['name'])) {
        $ext = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
        if (!in_array($ext, ['mp3','wav'])) {
            $errors[] = "Audio harus mp3 / wav";
        } else {
            $dest = "uploads/choukai/".uniqid().".".$ext;
            move_uploaded_file($_FILES['audio']['tmp_name'], __DIR__."/../".$dest);
            $audio_path = $dest;
        }
    }

    // =====================
    // SIMPAN KE DB
    // =====================
    if (!$errors) {
        if ($choukai_id > 0) {
            $stmt = $pdo->prepare("
                UPDATE choukai SET
                title=?, chapter_start=?, chapter_end=?, level=?,
                file_path=?, audio_path=?, answers_correct=?
                WHERE id=?
            ");
            $stmt->execute([
                $title,$chapter_start,$chapter_end,$level,
                $file_path,$audio_path,$answers_json,$choukai_id
            ]);
            $_SESSION['success'] = "Choukai berhasil diupdate";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO choukai
                (title,chapter_start,chapter_end,level,file_path,audio_path,created_by,created_at,answers_correct)
                VALUES (?,?,?,?,?,?,?,NOW(),?)
            ");
            $stmt->execute([
                $title,$chapter_start,$chapter_end,$level,
                $file_path,$audio_path,$user['id'],$answers_json
            ]);
            $_SESSION['success'] = "Choukai berhasil ditambahkan";
        }

        header("Location: choukai.php");
        exit;
    }
}

// =====================
// DATA
// =====================
$choukaiList = $pdo->query("SELECT * FROM choukai ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$selected = null;

if (isset($_GET['id'])) {
    foreach ($choukaiList as $c) {
        if ($c['id'] == (int)$_GET['id']) $selected = $c;
    }
}

require __DIR__ . "/../includes/header.php";
?>

<div class="card">
<div class="card-header"><div class="card-title">🎧 Kelola Choukai</div></div>

<?php if ($errors): ?>
<div style="color:red"><ul><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach ?></ul></div>
<?php endif ?>

<?php if (!empty($_SESSION['success'])): ?>
<div style="color:green"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif ?>

<!-- FORM -->
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="choukai_id" value="<?= $selected['id'] ?? '' ?>">

<label>Judul <input name="title" value="<?= htmlspecialchars($selected['title'] ?? '') ?>"></label><br>
<label>Bab <input name="chapter_start" type="number" value="<?= $selected['chapter_start'] ?? '' ?>"> -
<input name="chapter_end" type="number" value="<?= $selected['chapter_end'] ?? '' ?>"></label><br>

<select name="level">
<option value="N5" <?= ($selected['level']??'')=='N5'?'selected':'' ?>>N5</option>
<option value="N4" <?= ($selected['level']??'')=='N4'?'selected':'' ?>>N4</option>
</select><br><br>

<h4>Kunci Jawaban</h4>
<table id="answersTable" border="1">
<tbody>
<?php
$ans = json_decode($selected['answers_correct'] ?? '[]', true);
foreach ($ans as $i=>$a):
?>
<tr>
<td><?= $i+1 ?></td>
<td><input name="number[]" value="<?= $a['number'] ?>"></td>
<td><input name="letter[]" value="<?= $a['letter'] ?>"></td>
<td>
<select name="ox[]">
<option value="">-</option>
<option value="O" <?= $a['ox']=='O'?'selected':'' ?>>O</option>
<option value="X" <?= $a['ox']=='X'?'selected':'' ?>>X</option>
</select>
</td>
<td><input name="text[]" value="<?= $a['text'] ?>"></td>
</tr>
<?php endforeach ?>
</tbody>
</table>

<button type="submit">Simpan</button>
</form>

<hr>

<h4>Daftar Choukai</h4>
<?php foreach ($choukaiList as $c): ?>
<form method="post" style="display:inline">
<?= htmlspecialchars($c['title']) ?>
<a href="?id=<?= $c['id'] ?>">Edit</a>
<input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
<button onclick="return confirm('Hapus?')">Hapus</button>
</form><br>
<?php endforeach ?>

</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>
