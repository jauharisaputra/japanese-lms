<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['teacher','admin']);
$page_title = "Hasil choukai Siswa";
$pdo = getPDO();

// Filter level / choukai (opsional)
$level = $_GET['level'] ?? '';
$choukai_id = (int)($_GET['choukai_id'] ?? 0);

// Ambil daftar choukai
$stmt = $pdo->query("SELECT id, title, chapter_start, chapter_end, level FROM choukai ORDER BY level, chapter_start");
$choukaiList = $stmt->fetchAll();

// Ambil hasil siswa
$sql = "SELECT r.*, u.full_name AS student_name, d.title AS choukai_title
        FROM choukai_results r
        JOIN users u ON r.user_id = u.id
        JOIN choukai d ON r.choukai_id = d.id
        WHERE 1=1";


$params = [];
if ($level) {
    $sql .= " AND d.level = ?";
    $params[] = $level;
}
if ($choukai_id) {
    $sql .= " AND d.id = ?";
    $params[] = $choukai_id;
}

$sql .= " ORDER BY r.submitted_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">📊 Hasil choukai Siswa</div>
    </div>

    <form method="get" style="margin-bottom:12px;">
        <label>Level:
            <select name="level" onchange="this.form.submit()">
                <option value="">Semua</option>
                <option value="N5" <?= $level==='N5'?'selected':'' ?>>N5</option>
                <option value="N4" <?= $level==='N4'?'selected':'' ?>>N4</option>
            </select>
        </label>

        <label>choukai:
            <select name="choukai_id" onchange="this.form.submit()">
                <option value="0">Semua</option>
                <?php foreach ($choukaiList as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $d['id']==$choukai_id?'selected':'' ?>>
                    <?= htmlspecialchars("Bab {$d['chapter_start']}–{$d['chapter_end']} - {$d['title']}") ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <p>
        <a href="choukai-results-export.php?level=<?= urlencode($level) ?>&choukai_id=<?= $choukai_id ?>" class="button">
            Export ke CSV
        </a>
    </p>

    <table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Siswa</th>
                <th>choukai</th>
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
                <td><?= htmlspecialchars($r['choukai_title']) ?></td>
                <td><?= $r['score'] ?></td>
                <td><?= $r['total_questions'] ?></td>
                <td><?= $r['submitted_at'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$results): ?>
            <tr>
                <td colspan="6" style="text-align:center;">Belum ada hasil choukai</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>
