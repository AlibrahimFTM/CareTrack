<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$medicationId = (int)($_POST['medication_id'] ?? 0);
$userId = $_SESSION['user_id'];
$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');
$currentTime = date('H:i:s');

if (!$medicationId) {
    echo json_encode(['success' => false, 'error' => 'Missing medication ID']);
    exit;
}

// Verify the medication belongs to this user (or their linked elderly)
$stmt = $pdo->prepare("SELECT m.*, u.role FROM medications m JOIN users u ON u.id = m.user_id WHERE m.id = ?");
$stmt->execute([$medicationId]);
$med = $stmt->fetch();

if (!$med) {
    echo json_encode(['success' => false, 'error' => 'Medication not found']);
    exit;
}

// For caregiver: confirm dose on behalf of elderly
$targetUserId = $med['user_id'];

// Check if a log already exists for today
$stmt = $pdo->prepare("SELECT id, status FROM dose_logs WHERE medication_id = ? AND scheduled_date = ? AND scheduled_time = ?");
$stmt->execute([$medicationId, $today, $med['time']]);
$existing = $stmt->fetch();

if ($existing) {
    if ($existing['status'] === 'taken') {
        echo json_encode(['success' => false, 'error' => 'Dose already confirmed for today']);
        exit;
    }
    // Update existing missed -> taken
    $stmt = $pdo->prepare("UPDATE dose_logs SET status = 'taken', taken_at = ? WHERE id = ?");
    $stmt->execute([$now, $existing['id']]);
} else {
    // Create new taken log
    $stmt = $pdo->prepare("INSERT INTO dose_logs (medication_id, user_id, scheduled_date, scheduled_time, status, taken_at) VALUES (?, ?, ?, ?, 'taken', ?)");
    $stmt->execute([$medicationId, $targetUserId, $today, $med['time'], $now]);
}

// Update any pending missed dose alerts for this medication
$stmt = $pdo->prepare("UPDATE missed_dose_alerts SET status = 'resolved' WHERE medication_id = ? AND elderly_id = ? AND status = 'pending'");
$stmt->execute([$medicationId, $targetUserId]);

echo json_encode(['success' => true]);
