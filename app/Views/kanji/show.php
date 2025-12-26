<?php
// $kanji: array berisi data 1 kanji, misalnya:
// ['kanji' => '北', 'on_yomi' => 'ホク', 'kun_yomi' => 'きた', 'arti' => 'utara', 'stroke_svg_path' => 'public/svg/animcjk/kanji/21271.svg']
?>

<div class="kanji-card">
    <div class="kanji-char">
        <object type="image/svg+xml" data="/<?= htmlspecialchars($kanji['stroke_svg_path'], ENT_QUOTES) ?>">
        </object>
    </div>

    <div class="kanji-info">
        <div class="kanji-main">
            <span class="kanji-symbol">
                <?= htmlspecialchars($kanji['kanji'], ENT_QUOTES) ?>
            </span>
        </div>

        <div class="kanji-reading">
            <div>On: <?= htmlspecialchars($kanji['on_yomi'] ?? '', ENT_QUOTES) ?></div>
            <div>Kun: <?= htmlspecialchars($kanji['kun_yomi'] ?? '', ENT_QUOTES) ?></div>
            <div>Arti: <?= htmlspecialchars($kanji['arti'] ?? '', ENT_QUOTES) ?></div>
        </div>
    </div>
</div>