<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";
requireRole(["student"]);

$user = currentUser();
$pdo = getPDO();
$exam_id = (int)($_GET['exam_id'] ?? 0);

$page_title = "Ujian TO";
require __DIR__ . "/../includes/header.php";

$exam = $pdo->prepare("SELECT * FROM placement_exams WHERE id = ?")->execute([$exam_id])->fetch();
$questions = $pdo->prepare("SELECT * FROM placement_questions WHERE exam_id = ? ORDER BY question_number")->execute([$exam_id])->fetchAll();

// Cek PDF TERBARU
$latest_pdf = $pdo->prepare("
    SELECT pep.* FROM placement_exam_pdfs pep 
    WHERE pep.exam_id = ? 
    ORDER BY pep.upload_date DESC 
    LIMIT 1
")->execute([$exam_id])->fetch();
$has_pdf = $latest_pdf && file_exists(__DIR__ . '/../uploads/placement_pdfs/' . $latest_pdf['filepath']);

// Cek attempt
$stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM placement_attempts WHERE user_id = ? AND exam_id = ?");
$stmt->execute([$user['id'], $exam_id]);
$attempts = $stmt->fetch()['attempts'];
$max_attempts = $exam['attempts_allowed'] ?? 3;

if ($attempts >= $max_attempts) {
    echo "<div class='alert alert-danger text-center'><h4>❌ Jatah attempt habis!</h4></div>";
    require __DIR__ . "/../includes/footer.php";
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <style>
    * {
        box-sizing: border-box;
    }

    body {
        background: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow-x: hidden;
    }

    .exam-container {
        min-height: 100vh;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .exam-header {
        background: linear-gradient(135deg, #dc3545, #ff6b6b);
        color: white;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(220, 53, 69, 0.3);
    }

    .pdf-section {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        height: 70vh;
        overflow: hidden;
    }

    .pdf-placeholder {
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        color: #6c757d;
    }

    .pdf-placeholder i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .pdf-frame {
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 10px;
        background: white;
    }

    .answers-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        height: 70vh;
        overflow-y: auto;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .question-item {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        scroll-margin-top: 100px;
    }

    .question-item:hover {
        border-color: #dc3545;
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.15);
        transform: translateY(-2px);
    }

    .question-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #dee2e6;
    }

    .question-number {
        background: #dc3545;
        color: white;
        padding: 8px 16px;
        border-radius: 50%;
        font-weight: bold;
        font-size: 1.1rem;
    }

    .option-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .option-btn {
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 15px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .option-btn:hover {
        border-color: #dc3545;
    }

    .option-btn.selected {
        background: #dc3545;
        color: white;
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
    }

    .submit-btn {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
        color: white;
        padding: 18px 40px;
        font-size: 1.2rem;
        font-weight: bold;
        border-radius: 50px;
        width: 100%;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
    }

    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(40, 167, 69, 0.4);
    }

    .progress-info {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    @media (max-width: 992px) {

        .pdf-section,
        .answers-section {
            height: 50vh;
        }

        .option-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {

        .pdf-section,
        .answers-section {
            height: 45vh;
            margin-bottom: 15px;
        }
    }
    </style>
</head>

<body>
    <div class="exam-container">
        <!-- HEADER -->
        <div class="exam-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>📄 Ujian TO <?= htmlspecialchars($exam['level']) ?></h2>
                    <p class="mb-0"><strong><?= htmlspecialchars($exam['name']) ?></strong></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="progress-info">
                        <strong>Attempt: <?= $attempts + 1 ?>/<?= $max_attempts ?></strong> |
                        Total Soal: <?= count($questions) ?> |
                        Passing: <?= (int)$exam['pass_score'] ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- PDF SOAL -->
            <div class="col-lg-8">
                <div class="pdf-section">
                    <?php if ($has_pdf): ?>
                    <iframe
                        src="../uploads/placement_pdfs/<?= htmlspecialchars($latest_pdf['filepath']) ?>#toolbar=0&view=FitH"
                        class="pdf-frame" title="Soal Ujian TO">
                    </iframe>
                    <?php else: ?>
                    <div class="pdf-placeholder">
                        <i class="fas fa-file-pdf fa-4x"></i>
                        <h4>PDF Soal Belum Tersedia</h4>
                        <p class="mb-0">Hubungi guru untuk upload soal PDF</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- LEMBAR JAWABAN -->
            <div class="col-lg-4">
                <div class="answers-section">
                    <h5 class="mb-4 text-danger border-bottom pb-2">📝 Lembar Jawaban</h5>

                    <form method="POST" action="to-submit.php" id="examForm">
                        <input type="hidden" name="exam_id" value="<?= $exam_id ?>">

                        <?php foreach ($questions as $i => $q): ?>
                        <div class="question-item" id="q<?= $q['question_number'] ?>">
                            <div class="question-header">
                                <span class="question-number"><?= (int)$q['question_number'] ?></span>
                                <small><?= ($i+1) ?>/<?= count($questions) ?></small>
                            </div>
                            <div class="option-grid">
                                <label class="option-btn" onclick="selectOption(<?= $q['question_number'] ?>, 'A')">
                                    <input type="radio" name="answer[<?= $q['question_number'] ?>]" value="A"
                                        style="display:none;">
                                    <span>A</span>
                                </label>
                                <label class="option-btn" onclick="selectOption(<?= $q['question_number'] ?>, 'B')">
                                    <input type="radio" name="answer[<?= $q['question_number'] ?>]" value="B"
                                        style="display:none;">
                                    <span>B</span>
                                </label>
                                <label class="option-btn" onclick="selectOption(<?= $q['question_number'] ?>, 'C')">
                                    <input type="radio" name="answer[<?= $q['question_number'] ?>]" value="C"
                                        style="display:none;">
                                    <span>C</span>
                                </label>
                                <label class="option-btn" onclick="selectOption(<?= $q['question_number'] ?>, 'D')">
                                    <input type="radio" name="answer[<?= $q['question_number'] ?>]" value="D"
                                        style="display:none;">
                                    <span>D</span>
                                </label>
                                <?php if ($exam['max_score'] > 50): // E option untuk exam panjang ?>
                                <label class="option-btn" onclick="selectOption(<?= $q['question_number'] ?>, 'E')"
                                    style="grid-column: span 2;">
                                    <input type="radio" name="answer[<?= $q['question_number'] ?>]" value="E"
                                        style="display:none;">
                                    <span>E</span>
                                </label>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <button type="submit" class="submit-btn mt-4" id="submitBtn">
                            <i class="fas fa-paper-plane me-2"></i>
                            Submit Jawaban (<?= count($questions) ?> Soal)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    let answeredCount = 0;

    function selectOption(qNum, option) {
        const question = document.getElementById('q' + qNum);
        const options = question.querySelectorAll('.option-btn');
        const selectedInput = question.querySelector(`input[value="${option}"]`);

        options.forEach(opt => opt.classList.remove('selected'));
        options[option.charCodeAt(0) - 65].classList.add('selected');
        selectedInput.checked = true;

        // Update counter
        answeredCount = document.querySelectorAll('input[type="radio"]:checked').length;
        updateProgress();

        // Scroll smooth
        question.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }

    function updateProgress() {
        const total = <?= count($questions) ?>;
        const percent = Math.round((answeredCount / total) * 100);
        console.log(`Progress: ${answeredCount}/${total} (${percent}%)`);

        // Enable/disable submit
        document.getElementById('submitBtn').disabled = answeredCount === 0;
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        updateProgress();
    });
    </script>
</body>

</html>