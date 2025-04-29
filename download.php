<?php
if (!isset($_GET['token'])) {
    http_response_code(400);
    exit("Missing token.");
}

$token = preg_replace('/[^0-9]/', '', $_GET['token']); // sanitize

$zipPath = __DIR__ . "/renamed_pdfs_$token.zip";
$extractDir = __DIR__ . "/extracted_$token";

if (!file_exists($zipPath)) {
    http_response_code(404);
    exit("File not found.");
}

// Set headers for download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="renamed_pdfs.zip"');
header('Content-Length: ' . filesize($zipPath));
flush();
readfile($zipPath);

// Cleanup after download
unlink($zipPath);

function deleteFolder($folder) {
    if (!is_dir($folder)) return;
    $items = scandir($folder);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $folder . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? deleteFolder($path) : unlink($path);
    }
    rmdir($folder);
}

deleteFolder($extractDir);
exit;
