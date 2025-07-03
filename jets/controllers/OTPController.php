<?php
require_once 'models/OTP.php';
require_once 'controllers/AuthController.php';
require_once 'config/database.php';

class OTPController {
    public static function requestOTP($userData) {
        // Validation ข้อมูลก่อนส่ง OTP
        $errors = [];
        
        // ตรวจสอบและทำความสะอาดข้อมูล
        $phone = isset($userData['phone']) ? trim($userData['phone']) : '';
        $email = isset($userData['email']) ? trim($userData['email']) : '';
        $username = isset($userData['username']) ? trim($userData['username']) : '';
        $password = isset($userData['password']) ? $userData['password'] : '';
        $confirmPassword = isset($userData['confirm_password']) ? $userData['confirm_password'] : '';
        $fullName = isset($userData['full_name']) ? trim($userData['full_name']) : '';
        
        // Validation
        if (empty($phone) || !preg_match('/^\+?[0-9]{8,15}$/', $phone)) {
            $errors[] = 'เบอร์โทรศัพท์ไม่ถูกต้อง (กรุณากรอกเบอร์โทรศัพท์ที่ถูกต้อง เช่น +66812345678 หรือ 0812345678)';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'อีเมลไม่ถูกต้อง';
        }
        
        if (empty($username) || strlen($username) < 3) {
            $errors[] = 'ชื่อผู้ใช้งานต้องมีอย่างน้อย 3 ตัวอักษร';
        }
        
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'รหัสผ่านไม่ตรงกัน';
        }
        
        if (empty($fullName)) {
            $errors[] = 'กรุณากรอกชื่อ-นามสกุล';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }
        
        // ตรวจสอบว่าเบอร์โทร อีเมล หรือ username ซ้ำหรือไม่
        $users = Database::loadUsers();
        foreach ($users as $user) {
            // ตรวจสอบเบอร์โทร (ใช้ isset และ ?? เพื่อป้องกัน undefined key)
            $existingPhone = isset($user['phone']) ? trim($user['phone']) : '';
            if (!empty($existingPhone) && $existingPhone === $phone) {
                throw new Exception('เบอร์โทรศัพท์นี้มีการใช้งานแล้ว');
            }
            
            // ตรวจสอบอีเมล
            $existingEmail = isset($user['email']) ? trim($user['email']) : '';
            if (!empty($existingEmail) && $existingEmail === $email) {
                throw new Exception('อีเมลนี้มีการใช้งานแล้ว');
            }
            
            // ตรวจสอบ username
            $existingUsername = isset($user['username']) ? trim($user['username']) : '';
            if (!empty($existingUsername) && $existingUsername === $username) {
                throw new Exception('ชื่อผู้ใช้งานนี้มีอยู่แล้ว');
            }
        }
        
        // ทำความสะอาดเบอร์โทรก่อนบันทึก
        $cleanPhone = self::cleanPhone($phone);
        $userData['phone'] = $cleanPhone;
        
        // สร้างและส่ง OTP
        $sessionId = OTP::createOTPVerification(
            $cleanPhone, 
            $email, 
            $userData
        );
        
