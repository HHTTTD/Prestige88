<?php
// api/notifications.php

if (
    isset($_POST['action']) && $_POST['action'] === 'send_notification'
    && class_exists('AuthController') && AuthController::isLoggedIn()
) {
    header('Content-Type: application/json');
    $currentUser = AuthController::getCurrentUser();
    if ($currentUser['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Permission denied.']);
        exit;
    }

    $userId = $_POST['user_id'] ?? '';
    $message = trim($_POST['message'] ?? '');
    $link = trim($_POST['link'] ?? '');

    if (empty($userId) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'User ID and message are required.']);
        exit;
    }

    if (!class_exists('Notification')) {
        require_once __DIR__ . '/../models/Notification.php';
    }
    
    // By default, link to the homepage if no link is provided.
    $notificationLink = !empty($link) ? $link : '?page=home';
    Notification::create($userId, $message, $notificationLink);

    echo json_encode(['success' => true]);
    exit;
} 