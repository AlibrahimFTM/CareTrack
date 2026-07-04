<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$medicationId = (int)($_POST['medication_id'] ?? 0);

if (!$medicationId) {
    echo json_encode(['success' => false, 'error' => 'Missing medication ID']);
    exit;
}

// Check the medication belongs to a user linked to this caregiver
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'caregiver') {
    $stmt = $pdo->prepare("
        DELETE m FROM medications m
        JOIN family_links fl ON fl.elderly_id = m.user_id
        WHERE m.id = ? AND fl.caregiver_id = ?
    ");
    $stmt->execute([$medicationId, $userId]);
} else {
    $stmt = $pdo->prepare("DELETE FROM medications WHERE id = ? AND user_id = ?");
    $stmt->execute([$medicationId, $userId]);
}

if ($stmt->rowCount() === 0) {
    echo json_encode(['success' => false, 'error' => 'Medication not found']);
    exit;
}

echo json_encode(['success' => true]);
