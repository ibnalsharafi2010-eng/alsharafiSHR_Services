<!DOCTYPE html>
<html dir="rtl" class="rtl" lang="ar-YE">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>شكراً لك - SHR Support</title>
    <meta name="description" content="شكراً لتقديم التذكرة">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/osticket.css" media="screen"/>
    <link rel="stylesheet" href="assets/default/css/theme.css" media="screen"/>
    <link rel="stylesheet" href="css/rtl.css" media="screen"/>
    <link rel="icon" type="image/png" href="images/oscar-favicon-32x32.png" sizes="32x32" />
    <script type="text/javascript" src="js/jquery-3.5.1.min.js"></script>
    <script src="js/osticket.js"></script>
</head>
<body>
    <div id="container">
        <div id="header">
            <div class="pull-right flush-right">
                <p><a href="index.html">العودة للرئيسية</a></p>
            </div>
            <a class="pull-left" id="logo" href="index.html" title="مركز الدعم">
                <span class="valign-helper"></span>
                <img src="logo.php" border=0 alt="SHR Support">
            </a>
        </div>
        <div class="clear"></div>
        <div id="content">
            <div id="landing_page">
                <div class="main-content">
                    <div class="thread-body">
                        <h1>شكراً لك!</h1>
                        <p>تم استلام تذكرتك بنجاح. سيقوم فريق الدعم بمراجعة تذكرتك والرد عليك في أقرب وقت ممكن.</p>
                        <p>رقم التذكرة: <strong>#<?php echo isset($_GET['ticket_id']) ? htmlspecialchars($_GET['ticket_id']) : rand(100000, 999999); ?></strong></p>
                        <p>تم إرسال تأكيد إلى بريدك الإلكتروني.</p>
                        
                        <div style="margin-top: 20px;">
                            <a href="index.html" class="blue button">العودة للرئيسية</a>
                            <a href="view.php.html" class="green button" style="margin-right: 10px;">التحقق من حالة التذكرة</a>
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
