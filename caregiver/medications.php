<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'caregiver') {
    header('Location: ../elderly/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'My Medications');

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

$selectedElderly = $_GET['elderly_id'] ?? ($linkedElderly[0]['id'] ?? 0);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">💊 My Medications</div>

    <?php if (empty($linkedElderly)): ?>
        <p style="text-align: center; color: var(--text-secondary);">
            No elderly profiles linked yet. 
            <a href="add_elderly.php">Add one now</a>.
        </p>
    <?php else: ?>
        <?php if (count($linkedElderly) > 1): ?>
        <form method="GET" class="mb-16">
            <select name="elderly_id" class="form-select" onchange="this.form.submit()">
                <?php foreach ($linkedElderly as $el): ?>
                <option value="<?php echo $el['id']; ?>" <?php echo $selectedElderly == $el['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($el['full_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php endif; ?>

        <?php
        $stmt = $pdo->prepare("
            SELECT m.*,
                (SELECT COUNT(*) FROM dose_logs WHERE medication_id = m.id AND status = 'taken') as times_taken,
                (SELECT COUNT(*) FROM dose_logs WHERE medication_id = m.id AND status = 'missed') as times_missed
            FROM medications m 
            WHERE m.user_id = ? AND m.status = 'active'
            ORDER BY m.time
        ");
        $stmt->execute([$selectedElderly]);
        $medications = $stmt->fetchAll();
        ?>

        <?php if (empty($medications)): ?>
            <p style="text-align: center; color: var(--text-secondary); margin: 24px 0;">
                No medications added yet.
            </p>
            <a href="add_medication.php?elderly_id=<?php echo $selectedElderly; ?>" class="btn btn-primary">
                ➕ Add First Medication
            </a>
        <?php else: ?>
            <?php foreach ($medications as $med): ?>
            <div class="medication-item" id="med-<?php echo $med['id']; ?>">
                <div class="pill-badge" style="background: <?php echo $med['color'] ?? '#3498db'; ?>; border-radius: <?php 
                    echo $med['shape'] === 'round' ? '50%' : ($med['shape'] === 'oval' ? '50%/40%' : '8px'); ?>;">
                </div>
                <div class="med-info">
                    <div class="med-name"><?php echo htmlspecialchars($med['name']); ?></div>
                    <div class="med-details">
                        <?php echo htmlspecialchars($med['dosage']); ?> · 
                        <?php echo date('g:i A', strtotime($med['time'])); ?>
                        <?php if ($med['color']): ?> · <?php echo ucfirst($med['color']); ?><?php endif; ?>
                        <?php if ($med['shape']): ?> · <?php echo ucfirst($med['shape']); ?><?php endif; ?>
                    </div>
                    <div class="text-xs" style="color: var(--text-secondary);">
                        ✅ Taken: <?php echo $med['times_taken']; ?> · ❌ Missed: <?php echo $med['times_missed']; ?>
                    </div>
                </div>
                <div>
                    <button class="btn btn-sm" style="width: auto;" onclick="deleteMedication(<?php echo $med['id']; ?>)">🗑️</button>
                </div>
            </div>
            <?php endforeach; ?>

            <a href="add_medication.php?elderly_id=<?php echo $selectedElderly; ?>" class="btn btn-primary mt-16">
                ➕ Add Another Medication
            </a>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
