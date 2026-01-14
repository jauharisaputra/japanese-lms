<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(['teacher','admin']);
$pdo = getPDO();
$page_title = "Hasil Choukai Siswa";

// Filter level dan choukai
$level = $_GET['level'] ?? '';
$choukai_id = (int)($_GET['choukai_id'] ?? 0);

// Ambil daftar choukai
$stmt = $pdo->query("SELECT id, title, chapter_start, chapter_end, level FROM choukai ORDER BY level, chapter_start");
$choukaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil jawaban siswa dari choukai_answers
$sql = "SELECT ca.*, u.full_name AS student_name, c.title AS choukai_title, c.level
        FROM choukai_answers ca
        JOIN users u ON ca.user_id = u.id
        JOIN choukai c ON ca.choukai_id = c.id
        WHERE 1=1";

$params = [];
if ($level) {
    $sql .= " AND c.level = ?";
    $params[] = $level;
}
if ($choukai_id) {
    $sql .= " AND c.id = ?";
    $params[] = $choukai_id;
}

$sql .= " ORDER BY ca.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">📊 Hasil Choukai Siswa</div>
    </div>

    <!-- Filter -->
    <form method="get" style="margin-bottom:12px;">
        <label>Level:
            <select name="level" onchange="this.form.submit()">
                <option value="">Semua</option>
                <option value="N5" <?= $level==='N5'?'selected':'' ?>>N5</option>
                <option value="N4" <?= $level==='N4'?'selected':'' ?>>N4</option>
            </select>
        </label>

        <label>Choukai:
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

    <!-- Export CSV -->
    <p>
        <a href="<?= BASE_URL ?>teacher/choukai-export-excel.php?level=<?= urlencode($level) ?>&choukai_id=<?= $choukai_id ?>"
            class="button">
            Export ke Excel
        </a>
    </p>


    <!-- Tabel hasil -->
    <table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;">
        <thead style="background:#eee;">
            <tr>
                <th>No</th>
                <th>Siswa</th>
                <th>Choukai</th>
                <th>Total Jawaban</th>
                <th>Waktu Submit</th>
                <th>Jawaban</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($results): ?>
            <?php $no=1; foreach ($results as $r): ?>
            <?php $answers = json_decode($r['answers'], true); ?>
            <tr>
                <td><?= $no ?></td>
                <td><?= htmlspecialchars($r['student_name']) ?></td>
                <td><?= htmlspecialchars($r['choukai_title']) ?></td>
                <td><?= count($answers) ?></td>
                <td><?= $r['created_at'] ?></td>
                <td>
                    <?php
                            foreach ($answers as $qno => $a) {
                                $parts = [];
                                if (!empty($a['number'])) $parts[] = "Angka: ".$a['number'];
                                if (!empty($a['letter'])) $parts[] = "Huruf: ".$a['letter'];
                                if (!empty($a['ox'])) $parts[] = "O/X: ".$a['ox'];
                                if (!empty($a['text'])) $parts[] = "Text: ".$a['text'];
                                echo "Q$qno: ".implode(" | ", $parts)."<br>";
                            }
                            ?>
                </td>
            </tr>
            <?php $no++; endforeach; ?>
            <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center;">Belum ada hasil choukai</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>