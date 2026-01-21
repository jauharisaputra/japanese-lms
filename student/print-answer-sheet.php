<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = getPDO();
$pdf_id = $_GET['pdf_id'] ?? 0;

$stmt = $pdo->prepare("SELECT pep.*, pe.max_score, pe.name 
                       FROM placement_exam_pdfs pep 
                       JOIN placement_exams pe ON pep.exam_id = pe.id 
                       WHERE pep.id = ?");
$stmt->execute([$pdf_id]);
$pdf_info = $stmt->fetch();

if (!$pdf_info) {
    die("PDF tidak ditemukan");
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Lembar Jawaban - <?php echo $pdf_info['name']; ?></title>
    <style>
    @media print {
        body {
            margin: 0;
        }
    }

    .answer-sheet {
        font-family: Arial;
        max-width: 210mm;
        margin: 0 auto;
        padding: 20mm;
    }

    .question {
        margin: 15px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    td {
        padding: 8px;
        text-align: center;
        border: 1px solid #ccc;
    }
    </style>
</head>

<body>
    <div class="answer-sheet">
        <h2>LEMBAR JAWABAN UJIAN TO - <?php echo strtoupper($pdf_info['name']); ?></h2>
        <p>Nama: ___________________________ &nbsp; Kelas: ___________ &nbsp; Tanggal: ___________</p>

        <h3>Pilihan Ganda (Total: <?php echo $pdf_info['max_score']; ?> soal)</h3>

        <table>
            <?php 
            $questions_per_row = 10;
            $total_questions = $pdf_info['max_score'];
            for ($i = 1; $i <= $total_questions; $i += $questions_per_row): 
                $end = min($i + $questions_per_row - 1, $total_questions);
            ?>
            <tr>
                <?php for ($j = $i; $j <= $end; $j++): ?>
                <td>
                    <div class="question"><?php echo $j; ?></div>
                    <label><input type="radio" name="q<?php echo $j; ?>" value="A"> A</label><br>
                    <label><input type="radio" name="q<?php echo $j; ?>" value="B"> B</label><br>
                    <label><input type="radio" name="q<?php echo $j; ?>" value="C"> C</label><br>
                    <label><input type="radio" name="q<?php echo $j; ?>" value="D"> D</label><br>
                    <label><input type="radio" name="q<?php echo $j; ?>" value="E"> E</label>
                </td>
                <?php endfor; ?>
            </tr>
            <?php endfor; ?>
        </table>

        <div style="margin-top: 30px;">
            <p><strong>Petunjuk:</strong> Isi lingkaran jawaban dengan tinta hitam. Hapuslah jawaban yang salah dengan
                rapi.</p>
            <p style="text-align: right;">Tanda Tangan Guru,</p>
            <p style="text-align: right;">&nbsp;<br>&nbsp;<br>&nbsp;</p>
        </div>
    </div>
</body>

</html>