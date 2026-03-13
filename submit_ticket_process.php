<?php
session_start();
require_once 'include/database_firebase.php';
require_once 'include/file_upload.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get form data
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $priority = $_POST['priority'] ?? 'normal';
    
    // Get user info
    $user = FirebaseDB::getUserByUsername($_SESSION['username']);
    
    // Simple validation
    if (empty($subject) || empty($message)) {
        $error = "جميع الحقول مطلوبة";
    } else {
        // Handle file uploads
        $uploadedFiles = [];
        if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
            foreach ($_FILES['attachments']['name'] as $key => $name) {
                if (!empty($name)) {
                    $file = [
                        'name' => $_FILES['attachments']['name'][$key],
                        'type' => $_FILES['attachments']['type'][$key],
                        'tmp_name' => $_FILES['attachments']['tmp_name'][$key],
                        'error' => $_FILES['attachments']['error'][$key],
                        'size' => $_FILES['attachments']['size'][$key]
                    ];
                    
                    $uploadResult = FileUploadManager::handleFileUpload($file);
                    if ($uploadResult['success']) {
                        $uploadedFiles[] = $uploadResult;
                    } else {
                        $error = $uploadResult['message'];
                        break;
                    }
                }
            }
        }
        
        // If no upload errors, save ticket
        if (!isset($error)) {
            // Prepare ticket data
            $ticketData = [
                'username' => $_SESSION['username'],
                'email' => $user['email'] ?? '',
                'subject' => $subject,
                'message' => $message,
                'priority' => $priority,
                'status' => 'مفتوح',
                'created' => date('Y-m-d H:i:s'),
                'attachments' => $uploadedFiles
            ];
            
            // Save ticket to Firebase
            $ticketId = FirebaseDB::saveTicket($ticketData);
            
            if ($ticketId) {
                $success = "تم إرسال التذكرة بنجاح! رقم التذكرة: #" . $ticketId;
            } else {
                $error = "حدث خطأ أثناء إرسال التذكرة. يرجى المحاولة مرة أخرى.";
            }
        }
    }
}
?>
