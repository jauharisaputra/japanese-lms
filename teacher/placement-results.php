<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/../includes/placement.php";

requireRole(["teacher", "admin"]);
$pdo = getPDO();

/* ================= CSRF TOKEN ================= */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ================= DATA UJIAN ================= */
$exam_id = isset($_GET["exam_id"]) ? (int)$_GET["exam_id"] : 0;

$stmt = $pdo->prepare("SELECT * FROM placement_exams WHERE id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exam) {
    die("Ujian TO tidak ditemukan.");
}

$sections = json_decode($exam["sections_json"], true) ?: [];

/* ================= RESET PER ATTEMPT ================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reset_attempt"])) {

    if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])) {
        die("CSRF token tidak valid");
    }

    $attempt_id = (int)$_POST["attempt_id"];

    $stmt = $pdo->prepare("
        DELETE FROM placement_attempts
        WHERE id = ? AND exam_id = ?
    ");
    $stmt->execute([$attempt_id, $exam_id]);

    $_SESSION["flash_success"] = "Attempt berhasil di-reset.";
    header("Location: placement-results.php?exam_id=" . $exam_id);
    exit;
}

/* ================= DATA ATTEMPT ================= */
$stmt = $pdo->prepare("
    SELECT pa.*, u.full_name, u.username
    FROM placement_attempts pa
    JOIN users u ON pa.user_id = u.id
    WHERE pa.exam_id = ?
    ORDER BY u.full_name, pa.attempt_no
");
$stmt->execute([$exam_id]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= GROUP PER SISWA ================= */
$grouped = [];
foreach ($attempts as $a) {
    $grouped[$a["user_id"]]["user"] = $a;
    $grouped[$a["user_id"]]["attempts"][] = $a;
}

$page_title = "Hasil Ujian TO";
require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            Hasil Ujian TO: <?= htmlspecialchars($exam["name"]); ?>
        </div>
    </div>

    <div class="card-body">

        <?php if (!empty($_SESSION["flash_success"])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION["flash_success"]); ?>
        </div>
        <?php unset($_SESSION["flash_success"]); ?>
        <?php endif; ?>

        <?php if (!$grouped): ?>
        <p>Belum ada attempt.</p>
        <?php else: ?>

        <?php foreach ($grouped as $user_id => $data): 
            $user = $data["user"];
        ?>
        <div class="card mb-3">
            <div class="card-header">
                <strong><?= htmlspecialchars($user["full_name"] ?? $user["username"]); ?></strong>
            </div>

            <div class="card-body p-0">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Attempt</th>
                            <th>Total</th>
                            <th>Status</th>
                            <?php foreach ($sections as $key => $max): ?>
                            <th><?= htmlspecialchars($key); ?> (<?= (int)$max; ?>)</th>
                            <?php endforeach; ?>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data["attempts"] as $a): 
                            $raw = json_decode($a["section_scores_json"], true) ?: [];
                        ?>
                        <tr>
                            <td><?= (int)$a["attempt_no"]; ?></td>
                            <td><?= (int)$a["total_score"]; ?></td>
                            <td><?= $a["passed"] ? "Lulus" : "Belum lulus"; ?></td>

                            <?php foreach ($sections as $key => $max): ?>
                            <td><?= $raw[$key] ?? 0; ?></td>
                            <?php endforeach; ?>

                            <td style="text-align:center">
                                <form method="post" onsubmit="return confirm('Reset attempt ini saja?');"
                                    style="display:inline">
                                    <input type="hidden" name="csrf_token"
                                        value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="attempt_id" value="<?= (int)$a['id']; ?>">
                                    <button type="submit" name="reset_attempt" class="btn btn-sm btn-danger">
                                        🗑 Reset
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>

        <?php endif; ?>

    </div>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>