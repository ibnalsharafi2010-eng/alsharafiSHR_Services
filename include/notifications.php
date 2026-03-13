<?php
class NotificationManager {

    // Mark ticket as read
    public static function markTicketAsRead($ticketId, $username) {
        $readTicketsFile = 'notifications/read_tickets_' . $username . '.json';
        $readTickets = [];
        
        if (file_exists($readTicketsFile)) {
            $data = file_get_contents($readTicketsFile);
            $readTickets = json_decode($data, true) ?: [];
        }
        
        if (!in_array($ticketId, $readTickets)) {
            $readTickets[] = $ticketId;
            file_put_contents($readTicketsFile, json_encode($readTickets));
        }
    }
    
    // Check if ticket has new replies
    public static function hasNewReplies($ticketId, $username, $lastReadTime = null) {
        $replies = FirebaseDB::getTicketReplies($ticketId);
        
        if (empty($replies)) {
            return false;
        }
        
        // Get last read time for this ticket
        if (!$lastReadTime) {
            $readTimesFile = 'notifications/read_times_' . $username . '.json';
            $readTimes = [];
            
            if (file_exists($readTimesFile)) {
                $data = file_get_contents($readTimesFile);
                $readTimes = json_decode($data, true) ?: [];
            }
            
            $lastReadTime = $readTimes[$ticketId] ?? 0;
        }
        
        foreach ($replies as $reply) {
            if (isset($reply['timestamp']) && strtotime($reply['timestamp']) > $lastReadTime) {
                // Check if reply is not from current user
                if ($reply['username'] !== $username) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    // Mark ticket replies as read
    public static function markRepliesAsRead($ticketId, $username) {
        $readTimesFile = 'notifications/read_times_' . $username . '.json';
        $readTimes = [];
        
        if (file_exists($readTimesFile)) {
            $data = file_get_contents($readTimesFile);
            $readTimes = json_decode($data, true) ?: [];
        }
        
        $readTimes[$ticketId] = time();
        file_put_contents($readTimesFile, json_encode($readTimes));
    }
    
    // Get unread tickets count
    public static function getUnreadTicketsCount($username) {
        $userTickets = FirebaseDB::getUserTickets($username);
        $readTicketsFile = 'notifications/read_tickets_' . $username . '.json';
        $readTickets = [];
        
        if (file_exists($readTicketsFile)) {
            $data = file_get_contents($readTicketsFile);
            $readTickets = json_decode($data, true) ?: [];
        }
        
        $unreadCount = 0;
        foreach ($userTickets as $ticket) {
            if (!in_array($ticket['id'], $readTickets)) {
                $unreadCount++;
            }
        }
        
        return $unreadCount;
    }
    
    // Get tickets with new replies
    public static function getTicketsWithNewReplies($username) {
        $userTickets = FirebaseDB::getUserTickets($username);
        $ticketsWithNewReplies = [];
        
        foreach ($userTickets as $ticket) {
            if (self::hasNewReplies($ticket['id'], $username)) {
                $ticketsWithNewReplies[] = $ticket;
            }
        }
        
        return $ticketsWithNewReplies;
    }
    
    // Get total notifications count
    public static function getTotalNotificationsCount($username) {
        $unreadTickets = self::getUnreadTicketsCount($username);
        $ticketsWithReplies = count(self::getTicketsWithNewReplies($username));
        
        return $unreadTickets + $ticketsWithReplies;
    }
    
    // Create notifications directory if not exists
    public static function ensureNotificationsDirectory() {
        if (!is_dir('notifications')) {
            mkdir('notifications', 0755, true);
        }
    }
}
?>
