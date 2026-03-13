<?php
class FileUploadManager {
    
    // Allowed file types
    private static $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'text/plain' => 'txt',
        'application/zip' => 'zip',
        'application/x-rar-compressed' => 'rar'
    ];
    
    // Maximum file size (5MB)
    private static $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Upload directory
    private static $uploadDir = 'uploads/';
    
    // Handle file upload
    public static function handleFileUpload($file, $ticketId = null, $replyId = null) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'خطأ في رفع الملف'];
        }
        
        // Check file size
        if ($file['size'] > self::$maxSize) {
            return ['success' => false, 'message' => 'حجم الملف كبير جداً. الحد الأقصى 5 ميجابايت'];
        }
        
        // Check file type
        $fileType = mime_content_type($file['tmp_name']);
        if (!isset(self::$allowedTypes[$fileType])) {
            return ['success' => false, 'message' => 'نوع الملف غير مدعوم'];
        }
        
        // Create upload directory if not exists
        if (!is_dir(self::$uploadDir)) {
            mkdir(self::$uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = self::$allowedTypes[$fileType];
        $prefix = $ticketId ? 'ticket_' . $ticketId : 'upload';
        $uniqueId = uniqid();
        $filename = $prefix . '_' . $uniqueId . '.' . $extension;
        $filepath = self::$uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'original_name' => $file['name'],
                'size' => $file['size'],
                'type' => $fileType,
                'path' => $filepath
            ];
        } else {
            return ['success' => false, 'message' => 'فشل في رفع الملف'];
        }
    }
    
    // Get file info
    public static function getFileInfo($filename) {
        $filepath = self::$uploadDir . $filename;
        if (!file_exists($filepath)) {
            return null;
        }
        
        return [
            'name' => $filename,
            'size' => filesize($filepath),
            'type' => mime_content_type($filepath),
            'url' => self::$uploadDir . $filename,
            'download_url' => 'download.php?file=' . $filename
        ];
    }
    
    // Delete file
    public static function deleteFile($filename) {
        $filepath = self::$uploadDir . $filename;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
    
    // Format file size
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    // Get allowed file types for display
    public static function getAllowedTypes() {
        return [
            'الصور' => ['jpg', 'jpeg', 'png', 'gif'],
            'المستندات' => ['pdf', 'doc', 'docx', 'txt'],
            'الجداول' => ['xls', 'xlsx'],
            'الأرشيف' => ['zip', 'rar']
        ];
    }
    
    // Check if file is image
    public static function isImage($filename) {
        $filepath = self::$uploadDir . $filename;
        if (!file_exists($filepath)) {
            return false;
        }
        
        $type = mime_content_type($filepath);
        return strpos($type, 'image/') === 0;
    }
    
    // Get file icon based on type
    public static function getFileIcon($filename) {
        $filepath = self::$uploadDir . $filename;
        if (!file_exists($filepath)) {
            return '📄';
        }
        
        $type = mime_content_type($filepath);
        
        if (strpos($type, 'image/') === 0) {
            return '🖼️';
        } elseif ($type === 'application/pdf') {
            return '📕';
        } elseif (strpos($type, 'word') !== false) {
            return '📘';
        } elseif (strpos($type, 'excel') !== false || strpos($type, 'sheet') !== false) {
            return '📗';
        } elseif (strpos($type, 'zip') !== false || strpos($type, 'rar') !== false) {
            return '📦';
        } else {
            return '📄';
        }
    }
}
?>
