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
