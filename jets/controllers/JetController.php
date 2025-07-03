<?php
require_once 'models/Jet.php';
require_once 'controllers/AuthController.php';

class JetController {
    public static function getAll() {
        return Jet::getAll();
    }
    
    public static function getAvailable() {
        return Jet::getAvailable();
    }
    
    public static function create($jetData) {
        if (!AuthController::hasPermission('admin')) {
            throw new Exception('คุณไม่มีสิทธิ์ในการเพิ่มเครื่องบิน');
        }
        
        // Validation
        $errors = [];
        
        if (empty($jetData['model'])) {
            $errors[] = 'กรุณากรอกรุ่นเครื่องบิน';
        }
        
        if (!is_numeric($jetData['capacity']) || $jetData['capacity'] <= 0) {
            $errors[] = 'จำนวนที่นั่งต้องเป็นตัวเลขที่มากกว่า 0';
        }
        
        if (!is_numeric($jetData['price_per_hour']) || $jetData['price_per_hour'] <= 0) {
            $errors[] = 'ราคาต่อชั่วโมงต้องเป็นตัวเลขที่มากกว่า 0';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }
        
        return Jet::create($jetData);
    }
    
    public static function update($jetId, $jetData) {
        if (!AuthController::hasPermission('admin')) {
            throw new Exception('คุณไม่มีสิทธิ์ในการแก้ไขเครื่องบิน');
        }
        
        return Jet::update($jetId, $jetData);
    }
    
    public static function delete($jetId) {
        if (!AuthController::hasPermission('admin')) {
            throw new Exception('คุณไม่มีสิทธิ์ในการลบเครื่องบิน');
        }
        
        // Check if jet has active bookings
        $bookings = Database::loadBookings();
        $activeBookings = array_filter($bookings, function($booking) use ($jetId) {
            return $booking['jet_id'] === $jetId && 
                   in_array($booking['status'], ['pending', 'confirmed']);
        });
        
        if (!empty($activeBookings)) {
            throw new Exception('ไม่สามารถลบเครื่องบินที่มีการจองที่ยังไม่เสร็จสิ้น');
        }
        
        return Jet::delete($jetId);
    }
    
    public static function updateStatus($jetId, $status) {
        if (!AuthController::hasPermission('admin')) {
            throw new Exception('คุณไม่มีสิทธิ์ในการอัพเดตสถานะเครื่องบิน');
        }
        
        $validStatuses = ['available', 'maintenance', 'unavailable'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception('สถานะไม่ถูกต้อง');
        }
        
        return Jet::updateStatus($jetId, $status);
    }
}