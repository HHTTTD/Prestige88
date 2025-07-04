<?php
// --- ดัก download_my_bookings ให้ทำงานก่อน output อื่นใด ---
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'download_my_bookings'
) {
    require_once __DIR__ . '/controllers/AuthController.php';
    require_once __DIR__ . '/controllers/BookingController.php';
    session_start();
    if (!AuthController::isLoggedIn()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
        exit;
    }
    $currentUser = AuthController::getCurrentUser();
    $userBookings = BookingController::getBookingsForUser('client', $currentUser['id']);
    $downloadData = [
        'user_info' => [
            'id' => $currentUser['id'],
            'full_name' => $currentUser['full_name'],
            'email' => $currentUser['email'],
            'phone' => $currentUser['phone'],
            'membership_tier' => $currentUser['membership_tier'] ?? 'silver'
        ],
        'bookings' => $userBookings,
        'download_date' => date('Y-m-d H:i:s'),
        'total_bookings' => count($userBookings)
    ];
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename=\"my_bookings.json\"');
    echo json_encode($downloadData, JSON_PRETTY_PRINT);
    exit;
}
// --- ดัก download_my_data ให้ทำงานก่อน output อื่นใด ---
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'download_my_data'
) {
    require_once __DIR__ . '/controllers/AuthController.php';
    require_once __DIR__ . '/controllers/BookingController.php';
    require_once __DIR__ . '/utils/pdf_generator.php';
    
    session_start();
    if (!AuthController::isLoggedIn()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
        exit;
    }
    
    $currentUser = AuthController::getCurrentUser();
    $userBookings = BookingController::getBookingsForUser('client', $currentUser['id']);
    
    // Generate PDF
    $pdfGenerator = new PDFGenerator();
    $html = $pdfGenerator->generateBookingsPDF($currentUser, $userBookings);
    
    // Add auto-print functionality
    $finalHtml = $pdfGenerator->outputPDF($html, 'prestige_jets_statement_' . $currentUser['id'] . '.pdf');
    
    // Output as HTML that will auto-print to PDF
    header('Content-Type: text/html; charset=UTF-8');
    echo $finalHtml;
    exit;
}
session_start();

// Include all required files
require_once 'config/database.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/BookingController.php';
require_once 'models/Membership.php';
require_once 'api/notifications.php';

// Include OTP System
require_once 'models/OTP.php';
require_once 'controllers/OTPController.php';
require_once 'views/auth/otp-verification.php';

// Include Offers & Rewards System
require_once 'models/OffersRewards.php';
require_once 'utils/functions.php';

// Include Views
require_once 'views/auth/login.php';
require_once 'views/components/navbar.php';
require_once 'views/components/message.php';
require_once 'views/components/membership-card.php';
require_once 'views/components/bookings-list.php';
require_once 'views/components/jets-showcase.php';
require_once 'views/components/admin-dashboard.php';
require_once 'views/components/footer.php';
require_once 'views/components/popular-destinations.php';
require_once 'views/components/recent-searches.php';
require_once 'views/components/special-offers.php';
require_once 'views/components/upcoming-tickets.php';
require_once 'views/admin/manage-users.php';
require_once 'views/admin/manage-jets.php';
require_once 'views/admin/manage-logs.php';
require_once 'views/admin/manage-settings.php';
require_once 'views/admin/manage-rewards.php';

// Initialize database
Database::initializeFiles();
OTP::initializeOTPFile();
OffersRewards::initializeDataFile();

$message = '';
$messageType = '';

