<?php
require_once 'config/database.php';
require_once 'models/Membership.php';
require_once 'models/User.php';
require_once 'utils/constants.php';
require_once 'models/Notification.php';

class Booking {
    public static function create($bookingData, $userId) {
        $jets = Database::loadJets();
        $selectedJet = null;
        
        foreach ($jets as $jet) {
            if ($jet['id'] === $bookingData['jet_id']) {
                $selectedJet = $jet;
                break;
            }
        }
        
        if (!$selectedJet) {
            throw new Exception('ไม่พบเครื่องบินที่เลือก');
        }
        
        // ตรวจสอบว่า slot ที่เลือกยังว่างอยู่หรือไม่
        if (!self::isSlotAvailable($selectedJet, $bookingData['departure_date'], $bookingData['departure_location'])) {
            throw new Exception('วันที่และเส้นทางที่เลือกไม่ว่างแล้ว กรุณาเลือกวันอื่น');
        }
        
        $hours = floatval($bookingData['flight_hours']);
        $baseTotal = $selectedJet['price_per_hour'] * $hours;
        
        // ปิดระบบส่วนลด: คิดราคาเต็ม
        $discount = 0;
        $discountAmount = 0;
        $totalCost = $baseTotal;
        
        $newBooking = [
            'id' => uniqid(),
            'user_id' => $userId,
            'user_name' => $bookingData['user_name'],
            'jet_id' => $bookingData['jet_id'],
            'jet_model' => $selectedJet['model'],
            'departure_location' => trim($bookingData['departure_location']),
            'arrival_location' => trim($bookingData['arrival_location']),
            'departure_date' => $bookingData['departure_date'],
            'departure_time' => $bookingData['departure_time'],
            'passengers' => intval($bookingData['passengers']),
            'flight_hours' => $hours,
            'price_per_hour' => $selectedJet['price_per_hour'],
            'base_total' => $baseTotal,
            'membership_discount' => $discount,
            'discount_amount' => $discountAmount,
            'total_cost' => $totalCost,
            'special_requests' => trim($bookingData['special_requests']),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => '',
            'updated_by' => '',
            'seat' => '',
            'bus_number' => '',
            'boarding_gate' => ''
        ];
        
        $bookings = Database::loadBookings();
        $bookings[] = $newBooking;
        Database::saveBookings($bookings);
        
        // ลบ slot ที่จองแล้วออกจาก available_slots
        self::removeBookedSlot($selectedJet['id'], $bookingData['departure_date'], $bookingData['departure_location']);
        
        // สร้างการแจ้งเตือนสำหรับแอดมิน
        Notification::create(
            'admin',
            "New booking for {$selectedJet['model']} from {$newBooking['user_name']}.",
            "?page=edit_booking&booking_id={$newBooking['id']}"
        );
        
        // Update user membership tier
        $newTier = Membership::calculateTier($userId);
        User::updateMembership($userId, $newTier);
        
        return $newBooking;
    }
    
