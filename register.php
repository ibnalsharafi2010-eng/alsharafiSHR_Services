<?php
require_once 'include/database_firebase.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    // Validate required fields
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($phone)) {
        $error = "جميع الحقول مطلوبة";
    } elseif ($password !== $confirm_password) {
        $error = "كلمة المرور غير متطابقة";
    } elseif (strlen($password) < 6) {
        $error = "كلمة المرور يجب أن تكون 6 أحرف على الأقل";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "البريد الإلكتروني غير صحيح";
    } elseif (!preg_match('/^[0-9]{9,15}$/', $phone)) {
        $error = "رقم الهاتف يجب أن يكون أرقاماً فقط (9-15 رقم)";
    } else {
        $existingUser = FirebaseDB::getUserByUsername($username);
        if ($existingUser) {
            $error = "اسم المستخدم موجود بالفعل";
        } else {
            $allUsers = FirebaseDB::getAllUsers();
            $emailExists = false;
            $phoneExists = false;
            
            if ($allUsers) {
                foreach ($allUsers as $user) {
                    if (isset($user['email']) && $user['email'] === $email) {
                        $emailExists = true;
                        break;
                    }
                    if (isset($user['phone']) && $user['phone'] === $phone) {
                        $phoneExists = true;
                        break;
                    }
                }
            }
            
            if ($emailExists) {
                $error = "البريد الإلكتروني مستخدم بالفعل";
            } elseif ($phoneExists) {
                $error = "رقم الهاتف مستخدم بالفعل";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Save user data (ID will be generated automatically)
                $userData = [
                    'username' => $username,
                    'password' => $hashedPassword,
                    'email' => $email,
                    'phone' => $phone,
                    'role' => 'user',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                if (FirebaseDB::saveUser($userData)) {
                    $success = "تم تسجيل حسابك بنجاح! يمكنك الآن تسجيل الدخول.";
                } else {
                    $error = "حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" class="rtl" lang="ar-YE">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>تسجيل حساب جديد - SHR Support</title>
    <meta name="description" content="تسجيل حساب جديد في مركز الدعم">
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
                <p><a href="login.php">تسجيل الدخول</a></p>
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
            <li><a href="register.php" class="active">تسجيل حساب جديد</a></li>
        </ul>
        <div id="content">
            <div id="landing_page">
                <div class="main-content">
                    <div class="thread-body">
                        <h1>تسجيل حساب جديد</h1>
                        <p>يرجى ملء النموذج أدناه لإنشاء حساب جديد في مركز الدعم.</p>
                        
                        <?php if (isset($error)): ?>
                            <div style="background-color: #ffe0e0; border: 1px solid #a00; color: #a00; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
                            <div style="background-color: #e0f7e0; border: 1px solid #28a745; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="register.php">
                            <fieldset>
                                <legend>معلومات الحساب</legend>
                                
                                <div class="form-group">
                                    <label for="username">اسم المستخدم *</label>
                                    <input type="text" name="username" id="username" required 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">البريد الإلكتروني *</label>
                                    <input type="email" name="email" id="email" required 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">رقم الهاتف *</label>
                                    <input type="tel" name="phone" id="phone" required 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                           pattern="[0-9]{9,15}" placeholder="أرقام فقط (9-15 رقم)">
                                </div>
                                
                                <div class="form-group">
                                    <label for="password">كلمة المرور *</label>
                                    <input type="password" name="password" id="password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">تأكيد كلمة المرور *</label>
                                    <input type="password" name="confirm_password" id="confirm_password" required>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="blue button">إنشاء حساب</button>
                                </div>
                            </fieldset>
                        </form>
                        
                        <p>لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
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
