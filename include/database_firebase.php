<?php
class FirebaseDB {

    private static $firebaseUrl = "https://customersservices-451af-default-rtdb.europe-west1.firebasedatabase.app/tblusers";
    private static $ticketsUrl = "https://customersservices-451af-default-rtdb.europe-west1.firebasedatabase.app/tbltickets";

    // حفظ مستخدم جديد
    public static function saveUser($userData) {
        if (!isset($userData['username'])) return false;

        $id = self::getNextUserId(); // Use sequential ID
        $userData['id'] = $id; // Add ID to user data
        $url = self::$firebaseUrl . "/" . $id . ".json";

        $options = [
            'http' => [
                'method' => 'PUT',
                'header' => "Content-Type: application/json",
                'content' => json_encode($userData)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result !== false;
    }

    // الحصول على مستخدم عن طريق username
    public static function getUserByUsername($username) {
        $users = self::getAllUsers();
        if (empty($users)) {
            return null;
        }
        
        foreach ($users as $user) {
            if (isset($user['username']) && $user['username'] === $username) {
                return $user;
            }
        }
        return null;
    }

    // الحصول على جميع المستخدمين
    public static function getAllUsers() {
        $data = file_get_contents(self::$firebaseUrl . ".json");
        if ($data === false || $data === 'null') {
            return [];
        }
        return json_decode($data, true) ?: [];
    }
    
    // التحقق من المستخدم وكلمة المرور
    public static function validateUser($username, $password) {
        $user = self::getUserByUsername($username);
        if ($user && password_verify($password, $user['password'] ?? '')) {
            return true;
        }
        return false;
    }
    
    // حفظ تذكرة جديدة
    public static function saveTicket($ticketData) {
        $ticketId = time() . rand(1000, 9999); // ID فريد للتذكرة
        $url = self::$ticketsUrl . "/" . $ticketId . ".json";

        $options = [
            'http' => [
                'method' => 'PUT',
                'header' => "Content-Type: application/json",
                'content' => json_encode($ticketData)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result !== false ? $ticketId : false;
    }
    
    // الحصول على التذاكر
    public static function getTickets() {
        $data = file_get_contents(self::$ticketsUrl . ".json");
        if ($data === false || $data === 'null') {
            return [];
        }
        return json_decode($data, true) ?: [];
    }
    
    // الحصول على تذاكر مستخدم معين
    public static function getUserTickets($username) {
        $tickets = self::getTickets();
        $userTickets = [];
        
        if ($tickets) {
            foreach ($tickets as $ticketId => $ticket) {
                if (isset($ticket['username']) && $ticket['username'] === $username) {
                    $ticket['id'] = $ticketId;
                    $userTickets[] = $ticket;
                }
            }
        }
        
        return $userTickets;
    }
    
    // الحصول على تذكرة محددة
    public static function getTicketById($ticketId) {
        $tickets = self::getTickets();
        if ($tickets && isset($tickets[$ticketId])) {
            $ticket = $tickets[$ticketId];
            $ticket['id'] = $ticketId;
            return $ticket;
        }
        return null;
    }
    
    // إضافة رد لتذكرة
    public static function addReplyToTicket($ticketId, $replyData) {
        $repliesUrl = self::$ticketsUrl . "/" . $ticketId . "/replies.json";
        
        // Get existing replies
        $existingReplies = file_get_contents($repliesUrl);
        $replies = json_decode($existingReplies, true) ?: [];
        
        // Add new reply
        $replies[] = $replyData;
        
        // Save updated replies
        $options = [
            'http' => [
                'method' => 'PUT',
                'header' => "Content-Type: application/json",
                'content' => json_encode($replies)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($repliesUrl, false, $context);
        
        return $result !== false;
    }
    
    // الحصول على ردود التذكرة
    public static function getTicketReplies($ticketId) {
        $repliesUrl = self::$ticketsUrl . "/" . $ticketId . "/replies.json";
        $data = file_get_contents($repliesUrl);
        return json_decode($data, true) ?: [];
    }
    
    // الحصول على الرقم التسلسلي التالي للمستخدم
    public static function getNextUserId() {
        $users = self::getAllUsers();
        $maxId = 0;
        
        if ($users) {
            foreach ($users as $user) {
                if (isset($user['id']) && is_numeric($user['id']) && $user['id'] > $maxId) {
                    $maxId = $user['id'];
                }
            }
        }
        
        return $maxId + 1;
    }
}
?>