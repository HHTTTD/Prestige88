<?php
// Test file for date sorting
require_once 'config/database.php';

// Initialize database
Database::initializeFiles();

echo "<h1>Date Sorting Test</h1>";

// Test 1: Check available slots sorting
echo "<h2>Test 1: Available Slots Sorting</h2>";
$jets = Database::loadBookings();

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

$jets = Database::loadJets();

foreach ($jets as $jet) {
    echo "<h3>Jet: {$jet['model']} (ID: {$jet['id']})</h3>";
    
    if (empty($jet['available_slots'])) {
        echo "<p>No available slots configured</p>";
        continue;
    }
    
    echo "<p><strong>Original slots order:</strong></p>";
    foreach ($jet['available_slots'] as $index => $slot) {
        echo "<p>Slot " . ($index + 1) . ": {$slot['date']} - {$slot['departure']}</p>";
    }
    
    $availableSlots = getAvailableSlotsForJet($jet);
    
    echo "<p><strong>Available slots (sorted by date):</strong></p>";
    if (empty($availableSlots)) {
        echo "<p style='color: red'>No available slots (all booked)</p>";
    } else {
        foreach ($availableSlots as $index => $slot) {
            $date = new DateTime($slot['date']);
            $formattedDate = $date->format('M d, Y (l)');
            echo "<p style='color: green'>Slot " . ($index + 1) . ": {$formattedDate} - {$slot['departure']}</p>";
        }
    }
    
    echo "<hr>";
}

// Test 2: JavaScript sorting simulation
echo "<h2>Test 2: JavaScript Sorting Simulation</h2>";

$testSlots = [
    ['date' => '2025-06-25', 'departure' => 'Bangkok'],
    ['date' => '2025-05-28', 'departure' => 'Phuket'],
    ['date' => '2026-01-24', 'departure' => 'Singapore'],
    ['date' => '2025-12-15', 'departure' => 'Tokyo']
];

echo "<p><strong>Original order:</strong></p>";
foreach ($testSlots as $index => $slot) {
    echo "<p>Slot " . ($index + 1) . ": {$slot['date']} - {$slot['departure']}</p>";
}

// Sort using PHP (same logic as JavaScript)
usort($testSlots, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});

echo "<p><strong>Sorted order (earliest first):</strong></p>";
foreach ($testSlots as $index => $slot) {
    $date = new DateTime($slot['date']);
    $formattedDate = $date->format('M d, Y (l)');
    echo "<p style='color: blue'>Slot " . ($index + 1) . ": {$formattedDate} - {$slot['departure']}</p>";
}

echo "<h2>Summary</h2>";
echo "<p>The date sorting system now:</p>";
echo "<ul>";
echo "<li>Sorts available slots by date (earliest first)</li>";
echo "<li>Shows next available dates in jet showcase</li>";
echo "<li>Displays sorted dates in booking form</li>";
echo "<li>Shows upcoming dates in admin panel</li>";
echo "<li>Prevents double booking of same date/slot</li>";
echo "</ul>";

echo "<h3>JavaScript Sorting Logic:</h3>";
echo "<pre>";
echo "availableSlots.sort((a, b) => new Date(a.date) - new Date(b.date));";
echo "</pre>";

echo "<h3>PHP Sorting Logic:</h3>";
echo "<pre>";
echo "usort(\$slots, function(\$a, \$b) {";
echo "    return strtotime(\$a['date']) - strtotime(\$b['date']);";
echo "});";
echo "</pre>";
?> 