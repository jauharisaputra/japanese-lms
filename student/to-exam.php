<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";
requireRole(["student"]);

$user = currentUser();
$pdo = getPDO();
$exam_id = (int)($_GET['exam_id'] ?? 0);

$page_title = "Ujian TO";
require __DIR__ . "/../includes/header.php";

// CEK EXAM
$stmt = $pdo->prepare("SELECT * FROM placement_exams WHERE id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();

if (!$exam) {
    echo "<div class='alert alert-danger text-center p-5'><h3>❌ Exam tidak ditemukan!</h3><a href='javascript:history.back()' class='btn btn-secondary mt-3'>← Kembali</a></div>";
    require __DIR__ . "/../includes/footer.php";
    exit;
}

// CEK PDF
$stmt = $pdo->prepare("SELECT * FROM placement_exam_pdfs WHERE exam_id = ? ORDER BY upload_date DESC LIMIT 1");
$stmt->execute([$exam_id]);
$latest_pdf = $stmt->fetch();
$has_pdf = $latest_pdf && file_exists(__DIR__ . '/../uploads/placement_pdfs/' . $latest_pdf['filepath']);

// TOTAL SOAL & TIMER
$total_questions = (int)$exam['max_score'];
$exam_duration = (int)$exam['duration_minutes'] ?? 60; // Menit

// ATTEMPT
$stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM placement_attempts WHERE user_id = ? AND exam_id = ?");
$stmt->execute([$user['id'], $exam_id]);
$attempts = $stmt->fetch()['attempts'] ?? 0;
$max_attempts = $exam['attempts_allowed'] ?? 3;

if ($attempts >= $max_attempts) {
    echo "<div class='alert alert-danger text-center p-5'><h3>❌ Jatah attempt habis ($attempts/$max_attempts)!</h3><a href='javascript:history.back()' class='btn btn-secondary mt-3'>← Kembali</a></div>";
    require __DIR__ . "/../includes/footer.php";
    exit;
}
?>

<style>
* {
    box-sizing: border-box;
}

body {
    background: #f8f9fa;
    font-family: 'Segoe UI', sans-serif;
    overflow-x: hidden;
}

.exam-container {
    max-width: 1400px;
    margin: 20px auto;
    padding: 0 15px;
}

/* HEADER */
.header-exam {
    background: linear-gradient(135deg, #dc3545, #ff6b6b);
    color: white;
    padding: 25px;
    border-radius: 20px;
    margin-bottom: 25px;
    box-shadow: 0 15px 35px rgba(220, 53, 69, 0.3);
}

/* TIMER */
.timer-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    background: linear-gradient(135deg, #dc3545, #ff4757);
    color: white;
    padding: 15px 25px;
    border-radius: 25px;
    box-shadow: 0 10px 30px rgba(220, 53, 69, 0.4);
    font-weight: bold;
    font-size: 1.3rem;
    min-width: 180px;
    text-align: center;
}

.timer-circle {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    margin: 0 auto 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    font-weight: bold;
    backdrop-filter: blur(10px);
}

.timer-warning {
    animation: pulse 1s infinite;
    background: linear-gradient(135deg, #ffc107, #ffed4e);
    color: #856404;
}

.timer-danger {
    animation: shake 0.5s infinite;
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

@keyframes pulse {

    0%,
    100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.05);
    }
}

@keyframes shake {

    0%,
    100% {
        transform: translateX(0);
    }

    25% {
        transform: translateX(-5px);
    }

    75% {
        transform: translateX(5px);
    }
}

/* PDF */
.pdf-container {
    height: 75vh;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    background: white;
    margin-bottom: 25px;
}

.pdf-viewer {
    width: 100%;
    height: 100%;
    border: none;
}

/* LEMBAR JAWABAN */
.answer-section {
    height: 75vh;
    overflow-y: auto;
    background: white;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    padding: 25px;
}

.question-item {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border: 2px solid #dee2e6;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 18px;
    transition: all 0.3s ease;
    scroll-margin-top: 100px;
}

.question-item:hover {
    border-color: #dc3545;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(220, 53, 69, 0.15);
}

.q-number {
    background: #dc3545;
    color: white;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
    margin-right: 15px;
}

.option-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 15px;
}

.option-btn {
    padding: 18px;
    text-align: center;
    border: 3px solid #dee2e6;
    border-radius: 12px;
    cursor: pointer;
    font-size: 1.4rem;
    font-weight: 700;
    transition: all 0.3s ease;
    background: white;
}

.option-btn:hover {
    border-color: #dc3545;
    background: #fff5f5;
}

.option-btn.selected {
    background: #dc3545 !important;
    color: white !important;
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.25);
}

