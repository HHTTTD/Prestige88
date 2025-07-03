<?php
// Application Constants
define('APP_NAME', 'prestige88');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Private Jet Booking System');

// Database Configuration
define('DATA_DIR', 'data');
define('BOOKINGS_FILE', DATA_DIR . '/jet_bookings.json');
define('USERS_FILE', DATA_DIR . '/jet_users.json');
define('JETS_FILE', DATA_DIR . '/private_jets.json');

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_CLIENT', 'client');

// Booking Status
define('STATUS_PENDING', 'pending');
define('STATUS_CONFIRMED', 'confirmed');
define('STATUS_CANCELLED', 'cancelled');
define('STATUS_COMPLETED', 'completed');

// Membership Tiers
define('TIER_SILVER', 'silver');
define('TIER_GOLD', 'gold');
define('TIER_PLATINUM', 'platinum');
define('TIER_BLACK', 'black');

// File Upload Settings
define('UPLOAD_DIR', 'uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);

// Pagination
define('ITEMS_PER_PAGE', 10);

// Email Settings (for future implementation)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');

// Points Rate
define('POINTS_RATE', 100); // 1 คะแนนต่อ 100 บาท

// Currency Exchange Rate
// 1 USD = 36 บาท (แก้ไขได้ตามอัตราจริง)
define('THB_TO_USD', 36);