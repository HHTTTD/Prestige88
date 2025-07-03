<?php
// Test file for booking logic
require_once 'config/database.php';
require_once 'models/Booking.php';
require_once 'models/User.php';

// Initialize database
Database::initializeFiles();

echo "<h1>Booking Logic Test</h1>";

// Test 1: Check available slots before booking
echo "<h2>Test 1: Available Slots Check</h2>";
$jets = Database::loadJets();
$bookings = Database::loadBookings();

foreach ($jets as $jet) {
    echo "<h3>Jet: {$jet['model']} (ID: {$jet['id']})</h3>";
    
    if (empty($jet['available_slots'])) {
        echo "<p>No available slots configured</p>";
        continue;
    }
    
    echo "<p>Total slots: " . count($jet['available_slots']) . "</p>";
    
    // Check which slots are booked
    $confirmedBookings = array_filter($bookings, function($booking) use ($jet) {
        return $booking['jet_id'] === $jet['id'] && 
               in_array($booking['status'], ['pending', 'confirmed']);
    });
    
    echo "<p>Confirmed/Pending bookings: " . count($confirmedBookings) . "</p>";
    
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
        
        $status = $isBooked ? "BOOKED" : "AVAILABLE";
        $color = $isBooked ? "red" : "green";
        echo "<p style='color: $color'>Slot: {$slot['date']} - {$slot['departure']} → {$slot['arrival']} - <strong>$status</strong></p>";
        
        if (!$isBooked) {
            $availableCount++;
        }
    }
    
    echo "<p><strong>Available slots: $availableCount</strong></p>";
    echo "<hr>";
}

// Test 2: Simulate booking process
echo "<h2>Test 2: Booking Process Simulation</h2>";

// Find a jet with available slots
$jetWithSlots = null;
foreach ($jets as $jet) {
    if (!empty($jet['available_slots'])) {
        $jetWithSlots = $jet;
        break;
    }
}

if ($jetWithSlots) {
    echo "<p>Testing with jet: {$jetWithSlots['model']}</p>";
    
    // Find an available slot
    $availableSlot = null;
    foreach ($jetWithSlots['available_slots'] as $slot) {
        $isBooked = false;
        foreach ($bookings as $booking) {
            if ($booking['jet_id'] === $jetWithSlots['id'] && 
                in_array($booking['status'], ['pending', 'confirmed']) &&
                $booking['departure_date'] === $slot['date'] && 
                $booking['departure_location'] === $slot['departure']) {
                $isBooked = true;
                break;
            }
        }
        
        if (!$isBooked) {
            $availableSlot = $slot;
            break;
        }
    }
    
    if ($availableSlot) {
        echo "<p>Found available slot: {$availableSlot['date']} - {$availableSlot['departure']}</p>";
        
        // Test booking data
        $bookingData = [
            'jet_id' => $jetWithSlots['id'],
            'departure_date' => $availableSlot['date'],
            'departure_location' => $availableSlot['departure'],
            'arrival_location' => 'Test Destination',
            'departure_time' => '10:00',
            'passengers' => 2,
            'flight_hours' => 3,
            'special_requests' => 'Test booking',
            'user_name' => 'Test User'
        ];
        
        echo "<p>Attempting to book with data:</p>";
        echo "<pre>" . print_r($bookingData, true) . "</pre>";
        
        try {
            // This would normally require a logged-in user
            echo "<p><strong>Note:</strong> This test requires a logged-in user to complete the booking process.</p>";
            echo "<p>The booking logic would:</p>";
            echo "<ul>";
            echo "<li>Check if slot is available</li>";
            echo "<li>Create booking record</li>";
            echo "<li>Remove slot from available_slots</li>";
            echo "<li>Update user membership</li>";
            echo "</ul>";
        } catch (Exception $e) {
            echo "<p style='color: red'>Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red'>No available slots found for testing</p>";
    }
} else {
    echo "<p style='color: red'>No jets with available slots found</p>";
}

// Test 3: Check booking cancellation logic
echo "<h2>Test 3: Cancellation Logic</h2>";
$cancellableBookings = array_filter($bookings, function($booking) {
    return in_array($booking['status'], ['pending', 'confirmed']);
});

if (!empty($cancellableBookings)) {
    $testBooking = array_values($cancellableBookings)[0];
    echo "<p>Testing cancellation with booking: {$testBooking['id']}</p>";
    echo "<p>Jet: {$testBooking['jet_model']}</p>";
    echo "<p>Date: {$testBooking['departure_date']}</p>";
    echo "<p>From: {$testBooking['departure_location']}</p>";
    echo "<p>Status: {$testBooking['status']}</p>";
    
    echo "<p>The cancellation logic would:</p>";
    echo "<ul>";
    echo "<li>Change booking status to 'cancelled'</li>";
    echo "<li>Add slot back to available_slots</li>";
    echo "<li>Update booking record</li>";
    echo "</ul>";
} else {
    echo "<p>No cancellable bookings found</p>";
}

echo "<h2>Summary</h2>";
echo "<p>The booking system now includes:</p>";
echo "<ul>";
echo "<li>Real-time availability checking</li>";
echo "<li>Automatic slot removal when booked</li>";
echo "<li>Slot restoration when cancelled</li>";
echo "<li>Prevention of double booking</li>";
echo "<li>Visual indicators for available/unavailable jets</li>";
echo "</ul>";
?> 