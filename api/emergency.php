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

if ($action === 'alert') {
    // Send emergency alert to all linked caregivers
    $stmt = $pdo->prepare("
        SELECT fl.caregiver_id 
        FROM family_links fl 
        WHERE fl.elderly_id = ? AND fl.status = 'active'
    ");
    $stmt->execute([$userId]);
    $caregivers = $stmt->fetchAll();

    if (empty($caregivers)) {
        echo json_encode(['success' => false, 'error' => 'No caregiver linked']);
        exit;
    }

    foreach ($caregivers as $cg) {
        // Check if there's already an active alert
        $stmt = $pdo->prepare("SELECT id FROM emergency_alerts WHERE elderly_id = ? AND caregiver_id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$userId, $cg['caregiver_id']]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO emergency_alerts (elderly_id, caregiver_id, status) VALUES (?, ?, 'active')");
            $stmt->execute([$userId, $cg['caregiver_id']]);
        }
    }

    echo json_encode(['success' => true]);
} elseif ($action === 'resolve') {
    $alertId = (int)($_POST['alert_id'] ?? 0);
    if (!$alertId) {
        echo json_encode(['success' => false, 'error' => 'Missing alert ID']);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE emergency_alerts SET status = 'resolved', resolved_at = NOW() WHERE id = ?");
    $stmt->execute([$alertId]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
