<?php
require_once(__DIR__ . '/../models/User.php');

class AuthController {
    public static function login($username, $password) {
        $user = User::authenticate($username, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_full_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_membership_tier'] = $user['membership_tier'] ?? 'silver';
            $_SESSION['user_points'] = isset($user['points']) ? $user['points'] : 0;
            return true;
        }
        
        return false;
    }
    
    public static function register($userData) {
        // Validation
        $errors = [];
        
        if (empty($userData['username']) || strlen($userData['username']) < 3) {
            $errors[] = 'ชื่อผู้ใช้งานต้องมีอย่างน้อย 3 ตัวอักษร';
        }
        
        if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'อีเมลไม่ถูกต้อง';
        }
        
        if (empty($userData['password']) || strlen($userData['password']) < 6) {
            $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
        }
        
        if ($userData['password'] !== $userData['confirm_password']) {
            $errors[] = 'รหัสผ่านไม่ตรงกัน';
        }
        
        if (empty($userData['full_name'])) {
            $errors[] = 'กรุณากรอกชื่อ-นามสกุล';
        }
        
        if (empty($userData['phone'])) {
            $errors[] = 'กรุณากรอกเบอร์โทรศัพท์';
        }
        
        // ตรวจสอบเบอร์โทรศัพท์ให้รองรับเบอร์ต่างประเทศ
        if (!empty($userData['phone']) && !preg_match('/^\+?[0-9]{8,15}$/', $userData['phone'])) {
            $errors[] = 'เบอร์โทรศัพท์ไม่ถูกต้อง (กรุณากรอกเบอร์โทรศัพท์ที่ถูกต้อง เช่น +66812345678 หรือ 0812345678)';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }
        
        return User::create($userData);
    }
    
    public static function logout() {
        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['user_role']);
    }
    
    public static function hasPermission($requiredRole) {
        if (!self::isLoggedIn()) return false;
        $userRole = $_SESSION['user_role'];
        $roleHierarchy = ['admin' => 2, 'client' => 1];
        return isset($roleHierarchy[$userRole]) && 
               isset($roleHierarchy[$requiredRole]) && 
               $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
    }
    
    public static function getCurrentUser() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) return null;
        $users = Database::loadUsers();
        foreach ($users as $user) {
            if ($user['id'] === $userId) {
                return [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'] ?? null,
                    'membership_tier' => $user['membership_tier'] ?? 'silver',
                    'points' => $user['points'] ?? 0
                ];
            }
        }
        return null;
    }

    public static function updateUser($userId, $newData) {
        $users = Database::loadUsers();
        foreach ($users as &$user) {
            if ($user['id'] === $userId) {
                foreach ($newData as $k => $v) {
                    if ($k !== 'id' && $k !== 'username' && $k !== 'password') {
                        $user[$k] = $v;
                    }
                }
                break;
            }
        }
        Database::saveUsers($users);
        // Update session values for current user
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $userId) {
            $_SESSION['user_full_name'] = $newData['full_name'] ?? $_SESSION['user_full_name'];
            $_SESSION['user_email'] = $newData['email'] ?? $_SESSION['user_email'];
            $_SESSION['user_phone'] = $newData['phone'] ?? $_SESSION['user_phone'];
            if (isset($newData['membership_tier'])) {
                $_SESSION['user_membership_tier'] = $newData['membership_tier'];
            }
            if (isset($newData['points'])) {
                $_SESSION['user_points'] = $newData['points'];
            }
        }
    }

    public static function verifyPassword($userId, $password) {
        $users = Database::loadUsers();
        foreach ($users as $user) {
            if ($user['id'] === $userId) {
                return password_verify($password, $user['password']);
            }
        }
        return false;
    }

    public static function reloadCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            $users = Database::loadUsers();
            foreach ($users as $user) {
                if ($user['id'] === $_SESSION['user_id']) {
                    // Update session variables
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_full_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_phone'] = $user['phone'] ?? null;
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_membership_tier'] = $user['membership_tier'] ?? 'silver';
                    // Return the updated user array
                    return $user;
                }
            }
        }
        // Fallback to getCurrentUser if reload fails
        return self::getCurrentUser();
    }
}