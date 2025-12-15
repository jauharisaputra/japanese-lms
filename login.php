<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = sanitize($_POST['username'] ?? '');
    $password        = $_POST['password'] ?? '';

    if ($usernameOrEmail === '' || $password === '') {
        $error = 'Username/email dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch();
       


        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['level']   = $user['level'] ?? 'N5';

            if ($user['role'] === 'student') {
                redirect('student/dashboard.php');
            } elseif ($user['role'] === 'teacher') {
                redirect('teacher/dashboard.php');
            } elseif ($user['role'] === 'admin') {
                redirect('admin/index.php');
            } else {
                redirect('login.php');
            }
        } else {
            $error = 'Username/email atau password salah.';
        }
    }
}

$page_title = 'Login - Japanese LMS';
require __DIR__ . '/includes/header.php';
?>
<div class="login-page">
    <div class="login-card">
        <h1>Japanese LMS</h1>
        <p>Masuk untuk mulai belajar JLPT N5-N4</p>

        <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <label>Username atau Email</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Masuk</button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>