<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);
global $pdo;

$lesson_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($lesson_id <= 0) {
    redirect("teacher/lessons.php");
}

// ambil level untuk redirect setelah hapus
$stmt = $pdo->prepare("SELECT level FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if ($lesson) {
    $level = $lesson["level"];
    // hapus lesson (foreign key di lesson_progress akan ikut ON DELETE CASCADE jika di-set)
    $del = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
    $del->execute([$lesson_id]);
} else {
    $level = "N5";
}

redirect("teacher/lessons.php?level={$level}");
