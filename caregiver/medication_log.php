<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'caregiver') {
    header('Location: ../elderly/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'Medication Log');

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT u.id, u.full_name 
    FROM family_links fl 
    JOIN users u ON u.id = fl.elderly_id 
    WHERE fl.caregiver_id = ? AND fl.status = 'active'
");
$stmt->execute([$userId]);
$linkedElderly = $stmt->fetchAll();

$selectedElderly = $_GET['elderly_id'] ?? ($linkedElderly[0]['id'] ?? 0);
$date = $_GET['date'] ?? date('Y-m-d');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">📋 Medication Log</div>

    <form method="GET" class="mb-16">
        <div class="form-row">
            <?php if (count($linkedElderly) > 1): ?>
            <div class="form-group">
                <select name="elderly_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($linkedElderly as $el): ?>
                    <option value="<?php echo $el['id']; ?>" <?php echo $selectedElderly == $el['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($el['full_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <input type="date" name="date" class="form-input" value="<?php echo $date; ?>" onchange="this.form.submit()">
            </div>
        </div>
    </form>

    <?php
    $stmt = $pdo->prepare("
        SELECT dl.*, m.name, m.dosage, m.color, m.shape, m.time as scheduled_time 
        FROM dose_logs dl
        JOIN medications m ON m.id = dl.medication_id
        WHERE dl.user_id = ? AND dl.scheduled_date = ?
        ORDER BY dl.scheduled_time
    ");
    $stmt->execute([$selectedElderly, $date]);
    $logs = $stmt->fetchAll();
    ?>

    <div style="text-align: center; margin-bottom: 16px;">
        <strong style="font-size: var(--font-size-lg);"><?php echo date('l — F j, Y', strtotime($date)); ?></strong>
    </div>

    <?php if (empty($logs)): ?>
        <p style="text-align: center; color: var(--text-secondary);">No medication records for this date.</p>
    <?php else: ?>
        <?php foreach ($logs as $log): 
            $statusIcon = $log['status'] === 'taken' ? '✅' : ($log['status'] === 'missed' ? '❌' : '⏭️');
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
            <div class="<?php echo $statusClass; ?>" style="font-weight: 800; font-size: var(--font-size-base);">
                <?php echo $statusIcon; ?> <?php echo ucfirst($log['status']); ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="display: flex; gap: 8px; margin-top: 16px;">
        <a href="?elderly_id=<?php echo $selectedElderly; ?>&date=<?php echo date('Y-m-d', strtotime($date . ' -1 day')); ?>" class="btn btn-sm">← Previous Day</a>
        <a href="?elderly_id=<?php echo $selectedElderly; ?>&date=<?php echo date('Y-m-d'); ?>" class="btn btn-sm btn-primary">Today</a>
        <a href="?elderly_id=<?php echo $selectedElderly; ?>&date=<?php echo date('Y-m-d', strtotime($date . ' +1 day')); ?>" class="btn btn-sm">Next Day →</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
