<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return !empty($_SESSION["user"]);
}

function currentUser() {
    return $_SESSION["user"] ?? null;
}

function requireRole(array $roles) {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
    $u = currentUser();
    if (!in_array($u["role"], $roles, true)) {
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
}

/**
 * Sanitasi input sederhana untuk teks dan array teks.
 */
function sanitize($input) {
    if (is_array($input)) {
        $result = [];
        foreach ($input as $k => $v) {
            $result[$k] = sanitize($v);
        }
        return $result;
    }
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, "UTF-8");
}

/**
 * Helper redirect sederhana.
 */
function redirect(string $path) {
    // jika path sudah absolut (mulai dengan http atau BASE_URL), kirim apa adanya
    if (preg_match("/^https?:\\/\\//", $path)) {
        header("Location: " . $path);
    } else {
        header("Location: " . BASE_URL . ltrim($path, "/"));
    }
    exit;
}
