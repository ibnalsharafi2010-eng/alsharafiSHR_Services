<?php
session_start();
require_once 'include/database_firebase.php';
require_once 'include/file_upload.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Handle ticket submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $priority = $_POST['priority'] ?? 'normal';
    
    // Validate required fields
    if (empty($subject) || empty($message)) {
        $error = "جميع الحقول المطلوبة يجب ملؤها";
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
            // Get user info from session
            $user = FirebaseDB::getUserByUsername($_SESSION['username']);
            
            // Save ticket to Firebase with user data
            $ticketData = [
                'subject' => $subject,
                'message' => $message,
                'priority' => $priority,
                'username' => $_SESSION['username'],
                'email' => $user['email'] ?? '',
                'name' => $user['username'] ?? '',
                'status' => 'مفتوح',
                'created' => date('Y-m-d H:i:s'),
                'updated' => date('Y-m-d H:i:s'),
                'attachments' => $uploadedFiles
            ];
            
            $ticketId = FirebaseDB::saveTicket($ticketData);
            
            if ($ticketId) {
                // Redirect to success page
                header("Location: submit_ticket.php?ticket_id=" . $ticketId);
                exit();
            } else {
                $error = "حدث خطأ أثناء حفظ التذكرة. يرجى المحاولة مرة أخرى.";
            }
        }
    }
}

// Get user info for display
$user = FirebaseDB::getUserByUsername($_SESSION['username']);
$_SESSION['user_id'] = $_SESSION['username']; // Use username as user_id
?>

<!DOCTYPE html>
<html dir="rtl" class="rtl" lang="ar-YE">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>فتح تذكرة جديدة - SHR Support</title>
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
</head>
<body>
    <div id="container">
        <div id="header">
            <div class="pull-right flush-right">
                <p>
                    <span style="color: #28a745; font-weight: bold;">مرحباً: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <span style="margin: 0 10px;">|</span>
                    <a href="logout.php" style="color: #dc3545;">خروج</a>
                </p>
            </div>
            <a class="pull-left" id="logo" href="index.html" title="مركز الدعم">
                <span class="valign-helper"></span>
                <img src="logo.php" border=0 alt="SHR Support">
            </a>
        </div>
        <div class="clear"></div>
        <ul id="nav" class="flush-left">
            <li><a href="index.html">الصفحة الرئيسية لمركز الدعم</a></li>
            <li><a href="open.php" class="active">فتح تذكرة جديدة</a></li>
            <li><a href="view_ar_YE.html">التحقق من حالة تذكرة</a></li>
            <li><a href="tickets.php">التذاكر</a></li>
            <li><a href="dashboard.php">لوحة التحكم</a></li>
        </ul>
        <div id="content">
            <div id="landing_page">
                <div class="main-content">
                    <div class="thread-body">
                        <h1>فتح تذكرة جديدة</h1>
                        <p>يرجى ملء النموذج أدناه لإنشاء تذكرة دعم جديدة.</p>
                        
                        <?php if (isset($error)): ?>
                            <div style="background-color: #ffe0e0; border: 1px solid #a00; color: #a00; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="open.php" enctype="multipart/form-data">
                            <fieldset>
                                <legend>معلومات التذكرة</legend>
                                
                                <div class="form-group">
                                    <label for="subject">الموضوع *</label>
                                    <input type="text" name="subject" id="subject" required 
                                           value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="priority">الأولوية</label>
                                    <select name="priority" id="priority">
                                        <option value="low">منخفضة</option>
                                        <option value="normal" selected>عادية</option>
                                        <option value="high">عالية</option>
                                        <option value="urgent">عاجلة</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="message">الرسالة *</label>
                                    <textarea name="message" id="message" rows="8" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="attachments">المرفقات (اختياري)</label>
                                    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 4px; background-color: #f9f9f9;">
                                        <p style="margin: 0 0 10px 0; color: #666; font-size: 0.9em;">
                                            يمكنك إرفاق صور أو مستندات. الحد الأقصى: 5 ميجابايت لكل ملف
                                        </p>
                                        <div id="file-upload-container">
                                            <div class="file-input-group" style="margin-bottom: 10px;">
                                                <input type="file" name="attachments[]" class="file-input" 
                                                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar"
                                                       onchange="showFileName(this)">
                                                <span class="file-name" style="margin-right: 10px; color: #666;"></span>
                                                <button type="button" class="remove-file" onclick="removeFile(this)" style="display: none; background-color: #dc3545; color: white; border: none; padding: 2px 8px; border-radius: 3px; cursor: pointer;">حذف</button>
                                            </div>
                                        </div>
                                        <button type="button" onclick="addFileInput()" style="background-color: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-top: 10px;">+ إضافة ملف آخر</button>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="blue button">إرسال التذكرة</button>
                                </div>
                            </fieldset>
                        </form>
                        
                        <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 4px;">
                            <h4>معلومات المستخدم:</h4>
                            <p><strong>الاسم:</strong> <?php echo htmlspecialchars($user['username'] ?? ''); ?></p>
                            <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                            <p><em>سيتم إضافة هذه المعلومات تلقائياً إلى التذكرة عند الإرسال.</em></p>
                        </div>
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
        function showFileName(input) {
            const fileName = input.files[0] ? input.files[0].name : '';
            const fileGroup = input.parentElement;
            const nameSpan = fileGroup.querySelector('.file-name');
            const removeBtn = fileGroup.querySelector('.remove-file');
            
            nameSpan.textContent = fileName;
            removeBtn.style.display = fileName ? 'inline-block' : 'none';
        }
        
        function removeFile(button) {
            const fileGroup = button.parentElement;
            const input = fileGroup.querySelector('.file-input');
            const nameSpan = fileGroup.querySelector('.file-name');
            const removeBtn = fileGroup.querySelector('.remove-file');
            
            input.value = '';
            nameSpan.textContent = '';
            removeBtn.style.display = 'none';
        }
        
        function addFileInput() {
            const container = document.getElementById('file-upload-container');
            const fileGroups = container.querySelectorAll('.file-input-group');
            
            // Limit to 5 files
            if (fileGroups.length >= 5) {
                alert('الحد الأقصى هو 5 ملفات');
                return;
            }
            
            const newGroup = document.createElement('div');
            newGroup.className = 'file-input-group';
            newGroup.style.marginBottom = '10px';
            newGroup.innerHTML = `
                <input type="file" name="attachments[]" class="file-input" 
                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar"
                       onchange="showFileName(this)">
                <span class="file-name" style="margin-right: 10px; color: #666;"></span>
                <button type="button" class="remove-file" onclick="removeFile(this)" style="display: none; background-color: #dc3545; color: white; border: none; padding: 2px 8px; border-radius: 3px; cursor: pointer;">حذف</button>
            `;
            
            container.appendChild(newGroup);
        }
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const fileInputs = document.querySelectorAll('.file-input');
            let totalSize = 0;
            
            fileInputs.forEach(input => {
                if (input.files[0]) {
                    totalSize += input.files[0].size;
                }
            });
            
            // Check total size (5MB per file, but we'll allow up to 10MB total)
            if (totalSize > 10 * 1024 * 1024) {
                e.preventDefault();
                alert('حجم الملفات كبير جداً. الحد الأقصى هو 10 ميجابايت إجمالي');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>
