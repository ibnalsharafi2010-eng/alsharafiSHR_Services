<?php
session_start();
require_once 'include/database_firebase.php';

// Check if user is logged in
if (isset($_SESSION['logged_in']) && isset($_SESSION['username'])) {
    $user = FirebaseDB::getUserByUsername($_SESSION['username']);
    $_SESSION['user_id'] = $_SESSION['username']; // Use username as user_id
} else {
    $user = null;
}
?>

<!DOCTYPE html>
<html dir="rtl" class="rtl" lang="ar-YE">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>SHR Support - مركز الدعم</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/osticket.css@65ca4e6.css" media="screen"/>
    <link rel="stylesheet" href="assets/default/css/theme.css@65ca4e6.css" media="screen"/>
    <link rel="stylesheet" href="css/rtl.css@65ca4e6.css" media="screen"/>
    <link rel="stylesheet" href="css/forms.css" media="screen"/>
    <link rel="icon" type="image/png" href="images/oscar-favicon-32x32.png" sizes="32x32" />
    <script type="text/javascript" src="js/jquery-3.5.1.min.js"></script>
    <script src="js/osticket.js"></script>
</head>
<body>
    <div id="container">
        <div id="header">
            <div class="pull-right flush-right">
                <p>
                    <?php if ($user): ?>
                        <span style="color: #28a745; font-weight: bold;">مرحباً: <?php echo htmlspecialchars($user['username']); ?></span>
                        <span style="margin: 0 10px;">|</span>
                        <a href="logout.php" style="color: #dc3545;">خروج</a>
                    <?php else: ?>
                        <a href="login.php">تسجيل الدخول</a>
                    <?php endif; ?>
                </p>
            </div>
            <a class="pull-left" id="logo" href="index.php" title="مركز الدعم">
                <span class="valign-helper"></span>
                <img src="logo.php" border=0 alt="SHR Support">
            </a>
        </div>
        <div class="clear"></div>
        <ul id="nav" class="flush-left">
            <li><a href="index.php" class="active">الصفحة الرئيسية لمركز الدعم</a></li>
            <li><a href="open.php">فتح تذكرة جديدة</a></li>
            <li><a href="view_ar_YE.html">التحقق من حالة تذكرة</a></li>
            <li><a href="tickets.php">التذاكر</a></li>
        </ul>
        <div id="content">
            <div id="landing_page">
                <div class="main-content">
                    <div class="thread-body">
                        <h1>مرحباً بك في مركز الدعم</h1>
                        <p>نحن هنا لمساعدتك في حل أي مشاكل أو الإجابة على استفساراتك.</p>
                        
                        <?php if ($user): ?>
                            <div style="background-color: #e8f5e8; border: 1px solid #28a745; padding: 20px; border-radius: 6px; margin: 20px 0;">
                                <h3 style="color: #155724; margin-bottom: 15px;">🎉 مرحباً بك في حسابك!</h3>
                                <p style="color: #155724;">يمكنك الآن:</p>
                                <ul style="color: #155724; margin-right: 20px;">
                                    <li>فتح تذكرة دعم جديدة</li>
                                    <li>عرض تذاكرك السابقة</li>
                                    <li>الوصول إلى لوحة التحكم</li>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="features" style="display: flex; gap: 20px; margin: 30px 0;">
                            <div class="feature" style="flex: 1; padding: 20px; border: 1px solid #ddd; border-radius: 6px; text-align: center;">
                                <h3>📝 فتح تذكرة</h3>
                                <p>إنشاء تذكرة دعم جديدة للحصول على المساعدة</p>
                                <a href="open.php" class="blue button">فتح تذكرة</a>
                            </div>
                            
                            <div class="feature" style="flex: 1; padding: 20px; border: 1px solid #ddd; border-radius: 6px; text-align: center;">
                                <h3>🔍 التحقق من التذكرة</h3>
                                <p>متابعة حالة تذكرتك الموجودة</p>
                                <a href="view_ar_YE.html" class="blue button">التحقق من التذكرة</a>
                            </div>
                            
                            <div class="feature" style="flex: 1; padding: 20px; border: 1px solid #ddd; border-radius: 6px; text-align: center;">
                                <h3>📊 لوحة التحكم</h3>
                                <p>عرض إحصائيات وإدارة التذاكر</p>
                                <a href="dashboard.php" class="blue button">لوحة التحكم</a>
                            </div>
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
</body>
</html>
