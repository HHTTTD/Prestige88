<?php
// Helper functions moved from index.php

// --- Persistent Recent Searches Helper Functions ---
function getRecentSearchesFile($userId) {
    $dir = 'data/recent_searches';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir . '/recent_searches_' . $userId . '.json';
}

function loadRecentSearches($userId) {
    $file = getRecentSearchesFile($userId);
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

function saveRecentSearches($userId, $recentSearches) {
    $file = getRecentSearchesFile($userId);
    file_put_contents($file, json_encode($recentSearches, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Calculate user points from bookings
function calculateUserPoints($userId) {
    $bookings = Database::loadBookings();
    $points = 0;
    foreach ($bookings as $b) {
        if ($b['user_id'] === $userId && in_array($b['status'], ['confirmed', 'completed'])) {
            $points += floor($b['total_cost'] / (defined('POINTS_RATE') ? POINTS_RATE : 100));
        }
    }
    return $points;
}

// Format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Get travel statistics
function getTravelStats($userId) {
    $bookings = Database::loadBookings();
    $totalTrips = 0;
    $totalMiles = 0;
    $totalSpent = 0;
    foreach ($bookings as $b) {
        if ($b['user_id'] === $userId && in_array($b['status'], ['confirmed', 'completed'])) {
            $totalTrips++;
            $totalMiles += ($b['flight_hours'] ?? 0) * 800; // สมมติ 1 ชม. = 800 ไมล์
            $totalSpent += $b['total_cost'] ?? 0;
        }
    }
    return [
        'trips' => $totalTrips,
        'miles' => $totalMiles,
        'spent' => $totalSpent
    ];
}

// Log activity (data changes)
function logActivity($action, $details, $userId = null) {
    $logFile = 'storage/logs/activity.log';
    $user = $userId ? "user_id=$userId" : "user_id=unknown";
    $line = '[' . date('Y-m-d H:i:s') . "] [$user] $action: $details\n";
    file_put_contents($logFile, $line, FILE_APPEND);
}

// --- Popular Destinations CRUD ---
function loadPopularDestinations() {
    $file = 'data/popular_destinations.json';
    if (!file_exists($file)) file_put_contents($file, '[]');
    $data = file_get_contents($file);
    return json_decode($data, true) ?: [];
}
function savePopularDestinations($destinations) {
    $file = 'data/popular_destinations.json';
    file_put_contents($file, json_encode($destinations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function addPopularDestination($name, $image, $price) {
    $destinations = loadPopularDestinations();
    $destinations[] = [
        'id' => uniqid('dest_'),
        'name' => $name,
        'image' => $image,
        'price' => $price
    ];
    savePopularDestinations($destinations);
    return end($destinations);
}
function updatePopularDestination($id, $name, $image, $price) {
    $destinations = loadPopularDestinations();
    foreach ($destinations as &$dest) {
        if ($dest['id'] === $id) {
            $dest['name'] = $name;
            $dest['image'] = $image;
            $dest['price'] = $price;
            break;
        }
    }
    savePopularDestinations($destinations);
}
function deletePopularDestination($id) {
    $destinations = loadPopularDestinations();
    $destinations = array_filter($destinations, function($d) use ($id) { return $d['id'] !== $id; });
    savePopularDestinations(array_values($destinations));
}
?> 