<?php
$file = __DIR__ . "/data/to_n5.json";

if (!file_exists($file)) {
    echo "TIDAK ADA FILE\n";
    exit;
}

$json = file_get_contents($file);
echo "Panjang JSON: " . strlen($json) . "\n";

$data = json_decode($json, true);
echo "json_last_error: " . json_last_error() . "\n";
echo "json_last_error_msg: " . json_last_error_msg() . "\n";
