<?php
session_start();
require_once 'include/database_firebase.php';
require_once 'include/notifications.php';
require_once 'include/user_roles.php';
require_once 'include/file_upload.php';
require_once 'include/attachment_display.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

// Check if user is regular user
if (!UserRoleManager::checkAccess('user', $_SESSION['username'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Ensure notifications directory exists
NotificationManager::ensureNotificationsDirectory();

// Get user tickets
$userTickets = FirebaseDB::getUserTickets($_SESSION['username']);

// Handle filtering
$status_filter = $_GET['status'] ?? '';

// Filter tickets based on status
$filteredTickets = $userTickets;
if ($status_filter && $status_filter !== 'all') {
    $filteredTickets = [];
    foreach ($userTickets as $ticket) {
        if ($ticket['status'] === $status_filter) {
            $filteredTickets[] = $ticket;
        }
    }
}

// Handle reply submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply'])) {
    $ticket_id = $_POST['ticket_id'] ?? '';
    $reply_message = $_POST['reply_message'] ?? '';
    
    if (!empty($ticket_id) && !empty($reply_message)) {
        // Handle file uploads for reply
        $uploadedFiles = [];
        if (isset($_FILES['reply_attachments']) && !empty($_FILES['reply_attachments']['name'][0])) {
            foreach ($_FILES['reply_attachments']['name'] as $key => $name) {
                if (!empty($name)) {
                    $file = [
                        'name' => $_FILES['reply_attachments']['name'][$key],
                        'type' => $_FILES['reply_attachments']['type'][$key],
                        'tmp_name' => $_FILES['reply_attachments']['tmp_name'][$key],
                        'error' => $_FILES['reply_attachments']['error'][$key],
                        'size' => $_FILES['reply_attachments']['size'][$key]
                    ];
                    
                    $uploadResult = FileUploadManager::handleFileUpload($file, $ticket_id, 'reply');
                    if ($uploadResult['success']) {
                        $uploadedFiles[] = $uploadResult;
                    } else {
                        $error = $uploadResult['message'];
                        break;
                    }
                }
            }
        }
        
        // If no upload errors, save reply
        if (!isset($error)) {
            $reply_data = [
                'message' => $reply_message,
                'username' => $_SESSION['username'],
                'timestamp' => date('Y-m-d H:i:s'),
                'type' => 'user',
                'attachments' => $uploadedFiles
            ];
            
            // Add reply to ticket
            FirebaseDB::addReplyToTicket($ticket_id, $reply_data);
            
            // Redirect to prevent form resubmission
            header("Location: user_tickets.php?ticket_id=" . $ticket_id);
            exit();
        }
    }
}

// Get specific ticket details if requested
$ticket_id = $_GET['ticket_id'] ?? '';
$selected_ticket = null;
$replies = [];

if ($ticket_id) {
    $selected_ticket = FirebaseDB::getTicketById($ticket_id);
    $replies = FirebaseDB::getTicketReplies($ticket_id);
    
    // Mark ticket as read when viewed
    NotificationManager::markTicketAsRead($ticket_id, $_SESSION['username']);
    NotificationManager::markRepliesAsRead($ticket_id, $_SESSION['username']);
}

// Get user notifications
$unreadCount = NotificationManager::getUnreadTicketsCount($_SESSION['username']);
$repliesCount = count(NotificationManager::getTicketsWithNewReplies($_SESSION['username']));
$totalNotifications = NotificationManager::getTotalNotificationsCount($_SESSION['username']);
?>

<!DOCTYPE html>
<html dir="rtl" class="rtl" lang="ar-YE">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>تذاكري - لوحة المستخدم</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/osticket.css@65ca4e6.css" media="screen"/>
    <link rel="stylesheet" href="assets/default/css/theme.css@65ca4e6.css" media="screen"/>
    <link rel="stylesheet" href="css/rtl.css@65ca4e6.css" media="screen"/>
    <link rel="stylesheet" href="css/forms.css" media="screen"/>
    <link rel="icon" type="image/png" href="images/oscar-favicon-32x32.png" sizes="32x32" />
    <script type="text/javascript" src="js/jquery-3.5.1.min.js"></script>
    <script src="js/osticket.js"></script>
    <script>
        // Import Firebase functions
        import { initializeApp } from "firebase/app";
        import { getAnalytics } from "firebase/analytics";
        
        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const analytics = getAnalytics(app);
    </script>
    <style>
        .notification-badge {
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7em;
            font-weight: bold;
            margin-right: 5px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .ticket-new {
            background-color: #fff3cd;
            border-left-color: #ffc107;
        }
        
        .ticket-new-reply {
            background-color: #d1ecf1;
            border-left-color: #17a2b8;
        }
        
        .user-controls {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div id="container">
        <div id="header">
            <div class="pull-right flush-right">
                <p>
                    <span style="color: #28a745; font-weight: bold;">مرحباً: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <?php if ($totalNotifications > 0): ?>
                        <span class="notification-badge"><?php echo $totalNotifications; ?></span>
                    <?php endif; ?>
                    <span style="margin: 0 10px;">|</span>
                    <a href="logout.php" style="color: #dc3545;">خروج</a>
                </p>
            </div>
            <a class="pull-left" id="logo" href="index.php" title="مركز الدعم">
                <span class="valign-helper"></span>
                <img src="logo.php" border=0 alt="SHR Support">
            </a>
        </div>
        <div class="clear"></div>
        <ul id="nav" class="flush-left">
            <li><a href="index.php">الصفحة الرئيسية</a></li>
            <li><a href="open.php">فتح تذكرة جديدة</a></li>
            <li><a href="user_tickets.php" class="active">تذاكري</a></li>
            <li><a href="user_dashboard.php">لوحة التحكم</a></li>
        </ul>
        <div id="content">
            <div id="landing_page">
                <div class="main-content">
                    <div class="thread-body">
                        <h1>تذاكري</h1>
                        
                        <?php if ($totalNotifications > 0): ?>
                            <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                                📢 لديك <strong><?php echo $totalNotifications; ?></strong> إشعارات جديدة
                                <?php if ($unreadCount > 0): ?>
                                    (<?php echo $unreadCount; ?> تذاكر جديدة)
                                <?php endif; ?>
                                <?php if ($repliesCount > 0): ?>
                                    (<?php echo $repliesCount; ?> ردود جديدة)
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- User Controls -->
                        <div class="user-controls">
                            <h3>🎛️ تحكم المستخدم</h3>
                            <form method="get" class="filter-form">
                                <label>
                                    <strong>الحالة:</strong>
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="">الكل</option>
                                        <option value="مفتوح" <?php echo $status_filter === 'مفتوح' ? 'selected' : ''; ?>>مفتوح</option>
                                        <option value="قيد المعالجة" <?php echo $status_filter === 'قيد المعالجة' ? 'selected' : ''; ?>>قيد المعالجة</option>
                                        <option value="مغلق" <?php echo $status_filter === 'مغلق' ? 'selected' : ''; ?>>مغلق</option>
                                    </select>
                                </label>
                                
                                <a href="user_tickets.php" class="blue button">عرض الكل</a>
                                <a href="open.php" class="blue button">فتح تذكرة جديدة</a>
                            </form>
                        </div>
                        
                        <?php if ($selected_ticket): ?>
                            <!-- Ticket Details -->
                            <div style="background-color: #e8f5e8; border: 1px solid #28a745; padding: 20px; border-radius: 6px; margin-bottom: 20px;">
                                <h3>تفاصيل التذكرة #<?php echo $selected_ticket['id']; ?></h3>
                                <div style="margin-bottom: 15px;">
                                    <p><strong>الموضوع:</strong> <?php echo htmlspecialchars($selected_ticket['subject']); ?></p>
                                    <p><strong>الحالة:</strong> 
                                        <span class="status-<?php echo $selected_ticket['status']; ?>">
                                            <?php 
                                                switch($selected_ticket['status']) {
                                                    case 'مفتوح': echo 'مفتوح'; break;
                                                    case 'قيد المعالجة': echo 'قيد المعالجة'; break;
                                                    case 'مغلق': echo 'مغلق'; break;
                                                    default: echo $selected_ticket['status']; break;
                                                }
                                            ?>
                                        </span>
                                    </p>
                                    <p><strong>التاريخ:</strong> <?php echo $selected_ticket['created']; ?></p>
                                    <p><strong>الرسالة:</strong></p>
                                    <div style="background-color: #fff; padding: 15px; border-radius: 4px; margin-top: 10px;">
                                        <?php 
                                        echo nl2br(htmlspecialchars($selected_ticket['message']));
                                        echo displayInlineImages($selected_ticket['attachments'] ?? []);
                                        ?>
                                    </div>
                                    <?php echo displayAttachments($selected_ticket['attachments'] ?? []); ?>
                                </div>
                                
                                <!-- User Reply Form -->
                                <div style="margin-top: 20px;">
                                    <h4>📝 إضافة رد</h4>
                                    <form method="post" action="user_tickets.php?ticket_id=<?php echo $selected_ticket['id']; ?>" enctype="multipart/form-data">
                                        <input type="hidden" name="ticket_id" value="<?php echo $selected_ticket['id']; ?>">
                                        <input type="hidden" name="reply" value="1">
                                        <div class="form-group">
                                            <label for="reply_message">الرد:</label>
                                            <textarea name="reply_message" id="reply_message" rows="4" required placeholder="اكتب ردك هنا..."></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>📎 مرفقات الرد (اختياري):</label>
                                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                                <input type="file" name="reply_attachments[]" id="reply_file_input" 
                                                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar"
                                                       style="display: none;" multiple onchange="showReplyFiles(this)">
                                                <button type="button" onclick="document.getElementById('reply_file_input').click()" 
                                                        style="background-color: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">
                                                    📎 إرفاق ملف
                                                </button>
                                                <span id="reply_files_info" style="color: #666; font-size: 0.9em;"></span>
                                            </div>
                                            <div id="reply_files_preview" style="margin-top: 10px;"></div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <button type="submit" class="blue button">إرسال الرد</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Existing Replies -->
                                <?php if (!empty($replies)): ?>
                                    <div style="margin-top: 20px;">
                                        <h4>💬 سجل الردود</h4>
                                        <?php foreach ($replies as $reply): ?>
                                            <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 4px; margin-bottom: 10px;">
                                                <div style="font-size: 0.9em; color: #6c757d; margin-bottom: 10px;">
                                                    <strong><?php echo htmlspecialchars($reply['username']); ?></strong> - 
                                                    <?php echo $reply['timestamp']; ?>
                                                    <?php if ($reply['type'] === 'admin'): ?>
                                                        <span style="background-color: #dc3545; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.8em;">مدير</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div><?php echo nl2br(htmlspecialchars($reply['message'])); ?></div>
                                                <?php 
                                                echo displayInlineImages($reply['attachments'] ?? []);
                                                echo displayAttachments($reply['attachments'] ?? []);
                                                ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="margin-top: 20px;">
                                    <a href="user_tickets.php" class="blue button">العودة لقائمة التذاكر</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Tickets List -->
                            <?php if (empty($filteredTickets)): ?>
                                <p style="text-align: center; padding: 40px; color: #666;">
                                    لا توجد تذاكر حالياً. <a href="open.php">افتح تذكرة جديدة</a>
                                </p>
                            <?php else: ?>
                                <div class="tickets-table">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <thead>
                                            <tr style="background-color: #f5f5f5;">
                                                <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">رقم التذكرة</th>
                                                <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">الموضوع</th>
                                                <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">الحالة</th>
                                                <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">التاريخ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $readTicketsFile = 'notifications/read_tickets_' . $_SESSION['username'] . '.json';
                                            $readTickets = [];
                                            if (file_exists($readTicketsFile)) {
                                                $data = file_get_contents($readTicketsFile);
                                                $readTickets = json_decode($data, true) ?: [];
                                            }
                                            
                                            foreach ($filteredTickets as $ticket): 
                                                $isUnread = !in_array($ticket['id'], $readTickets);
                                                $hasNewReply = NotificationManager::hasNewReplies($ticket['id'], $_SESSION['username']);
                                            ?>
                                                <tr style="cursor: pointer; <?php echo $isUnread ? 'background-color: #fff3cd;' : ($hasNewReply ? 'background-color: #d1ecf1;' : ''); ?>" 
                                                    onclick="window.location.href='user_tickets.php?ticket_id=<?php echo $ticket['id']; ?>'">
                                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                                        <strong>#<?php echo $ticket['id']; ?></strong>
                                                        <?php if ($isUnread): ?>
                                                            <span style="background-color: #ffc107; color: #856404; padding: 2px 5px; border-radius: 3px; font-size: 0.7em; margin-right: 5px;">جديد</span>
                                                        <?php endif; ?>
                                                        <?php if ($hasNewReply): ?>
                                                            <span style="background-color: #17a2b8; color: white; padding: 2px 5px; border-radius: 3px; font-size: 0.7em; margin-right: 5px;">رد جديد</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                                        <?php echo htmlspecialchars($ticket['subject']); ?>
                                                    </td>
                                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                                        <span class="status-<?php echo $ticket['status']; ?>">
                                                            <?php 
                                                                switch($ticket['status']) {
                                                                    case 'مفتوح': echo 'مفتوح'; break;
                                                                    case 'قيد المعالجة': echo 'قيد المعالجة'; break;
                                                                    case 'مغلق': echo 'مغلق'; break;
                                                                    default: echo $ticket['status']; break;
                                                                }
                                                            ?>
                                                        </span>
                                                    </td>
                                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                                        <?php echo $ticket['created']; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    
    <div id="footer">
        <p>Copyright &copy; 2026 SHR Support - All rights reserved.</p>
    </div>
    
    <script>
        function showReplyFiles(input) {
            const files = input.files;
            const info = document.getElementById('reply_files_info');
            const preview = document.getElementById('reply_files_preview');
            
            if (files.length > 0) {
                info.textContent = `تم اختيار ${files.length} ملف(ات)`;
                
                // Show file preview
                preview.innerHTML = '';
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileDiv = document.createElement('div');
                    fileDiv.style.cssText = 'display: flex; align-items: center; gap: 10px; margin-bottom: 5px; padding: 5px; background-color: #f8f9fa; border-radius: 3px;';
                    
                    const icon = getFileIcon(file.type);
                    const size = formatFileSize(file.size);
                    
                    fileDiv.innerHTML = `
                        <span>${icon}</span>
                        <span style="flex: 1;">${file.name}</span>
                        <span style="color: #666; font-size: 0.8em;">${size}</span>
                    `;
                    
                    preview.appendChild(fileDiv);
                }
            } else {
                info.textContent = '';
                preview.innerHTML = '';
            }
        }
        
        function getFileIcon(fileType) {
            if (fileType.startsWith('image/')) return '🖼️';
            if (fileType === 'application/pdf') return '📕';
            if (fileType.includes('word')) return '📘';
            if (fileType.includes('excel') || fileType.includes('sheet')) return '📗';
            if (fileType.includes('zip') || fileType.includes('rar')) return '📦';
            return '📄';
        }
        
        function formatFileSize(bytes) {
            const units = ['B', 'KB', 'MB', 'GB'];
            if (bytes === 0) return '0 B';
            const k = 1024;
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + units[i];
        }
        
        // Form validation for reply files
        document.querySelector('form').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('reply_file_input');
            if (fileInput.files.length > 0) {
                let totalSize = 0;
                for (let file of fileInput.files) {
                    totalSize += file.size;
                }
                
                // Check total size (5MB per file, but we'll allow up to 10MB total for replies)
                if (totalSize > 10 * 1024 * 1024) {
                    e.preventDefault();
                    alert('حجم الملفات كبير جداً. الحد الأقصى هو 10 ميجابايت إجمالي للرد');
                    return false;
                }
            }
            return true;
        });
    </script>
</body>
</html>
