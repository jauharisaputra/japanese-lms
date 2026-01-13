<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['teacher','admin']);
$page_title = "Hasil Dokkai Siswa";
$pdo = getPDO();

// Filter level / dokkai (opsional)
$level = $_GET['level'] ?? '';
$dokkai_id = (int)($_GET['dokkai_id'] ?? 0);

// Ambil daftar dokkai
$stmt = $pdo->query("SELECT id, title, chapter_start, chapter_end, level FROM dokkai ORDER BY level, chapter_start");
$dokkaiList = $stmt->fetchAll();

// Ambil hasil siswa
$sql = "SELECT r.*, u.full_name AS student_name, d.title AS dokkai_title
        FROM dokkai_results r
        JOIN users u ON r.user_id = u.id
        JOIN dokkai d ON r.dokkai_id = d.id
        WHERE 1=1";


$params = [];
if ($level) {
    $sql .= " AND d.level = ?";
    $params[] = $level;
}
if ($dokkai_id) {
    $sql .= " AND d.id = ?";
    $params[] = $dokkai_id;
}

$sql .= " ORDER BY r.submitted_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">📊 Hasil Dokkai Siswa</div>
    </div>

    <form method="get" style="margin-bottom:12px;">
        <label>Level:
            <select name="level" onchange="this.form.submit()">
                <option value="">Semua</option>
                <option value="N5" <?= $level==='N5'?'selected':'' ?>>N5</option>
                <option value="N4" <?= $level==='N4'?'selected':'' ?>>N4</option>
            </select>
        </label>

        <label>Dokkai:
            <select name="dokkai_id" onchange="this.form.submit()">
                <option value="0">Semua</option>
                <?php foreach ($dokkaiList as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $d['id']==$dokkai_id?'selected':'' ?>>
                    <?= htmlspecialchars("Bab {$d['chapter_start']}–{$d['chapter_end']} - {$d['title']}") ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <p>
        <a href="dokkai-results-export.php?level=<?= urlencode($level) ?>&dokkai_id=<?= $dokkai_id ?>" class="button">
            Export ke CSV
        </a>
    </p>

    <table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Siswa</th>
                <th>Dokkai</th>
                <th>Score</th>
                <th>Total Soal</th>
                <th>Tanggal Submit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $i => $r): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($r['student_name']) ?></td>
                <td><?= htmlspecialchars($r['dokkai_title']) ?></td>
                <td><?= $r['score'] ?></td>
                <td><?= $r['total_questions'] ?></td>
                <td><?= $r['submitted_at'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$results): ?>
            <tr>
                <td colspan="6" style="text-align:center;">Belum ada hasil dokkai</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>