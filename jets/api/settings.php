<?php
header('Content-Type: application/json');
$settings_file = __DIR__ . '/../data/settings.json';
$default_settings = [
    "site_name" => "prestige88",
    "site_description" => "Premium Private Jet Booking System",
    "contact_email" => "admin@prestige88.com",
    "contact_phone" => "+66 2 123 4567",
    "otp_expiry" => 5,
    "otp_length" => 6,
    "enable_sms" => true,
    "enable_email_otp" => false,
    "membership_tiers" => [
        "silver" => ["points" => 0, "discount" => 0],
        "gold" => ["points" => 4000, "discount" => 10],
        "platinum" => ["points" => 10000, "discount" => 20]
    ],
    "points_rate" => 100,
    "default_currency" => "USD",
    "maintenance_mode" => false,
    "debug_mode" => false
];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
if ($action === 'save') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;
    unset($input['action']);
    file_put_contents($settings_file, json_encode($input, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    echo json_encode(["success" => true, "message" => "Settings saved."]);
    exit;
} elseif ($action === 'reset') {
    file_put_contents($settings_file, json_encode($default_settings, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    echo json_encode(["success" => true, "message" => "Settings reset to default.", "settings" => $default_settings]);
    exit;
} elseif ($action === 'export') {
    $settings = file_exists($settings_file) ? json_decode(file_get_contents($settings_file), true) : $default_settings;
    header('Content-Disposition: attachment; filename="settings_export.json"');
    echo json_encode($settings, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    exit;
} elseif ($action === 'get') {
    $settings = file_exists($settings_file) ? json_decode(file_get_contents($settings_file), true) : $default_settings;
    echo json_encode(["success" => true, "settings" => $settings]);
    exit;
} else {
    echo json_encode(["success" => false, "message" => "Invalid action."]);
    exit;
} 