<?php
// API Endpoints for AJAX calls
session_start();

require_once '../config/database.php';
require_once '../controllers/AuthController.php';
require_once '../controllers/BookingController.php';
require_once '../controllers/JetController.php';

header('Content-Type: application/json');

if (!AuthController::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_jets':
            $jets = JetController::getAvailable();
            echo json_encode(['success' => true, 'data' => $jets]);
            break;
            
        case 'get_bookings':
            $currentUser = AuthController::getCurrentUser();
            $bookings = BookingController::getBookingsForUser($currentUser['role'], $currentUser['id']);
            echo json_encode(['success' => true, 'data' => $bookings]);
            break;
            
        case 'get_booking_stats':
            if (!AuthController::hasPermission('admin')) {
                throw new Exception('ไม่มีสิทธิ์เข้าถึงข้อมูลนี้');
            }
            
            $bookings = Database::loadBookings();
            $stats = [
                'total' => count($bookings),
                'pending' => count(array_filter($bookings, fn($b) => $b['status'] === 'pending')),
                'confirmed' => count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed')),
                'cancelled' => count(array_filter($bookings, fn($b) => $b['status'] === 'cancelled')),
                'revenue' => array_sum(array_column(
                    array_filter($bookings, fn($b) => $b['status'] === 'confirmed'), 
                    'total_cost'
                ))
            ];
            
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        case 'calculate_cost':
            $jetId = $_POST['jet_id'] ?? '';
            $hours = floatval($_POST['hours'] ?? 0);
            
            $jet = JetController::getById($jetId);
            if (!$jet) {
                throw new Exception('ไม่พบเครื่องบินที่เลือก');
            }
            
            $currentUser = AuthController::getCurrentUser();
            $baseTotal = $jet['price_per_hour'] * $hours;
            $discount = Membership::getDiscount($currentUser['id']);
            $discountAmount = $baseTotal * ($discount / 100);
            $totalCost = $baseTotal - $discountAmount;
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'base_total' => $baseTotal,
                    'discount_percent' => $discount,
                    'discount_amount' => $discountAmount,
                    'total_cost' => $totalCost
                ]
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}