<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action !== 'generate') {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit;
}

// Generate 4-digit code
$code = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

// Expire old active codes
$stmt = $pdo->prepare("UPDATE device_links SET status = 'expired' WHERE caregiver_id = ? AND status = 'active'");
$stmt->execute([$userId]);

// Insert new code (expires in 60 seconds)
$stmt = $pdo->prepare("
    INSERT INTO device_links (link_code, caregiver_id, expires_at, status) 
    VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 60 SECOND), 'active')
");
$stmt->execute([$code, $userId]);

echo json_encode(['success' => true, 'code' => $code]);
