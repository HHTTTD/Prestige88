<?php
// Environment Configuration
class Environment {
    // Environment Detection
    const ENV_DEVELOPMENT = 'development';
    const ENV_STAGING = 'staging';
    const ENV_PRODUCTION = 'production';
    
    private static $environment;
    private static $config;
    
    public static function initialize() {
        // Detect environment
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            self::$environment = self::ENV_DEVELOPMENT;
        } elseif (strpos($host, 'staging') !== false || strpos($host, 'test') !== false) {
            self::$environment = self::ENV_STAGING;
        } else {
            self::$environment = self::ENV_PRODUCTION;
        }
        
        // Load configuration based on environment
        self::loadConfig();
        
        // Set error reporting
        self::setErrorReporting();
        
        // Set timezone
        date_default_timezone_set('Asia/Bangkok');
    }
    
    private static function loadConfig() {
        $configs = [
            self::ENV_DEVELOPMENT => [
                'debug' => true,
                'log_errors' => true,
                'display_errors' => true,
                'cache_enabled' => false,
                'sms_provider' => 'log',
                'database_type' => 'json',
                'session_timeout' => 7200, // 2 hours for development
                'rate_limit_enabled' => false
            ],
            self::ENV_STAGING => [
                'debug' => true,
                'log_errors' => true,
                'display_errors' => false,
                'cache_enabled' => true,
                'sms_provider' => 'log', // Use real SMS in production
                'database_type' => 'json',
                'session_timeout' => 3600, // 1 hour
                'rate_limit_enabled' => true
            ],
            self::ENV_PRODUCTION => [
                'debug' => false,
                'log_errors' => true,
                'display_errors' => false,
                'cache_enabled' => true,
                'sms_provider' => 'twilio', // or 'thai_sms'
                'database_type' => 'mysql', // Consider using MySQL for production
                'session_timeout' => 1800, // 30 minutes for security
                'rate_limit_enabled' => true
            ]
        ];
        
        self::$config = $configs[self::$environment] ?? $configs[self::ENV_DEVELOPMENT];
    }
    
    private static function setErrorReporting() {
        if (self::$config['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }
        
        if (self::$config['log_errors']) {
            ini_set('log_errors', 1);
            ini_set('error_log', 'storage/logs/php_errors.log');
        }
    }
    
    public static function get($key, $default = null) {
        return self::$config[$key] ?? $default;
    }
    
    public static function isDevelopment() {
        return self::$environment === self::ENV_DEVELOPMENT;
    }
    
    public static function isStaging() {
        return self::$environment === self::ENV_STAGING;
    }
    
    public static function isProduction() {
        return self::$environment === self::ENV_PRODUCTION;
    }
    
    public static function getEnvironment() {
        return self::$environment;
    }
    
    // Database Configuration
    public static function getDatabaseConfig() {
        if (self::get('database_type') === 'mysql') {
            return [
                'host' => 'localhost',
                'dbname' => 'prestige88_db',
                'username' => 'prestige88_user',
                'password' => 'your_secure_password',
                'charset' => 'utf8mb4'
            ];
        }
        
        return null; // Use JSON files
    }
    
    // Cache Configuration
    public static function getCacheConfig() {
        return [
            'enabled' => self::get('cache_enabled'),
            'path' => 'storage/cache/',
            'ttl' => 3600 // 1 hour
        ];
    }
    
    // Security Configuration
    public static function getSecurityConfig() {
        return [
            'session_timeout' => self::get('session_timeout'),
            'rate_limit_enabled' => self::get('rate_limit_enabled'),
            'max_login_attempts' => 5,
            'lockout_duration' => 900, // 15 minutes
            'password_min_length' => 8,
            'require_special_chars' => true
        ];
    }
    
    // Email Configuration (for future use)
    public static function getEmailConfig() {
        return [
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_username' => 'noreply@prestige88.com',
            'smtp_password' => 'your_email_password',
            'from_name' => 'Prestige88',
            'from_email' => 'noreply@prestige88.com'
        ];
    }
    
    // File Upload Configuration
    public static function getUploadConfig() {
        return [
            'max_file_size' => 5 * 1024 * 1024, // 5MB
            'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'upload_path' => 'uploads/',
            'create_thumbnails' => true,
            'thumbnail_size' => [300, 300]
        ];
    }
    
    // Logging Configuration
    public static function getLoggingConfig() {
        return [
            'enabled' => true,
            'path' => 'storage/logs/',
            'max_files' => 30, // Keep logs for 30 days
            'max_file_size' => 10 * 1024 * 1024 // 10MB per log file
        ];
    }
}

// Initialize Environment
Environment::initialize();

// Helper functions
function isDevelopment() {
    return Environment::isDevelopment();
}

function isProduction() {
    return Environment::isProduction();
}

function config($key, $default = null) {
    return Environment::get($key, $default);
}

// Error handling
function handleError($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $errorMessage = date('Y-m-d H:i:s') . " Error [$errno]: $errstr in $errfile on line $errline\n";
    
    if (Environment::get('log_errors')) {
        error_log($errorMessage, 3, 'storage/logs/php_errors.log');
    }
    
    if (Environment::isDevelopment() && Environment::get('display_errors')) {
        echo "<div style='background: #fee; border: 1px solid #fcc; padding: 10px; margin: 10px;'>";
        echo "<strong>Error:</strong> $errstr<br>";
        echo "<strong>File:</strong> $errfile<br>";
        echo "<strong>Line:</strong> $errline";
        echo "</div>";
    }
    
    return true;
}

// Set error handler
set_error_handler('handleError');

// Fatal error handler
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $errorMessage = date('Y-m-d H:i:s') . " Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}\n";
        
        if (Environment::get('log_errors')) {
            error_log($errorMessage, 3, 'storage/logs/php_errors.log');
        }
        
        if (Environment::isDevelopment()) {
            echo "<div style='background: #fee; border: 1px solid #fcc; padding: 10px; margin: 10px;'>";
            echo "<strong>Fatal Error:</strong> {$error['message']}<br>";
            echo "<strong>File:</strong> {$error['file']}<br>";
            echo "<strong>Line:</strong> {$error['line']}";
            echo "</div>";
        } else {
            // Show user-friendly error page in production
            http_response_code(500);
            include 'views/errors/500.php';
        }
    }
});
?> 