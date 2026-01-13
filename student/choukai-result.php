<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['student']);
$user = currentUser();
$pdo = getPDO();

$choukai_id = (int)($_GET['id'] ?? 0);
if ($choukai_id <= 0) die("choukai tidak valid.");

// Ambil choukai
$stmt = $pdo->prepare("SELECT * FROM choukai WHERE id = ?");
$stmt->execute([$choukai_id]);
$choukai = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$choukai) die("choukai tidak ditemukan.");

// Ambil hasil siswa terbaru
$stmt = $pdo->prepare("
    SELECT * 
    FROM choukai_results 
    WHERE choukai_id = ? AND user_id = ? 
    ORDER BY submitted_at DESC 
    LIMIT 1
");
$stmt->execute([$choukai_id, $user['id']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$result) die("Hasil choukai belum tersedia.");

// Ambil semua soal
$stmt = $pdo->prepare("SELECT * FROM choukai_questions WHERE choukai_id = ? ORDER BY id");
$stmt->execute([$choukai_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Decode jawaban siswa
$studentAnswers = json_decode($result['answers'], true) ?? [];

$page_title = "Hasil choukai";
require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">Hasil choukai</div>
    </div>

    <h3><?= htmlspecialchars($choukai['title']) ?></h3>
    <p>Bab <?= $choukai['chapter_start'] ?> – <?= $choukai['chapter_end'] ?></p>
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
            <li
                <?php 
                    if ($idx === $correct) echo ' style="color:green;font-weight:bold;"';
                    if ($studentAnswer !== null && $idx === (int)$studentAnswer && $studentAnswer !== $correct) echo ' style="color:red;"';
                ?>
            >
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

    <p><a href="choukai.php">&laquo; Kembali ke daftar choukai</a></p>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>
