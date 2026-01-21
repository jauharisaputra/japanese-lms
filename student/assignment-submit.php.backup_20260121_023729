<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$pdo = getPDO();

$assignment_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$user = currentUser();

$stmt = $pdo->prepare("SELECT * FROM assignments WHERE id = ?");
$stmt->execute([$assignment_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$assignment) {
    die("Tugas tidak ditemukan.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("
            INSERT INTO assignment_submissions (assignment_id, user_id, submitted_at)
            VALUES (?,?,NOW())
        ");
        $stmt->execute([$assignment_id, $user["id"]]);
        $submission_id = $pdo->lastInsertId();

        if (!empty($_FILES["files"]["name"][0])) {
            $baseDir = __DIR__ . "/../uploads/assignments";
            if (!is_dir($baseDir)) {
                mkdir($baseDir, 0777, true);
            }

            foreach ($_FILES["files"]["name"] as $idx => $name) {
                if ($_FILES["files"]["error"][$idx] !== UPLOAD_ERR_OK) continue;

                $tmp  = $_FILES["files"]["tmp_name"][$idx];
                $ext  = pathinfo($name, PATHINFO_EXTENSION);
                $safe = uniqid("a{$assignment_id}_u{$user["id"]}_") . "." . $ext;
                $dest = $baseDir . "/" . $safe;

                if (move_uploaded_file($tmp, $dest)) {
                    $relPath = "uploads/assignments/" . $safe;
                    $type    = mime_content_type($dest) ?: $ext;

                    $stmt = $pdo->prepare("
                        INSERT INTO assignment_files (submission_id, file_path, file_type)
                        VALUES (?,?,?)
                    ");
                    $stmt->execute([$submission_id, $relPath, $type]);
                }
            }
        }

        $pdo->commit();
        redirect("student/assignments.php");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Gagal mengirim tugas.");
    }
}

$page_title = "Kirim Tugas";
require __DIR__ . "/../includes/header.php";
?>
<div class="card">
  <div class="card-header">
    <div class="card-title">Kirim Tugas: <?php echo htmlspecialchars($assignment["title"]); ?></div>
  </div>
  <form method="post" enctype="multipart/form-data">
    <p><?php echo nl2br(htmlspecialchars($assignment["description"])); ?></p>
    <p>
      <label>File jawaban (bisa beberapa):<br>
        <input type="file" name="files[]" multiple>
      </label>
    </p>
    <p>
      <button type="submit">Kirim</button>
      <a class="button secondary" href="<?php echo BASE_URL; ?>student/assignments.php">Batal</a>
    </p>
  </form>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
