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

// Check if user is admin
if (!UserRoleManager::checkAccess('admin', $_SESSION['username'])) {
    header("Location: user_dashboard.php");
    exit();
}

// Ensure notifications directory exists
NotificationManager::ensureNotificationsDirectory();

// Get all tickets and users
$allTickets = FirebaseDB::getTickets();
$allUsers = FirebaseDB::getAllUsers();

// Calculate statistics
$totalTickets = count($allTickets);
$totalUsers = count($allUsers);

$openTickets = 0;
$processingTickets = 0;
$closedTickets = 0;

foreach ($allTickets as $ticket) {
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

// Get admin notifications (all new tickets and replies)
$adminNotifications = [];
$newTicketsCount = 0;
$newRepliesCount = 0;

// Count all unread tickets for admin
if ($allUsers && is_array($allUsers)) {
    foreach ($allUsers as $user) {
        if ($user && is_array($user) && isset($user['username'])) {
            $username = $user['username'];
            $unreadCount = NotificationManager::getUnreadTicketsCount($username);
            $repliesCount = count(NotificationManager::getTicketsWithNewReplies($username));
            $newTicketsCount += $unreadCount;
            $newRepliesCount += $repliesCount;
        }
    }
}

$totalAdminNotifications = $newTicketsCount + $newRepliesCount;

// Get recent tickets for admin
$recentTickets = [];
if ($allTickets) {
    foreach ($allTickets as $ticketId => $ticket) {
        if ($ticket && is_array($ticket)) {
            $ticket['id'] = $ticketId;
            $recentTickets[] = $ticket;
        }
    }
    // Sort by creation date (newest first)
    usort($recentTickets, function($a, $b) {
        return strtotime($b['created'] ?? '1970-01-01') - strtotime($a['created'] ?? '1970-01-01');
    });
    $recentTickets = array_slice($recentTickets, 0, 10); // Last 10 tickets
}

// Get user statistics
$userStats = [];
if ($allUsers && is_array($allUsers)) {
    foreach ($allUsers as $user) {
        if ($user && is_array($user) && isset($user['username'])) {
            $username = $user['username'];
            $userStats[$username] = [
                'name' => $username,
                'email' => $user['email'] ?? '',
                'total_tickets' => 0, // Will be calculated below
                'role' => $user['role'] ?? 'user',
                'created_at' => $user['created_at'] ?? ''
            ];
            
            // Calculate tickets for this user
            $userTickets = FirebaseDB::getUserTickets($username);
            $userStats[$username]['total_tickets'] = count($userTickets);
        }
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" class="rtl" lang="ar-YE">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>لوحة تحكم المدير - SHR Support</title>
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
        
        .admin-section {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
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
            background-color: #dc3545;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
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
        
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .user-table th, .user-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: right;
        }
        
        .user-table th {
            background-color: #f8f9fa;
        }
        
        .admin-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .admin-actions a {
            background-color: #007bff;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
        }
        
        .admin-actions a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div id="container">
        <div id="header">
            <div class="pull-right flush-right">
                <p>
                    <span style="color: #dc3545; font-weight: bold;">👨‍💼 المدير: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <?php if ($totalAdminNotifications > 0): ?>
                        <span class="notification-badge"><?php echo $totalAdminNotifications; ?></span>
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
            <li><a href="admin_dashboard.php" class="active">لوحة المدير</a></li>
            <li><a href="admin_tickets.php">جميع التذاكر</a></li>
            <li><a href="admin_users.php">إدارة المستخدمين</a></li>
            <li><a href="show_users.php">عرض المستخدمين</a></li>
        </ul>
        <div id="content">
            <div id="landing_page">
                <div class="main-content">
                    <div class="thread-body">
                        <h1>لوحة تحكم المدير</h1>
                        
                        <?php if ($totalAdminNotifications > 0): ?>
                            <div class="notification-alert">
                                🚨 لديك <strong><?php echo $totalAdminNotifications; ?></strong> إشعارات جديدة من النظام
                                <?php if ($newTicketsCount > 0): ?>
                                    (<?php echo $newTicketsCount; ?> تذاكر جديدة)
                                <?php endif; ?>
                                <?php if ($newRepliesCount > 0): ?>
                                    (<?php echo $newRepliesCount; ?> ردود جديدة)
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="admin-actions">
                            <a href="admin_tickets.php">📋 عرض جميع التذاكر</a>
                            <a href="admin_users.php">👥 إدارة المستخدمين</a>
                            <a href="show_users.php">📊 إحصائيات المستخدمين</a>
                            <a href="add_user.php">➕ إضافة مستخدم جديد</a>
                        </div>
                        
                        <div class="welcome-section">
                            <h2 style="margin: 0 0 5px 0; font-size: 1.2em;">
                                لوحة تحكم المدير العام
                                <?php if ($totalAdminNotifications > 0): ?>
                                    <span class="notification-badge"><?php echo $totalAdminNotifications; ?></span>
                                <?php endif; ?>
                            </h2>
                            <p style="margin: 0; font-size: 0.9em;">إحصائيات نظام الدعم الشامل</p>
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
                            
                            <div class="dashboard-card" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                                <h3>🔔 إشعارات النظام</h3>
                                <p><?php echo $totalAdminNotifications; ?></p>
                            </div>
                        </div>
                        
                        <div class="stats-section">
                            <h3>📈 إحصائيات النظام العامة</h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px;">
                                <div>
                                    <strong>إجمالي التذاكر:</strong> <?php echo $totalTickets; ?>
                                </div>
                                <div>
                                    <strong>إجمالي المستخدمين:</strong> <?php echo $totalUsers; ?>
                                </div>
                                <div>
                                    <strong>التذاكر النشطة:</strong> <?php echo $openTickets + $processingTickets; ?>
                                </div>
                                <div>
                                    <strong>نسبة الإغلاق:</strong> <?php echo $totalTickets > 0 ? round(($closedTickets / $totalTickets) * 100, 1) : 0; ?>%
                                </div>
                                <div>
                                    <strong>تذاكر جديدة:</strong> <?php echo $newTicketsCount; ?>
                                </div>
                                <div>
                                    <strong>ردود جديدة:</strong> <?php echo $newRepliesCount; ?>
                                </div>
                                <div>
                                    <strong>آخر تحديث:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="admin-section">
                            <h3>👥 إحصائيات المستخدمين</h3>
                            <table class="user-table">
                                <thead>
                                    <tr>
                                        <th>اسم المستخدم</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>الدور</th>
                                        <th>عدد التذاكر</th>
                                        <th>تاريخ التسجيل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userStats as $username => $stats): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($username); ?></td>
                                            <td><?php echo htmlspecialchars($stats['email']); ?></td>
                                            <td>
                                                <span style="background-color: <?php echo $stats['role'] === 'admin' ? '#dc3545' : '#007bff'; ?>; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.8em;">
                                                    <?php echo $stats['role'] === 'admin' ? 'مدير' : 'مستخدم'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $stats['total_tickets']; ?></td>
                                            <td><?php echo $stats['created_at']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="recent-tickets">
                            <h3>📋 أحدث التذاكر في النظام</h3>
                            <?php if (!empty($recentTickets)): ?>
                                <?php foreach ($recentTickets as $ticket): ?>
                                    <div class="ticket-item status-<?php echo $ticket['status']; ?>">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <strong>#<?php echo $ticket['id']; ?></strong> - 
                                                <?php echo htmlspecialchars($ticket['subject']); ?>
                                                <span style="color: #666; margin-right: 10px;">(<?php echo htmlspecialchars($ticket['username']); ?>)</span>
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
                                    لا توجد تذاكر في النظام حالياً.
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
