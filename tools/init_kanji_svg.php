<?php
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/includes/functions.php";

$pdo = getPDO();

// daftar level yang mau diproses
$levels = ["N5","N4"];

// pastikan extension mbstring aktif untuk mb_ord
if (!function_exists("mb_ord")) {
    die("mbstring / mb_ord tidak tersedia.\n");
}

foreach ($levels as $level) {
    $stmt = $pdo->prepare("SELECT id, kanji FROM kanji_chars WHERE level = ? AND (stroke_svg_path IS NULL OR stroke_svg_path = '')");
    $stmt->execute([$level]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Memproses level $level, " . count($rows) . " kanji...\n";

    foreach ($rows as $row) {
        $ch   = $row["kanji"];
        $code = mb_ord($ch, "UTF-8");              // kode Unicode desimal
        $src  = __DIR__ . "/../animCJK/svgsJa/$code.svg";   // lokasi file di repo clone
        $dstRel = "public/svg/animcjk/kanji/$code.svg";     // path relatif untuk web
        $dst  = __DIR__ . "/$dstRel";

        if (!file_exists($src)) {
            echo "[$level] SKIP {$ch} (id={$row["id"]}) - file $src tidak ada\n";
            continue;
        }

        // buat folder tujuan jika belum ada
        if (!is_dir(dirname($dst))) {
            mkdir(dirname($dst), 0777, true);
        }

        // copy file hanya jika belum ada
        if (!file_exists($dst)) {
            if (!copy($src, $dst)) {
                echo "[$level] GAGAL copy {$ch} ($src -> $dst)\n";
                continue;
            }
        }

        // update path di database
        $upd = $pdo->prepare("UPDATE kanji_chars SET stroke_svg_path = ? WHERE id = ?");
        $upd->execute([$dstRel, $row["id"]]);

        echo "[$level] OK {$ch} (id={$row["id"]}) -> $dstRel\n";
    }
}

echo "Selesai.\n";
?>
