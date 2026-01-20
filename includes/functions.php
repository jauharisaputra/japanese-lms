<?php

/* ======================================================
   SESSION
====================================================== */

if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ======================================================
   AUTH
====================================================== */

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

/* ======================================================
   UTIL
====================================================== */

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

/* ======================================================
   KEAKTIFAN SISWA (FINAL – AMAN & PASTI INSERT)
====================================================== */

function logActivity($user_id, $type, $ref_id = null) {
    if (empty($user_id) || empty($type)) {
        return;
    }

    // Pastikan getPDO() tersedia (INI KUNCI FIX)
    if (!function_exists('getPDO')) {
        require_once __DIR__ . '/../config/config.php';
    }

    try {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            INSERT INTO student_activity_logs (user_id, activity_type, reference_id)
            VALUES (:user_id, :activity_type, :reference_id)
        ");

        $stmt->execute([
            ':user_id'       => $user_id,
            ':activity_type'=> $type,
            ':reference_id' => $ref_id
        ]);

    } catch (PDOException $e) {
    die("LOG ACTIVITY ERROR: " . $e->getMessage());
}

}