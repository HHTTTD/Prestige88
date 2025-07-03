<?php
require_once(__DIR__ . '/../config/database.php');

class User {
    public static function authenticate($usernameOrEmail, $password) {
        $users = Database::loadUsers();
        foreach ($users as $user) {
            if ((strcasecmp($user['username'], $usernameOrEmail) === 0 || strcasecmp($user['email'], $usernameOrEmail) === 0)
                && password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }
    
    public static function create($userData) {
        $users = Database::loadUsers();
        
        // Check for existing username or email
        foreach ($users as $user) {
            if ($user['username'] === $userData['username']) {
                throw new Exception('ชื่อผู้ใช้งานนี้มีอยู่แล้ว');
            }
            if ($user['email'] === $userData['email']) {
                throw new Exception('อีเมลนี้มีอยู่แล้ว');
            }
        }
        
        $newUser = [
            'id' => uniqid(),
            'username' => $userData['username'],
            'password' => password_hash($userData['password'], PASSWORD_DEFAULT),
            'email' => $userData['email'],
            'full_name' => $userData['full_name'],
            'phone' => $userData['phone'],
            'company' => $userData['company'] ?? '',
            'role' => 'client',
            'status' => 'active',
            'membership_tier' => 'silver',
            'points' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $users[] = $newUser;
        Database::saveUsers($users);
        
        return $newUser;
    }
    
    public static function updateMembership($userId, $newTier) {
        $users = Database::loadUsers();
        
        foreach ($users as &$user) {
            if ($user['id'] === $userId) {
                $oldTier = $user['membership_tier'] ?? 'silver';
                $user['membership_tier'] = $newTier;
                $user['tier_updated_at'] = date('Y-m-d H:i:s');
                
                // Check for tier upgrade
                $tierOrder = ['silver', 'gold', 'platinum', 'black'];
                $oldIndex = array_search($oldTier, $tierOrder);
                $newIndex = array_search($newTier, $tierOrder);
                
                if ($newIndex > $oldIndex) {
                    $user['tier_upgraded'] = true;
                    $user['tier_upgrade_date'] = date('Y-m-d H:i:s');
                }
                break;
            }
        }
        
        Database::saveUsers($users);
    }

    public static function getPoints($userId) {
        $users = Database::loadUsers();
        foreach ($users as $user) {
            if ($user['id'] === $userId) {
                return isset($user['points']) ? (int)$user['points'] : 0;
            }
        }
        return 0;
    }

    public static function setPoints($userId, $points) {
        $users = Database::loadUsers();
        foreach ($users as &$user) {
            if ($user['id'] === $userId) {
                $user['points'] = (int)$points;
                // Auto update membership_tier
                if (class_exists('Membership')) {
                    $user['membership_tier'] = Membership::getTierByPoints($user['points']);
                }
                break;
            }
        }
        Database::saveUsers($users);
    }
}