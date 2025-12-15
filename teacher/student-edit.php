<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
global $pdo;

$student_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($student_id <= 0) {
    redirect("teacher/students.php");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$student) {
    redirect("teacher/students.php");
}

$page_title = "Edit Siswa / Kelas";
require __DIR__ . "/../includes/header.php";

$errors = [];
$full_name = $student["full_name"];
$email     = $student["email"];
$level     = $student["level"];
$class_id  = $student["class_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $level     = $_POST["level"] ?? "N5";
    $class_id  = $_POST["class_id"] !== "" ? (int)$_POST["class_id"] : null;

    if ($full_name === "") $errors[] = "Nama lengkap wajib diisi.";
    if ($email === "")     $errors[] = "Email wajib diisi.";

    if (!$errors) {
        $stmt = $pdo->prepare("
            UPDATE users
               SET full_name = ?, email = ?, level = ?, class_id = ?
             WHERE id = ? AND role = 'student'
        ");
        $stmt->execute([$full_name, $email, $level, $class_id, $student_id]);
        redirect("teacher/students.php");
    }
}

$classes = $pdo->query("SELECT id, name, level FROM classes ORDER BY level, name")
               ->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card" style="max-width:600px;margin:24px auto;">
    <div class="card-header">
        <div class="card-title">Edit Siswa / Kelas</div>
    </div>

    <?php if ($errors): ?>
        <ul style="color:#c62828;">
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <p>
            <label>Nama lengkap<br>
                <input type="text" name="full_name" required
                       value="<?php echo htmlspecialchars($full_name); ?>">
            </label>
        </p>
        <p>
            <label>Email<br>
                <input type="email" name="email" required
                       value="<?php echo htmlspecialchars($email); ?>">
            </label>
        </p>
        <p>
            <label>Level<br>
                <select name="level">
                    <option value="N5" <?php echo $level === "N5" ? "selected" : ""; ?>>N5</option>
                    <option value="N4" <?php echo $level === "N4" ? "selected" : ""; ?>>N4</option>
                </select>
            </label>
        </p>
        <p>
            <label>Kelas<br>
                <select name="class_id">
                    <option value="">(Belum dikelompokkan)</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo (int)$c["id"]; ?>"
                            <?php echo ($class_id == $c["id"]) ? "selected" : ""; ?>>
                            [<?php echo htmlspecialchars($c["level"]); ?>]
                            <?php echo htmlspecialchars($c["name"]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
        <p>
            <button type="submit">Simpan</button>
            <a class="button secondary" href="<?php echo BASE_URL; ?>teacher/students.php">Batal</a>
        </p>
    </form>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
