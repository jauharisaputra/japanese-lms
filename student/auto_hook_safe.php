<?php
/* ======================================================
   AUTO PATCH CENTRAL HOOK (STUDENT â€“ SUPER SAFE)
   ======================================================
   - Scan semua folder/subfolder
   - Backup file otomatis
   - Sisipkan require_once 'central_hook.php' jika belum ada
   ====================================================== */

$studentDir = __DIR__; // folder student
$hookFile = 'central_hook.php';
$hookRequire = "require_once __DIR__ . '/$hookFile';\n";

// Fungsi rekursif scan folder
function scanFolder($dir) {
    $files = [];
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        $fullPath = $dir . DIRECTORY_SEPARATOR . $f;
        if (is_dir($fullPath)) {
            $files = array_merge($files, scanFolder($fullPath));
        } elseif (is_file($fullPath) && pathinfo($fullPath, PATHINFO_EXTENSION) === 'php') {
            $files[] = $fullPath;
        }
    }
    return $files;
}

$allFiles = scanFolder($studentDir);
$patchedCount = 0;

foreach ($allFiles as $filePath) {
    $content = file_get_contents($filePath);

    // Skip jika sudah ada hook
    if (strpos($content, $hookFile) !== false) continue;

    // Backup file
    $backupFile = $filePath . '.backup_' . date('Ymd_His');
    copy($filePath, $backupFile);

    // Cari posisi sisipkan hook
    $pos = strpos($content, '<!DOCTYPE html>');
    if ($pos === false) $pos = strpos($content, '<html');
    if ($pos === false) $pos = 0; // fallback atas

    $newContent = substr($content, 0, $pos) . $hookRequire . substr($content, $pos);

    file_put_contents($filePath, $newContent);
    $patchedCount++;
    echo "âœ… Hook ditambahkan di file: $filePath (backup: $backupFile)\n";
}

echo "\nðŸŽ‰ Selesai! Total file dipatch: $patchedCount\n";
echo "Semua file PHP di folder student/ sudah include central_hook.php.\n";

