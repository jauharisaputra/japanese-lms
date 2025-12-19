<?php
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/includes/functions.php";

$pdo = getPDO();

// jika sudah login, langsung ke dashboard masing-masing
if (function_exists("isLoggedIn") && isLoggedIn()) {
    $u = currentUser();
    if ($u["role"] === "admin" || $u["role"] === "teacher") {
        header("Location: " . BASE_URL . "teacher/dashboard.php");
    } else {
        header("Location: " . BASE_URL . "student/dashboard.php");
    }
    exit;
}

$error_message = "";
$success_message = "";

if (isset($_GET["registered"])) {
    $success_message = "Pendaftaran berhasil. Silakan login.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login   = trim($_POST["username"] ?? "");
    $pass    = $_POST["password"] ?? "";

    if ($login === "" || $pass === "") {
        $error_message = "Username/email dan password wajib diisi.";
    } else {
        // cari user berdasarkan username atau email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user["password"])) {
            // simpan ke session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION["user"] = [
                "id"        => $user["id"],
                "username"  => $user["username"],
                "email"     => $user["email"],
                "role"      => $user["role"],
                "full_name" => $user["full_name"],
                "level"     => $user["level"],
            ];

            // redirect sesuai role
            if ($user["role"] === "admin" || $user["role"] === "teacher") {
                header("Location: " . BASE_URL . "teacher/dashboard.php");
            } else {
                header("Location: " . BASE_URL . "student/dashboard.php");
            }
            exit;
        } else {
            $error_message = "Username/email atau password salah.";
        }
    }
}

$page_title = "Login";
require __DIR__ . "/includes/header.php";
?>
<div class="card" style="max-width:420px;margin:40px auto;">
    <div class="card-header">
        <div class="card-title">Masuk ke Nihongo Daichi Online</div>
    </div>
    <?php if ($success_message): ?>
        <p style="color:#2e7d32;"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <p style="color:#c62828;"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
    <form method="post" action="login.php">
        <p>
            <label>Username atau Email<br>
                <input type="text" name="username" required>
            </label>
        </p>
        <p>
            <label>Password<br>
                <input type="password" name="password" required>
            </label>
        </p>
        <p>
            <button type="submit">Masuk</button>
        </p>
        <p>
            Belum punya akun? <a href="<?php echo BASE_URL; ?>register.php">Daftar sebagai siswa baru</a>
        </p>
    </form>
</div>
<?php require __DIR__ . "/includes/footer.php"; ?>
