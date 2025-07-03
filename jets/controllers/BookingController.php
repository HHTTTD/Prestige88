<?php
require_once 'models/Booking.php';
require_once 'controllers/AuthController.php';

class BookingController {
    public static function create($bookingData) {
        if (!AuthController::hasPermission('client')) {
            throw new Exception('คุณไม่มีสิทธิ์ในการจอง');
        }
        
        $currentUser = AuthController::getCurrentUser();
        $bookingData['user_name'] = $currentUser['full_name'];
        
        return Booking::create($bookingData, $currentUser['id']);
    }
    
    public static function updateStatus($bookingId, $newStatus) {
        if (!AuthController::hasPermission('admin')) {
            throw new Exception('คุณไม่มีสิทธิ์ในการอัปเดตสถานะ');
        }
        
        $currentUser = AuthController::getCurrentUser();
        Booking::updateStatus($bookingId, $newStatus, $currentUser['username']);
    }
    
    public static function cancel($bookingId) {
        $currentUser = AuthController::getCurrentUser();
        return Booking::cancel($bookingId, $currentUser['id'], $currentUser['role']);
    }
    
    public static function getBookingsForUser($userRole, $userId) {
        $bookings = Database::loadBookings();
        
        if ($userRole === 'client') {
            return array_filter($bookings, function($booking) use ($userId) {
                return $booking['user_id'] === $userId;
            });
        }
        
        return $bookings;
    }
}