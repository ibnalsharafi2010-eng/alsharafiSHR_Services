<?php
require_once 'include/file_upload.php';

// Function to display attachments in tickets and replies
function displayAttachments($attachments, $isAdmin = false) {
    if (empty($attachments) || !is_array($attachments)) {
        return '';
    }
    
    $html = '<div style="margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 4px; border: 1px solid #dee2e6;">';
    $html .= '<h5 style="margin: 0 0 10px 0; color: #495057; font-size: 0.9em;">📎 المرفقات:</h5>';
    
    foreach ($attachments as $attachment) {
        if (!isset($attachment['filename'])) continue;
        
        $fileInfo = FileUploadManager::getFileInfo($attachment['filename']);
        if (!$fileInfo) continue;
        
        $icon = FileUploadManager::getFileIcon($attachment['filename']);
        $size = FileUploadManager::formatFileSize($fileInfo['size']);
        $originalName = $attachment['original_name'] ?? $attachment['filename'];
        
        $html .= '<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; padding: 8px; background-color: white; border-radius: 3px; border: 1px solid #e9ecef;">';
        $html .= '<span style="font-size: 1.2em;">' . $icon . '</span>';
        $html .= '<div style="flex: 1;">';
        $html .= '<div style="font-weight: 500; color: #212529;">' . htmlspecialchars($originalName) . '</div>';
        $html .= '<div style="font-size: 0.8em; color: #6c757d;">' . $size . '</div>';
        $html .= '</div>';
        $html .= '<a href="' . $fileInfo['download_url'] . '" style="background-color: #007bff; color: white; padding: 4px 8px; text-decoration: none; border-radius: 3px; font-size: 0.8em;" target="_blank">تحميل</a>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    return $html;
}

// Function to display inline images
function displayInlineImages($attachments) {
    if (empty($attachments) || !is_array($attachments)) {
        return '';
    }
    
    $html = '';
    foreach ($attachments as $attachment) {
        if (!isset($attachment['filename'])) continue;
        
        $fileInfo = FileUploadManager::getFileInfo($attachment['filename']);
        if (!$fileInfo) continue;
        
        // Only display images inline
        if (FileUploadManager::isImage($attachment['filename'])) {
            $html .= '<div style="margin: 10px 0; text-align: center;">';
            $html .= '<img src="' . $fileInfo['url'] . '" alt="' . htmlspecialchars($attachment['original_name'] ?? 'صورة') . '" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
            $html .= '<div style="margin-top: 5px; font-size: 0.8em; color: #6c757d;">' . htmlspecialchars($attachment['original_name'] ?? 'صورة') . '</div>';
            $html .= '</div>';
        }
    }
    
    return $html;
}
?>
