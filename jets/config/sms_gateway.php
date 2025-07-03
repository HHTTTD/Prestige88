<?php
// SMS Gateway Configuration
class SMSConfig {
    // Twilio Configuration
    const TWILIO_ACCOUNT_SID = 'your_twilio_account_sid';
    const TWILIO_AUTH_TOKEN = 'your_twilio_auth_token';
    const TWILIO_FROM_NUMBER = '+1234567890';
    
    // Thai SMS Gateway Configuration
    const THAI_SMS_USERNAME = 'your_thai_sms_username';
    const THAI_SMS_PASSWORD = 'your_thai_sms_password';
    const THAI_SMS_SENDER = 'PRESTIGE88';
    
    // SMS Provider Selection
    const SMS_PROVIDER = 'log'; // 'twilio', 'thai_sms', 'log' (for testing)
    
    // SMS Templates
    const OTP_TEMPLATE = 'รหัส OTP ของคุณคือ: {otp} หมดอายุใน 5 นาที - Prestige88';
    const BOOKING_CONFIRMATION = 'การจองของคุณได้รับการยืนยันแล้ว - Prestige88';
    const BOOKING_CANCELLATION = 'การจองของคุณถูกยกเลิกแล้ว - Prestige88';
    const REMINDER_TEMPLATE = 'เตือน: การเดินทางของคุณในวันที่ {date} เวลา {time} - Prestige88';
}

// SMS Gateway Implementation
class SMSGateway {
    private static $provider;
    private static $config;
    
    public static function initialize() {
        self::$provider = SMSConfig::SMS_PROVIDER;
        self::$config = [
            'twilio' => [
                'account_sid' => SMSConfig::TWILIO_ACCOUNT_SID,
                'auth_token' => SMSConfig::TWILIO_AUTH_TOKEN,
                'from_number' => SMSConfig::TWILIO_FROM_NUMBER
            ],
            'thai_sms' => [
                'username' => SMSConfig::THAI_SMS_USERNAME,
                'password' => SMSConfig::THAI_SMS_PASSWORD,
                'sender' => SMSConfig::THAI_SMS_SENDER
            ]
        ];
    }
    
    public static function sendOTP($phone, $otp) {
        $message = str_replace('{otp}', $otp, SMSConfig::OTP_TEMPLATE);
        return self::send($phone, $message);
    }
    
    public static function sendBookingConfirmation($phone, $bookingDetails) {
        $message = SMSConfig::BOOKING_CONFIRMATION;
        return self::send($phone, $message);
    }
    
    public static function sendBookingCancellation($phone) {
        $message = SMSConfig::BOOKING_CANCELLATION;
        return self::send($phone, $message);
    }
    
    public static function sendReminder($phone, $date, $time) {
        $message = str_replace(['{date}', '{time}'], [$date, $time], SMSConfig::REMINDER_TEMPLATE);
        return self::send($phone, $message);
    }
    
    private static function send($phone, $message) {
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
    
    private static function sendViaTwilio($phone, $message) {
        $config = self::$config['twilio'];
        
        if (empty($config['account_sid']) || empty($config['auth_token'])) {
            error_log('Twilio credentials not configured');
            return false;
        }
        
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$config['account_sid']}/Messages.json";
        
        $data = [
            'From' => $config['from_number'],
            'To' => $phone,
            'Body' => $message
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, "{$config['account_sid']}:{$config['auth_token']}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("Twilio SMS Error: $error");
            return false;
        }
        
        if ($httpCode === 201) {
            self::logSMS($phone, $message, 'twilio', true);
            return true;
        } else {
            error_log("Twilio SMS HTTP Error: $httpCode - $response");
            self::logSMS($phone, $message, 'twilio', false, $response);
            return false;
        }
    }
    
    private static function sendViaThaiSMS($phone, $message) {
        $config = self::$config['thai_sms'];
        
        if (empty($config['username']) || empty($config['password'])) {
            error_log('Thai SMS credentials not configured');
            return false;
        }
        
        // Thai SMS API endpoint (example)
        $url = 'https://api.thaisms.com/send';
        
        $data = [
            'username' => $config['username'],
            'password' => $config['password'],
            'sender' => $config['sender'],
            'msisdn' => $phone,
            'message' => $message
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("Thai SMS Error: $error");
            return false;
        }
        
        if ($httpCode === 200) {
            self::logSMS($phone, $message, 'thai_sms', true);
            return true;
        } else {
            error_log("Thai SMS HTTP Error: $httpCode - $response");
            self::logSMS($phone, $message, 'thai_sms', false, $response);
            return false;
        }
    }
    
    private static function sendViaLog($phone, $message) {
        // For testing purposes - log to file instead of sending real SMS
        if (!is_dir('storage/logs')) {
            mkdir('storage/logs', 0755, true);
        }
        
        $logMessage = "[" . date('Y-m-d H:i:s') . "] SMS to {$phone}: {$message}\n";
        file_put_contents('storage/logs/sms.log', $logMessage, FILE_APPEND | LOCK_EX);
        
        self::logSMS($phone, $message, 'log', true);
        return true;
    }
    
    private static function logSMS($phone, $message, $provider, $success, $error = null) {
        if (!is_dir('storage/logs')) {
            mkdir('storage/logs', 0755, true);
        }
        
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'phone' => $phone,
            'message' => $message,
            'provider' => $provider,
            'success' => $success,
            'error' => $error
        ];
        
        $logMessage = json_encode($logData) . "\n";
        file_put_contents('storage/logs/sms_detailed.log', $logMessage, FILE_APPEND | LOCK_EX);
    }
}

// Initialize SMS Gateway
SMSGateway::initialize();
?> 