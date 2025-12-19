<?php
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/includes/functions.php";

if (isLoggedIn()) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$page_title = "Daftar Siswa Baru";

$errors = [];
$username = "";
$email = "";
$full_name = "";
$level = "N5";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pdo = getPDO();

    $username  = trim($_POST["username"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $full_name = trim($_POST["full_name"] ?? "");
    $level     = $_POST["level"] ?? "N5";
    $password  = $_POST["password"] ?? "";
    $password2 = $_POST["password_confirm"] ?? "";

    if ($username === "") $errors[] = "Username wajib diisi.";
    if ($email === "")    $errors[] = "Email wajib diisi.";
    if ($full_name === "")$errors[] = "Nama lengkap wajib diisi.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
    if ($password === "") $errors[] = "Password wajib diisi.";
    if ($password !== $password2) $errors[] = "Konfirmasi password tidak sama.";

    // cek username / email sudah dipakai
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
            VALUES (?, ?, ?, 'student', ?, ?)
        ");
        $stmt->execute([$username, $email, $hash, $full_name, $level]);

        header("Location: " . BASE_URL . "login.php?registered=1");
        exit;
    }
}

require __DIR__ . "/includes/header.php";
?>
<div class="card" style="max-width:520px;margin:32px auto;">
    <div class="card-header">
        <div class="card-title">Daftar sebagai Siswa Baru</div>
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
            <label>Level awal<br>
                <select name="level">
                    <option value="N5" <?php echo $level === "N5" ? "selected" : ""; ?>>N5</option>
                    <option value="N4" <?php echo $level === "N4" ? "selected" : ""; ?>>N4</option>
                </select>
            </label>
        </p>
        <p>
            <label>Password<br>
                <input type="password" name="password" required>
            </label>
        </p>
        <p>
            <label>Ulangi Password<br>
                <input type="password" name="password_confirm" required>
            </label>
        </p>
        <p>
            <button type="submit">Daftar</button>
            <a class="button secondary" href="<?php echo BASE_URL; ?>login.php">Kembali ke Login</a>
        </p>
    </form>
</div>
<?php require __DIR__ . "/includes/footer.php"; ?>
