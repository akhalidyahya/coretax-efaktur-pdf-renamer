<?php
require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

function extractReferensiFromPdf($pdfPath)
{
    $parser = new Parser();
    $pdf = $parser->parseFile($pdfPath);
    $text = $pdf->getText();

    // Match: (Referensi: VALUE)
    if (preg_match('/\(\s*Referensi:\s*([^)]+)\)/i', $text, $matches)) {
        return trim($matches[1]);
    }
    return null;
}

function sanitizeFilename($filename)
{
    return preg_replace('/[^A-Za-z0-9 _.-]/', '', $filename);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zip_file'])) {
    $zipPath = $_FILES['zip_file']['tmp_name'];
    $timestamp = time();
    $extractDir = __DIR__ . "/extracted_$timestamp";
    $outputZipPath = __DIR__ . "/renamed_pdfs_$timestamp.zip";

    mkdir($extractDir);
    $zip = new ZipArchive;
    if ($zip->open($zipPath) === TRUE) {
        $zip->extractTo($extractDir);
        $zip->close();

        $pdfFiles = glob($extractDir . '/*.pdf');
        if (!empty($pdfFiles)) {
            $zipRenamed = new ZipArchive();
            $zipRenamed->open($outputZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach ($pdfFiles as $pdfFile) {
                $referensi = extractReferensiFromPdf($pdfFile);
                if ($referensi) {
                    $cleanName = sanitizeFilename($referensi);
                    $newName = $extractDir . '/' . $cleanName . '.pdf';
                    $counter = 1;
                    while (file_exists($newName)) {
                        $newName = $extractDir . '/' . $cleanName . "_$counter.pdf";
                        $counter++;
                    }
                    rename($pdfFile, $newName);
                    $zipRenamed->addFile($newName, basename($newName));
                }
            }

            $zipRenamed->close();
            echo json_encode([
                'status' => 'success',
                'download_url' => "download.php?token=$timestamp"
            ]);
            exit;
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'Failed to process ZIP']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Coretax Efaktur PDF Renamer by Referensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🧾</text></svg>">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-xl bg-white p-8 rounded-2xl shadow-lg">
        <h1 class="text-2xl font-bold text-center mb-6">📄 Rename Coretax E-Faktur PDFs by <span class="text-blue-600">Referensi</span></h1>

        <form id="uploadForm" class="space-y-4">
            <div class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-lg p-6 bg-gray-50 hover:bg-gray-100 transition">
                <input type="file" name="zip_file" accept=".zip" required class="hidden" id="fileInput">
                <label for="fileInput" class="text-gray-600 cursor-pointer text-center">
                    <p class="text-lg">Click or drag to upload your ZIP file</p>
                    <p class="text-sm text-gray-400 mt-1">Only .zip files containing PDFs</p>
                </label>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                <div id="progressBar" class="bg-blue-500 h-full w-0 transition-all duration-200 ease-in-out"></div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition font-semibold">
                🚀 Upload & Rename
            </button>

            <div id="downloadSection" class="mt-6 text-center hidden">
                <p class="text-green-600 font-medium">✅ Processing complete!</p>
                <a id="downloadLink" href="#" class="mt-2 inline-block bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition">
                    ⬇️ Download Renamed ZIP
                </a>
            </div>
        </form>

        <hr class="my-8 border-gray-300">

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-blue-800">
            <h2 class="text-lg font-semibold mb-2">🛠 How to Use This Tool</h2>
            <ol class="list-decimal list-inside space-y-2 text-sm">
                <li>Create a <strong>.zip</strong> file that contains one or more <strong>PDF invoices</strong>.</li>
                <li>Ensure each PDF contains a line like <code>Referensi: YOUR-REFERENCE</code>.</li>
                <li>Upload your ZIP file using the form above.</li>
                <li>The system will extract the Referensi number from each PDF and rename the file accordingly.</li>
                <li>Once complete, you'll get a <strong>ZIP download</strong> containing all renamed PDFs.</li>
                <li><strong>We never store your data. All files are processed temporarily and deleted automatically after download — so don’t forget to download.</strong></li>
            </ol>
            <p class="mt-4 text-xs text-blue-600 italic">Note: This tool works best with machine-readable PDFs (not scanned images).</p>
            <div class="text-center mt-2">
                <a href="https://github.com/akhalidyahya/coretax-efaktur-pdf-renamer" target="_blank"
                    class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 h-4 mr-1" viewBox="0 0 24 24">
                        <path d="M12 .5C5.73.5.5 5.73.5 12a11.5 11.5 0 008.06 10.94c.59.11.81-.26.81-.58v-2.02c-3.28.71-3.97-1.58-3.97-1.58-.53-1.35-1.3-1.71-1.3-1.71-1.07-.73.08-.72.08-.72 1.18.08 1.8 1.21 1.8 1.21 1.05 1.8 2.75 1.28 3.42.98.11-.76.41-1.28.75-1.57-2.62-.3-5.38-1.31-5.38-5.83 0-1.29.47-2.35 1.24-3.17-.12-.31-.54-1.56.12-3.26 0 0 .99-.32 3.25 1.22a11.3 11.3 0 015.92 0C17.7 5.43 18.7 5.75 18.7 5.75c.66 1.7.24 2.95.12 3.26.77.82 1.24 1.88 1.24 3.17 0 4.53-2.77 5.53-5.41 5.82.42.37.8 1.1.8 2.22v3.3c0 .32.22.69.82.58A11.5 11.5 0 0023.5 12C23.5 5.73 18.27.5 12 .5z" />
                    </svg>
                    View on GitHub
                </a>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('uploadForm');
        const progressBar = document.getElementById('progressBar');
        const downloadSection = document.getElementById('downloadSection');
        const downloadLink = document.getElementById('downloadLink');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const fileInput = document.getElementById('fileInput');
            const file = fileInput.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('zip_file', file);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percent = (e.loaded / e.total) * 100;
                    progressBar.style.width = percent + '%';
                }
            };

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.status === 'success') {
                            downloadLink.href = res.download_url;
                            downloadSection.classList.remove('hidden');
                        } else {
                            alert('Error: ' + res.message);
                        }
                    } catch (err) {
                        alert('Invalid server response.');
                    }
                } else {
                    alert('Upload failed.');
                }
            };

            xhr.send(formData);
        });
    </script>
</body>

</html>