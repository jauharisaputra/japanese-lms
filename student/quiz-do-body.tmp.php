<h1><?php echo htmlspecialchars($quiz['title']); ?></h1>

<?php if ($result): ?>
    <p>Nilai Anda: <strong><?php echo $result['score']; ?></strong>
       (<?php echo $result['correct']; ?>/<?php echo $result['total']; ?> benar)</p>
    <p>Status:
        <?php if ($result['is_passed']): ?>
            <span style="color:green;">Lulus</span>
        <?php else: ?>
            <span style="color:red;">Perlu remedial</span>
        <?php endif; ?>
        (Attempt ke-<?php echo $result['attempt']; ?>)
    </p>
    <p><a href="quizzes.php">&laquo; Kembali ke daftar kuis</a></p>
<?php else: ?>
    <p>Waktu tersisa: <span id="timer"></span></p>
    <form method="post">
        <?php foreach ($questions as $index => $q): ?>
            <fieldset style="margin-bottom:15px;">
                <legend><?php echo ($index+1) . '. ' . htmlspecialchars($q['question']); ?></legend>
                <?php foreach ($q['options'] as $optIndex => $opt): ?>
                    <label>
                        <input type="radio" name="answer[<?php echo $index; ?>]" value="<?php echo $optIndex; ?>">
                        <?php echo htmlspecialchars($opt); ?>
                    </label><br>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
        <button type="submit">Kirim Jawaban</button>
    </form>
    <p><a href="quizzes.php">&laquo; Kembali ke daftar kuis</a></p>
<?php endif; ?>
