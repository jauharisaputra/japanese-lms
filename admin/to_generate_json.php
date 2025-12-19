<?php
require_once __DIR__ . "/../config/config.php";

function regenerateToJson($exam_id) {
    $pdo = getPDO();

    $stmt = $pdo->prepare("SELECT * FROM to_exams WHERE id = ?");
    $stmt->execute([$exam_id]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$exam) {
        throw new RuntimeException("Exam not found: " . $exam_id);
    }

    $q = $pdo->prepare("SELECT * FROM to_questions WHERE exam_id = ? ORDER BY id");
    $q->execute([$exam_id]);
    $sections = [];
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        $sec = $row["section"];
        if (!isset($sections[$sec])) {
            $sections[$sec] = [];
        }
        $sections[$sec][] = [
            "id"       => (int)$row["id"],
            "question" => $row["question_text"],
            "choices"  => [
                "A" => $row["choice_a"],
                "B" => $row["choice_b"],
                "C" => $row["choice_c"],
                "D" => $row["choice_d"],
            ],
            "answer"   => $row["correct_choice"],
            "point"    => (int)$row["point"],
        ];
    }

    $data = [
        "exam_id"  => (int)$exam["id"],
        "level"    => $exam["level"],
        "title"    => $exam["name"],
        "sections" => $sections,
    ];

    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false) {
        throw new RuntimeException("json_encode error: " . json_last_error_msg());
    }

    $file = __DIR__ . "/../data/to_exam_" . (int)$exam["id"] . ".json";
    file_put_contents($file, $json);
}

// mode CLI opsional
if (PHP_SAPI === "cli" && isset($argv[1])) {
    regenerateToJson((int)$argv[1]);
}
