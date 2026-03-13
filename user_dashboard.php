<?php
session_start();
require_once 'include/database_firebase.php';
require_once 'include/notifications.php';
require_once 'include/user_roles.php';

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

// Calculate statistics
$userTotalTickets = count($userTickets);

$openTickets = 0;
$processingTickets = 0;
$closedTickets = 0;

foreach ($userTickets as $ticket) {
    switch($ticket['status']) {
        case 'مفتوح':
            $openTickets++;
            break;
        case 'قيد المعالجة':
            $processingTickets++;
            break;
        case 'مغلق':
            $closedTickets++;
            break;
    }
}

// Get user notifications
$unreadCount = NotificationManager::getUnreadTicketsCount($_SESSION['username']);
$repliesCount = count(NotificationManager::getTicketsWithNewReplies($_SESSION['username']));
$totalNotifications = NotificationManager::getTotalNotificationsCount($_SESSION['username']);

// Mark all user tickets as read when viewing dashboard
foreach ($userTickets as $ticket) {
    NotificationManager::markTicketAsRead($ticket['id'], $_SESSION['username']);
}

// Get user info
$userInfo = FirebaseDB::getUserByUsername($_SESSION['username']);
?>

<!DOCTYPE html>
<html dir="rtl" class="rtl" lang="ar-YE">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>لوحة تحكم المستخدم - SHR Support</title>
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
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        
        .dashboard-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            min-height: 30px;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .dashboard-card h3 {
            margin: 0 0 8px 0;
            font-size: 1em;
        }
        
        .dashboard-card p {
            margin: 0;
            font-size: 1.5em;
            font-weight: bold;
        }
        
        .stats-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .recent-tickets {
            margin-top: 20px;
        }
        
        .ticket-item {
            background-color: white;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 6px;
            border-left: 4px solid #007bff;
        }
        
        .status-open { border-left-color: #28a745; }
        .status-processing { border-left-color: #ffc107; }
        .status-closed { border-left-color: #dc3545; }
        
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
            min-height: 30px;
        }
        
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
        
        .notification-alert {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .user-info {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #2196f3;
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
            <li><a href="user_dashboard.php" class="active">لوحة التحكم</a></li>
        </ul>
        <div id="content">
            <div id="landing_page">
                <div class="main-content">
                    <div class="thread-body">
                        <h1>لوحة تحكم المستخدم</h1>
                        
                        <?php if ($totalNotifications > 0): ?>
                            <div class="notification-alert">
                                📢 لديك <strong><?php echo $totalNotifications; ?></strong> إشعارات جديدة
                                <?php if ($unreadCount > 0): ?>
                                    (<?php echo $unreadCount; ?> تذاكر جديدة)
                                <?php endif; ?>
                                <?php if ($repliesCount > 0): ?>
                                    (<?php echo $repliesCount; ?> ردود جديدة)
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="user-info">
                            <h3>👤 معلومات حسابك</h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                                <div>
                                    <strong>الاسم:</strong> <?php echo htmlspecialchars($userInfo['username']); ?>
                                </div>
                                <div>
                                    <strong>البريد:</strong> <?php echo htmlspecialchars($userInfo['email']); ?>
                                </div>
                                <div>
                                    <strong>الهاتف:</strong> <?php echo htmlspecialchars($userInfo['phone']); ?>
                                </div>
                                <div>
                                    <strong>نوع العضوية:</strong> مستخدم
                                </div>
                            </div>
                        </div>
                        
                        <div class="welcome-section">
                            <h2 style="margin: 0 0 5px 0; font-size: 1.2em;">
                                مرحباً بك في لوحة التحكم
                                <?php if ($totalNotifications > 0): ?>
                                    <span class="notification-badge"><?php echo $totalNotifications; ?></span>
                                <?php endif; ?>
                            </h2>
                            <p style="margin: 0; font-size: 0.9em;">إحصائيات تذاكرك</p>
                        </div>
                        
                        <div class="dashboard-grid">
                            <div class="dashboard-card">
                                <h3>🎯 التذاكر المفتوحة</h3>
                                <p><?php echo $openTickets; ?></p>
                            </div>
                            
                            <div class="dashboard-card">
                                <h3>⏳ التذاكر قيد المعالجة</h3>
                                <p><?php echo $processingTickets; ?></p>
                            </div>
                            
                            <div class="dashboard-card">
                                <h3>✅ التذاكر المغلقة</h3>
                                <p><?php echo $closedTickets; ?></p>
                            </div>
                            
                            <div class="dashboard-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <h3>🔔 الإشعارات</h3>
                                <p><?php echo $totalNotifications; ?></p>
                            </div>
                        </div>
                        
                        <div class="stats-section">
                            <h3>📊 إحصائيات حسابك</h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px;">
                                <div>
                                    <strong>إجمالي تذاكرك:</strong> <?php echo $userTotalTickets; ?>
                                </div>
                                <div>
                                    <strong>التذاكر النشطة:</strong> <?php echo $openTickets + $processingTickets; ?>
                                </div>
                                <div>
                                    <strong>نسبة الإغلاق:</strong> <?php echo $userTotalTickets > 0 ? round(($closedTickets / $userTotalTickets) * 100, 1) : 0; ?>%
                                </div>
                                <div>
                                    <strong>آخر تحديث:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="recent-tickets">
                            <h3>📋 أحدث تذاكرك</h3>
                            <?php if (!empty($userTickets)): ?>
                                <?php 
                                $recentTickets = array_slice($userTickets, -5); // Last 5 tickets
                                foreach (array_reverse($recentTickets) as $ticket): 
                                ?>
                                    <div class="ticket-item status-<?php echo $ticket['status']; ?>">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <strong>#<?php echo $ticket['id']; ?></strong> - 
                                                <?php echo htmlspecialchars($ticket['subject']); ?>
                                            </div>
                                            <div>
                                                <span style="background-color: #007bff; color: white; padding: 3px 8px; border-radius: 3px; font-size: 0.8em;">
                                                    <?php echo $ticket['status']; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div style="margin-top: 5px; font-size: 0.85em; color: #666;">
                                            <?php echo $ticket['created']; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="text-align: center; padding: 20px; color: #666;">
                                    لا توجد تذاكر حالياً. <a href="open.php">افتح تذكرة جديدة</a>
                                </p>
                            <?php endif; ?>
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
