<!DOCTYPE html>
<html dir="rtl" class="rtl" lang="ar-YE">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>تسجيل الدخول - SHR Support</title>
    <meta name="description" content="تسجيل الدخول إلى مركز الدعم">
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
    <!--div id="container">
        <div id="header">
            <div class="pull-right flush-right">
                <p><a href="index.html">العودة للرئيسية</a></p>
            </div>
            <ul id="nav" class="flush-left">
                <li><a href="index.html">الصفحة الرئيسية لمركز الدعم</a></li>
                <li><a href="open.php">فتح تذكرة جديدة</a></li>
                <li><a href="view.php.html">التحقق من حالة تذكرة</a></li>
                <li><a href="tickets.php">التذاكر</a></li>
                <li><a href="dashboard.php">لوحة التحكم</a></li>
            </ul>
            <a class="pull-left" id="logo" href="index.html" title="مركز الدعم">
                <span class="valign-helper"></span>
                <img src="logo.php" border=0 alt="SHR Support">
            </a>
        </div-->
        <div class="clear"></div>
        <div id="content">
            <div id="landing_page">
                <div class="main-content">
                    <div class="thread-body">
                        <h1>تسجيل الدخول</h1>
                        <p>يرجى إدخال بيانات الاعتماد الخاصة بك للوصول إلى حسابك.</p>
                        
                        <form method="post" action="login_process.php">
                            <fieldset>
                                <legend>معلومات تسجيل الدخول</legend>
                                
                                <div class="form-group">
                                    <label for="username">اسم المستخدم أو البريد الإلكتروني *</label>
                                    <input type="text" name="username" id="username" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password">كلمة المرور *</label>
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
