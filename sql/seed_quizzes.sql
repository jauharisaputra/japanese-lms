INSERT INTO quizzes (id, title, level)
VALUES
(1, 'Kuis Hiragana Dasar', 'N5'),
(2, 'Kuis Katakana Dasar', 'N5'),
(3, 'Kuis Kanji N5',      'N5'),
(4, 'Kuis Kanji N4',      'N4')
ON DUPLICATE KEY UPDATE
  title = VALUES(title),
  level = VALUES(level);
