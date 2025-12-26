<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

$pdo = getPDO();

// ambil semua hiragana & katakana yang belum punya SVG
$stmt = $pdo->prepare("
    SELECT id, char_symbol, script
    FROM kana_chars
    WHERE stroke_svg_path IS NULL
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    echo "Tidak ada kana yang perlu diproses.\n";
    exit;
}

$srcDir = __DIR__ . "/../animCJK/svgsJaKana/";
$dstBase = "public/svg/animcjk/kana/";
$dstDir  = __DIR__ . "/../" . $dstBase;

if (!is_dir($dstDir)) {
    mkdir($dstDir, 0777, true);
}

foreach ($rows as $row) {
    $ch   = $row["char_symbol"];
    $id   = (int)$row["id"];

    // kode Unicode desimal
    $code = mb_ord($ch, "UTF-8");
    if ($code === false) {
        echo "[SKIP] {$ch} (id={$id}) - mb_ord gagal\n";
        continue;
    }

    $file = $code . ".svg";
    $src  = $srcDir . $file;
    if (!file_exists($src)) {
        echo "[SKIP] {$ch} (id={$id}) - file {$src} tidak ada\n";
        continue;
    }

    $dstRel = $dstBase . $file;
    $dst    = __DIR__ . "/../" . $dstRel;

    if (!copy($src, $dst)) {
        echo "[SKIP] {$ch} (id={$id}) - gagal copy ke {$dst}\n";
        continue;
    }

    $upd = $pdo->prepare("UPDATE kana_chars SET stroke_svg_path = ? WHERE id = ?");
    $upd->execute([$dstRel, $id]);

    echo "[OK] {$ch} (id={$id}) -> {$dstRel}\n";
}

echo "Selesai.\n";
?>