.submit-exam {
    background: linear-gradient(135deg, #28a745, #20c997) !important;
    border: none !important;
    font-size: 1.3rem !important;
    padding: 20px !important;
    border-radius: 25px !important;
    font-weight: bold !important;
}

/* RESPONSIVE */
@media (max-width: 992px) {

    .pdf-container,
    .answer-section {
        height: 55vh;
    }

    .option-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {

    .pdf-container,
    .answer-section {
        height: 50vh;
    }

    .timer-container {
        top: 10px;
        right: 10px;
        font-size: 1.1rem;
        min-width: 150px;
    }
}
</style>

<!-- TIMER -->
<div class="timer-container" id="timerContainer">
    <div class="timer-circle" id="timerCircle"><?= sprintf('%02d:%02d', $exam_duration, 0) ?></div>
    <div id="timerText">Sisa Waktu</div>
</div>

<div class="exam-container">
    <!-- HEADER -->
    <div class="header-exam">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-2 display-5 fw-bold">📄 Ujian TO</h1>
                <h3 class="mb-0"><?= htmlspecialchars($exam['level']) ?> - <?= htmlspecialchars($exam['name']) ?></h3>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="row g-2 justify-content-end">
                    <div class="col-auto">
                        <div class="p-3 bg-light text-danger rounded shadow-sm">
                            <div class="h4 mb-0 fw-bold"><?= $attempts + 1 ?>/<?= $max_attempts ?></div>
                            <small class="text-muted">Attempt</small>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="p-3 bg-light text-dark rounded shadow-sm">
                            <div class="h5 mb-0"><?= $total_questions ?></div>
                            <small class="text-muted">Soal</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- PDF SOAL (70%) -->
        <div class="col-lg-8">
            <div class="pdf-container">
                <?php if ($has_pdf): ?>
                <iframe
                    src="../uploads/placement_pdfs/<?= htmlspecialchars($latest_pdf['filepath']) ?>#toolbar=0&navpanes=0&view=FitH"
                    class="pdf-viewer" title="Soal Ujian <?= htmlspecialchars($exam['name']) ?>">
                </iframe>
                <?php else: ?>
                <div class="d-flex flex-column justify-content-center align-items-center h-100 p-5 text-center">
                    <i class="fas fa-file-pdf fa-5x text-muted mb-4 opacity-50"></i>
                    <h3 class="text-muted mb-3">PDF Soal Belum Tersedia</h3>
                    <p class="lead text-muted">Hubungi guru untuk upload soal PDF</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- LEMBAR JAWABAN (30%) -->
        <div class="col-lg-4">
            <div class="answer-section">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-danger">
                    <h4 class="mb-0 text-danger fw-bold">📝 Lembar Jawaban</h4>
                    <span class="badge bg-danger fs-6 px-3 py-2"><?= $total_questions ?> Soal</span>
                </div>

                <form method="POST" action="to-submit.php" id="examForm">
                    <input type="hidden" name="exam_id" value="<?= $exam_id ?>">

                    <?php for ($i = 1; $i <= $total_questions; $i++): ?>
                    <div class="question-item" id="q<?= $i ?>">
                        <div class="d-flex align-items-center mb-3">
                            <div class="q-number"><?= $i ?></div>
                            <div class="flex-grow-1">
                                <small class="badge bg-secondary"><?= $i ?>/<?= $total_questions ?></small>
                            </div>
                        </div>

                        <div class="option-grid">
                            <label class="option-btn" onclick="selectAnswer(<?= $i ?>, 'A')">
                                <input type="radio" name="answer[<?= $i ?>]" value="A" style="display:none;">
                                <span>A</span>
                            </label>
                            <label class="option-btn" onclick="selectAnswer(<?= $i ?>, 'B')">
                                <input type="radio" name="answer[<?= $i ?>]" value="B" style="display:none;">
                                <span>B</span>
                            </label>
                            <label class="option-btn" onclick="selectAnswer(<?= $i ?>, 'C')">
                                <input type="radio" name="answer[<?= $i ?>]" value="C" style="display:none;">
                                <span>C</span>
                            </label>
                            <label class="option-btn" onclick="selectAnswer(<?= $i ?>, 'D')">
                                <input type="radio" name="answer[<?= $i ?>]" value="D" style="display:none;">
                                <span>D</span>
                            </label>
                        </div>
                    </div>
                    <?php endfor; ?>

                    <button type="submit" class="btn btn-success submit-exam mt-4 w-100 shadow-lg" id="submitBtn"
                        disabled>
                        <i class="fas fa-paper-plane me-2"></i>🚀 Submit Ujian (0/<?= $total_questions ?>)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const examDuration = <?= $exam_duration * 60 ?>; // Total detik
let timeLeft = examDuration;
let timerInterval;
let answeredCount = 0;
const totalQuestions = <?= $total_questions ?>;

// Format MM:SS
function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

// Update timer display
function updateTimer() {
    const circle = document.getElementById('timerCircle');
    const text = document.getElementById('timerText');
    const container = document.getElementById('timerContainer');

    circle.textContent = formatTime(timeLeft);

    // 5 MENIT TERAKHIR
    if (timeLeft <= 300) {
        container.classList.add('timer-warning');
        text.textContent = 'SEGERA SELESAI!';
    }

    // 1 MENIT TERAKHIR
    if (timeLeft <= 60) {
        container.classList.remove('timer-warning');
        container.classList.add('timer-danger');
        text.textContent = 'SUBMIT SEKARANG!';
    }
}

// Timer countdown
function startTimer() {
    timerInterval = setInterval(() => {
        timeLeft--;
        updateTimer();

        // WAKTU HABIS → AUTO SUBMIT
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            document.body.innerHTML = `
                <div style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(220,53,69,0.95);color:white;display:flex;align-items:center;justify-content:center;flex-direction:column;font-size:2rem;text-align:center;padding:20px;z-index:99999">
                    <div style="font-size:4rem;margin-bottom:20px">⏰</div>
                    <h1 style="margin:0;font-size:3rem">WAKTU UJIAN HABIS!</h1>
                    <p style="font-size:1.5rem;margin:20px 0">Auto submit dalam <span id="countdown">3</span> detik...</p>
                </div>
            `;

            let countdown = 3;
            const countdownEl = document.getElementById('countdown');
            const autoSubmit = setInterval(() => {
                countdown--;
                countdownEl.textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(autoSubmit);
                    document.getElementById('examForm').submit();
                }
            }, 1000);
        }
    }, 1000);
}

// Jawaban selection
function selectAnswer(qNum, option) {
    const question = document.getElementById('q' + qNum);
    const options = question.querySelectorAll('.option-btn');

    options.forEach((opt, index) => {
        opt.classList.remove('selected');
        if (String.fromCharCode(65 + index) === option) opt.classList.add('selected');
    });

    answeredCount = document.querySelectorAll('input[type="radio"]:checked').length;
    updateSubmitButton();
    question.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
    });
}

// Update tombol submit
function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = answeredCount === 0;
    submitBtn.innerHTML = answeredCount === totalQuestions ?
        '<i class="fas fa-check-circle me-2"></i>✅ Submit Semua!' :
        `<i class="fas fa-paper-plane me-2"></i>🚀 Submit (${answeredCount}/${totalQuestions})`;
}

// INIT
document.addEventListener('DOMContentLoaded', function() {
    updateTimer();
    startTimer();
    updateSubmitButton();

    // Anti reload
    window.addEventListener('beforeunload', function(e) {
        if (timeLeft > 0 && timeLeft < examDuration) {
            e.preventDefault();
            e.returnValue = 'Ujian berlangsung! Yakin keluar?';
        }
    });
});
</script>

<?php require __DIR__ . "/../includes/footer.php"; ?>