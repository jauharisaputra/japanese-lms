<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    $role = $_SESSION['role'] ?? 'student';
    if ($role === 'student') {
        redirect('student/dashboard.php');
    } elseif ($role === 'teacher') {
        redirect('teacher/dashboard.php');
    } elseif ($role === 'admin') {
        redirect('admin/index.php');
    }
}

redirect('login.php');
?>
