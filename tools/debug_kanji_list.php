<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = getPDO();

$stmt = $pdo->query("SELECT id, kanji, level, stroke_svg_path FROM kanji_chars ORDER BY id LIMIT 20");
foreach ($stmt as $row) {
    echo $row["id"] . " | " . $row["kanji"] . " | " . $row["level"] . " | " . $row["stroke_svg_path"] . PHP_EOL;
}
?>

