<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['student']);
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Metode request tidak valid.");
}

// Ambil data dari form
$choukai_id = $_POST['choukai_id'] ?? 0;
$answers = $_POST['answers'] ?? [];

if (!$choukai_id || empty($answers)) {
    die("Data jawaban tidak lengkap.");
}

$pdo = getPDO();

// Simpan jawaban dalam format JSON
$answers_json = json_encode($answers, JSON_UNESCAPED_UNICODE);

try {
    // Cek apakah siswa sudah pernah submit sebelumnya
    $stmt = $pdo->prepare("SELECT id FROM choukai_answers WHERE choukai_id=? AND user_id=?");
    $stmt->execute([$choukai_id, $user['id']]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update jawaban lama
        $stmt = $pdo->prepare("UPDATE choukai_answers SET answers=?, created_at=NOW() WHERE id=?");
        $stmt->execute([$answers_json, $existing['id']]);
    } else {
        // Insert jawaban baru
        $stmt = $pdo->prepare("INSERT INTO choukai_answers (choukai_id, user_id, answers) VALUES (?, ?, ?)");
        $stmt->execute([$choukai_id, $user['id'], $answers_json]);
    }

    // Redirect ke halaman Terima Kasih
    header("Location: " . BASE_URL . "student/choukai-thanks.php");
    exit;

} catch (PDOException $e) {
    die("Terjadi kesalahan saat menyimpan jawaban: " . $e->getMessage());
}