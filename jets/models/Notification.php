<?php
require_once 'config/database.php';
require_once 'controllers/AuthController.php';

class Notification {
    private static $file = 'data/notifications.json';

    private static function loadNotifications() {
        if (!file_exists(self::$file)) {
            file_put_contents(self::$file, '[]');
        }
        $data = file_get_contents(self::$file);
        return json_decode($data, true) ?: [];
    }

    private static function saveNotifications($notifications) {
        file_put_contents(self::$file, json_encode($notifications, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function create($userId, $message, $link = '#') {
        $notifications = self::loadNotifications();
        $newNotification = [
            'id' => uniqid('notif_'),
            'user_id' => $userId, // ID ของผู้ใช้ หรือ 'admin' สำหรับแอดมิน
            'message' => $message,
            'link' => $link,
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s')
        ];
        // เพิ่มการแจ้งเตือนใหม่เข้าไปด้านบนของ array
        array_unshift($notifications, $newNotification);
        self::saveNotifications($notifications);
        // --- LOG TO notifications.log ---
        $logLine = '[' . date('Y-m-d H:i:s') . "] Notification created for user_id={$userId}: \"{$message}\" (link={$link})\n";
        file_put_contents('storage/logs/notifications.log', $logLine, FILE_APPEND);
    }

    public static function getForUser($userId) {
        $allNotifications = self::loadNotifications();
        $userNotifications = [];
        
        $currentUser = AuthController::getCurrentUser();
        $isAdmin = ($currentUser && $currentUser['role'] === 'admin');

        foreach ($allNotifications as $notification) {
            // แอดมินจะเห็นการแจ้งเตือนของ 'admin'
            if ($isAdmin && $notification['user_id'] === 'admin') {
                 $userNotifications[] = $notification;
            } 
            // ผู้ใช้จะเห็นการแจ้งเตือนของตัวเอง
            else if ($notification['user_id'] === $userId) {
                $userNotifications[] = $notification;
            }
        }
        
        // ข้อมูลถูกเรียงจากใหม่ไปเก่าอยู่แล้วตอนที่บันทึก
        return $userNotifications;
    }

    public static function getUnreadCount($userId) {
        $notifications = self::getForUser($userId);
        $count = 0;
        foreach ($notifications as $notification) {
            if (!$notification['is_read']) {
                $count++;
            }
        }
        return $count;
    }

    public static function markAsRead($notificationId, $userId) {
        $notifications = self::loadNotifications();
        $currentUser = AuthController::getCurrentUser();
        $isAdmin = ($currentUser && $currentUser['role'] === 'admin');
        $updated = false;

        foreach ($notifications as &$notification) {
            if ($notification['id'] === $notificationId) {
                // ตรวจสอบสิทธิ์: ผู้ใช้สามารถแก้ไขเฉพาะการแจ้งเตือนของตนเอง
                if ($notification['user_id'] === $userId || ($isAdmin && $notification['user_id'] === 'admin')) {
                    if (!$notification['is_read']) {
                        $notification['is_read'] = true;
                        $updated = true;
                    }
                    break; // Found and handled, no need to continue loop
                }
            }
        }

        if ($updated) {
            self::saveNotifications($notifications);
        }
        return $updated;
    }
    
    public static function markAllAsRead($userId) {
        $notifications = self::loadNotifications();
        $currentUser = AuthController::getCurrentUser();
        $isAdmin = ($currentUser && $currentUser['role'] === 'admin');
        
        $updated = false;
        foreach ($notifications as &$notification) {
            $isForAdmin = $isAdmin && $notification['user_id'] === 'admin';
            $isForUser = $notification['user_id'] === $userId;

            if ($isForAdmin || $isForUser) {
                if (!$notification['is_read']) {
                    $notification['is_read'] = true;
                    $updated = true;
                }
            }
        }
        
        if ($updated) {
            self::saveNotifications($notifications);
        }
        return true;
    }
} 