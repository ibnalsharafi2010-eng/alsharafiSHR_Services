<?php
class UserRoleManager {
    
    // Check if user is admin
    public static function isAdmin($username) {
        $user = FirebaseDB::getUserByUsername($username);
        return $user && ($user['role'] === 'admin' || $user['role'] === 'administrator');
    }
    
    // Check if user is moderator
    public static function isModerator($username) {
        $user = FirebaseDB::getUserByUsername($username);
        return $user && ($user['role'] === 'moderator' || self::isAdmin($username));
    }
    
    // Get user role
    public static function getUserRole($username) {
        $user = FirebaseDB::getUserByUsername($username);
        return $user ? ($user['role'] ?? 'user') : 'guest';
    }
    
    // Redirect based on role
    public static function redirectBasedOnRole($username) {
        if (self::isAdmin($username)) {
            header("Location: admin_dashboard.php");
            exit();
        } elseif (self::isModerator($username)) {
            header("Location: moderator_dashboard.php");
            exit();
        } else {
            header("Location: user_dashboard.php");
            exit();
        }
    }
    
    // Check access permissions
    public static function checkAccess($requiredRole, $username) {
        $userRole = self::getUserRole($username);
        
        switch($requiredRole) {
            case 'admin':
                return self::isAdmin($username);
            case 'moderator':
                return self::isModerator($username);
            case 'user':
                return true; // All logged-in users can access user pages
            default:
                return false;
        }
    }
}
?>