        return $sessionId;
    }
    
    public static function verifyOTPAndRegister($sessionId, $otp) {
        if (empty($sessionId) || empty($otp)) {
            return [
                'success' => false,
                'message' => 'ข้อมูลไม่ครบถ้วน'
            ];
        }
        
        $result = OTP::verifyOTP($sessionId, $otp);
        
        if ($result['success'] && isset($result['user_data']) && $result['user_data']) {
            // สร้างบัญชีผู้ใช้ใหม่
            try {
                $user = AuthController::register($result['user_data']);
                
                // ลบ OTP ที่ใช้แล้ว
                OTP::cleanupUsedOTP($sessionId);
                
                return [
                    'success' => true,
                    'message' => 'สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบด้วยบัญชีใหม่',
                    'user' => $user
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการสร้างบัญชี: ' . $e->getMessage()
                ];
            }
        }
        
        return $result;
    }
    
    public static function resendOTP($sessionId) {
        if (empty($sessionId)) {
            return [
                'success' => false,
                'message' => 'ไม่พบข้อมูล session'
            ];
        }
        
        return OTP::resendOTP($sessionId);
    }
    
    private static function validatePhone($phone) {
        if (empty($phone)) {
            return false;
        }
        
        // ลบทุกอย่างที่ไม่ใช่ตัวเลข
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // ตรวจสอบรูปแบบเบอร์โทรศัพท์ไทย
        // รูปแบบที่ยอมรับ: 08x-xxx-xxxx, 09x-xxx-xxxx, 06x-xxx-xxxx
        return preg_match('/^\+?[0-9]{8,15}$/', $phone);
    }
    
    private static function cleanPhone($phone) {
        // ลบทุกอย่างที่ไม่ใช่ตัวเลข
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // ตรวจสอบและแปลงรูปแบบ
        if (preg_match('/^0[689][0-9]{8}$/', $cleanPhone)) {
            return $cleanPhone;
        }
        
        // ถ้าเริ่มต้นด้วย +66 ให้แปลงเป็น 0
        if (preg_match('/^66[689][0-9]{8}$/', $cleanPhone)) {
            return '0' . substr($cleanPhone, 2);
        }
        
        return $cleanPhone;
    }
    
    public static function formatPhoneDisplay($phone) {
        $cleanPhone = self::cleanPhone($phone);
        
        if (strlen($cleanPhone) === 10) {
            // Format: 0xx-xxx-xxxx
            return substr($cleanPhone, 0, 3) . '-' . 
                   substr($cleanPhone, 3, 3) . '-' . 
                   substr($cleanPhone, 6, 4);
        }
        
        return $phone;
    }
    
    public static function maskPhone($phone) {
        $cleanPhone = self::cleanPhone($phone);
        
        if (strlen($cleanPhone) === 10) {
            // แสดงเฉพาะ 3 หลักแรกและ 2 หลักสุดท้าย
            return substr($cleanPhone, 0, 3) . 'xxxx' . substr($cleanPhone, -2);
        }
        
        return 'xxx-xxx-xxxx';
    }
    
    // ฟังก์ชันเพิ่มเติมสำหรับการจัดการ OTP
    public static function getOTPStatus($sessionId) {
        if (empty($sessionId)) {
            return [
                'exists' => false,
                'message' => 'ไม่พบ session'
            ];
        }
        
        $otps = OTP::loadOTPs();
        
        foreach ($otps as $otp) {
            if (isset($otp['session_id']) && $otp['session_id'] === $sessionId) {
                $now = time();
                $isExpired = $now > ($otp['expires_at'] ?? 0);
                $attemptsLeft = max(0, ($otp['max_attempts'] ?? 3) - ($otp['attempts'] ?? 0));
                
                return [
                    'exists' => true,
                    'expired' => $isExpired,
                    'verified' => $otp['verified'] ?? false,
                    'attempts_left' => $attemptsLeft,
                    'phone' => self::maskPhone($otp['phone'] ?? ''),
                    'created_at' => $otp['created_at'] ?? null,
                    'expires_at' => $otp['expires_at'] ?? null
                ];
            }
        }
        
        return [
            'exists' => false,
            'message' => 'ไม่พบข้อมูล OTP'
        ];
    }
    
    public static function cancelOTP($sessionId) {
        if (empty($sessionId)) {
            return false;
        }
        
        $otps = OTP::loadOTPs();
        $found = false;
        
        $otps = array_filter($otps, function($otp) use ($sessionId, &$found) {
            if (isset($otp['session_id']) && $otp['session_id'] === $sessionId) {
                $found = true;
                return false; // ลบ OTP นี้
            }
            return true; // เก็บ OTP อื่นไว้
        });
        
        if ($found) {
            OTP::saveOTPs(array_values($otps));
            return true;
        }
        
        return false;
    }
    
    // ฟังก์ชันสำหรับทดสอบ (ใช้เฉพาะในโหมด development)
    public static function getTestOTP($sessionId) {
        if (!defined('APP_DEBUG') || !APP_DEBUG) {
            return null;
        }
        
        $otps = OTP::loadOTPs();
        
        foreach ($otps as $otp) {
            if (isset($otp['session_id']) && $otp['session_id'] === $sessionId) {
                return $otp['otp'] ?? null;
            }
        }
        
        return null;
    }
}

// เพิ่มคลาสสำหรับ validation ที่ครอบคลุมมากขึ้น
class OTPValidator {
    public static function validateUserData($userData) {
        $errors = [];
        
        // ตรวจสอบข้อมูลที่จำเป็น
        $requiredFields = ['username', 'email', 'password', 'confirm_password', 'full_name', 'phone'];
        
        foreach ($requiredFields as $field) {
            if (!isset($userData[$field]) || empty(trim($userData[$field]))) {
                $fieldNames = [
                    'username' => 'ชื่อผู้ใช้งาน',
                    'email' => 'อีเมล',
                    'password' => 'รหัสผ่าน',
                    'confirm_password' => 'ยืนยันรหัสผ่าน',
                    'full_name' => 'ชื่อ-นามสกุล',
                    'phone' => 'เบอร์โทรศัพท์'
                ];
                
                $errors[] = 'กรุณากรอก' . ($fieldNames[$field] ?? $field);
            }
        }
        
        if (!empty($errors)) {
            return $errors;
        }
        
        // ตรวจสอบรูปแบบข้อมูล
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
        }
        
        if (strlen($userData['username']) < 3 || strlen($userData['username']) > 20) {
            $errors[] = 'ชื่อผู้ใช้งานต้องมี 3-20 ตัวอักษร';
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $userData['username'])) {
            $errors[] = 'ชื่อผู้ใช้งานใช้ได้เฉพาะตัวอักษร ตัวเลข และ _ เท่านั้น';
        }
        
        if (strlen($userData['password']) < 6 || strlen($userData['password']) > 50) {
            $errors[] = 'รหัสผ่านต้องมี 6-50 ตัวอักษร';
        }
        
        if ($userData['password'] !== $userData['confirm_password']) {
            $errors[] = 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน';
        }
        
        if (strlen($userData['full_name']) < 2 || strlen($userData['full_name']) > 100) {
            $errors[] = 'ชื่อ-นามสกุลต้องมี 2-100 ตัวอักษร';
        }
        
        $cleanPhone = preg_replace('/[^0-9]/', '', $userData['phone']);
        if (!preg_match('/^\+?[0-9]{8,15}$/', $userData['phone'])) {
            $errors[] = 'เบอร์โทรศัพท์ไม่ถูกต้อง (กรุณากรอกเบอร์โทรศัพท์ที่ถูกต้อง เช่น +66812345678 หรือ 0812345678)';
        }
        
        return $errors;
    }
    
    public static function validateOTP($otp) {
        if (empty($otp)) {
            return 'กรุณากรอกรหัส OTP';
        }
        
        if (!preg_match('/^[0-9]{6}$/', $otp)) {
            return 'รหัส OTP ต้องเป็นตัวเลข 6 หลัก';
        }
        
        return null; // ไม่มี error
    }
}
?>