// --- HANDLE LOG MANAGEMENT (AJAX) ---
if (isset($_POST['action']) && AuthController::isLoggedIn()) {
    $action = $_POST['action'];
    $logFiles = [
        'all' => [
            'storage/logs/otp.log',
            'storage/logs/sms.log',
            'storage/logs/notifications.log'
        ],
        'otp' => ['storage/logs/otp.log'],
        'sms' => ['storage/logs/sms.log'],
        'notification' => ['storage/logs/notifications.log']
    ];
    if ($action === 'export_logs') {
        $type = $_POST['log_type'] ?? 'all';
        $files = $logFiles[$type] ?? $logFiles['all'];
        $content = '';
        foreach ($files as $file) {
            if (file_exists($file)) {
                $content .= file_get_contents($file) . "\n";
            }
        }
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="system_logs.txt"');
        echo $content;
        exit;
    } elseif ($action === 'clear_logs') {
        $type = $_POST['log_type'] ?? 'all';
        $files = $logFiles[$type] ?? $logFiles['all'];
        foreach ($files as $file) {
            file_put_contents($file, '');
        }
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'delete_log') {
        $type = $_POST['log_type'] ?? 'all';
        $index = intval($_POST['log_index'] ?? -1);
        $files = $logFiles[$type] ?? $logFiles['all'];
        foreach ($files as $file) {
            if (file_exists($file)) {
                $lines = file($file, FILE_IGNORE_NEW_LINES);
                if ($index >= 0 && $index < count($lines)) {
                    array_splice($lines, $index, 1);
                    file_put_contents($file, implode("\n", $lines));
                }
            }
        }
        echo json_encode(['success' => true]);
        exit;
    } elseif ($action === 'filter_logs') {
        $logType = $_POST['log_type'] ?? 'all';
        $logs = [];
        if ($logType === 'activity') {
            $file = __DIR__ . '/storage/logs/activity.log';
            if (file_exists($file)) {
                $logs = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            }
        } elseif ($logType === 'otp') {
            $file = __DIR__ . '/storage/logs/otp.log';
            if (file_exists($file)) {
                $logs = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            }
        } elseif ($logType === 'notification') {
            $file = __DIR__ . '/storage/logs/notifications.log';
            if (file_exists($file)) {
                $logs = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            }
        } elseif ($logType === 'all') {
            foreach (['activity', 'otp', 'notifications'] as $type) {
                $file = __DIR__ . "/storage/logs/{$type}.log";
                if (file_exists($file)) {
                    $logs = array_merge($logs, file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
                }
            }
        }
        echo json_encode(['success' => true, 'logs' => $logs]);
        exit;
    }
}

// --- HANDLE USER MANAGEMENT (AJAX) ---
if (
    isset($_POST['action']) && in_array($_POST['action'], ['update_user', 'delete_user'])
    && AuthController::isLoggedIn()
) {
    $currentUser = AuthController::getCurrentUser();
    if ($currentUser['role'] !== 'admin') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Permission denied.']);
        exit;
    }
    header('Content-Type: application/json');
    try {
        if ($_POST['action'] === 'update_user') {
            $userId = $_POST['user_id'] ?? '';
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'user';
            $tier = $_POST['membership_tier'] ?? 'silver';
            $users = Database::loadUsers();
            $found = false;
            foreach ($users as &$user) {
                if ($user['id'] === $userId) {
                    $user['full_name'] = $fullName;
                    $user['email'] = $email;
                    $user['role'] = $role;
                    $user['membership_tier'] = $tier;
                    $found = true;
                    break;
                }
            }
            if (!$found) throw new Exception('User not found');
            Database::saveUsers($users);
            $updatedUser = null;
            foreach ($users as $u) {
                if ($u['id'] === $userId) { $updatedUser = $u; break; }
            }
            echo json_encode(['success' => true, 'user' => $updatedUser]);
            exit;
        } elseif ($_POST['action'] === 'delete_user') {
            $userId = $_POST['user_id'] ?? '';
            $users = Database::loadUsers();
            $idx = -1;
            foreach ($users as $i => $user) {
                if ($user['id'] === $userId) { $idx = $i; break; }
            }
            if ($idx === -1) throw new Exception('User not found');
            array_splice($users, $idx, 1);
            Database::saveUsers($users);
            echo json_encode(['success' => true]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// --- HANDLE PROFILE UPDATE (AJAX) ---
if (
    isset($_POST['action']) && $_POST['action'] === 'update_profile'
    && AuthController::isLoggedIn()
) {
    $currentUser = AuthController::getCurrentUser();
    header('Content-Type: application/json');
    $userId = $currentUser['id'];
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    // Validate password
    if (!AuthController::verifyPassword($userId, $currentPassword)) {
        echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
        exit;
    }
    // Update user data (in file/db)
    $user = AuthController::getCurrentUser();
    $phoneChanged = ($phone !== ($user['phone'] ?? ''));
    // Remove OTP requirement: always update directly
    $user['full_name'] = $fullName;
    $user['email'] = $email;
    $user['phone'] = $phone;
    $user['dob'] = $dob;
    $user['address'] = $address;
    AuthController::updateUser($userId, $user);
    // ดึง user ล่าสุดจากไฟล์
    $updatedUser = null;
    $users = Database::loadUsers();
    foreach ($users as $u) {
        if ($u['id'] === $userId) { $updatedUser = $u; break; }
    }
    if ($updatedUser) {
        $_SESSION['user_full_name'] = $updatedUser['full_name'];
        $_SESSION['user_email'] = $updatedUser['email'];
        $_SESSION['user_phone'] = $updatedUser['phone'];
        $_SESSION['user_membership_tier'] = $updatedUser['membership_tier'] ?? 'silver';
    }
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!', 'user' => $updatedUser]);
    exit;
}

// --- HANDLE BOOKING UPDATE BY ADMIN (AJAX) ---
if (
    isset($_POST['action']) && $_POST['action'] === 'update_booking'
    && AuthController::isLoggedIn()
) {
    $currentUser = AuthController::getCurrentUser();
    if ($currentUser['role'] !== 'admin') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Permission denied.']);
        exit;
    }

    header('Content-Type: application/json');
    
    $bookingId = $_POST['booking_id'] ?? '';
    $userName = trim($_POST['user_name'] ?? '');
    $departureLocation = trim($_POST['departure_location'] ?? '');
    $arrivalLocation = trim($_POST['arrival_location'] ?? '');
    $departureDate = trim($_POST['departure_date'] ?? '');
    $departureTime = trim($_POST['departure_time'] ?? '');
    $passengers = intval($_POST['passengers'] ?? 1);
    $flightHours = intval($_POST['flight_hours'] ?? 1);
    $specialRequests = trim($_POST['special_requests'] ?? '');
    $status = $_POST['status'] ?? 'pending';
    $seat = trim($_POST['seat'] ?? '');
    $busNumber = trim($_POST['bus_number'] ?? '');
    $boardingGate = trim($_POST['boarding_gate'] ?? '');
    
    try {
        // Load current booking
        $bookings = Database::loadBookings();
        $bookingIndex = -1;
        $booking = null;
        
        foreach ($bookings as $index => $b) {
            if ($b['id'] === $bookingId) {
                $bookingIndex = $index;
                $booking = $b;
                break;
            }
        }
        
        if ($bookingIndex === -1) {
            echo json_encode(['success' => false, 'message' => 'Booking not found.']);
            exit;
        }
        
        // Update booking data
        $booking['user_name'] = $userName;
        $booking['departure_location'] = $departureLocation;
        $booking['arrival_location'] = $arrivalLocation;
        $booking['departure_date'] = $departureDate;
        $booking['departure_time'] = $departureTime;
        $booking['passengers'] = $passengers;
        $booking['flight_hours'] = $flightHours;
        $booking['special_requests'] = $specialRequests;
        $booking['status'] = $status;
        $booking['seat'] = $seat;
        $booking['bus_number'] = $busNumber;
        $booking['boarding_gate'] = $boardingGate;
        $booking['updated_at'] = date('Y-m-d H:i:s');
        $booking['updated_by'] = $currentUser['username'];
        
        // Recalculate total cost
        $baseTotal = $booking['price_per_hour'] * $flightHours;
        $discountAmount = $baseTotal * ($booking['membership_discount'] / 100);
        $booking['base_total'] = $baseTotal;
        $booking['discount_amount'] = $discountAmount;
        $booking['total_cost'] = $baseTotal - $discountAmount;
        
        // Save updated booking
        $bookings[$bookingIndex] = $booking;
        Database::saveBookings($bookings);
        
        // แจ้งเตือนผู้ใช้ว่าข้อมูลการจองของพวกเขาถูกอัปเดต
        require_once 'models/Notification.php';
        Notification::create(
            $booking['user_id'],
            "An admin has updated your booking for {$booking['jet_model']}. Please review the changes.",
            "?page=my-tickets"
        );
        
        echo json_encode([
            'success' => true, 
            'message' => 'Booking updated successfully!',
            'booking' => $booking
        ]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating booking: ' . $e->getMessage()]);
        exit;
    }
}

// --- HANDLE OFFERS & REWARDS MANAGEMENT (AJAX) ---
if (
    isset($_POST['action']) && in_array($_POST['action'], ['add_offer', 'update_offer', 'delete_offer', 'add_reward', 'update_reward', 'delete_reward', 'add_promo', 'update_promo', 'delete_promo'])
    && AuthController::isLoggedIn()
) {
    $currentUser = AuthController::getCurrentUser();
    if ($currentUser['role'] !== 'admin') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Permission denied.']);
        exit;
    }

    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'add_offer':
                $offerData = [
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'icon' => trim($_POST['icon'] ?? 'fas fa-gift'),
                    'badge' => trim($_POST['badge'] ?? ''),
                    'discount_type' => $_POST['discount_type'] ?? 'percentage',
                    'discount_value' => floatval($_POST['discount_value'] ?? 0),
                    'valid_until' => $_POST['valid_until'] ?: null,
                    'target_audience' => $_POST['target_audience'] ?? 'all',
                    'max_usage' => $_POST['max_usage'] ? intval($_POST['max_usage']) : null,
                    'is_active' => isset($_POST['is_active']) ? true : false,
                    'note' => trim($_POST['note'] ?? '')
                ];
                
                $id = OffersRewards::addSpecialOffer($offerData);
                echo json_encode(['success' => true, 'message' => 'Special offer added successfully!', 'id' => $id]);
                break;
                
            case 'update_offer':
                $id = $_POST['offer_id'] ?? '';
                $offerData = [
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'icon' => trim($_POST['icon'] ?? 'fas fa-gift'),
                    'badge' => trim($_POST['badge'] ?? ''),
                    'discount_type' => $_POST['discount_type'] ?? 'percentage',
                    'discount_value' => floatval($_POST['discount_value'] ?? 0),
                    'valid_until' => $_POST['valid_until'] ?: null,
                    'target_audience' => $_POST['target_audience'] ?? 'all',
                    'max_usage' => $_POST['max_usage'] ? intval($_POST['max_usage']) : null,
                    'is_active' => isset($_POST['is_active']) ? true : false,
                    'note' => trim($_POST['note'] ?? '')
                ];
                
                OffersRewards::updateSpecialOffer($id, $offerData);
                echo json_encode(['success' => true, 'message' => 'Special offer updated successfully!']);
                break;
                
            case 'delete_offer':
                $id = $_POST['offer_id'] ?? '';
                OffersRewards::deleteSpecialOffer($id);
                echo json_encode(['success' => true, 'message' => 'Special offer deleted successfully!']);
                break;
                
            case 'add_reward':
                $rewardData = [
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'icon' => trim($_POST['icon'] ?? 'fas fa-star'),
                    'required_points' => intval($_POST['required_points'] ?? 0),
                    'tier' => $_POST['tier'] ?? 'silver',
                    'is_active' => isset($_POST['is_active']) ? true : false
                ];
                
                $id = OffersRewards::addAvailableReward($rewardData);
                echo json_encode(['success' => true, 'message' => 'Available reward added successfully!', 'id' => $id]);
                break;
                
            case 'update_reward':
                $id = $_POST['reward_id'] ?? '';
                $rewardData = [
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'icon' => trim($_POST['icon'] ?? 'fas fa-star'),
                    'required_points' => intval($_POST['required_points'] ?? 0),
                    'tier' => $_POST['tier'] ?? 'silver',
                    'is_active' => isset($_POST['is_active']) ? true : false
                ];
                
                OffersRewards::updateAvailableReward($id, $rewardData);
                echo json_encode(['success' => true, 'message' => 'Available reward updated successfully!']);
                break;
                
            case 'delete_reward':
                $id = $_POST['reward_id'] ?? '';
                OffersRewards::deleteAvailableReward($id);
                echo json_encode(['success' => true, 'message' => 'Available reward deleted successfully!']);
                break;
                
            case 'add_promo':
                $codeData = [
                    'code' => strtoupper(trim($_POST['code'] ?? '')),
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'discount_type' => $_POST['discount_type'] ?? 'percentage',
                    'discount_value' => floatval($_POST['discount_value'] ?? 0),
                    'valid_until' => $_POST['valid_until'] ?: null,
                    'max_usage' => $_POST['max_usage'] ? intval($_POST['max_usage']) : null,
                    'is_active' => isset($_POST['is_active']) ? true : false
                ];
                
                OffersRewards::addPromoCode($codeData);
                echo json_encode(['success' => true, 'message' => 'Promo code added successfully!']);
                break;
                
            case 'update_promo':
                $code = strtoupper(trim($_POST['code'] ?? ''));
                $codeData = [
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'discount_type' => $_POST['discount_type'] ?? 'percentage',
                    'discount_value' => floatval($_POST['discount_value'] ?? 0),
                    'valid_until' => $_POST['valid_until'] ?: null,
                    'max_usage' => $_POST['max_usage'] ? intval($_POST['max_usage']) : null,
                    'is_active' => isset($_POST['is_active']) ? true : false
                ];
                
                OffersRewards::updatePromoCode($code, $codeData);
                echo json_encode(['success' => true, 'message' => 'Promo code updated successfully!']);
                break;
                
            case 'delete_promo':
                $code = strtoupper(trim($_POST['code'] ?? ''));
                OffersRewards::deletePromoCode($code);
                echo json_encode(['success' => true, 'message' => 'Promo code deleted successfully!']);
                break;
        }
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}

// --- HANDLE JET MANAGEMENT (AJAX) ---
if (
    isset($_POST['action']) && in_array($_POST['action'], ['add_jet', 'update_jet', 'delete_jet'])
    && AuthController::isLoggedIn()
) {
    $currentUser = AuthController::getCurrentUser();
    if ($currentUser['role'] !== 'admin') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Permission denied.']);
        exit;
    }

    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'add_jet':
                $jetData = [
                    'id' => 'jet_' . uniqid(),
                    'model' => trim($_POST['model'] ?? ''),
                    'image' => trim($_POST['image'] ?? ''),
                    'capacity' => intval($_POST['capacity'] ?? 0),
                    'max_speed' => intval($_POST['max_speed'] ?? 0),
                    'range_km' => intval($_POST['range_km'] ?? 0),
                    'price_per_hour' => floatval($_POST['price_per_hour'] ?? 0),
                    'amenities' => json_decode($_POST['amenities'] ?? '[]', true) ?: [],
                    'available_slots' => json_decode($_POST['available_slots'] ?? '[]', true) ?: []
                ];
                // Validate required fields
                if (empty($jetData['model'])) throw new Exception('Jet model is required');
                if ($jetData['capacity'] <= 0) throw new Exception('Capacity must be greater than 0');
                if ($jetData['max_speed'] <= 0) throw new Exception('Max speed must be greater than 0');
                if ($jetData['range_km'] <= 0) throw new Exception('Range must be greater than 0');
                if ($jetData['price_per_hour'] <= 0) throw new Exception('Price per hour must be greater than 0');
                $jets = Database::loadJets();
                foreach ($jets as $jet) {
                    if (strtolower($jet['model']) === strtolower($jetData['model'])) {
                        throw new Exception('A jet with this model already exists');
                    }
                }
                $jets[] = $jetData;
                Database::saveJets($jets);
                echo json_encode(['success' => true, 'message' => 'Jet added successfully!']);
                break;
            case 'update_jet':
                $jetId = $_POST['jet_id'] ?? '';
                $jetData = [
                    'model' => trim($_POST['model'] ?? ''),
                    'image' => trim($_POST['image'] ?? ''),
                    'capacity' => intval($_POST['capacity'] ?? 0),
                    'max_speed' => intval($_POST['max_speed'] ?? 0),
                    'range_km' => intval($_POST['range_km'] ?? 0),
                    'price_per_hour' => floatval($_POST['price_per_hour'] ?? 0),
                    'amenities' => json_decode($_POST['amenities'] ?? '[]', true) ?: [],
                    'available_slots' => json_decode($_POST['available_slots'] ?? '[]', true) ?: []
                ];
                if (empty($jetData['model'])) throw new Exception('Jet model is required');
                if ($jetData['capacity'] <= 0) throw new Exception('Capacity must be greater than 0');
                if ($jetData['max_speed'] <= 0) throw new Exception('Max speed must be greater than 0');
                if ($jetData['range_km'] <= 0) throw new Exception('Range must be greater than 0');
                if ($jetData['price_per_hour'] <= 0) throw new Exception('Price per hour must be greater than 0');
                $jets = Database::loadJets();
                $jetIndex = -1;
                foreach ($jets as $index => $jet) {
                    if ($jet['id'] === $jetId) {
                        $jetIndex = $index;
                        break;
                    }
                }
                if ($jetIndex === -1) throw new Exception('Jet not found');
                foreach ($jets as $index => $jet) {
                    if ($index !== $jetIndex && strtolower($jet['model']) === strtolower($jetData['model'])) {
                        throw new Exception('A jet with this model already exists');
                    }
                }
                $jets[$jetIndex] = array_merge($jets[$jetIndex], $jetData);
                Database::saveJets($jets);
                echo json_encode(['success' => true, 'message' => 'Jet updated successfully!']);
                break;
            case 'delete_jet':
                $jetId = $_POST['jet_id'] ?? '';
                $jets = Database::loadJets();
                $jetIndex = -1;
                foreach ($jets as $index => $jet) {
                    if ($jet['id'] === $jetId) {
                        $jetIndex = $index;
                        break;
                    }
                }
                if ($jetIndex === -1) throw new Exception('Jet not found');
                $bookings = Database::loadBookings();
                foreach ($bookings as $booking) {
                    if ($booking['jet_id'] === $jetId && in_array($booking['status'], ['pending', 'confirmed'])) {
                        throw new Exception('Cannot delete jet with active bookings');
                    }
                }
                array_splice($jets, $jetIndex, 1);
                Database::saveJets($jets);
                echo json_encode(['success' => true, 'message' => 'Jet deleted successfully!']);
                break;
        }
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// --- HANDLE JET STATUS UPDATE (AJAX) ---
if (
    isset($_POST['action']) && $_POST['action'] === 'update_jet_status'
    && AuthController::isLoggedIn()
) {
    $currentUser = AuthController::getCurrentUser();
    if ($currentUser['role'] !== 'admin') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Permission denied.']);
        exit;
    }
    header('Content-Type: application/json');
    $jetId = $_POST['jet_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $validStatuses = ['available', 'unavailable'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    $jets = Database::loadJets();
    $found = false;
    foreach ($jets as &$jet) {
        if ($jet['id'] === $jetId) {
            $jet['status'] = $status;
            $jet['updated_at'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }
    if (!$found) {
        echo json_encode(['success' => false, 'message' => 'Jet not found']);
        exit;
    }
    Database::saveJets($jets);
    echo json_encode(['success' => true]);
    exit;
}

// --- HANDLE GET REQUESTS FOR EDITING ---
if (
    isset($_GET['action']) && in_array($_GET['action'], ['get_offer', 'get_reward', 'get_promo'])
    && AuthController::isLoggedIn()
) {
    $currentUser = AuthController::getCurrentUser();
    if ($currentUser['role'] !== 'admin') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Permission denied.']);
        exit;
    }

    header('Content-Type: application/json');
    
    try {
        switch ($_GET['action']) {
            case 'get_offer':
                $offerId = $_GET['offer_id'] ?? '';
                $offers = OffersRewards::getSpecialOffers(false);
                $offer = null;
                
                foreach ($offers as $o) {
                    if ($o['id'] === $offerId) {
                        $offer = $o;
                        break;
                    }
                }
                
                if ($offer) {
                    echo json_encode(['success' => true, 'offer' => $offer]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Offer not found']);
                }
                break;
                
            case 'get_reward':
                $rewardId = $_GET['reward_id'] ?? '';
                $rewards = OffersRewards::getAvailableRewards(false);
                $reward = null;
                
                foreach ($rewards as $r) {
                    if ($r['id'] === $rewardId) {
                        $reward = $r;
                        break;
                    }
                }
                
                if ($reward) {
                    echo json_encode(['success' => true, 'reward' => $reward]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Reward not found']);
                }
                break;
                
            case 'get_promo':
                $code = strtoupper(trim($_GET['code'] ?? ''));
                $promos = OffersRewards::getPromoCodes(false);
                $promo = null;
                
                foreach ($promos as $p) {
                    if (strtoupper($p['code']) === $code) {
                        $promo = $p;
                        break;
                    }
                }
                
                if ($promo) {
                    echo json_encode(['success' => true, 'promo' => $promo]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Promo code not found']);
                }
                break;
        }
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}

// --- HANDLE NOTIFICATION ACTIONS (AJAX) ---
if (isset($_POST['action']) && in_array($_POST['action'], ['mark_notification_read', 'mark_all_notifications_read']) && AuthController::isLoggedIn()) {
    header('Content-Type: application/json');
    $currentUser = AuthController::getCurrentUser();
    if (!class_exists('Notification')) {
        require_once 'models/Notification.php';
    }
    if ($_POST['action'] === 'mark_notification_read') {
        $notificationId = $_POST['notification_id'] ?? '';
        if (Notification::markAsRead($notificationId, $currentUser['id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    } elseif ($_POST['action'] === 'mark_all_notifications_read') {
        if (Notification::markAllAsRead($currentUser['id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
    exit;
}

// --- HANDLE PROMO CODE VALIDATION (AJAX) ---
if (
    isset($_POST['action']) && $_POST['action'] === 'validate_promo'
    && AuthController::isLoggedIn()
) {
    header('Content-Type: application/json');
    
    $code = strtoupper(trim($_POST['code'] ?? ''));
    
    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a promo code']);
        exit;
    }
    
    $promoCode = OffersRewards::validatePromoCode($code);
    
    if ($promoCode) {
        echo json_encode([
            'success' => true, 
            'message' => 'Promo code applied successfully!',
            'promo' => [
                'code' => $promoCode['code'],
                'title' => $promoCode['title'],
                'description' => $promoCode['description'],
                'discount_type' => $promoCode['discount_type'],
                'discount_value' => $promoCode['discount_value']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired promo code']);
    }
    exit;
}

if (isset($_GET['page']) && $_GET['page'] === 'booking') {
    include 'booking_page.php';
}

// --- HANDLE POPULAR DESTINATIONS MANAGEMENT (AJAX) ---
if (
    isset($_POST['action']) && in_array($_POST['action'], ['add_destination', 'update_destination', 'delete_destination'])
    && AuthController::isLoggedIn()
) {
    $currentUser = AuthController::getCurrentUser();
    if ($currentUser['role'] !== 'admin') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Permission denied.']);
        exit;
    }
    header('Content-Type: application/json');
    require_once 'utils/functions.php';
    try {
        if ($_POST['action'] === 'add_destination') {
            $name = trim($_POST['name'] ?? '');
            $image = trim($_POST['image'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $dest = addPopularDestination($name, $image, $price);
            logActivity('Add Popular Destination', "name=$name, price=$price", $currentUser['id']);
            echo json_encode(['success' => true, 'destination' => $dest]);
            exit;
        } elseif ($_POST['action'] === 'update_destination') {
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $image = trim($_POST['image'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            updatePopularDestination($id, $name, $image, $price);
            logActivity('Update Popular Destination', "id=$id, name=$name, price=$price", $currentUser['id']);
            echo json_encode(['success' => true]);
            exit;
        } elseif ($_POST['action'] === 'delete_destination') {
            $id = $_POST['id'] ?? '';
            deletePopularDestination($id);
            logActivity('Delete Popular Destination', "id=$id", $currentUser['id']);
            echo json_encode(['success' => true]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    AuthController::logout();
    // ลบ OTP session ด้วย
    unset($_SESSION['otp_session_id'], $_SESSION['otp_phone']);
    header('Location: ?');
    exit;
}

// Handle OTP request (การสมัครสมาชิก)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    try {
        $userData = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'password' => $_POST['password'],
            'confirm_password' => $_POST['confirm_password'],
            'full_name' => trim($_POST['full_name']),
            'phone' => trim($_POST['phone']),
            'company' => trim($_POST['company'])
        ];
        
        // ส่ง OTP แทนการสมัครสมาชิกเลย
        $sessionId = OTPController::requestOTP($userData);
        
        // เก็บ session_id ใน session
        $_SESSION['otp_session_id'] = $sessionId;
        $_SESSION['otp_phone'] = $userData['phone'];
        
        $message = 'ส่งรหัส OTP ไปยังเบอร์โทรศัพท์ของคุณแล้ว กรุณาตรวจสอบและกรอกรหัสเพื่อยืนยันตัวตน';
        $messageType = 'success';
        
        // Check if this is an AJAX request from widget
        if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'ส่งรหัส OTP ไปยังเบอร์โทรศัพท์ของคุณแล้ว กรุณาตรวจสอบและกรอกรหัสเพื่อยืนยันตัวตน',
                'session_id' => $sessionId,
                'phone' => $userData['phone']
            ]);
            exit;
        }
        
        // แสดงหน้า OTP verification
        echo renderOTPVerificationPage($sessionId, $userData['phone'], $message, $messageType);
        exit;
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
        
        // Check if this is an AJAX request from widget
        if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
        
        // ถ้า error ให้กลับไปหน้า register
        $showRegister = true;
        echo renderAuthPage($showRegister, $message, $messageType);
        exit;
    }
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
    try {
        $sessionId = $_POST['session_id'];
        $otp = $_POST['otp'];
        
        $result = OTPController::verifyOTPAndRegister($sessionId, $otp);
        
        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';
            // ลบ session OTP
            unset($_SESSION['otp_session_id'], $_SESSION['otp_phone']);
            // กลับไปหน้า login
            $showRegister = false;
            echo renderAuthPage($showRegister, $message, $messageType);
            exit;
        } else {
            $message = $result['message'];
            $messageType = 'error';
            // แสดงหน้า OTP อีกครั้ง
            echo renderOTPVerificationPage($sessionId, $_SESSION['otp_phone'] ?? 'ไม่ระบุ', $message, $messageType);
            exit;
        }
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
        $sessionId = $_POST['session_id'] ?? '';
        $phone = $_SESSION['otp_phone'] ?? 'ไม่ระบุ';
        echo renderOTPVerificationPage($sessionId, $phone, $message, $messageType);
        exit;
    }
}

// Handle OTP resend
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resend_otp') {
    try {
        $sessionId = $_POST['session_id'];
        $result = OTPController::resendOTP($sessionId);
        
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
        
        echo renderOTPVerificationPage($sessionId, $_SESSION['otp_phone'] ?? 'ไม่ระบุ', $message, $messageType);
        exit;
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
        $sessionId = $_POST['session_id'] ?? '';
        $phone = $_SESSION['otp_phone'] ?? 'ไม่ระบุ';
        echo renderOTPVerificationPage($sessionId, $phone, $message, $messageType);
        exit;
    }
}

// Handle normal login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (AuthController::login($username, $password)) {
        $message = 'เข้าสู่ระบบสำเร็จ! ยินดีต้อนรับสู่ prestige88';
        $messageType = 'success';
        $userId = $_SESSION['user_id'];
        $_SESSION['recent_searches'] = loadRecentSearches($userId);
        
        // Check if this is an AJAX request from widget
        if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'เข้าสู่ระบบสำเร็จ! ยินดีต้อนรับสู่ prestige88',
                'redirect' => 'index.php'
            ]);
            exit;
        }
    } else {
        $message = 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง!';
        $messageType = 'error';
        
        // Check if this is an AJAX request from widget
        if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง!'
            ]);
            exit;
        }
    }
}

// Redirect to login if not authenticated
// DEBUG: Log session before login check
// (ลบบรรทัดนี้ออก)
// file_put_contents('storage/logs/debug.log', date('c')." | SESSION: ".json_encode($_SESSION)."\n", FILE_APPEND);
if (!AuthController::isLoggedIn()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
        exit;
    }
    $showRegister = isset($_GET['show']) && $_GET['show'] === 'register';
    echo renderAuthPage($showRegister, $message, $messageType);
    exit;
}

// Handle booking operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'create_booking':
                BookingController::create($_POST);
                $message = 'ส่งคำขอจองเรียบร้อยแล้ว! ทีมงานจะติดต่อกลับภายใน 24 ชั่วโมง';
                $messageType = 'success';
                break;
                
            case 'update_status':
                BookingController::updateStatus($_POST['booking_id'], $_POST['status']);
                $message = 'อัปเดตสถานะการจองเรียบร้อยแล้ว!';
                $messageType = 'success';
                break;
                
            case 'cancel_booking':
                BookingController::cancel($_POST['booking_id']);
                $message = 'ยกเลิกการจองเรียบร้อยแล้ว';
                $messageType = 'success';
                break;
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Get current data
$currentUser = AuthController::getCurrentUser();
$userPoints = calculateUserPoints($currentUser['id']);
$userTier = Membership::getTierByPoints($userPoints);

// --- TIER UPDATE LOGIC ---
if (isset($currentUser['membership_tier']) && $userTier !== $currentUser['membership_tier']) {
    $users = Database::loadUsers();
    $userUpdated = false;
    foreach ($users as &$user) {
        if ($user['id'] === $currentUser['id']) {
            $user['membership_tier'] = $userTier;
            $user['tier_updated_at'] = date('Y-m-d H:i:s');
            $user['points'] = $userPoints; // Also update points
            $userUpdated = true;
            break;
        }
    }
    if ($userUpdated) {
        Database::saveUsers($users);
        // Reload current user to reflect changes in the session and the variable
        $currentUser = AuthController::reloadCurrentUser();
    }
}
// --- END TIER UPDATE LOGIC ---

$nextTierInfo = Membership::getNextTierInfo($userPoints);
$bookings = BookingController::getBookingsForUser($currentUser['role'], $currentUser['id']);
$jets = Database::loadJets();


// Cleanup expired OTPs (เรียกเป็นครั้งคราว)
if (rand(1, 100) <= 5) { // 5% chance
    OTP::cleanupExpiredOTPs();
}

// เพิ่มฟังก์ชันสำรองในกรณีที่ไฟล์ component ไม่พบ
if (!function_exists('renderBookingsList')) {
    function renderBookingsList($bookings, $currentUser) {
        ob_start();
        ?>
        <div class="glass-effect rounded-2xl shadow-xl">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-list-alt mr-3 text-green-600"></i>
                    <?php echo $currentUser['role'] === 'admin' ? 'การจองทั้งหมด' : 'การจองของคุณ'; ?>
                </h2>
            </div>
            <div class="p-6">
                <?php if (empty($bookings)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-calendar-times text-gray-400 text-6xl mb-4"></i>
                    <p class="text-gray-500 text-lg">ยังไม่มีการจอง</p>
                    <p class="text-gray-400">เริ่มต้นการเดินทางสุดหรูของคุณกันเถอะ!</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($bookings as $booking): ?>
                    <div class="border rounded-lg p-4 bg-white hover:shadow-md transition duration-200">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($booking['jet_model'] ?? ''); ?></h3>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                <?php 
                                $status = $booking['status'] ?? '';
                                switch($status) {
                                    case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                    case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                                    case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                    case 'completed': echo 'bg-blue-100 text-blue-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <?php 
                                $statusText = [
                                    'pending' => 'รอดำเนินการ',
                                    'confirmed' => 'ยืนยันแล้ว',
                                    'cancelled' => 'ยกเลิกแล้ว',
                                    'completed' => 'เสร็จสิ้น'
                                ];
                                echo $statusText[$status] ?? $status;
                                ?>
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600 mb-3">
                            <p><i class="fas fa-route mr-2 text-green-600"></i><?php echo htmlspecialchars($booking['departure_location'] ?? ''); ?> → <?php echo htmlspecialchars($booking['arrival_location'] ?? ''); ?></p>
                            <p><i class="fas fa-calendar mr-2 text-blue-600"></i><?php echo isset($booking['departure_date']) ? date('d/m/Y', strtotime($booking['departure_date'])) : 'ไม่ระบุ'; ?></p>
                            <p><i class="fas fa-users mr-2 text-purple-600"></i><?php echo $booking['passengers'] ?? 0; ?> ผู้โดยสาร</p>
                            <p><i class="fas fa-clock mr-2 text-orange-600"></i><?php echo $booking['flight_hours'] ?? 0; ?> ชั่วโมง</p>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="text-green-600 font-semibold">
                                <?php if (isset($booking['base_total']) && ($booking['membership_discount'] ?? 0) > 0): ?>
                                    <span class="text-xs text-gray-500 line-through block"><?php echo formatCurrency($booking['base_total']); ?></span>
                                    <span class="flex items-center">
                                        <?php echo formatCurrency($booking['total_cost'] ?? 0); ?>
                                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">
                                            -<?php echo $booking['membership_discount'] ?? 0; ?>%
                                        </span>
                                    </span>
                                <?php else: ?>
                                    <?php echo formatCurrency($booking['total_cost'] ?? 0); ?>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (($booking['status'] ?? '') === 'pending' || ($booking['status'] ?? '') === 'confirmed'): ?>
                            <div class="flex space-x-2">
                                <?php if ($currentUser['role'] === 'admin' && ($booking['status'] ?? '') === 'pending'): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id'] ?? ''; ?>">
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded-lg" title="อนุมัติ">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id'] ?? ''; ?>">
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg" title="ปฏิเสธ">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="cancel_booking">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id'] ?? ''; ?>">
                                    <button type="submit" class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-xs rounded-lg" 
                                            title="ยกเลิก" onclick="return confirm('ยืนยันการยกเลิก?')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                
                                <?php if ($currentUser['role'] === 'admin'): ?>
                                <a href="?page=edit_booking&booking_id=<?php echo $booking['id'] ?? ''; ?>" 
                                   class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-lg inline-block" 
                                   title="แก้ไขรายละเอียด">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($booking['special_requests'])): ?>
                        <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <i class="fas fa-star mr-2 text-yellow-600"></i>
                                <strong>ความต้องการพิเศษ:</strong> <?php echo htmlspecialchars($booking['special_requests']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

if (!function_exists('renderJetsShowcase')) {
    function renderJetsShowcase($jets) {
        // ฟังก์ชันตรวจสอบ available slots ที่ว่างอยู่จริง
        function getAvailableSlotsCount($jet) {
            if (empty($jet['available_slots'])) {
                return 0;
            }
            
            $bookings = Database::loadBookings();
            $confirmedBookings = array_filter($bookings, function($booking) use ($jet) {
                return $booking['jet_id'] === $jet['id'] && 
                       in_array($booking['status'], ['pending', 'confirmed']);
            });
            
            $availableCount = 0;
            foreach ($jet['available_slots'] as $slot) {
                $isBooked = false;
                foreach ($confirmedBookings as $booking) {
                    if ($booking['departure_date'] === $slot['date'] && 
                        $booking['departure_location'] === $slot['departure']) {
                        $isBooked = true;
                        break;
                    }
                }
                if (!$isBooked) {
                    $availableCount++;
                }
            }
            
            return $availableCount;
        }
        
        // ฟังก์ชันเรียงลำดับ available slots ตามวันที่
        function sortAvailableSlots($slots) {
            if (empty($slots)) {
                return [];
            }
            
            usort($slots, function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });
            
            return $slots;
        }
        
        // ฟังก์ชันกรอง available slots ที่ว่างอยู่จริง
        function getAvailableSlotsForJet($jet) {
            if (empty($jet['available_slots'])) {
                return [];
            }
            
            $bookings = Database::loadBookings();
            $confirmedBookings = array_filter($bookings, function($booking) use ($jet) {
                return $booking['jet_id'] === $jet['id'] && 
                       in_array($booking['status'], ['pending', 'confirmed']);
            });
            
            $availableSlots = [];
            foreach ($jet['available_slots'] as $slot) {
                $isBooked = false;
                foreach ($confirmedBookings as $booking) {
                    if ($booking['departure_date'] === $slot['date'] && 
                        $booking['departure_location'] === $slot['departure']) {
                        $isBooked = true;
                        break;
                    }
                }
                if (!$isBooked) {
                    $availableSlots[] = $slot;
                }
            }
            
            // เรียงลำดับตามวันที่ (วันที่ใกล้สุดอยู่บน)
            return sortAvailableSlots($availableSlots);
        }
        
        ob_start();
        ?>
        <div class="mt-12">
            <div class="glass-effect rounded-2xl shadow-xl p-8">
                <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">
                    <i class="mr-3"></i>
                    คลังเครื่องบินเจทส่วนตัว
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($jets as $jet): ?>
                    <?php 
                    $availableSlotsCount = getAvailableSlotsCount($jet);
                    $isAvailable = $availableSlotsCount > 0;
                    $availableSlots = getAvailableSlotsForJet($jet);
                    ?>
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition duration-300 transform hover:scale-105">
                        <?php if (!empty($jet['image'])): ?>
                        <div class="h-48 bg-gradient-to-br from-blue-400 to-purple-500 relative overflow-hidden">
                            <img src="<?php echo htmlspecialchars($jet['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($jet['model'] ?? ''); ?>"
                                 class="w-full h-full object-cover opacity-80">
                            <div class="absolute top-4 right-4">
                                <?php if ($isAvailable): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i><?php echo $availableSlotsCount; ?> slots available
                                </span>
                                <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>Fully booked
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($jet['model'] ?? ''); ?></h3>
                            
                            <div class="grid grid-cols-2 gap-4 mb-4 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-users mr-2 text-blue-600"></i>
                                    <span><?php echo $jet['capacity'] ?? 0; ?> ที่นั่ง</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-tachometer-alt mr-2 text-red-600"></i>
                                    <span><?php echo number_format($jet['max_speed'] ?? 0); ?> km/h</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-globe mr-2 text-green-600"></i>
                                    <span><?php echo number_format($jet['range_km'] ?? 0); ?> km</span>
                                </div>
                                <div class="flex items-center font-semibold text-purple-600">
                                    <i class="fas fa-baht-sign mr-2"></i>
                                    <span><?php echo number_format($jet['price_per_hour'] ?? 0); ?>/ชม.</span>
                                </div>
                            </div>
                            
                            <?php if (!empty($jet['amenities'])): ?>
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-gem mr-1 text-yellow-600"></i>สิ่งอำนวยความสะดวก:
                                </h4>
                                <div class="flex flex-wrap gap-1">
                                    <?php foreach ($jet['amenities'] as $amenity): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars($amenity); ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($isAvailable && !empty($availableSlots)): ?>
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar mr-1 text-green-600"></i>Available Dates:
                                </h4>
                                <div class="text-xs text-gray-600 space-y-1">
                                    <?php foreach (array_slice($availableSlots, 0, 3) as $slot): ?>
                                    <div class="flex justify-between">
                                        <span><?php echo date('M d, Y', strtotime($slot['date'])); ?></span>
                                        <span class="text-gray-500"><?php echo htmlspecialchars($slot['departure']); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php if (count($availableSlots) > 3): ?>
                                    <div class="text-gray-500 italic">+<?php echo count($availableSlots) - 3; ?> more dates</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($isAvailable): ?>
                            <a href="?page=booking&jet_id=<?php echo $jet['id']; ?>" class="mt-4 block w-full bg-[#a89c8a] hover:bg-[#b3a89c] text-white font-semibold py-3 px-4 rounded-lg text-center transition duration-200">
                                <i class="fas fa-calendar-check mr-2"></i>จองเครื่องบินลำนี้
                            </a>
                            <?php else: ?>
                            <button disabled class="mt-4 block w-full bg-gray-400 text-white font-semibold py-3 px-4 rounded-lg text-center cursor-not-allowed">
                                <i class="fas fa-calendar-times mr-2"></i>Fully Booked
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

require_once 'utils/constants.php';

function getSystemSettings() {
    $settingsFile = __DIR__ . '/data/settings.json';
    if (file_exists($settingsFile)) {
        $data = file_get_contents($settingsFile);
        return json_decode($data, true) ?: [];
    }
    return [];
}

if (!function_exists('renderAdminDashboard')) {
    function renderAdminDashboard($bookings, $currentUser) {
        $totalBookings = count($bookings);
        $pendingBookings = count(array_filter($bookings, function($b) { return ($b['status'] ?? '') === 'pending'; }));
        $confirmedBookings = count(array_filter($bookings, function($b) { return ($b['status'] ?? '') === 'confirmed'; }));
        $totalRevenue = array_sum(array_column($bookings, 'total_cost'));
        $settings = getSystemSettings();
        $currency = $settings['default_currency'] ?? 'THB';
        $revenueDisplay = '';
        $unit = '';
        $icon = '';
        if ($currency === 'USD') {
            $usdRevenue = $totalRevenue / (defined('THB_TO_USD') ? THB_TO_USD : 36);
            $revenueDisplay = formatCurrency($usdRevenue);
            $unit = 'USD';
            $icon = '<i class="fas fa-dollar-sign text-2xl"></i>';
        } else {
            $revenueDisplay = number_format($totalRevenue / 1000000, 1) . 'M';
            $unit = 'บาท';
            $icon = '<i class="fas fa-baht-sign text-2xl"></i>';
        }
        ob_start();
        ?>
        <div class="mt-12">
            <div class="glass-effect rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-chart-bar mr-3 text-blue-600"></i>
                    Dashboard สถิติ
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm opacity-90">การจองทั้งหมด</h3>
                                <p class="text-3xl font-bold"><?php echo $totalBookings; ?></p>
                            </div>
                            <div class="p-3 bg-white/20 rounded-full">
                                <i class="fas fa-calendar-check text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm opacity-90">รอดำเนินการ</h3>
                                <p class="text-3xl font-bold"><?php echo $pendingBookings; ?></p>
                            </div>
                            <div class="p-3 bg-white/20 rounded-full">
                                <i class="fas fa-clock text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm opacity-90">ยืนยันแล้ว</h3>
                                <p class="text-3xl font-bold"><?php echo $confirmedBookings; ?></p>
                            </div>
                            <div class="p-3 bg-white/20 rounded-full">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

if (!function_exists('renderFooter')) {
    function renderFooter($currentUser) {
        ob_start();
        ?>
        <div class="text-center mt-12 text-gray-500">
            <div class="glass-effect rounded-xl p-6">
                <p class="flex items-center justify-center mb-2">
                    <i class="fas fa-plane mr-2"></i>
                    prestige88 Private Jet Booking System | Made with PHP + Tailwind CSS
                </p>
                <p class="text-sm">
                    Current User: <strong><?php echo htmlspecialchars($currentUser['full_name'] ?? 'Guest'); ?></strong> 
                    (<?php echo ucfirst($currentUser['role'] ?? 'guest'); ?>)
                </p>
                <div class="mt-4 text-xs text-gray-400">
                    <p>&copy; <?php echo date('Y'); ?> prestige88. All rights reserved.</p>
                    <p>Version 1.0.0 | Secured with OTP Verification</p>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// --- MOCK DATA สำหรับ UI ใหม่ ---
$popularDestinations = loadPopularDestinations();
// Remove mock recentSearches, use session instead
if (!isset($_SESSION['recent_searches'])) {
    $_SESSION['recent_searches'] = [];
}
$recentSearches = $_SESSION['recent_searches'];

// Load real offers and rewards data
$specialOffers = OffersRewards::getOffersForDisplay();
$availableRewards = OffersRewards::getRewardsForDisplay($userPoints);
$promoCodes = OffersRewards::getPromoCodes(true);

// --- HANDLE SEARCH FORM ---
$searchJets = $jets;
$searching = false;
$searchSummary = '';
if ((isset($_GET['page']) && $_GET['page'] === 'search') || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search_jets')) {
    // Support both GET and POST for search
    $from = trim($_POST['from'] ?? $_GET['from'] ?? '');
    $to = trim($_POST['to'] ?? $_GET['to'] ?? '');
    $date = trim($_POST['date'] ?? $_GET['date'] ?? '');
    $passengers = intval($_POST['passengers'] ?? $_GET['passengers'] ?? 1);
    $searching = true;

    // New flexible search logic based on available_slots
    $searchJets = array_filter($jets, function($jet) use ($from, $to, $date, $passengers) {
        // ต้องกรอกอย่างน้อย from หรือ to
        if (empty($from) && empty($to)) {
            return false;
        }

        if (empty($jet['available_slots'])) {
            return false;
        }

        // ตรวจสอบ available slots ที่ว่างอยู่จริง (ไม่ถูกจองแล้ว)
        $bookings = Database::loadBookings();
        $confirmedBookings = array_filter($bookings, function($booking) use ($jet) {
            return $booking['jet_id'] === $jet['id'] && 
                   in_array($booking['status'], ['pending', 'confirmed']);
        });

        // Check if ANY slot in the jet matches ALL provided criteria and is not booked.
        foreach ($jet['available_slots'] as $slot) {
            // ตรวจสอบว่า slot นี้ถูกจองแล้วหรือไม่
            $isBooked = false;
            foreach ($confirmedBookings as $booking) {
                if ($booking['departure_date'] === $slot['date'] && 
                    $booking['departure_location'] === $slot['departure']) {
                    $isBooked = true;
                    break;
                }
            }
            
            if ($isBooked) {
                continue; // ข้าม slot ที่ถูกจองแล้ว
            }
            
            $dateOK = empty($date) || $slot['date'] === $date;
            $fromOK = empty($from) || stripos($slot['departure'], $from) !== false;
            // ปรับ logic การค้นหาปลายทาง
            $toOK = true;
            if (!empty($to)) {
                if (!empty($slot['arrival'])) {
                    $toOK = stripos($slot['arrival'], $to) !== false;
                } else {
                    $toOK = true;
                }
            }
            if ($dateOK && $fromOK && $toOK) {
                return true;
            }
        }

        return false; // No available slots in this jet matched all criteria.
    });

    $searchSummary = "From <b>".htmlspecialchars($from)."</b>" . (!empty($to) ? " to <b>".htmlspecialchars($to)."</b>" : "") . (!empty($date) ? " on <b>".htmlspecialchars($date)."</b>" : "") . " for <b>".htmlspecialchars($passengers)."</b> passenger(s)";
    
    // Add to recentSearches (เก็บทุกครั้งที่มีการค้นหา)
    if ($from) { // เก็บทุกครั้งที่มีการค้นหา (มีต้นทาง)
        $newSearch = [
            'from' => $from,
            'to' => $to ?: 'Any destination',
            'date' => $date ? date('M d, Y', strtotime($date)) : 'Any date',
            'passengers' => $passengers
        ];
        // Remove duplicate
        $_SESSION['recent_searches'] = array_filter($_SESSION['recent_searches'], function($s) use ($newSearch) {
            return !($s['from'] === $newSearch['from'] && $s['to'] === $newSearch['to'] && $s['date'] === $newSearch['date'] && $s['passengers'] === $newSearch['passengers']);
        });
        array_unshift($_SESSION['recent_searches'], $newSearch);
        if (count($_SESSION['recent_searches']) > 5) array_splice($_SESSION['recent_searches'], 5);
        // Save to file if logged in
        if (isset($_SESSION['user_id'])) {
            saveRecentSearches($_SESSION['user_id'], $_SESSION['recent_searches']);
        }
    }
    $recentSearches = $_SESSION['recent_searches'];
}
// --- END HANDLE SEARCH FORM ---

if (isset($_GET['page']) && $_GET['page'] === 'upcoming-tickets') {
    // Mock seat, ticket, time (ใช้ booking จริง + mock)
    $mockedBookings = array_map(function($b, $i) {
        $b['seat'] = '12A';
        $b['ticket_no'] = $b['id'];
        $b['departure_time'] = $b['departure_time'] ?? '10:30';
        return $b;
    }, $bookings, array_keys($bookings));
    echo renderNavbar($currentUser);
    echo '<div class="container mx-auto px-4 py-12">';
    echo renderUpcomingTickets($mockedBookings, $currentUser);
    echo '</div>';
    echo renderFooter($currentUser);
    exit;
}

if (isset($_GET['page']) && $_GET['page'] === 'my-tickets') {
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>prestige88 - Tickets</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            .gradient-bg {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .glass-effect {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
        </style>
    </head>
    <body class="bg-gray-50">
    <?php
    echo renderNavbar($currentUser);
    echo '<div class="min-h-screen w-full flex justify-center items-start bg-gradient-to-b from-[#2c3744] to-[#363c41] py-12">';
    echo '<div class="w-full max-w-4xl">';
    // --- คืนหัวข้อ Upcoming Tickets และ Filter ---
    ?>
    <div class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Upcoming Tickets</h2>
            <button class="flex items-center gap-2 border border-[#a89c8a] text-[#a89c8a] px-4 py-1 rounded-xl hover:bg-[#a89c8a]/10 transition">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
    </div>
    <?php
    // --- END ---
    echo renderUpcomingTickets($bookings, $currentUser);
    echo renderFooter($currentUser);
    echo '</div></div>';
    echo renderFooterNavbar('tickets');
    ?>
    <script>
    // ... existing JS ...
    </script>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_GET['page']) && $_GET['page'] === 'offers') {
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>prestige88 - Offers & Rewards</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            body { font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        </style>
    </head>
    <body class="bg-gradient-to-b from-[#2c3744] to-[#363c41] min-h-screen">
    <?php echo renderNavbar($currentUser); ?>
    <div class="flex justify-center w-full mt-[120px]">
      <div class="w-full max-w-5xl">
        <div class="mb-12">
          <div class="flex items-center justify-between mb-2">
            <h2 class="text-2xl font-bold text-white">Your Travel Points</h2>
            <div class="flex items-center gap-2 text-white text-2xl font-semibold"><i class="fas fa-star text-[#a89c8a]"></i> <?php echo number_format($userPoints); ?></div>
          </div>
          <div class="bg-white rounded-2xl p-6 flex flex-col gap-2 shadow mb-6">
            <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden mb-2">
              <div class="h-full bg-[#a89c8a] rounded-full" style="width:<?php echo $nextTierInfo['progress_percent']; ?>%"></div>
            </div>
            <div class="flex justify-between text-sm font-semibold">
              <span>Current Level<br><span class="text-lg text-black"><?php echo ucfirst($userTier); ?></span></span>
              <span class="text-right">Next Level<br><span class="text-lg text-black"><?php echo $nextTierInfo['next_tier']; ?> <span class="font-normal"><?php if($nextTierInfo['next_tier']){ ?>(<?php echo number_format($nextTierInfo['points_to_next']); ?> points to go)<?php } ?></span></span></span>
            </div>
          </div>
        </div>
        <div class="mb-12">
          <h3 class="text-xl font-bold text-white mb-4">Available Offers (<?php echo count($specialOffers); ?> offers)</h3>
          <div class="flex flex-col gap-6">
            <?php if (empty($specialOffers)): ?>
              <div class="text-center text-white py-8">
                <p>No offers available at the moment.</p>
              </div>
            <?php else: ?>
            <?php foreach ($specialOffers as $offer): ?>
            <div class="flex rounded-2xl overflow-hidden bg-white shadow items-center">
              <div class="w-24 h-full flex items-center justify-center bg-[#a89c8a]/90"><i class="<?php echo $offer['icon']; ?> text-3xl text-white"></i></div>
              <div class="flex-1 p-6">
                <div class="font-bold text-lg"><?php echo htmlspecialchars($offer['title']); ?></div>
                <div class="text-sm text-gray-700"><?php echo htmlspecialchars($offer['desc']); ?></div>
                <div class="text-xs text-red-500 mt-1 flex items-center gap-1">
                  <i class="fas fa-clock"></i> <?php echo htmlspecialchars($offer['note']); ?>
                </div>
              </div>
              <div class="flex items-center p-6"><button class="bg-[#a89c8a]/90 text-white px-6 py-2 rounded-xl font-medium">Use Now</button></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="mb-12">
          <h3 class="text-xl font-bold text-white mb-4">Rewards You Can Unlock (<?php echo count($availableRewards); ?> rewards)</h3>
          <div class="flex flex-col gap-6">
            <?php if (empty($availableRewards)): ?>
              <div class="text-center text-white py-8">
                <p>No rewards available at the moment.</p>
              </div>
            <?php else: ?>
            <?php foreach ($availableRewards as $reward): ?>
            <div class="flex rounded-2xl overflow-hidden bg-white shadow items-center">
              <div class="w-24 h-full flex items-center justify-center bg-[#a89c8a]/90"><i class="fas <?php echo $reward['icon']; ?> text-3xl text-white"></i></div>
              <div class="flex-1 p-6">
                <div class="font-bold text-lg"><?php echo $reward['title']; ?>
                  <?php if ($reward['is_unlocked']): ?><span class="ml-2 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">Unlocked</span><?php endif; ?>
                </div>
                <div class="text-sm text-gray-700"><?php echo $reward['desc']; ?></div>
                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden mt-3">
                  <div class="h-full bg-[#a89c8a] rounded-full transition-all duration-500" style="width:<?php echo $reward['progress']; ?>%"></div>
                </div>
                <?php if (!$reward['is_unlocked']): ?>
                  <div class="text-xs text-gray-500 mt-1">Need <?php echo number_format(max(0, $reward['points'] - $userPoints)); ?> more points</div>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="mb-12">
          <h3 class="text-xl font-bold text-white mb-4">Have a Promo Code?</h3>
          <form id="promo-form" class="flex gap-4">
            <input type="text" name="promo_code" placeholder="Enter Promo Code" class="flex-1 px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#a89c8a] focus:border-[#a89c8a] bg-white text-gray-800" />
            <button type="submit" class="bg-[#a89c8a]/90 text-white px-8 py-2 rounded-xl font-medium">Apply</button>
          </form>
          <div id="promo-result" class="mt-4 hidden"></div>
        </div>
      </div>
    </div>
    <script>
    // Promo code form handling
    document.getElementById('promo-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('action', 'validate_promo');
        formData.append('code', this.querySelector('input[name="promo_code"]').value);
        
        const resultDiv = document.getElementById('promo-result');
        resultDiv.innerHTML = '<div class="text-center text-gray-400">Validating...</div>';
        resultDiv.classList.remove('hidden');
        
        fetch('', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            <div class="font-bold">${data.message}</div>
                            <div class="text-sm">${data.promo.title} - ${data.promo.description}</div>
                            <div class="text-sm font-semibold">Discount: ${data.promo.discount_type === 'percentage' ? data.promo.discount_value + '%' : '$' + data.promo.discount_value}</div>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        An error occurred. Please try again.
                    </div>
                `;
            });
    });
    </script>
    <?php echo renderFooter($currentUser); ?>
    <?php echo renderFooterNavbar('offers'); ?>
    </body></html>
    <?php
    exit;
}

if (isset($_GET['page']) && $_GET['page'] === 'profile') {
    $stats = getTravelStats($currentUser['id']);
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>prestige88 - Profile</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            body { font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        </style>
    </head>
    <body class="bg-gradient-to-b from-[#2c3744] to-[#363c41] min-h-screen">
    <?php echo renderNavbar($currentUser); ?>
    <div class="flex justify-center w-full mt-[120px]">
      <div class="w-full max-w-5xl">
        <div class="flex flex-col items-center mb-12">
          <div class="w-36 h-36 rounded-full bg-[#a89c8a] flex items-center justify-center text-white text-6xl relative mb-4">
            <i class="fas fa-user"></i>
            <button id="edit-avatar-btn" class="absolute bottom-2 right-2 bg-white text-[#a89c8a] rounded-full w-8 h-8 flex items-center justify-center shadow hover:bg-[#f5f5f5] border border-[#a89c8a]/30"><i class="fas fa-pen text-base"></i></button>
          </div>
          <div class="text-3xl font-bold text-white mb-1" id="profile-fullname"><?php echo htmlspecialchars($currentUser['full_name'] ?? 'Alex Johnson'); ?></div>
          <div class="text-lg text-[#e5e5e5] mb-2">Premium Member</div>
          <div class="flex items-center gap-2 text-white text-xl font-semibold mb-2"><i class="fas fa-star text-[#a89c8a]"></i> <?php echo number_format($userPoints); ?> Points <span class="bg-[#a89c8a]/90 text-xs px-2 py-1 rounded ml-2"><?php echo ucfirst($userTier); ?></span></div>
        </div>
        <div class="bg-white rounded-2xl p-6 mb-12 shadow">
          <div class="font-semibold mb-2 flex items-center justify-between">
            <span>Membership Progress</span>
          </div>
          <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden mb-2">
            <div class="h-full bg-[#a89c8a] rounded-full" style="width:<?php echo $nextTierInfo['progress_percent']; ?>%"></div>
          </div>
          <div class="flex justify-between text-sm font-semibold">
            <span><?php echo ucfirst($userTier); ?><br><span class="text-lg text-black"><?php echo number_format($userPoints); ?> pts</span></span>
            <span class="text-center"><?php if($nextTierInfo['next_tier']){ echo number_format($nextTierInfo['points_to_next']).' pts to '.$nextTierInfo['next_tier']; } ?><br><span class="text-lg text-black"><?php echo $nextTierInfo['progress_percent']; ?>%</span></span>
            <span class="text-right"><?php echo $nextTierInfo['next_tier']; ?><br><span class="text-lg text-black"><?php echo number_format($nextTierInfo['next_tier_points']); ?> pts</span></span>
          </div>
        </div>
        <div class="mb-12">
          <h3 class="text-xl font-bold text-white mb-4">Personal Information</h3>
          <div class="bg-white rounded-2xl p-6 shadow mb-6">
            <div class="flex items-center justify-between mb-4">
              <div class="font-semibold">Personal Details</div>
              <button id="edit-personal-btn" class="bg-[#a89c8a]/90 text-white px-4 py-1 rounded-xl font-medium">Edit</button>
            </div>
            <div class="w-full">
              <div class="flex flex-col gap-2">
                <div class="flex justify-between items-center">
                  <span class="font-semibold">Full Name</span>
                  <span id="profile-fullname-detail" class="text-right"><?php echo htmlspecialchars($currentUser['full_name'] ?? 'Alex Johnson'); ?></span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="font-semibold">Email</span>
                  <span id="profile-email-detail" class="text-right"><?php echo htmlspecialchars($currentUser['email'] ?? '[email protected]'); ?></span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="font-semibold">Phone</span>
                  <span id="profile-phone-detail" class="text-right"><?php echo htmlspecialchars($currentUser['phone'] ?? '+1 (555) 123-4567'); ?></span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="font-semibold">Date of Birth</span>
                  <span id="profile-dob-detail" class="text-right"><?php echo htmlspecialchars($currentUser['dob'] ?? '-'); ?></span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="font-semibold">Address</span>
                  <span id="profile-address-detail" class="text-right"><?php echo htmlspecialchars($currentUser['address'] ?? '-'); ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="mb-12">
          <h3 class="text-xl font-bold text-white mb-4">Travel Stats</h3>
          <div class="bg-white rounded-2xl px-12 py-4 shadow flex justify-between text-center">
            <div>
              <div class="text-3xl font-bold text-[#a89c8a]"><?php echo $stats['trips']; ?></div>
              <div class="text-gray-700">Total Trips</div>
            </div>
            <div>
              <div class="text-3xl font-bold text-[#a89c8a]"><?php echo number_format($stats['miles']); ?></div>
              <div class="text-gray-700">Miles</div>
            </div>
            <div>
              <div class="text-3xl font-bold text-[#a89c8a]">$<?php echo number_format($stats['spent']); ?></div>
              <div class="text-gray-700">Spent</div>
            </div>
          </div>
        </div>
        <div class="mb-12">
          <h3 class="text-xl font-bold text-white mb-4">Preferences</h3>
          <div class="flex flex-col gap-4">
            <div class="flex items-center bg-white rounded-2xl p-4 shadow justify-between">
              <div class="flex items-center gap-4"><span class="bg-[#a89c8a]/90 text-white rounded-xl p-3"><i class="fas fa-language text-2xl"></i></span><div><div class="font-semibold">Language</div><div class="text-gray-700 text-sm">English (US)</div></div></div>
              <i class="fas fa-chevron-right text-[#a89c8a]"></i>
            </div>
            <div class="flex items-center bg-white rounded-2xl p-4 shadow justify-between">
              <div class="flex items-center gap-4"><span class="bg-[#a89c8a]/90 text-white rounded-xl p-3"><i class="fas fa-dollar-sign text-2xl"></i></span><div><div class="font-semibold">Currency</div><div class="text-gray-700 text-sm">USD ($)</div></div></div>
              <i class="fas fa-chevron-right text-[#a89c8a]"></i>
            </div>
            <div class="flex items-center bg-white rounded-2xl p-4 shadow justify-between">
              <div class="flex items-center gap-4"><span class="bg-[#a89c8a]/90 text-white rounded-xl p-3"><i class="fas fa-bell text-2xl"></i></span><div><div class="font-semibold">Notifications</div><div class="text-gray-700 text-sm">Manage notification preferences</div></div></div>
              <i class="fas fa-chevron-right text-[#a89c8a]"></i>
            </div>
            <div class="flex items-center bg-white rounded-2xl p-4 shadow justify-between">
              <div class="flex items-center gap-4"><span class="bg-[#a89c8a]/90 text-white rounded-xl p-3"><i class="fas fa-shield-alt text-2xl"></i></span><div><div class="font-semibold">Privacy Settings</div><div class="text-gray-700 text-sm">Manage your privacy preferences</div></div></div>
              <i class="fas fa-chevron-right text-[#a89c8a]"></i>
            </div>
          </div>
        </div>
        <div class="mb-12">
          <h3 class="text-xl font-bold text-white mb-4">Account Actions</h3>
          <div class="flex flex-col gap-4">
            <button class="bg-[#a89c8a]/90 text-white px-6 py-3 rounded-xl font-medium">Switch to Business Account</button>
            <button class="bg-[#a89c8a]/90 text-white px-6 py-3 rounded-xl font-medium">Download My Data</button>
            <button class="bg-[#a89c8a]/90 text-white px-6 py-3 rounded-xl font-medium">Sign Out</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Edit Personal Details Modal -->
    <div id="edit-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
      <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md relative">
        <button onclick="closeEditModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
        <h3 class="text-lg font-semibold mb-4">Edit Personal Details</h3>
        <form id="edit-personal-form" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Full Name</label>
            <input type="text" name="full_name" id="edit-fullname" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#a89c8a]" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">E-mail</label>
            <input type="email" name="email" id="edit-email" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#a89c8a]" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Phone</label>
            <div class="flex">
              <select class="rounded-l-lg border border-gray-300 bg-gray-50 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-[#a89c8a]">
                <option>+1</option><option>+66</option><option>+44</option><option>+81</option>
              </select>
              <input type="text" name="phone" id="edit-phone" class="w-full rounded-r-lg border-t border-b border-r border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#a89c8a]" required />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Date of Birth</label>
            <input type="date" name="dob" id="edit-dob" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#a89c8a]" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Address</label>
            <textarea name="address" id="edit-address" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#a89c8a] bg-gray-50"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Current Password (to confirm changes)</label>
            <input type="password" name="current_password" id="edit-password" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#a89c8a]" placeholder="Enter your password" required />
          </div>
          <button type="submit" class="w-full bg-[#a89c8a] hover:bg-[#b3a89c] text-white font-semibold py-3 rounded-xl mt-2">Save Change</button>
        </form>
      </div>
    </div>
    <script>
    // Modal logic
    function openEditModal() {
      document.getElementById('edit-modal').classList.remove('hidden');
      document.getElementById('edit-fullname').value = document.getElementById('profile-fullname-detail').innerText;
      document.getElementById('edit-email').value = document.getElementById('profile-email-detail').innerText;
      // Set phone value
      var phone = document.getElementById('profile-phone-detail').innerText.trim();
      var select = document.querySelector('#edit-modal select');
      var phoneInput = document.getElementById('edit-phone');
      var countryCode = '+66';
      var phoneNumber = phone;
      var match = phone.match(/^\+(\d{1,3})\s*(\d{6,})$/);
      if (match) {
        countryCode = '+' + match[1];
        phoneNumber = match[2];
      } else if (phone.length === 10 && phone.startsWith('0')) {
        countryCode = '+66';
        phoneNumber = phone;
      }
      if (select) select.value = countryCode;
      if (phoneInput) phoneInput.value = phoneNumber;
      document.getElementById('edit-dob').value = document.getElementById('profile-dob-detail').innerText;
      document.getElementById('edit-address').value = document.getElementById('profile-address-detail').innerText;
    }
    function closeEditModal() {
      document.getElementById('edit-modal').classList.add('hidden');
    }
    document.getElementById('edit-avatar-btn').onclick = openEditModal;
    document.getElementById('edit-personal-btn').onclick = openEditModal;
    // Form submit logic (real backend)
    document.getElementById('edit-personal-form').onsubmit = async function(e) {
      e.preventDefault();
      var form = e.target;
      var formData = new FormData(form);
      // Combine country code and phone
      var countryCode = form.querySelector('select').value;
      var phone = form.querySelector('input[name="phone"]').value.trim();
      if (countryCode === '+66' && phone.startsWith('0')) {
        phone = phone.substring(1);
      }
      var fullPhone = countryCode + phone;
      formData.set('phone', fullPhone);
      formData.append('action', 'update_profile');
      const res = await fetch('', { method: 'POST', body: formData });
      const data = await res.json();
      if (data.success) {
        document.getElementById('profile-fullname').innerText = data.user.full_name;
        document.getElementById('profile-fullname-detail').innerText = data.user.full_name;
        document.getElementById('profile-email-detail').innerText = data.user.email;
        document.getElementById('profile-phone-detail').innerText = data.user.phone;
        document.getElementById('profile-address-detail').innerText = data.user.address;
        if (data.user.dob) document.getElementById('profile-dob-detail').innerText = data.user.dob;
        closeEditModal();
        alert('Profile updated!');
      } else if (data.otp_required) {
        window.location.href = `?action=verify_otp_profile&session_id=${encodeURIComponent(data.session_id)}&phone=${encodeURIComponent(data.phone)}`;
      } else {
        alert(data.message || 'Update failed');
      }
    };
    </script>
    <?php echo renderFooter($currentUser); ?>
    <?php echo renderFooterNavbar('profile'); ?>

    <!-- Preferences Modal -->
    <div id="preferences-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
      <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md relative">
        <button onclick="closePreferencesModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
        <h3 class="text-lg font-semibold mb-4">Preferences</h3>
        <form id="preferences-form" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Language</label>
            <select name="language" id="pref-language" class="w-full rounded-lg border border-gray-300 px-3 py-2">
              <option value="en">English (US)</option>
              <option value="th">ไทย (TH)</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Currency</label>
            <select name="currency" id="pref-currency" class="w-full rounded-lg border border-gray-300 px-3 py-2">
              <option value="USD">USD ($)</option>
              <option value="THB">THB (฿)</option>
            </select>
          </div>
          <button type="submit" class="w-full bg-[#a89c8a] hover:bg-[#b3a89c] text-white font-semibold py-3 rounded-xl mt-2">Save Preferences</button>
        </form>
      </div>
    </div>
    <script>
    // Preferences Modal logic
    function openPreferencesModal() {
      document.getElementById('preferences-modal').classList.remove('hidden');
    }
    function closePreferencesModal() {
      document.getElementById('preferences-modal').classList.add('hidden');
    }
    // Preferences click events
    const prefItems = document.querySelectorAll('.flex.items-center.bg-white.rounded-2xl.p-4.shadow.justify-between');
    if (prefItems.length > 0) {
      prefItems[0].onclick = openPreferencesModal; // Language
      prefItems[1].onclick = openPreferencesModal; // Currency
    }
    // Preferences form submit
    const prefForm = document.getElementById('preferences-form');
    if (prefForm) {
      prefForm.onsubmit = async function(e) {
        e.preventDefault();
        const formData = new FormData(prefForm);
        formData.append('action', 'update_preferences');
        const res = await fetch('', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
          closePreferencesModal();
          alert('Preferences updated!');
          location.reload();
        } else {
          alert(data.message || 'Update failed');
        }
      };
    }
    // Account Actions
    const accountBtns = document.querySelectorAll('.mb-12:last-of-type button');
    if (accountBtns.length > 0) {
      // Switch to Business Account
      accountBtns[0].onclick = async function() {
        if (!confirm('Switch to Business Account?')) return;
        const formData = new FormData();
        formData.append('action', 'switch_business_account');
        const res = await fetch('', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
          alert('Switched to Business Account!');
          location.reload();
        } else {
          alert(data.message || 'Failed to switch');
        }
      };
      // Download My Data (PDF Statement)
      accountBtns[1].onclick = function() {
        // Create a form for POST request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        form.target = '_blank'; // Open in new tab
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'download_my_data';
        form.appendChild(actionInput);
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
      };
      // Sign Out
      accountBtns[2].onclick = function() {
        if (!confirm('Sign out?')) return;
        window.location.href = '?logout=1';
      };
    }
    </script>
    </body>
    </html>
    <?php
    exit;
}

// ฟังก์ชันคำนวณ Travel Stats จริง
// (getTravelStats() ถูกย้ายไป utils/functions.php แล้ว)
// ... existing code ...

if (!function_exists('renderUpcomingTickets')) {
    function renderUpcomingTickets($bookings, $currentUser) {
        ob_start();
        ?>
        <div class="space-y-8" id="tickets-container">
            <?php if (empty($bookings)): ?>
                <div class="text-center py-16 text-white">
                    <i class="fas fa-ticket-alt text-6xl opacity-50 mb-4"></i>
                    <p class="text-xl">You have no upcoming tickets.</p>
                </div>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <div class="bg-white/10 rounded-2xl p-6 text-white backdrop-blur-sm ticket-card" data-booking-id="<?php echo htmlspecialchars($booking['id']); ?>">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-2xl font-bold tracking-wider">
                                    <?php echo htmlspecialchars($booking['departure_location']); ?>
                                    <i class="fas fa-plane mx-4"></i>
                                    <?php echo htmlspecialchars($booking['arrival_location']); ?>
                                </div>
                                <div class="text-sm opacity-80 mt-2">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    <?php echo date('D, M j, Y', strtotime($booking['departure_date'])); ?>
                                </div>
                                <div class="text-sm opacity-80">
                                    Ticket #<?php echo htmlspecialchars($booking['id']); ?>
                                </div>
                            </div>
                            <div class="text-center">
                                <span class="inline-block px-4 py-1 bg-green-500/80 rounded-full text-xs font-semibold uppercase tracking-wider">
                                    <?php echo htmlspecialchars($booking['status']); ?>
                                </span>
                                <div class="mt-2 text-lg font-semibold"><?php echo date('h:i A', strtotime($booking['departure_time'])); ?></div>
                                <div class="text-sm opacity-80">Seat <?php echo $booking['seat'] ?? '12A'; ?></div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-white/20 flex justify-end gap-4">
                             <button class="show-ticket-btn bg-white/20 hover:bg-white/30 px-6 py-2 rounded-lg text-sm font-medium transition-colors">Show Ticket</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Ticket Modal -->
        <div id="ticket-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md relative text-gray-800">
                <button onclick="closeTicketModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                <h3 class="text-2xl font-bold mb-6 text-center">Ticket Details</h3>

                <div id="ticket-modal-content">
                    <!-- Dynamic content will be injected here -->
                </div>

                <div id="ticket-modal-form" class="hidden">
                    <form id="edit-ticket-form" class="space-y-4">
                        <!-- Dynamic form fields will be injected here -->
                    </form>
                </div>
            </div>
        </div>

        <script>
            const bookingsData = <?php echo json_encode(array_values($bookings)); ?>;
            const currentUserRole = '<?php echo $currentUser['role']; ?>';

            function closeTicketModal() {
                document.getElementById('ticket-modal').classList.add('hidden');
            }

            function showTicketModal(bookingId) {
                const booking = bookingsData.find(b => b.id === bookingId);
                if (!booking) return;

                const modalContent = document.getElementById('ticket-modal-content');
                const modalFormContainer = document.getElementById('ticket-modal-form');
                
                // Show content, hide form
                modalContent.classList.remove('hidden');
                modalFormContainer.classList.add('hidden');

                modalContent.innerHTML = `
                    <div class="text-center mb-6">
                        <div class="w-52 h-52 bg-[#a89c8a] flex items-center justify-center rounded-md mb-4 text-center text-black text-lg relative">
                            ${booking.qr_code ? `<img id='qr-img' src="${booking.qr_code}" alt="QR CODE" style="width:200px;height:200px;object-fit:contain;border-radius:12px;background:#fff;" onerror="this.style.display='none'; document.getElementById('qr-fallback').style.display='flex';" />
                            <div id='qr-fallback' style='display:none; position:absolute; inset:0;' class='w-full h-full items-center justify-center flex flex-col'><i class='fas fa-qrcode text-6xl text-gray-400'></i><div class='text-xs text-gray-700 mt-2'>QR CODE NOT FOUND</div></div>`
                            : `<div class='flex flex-col items-center justify-center w-full h-full'><i class='fas fa-qrcode text-6xl text-gray-400'></i><div class='text-xs text-gray-700 mt-2'>No QR CODE</div></div>`}
                        </div>
                    </div>
                    <div class="space-y-3 text-sm">
                        <p><strong>Passenger:</strong> <span class="detail-passenger">${booking.user_name}</span></p>
                        <p><strong>Ticket Number:</strong> <span class="detail-id">${booking.id}</span></p>
                        <p><strong>Date:</strong> <span class="detail-date">${new Date(booking.departure_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</span></p>
                        <p><strong>Departure:</strong> <span class="detail-departure">${booking.departure_location}</span></p>
                        <p><strong>Arrival:</strong> <span class="detail-arrival">${booking.arrival_location}</span></p>
                        <p><strong>Boarding Time:</strong> <span class="detail-time">${new Date('1970-01-01T' + booking.departure_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</span></p>
                        <p><strong>Seat:</strong> <span class="detail-seat">${booking.seat || '-'}</span></p>
                        <p><strong>Bus Number:</strong> <span class="detail-bus">${booking.bus_number || '-'}</span></p>
                        <p><strong>Boarding Gate:</strong> <span class="detail-gate">${booking.boarding_gate || '-'}</span></p>
                        <p><strong>Special Requests:</strong> <span class="detail-requests">${booking.special_requests || '-'}</span></p>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        ${currentUserRole === 'admin' ? `<button onclick=\"editTicket('${booking.id}')\" class=\"px-4 py-2 bg-blue-600 text-white rounded-lg text-sm\">Edit</button>` : ''}
                        <button onclick="closeTicketModal()" class="px-4 py-2 bg-gray-200 rounded-lg text-sm">Close</button>
                    </div>
                `;
                // Debug log
                if (booking.qr_code) {
                    console.log('QR CODE path:', booking.qr_code);
                }

                document.getElementById('ticket-modal').classList.remove('hidden');
            }

            function editTicket(bookingId) {
                const booking = bookingsData.find(b => b.id === bookingId);
                if (!booking) return;

                const modalContent = document.getElementById('ticket-modal-content');
                const modalFormContainer = document.getElementById('ticket-modal-form');
                const form = modalFormContainer.querySelector('form');

                form.innerHTML = `
                    <input type="hidden" name="action" value="update_booking">
                    <input type="hidden" name="booking_id" value="${booking.id}">
                     <div>
                        <label class="block text-sm font-medium mb-1">Passenger</label>
                        <input type="text" name="user_name" class="w-full rounded-lg border-gray-300" value="${booking.user_name}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Departure Location</label>
                        <input type="text" name="departure_location" class="w-full rounded-lg border-gray-300" value="${booking.departure_location}">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Date</label>
                            <input type="date" name="departure_date" class="w-full rounded-lg border-gray-300" value="${booking.departure_date}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Time</label>
                            <input type="time" name="departure_time" class="w-full rounded-lg border-gray-300" value="${booking.departure_time}">
                        </div>
                    </div>
                     <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Passengers</label>
                            <input type="number" name="passengers" class="w-full rounded-lg border-gray-300" value="${booking.passengers}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Flight Hours</label>
                            <input type="number" name="flight_hours" class="w-full rounded-lg border-gray-300" value="${booking.flight_hours}">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Seat</label>
                            <input type="text" name="seat" class="w-full rounded-lg border-gray-300" value="${booking.seat || ''}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Bus Number</label>
                            <input type="text" name="bus_number" class="w-full rounded-lg border-gray-300" value="${booking.bus_number || ''}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Boarding Gate</label>
                            <input type="text" name="boarding_gate" class="w-full rounded-lg border-gray-300" value="${booking.boarding_gate || ''}">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Status</label>
                        <select name="status" class="w-full rounded-lg border-gray-300">
                            <option value="pending" ${booking.status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="confirmed" ${booking.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                            <option value="cancelled" ${booking.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            <option value="completed" ${booking.status === 'completed' ? 'selected' : ''}>Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Special Requests</label>
                        <textarea name="special_requests" rows="2" class="w-full rounded-lg border-gray-300">${booking.special_requests || ''}</textarea>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">Save Changes</button>
                        <button type="button" onclick="showTicketModal('${booking.id}')" class="px-4 py-2 bg-gray-200 rounded-lg text-sm">Cancel</button>
                    </div>
                `;

                modalContent.classList.add('hidden');
                modalFormContainer.classList.remove('hidden');
            }

            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.show-ticket-btn').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const ticketCard = e.target.closest('.ticket-card');
                        const bookingId = ticketCard.dataset.bookingId;
                        showTicketModal(bookingId);
                    });
                });

                document.getElementById('edit-ticket-form').addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const form = e.target;
                    const formData = new FormData(form);
                    const bookingId = formData.get('booking_id');

                    try {
                        const response = await fetch('', { method: 'POST', body: formData });
                        const result = await response.json();
                        
                        if (result.success) {
                            alert('Booking updated successfully!');
                            const index = bookingsData.findIndex(b => b.id === bookingId);
                            if (index !== -1) {
                                // Create a new object for the updated booking
                                const updatedBooking = { ...bookingsData[index], ...result.booking };
                                // Replace the old booking data
                                bookingsData[index] = updatedBooking;
                            }
                            // Refresh the modal with new data
                            showTicketModal(bookingId);
                            window.location.reload(); 
                        } else {
                            alert('Error: ' + (result.message || 'Update failed'));
                        }
                    } catch (error) {
                        alert('An error occurred. Please try again.');
                        console.error('Submit Error:', error);
                    }
                });
            });

        </script>
        <?php
        return ob_get_clean();
    }
}

if (isset($_GET['page']) && $_GET['page'] === 'search') {
    // If no search params, show all jets
    $hasSearch = isset($_GET['from']) || isset($_GET['to']) || isset($_GET['date']) || isset($_GET['passengers']) || isset($_GET['round_trip']);
    if (!$hasSearch) {
        $searching = true;
        $searchJets = $jets;
        $searchSummary = 'All available jets';
    }
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>prestige88 - Search Jets</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            body { font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        </style>
    </head>
    <body class="bg-gradient-to-b from-[#2c3744] to-[#363c41] min-h-screen">
    <?php echo renderNavbar($currentUser); ?>
    <div class="flex justify-center w-full mt-[120px]">
      <div class="w-full max-w-5xl">
        <!-- Messages -->
        <?php echo renderMessage($message, $messageType); ?>
        <!-- Find Your Private Jet Search Form (บนสุด) -->
        <div class="flex justify-center mb-12 mt-12">
            <form method="GET" action="" class="w-full max-w-4xl bg-white rounded-3xl shadow-xl px-10 py-10 flex flex-col gap-6" style="font-family: 'Inter', sans-serif;">
                <input type="hidden" name="page" value="search">
                <h2 class="text-2xl font-semibold text-[#b3a89c] mb-2 tracking-wide" style="letter-spacing:0.5px;">Find Your Private Jet</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">From</label>
                        <input list="from-list" type="text" name="from" placeholder="Ex Bangkok, Dubai, NYC" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#b3a89c] focus:border-[#b3a89c]" value="<?php echo htmlspecialchars($_GET['from'] ?? ''); ?>">
                        <datalist id="from-list">
                            <option value="Bangkok"><option value="Phuket"><option value="New York"><option value="London"><option value="Dubai"><option value="Muscat"><option value="NYC"><option value="DC">
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">To</label>
                        <input list="to-list" type="text" name="to" placeholder="Ex Phuket, London, Muscat (Optional)" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#b3a89c] focus:border-[#b3a89c]" value="<?php echo htmlspecialchars($_GET['to'] ?? ''); ?>">
                        <datalist id="to-list">
                            <option value="Bangkok"><option value="Phuket"><option value="New York"><option value="London"><option value="Dubai"><option value="Muscat"><option value="NYC"><option value="DC">
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Date</label>
                        <input type="date" name="date" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#b3a89c] focus:border-[#b3a89c]" value="<?php echo htmlspecialchars($_GET['date'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Passengers</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b3a89c] text-lg pointer-events-none"><i class="fas fa-user"></i></span>
                            <input type="number" name="passengers" min="1" max="20" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#b3a89c] focus:border-[#b3a89c] placeholder-[#b3a89c]" value="<?php echo htmlspecialchars($_GET['passengers'] ?? '1'); ?>" placeholder="1">
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" name="round_trip" id="round_trip" class="accent-[#b3a89c]" <?php if (!empty($_GET['round_trip'])) echo 'checked'; ?>>
                    <label for="round_trip" class="text-gray-700 text-sm">Round Trip</label>
                </div>
                <button type="submit" class="w-full bg-[#a89c8a] hover:bg-[#b3a89c] text-white font-semibold py-4 px-6 rounded-xl shadow transition duration-200 text-lg tracking-wide" style="box-shadow:0 2px 8px #b3a89c30;">Search Jets</button>
            </form>
        </div>
        <!-- Search Result (Jets Showcase) -->
        <?php if ($searching): ?>
            <div id="search-result"></div>
            <div class="mb-4 text-center text-lg text-gray-700">
                <span>Showing results for: <?php echo $searchSummary; ?></span>
                <span class="ml-4 text-sm text-gray-500">(Found <?php echo count($searchJets); ?> jets)</span>
            </div>
            <?php if (count($searchJets) === 0): ?>
                <div class="text-center text-red-500 text-lg mb-8">ไม่พบเครื่องบินที่ตรงกับเงื่อนไข</div>
            <?php else: ?>
                <?php echo renderJetsShowcase($searchJets); ?>
            <?php endif; ?>
            <script>
            setTimeout(function(){
                var el = document.getElementById('search-result');
                if (el) el.scrollIntoView({behavior: 'smooth'});
            }, 200);
            </script>
        <?php endif; ?>
        <?php echo renderFooter($currentUser); ?>
      </div>
    </div>
    <?php echo renderFooterNavbar('search'); ?>
    </body></html>
    <?php
    exit;
}

if (!isset($_GET['page']) || $_GET['page'] === 'home') {
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>prestige88 - Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation Bar -->
    <?php echo renderNavbar($currentUser); ?>

    <div class="flex justify-center w-full mt-[120px]">
      <div class="w-full max-w-5xl">
        <!-- Messages -->
        <?php echo renderMessage($message, $messageType); ?>

        <!-- Find Your Private Jet Search Form (บนสุด) -->
        <div class="flex justify-center mb-12 mt-12">
            <form method="GET" action="" class="w-full max-w-4xl bg-white rounded-3xl shadow-xl px-10 py-10 flex flex-col gap-6" style="font-family: 'Inter', sans-serif;">
                <input type="hidden" name="page" value="search">
                <h2 class="text-2xl font-semibold text-[#b3a89c] mb-2 tracking-wide" style="letter-spacing:0.5px;">Find Your Private Jet</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">From</label>
                        <input list="from-list" type="text" name="from" placeholder="Ex Bangkok, Dubai, NYC" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#b3a89c] focus:border-[#b3a89c]" value="<?php echo htmlspecialchars($_GET['from'] ?? ''); ?>">
                        <datalist id="from-list">
                            <option value="Bangkok"><option value="Phuket"><option value="New York"><option value="London"><option value="Dubai"><option value="Muscat"><option value="NYC"><option value="DC">
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">To</label>
                        <input list="to-list" type="text" name="to" placeholder="Ex Phuket, London, Muscat (Optional)" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#b3a89c] focus:border-[#b3a89c]" value="<?php echo htmlspecialchars($_GET['to'] ?? ''); ?>">
                        <datalist id="to-list">
                            <option value="Bangkok"><option value="Phuket"><option value="New York"><option value="London"><option value="Dubai"><option value="Muscat"><option value="NYC"><option value="DC">
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Date</label>
                        <input type="date" name="date" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#b3a89c] focus:border-[#b3a89c]" value="<?php echo htmlspecialchars($_GET['date'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Passengers</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b3a89c] text-lg pointer-events-none"><i class="fas fa-user"></i></span>
                            <input type="number" name="passengers" min="1" max="20" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#b3a89c] focus:border-[#b3a89c] placeholder-[#b3a89c]" value="<?php echo htmlspecialchars($_GET['passengers'] ?? '1'); ?>" placeholder="1">
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" name="round_trip" id="round_trip" class="accent-[#b3a89c]" <?php if (!empty($_GET['round_trip'])) echo 'checked'; ?>>
                    <label for="round_trip" class="text-gray-700 text-sm">Round Trip</label>
                </div>
                <button type="submit" class="w-full bg-[#a89c8a] hover:bg-[#b3a89c] text-white font-semibold py-4 px-6 rounded-xl shadow transition duration-200 text-lg tracking-wide" style="box-shadow:0 2px 8px #b3a89c30;">Search Jets</button>
            </form>
        </div>

        <!-- Search Result (Jets Showcase) -->
        <?php if ($searching): ?>
            <div id="search-result"></div>
            <div class="mb-4 text-center text-lg text-gray-700">
                <span>Showing results for: <?php echo $searchSummary; ?></span>
                <span class="ml-4 text-sm text-gray-500">(Found <?php echo count($searchJets); ?> jets)</span>
            </div>
            <?php if (count($searchJets) === 0): ?>
                <div class="text-center text-red-500 text-lg mb-8">ไม่พบเครื่องบินที่ตรงกับเงื่อนไข</div>
            <?php else: ?>
                <?php echo renderJetsShowcase($searchJets); ?>
            <?php endif; ?>
            <?php echo renderPopularDestinations($popularDestinations); ?>
            <?php echo renderRecentSearches($recentSearches, true); ?>
            <?php echo renderSpecialOffers($specialOffers); ?>
            <script>
            setTimeout(function(){
                var el = document.getElementById('search-result');
                if (el) el.scrollIntoView({behavior: 'smooth'});
            }, 200);
            </script>
        <?php else: ?>
            <?php echo renderPopularDestinations($popularDestinations); ?>
            <?php echo renderRecentSearches($recentSearches, true); ?>
            <?php echo renderSpecialOffers($specialOffers); ?>
        <?php endif; ?>

        <!-- Main Grid (Booking/Membership/Jets) -->
        <!-- (Bookings List ย้ายไปหน้า My Bookings เท่านั้น) -->
      </div>
    </div>
    <?php echo renderFooterNavbar($_GET['page'] ?? 'home'); ?>
    <script>
    // Notification-related JavaScript has been moved to views/components/navbar.php
    </script>
</body>
</html>
<?php
}

if (isset($_GET['page']) && $_GET['page'] === 'bookings') {
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Bookings - prestige88</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-50">
        <?php echo renderNavbar($currentUser); ?>
        <div class="flex justify-center w-full mt-[120px]">
            <div class="w-full max-w-5xl">
            <div class="mb-6">
                    <a href="?page=database" class="text-[#a39786] hover:text-[#8b7d6b] flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
                <?php echo renderBookingsList($bookings, $currentUser); ?>
                <?php if ($currentUser['role'] === 'admin'): ?>
                <div class="bg-white rounded-2xl shadow p-6 mb-10 mt-10">
                    <div class="font-bold text-lg mb-4 flex items-center gap-2"><i class="fas fa-plane text-[#a39786]"></i>Latest Bookings</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-3 py-2">User</th>
                                    <th class="px-3 py-2">Jet</th>
                                    <th class="px-3 py-2">From → To</th>
                                    <th class="px-3 py-2">Date</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $allBookings = Database::loadBookings(); ?>
                                <?php foreach (array_slice(array_reverse($allBookings), 0, 10) as $b): ?>
                                <tr class="border-b">
                                    <td class="px-3 py-2"><?php echo htmlspecialchars($b['user_name'] ?? '-'); ?></td>
                                    <td class="px-3 py-2"><?php echo htmlspecialchars($b['jet_model'] ?? '-'); ?></td>
                                    <td class="px-3 py-2"><?php echo htmlspecialchars(($b['departure_location'] ?? '-') . ' → ' . ($b['arrival_location'] ?? '-')); ?></td>
                                    <td class="px-3 py-2"><?php echo isset($b['departure_date']) ? date('d/m/Y', strtotime($b['departure_date'])) : '-'; ?></td>
                                    <td class="px-3 py-2">
                                        <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold
                                            <?php
                                            switch($b['status']) {
                                                case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                                case 'completed': echo 'bg-blue-100 text-blue-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>"><?php echo ucfirst($b['status'] ?? '-'); ?></span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <a href="?page=edit_booking&booking_id=<?php echo $b['id']; ?>" class="text-blue-600 hover:underline">Edit</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php echo renderFooter($currentUser); ?>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_GET['page']) && $_GET['page'] === 'database') {
    if ($currentUser['role'] !== 'admin') {
        header('Location: ?'); exit;
    }
    // สถิติ summary
    $allUsers = Database::loadUsers();
    $allBookings = Database::loadBookings();
    $totalUsers = count($allUsers);
    $totalBookings = count($allBookings);
    $totalRevenue = array_sum(array_map(function($b){ return $b['total_cost'] ?? 0; }, $allBookings));
    // Mock กราฟจองรายเดือน
    $monthlyStats = array_fill(1, 12, 0);
    foreach ($allBookings as $b) {
        if (!empty($b['created_at'])) {
            $m = (int)date('n', strtotime($b['created_at']));
            $monthlyStats[$m]++;
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard Management - prestige88</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body class="bg-gray-50">
        <?php echo renderNavbar($currentUser); ?>
        <div class="flex justify-center w-full mt-[120px]">
            <div class="w-full max-w-5xl">
                <h2 class="text-3xl font-bold mb-8 text-gray-800 flex items-center gap-3"><i class="fas fa-chart-pie text-[#a39786]"></i> Dashboard Management</h2>
                <?php if (function_exists('renderAdminDashboard')) { echo '<div class="mb-12">' . renderAdminDashboard($allBookings, $currentUser) . '</div>'; } ?>
                <!-- สถิติ summary -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 mt-12">
                    <div class="bg-white rounded-2xl shadow p-6 flex flex-col items-center">
                        <div class="text-3xl font-bold text-[#a39786]"><i class="fas fa-users mr-2"></i><?php echo $totalUsers; ?></div>
                        <div class="text-gray-600 mt-2">Total Users</div>
                    </div>
                    <div class="bg-white rounded-2xl shadow p-6 flex flex-col items-center">
                        <div class="text-3xl font-bold text-[#a39786]"><i class="fas fa-list-alt mr-2"></i><?php echo $totalBookings; ?></div>
                        <div class="text-gray-600 mt-2">Total Bookings</div>
                    </div>
                    <div class="bg-white rounded-2xl shadow p-6 flex flex-col items-center">
                        <div class="text-3xl font-bold text-[#a39786]"><i class="fas fa-dollar-sign mr-2"></i><?php echo number_format($totalRevenue, 2); ?></div>
                        <div class="text-gray-600 mt-2">Total Revenue</div>
                    </div>
                </div>
                <!-- กราฟจองรายเดือน -->
                <div class="bg-white rounded-2xl shadow p-6 mb-10">
                    <div class="font-bold text-lg mb-2 flex items-center gap-2"><i class="fas fa-chart-line text-[#a39786]"></i>Bookings per Month</div>
                    <canvas id="bookingsChart" height="80"></canvas>
                </div>
                <!-- ตารางจัดการ Popular Destinations (นำกลับมา) -->
                <div class="bg-white rounded-2xl shadow-xl p-8 mb-10">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                            <i class="fas fa-map-marker-alt text-[#a39786]"></i>
                            Manage Popular Destinations
                        </h2>
                        <button id="add-dest-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                            <i class="fas fa-plus"></i> Add Destination
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-3 py-2">Image</th>
                                    <th class="px-3 py-2">Name</th>
                                    <th class="px-3 py-2">Price</th>
                                    <th class="px-3 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody id="dest-table-body">
                                <?php $destinations = function_exists('loadPopularDestinations') ? loadPopularDestinations() : []; ?>
                                <?php foreach ($destinations as $dest): ?>
                                <tr data-id="<?php echo $dest['id']; ?>" class="border-b">
                                    <td class="px-3 py-2"><img src="<?php echo htmlspecialchars($dest['image']); ?>" alt="" class="w-16 h-10 object-cover rounded"></td>
                                    <td class="px-3 py-2"><?php echo htmlspecialchars($dest['name']); ?></td>
                                    <td class="px-3 py-2">$<?php echo number_format($dest['price'], 2); ?></td>
                                    <td class="px-3 py-2">
                                        <button class="edit-dest-btn text-blue-600 hover:underline mr-2">Edit</button>
                                        <button class="delete-dest-btn text-red-600 hover:underline">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- เมนูจัดการ -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                    <a href="?page=manage_users" class="rounded-2xl shadow-lg bg-white p-8 flex items-center gap-4 hover:bg-[#f7f5f2] transition">
                        <i class="fas fa-users text-3xl text-[#a39786]"></i>
                        <div><div class="font-bold text-lg">Users</div><div class="text-gray-500 text-sm">Manage all users, roles, and membership</div></div>
                    </a>
                    <a href="?page=bookings" class="rounded-2xl shadow-lg bg-white p-8 flex items-center gap-4 hover:bg-[#f7f5f2] transition">
                        <i class="fas fa-list-alt text-3xl text-[#a39786]"></i>
                        <div><div class="font-bold text-lg">Bookings</div><div class="text-gray-500 text-sm">View and manage all bookings</div></div>
                    </a>
                    <a href="?page=manage_jets" class="rounded-2xl shadow-lg bg-white p-8 flex items-center gap-4 hover:bg-[#f7f5f2] transition">
                        <i class="fas fa-plane text-3xl text-[#a39786]"></i>
                        <div><div class="font-bold text-lg">Jets</div><div class="text-gray-500 text-sm">Manage private jet data</div></div>
                    </a>
                    <a href="?page=manage_rewards" class="rounded-2xl shadow-lg bg-white p-8 flex items-center gap-4 hover:bg-[#f7f5f2] transition">
                        <i class="fas fa-gift text-3xl text-[#a39786]"></i>
                        <div><div class="font-bold text-lg">Rewards</div><div class="text-gray-500 text-sm">Manage user points and rewards</div></div>
                    </a>
                    <a href="?page=manage_logs" class="rounded-2xl shadow-lg bg-white p-8 flex items-center gap-4 hover:bg-[#f7f5f2] transition">
                        <i class="fas fa-file-alt text-3xl text-[#a39786]"></i>
                        <div><div class="font-bold text-lg">Logs</div><div class="text-gray-500 text-sm">System and notification logs</div></div>
                    </a>
                    <a href="?page=manage_settings" class="rounded-2xl shadow-lg bg-white p-8 flex items-center gap-4 hover:bg-[#f7f5f2] transition">
                        <i class="fas fa-cogs text-3xl text-[#a39786]"></i>
                        <div><div class="font-bold text-lg">Settings</div><div class="text-gray-500 text-sm">Site configuration and constants</div></div>
                    </a>
                </div>
                
                <!-- Modal for Add/Edit Destination -->
                <div id="destinationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
                  <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md relative">
                    <button id="closeDestinationModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                    <h3 class="text-lg font-semibold mb-4" id="destinationModalTitle">Add Destination</h3>
                    <form id="destinationForm" class="space-y-4">
                      <input type="hidden" name="id" id="destinationId">
                      <input type="hidden" name="action" id="destinationModalAction">
                      <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input type="text" name="name" id="destinationName" class="w-full rounded-lg border border-gray-300 px-3 py-2" required />
                      </div>
                      <div>
                        <label class="block text-sm font-medium mb-1">Image URL</label>
                        <input type="text" name="image" id="destinationImage" class="w-full rounded-lg border border-gray-300 px-3 py-2" required />
                      </div>
                      <div>
                        <label class="block text-sm font-medium mb-1">Price</label>
                        <input type="number" name="price" id="destinationPrice" class="w-full rounded-lg border border-gray-300 px-3 py-2" required min="0" step="0.01" />
                      </div>
                      <button type="submit" class="w-full bg-[#a89c8a] hover:bg-[#b3a89c] text-white font-semibold py-3 rounded-xl mt-2">Save</button>
                    </form>
                  </div>
                </div>
                
                <script>
                // Chart.js กราฟจองรายเดือน
                const ctx = document.getElementById('bookingsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                        datasets: [{
                            label: 'Bookings',
                            data: <?php echo json_encode(array_values($monthlyStats)); ?>,
                            backgroundColor: '#a39786',
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
                
                // Destinations Management JavaScript
                document.addEventListener('DOMContentLoaded', function() {
                  // ปุ่มปิด modal
                  const closeBtn = document.getElementById('closeDestinationModal');
                  if (closeBtn) {
                    closeBtn.onclick = function() {
                      const modal = document.getElementById('destinationModal');
                      if (modal) modal.classList.add('hidden');
                    };
                  }
                  
                  // ปุ่ม Add
                  const addBtn = document.getElementById('add-dest-btn');
                  if (addBtn) {
                    addBtn.addEventListener('click', function() {
                      // Reset modal fields
                      document.getElementById('destinationModalTitle').textContent = 'Add Destination';
                      document.getElementById('destinationId').value = '';
                      document.getElementById('destinationName').value = '';
                      document.getElementById('destinationImage').value = '';
                      document.getElementById('destinationPrice').value = '';
                      document.getElementById('destinationModalAction').value = 'add_destination';
                      // Show modal
                      document.getElementById('destinationModal').classList.remove('hidden');
                    });
                  }
                  
                  // ปุ่ม Edit
                  document.querySelectorAll('.edit-dest-btn').forEach(btn => {
                    btn.onclick = function() {
                      const tr = btn.closest('tr');
                      const modal = document.getElementById('destinationModal');
                      if (modal) {
                        modal.classList.remove('hidden');
                        const modalTitle = document.getElementById('destinationModalTitle');
                        const destId = document.getElementById('destinationId');
                        const destName = document.getElementById('destinationName');
                        const destImage = document.getElementById('destinationImage');
                        const destPrice = document.getElementById('destinationPrice');
                        if (modalTitle) modalTitle.innerText = 'Edit Destination';
                        if (destId) destId.value = tr.dataset.id;
                        if (destName) destName.value = tr.children[1].innerText;
                        if (destImage) destImage.value = tr.children[0].querySelector('img').src;
                        if (destPrice) destPrice.value = tr.children[2].innerText.replace('$','').replace(',','');
                      }
                    };
                  });
                  
                  // ปุ่ม Delete
                  document.querySelectorAll('.delete-dest-btn').forEach(btn => {
                    btn.onclick = function() {
                      if (!confirm('Delete this destination?')) return;
                      const tr = btn.closest('tr');
                      const id = tr.dataset.id;
                      const formData = new FormData();
                      formData.append('action', 'delete_destination');
                      formData.append('id', id);
                      fetch('index.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => { if (data.success) location.reload(); else alert(data.message); });
                    };
                  });
                  
                  // ฟอร์ม modal
                  const destForm = document.getElementById('destinationForm');
                  if (destForm) {
                    destForm.onsubmit = function(e) {
                      e.preventDefault();
                      const destId = document.getElementById('destinationId');
                      const destName = document.getElementById('destinationName');
                      const destImage = document.getElementById('destinationImage');
                      const destPrice = document.getElementById('destinationPrice');
                      const formData = new FormData();
                      if (destId && destId.value) {
                        formData.append('action', 'update_destination');
                        formData.append('id', destId.value);
                      } else {
                        formData.append('action', 'add_destination');
                      }
                      if (destName) formData.append('name', destName.value);
                      if (destImage) formData.append('image', destImage.value);
                      if (destPrice) formData.append('price', destPrice.value);
                      fetch('index.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                          if (data.success) location.reload();
                          else alert(data.message);
                        });
                    };
                  }
                });
                </script>
            </div>
        </div>
        <?php echo renderFooter($currentUser); ?>
        <?php echo renderFooterNavbar('database'); ?>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_GET['page']) && $_GET['page'] === 'edit_booking') {
    if ($currentUser['role'] !== 'admin') {
        header('Location: ?'); exit;
    }
    $bookingId = $_GET['booking_id'] ?? '';
    if (empty($bookingId)) {
        header('Location: ?page=database'); exit;
    }
    // Load booking data
    $bookings = Database::loadBookings();
    $booking = null;
    foreach ($bookings as $b) {
        if ($b['id'] === $bookingId) {
            $booking = $b;
            break;
        }
    }
    if (!$booking) {
        header('Location: ?page=database'); exit;
    }
    // --- HANDLE UPLOAD QR CODE ก่อน ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['qr_code']['name'], PATHINFO_EXTENSION));
        $allowed = ['png','jpg','jpeg','gif','webp'];
        if (in_array($ext, $allowed)) {
            $qrDir = 'qr_images';
            if (!is_dir($qrDir)) mkdir($qrDir, 0755, true);
            $qrPath = $qrDir . '/qr_' . $bookingId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['qr_code']['tmp_name'], $qrPath)) {
                $booking['qr_code'] = $qrPath;
                // อัปเดต booking ใน $bookings array
                foreach ($bookings as &$b) {
                    if ($b['id'] === $bookingId) {
                        $b = $booking;
                        break;
                    }
                }
                Database::saveBookings($bookings);
            }
            header('Location: ?page=edit_booking&booking_id=' . urlencode($bookingId));
            exit;
        }
    }
    // --- HANDLE DELETE QR CODE ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_qr'])) {
        if (!empty($booking['qr_code']) && file_exists($booking['qr_code'])) {
            unlink($booking['qr_code']);
        }
        unset($booking['qr_code']);
        foreach ($bookings as &$b) {
            if ($b['id'] === $bookingId) {
                $b = $booking;
                break;
            }
        }
        Database::saveBookings($bookings);
        header('Location: ?page=edit_booking&booking_id=' . urlencode($bookingId));
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Booking - prestige88</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-50">
        <?php echo renderNavbar($currentUser); ?>
        <div class="flex justify-center w-full mt-[120px]">
            <div class="w-full max-w-4xl">
                <div class="mb-6">
                    <a href="?page=bookings" class="text-[#a39786] hover:text-[#8b7d6b] flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back to Manage all Bookings
                    </a>
                </div>
                
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                        <i class="fas fa-edit text-[#a39786]"></i>
                        Edit Booking Details
                    </h2>
                    
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <div class="flex items-center gap-2 text-blue-800 mb-2">
                            <i class="fas fa-info-circle"></i>
                            <span class="font-semibold">Booking Information</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium">Booking ID:</span>
                                <span class="text-gray-600"><?php echo htmlspecialchars($booking['id']); ?></span>
                            </div>
                            <div>
                                <span class="font-medium">Customer:</span>
                                <span class="text-gray-600"><?php echo htmlspecialchars($booking['user_name']); ?></span>
                            </div>
                            <div>
                                <span class="font-medium">Jet Model:</span>
                                <span class="text-gray-600"><?php echo htmlspecialchars($booking['jet_model']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- QR CODE Upload/Preview -->
                    <form id="qr-upload-form" method="POST" enctype="multipart/form-data" class="mb-6">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">QR CODE (Upload Image)</label>
                            <input type="file" name="qr_code" accept="image/*" class="block w-full border border-gray-300 rounded-lg px-3 py-2" />
                        </div>
                        <?php if (!empty($booking['qr_code']) && file_exists($booking['qr_code'])): ?>
                        <div class="mb-4 flex items-center gap-4">
                            <img src="<?php echo $booking['qr_code']; ?>" alt="QR CODE" class="w-40 h-40 object-contain border rounded-lg" />
                            <input type="hidden" name="delete_qr" value="1">
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg">Delete QR CODE</button>
                        </div>
                        <?php endif; ?>
                        <button type="submit" class="bg-[#a39786] hover:bg-[#8b7d6b] text-white font-semibold py-2 px-6 rounded-lg">Upload QR CODE</button>
                    </form>
                    
                    <form id="edit-booking-form" class="space-y-6">
                        <input type="hidden" name="action" value="update_booking">
                        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['id']); ?>">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Passenger Name</label>
                            <input type="text" name="user_name" value="<?php echo htmlspecialchars($booking['user_name']); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]" required>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Departure Location</label>
                                <input type="text" name="departure_location" value="<?php echo htmlspecialchars($booking['departure_location']); ?>" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Arrival Location</label>
                                <input type="text" name="arrival_location" value="<?php echo htmlspecialchars($booking['arrival_location']); ?>" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]" required>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Departure Date</label>
                                <input type="date" name="departure_date" value="<?php echo htmlspecialchars($booking['departure_date']); ?>" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Departure Time</label>
                                <input type="time" name="departure_time" value="<?php echo htmlspecialchars($booking['departure_time']); ?>" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]" required>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Number of Passengers</label>
                                <input type="number" name="passengers" min="1" max="20" value="<?php echo htmlspecialchars($booking['passengers']); ?>" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Flight Hours</label>
                                <input type="number" name="flight_hours" min="1" max="24" value="<?php echo htmlspecialchars($booking['flight_hours']); ?>" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Seat</label>
                                <input type="text" name="seat" value="<?php echo htmlspecialchars($booking['seat'] ?? ''); ?>" placeholder="e.g. 12A"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bus Number</label>
                                <input type="text" name="bus_number" value="<?php echo htmlspecialchars($booking['bus_number'] ?? ''); ?>" placeholder="e.g. B7"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Boarding Gate</label>
                                <input type="text" name="boarding_gate" value="<?php echo htmlspecialchars($booking['boarding_gate'] ?? ''); ?>" placeholder="e.g. G3"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Special Requests</label>
                            <textarea name="special_requests" rows="3" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]"
                                      placeholder="Any special requests or notes..."><?php echo htmlspecialchars($booking['special_requests']); ?></textarea>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 mb-3">Pricing Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="font-medium">Price per Hour:</span>
                                    <span class="text-gray-600">$<?php echo number_format($booking['price_per_hour']); ?></span>
                                </div>
                                <div>
                                    <span class="font-medium">Membership Discount:</span>
                                    <span class="text-gray-600"><?php echo $booking['membership_discount']; ?>%</span>
                                </div>
                                <div>
                                    <span class="font-medium">Total Cost:</span>
                                    <span class="text-green-600 font-semibold">$<?php echo number_format($booking['total_cost']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-4 pt-6">
                            <button type="submit" class="flex-1 bg-[#a39786] hover:bg-[#8b7d6b] text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Update Booking
                            </button>
                            <a href="?page=bookings" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <script>
        document.getElementById('edit-booking-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Booking updated successfully!');
                    window.location.href = '?page=database';
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error updating booking. Please try again.');
                console.error('Error:', error);
            }
        });
        </script>
        
        <?php echo renderFooter($currentUser); ?>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_GET['page']) && $_GET['page'] === 'manage_users') {
    if (!isset($currentUser) || $currentUser['role'] !== 'admin') {
        header('Location: ?'); exit;
    }
    
    $allUsers = Database::loadUsers();
    
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Users - prestige88</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-50">
        <?php echo renderNavbar($currentUser); ?>
        <div class="flex justify-center w-full mt-[120px]">
            <div class="w-full max-w-6xl">
                 <div class="mb-6">
                    <a href="?page=database" class="text-[#a39786] hover:text-[#8b7d6b] flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
                <?php echo renderManageUsersPage($allUsers); ?>
            </div>
        </div>
        <?php echo renderFooter($currentUser); ?>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_GET['page']) && $_GET['page'] === 'manage_jets') {
    if (!isset($currentUser) || $currentUser['role'] !== 'admin') {
        header('Location: ?'); exit;
    }
    
    $allJets = Database::loadJets();
    
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Jets - prestige88</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-50">
        <?php echo renderNavbar($currentUser); ?>
        <div class="flex justify-center w-full mt-[120px]">
            <div class="w-full max-w-6xl">
                 <div class="mb-6">
                    <a href="?page=database" class="text-[#a39786] hover:text-[#8b7d6b] flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
                <?php echo renderManageJetsPage($allJets); ?>
            </div>
        </div>
        <?php echo renderFooter($currentUser); ?>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_GET['page']) && $_GET['page'] === 'manage_otp') {
    if (!isset($currentUser) || $currentUser['role'] !== 'admin') {
        header('Location: ?'); exit;
    }
    
    $otpData = OTP::loadOTPData();
    
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage OTP - prestige88</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-50">
        <?php echo renderNavbar($currentUser); ?>
        <div class="flex justify-center w-full mt-[120px]">
            <div class="w-full max-w-5xl">
                 <div class="mb-6">
                    <a href="?page=database" class="text-[#a39786] hover:text-[#8b7d6b] flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
                <?php echo renderManageOTPPage($otpData); ?>
            </div>
        </div>
        <?php echo renderFooter($currentUser); ?>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_GET['page']) && $_GET['page'] === 'manage_logs') {
    if (!isset($currentUser) || $currentUser['role'] !== 'admin') {
        header('Location: ?'); exit;
    }
    
    // Load logs from different log files
    $logs = [];
    $logFiles = [
        'storage/logs/otp.log',
        'storage/logs/sms.log', 
        'storage/logs/notifications.log'
    ];
    
    foreach ($logFiles as $logFile) {
        if (file_exists($logFile)) {
            $fileLogs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($fileLogs) {
                $logs = array_merge($logs, $fileLogs);
            }
        }
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Logs - prestige88</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-50">
        <?php echo renderNavbar($currentUser); ?>
        <div class="flex justify-center w-full mt-[120px]">
            <div class="w-full max-w-5xl">
                 <div class="mb-6">
                    <a href="?page=database" class="text-[#a39786] hover:text-[#8b7d6b] flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
                <?php echo renderManageLogsPage($logs); ?>
            </div>
        </div>
        <?php echo renderFooter($currentUser); ?>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_GET['page']) && $_GET['page'] === 'manage_settings') {
    if (!isset($currentUser) || $currentUser['role'] !== 'admin') {
        header('Location: ?'); exit;
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Settings - prestige88</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-50">
        <?php echo renderNavbar($currentUser); ?>
        <div class="flex justify-center w-full mt-[120px]">
            <div class="w-full max-w-5xl">
                 <div class="mb-6">
                    <a href="?page=database" class="text-[#a39786] hover:text-[#8b7d6b] flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
                <?php echo renderManageSettingsPage(); ?>
            </div>
        </div>
        <?php echo renderFooter($currentUser); ?>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_GET['page']) && $_GET['page'] === 'manage_rewards') {
    if (!isset($currentUser) || $currentUser['role'] !== 'admin') {
        header('Location: ?'); exit;
    }
    $allUsers = Database::loadUsers();
    $allBookings = Database::loadBookings();
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Rewards - prestige88</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-50">
        <?php echo renderNavbar($currentUser); ?>
        <div class="flex justify-center w-full mt-[120px]">
            <div class="w-full max-w-5xl">
                 <div class="mb-6">
                    <a href="?page=database" class="text-[#a39786] hover:text-[#8b7d6b] flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
                <?php echo renderManageRewardsPage($allUsers, $allBookings); ?>
            </div>
        </div>
        <?php echo renderFooter($currentUser); ?>
    </body>
    </html>
    <?php
    exit;
}

// PHP: handle delete QR CODE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_qr'])) {
    if (!empty($booking['qr_code']) && file_exists($booking['qr_code'])) {
        unlink($booking['qr_code']);
    }
    unset($booking['qr_code']);
    foreach ($bookings as &$b) {
        if ($b['id'] === $bookingId) {
            $b = $booking;
            break;
        }
    }
    Database::saveBookings($bookings);
    // reload page to update UI
    header('Location: ?page=edit_booking&booking_id=' . urlencode($bookingId));
    exit;
}

// ... existing code ...
    // --- HANDLE UPLOAD QR CODE ก่อน ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['qr_code']['name'], PATHINFO_EXTENSION));
        $allowed = ['png','jpg','jpeg','gif','webp'];
        if (in_array($ext, $allowed)) {
            $qrDir = 'qr_images';
            if (!is_dir($qrDir)) mkdir($qrDir, 0755, true);
            $qrPath = $qrDir . '/qr_' . $bookingId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['qr_code']['tmp_name'], $qrPath)) {
                $booking['qr_code'] = $qrPath;
                // อัปเดต booking ใน $bookings array
                foreach ($bookings as &$b) {
                    if ($b['id'] === $bookingId) {
                        $b = $booking;
                        break;
                    }
                }
                Database::saveBookings($bookings);
            }
            header('Location: ?page=edit_booking&booking_id=' . urlencode($bookingId));
            exit;
        }
    }
// ... existing code ...

// --- Popular Destinations AJAX API (เหมือนกับ api/destinations.php) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['add_destination','update_destination','delete_destination'])) {
    require_once __DIR__ . '/utils/functions.php';
    require_once __DIR__ . '/controllers/AuthController.php';
    require_once __DIR__ . '/utils/constants.php';
    header('Content-Type: application/json');
    session_start();
    if (!AuthController::isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
        exit;
    }
    $currentUser = AuthController::getCurrentUser();
    if ($currentUser['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Permission denied.']);
        exit;
    }
    $action = $_POST['action'];
    try {
        if ($action === 'add_destination') {
            $name = trim($_POST['name'] ?? '');
            $image = trim($_POST['image'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $dest = addPopularDestination($name, $image, $price);
            logActivity('Add Popular Destination', "name=$name, price=$price", $currentUser['id']);
            echo json_encode(['success' => true, 'destination' => $dest]);
            exit;
        } elseif ($action === 'update_destination') {
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $image = trim($_POST['image'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            updatePopularDestination($id, $name, $image, $price);
            logActivity('Update Popular Destination', "id=$id, name=$name, price=$price", $currentUser['id']);
            echo json_encode(['success' => true]);
            exit;
        } elseif ($action === 'delete_destination') {
            $id = $_POST['id'] ?? '';
            deletePopularDestination($id);
            logActivity('Delete Popular Destination', "id=$id", $currentUser['id']);
            echo json_encode(['success' => true]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// --- Preferences & Account Actions AJAX API ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_once __DIR__ . '/controllers/AuthController.php';
    session_start();
    if (!AuthController::isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
        exit;
    }
    $currentUser = AuthController::getCurrentUser();
    $action = $_POST['action'];
    if ($action === 'update_preferences') {
        // Save language/currency to session (or user profile if needed)
        $_SESSION['preferences'] = [
            'language' => $_POST['language'] ?? 'en',
            'currency' => $_POST['currency'] ?? 'USD',
        ];
        echo json_encode(['success' => true, 'preferences' => $_SESSION['preferences']]);
        exit;
    } elseif ($action === 'switch_business_account') {
        // Mock: เปลี่ยน role ใน session
        $_SESSION['user']['role'] = 'business';
        echo json_encode(['success' => true, 'role' => 'business']);
        exit;
    } elseif ($action === 'sign_out') {
        // Logout
        session_destroy();
        echo json_encode(['success' => true, 'redirect' => 'index.php?page=login']);
        exit;
    }
}

?>
