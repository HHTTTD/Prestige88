<?php
// File: models/OTP.php
require_once 'config/database.php';

class OTP {
    private static $otpFile = 'data/otp_verifications.json';
    
    public static function initializeOTPFile() {
        if (!is_dir('data')) {
            mkdir('data', 0755, true);
        }
        
        if (!file_exists(self::$otpFile)) {
            file_put_contents(self::$otpFile, json_encode([]));
        }
    }
    
    public static function loadOTPs() {
        self::initializeOTPFile();
        $data = file_get_contents(self::$otpFile);
        return json_decode($data, true) ?: [];
    }
    
    public static function saveOTPs($otps) {
        self::initializeOTPFile();
        file_put_contents(self::$otpFile, json_encode($otps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    public static function generateOTP() {
        return sprintf('%06d', mt_rand(100000, 999999));
    }
    
    public static function sendOTP($phone, $otp) {
        // ในการใช้งานจริง ให้เชื่อมต่อกับ SMS Gateway
        // เช่น Twilio, AWS SNS, หรือผู้ให้บริการ SMS ในไทย
        
        // สำหรับการทดสอบ ให้บันทึกลง log file
        if (!is_dir('storage/logs')) {
            mkdir('storage/logs', 0755, true);
        }
        
        $logMessage = "[" . date('Y-m-d H:i:s') . "] OTP sent to {$phone}: {$otp}\n";
        file_put_contents('storage/logs/otp.log', $logMessage, FILE_APPEND | LOCK_EX);
        
        // ในการใช้งานจริง return true/false ตามผลการส่ง SMS
        return true;
    }
    
    public static function createOTPVerification($phone, $email, $userData) {
        $otps = self::loadOTPs();
        $otp = self::generateOTP();
        $sessionId = uniqid('otp_');
        
        // ลบ OTP เก่าของเบอร์นี้ (ถ้ามี)
        $otps = array_filter($otps, function($item) use ($phone) {
            return ($item['phone'] ?? '') !== $phone;
        });
        
        $otpData = [
            'session_id' => $sessionId,
            'phone' => $phone,
            'email' => $email,
            'otp' => $otp,
            'user_data' => $userData,
            'created_at' => time(),
            'expires_at' => time() + (5 * 60), // หมดอายุใน 5 นาที
            'attempts' => 0,
            'max_attempts' => 3,
            'verified' => false
        ];
        
        $otps[] = $otpData;
        self::saveOTPs($otps);
        
        // ส่ง OTP ไปยังเบอร์โทร
        self::sendOTP($phone, $otp);
        
        return $sessionId;
    }
    
    public static function verifyOTP($sessionId, $inputOTP) {
        $otps = self::loadOTPs();
        $result = ['success' => false, 'message' => '', 'user_data' => null];
        
        foreach ($otps as &$otpData) {
            if (($otpData['session_id'] ?? '') === $sessionId) {
                // ตรวจสอบว่าหมดอายุหรือไม่
                if (time() > ($otpData['expires_at'] ?? 0)) {
                    $result['message'] = 'รหัส OTP หมดอายุแล้ว กรุณาขอรหัสใหม่';
                    break;
                }
                
                // ตรวจสอบจำนวนครั้งที่ลองผิด
                if (($otpData['attempts'] ?? 0) >= ($otpData['max_attempts'] ?? 3)) {
                    $result['message'] = 'ใส่รหัส OTP ผิดเกินกำหนด กรุณาขอรหัสใหม่';
                    break;
                }
                
                // เพิ่มจำนวนครั้งที่ลอง
                $otpData['attempts'] = ($otpData['attempts'] ?? 0) + 1;
                
                // ตรวจสอบรหัส OTP
                if (($otpData['otp'] ?? '') === $inputOTP) {
                    $otpData['verified'] = true;
                    $otpData['verified_at'] = time();
                    $result['success'] = true;
                    $result['message'] = 'ยืนยัน OTP สำเร็จ';
                    $result['user_data'] = $otpData['user_data'] ?? null;
                } else {
                    $remaining = ($otpData['max_attempts'] ?? 3) - ($otpData['attempts'] ?? 0);
                    if ($remaining > 0) {
                        $result['message'] = "รหัส OTP ไม่ถูกต้อง เหลือโอกาสอีก {$remaining} ครั้ง";
                    } else {
                        $result['message'] = 'รหัส OTP ไม่ถูกต้อง หมดโอกาสในการลอง';
                    }
                }
                
                self::saveOTPs($otps);
                break;
            }
        }
        
        if (!isset($otpData)) {
            $result['message'] = 'ไม่พบข้อมูล OTP หรือเซสชันหมดอายุ';
        }
        
        return $result;
    }
    
    public static function resendOTP($sessionId) {
        $otps = self::loadOTPs();
        
        foreach ($otps as &$otpData) {
            if (($otpData['session_id'] ?? '') === $sessionId && !($otpData['verified'] ?? false)) {
                // ตรวจสอบว่าส่งซ้ำเร็วเกินไปหรือไม่ (ต้องรอ 1 นาที)
                $lastSent = $otpData['last_sent'] ?? $otpData['created_at'] ?? 0;
                if (time() - $lastSent < 60) {
                    $remainingTime = 60 - (time() - $lastSent);
                    return [
                        'success' => false,
                        'message' => "กรุณารอ {$remainingTime} วินาทีก่อนขอรหัสใหม่"
                    ];
                }
                
                // สร้าง OTP ใหม่
                $newOTP = self::generateOTP();
                $otpData['otp'] = $newOTP;
                $otpData['last_sent'] = time();
                $otpData['expires_at'] = time() + (5 * 60);
                $otpData['attempts'] = 0;
                
                self::saveOTPs($otps);
                self::sendOTP($otpData['phone'] ?? '', $newOTP);
                
                return [
                    'success' => true,
                    'message' => 'ส่งรหัส OTP ใหม่แล้ว'
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'ไม่พบข้อมูล OTP'
        ];
    }
    
    // เพิ่ม method ที่ขาดหาย
    public static function cleanupUsedOTP($sessionId) {
        $otps = self::loadOTPs();
        
        // ลบ OTP ที่ใช้แล้วออก
        $otps = array_filter($otps, function($otp) use ($sessionId) {
            return ($otp['session_id'] ?? '') !== $sessionId;
        });
        
        self::saveOTPs(array_values($otps));
        
        // บันทึก log
        if (!is_dir('storage/logs')) {
            mkdir('storage/logs', 0755, true);
        }
        
        $logMessage = "[" . date('Y-m-d H:i:s') . "] OTP session cleaned up: {$sessionId}\n";
        file_put_contents('storage/logs/otp.log', $logMessage, FILE_APPEND | LOCK_EX);
        
        return true;
    }
    
    public static function cleanupExpiredOTPs() {
        $otps = self::loadOTPs();
        $currentTime = time();
        $beforeCount = count($otps);
        
        $otps = array_filter($otps, function($otp) use ($currentTime) {
            // เก็บ OTP ที่ยังไม่หมดอายุ หรือที่ยืนยันแล้วแต่ยังไม่เกิน 1 ชั่วโมง
            $isNotExpired = ($otp['expires_at'] ?? 0) > $currentTime;
            $isRecentlyVerified = ($otp['verified'] ?? false) && 
                                 (($currentTime - ($otp['verified_at'] ?? 0)) < 3600);
            
            return $isNotExpired || $isRecentlyVerified;
        });
        
        $afterCount = count($otps);
        $cleanedCount = $beforeCount - $afterCount;
        
        if ($cleanedCount > 0) {
            self::saveOTPs(array_values($otps));
            
            // บันทึก log
            if (!is_dir('storage/logs')) {
                mkdir('storage/logs', 0755, true);
            }
            
            $logMessage = "[" . date('Y-m-d H:i:s') . "] Cleaned up {$cleanedCount} expired OTP(s)\n";
            file_put_contents('storage/logs/otp.log', $logMessage, FILE_APPEND | LOCK_EX);
        }
        
        return $cleanedCount;
    }
    
    // เพิ่ม method สำหรับดูข้อมูล OTP (สำหรับ debug)
    public static function getOTPBySessionId($sessionId) {
        $otps = self::loadOTPs();
        
        foreach ($otps as $otp) {
            if (($otp['session_id'] ?? '') === $sessionId) {
                return $otp;
            }
        }
        
        return null;
    }
    
    // เพิ่ม method สำหรับลบ OTP ทั้งหมดของเบอร์โทร
    public static function removeOTPsByPhone($phone) {
        $otps = self::loadOTPs();
        
        $otps = array_filter($otps, function($otp) use ($phone) {
            return ($otp['phone'] ?? '') !== $phone;
        });
        
        self::saveOTPs(array_values($otps));
        
        return true;
    }
    
    // เพิ่ม method สำหรับการสถิติ
    public static function getOTPStatistics() {
        $otps = self::loadOTPs();
        $currentTime = time();
        
        $stats = [
            'total' => count($otps),
            'active' => 0,
            'expired' => 0,
            'verified' => 0,
            'pending' => 0
        ];
        
        foreach ($otps as $otp) {
            if ($otp['verified'] ?? false) {
                $stats['verified']++;
            } elseif (($otp['expires_at'] ?? 0) <= $currentTime) {
                $stats['expired']++;
            } else {
                $stats['active']++;
                if (($otp['attempts'] ?? 0) === 0) {
                    $stats['pending']++;
                }
            }
        }
        
        return $stats;
    }
    
    // เพิ่ม method สำหรับ rate limiting
    public static function checkRateLimit($phone, $timeWindow = 3600, $maxAttempts = 5) {
        $otps = self::loadOTPs();
        $currentTime = time();
        $attempts = 0;
        
        foreach ($otps as $otp) {
            if (($otp['phone'] ?? '') === $phone && 
                ($otp['created_at'] ?? 0) > ($currentTime - $timeWindow)) {
                $attempts++;
            }
        }
        
        return [
            'allowed' => $attempts < $maxAttempts,
            'attempts' => $attempts,
            'max_attempts' => $maxAttempts,
            'reset_time' => $currentTime + $timeWindow
        ];
    }
    
    // เพิ่ม method สำหรับส่งข้อความแจ้งเตือน
    public static function sendNotification($phone, $message, $type = 'info') {
        // ในการใช้งานจริงอาจส่งผ่าน SMS, Push Notification, หรือ Email
        if (!is_dir('storage/logs')) {
            mkdir('storage/logs', 0755, true);
        }
        
        $logMessage = "[" . date('Y-m-d H:i:s') . "] Notification ({$type}) to {$phone}: {$message}\n";
        file_put_contents('storage/logs/notifications.log', $logMessage, FILE_APPEND | LOCK_EX);
        
        return true;
    }
}

// เพิ่มคลาสสำหรับจัดการ SMS Gateway
class SMSGateway {
    private static $provider = 'log'; // log, twilio, thai_sms
    private static $config = [];
    
    public static function setProvider($provider, $config = []) {
        self::$provider = $provider;
        self::$config = $config;
    }
    
    public static function send($phone, $message) {
        switch (self::$provider) {
            case 'twilio':
                return self::sendViaTwilio($phone, $message);
            case 'thai_sms':
                return self::sendViaThaiSMS($phone, $message);
            case 'log':
            default:
                return self::sendViaLog($phone, $message);
        }
    }
    
    private static function sendViaLog($phone, $message) {
        if (!is_dir('storage/logs')) {
            mkdir('storage/logs', 0755, true);
        }
        
        $logMessage = "[" . date('Y-m-d H:i:s') . "] SMS to {$phone}: {$message}\n";
        file_put_contents('storage/logs/sms.log', $logMessage, FILE_APPEND | LOCK_EX);
        
        return true;
    }
    
    private static function sendViaTwilio($phone, $message) {
        $accountSid = self::$config['account_sid'] ?? '';
        $authToken = self::$config['auth_token'] ?? '';
        $fromNumber = self::$config['from_number'] ?? '';
        
        if (empty($accountSid) || empty($authToken) || empty($fromNumber)) {
            return false;
        }
        
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";
        
        $data = [
            'From' => $fromNumber,
            'To' => $phone,
            'Body' => $message
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, "{$accountSid}:{$authToken}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 201;
    }
    
    private static function sendViaThaiSMS($phone, $message) {
        $apiKey = self::$config['api_key'] ?? '';
        $apiSecret = self::$config['api_secret'] ?? '';
        $senderName = self::$config['sender_name'] ?? 'prestige88';
        
        if (empty($apiKey) || empty($apiSecret)) {
            return false;
        }
        
        // ใส่ URL และพารามิเตอร์ตาม SMS Gateway ที่ใช้
        $url = 'https://api.example-sms.com/send';
        $data = [
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'sender' => $senderName,
            'to' => $phone,
            'message' => $message
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
}
?>