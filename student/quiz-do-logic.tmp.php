$questions = json_decode($quiz['questions'], true);
if (!is_array($questions)) {
    echo "<p>Format soal kuis rusak.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$totalQuestions      = count($questions);
$secondsPerQuestion  = 90; // 1,5 menit per soal
$totalSeconds        = $totalQuestions * $secondsPerQuestion;

$result = null;
