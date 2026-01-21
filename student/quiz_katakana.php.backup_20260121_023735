<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$user = currentUser();
$pdo  = getPDO();

// pakai quiz_id yang sudah ada di tabel quizzes
$quizId = 20;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $answers = $_POST["answer"] ?? [];
    $ids     = array_keys($answers);
    $score   = 0;
    $total   = count($ids);

    if ($total > 0) {
        $placeholders = implode(",", array_fill(0, $total, "?"));
        $stmt = $pdo->prepare("SELECT id, romaji FROM kana_chars WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $map[$row["id"]] = $row["romaji"];
        }

        foreach ($answers as $id => $ans) {
            $id  = (int)$id;
            $ans = trim(mb_strtolower($ans, "UTF-8"));
            if (isset($map[$id]) && $ans === mb_strtolower($map[$id], "UTF-8")) {
                $score++;
            }
        }
    }

    $scorePercent = $total > 0 ? round($score / $total * 100, 2) : 0;

    $ins = $pdo->prepare("
    INSERT INTO quiz_attempts (user_id, quiz_id, score, total_questions)
    VALUES (?, ?, ?, ?)
");
$ins->execute([$user["id"], 20, $scorePercent, $total]);

    $page_title = "Hasil Kuis Katakana";
    require __DIR__ . "/../includes/header.php";
    ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Hasil Kuis Katakana</div>
    </div>
    <div class="card-body">
        <p>Skor Anda: <strong><?= htmlspecialchars($scorePercent) ?></strong> / 100 (<?= $score ?> dari <?= $total ?> benar).
        </p>
        <p>
            <a href="<?= BASE_URL ?>student/quiz_hiragana.php">Ulangi kuis</a> |
            <a href="<?= BASE_URL ?>student/dashboard.php">Kembali ke Dashboard</a>
        </p>
    </div>
</div>
<?php
    require __DIR__ . "/../includes/footer.php";
    exit;
}

$stmt = $pdo->query("
    SELECT id, char_symbol, romaji
    FROM kana_chars
    WHERE script = 'katakana'
    ORDER BY RAND()
    LIMIT 10
");
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Kuis Katakana Dasar";
require __DIR__ . "/../includes/header.php";
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Kuis Katakana Dasar</div>
    </div>
    <div class="card-body">
        <p>Tulislah romaji yang benar untuk setiap huruf katakana berikut.</p>
        <form method="post">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Hiragana</th>
                        <th>Jawaban (romaji)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $i => $q): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td style="font-size:32px;"><?= htmlspecialchars($q["char_symbol"]) ?></td>
                        <td>
                            <input type="text" name="answer[<?= (int)$q["id"] ?>]" class="form-control"
                                autocomplete="off">
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






\->execute([
    \['id'],
    20,
    \,
    count(\)
]);






