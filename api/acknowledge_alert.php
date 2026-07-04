<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$alertId = (int)($_POST['alert_id'] ?? 0);
$userId = $_SESSION['user_id'];

if (!$alertId) {
    echo json_encode(['success' => false, 'error' => 'Missing alert ID']);
    exit;
}

$stmt = $pdo->prepare("UPDATE missed_dose_alerts SET status = 'acknowledged' WHERE id = ? AND caregiver_id = ?");
$stmt->execute([$alertId, $userId]);

if ($stmt->rowCount() === 0) {
    echo json_encode(['success' => false, 'error' => 'Alert not found or already acknowledged']);
    exit;
}

echo json_encode(['success' => true]);
