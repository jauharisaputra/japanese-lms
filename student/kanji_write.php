<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

requireRole(["student"]);
$user = currentUser();
$pdo  = getPDO();

$tab = $_GET["tab"] ?? "hiragana";

if ($tab === "kanji") {
    $stmt = $pdo->prepare("SELECT * FROM kanji_chars WHERE level = 'N5' ORDER BY id LIMIT 50");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT * FROM kana_chars WHERE script = ? ORDER BY id");
    $script = ($tab === "katakana") ? "katakana" : "hiragana";
    $stmt->execute([$script]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = "Belajar Menulis";
require __DIR__ . "/../includes/header.php";
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Belajar Menulis (Stroke Order)</div>
    </div>
    <div class="card-body">
        <p>Pilih jenis tulisan:</p>
        <p>
            <a href="?tab=hiragana">Hiragana</a> |
            <a href="?tab=katakana">Katakana</a> |
            <a href="?tab=kanji">Kanji N5</a>
        </p>

        <?php if (!$items): ?>
        <p>Belum ada data.</p>
        <?php else: ?>
        <div style="display:flex;flex-wrap:wrap;gap:16px;">
            <?php foreach ($items as $item): ?>
            <div style="border:1px solid #ddd;padding:8px;width:160px;">
                <?php if ($tab === "kanji"): ?>
                <div style="font-size:32px;text-align:center;"><?php echo htmlspecialchars($item["kanji"]); ?></div>
                <?php if (!empty($item["stroke_svg_path"])): ?>
                <object data="<?php echo BASE_URL . htmlspecialchars($item["stroke_svg_path"]); ?>" type="image/svg+xml"
                    style="width:100%;height:auto;border:1px solid #ccc;margin-top:4px;"></object>
                <?php endif; ?>
                <div style="font-size:12px;margin-top:4px;">
                    <div>On: <?php echo htmlspecialchars($item["onyomi"]); ?></div>
                    <div>Kun: <?php echo htmlspecialchars($item["kunyomi"]); ?></div>
                    <div>Arti: <?php echo htmlspecialchars($item["meaning_id"]); ?></div>
                </div>
                <?php else: ?>
                <div style="font-size:32px;text-align:center;"><?php echo htmlspecialchars($item["char_symbol"]); ?>
                </div>
                <?php if (!empty($item["stroke_svg_path"])): ?>
                <object data="<?php echo BASE_URL . htmlspecialchars($item["stroke_svg_path"]); ?>" type="image/svg+xml"
                    style="width:100%;height:auto;border:1px solid #ccc;margin-top:4px;"></object>
                <?php endif; ?>
                <div style="font-size:12px;margin-top:4px;">
                    <div>Romaji: <?php echo htmlspecialchars($item["romaji"]); ?></div>
                    <div><?php echo htmlspecialchars($item["description"]); ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>

<script>
function playAnimCJK(svgEl, speedMs = 400) {
    if (!svgEl) return;
    const rootGroup = svgEl.querySelector('g[id^="kvg:StrokePaths"]') || svgEl;
    const strokes = Array.from(rootGroup.querySelectorAll("path"));
    if (!strokes.length) return;

    strokes.forEach(p => p.style.opacity = 0);

    let i = 0;

    function showNext() {
        if (i >= strokes.length) return;
        strokes[i].style.transition = "opacity 0.2s ease-in-out";
        strokes[i].style.opacity = 1;
        i++;
        if (i < strokes.length) {
            setTimeout(showNext, speedMs);
        }
    }
    showNext();
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('object[type="image/svg+xml"]').forEach(obj => {
        obj.addEventListener("load", () => {
            try {
                const svgDoc = obj.contentDocument;
                const svgEl = svgDoc && svgDoc.querySelector("svg");
                playAnimCJK(svgEl, 400);
                obj.addEventListener("click", () => playAnimCJK(svgEl, 300));
            } catch (e) {
                console.error("AnimCJK error", e);
            }
        });
    });
});
</script>