<?php
require_once 'include/file_upload.php';

// Get file parameter
$filename = $_GET['file'] ?? '';

// Security check
if (empty($filename) || strpos($filename, '..') !== false) {
    http_response_code(400);
    echo 'طلب غير صالح';
    exit();
}

// Get file info
$fileInfo = FileUploadManager::getFileInfo($filename);
if (!$fileInfo) {
    http_response_code(404);
    echo 'الملف غير موجود';
    exit();
}

// Set headers for download
header('Content-Type: ' . $fileInfo['type']);
header('Content-Disposition: attachment; filename="' . basename($fileInfo['name']) . '"');
header('Content-Length: ' . $fileInfo['size']);
header('Cache-Control: private, no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file
readfile($fileInfo['url']);
exit();
?>
