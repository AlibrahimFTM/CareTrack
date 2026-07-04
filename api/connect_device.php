<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$userId = $_SESSION['user_id'];
$code = trim($_POST['code'] ?? '');

if (strlen($code) !== 4) {
    echo json_encode(['success' => false, 'error' => 'Invalid code format']);
    exit;
}

// Find active code
$stmt = $pdo->prepare("
    SELECT dl.*, fl.id as link_id 
    FROM device_links dl
    LEFT JOIN family_links fl ON fl.elderly_id = ? AND fl.caregiver_id = dl.caregiver_id AND fl.status = 'active'
    WHERE dl.link_code = ? AND dl.status = 'active' AND dl.expires_at > NOW() AND dl.elderly_id IS NULL
    LIMIT 1
");
$stmt->execute([$userId, $code]);
$deviceLink = $stmt->fetch();

if (!$deviceLink) {
    echo json_encode(['success' => false, 'error' => 'Invalid or expired code']);
    exit;
}

// Link the device to this elderly user
$stmt = $pdo->prepare("UPDATE device_links SET elderly_id = ?, status = 'used' WHERE id = ?");
$stmt->execute([$userId, $deviceLink['id']]);

// Create/update family link
if ($deviceLink['link_id']) {
    // Already linked
} else {
    // Check if caregiver-elderly pair exists
    $stmt = $pdo->prepare("SELECT id FROM family_links WHERE caregiver_id = ? AND elderly_id = ?");
    $stmt->execute([$deviceLink['caregiver_id'], $userId]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO family_links (caregiver_id, elderly_id, link_code, status) VALUES (?, ?, ?, 'active')");
        $stmt->execute([$deviceLink['caregiver_id'], $userId, $code]);
    }
}

echo json_encode(['success' => true]);