    /**
     * ตรวจสอบว่า slot ที่เลือกยังว่างอยู่หรือไม่
     */
    public static function isSlotAvailable($jet, $date, $departure) {
        if (empty($jet['available_slots'])) {
            return false;
        }
        
        foreach ($jet['available_slots'] as $slot) {
            if ($slot['date'] === $date && $slot['departure'] === $departure) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * ลบ slot ที่จองแล้วออกจาก available_slots
     */
    public static function removeBookedSlot($jetId, $date, $departure) {
        $jets = Database::loadJets();
        
        foreach ($jets as &$jet) {
            if ($jet['id'] === $jetId) {
                if (!empty($jet['available_slots'])) {
                    $jet['available_slots'] = array_filter($jet['available_slots'], function($slot) use ($date, $departure) {
                        return !($slot['date'] === $date && $slot['departure'] === $departure);
                    });
                    $jet['available_slots'] = array_values($jet['available_slots']); // Reset array keys
                }
                $jet['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        Database::saveJets($jets);
    }
    
    /**
     * เพิ่ม slot กลับเข้าไปเมื่อการจองถูกยกเลิก
     */
    public static function addBackSlot($jetId, $date, $departure, $arrival = '') {
        $jets = Database::loadJets();
        
        foreach ($jets as &$jet) {
            if ($jet['id'] === $jetId) {
                if (!isset($jet['available_slots'])) {
                    $jet['available_slots'] = [];
                }
                
                $newSlot = [
                    'date' => $date,
                    'departure' => $departure,
                    'arrival' => $arrival,
                    'departure_time' => '',
                    'arrival_time' => ''
                ];
                
                $jet['available_slots'][] = $newSlot;
                $jet['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        Database::saveJets($jets);
    }
    
    public static function updateStatus($bookingId, $newStatus, $updatedBy) {
        $bookings = Database::loadBookings();
        
        foreach ($bookings as &$booking) {
            if ($booking['id'] === $bookingId) {
                $oldStatus = $booking['status'];
                $booking['status'] = $newStatus;
                $booking['updated_at'] = date('Y-m-d H:i:s');
                $booking['updated_by'] = $updatedBy;
                
                // ถ้า booking ถูกยืนยัน (confirmed) ให้เพิ่มคะแนนอีกครั้ง (ถ้ายังไม่ได้รับ)
                if ($newStatus === 'confirmed') {
                    $userId = $booking['user_id'];
                    $addPoints = floor($booking['total_cost'] / POINTS_RATE); // ใช้เรทจาก config
                    $currentPoints = User::getPoints($userId);
                    User::setPoints($userId, $currentPoints + $addPoints);
                }
                
                // ถ้า booking ถูกยกเลิก ให้เพิ่ม slot กลับเข้าไป
                if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                    self::addBackSlot(
                        $booking['jet_id'], 
                        $booking['departure_date'], 
                        $booking['departure_location'],
                        $booking['arrival_location']
                    );
                }
                
                // สร้างการแจ้งเตือนสำหรับผู้ใช้
                $message = '';
                switch ($newStatus) {
                    case 'confirmed':
                        $message = "Your booking for {$booking['jet_model']} has been confirmed!";
                        break;
                    case 'cancelled':
                        $message = "Your booking for {$booking['jet_model']} has been cancelled.";
                        break;
                }
                if ($message) {
                    Notification::create(
                        $booking['user_id'],
                        $message,
                        "?page=bookings"
                    );
                }
                
                break;
            }
        }
        
        Database::saveBookings($bookings);
    }
    
    public static function cancel($bookingId, $userId, $userRole) {
        $bookings = Database::loadBookings();
        
        foreach ($bookings as &$booking) {
            if ($booking['id'] === $bookingId) {
                // Admin can cancel any booking, clients can only cancel their own
                if ($userRole === 'admin' || $booking['user_id'] === $userId) {
                    $oldStatus = $booking['status'];
                    $booking['status'] = 'cancelled';
                    $booking['cancelled_at'] = date('Y-m-d H:i:s');
                    $booking['cancelled_by'] = $userId;
                    
                    // เพิ่ม slot กลับเข้าไปเมื่อยกเลิก
                    if ($oldStatus !== 'cancelled') {
                        self::addBackSlot(
                            $booking['jet_id'], 
                            $booking['departure_date'], 
                            $booking['departure_location'],
                            $booking['arrival_location']
                        );
                        
                        // แจ้งเตือนผู้ใช้เมื่อแอดมินยกเลิก
                        if ($userRole === 'admin') {
                            Notification::create(
                                $booking['user_id'],
                                "Your booking for {$booking['jet_model']} was cancelled by an administrator.",
                                "?page=bookings"
                            );
                        }
                    }
                    
                    Database::saveBookings($bookings);
                    return true;
                } else {
                    throw new Exception('คุณไม่มีสิทธิ์ยกเลิกการจองนี้!');
                }
            }
        }
        
        throw new Exception('ไม่พบการจองที่ต้องการยกเลิก');
    }
}