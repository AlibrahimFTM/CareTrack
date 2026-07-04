<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'elderly') {
    header('Location: ../caregiver/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'My History');

$userId = $_SESSION['user_id'];
$date = $_GET['date'] ?? date('Y-m-d');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">📋 My History</div>

    <form method="GET" class="mb-16">
        <input type="date" name="date" class="form-input" value="<?php echo $date; ?>" onchange="this.form.submit()">
    </form>

    <div style="text-align: center; margin-bottom: 16px;">
        <strong style="font-size: var(--font-size-lg);"><?php echo date('l — F j, Y', strtotime($date)); ?></strong>
    </div>

    <?php
    $stmt = $pdo->prepare("
        SELECT dl.*, m.name, m.dosage, m.color, m.shape, m.time as scheduled_time
        FROM dose_logs dl
        JOIN medications m ON m.id = dl.medication_id
        WHERE dl.user_id = ? AND dl.scheduled_date = ?
        ORDER BY dl.scheduled_time
    ");
    $stmt->execute([$userId, $date]);
    $logs = $stmt->fetchAll();
    ?>

    <?php if (empty($logs)): ?>
        <p style="text-align: center; color: var(--text-secondary); padding: 24px;">No records for this date.</p>
    <?php else: ?>
        <?php foreach ($logs as $log): 
            $icon = $log['status'] === 'taken' ? '✅' : ($log['status'] === 'missed' ? '❌' : '⏭️');
            $statusClass = $log['status'] === 'taken' ? 'text-taken' : 'text-missed';
        ?>
        <div class="medication-item">
            <div class="pill-badge" style="background: <?php echo $log['color'] ?? '#3498db'; ?>;"></div>
            <div class="med-info">
                <div class="med-name"><?php echo htmlspecialchars($log['name']); ?> <?php echo htmlspecialchars($log['dosage']); ?></div>
                <div class="med-details">
                    <?php echo date('g:i A', strtotime($log['scheduled_time'])); ?>
                    <?php if ($log['taken_at']): ?>
                        · Taken at <?php echo date('g:i A', strtotime($log['taken_at'])); ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="<?php echo $statusClass; ?>" style="font-weight: 800;">
                <?php echo $icon; ?> <?php echo ucfirst($log['status']); ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="display: flex; gap: 8px; margin-top: 16px;">
        <a href="?date=<?php echo date('Y-m-d', strtotime($date . ' -1 day')); ?>" class="btn btn-sm">← Yesterday</a>
        <a href="?date=<?php echo date('Y-m-d'); ?>" class="btn btn-sm btn-primary">Today</a>
        <a href="?date=<?php echo date('Y-m-d', strtotime($date . ' +1 day')); ?>" class="btn btn-sm">Tomorrow →</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
