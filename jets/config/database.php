<?php
class Database {
    private static $bookingsFile = 'data/jet_bookings.json';
    private static $jetsFile = 'data/private_jets.json';
    private static $usersFile = 'data/jet_users.json';
    
    public static function initializeFiles() {
        if (!is_dir('data')) {
            mkdir('data', 0755, true);
        }
        
        if (!file_exists(self::$bookingsFile)) {
            file_put_contents(self::$bookingsFile, json_encode([]));
        }
        
        if (!file_exists(self::$usersFile)) {
            self::createDefaultUsers();
        }
        
        if (!file_exists(self::$jetsFile)) {
            self::createDefaultJets();
        }
    }
    
    public static function loadUsers() {
        $data = file_get_contents(self::$usersFile);
        return json_decode($data, true) ?: [];
    }
    
    public static function saveUsers($users) {
        file_put_contents(self::$usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    public static function loadBookings() {
        $data = file_get_contents(self::$bookingsFile);
        return json_decode($data, true) ?: [];
    }
    
    public static function saveBookings($bookings) {
        file_put_contents(self::$bookingsFile, json_encode($bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    public static function loadJets() {
        $data = file_get_contents(self::$jetsFile);
        return json_decode($data, true) ?: [];
    }
    
    public static function saveJets($jets) {
        file_put_contents(self::$jetsFile, json_encode($jets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    private static function createDefaultUsers() {
        $defaultUsers = [
            [
                'id' => 'admin001',
                'username' => 'admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'email' => 'admin@prestige88.com',
                'full_name' => 'ผู้ดูแลระบบ',
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 'client001',
                'username' => 'client',
                'password' => password_hash('client123', PASSWORD_DEFAULT),
                'email' => 'client@example.com',
                'full_name' => 'นายสมชาย ลูกค้าวีไอพี',
                'phone' => '081-234-5678',
                'role' => 'client',
                'membership_tier' => 'silver',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        file_put_contents(self::$usersFile, json_encode($defaultUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    private static function createDefaultJets() {
        $defaultJets = [
            [
                'id' => 'jet001',
                'model' => 'Gulfstream G650',
                'capacity' => 18,
                'price_per_hour' => 350000,
                'range_km' => 13000,
                'max_speed' => 956,
                'amenities' => ['Wi-Fi', 'Bar', 'Bedroom', 'Conference Room', 'Entertainment System'],
                'image' => 'https://images.unsplash.com/photo-1540962351504-03099e0a754b?w=800',
                'status' => 'available'
            ],
            [
                'id' => 'jet002',
                'model' => 'Bombardier Global 7500',
                'capacity' => 19,
                'price_per_hour' => 400000,
                'range_km' => 14260,
                'max_speed' => 982,
                'amenities' => ['Wi-Fi', 'Full Kitchen', 'Master Suite', 'Shower', 'Office Space'],
                'image' => 'https://images.unsplash.com/photo-1583508915901-b5f84c1dcde1?w=800',
                'status' => 'available'
            ],
            [
                'id' => 'jet003',
                'model' => 'Cessna Citation X+',
                'capacity' => 12,
                'price_per_hour' => 180000,
                'range_km' => 6019,
                'max_speed' => 972,
                'amenities' => ['Wi-Fi', 'Refreshment Center', 'Work Area'],
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800',
                'status' => 'available'
            ]
        ];
        file_put_contents(self::$jetsFile, json_encode($defaultJets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}