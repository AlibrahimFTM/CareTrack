<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'elderly') {
    header('Location: ../caregiver/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'Home');

$userId = $_SESSION['user_id'];

// Get medications for today
$stmt = $pdo->prepare("
    SELECT m.*, dl.status as today_status 
    FROM medications m
    LEFT JOIN dose_logs dl ON dl.medication_id = m.id 
        AND dl.scheduled_date = CURDATE() 
        AND dl.scheduled_time = m.time
    WHERE m.user_id = ? AND m.status = 'active'
    ORDER BY m.time
");
$stmt->execute([$userId]);
$medications = $stmt->fetchAll();

$taken = 0;
$missed = 0;
$now = date('H:i:s');
foreach ($medications as $med) {
    if ($med['today_status'] === 'taken') $taken++;
    elseif ($med['today_status'] === 'missed') $missed++;
}

// Get caregiver info
$stmt = $pdo->prepare("
    SELECT u.full_name, u.phone 
    FROM family_links fl 
    JOIN users u ON u.id = fl.caregiver_id 
    WHERE fl.elderly_id = ? AND fl.status = 'active'
    LIMIT 1
");
$stmt->execute([$userId]);
$caregiver = $stmt->fetch();

// Check for active emergency alerts
$stmt = $pdo->prepare("SELECT id FROM emergency_alerts WHERE elderly_id = ? AND status = 'active' LIMIT 1");
$stmt->execute([$userId]);
$activeEmergency = $stmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="greeting">Good morning, <?php echo explode(' ', $_SESSION['full_name'])[0]; ?> 🌅</div>
<div class="date-display"><?php echo date('l, F j, Y'); ?></div>

<?php if ($activeEmergency): ?>
<div class="alert alert-danger" style="text-align: center; font-size: var(--font-size-lg);">
    🆘 Emergency alert sent to your family. Help is on the way.
    <br>
    <button class="btn btn-sm mt-8" style="background: #fff; color: var(--color-missed);" onclick="resolveEmergency(<?php echo $activeEmergency['id']; ?>)">
        Cancel Emergency
    </button>
</div>
<?php endif; ?>

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
        <div class="label">Next</div>
    </div>
</div>

<?php if ($taken > 0 && $missed === 0 && $taken >= count($medications)): ?>
<div class="card all-done" style="background: var(--color-taken); color: #fff;">
    <div class="done-icon">✅</div>
    <h2 style="color: #fff;">All done for today!</h2>
    <p style="color: #fff; font-size: var(--font-size-lg);">You have taken all your medications today. Great job!</p>
    <p style="color: #fff; opacity: 0.9;"><?php echo $taken; ?> / <?php echo count($medications); ?> Medications Taken</p>
    <?php if (count($medications) > 0): ?>
    <p class="text-sm" style="color: #fff; opacity: 0.7; margin-top: 8px;">
        Next reminder tomorrow at <?php echo date('g:i A', strtotime($medications[0]['time'])); ?>
    </p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (!empty($medications)): ?>
    <?php foreach ($medications as $med): 
        $status = $med['today_status'] ?? 'upcoming';
        $timeRemaining = strtotime($med['time']) - strtotime($now);
        $showConfirmBtn = ($status !== 'taken' && $status !== 'missed' && $timeRemaining < 3600 && $timeRemaining > -900);

        if ($status === 'taken') {
            $badgeClass = 'badge-taken';
            $badgeText = '✓ Taken';
        } elseif ($status === 'missed') {
            $badgeClass = 'badge-missed';
            $badgeText = '✗ Missed';
        } else {
            $badgeClass = 'badge-upcoming';
            $badgeText = '⏳ ' . date('g:i A', strtotime($med['time']));
        }
    ?>
    <div class="card" style="<?php echo $showConfirmBtn ? 'border-color: var(--color-accent); border-width: 4px;' : ''; ?>">
        <div class="medication-item" style="border: none; padding: 0; margin-bottom: 8px; box-shadow: none;">
            <div class="pill-badge" style="background: <?php echo $med['color'] ?? '#3498db'; ?>; border-radius: <?php 
                echo $med['shape'] === 'round' ? '50%' : ($med['shape'] === 'oval' ? '50%/40%' : '8px'); ?>;">
            </div>
            <div class="med-info">
                <div class="med-name" style="font-size: var(--font-size-lg);"><?php echo htmlspecialchars($med['name']); ?></div>
                <div class="med-details">
                    <?php echo htmlspecialchars($med['dosage']); ?> · 
                    <?php echo ucfirst($med['color'] ?? ''); ?> · 
                    <?php echo ucfirst($med['shape'] ?? ''); ?>
                </div>
                <div class="med-details" style="font-size: var(--font-size-lg); font-weight: 800;">
                    🕐 <?php echo date('g:i A', strtotime($med['time'])); ?>
                </div>
            </div>
            <div>
                <span class="badge <?php echo $badgeClass; ?>" style="font-size: var(--font-size-sm);"><?php echo $badgeText; ?></span>
            </div>
        </div>

        <?php if ($med['voice_reminder_path'] && $showConfirmBtn): ?>
        <div class="card" style="box-shadow: none; border-color: var(--color-blue); background: #e8f4fd; margin-bottom: 12px;">
            <p style="font-weight: 700;">🔊 Family Voice Reminder:</p>
            <p style="font-style: italic; font-size: var(--font-size-lg);">"<?php echo htmlspecialchars($med['voice_reminder_path']); ?>"</p>
        </div>
        <?php endif; ?>

        <?php if ($showConfirmBtn): ?>
        <button class="btn btn-taken" onclick="confirmDose(<?php echo $med['id']; ?>)">
            💊 Confirm Dose
        </button>
        <?php endif; ?>

        <?php if ($status === 'missed'): ?>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-sm btn-missed" onclick="confirmDose(<?php echo $med['id']; ?>)" style="flex: 1;">
                I Took My Medication
            </button>
            <a href="tel:<?php echo htmlspecialchars($caregiver['phone'] ?? ''); ?>" class="btn btn-sm btn-accent" style="flex: 1;">
                📞 Call Family
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php else: ?>
<div class="card" style="text-align: center;">
    <p style="font-size: 48px; margin-bottom: 12px;">💊</p>
    <h2>No Medications Yet</h2>
    <p class="text-sm" style="color: var(--text-secondary);">Your family will add medications for you.</p>
</div>
<?php endif; ?>

<?php if ($caregiver): ?>
<div class="card" style="text-align: center; background: var(--color-primary); color: #fff;">
    <p style="color: #fff;">👨‍👩‍👧 Connected to: <strong><?php echo htmlspecialchars($caregiver['full_name']); ?></strong></p>
    <?php if ($caregiver['phone']): ?>
    <a href="tel:<?php echo htmlspecialchars($caregiver['phone']); ?>" class="btn btn-accent mt-8">📞 Call <?php echo explode(' ', $caregiver['full_name'])[0]; ?></a>
    <?php endif; ?>
</div>
<?php endif; ?>

<div style="text-align: center; margin-top: 16px;">
    <a href="emergency.php" class="btn btn-missed" style="animation: pulse-emergency 2s infinite;">
        🆘 Need Help?
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
