<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'caregiver') {
    header('Location: ../elderly/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'Dashboard');

$userId = $_SESSION['user_id'];

// Get linked elderly
$stmt = $pdo->prepare("
    SELECT u.id, u.full_name 
    FROM family_links fl 
    JOIN users u ON u.id = fl.elderly_id 
    WHERE fl.caregiver_id = ? AND fl.status = 'active'
");
$stmt->execute([$userId]);
$linkedElderly = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="greeting">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?> 👋</div>
<div class="date-display"><?php echo date('l, F j, Y'); ?></div>

<?php if (empty($linkedElderly)): ?>
<div class="card" style="text-align: center;">
    <p style="font-size: 48px; margin-bottom: 12px;">👤</p>
    <h2>No Family Connected Yet</h2>
    <p class="text-sm" style="color: var(--text-secondary); margin-bottom: 16px;">
        Add an elderly profile to start managing their medications.
    </p>
    <a href="add_elderly.php" class="btn btn-primary">Add Elderly Profile</a>
    <div class="auth-divider">or</div>
    <a href="link_device.php" class="btn">Link a Device</a>
</div>
<?php else: ?>
    <?php foreach ($linkedElderly as $elder): ?>
    <div class="card">
        <div class="card-header">👤 <?php echo htmlspecialchars($elder['full_name']); ?></div>
        
        <?php
        // Get today's medications for this elderly
        $stmt = $pdo->prepare("
            SELECT m.*, dl.status as today_status 
            FROM medications m
            LEFT JOIN dose_logs dl ON dl.medication_id = m.id 
                AND dl.scheduled_date = CURDATE() 
                AND dl.scheduled_time = m.time
            WHERE m.user_id = ? AND m.status = 'active'
            ORDER BY m.time
        ");
        $stmt->execute([$elder['id']]);
        $medications = $stmt->fetchAll();

        // Count statistics
        $taken = 0;
        $missed = 0;
        $upcoming = 0;
        $now = date('H:i:s');
        foreach ($medications as $med) {
            if ($med['today_status'] === 'taken') $taken++;
            elseif ($med['today_status'] === 'missed') $missed++;
            elseif ($med['time'] <= $now) $upcoming++;
            else $upcoming++;
        }
        ?>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="number text-taken"><?php echo $taken; ?></div>
                <div class="label">Taken</div>
            </div>
            <div class="summary-card">
                <div class="number text-missed"><?php echo $missed; ?></div>
                <div class="label">Missed</div>
            </div>
            <div class="summary-card">
                <div class="number text-upcoming"><?php echo count($medications) - $taken - $missed; ?></div>
                <div class="label">Pending</div>
            </div>
        </div>

        <?php if ($taken > 0 && $missed === 0 && $taken >= count($medications)): ?>
        <div class="all-done">
            <div class="done-icon">✅</div>
            <h2>All done for today!</h2>
            <p><?php echo $taken; ?> / <?php echo count($medications); ?> Medications Taken</p>
        </div>
        <?php endif; ?>

        <?php if (!empty($medications)): ?>
            <?php foreach ($medications as $med): 
                $status = $med['today_status'] ?? 'upcoming';
                $badgeClass = $status === 'taken' ? 'badge-taken' : ($status === 'missed' ? 'badge-missed' : 'badge-upcoming');
                $badgeText = $status === 'taken' ? '✓ Taken' : ($status === 'missed' ? '✗ Missed' : '⏳ Upcoming');
            ?>
            <div class="medication-item" id="med-<?php echo $med['id']; ?>">
                <div class="pill-badge" style="background: <?php echo $med['color'] ?? '#3498db'; ?>;"></div>
                <div class="med-info">
                    <div class="med-name"><?php echo htmlspecialchars($med['name']); ?></div>
                    <div class="med-details"><?php echo htmlspecialchars($med['dosage']); ?> · <?php echo date('g:i A', strtotime($med['time'])); ?></div>
                </div>
                <div>
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $badgeText; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: var(--text-secondary);">No medications scheduled today.</p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="card" style="background: var(--color-primary); color: #fff;">
    <h3 style="color: #fff;">💡 Quick Actions</h3>
    <div class="btn-group mt-16" style="flex-direction: column; gap: 8px;">
        <a href="add_medication.php" class="btn" style="background: #fff; color: var(--color-primary);">➕ Add Medication</a>
        <a href="medication_log.php" class="btn" style="background: #fff; color: var(--color-primary);">📋 View Medication Log</a>
        <a href="alerts.php" class="btn" style="background: var(--color-accent); color: #fff;">🔔 Check Alerts</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
