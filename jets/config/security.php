<?php
// Security Configuration
define('CSRF_TOKEN_NAME', 'prestige88_csrf_token');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('OTP_EXPIRY_TIME', 300); // 5 minutes
define('MAX_OTP_ATTEMPTS', 3);

// Rate limiting
define('RATE_LIMIT_REQUESTS', 100); // requests per minute
define('RATE_LIMIT_WINDOW', 60); // seconds

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('UPLOAD_PATH', 'uploads/');

// Security headers
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; style-src \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com; img-src \'self\' data: https:; font-src \'self\' https://cdnjs.cloudflare.com;');
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function validateCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// Rate Limiting
function checkRateLimit($identifier, $maxRequests = RATE_LIMIT_REQUESTS, $window = RATE_LIMIT_WINDOW) {
    $cacheFile = 'storage/cache/rate_limit_' . md5($identifier) . '.json';
    $now = time();
    
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if ($data['expires'] > $now) {
            if ($data['count'] >= $maxRequests) {
                return false; // Rate limit exceeded
            }
            $data['count']++;
        } else {
            $data = ['count' => 1, 'expires' => $now + $window];
        }
    } else {
        $data = ['count' => 1, 'expires' => $now + $window];
    }
    
    if (!is_dir(dirname($cacheFile))) {
        mkdir(dirname($cacheFile), 0755, true);
    }
    file_put_contents($cacheFile, json_encode($data));
    return true;
}

// Input Sanitization
function sanitizeInput($input, $type = 'string') {
    switch ($type) {
        case 'email':
            return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
        case 'int':
            return intval($input);
        case 'float':
            return floatval($input);
        case 'url':
            return filter_var(trim($input), FILTER_SANITIZE_URL);
        case 'string':
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

// Session Security
function secureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    
    return true;
}
?> 