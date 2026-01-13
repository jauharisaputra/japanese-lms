<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['student']);
$user = currentUser();
$pdo = getPDO();

$dokkai_id = (int)($_GET['id'] ?? 0);
if ($dokkai_id <= 0) die("Dokkai tidak valid.");

// Ambil dokkai
$stmt = $pdo->prepare("SELECT * FROM dokkai WHERE id = ?");
$stmt->execute([$dokkai_id]);
$dokkai = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$dokkai) die("Dokkai tidak ditemukan.");

// Ambil hasil siswa terbaru
$stmt = $pdo->prepare("
    SELECT * 
    FROM dokkai_results 
    WHERE dokkai_id = ? AND user_id = ? 
    ORDER BY submitted_at DESC 
    LIMIT 1
");
$stmt->execute([$dokkai_id, $user['id']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$result) die("Hasil dokkai belum tersedia.");

// Ambil semua soal
$stmt = $pdo->prepare("SELECT * FROM dokkai_questions WHERE dokkai_id = ? ORDER BY id");
$stmt->execute([$dokkai_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Decode jawaban siswa
$studentAnswers = json_decode($result['answers'], true) ?? [];

$page_title = "Hasil Dokkai";
require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">Hasil Dokkai</div>
    </div>

    <h3><?= htmlspecialchars($dokkai['title']) ?></h3>
    <p>Bab <?= $dokkai['chapter_start'] ?> – <?= $dokkai['chapter_end'] ?></p>
    <p>Skor: <?= $result['score'] ?> / <?= $result['total_questions'] ?></p>

    <?php foreach ($questions as $i => $q): 
        $qid = $q['id'];
        $correct = (int)$q['correct'];
        $studentAnswer = $studentAnswers[$qid] ?? null;
        $options = json_decode($q['options'], true);
    ?>
    <div style="margin-bottom:16px; padding:8px; border-bottom:1px solid #ccc;">
        <strong><?= ($i+1) ?>. <?= htmlspecialchars($q['question']) ?></strong>
        <ul>
            <?php foreach ($options as $idx => $opt): ?>
            <li <?php 
                    if ($idx === $correct) echo ' style="color:green;font-weight:bold;"';
                    if ($studentAnswer !== null && $idx === (int)$studentAnswer && $studentAnswer !== $correct) echo ' style="color:red;"';
                ?>>
                <?= htmlspecialchars($opt) ?>
                <?php 
                    if ($idx === $correct) echo " (Jawaban Benar)";
                    if ($studentAnswer !== null && $idx === (int)$studentAnswer && $studentAnswer !== $correct) echo " (Jawaban Anda)";
                ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endforeach; ?>

    <p><a href="dokkai.php">&laquo; Kembali ke daftar dokkai</a></p>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>