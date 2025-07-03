<?php
class Utils {
    public static function generateId() {
        return uniqid();
    }
    
    public static function formatCurrency($amount) {
        return '$' . number_format($amount, 2);
    }
    
    public static function formatDate($date, $format = 'd/m/Y') {
        return date($format, strtotime($date));
    }
    
    public static function formatDateTime($datetime, $format = 'd/m/Y H:i') {
        return date($format, strtotime($datetime));
    }
    
    public static function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function generateRandomPassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($chars), 0, $length);
    }
}