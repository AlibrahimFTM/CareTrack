<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'elderly') {
    header('Location: ../caregiver/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'My Medications');

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT m.*,
        (SELECT COUNT(*) FROM dose_logs WHERE medication_id = m.id AND status = 'taken') as times_taken,
        (SELECT COUNT(*) FROM dose_logs WHERE medication_id = m.id AND status = 'missed') as times_missed
    FROM medications m 
    WHERE m.user_id = ? AND m.status = 'active'
    ORDER BY m.time
");
$stmt->execute([$userId]);
$medications = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">💊 My Medications</div>

    <?php if (empty($medications)): ?>
        <p style="text-align: center; color: var(--text-secondary); padding: 24px;">No medications scheduled.</p>
    <?php else: ?>
        <?php
        $now = date('H:i:s');
        $todayDate = date('Y-m-d');
        ?>
        <?php foreach ($medications as $med): 
            // Check today's status
            $stmt2 = $pdo->prepare("SELECT status FROM dose_logs WHERE medication_id = ? AND scheduled_date = ? AND scheduled_time = ? LIMIT 1");
            $stmt2->execute([$med['id'], $todayDate, $med['time']]);
            $todayLog = $stmt2->fetch();
            $todayStatus = $todayLog['status'] ?? null;
        ?>
        <div class="medication-item" id="med-<?php echo $med['id']; ?>">
            <div class="pill-badge" style="background: <?php echo $med['color'] ?? '#3498db'; ?>; border-radius: <?php 
                echo $med['shape'] === 'round' ? '50%' : ($med['shape'] === 'oval' ? '50%/40%' : '8px'); ?>;">
            </div>
            <div class="med-info">
                <div class="med-name" style="font-size: var(--font-size-lg);"><?php echo htmlspecialchars($med['name']); ?></div>
                <div class="med-details">
                    <?php echo htmlspecialchars($med['dosage']); ?> · 
                    <?php echo date('g:i A', strtotime($med['time'])); ?>
                    <?php if ($med['color']): ?> · <?php echo ucfirst($med['color']); ?><?php endif; ?>
                    <?php if ($med['shape']): ?> · <?php echo ucfirst($med['shape']); ?><?php endif; ?>
                </div>
                <div class="text-sm" style="margin-top: 4px;">
                    ✅ Taken: <?php echo $med['times_taken']; ?> times · ❌ Missed: <?php echo $med['times_missed']; ?> times
                </div>
            </div>
            <div>
                <?php if ($todayStatus === 'taken'): ?>
                    <span class="badge badge-taken">✓ Taken</span>
                <?php elseif ($todayStatus === 'missed'): ?>
                    <span class="badge badge-missed">✗ Missed</span>
                <?php else: ?>
                    <?php if (abs(strtotime($med['time']) - strtotime($now)) < 3600): ?>
                        <button class="btn btn-sm btn-taken" style="width: auto;" onclick="confirmDose(<?php echo $med['id']; ?>)">💊 Take</button>
                    <?php else: ?>
                        <span class="badge badge-upcoming">⏳ <?php echo date('g:i A', strtotime($med['time'])); ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
