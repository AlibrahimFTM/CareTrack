<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'caregiver') {
    header('Location: ../elderly/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'Missed Dose Alerts');

$userId = $_SESSION['user_id'];

// Get pending missed dose alerts
$stmt = $pdo->prepare("
    SELECT mda.*, m.name as med_name, m.dosage, u.full_name as elderly_name, u.phone
    FROM missed_dose_alerts mda
    JOIN medications m ON m.id = mda.medication_id
    JOIN users u ON u.id = mda.elderly_id
    WHERE mda.caregiver_id = ? AND mda.status = 'pending'
    ORDER BY mda.alert_time DESC
");
$stmt->execute([$userId]);
$pendingAlerts = $stmt->fetchAll();

// Get active emergency alerts
$stmt = $pdo->prepare("
    SELECT ea.*, u.full_name as elderly_name
    FROM emergency_alerts ea
    JOIN users u ON u.id = ea.elderly_id
    WHERE ea.caregiver_id = ? AND ea.status = 'active'
    ORDER BY ea.alert_time DESC
");
$stmt->execute([$userId]);
$emergencyAlerts = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (!empty($emergencyAlerts)): ?>
    <?php foreach ($emergencyAlerts as $alert): ?>
    <div class="alert alert-danger" style="font-size: var(--font-size-lg); text-align: center;">
        🆘 EMERGENCY ALERT from <?php echo htmlspecialchars($alert['elderly_name']); ?>
        <br>
        <small><?php echo date('g:i A', strtotime($alert['alert_time'])); ?></small>
        <br>
        <button class="btn btn-sm btn-primary mt-8" onclick="resolveEmergency(<?php echo $alert['id']; ?>)">
            Mark as Resolved
        </button>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="card">
    <div class="card-header">🔔 Missed Dose Alerts</div>

    <?php if (empty($pendingAlerts)): ?>
        <div style="text-align: center; padding: 24px;">
            <p style="font-size: 48px; margin-bottom: 12px;">✅</p>
            <h2>No Pending Alerts</h2>
            <p class="text-sm" style="color: var(--text-secondary);">All medications are being taken on schedule.</p>
        </div>
    <?php else: ?>
        <?php foreach ($pendingAlerts as $alert): ?>
        <div class="card" style="border-color: var(--color-missed);">
            <div style="display: flex; align-items: start; gap: 12px;">
                <span style="font-size: 36px;">⚠️</span>
                <div style="flex: 1;">
                    <h3>Missed Dose: <?php echo htmlspecialchars($alert['med_name']); ?> <?php echo htmlspecialchars($alert['dosage']); ?></h3>
                    <p class="text-sm">
                        <?php echo htmlspecialchars($alert['elderly_name']); ?> · 
                        Scheduled: <?php echo date('g:i A', strtotime($alert['alert_time'])); ?>
                    </p>
                    <p class="text-sm" style="color: var(--text-missed);">
                        No confirmation received within 15 minutes. Dose may have been missed.
                    </p>
                    <div class="btn-group mt-16">
                        <button class="btn btn-sm btn-primary" onclick="acknowledgeAlert(<?php echo $alert['id']; ?>)">
                            Follow Up Now
                        </button>
                        <?php if (!empty($alert['phone'])): ?>
                        <a href="tel:<?php echo htmlspecialchars($alert['phone']); ?>" class="btn btn-sm btn-accent">
                            📞 Call Family
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
