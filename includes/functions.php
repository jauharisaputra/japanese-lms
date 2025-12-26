<?php

if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
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

function redirect(string $path) {
    if (preg_match("/^https?:\\/\\//", $path)) {
        header("Location: " . $path);
    } else {
        header("Location: " . BASE_URL . ltrim($path, "/"));
    }
    exit;
}
