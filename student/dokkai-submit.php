<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$user = currentUser();
$pdo = getPDO();

$dokkai_id = (int)$_POST["dokkai_id"];
$answers   = $_POST["answers"] ?? [];

/* Ambil dokkai */
$stmt = $pdo->prepare("SELECT * FROM dokkai WHERE id = ?");
$stmt->execute([$dokkai_id]);
$dokkai = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dokkai) {
    die("Dokkai tidak valid");
}

/* Load JSON */
$jsonPath = __DIR__ . "/../" . $dokkai["question_file"];
$data = json_decode(file_get_contents($jsonPath), true);
$questions = $data["questions"];

$correct = 0;
$total = count($questions);

foreach ($questions as $i => $q) {
    if (isset($answers[$i]) && $answers[$i] == $q["correct"]) {
        $correct++;
    }
}

$score = round(($correct / $total) * 100);

/* Simpan hasil */
$stmt = $pdo->prepare("
    INSERT INTO dokkai_attempts (dokkai_id, user_id, score, started_at, finished_at)
    VALUES (?, ?, ?, NOW(), NOW())
");
$stmt->execute([$dokkai_id, $user["id"], $score]);

header("Location: dokkai-result.php?score=$score");
exit;