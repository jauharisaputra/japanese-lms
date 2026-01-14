<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

// Pastikan hanya teacher/admin yang bisa membuka
requireRole(['teacher','admin']);

// Inisialisasi PDO
$pdo = getPDO();

// Ambil daftar choukai untuk dropdown filter
$stmt = $pdo->prepare("SELECT id, title FROM choukai ORDER BY chapter_start ASC");
$stmt->execute();
$choukaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pilih choukai dari GET
$choukai_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selectedChoukai = null;
if ($choukai_id) {
    foreach ($choukaiList as $c) {
        if ($c['id'] == $choukai_id) {
            $selectedChoukai = $c;
            break;
        }
    }
}

// Ambil jawaban siswa jika choukai dipilih
$answersData = [];
if ($selectedChoukai) {
    $stmt = $pdo->prepare("
        SELECT ca.user_id, ca.answers, u.nama AS student_name
        FROM choukai_answers ca
        JOIN users u ON ca.user_id = u.id
        WHERE ca.choukai_id = ?
        ORDER BY u.nama
    ");
    $stmt->execute([$choukai_id]);
    $answersData = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = "Hasil Jawaban Choukai";
require __DIR__ . "/../includes/header.php";
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">📊 Hasil Choukai</div>
    </div>

    <!-- Dropdown filter Choukai -->
    <form method="get" style="margin-bottom:16px;">
        <label>Pilih Choukai:
            <select name="id" onchange="this.form.submit()">
                <option value="0">-- Pilih Choukai --</option>
                <?php foreach ($choukaiList as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $choukai_id == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['title']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <?php if (!$selectedChoukai): ?>
    <p>Pilih choukai untuk melihat hasil siswa.</p>
    <?php else: ?>

    <?php if (empty($answersData)): ?>
    <p>Belum ada siswa yang mengumpulkan jawaban untuk Choukai ini.</p>
    <?php else: ?>

    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;width:100%;">
        <thead style="background:#eee;">
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <?php
                        // Tentukan jumlah kolom jawaban (asumsi max 10)
                        $maxAnswers = 0;
                        foreach ($answersData as $a) {
                            $ans = json_decode($a['answers'], true);
                            $maxAnswers = max($maxAnswers, count($ans));
                        }
                        for ($i = 1; $i <= $maxAnswers; $i++):
                            echo "<th>Jawaban $i</th>";
                        endfor;
                        ?>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; foreach ($answersData as $a): ?>
            <?php $ans = json_decode($a['answers'], true); ?>
            <tr>
                <td><?= $no ?></td>
                <td><?= htmlspecialchars($a['student_name']) ?></td>
                <?php
                            for ($i = 1; $i <= $maxAnswers; $i++) {
                                if (isset($ans[$i])) {
                                    $j = $ans[$i];
                                    $display = [];
                                    if (!empty($j['number'])) $display[] = "Angka: ".$j['number'];
                                    if (!empty($j['letter'])) $display[] = "Huruf: ".$j['letter'];
                                    if (!empty($j['ox'])) $display[] = "O/X: ".$j['ox'];
                                    if (!empty($j['text'])) $display[] = "Text: ".$j['text'];
                                    echo "<td>".implode("<br>", $display)."</td>";
                                } else {
                                    echo "<td>-</td>";
                                }
                            }
                            ?>
            </tr>
            <?php $no++; endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top:12px;">
        <a href="<?= BASE_URL ?>student/choukai-export.php?id=<?= $choukai_id ?>" class="button">Export ke Excel</a>
    </p>

    <?php endif; ?>

    <?php endif; ?>
</div>

<?php require __DIR__ . "/../includes/footer.php"; ?>