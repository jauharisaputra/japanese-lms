<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$user = currentUser();
$pdo  = getPDO();

// pakai quiz_id yang sudah ada di tabel quizzes

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ansRomaji  = $_POST["answer_romaji"]  ?? [];
    $ansMeaning = $_POST["answer_meaning"] ?? [];

    $ids        = array_unique(array_merge(array_keys($ansRomaji), array_keys($ansMeaning)));
    $score      = 0;
    $totalKanji = count($ids);

    if ($totalKanji > 0) {
    $placeholders = implode(",", array_fill(0, $totalKanji, "?"));
    $stmt = $pdo->prepare("
        SELECT id, romaji, meaning_id
        FROM kanji_chars
        WHERE id IN ($placeholders)
    ");
    $stmt->execute($ids);
    $map = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $map[$row["id"]] = $row;
    }

    foreach ($ids as $id) {
        $id = (int)$id;
        $userRomaji  = isset($ansRomaji[$id])  ? trim(mb_strtolower($ansRomaji[$id], "UTF-8"))  : "";
        $userMeaning = isset($ansMeaning[$id]) ? trim(mb_strtolower($ansMeaning[$id], "UTF-8")) : "";

        $correctRomaji  = isset($map[$id]["romaji"])     ? mb_strtolower($map[$id]["romaji"], "UTF-8")     : "";
        $correctMeaning = isset($map[$id]["meaning_id"]) ? mb_strtolower($map[$id]["meaning_id"], "UTF-8") : "";

        if ($userRomaji !== "" && $userRomaji === $correctRomaji) {
            $score++;
        }
        if ($userMeaning !== "" && $userMeaning === $correctMeaning) {
            $score++;
        }
    }
}


   $maxScore = $totalKanji * 2;
$percent  = $maxScore > 0 ? round($score / $maxScore * 100, 2) : 0;


    $ins = $pdo->prepare("
        INSERT INTO quiz_attempts (user_id, quiz_id, score, total_questions)
        VALUES (?, ?, ?)
    ");

    $page_title = "Hasil Kuis Kanji N5";
    require __DIR__ . "/../includes/header.php";
    ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Hasil Kuis Kanji N5</div>
    </div>
    <div class="card-body">
        <p>Skor Anda: <strong><?= htmlspecialchars($percent) ?></strong> / 100
            (<?= $score ?> dari <?= $totalKanji * 2 ?> poin maksimal, <?= $totalKanji ?> kanji).
        </p>

        <p>
            <a href="<?= BASE_URL ?>student/quiz_kanji_n4.php">Ulangi kuis</a> |
            <a href="<?= BASE_URL ?>student/dashboard.php">Kembali ke Dashboard</a>
        </p>
    </div>
</div>
<?php
    require __DIR__ . "/../includes/footer.php";
    exit;
}

$stmt = $pdo->query("
    SELECT id, kanji, meaning_id
    FROM kanji_chars
    WHERE level = 'N5'
    ORDER BY RAND()
    LIMIT 10
");
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Kuis Kanji N5";
require __DIR__ . "/../includes/header.php";
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Kuis Kanji N5</div>
    </div>
    <div class="card-body">
        <p>Tulislah romaji dan arti dalam bahasa Indonesia untuk setiap kanji N5 berikut.</p>
        <form method="post">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>kanji N5</th>
                        <th>Jawaban (romaji)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $i => $q): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td style="font-size:32px;"><?= htmlspecialchars($q["kanji"]) ?></td>
                        <td>
                            <input type="text" name="answer_romaji[<?= (int)$q["id"] ?>]">
                            <input type="text" name="answer_meaning[<?= (int)$q["id"] ?>]">

                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Kumpulkan Jawaban</button>
        </form>
    </div>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>





