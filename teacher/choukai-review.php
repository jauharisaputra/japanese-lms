<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["teacher","admin"]);

$choukai_id = intval($_GET['choukai_id'] ?? 0);
$user_id    = intval($_GET['user_id'] ?? 0);

if ($choukai_id <= 0 || $user_id <= 0) {
    redirect(BASE_URL . "/teacher/dashboard.php");
    exit;
}

/**
 * Ambil hasil choukai siswa
 */
$stmt = $pdo->prepare("
    SELECT r.*, u.name AS student_name, c.title
    FROM choukai_results r
    JOIN users u ON u.id = r.user_id
    JOIN choukai c ON c.id = r.choukai_id
    WHERE r.choukai_id = ? AND r.user_id = ?
    LIMIT 1
");
$stmt->execute([$choukai_id, $user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    die("Data choukai siswa tidak ditemukan.");
}

/**
 * Decode jawaban siswa
 */
$answers = [];
if (!empty($result['answers_json'])) {
    $answers = json_decode($result['answers_json'], true);
}

/**
 * Ambil soal + kunci
 */
$stmt = $pdo->prepare("
    SELECT question_no, correct_option
    FROM choukai_questions
    WHERE choukai_id = ?
    ORDER BY question_no ASC
");
$stmt->execute([$choukai_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Review Choukai";
require __DIR__ . "/../includes/header.php";
?>

<div class="container">
    <h2>Review Choukai</h2>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Siswa:</strong> <?= htmlspecialchars($result['student_name']) ?></p>
            <p><strong>Choukai:</strong> <?= htmlspecialchars($result['title']) ?></p>
            <p><strong>Skor:</strong> <?= $result['score'] ?></p>
            <p>
                <strong>Benar:</strong>
                <?= $result['correct_count'] ?> /
                <?= $result['total_questions'] ?>
            </p>
            <p>
                <strong>Waktu submit:</strong>
                <?= date("d M Y H:i", strtotime($result['submitted_at'])) ?>
            </p>
        </div>
    </div>

    <h4>Detail Jawaban</h4>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Jawaban Siswa</th>
                <th>Kunci</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($questions as $q): 
                $no = $q['question_no'];
                $studentAnswer = $answers[$no] ?? '-';
                $isCorrect = ($studentAnswer == $q['correct_option']);
            ?>
            <tr class="<?= $isCorrect ? 'table-success' : 'table-danger' ?>">
                <td><?= $no ?></td>
                <td><?= htmlspecialchars($studentAnswer) ?></td>
                <td><?= htmlspecialchars($q['correct_option']) ?></td>
                <td><?= $isCorrect ? '✔ Benar' : '✘ Salah' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="<?= BASE_URL ?>/teacher/choukai-results.php?choukai_id=<?= $choukai_id ?>" class="btn btn-secondary">
        Kembali
    </a>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>