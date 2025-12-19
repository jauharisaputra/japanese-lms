<?php
/**
 * Menyimpan attempt ujian TO (belum dipakai penuh, bisa dikembangkan nanti).
 * Sekarang fokus fungsinya untuk struktur dasar dan konversi skor ke skala 0–100.
 */

function convertPlacementToReport(array $exam, array $raw_sections): array {
    // sections_json: max skor per bagian
    $sections = json_decode($exam["sections_json"] ?? "[]", true) ?: [];

    // mapping grup → key bagian J.TEST
    $map = [
        "bunpo"   => ["bunpo_goi"],
        "kanji"   => ["kanji"],
        "dokkai"  => ["dokkai","sakubun"],
        "choukai" => ["photo","list_read","ougo"],
        "kaiwa"   => ["kaiwa_setsumei"]
    ];

    $result = [];
    foreach ($map as $field => $keys) {
        $raw_sum = 0;
        $max_sum = 0;
        foreach ($keys as $k) {
            $max_sum += (int)($sections[$k] ?? 0);
            $raw_sum += (int)($raw_sections[$k] ?? 0);
        }
        if ($max_sum <= 0) {
            $result[$field] = null;
        } else {
            $result[$field] = round($raw_sum / $max_sum * 100, 1);
        }
    }
    return $result;
}
