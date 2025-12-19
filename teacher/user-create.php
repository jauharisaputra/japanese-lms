<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["admin","teacher"]);
$page_title = "Tambah Pengguna";
require __DIR__ . "/../includes/header.php";

$pdo = getPDO();
$errors = [];
$username = "";
$email = "";
$full_name = "";
$level = "N5";
$role = "student";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username  = trim($_POST["username"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $full_name = trim($_POST["full_name"] ?? "");
    $level     = $_POST["level"] ?? "N5";
    $role      = $_POST["role"] ?? "student";
    $password  = $_POST["password"] ?? "";

    if ($username === "") $errors[] = "Username wajib diisi.";
    if ($email === "")    $errors[] = "Email wajib diisi.";
    if ($full_name === "")$errors[] = "Nama lengkap wajib diisi.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
    if ($password === "") $errors[] = "Password wajib diisi.";
    if (!in_array($role, ["student","teacher","admin"], true)) $errors[] = "Role tidak valid.";

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username atau email sudah terdaftar.";
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role, full_name, level)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$username, $email, $hash, $role, $full_name, $level]);
        $success = true;
    }
}
?>
<div class="card" style="max-width:520px;margin:32px auto;">
    <div class="card-header">
        <div class="card-title">Tambah Pengguna (Siswa / Guru)</div>
    </div>

    <?php if (!empty($success)): ?>
        <p style="color:#2e7d32;">Pengguna baru berhasil dibuat.</p>
    <?php endif; ?>

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
            <label>Username<br>
                <input type="text" name="username" required
                       value="<?php echo htmlspecialchars($username); ?>">
            </label>
        </p>
        <p>
            <label>Email<br>
                <input type="email" name="email" required
                       value="<?php echo htmlspecialchars($email); ?>">
            </label>
        </p>
        <p>
            <label>Level (untuk siswa)<br>
                <select name="level">
                    <option value="N5" <?php echo $level === "N5" ? "selected" : ""; ?>>N5</option>
                    <option value="N4" <?php echo $level === "N4" ? "selected" : ""; ?>>N4</option>
                </select>
            </label>
        </p>
        <p>
            <label>Role<br>
                <select name="role">
                    <option value="student" <?php echo $role === "student" ? "selected" : ""; ?>>Siswa</option>
                    <option value="teacher" <?php echo $role === "teacher" ? "selected" : ""; ?>>Guru</option>
                    <option value="admin"   <?php echo $role === "admin" ? "selected" : ""; ?>>Admin</option>
                </select>
            </label>
        </p>
        <p>
            <label>Password<br>
                <input type="password" name="password" required>
            </label>
        </p>
        <p>
            <button type="submit">Simpan pengguna</button>
        </p>
    </form>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
