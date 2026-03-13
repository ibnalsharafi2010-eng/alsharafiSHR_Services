<?php
session_start();
require_once 'include/database_firebase.php';
require_once 'include/user_roles.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get form data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Simple validation
    if (empty($username) || empty($password)) {
        $error = "يرجى إدخال اسم المستخدم وكلمة المرور";
    } else {
        // Validate against Firebase database
        if (FirebaseDB::validateUser($username, $password)) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $username; // Use username as user_id
            
            // Set remember me cookie if checked
            if ($remember) {
                setcookie('remember_user', $username, time() + (86400 * 30), "/"); // 30 days
            }
            
            // Redirect based on user role
            UserRoleManager::redirectBasedOnRole($username);
        } else {
            $error = "اسم المستخدم أو كلمة المرور غير صحيحة";
        }
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" class="rtl" lang="ar-YE">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>تسجيل الدخول - SHR Support</title>
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
                <p><a href="register.php">تسجيل حساب جديد</a></p>
            </div>
            <a class="pull-left" id="logo" href="index.html" title="مركز الدعم">
                <span class="valign-helper"></span>
                <img src="logo.php" border=0 alt="SHR Support">
            </a>
        </div>
        <div class="clear"></div>
        <ul id="nav" class="flush-left">
            <li><a href="index.html">الصفحة الرئيسية لمركز الدعم</a></li>
            <li><a href="open.php">فتح تذكرة جديدة</a></li>
            <li><a href="view.php.html">التحقق من حالة تذكرة</a></li>
            <li><a href="tickets.php">التذاكر</a></li>
            <li><a href="login.php" class="active">تسجيل الدخول</a></li>
        </ul>
        <div id="content">
            <div id="landing_page">
                <div class="main-content">
                    <div class="thread-body">
                        <h1>تسجيل الدخول</h1>
                        <p>يرجى إدخال بيانات تسجيل الدخول للوصول إلى حسابك.</p>
                        
                        <?php if (isset($error)): ?>
                            <div style="background-color: #ffe0e0; border: 1px solid #a00; color: #a00; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="login_process.php">
                            <fieldset>
                                <legend>بيانات الدخول</legend>
                                
                                <div class="form-group">
                                    <label for="username">اسم المستخدم</label>
                                    <input type="text" name="username" id="username" required 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="password">كلمة المرور</label>
                                    <input type="password" name="password" id="password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="remember" id="remember">
                                        تذكرني
                                    </label>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="blue button">تسجيل الدخول</button>
                                    <a href="forgot_password.php">نسيت كلمة المرور؟</a>
                                </div>
                            </fieldset>
                        </form>
                        
                        <hr>
                        
                        <p>ليس لديك حساب؟ <a href="register.php">سجل الآن</a></p>
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
