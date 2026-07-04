<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'caregiver') {
    header('Location: ../elderly/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'Voice Reminders');

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT u.id, u.full_name 
    FROM family_links fl 
    JOIN users u ON u.id = fl.elderly_id 
    WHERE fl.caregiver_id = ? AND fl.status = 'active'
");
$stmt->execute([$userId]);
$linkedElderly = $stmt->fetchAll();

$selectedElderly = $_GET['elderly_id'] ?? ($_POST['elderly_id'] ?? ($linkedElderly[0]['id'] ?? 0));

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_recording'])) {
    $medication_id = $_POST['medication_id'] ?? 0;
    $recording_text = trim($_POST['recording_text'] ?? '');

    if ($medication_id && $recording_text) {
        // In a real app, we'd save an audio file. For prototype, save the text.
        $stmt = $pdo->prepare("UPDATE medications SET voice_reminder_path = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$recording_text, $medication_id, $selectedElderly]);
        $msg = 'Voice reminder saved successfully! ✔';
    } else {
        $error = 'Please select a medication and enter reminder text.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">🎤 Voice Reminders</div>

    <p class="text-sm" style="color: var(--text-secondary); margin-bottom: 16px;">
        Record a familiar voice message for each medication. Your voice helps them remember with comfort.
    </p>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (empty($linkedElderly)): ?>
        <p style="text-align: center; color: var(--text-secondary);">
            <a href="add_elderly.php">Add an elderly profile</a> first.
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
            SELECT * FROM medications 
            WHERE user_id = ? AND status = 'active'
            ORDER BY name
        ");
        $stmt->execute([$selectedElderly]);
        $medications = $stmt->fetchAll();

        if (empty($medications)): ?>
            <p style="text-align: center; color: var(--text-secondary);">
                No medications added yet. <a href="add_medication.php?elderly_id=<?php echo $selectedElderly; ?>">Add one</a>.
            </p>
        <?php else: ?>
            <?php foreach ($medications as $med): ?>
            <div class="card" style="box-shadow: var(--shadow-sm);">
                <div class="medication-item" style="border: none; padding: 0; margin-bottom: 12px; box-shadow: none;">
                    <div class="pill-badge" style="background: <?php echo $med['color'] ?? '#3498db'; ?>;"></div>
                    <div class="med-info">
                        <div class="med-name"><?php echo htmlspecialchars($med['name']); ?> <?php echo htmlspecialchars($med['dosage']); ?></div>
                        <div class="med-details"><?php echo date('g:i A', strtotime($med['time'])); ?></div>
                    </div>
                    <?php if ($med['voice_reminder_path']): ?>
                    <span class="badge badge-taken">✅ Recorded</span>
                    <?php endif; ?>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="elderly_id" value="<?php echo $selectedElderly; ?>">
                    <input type="hidden" name="medication_id" value="<?php echo $med['id']; ?>">
                    
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label for="recording_<?php echo $med['id']; ?>">Reminder Message</label>
                        <textarea id="recording_<?php echo $med['id']; ?>" name="recording_text" class="form-textarea" 
                            placeholder="e.g. Please take one Aspirin 100 mg - Blue, Round pill"
                            style="min-height: 60px;"><?php echo htmlspecialchars($med['voice_reminder_path'] ?? 'Please take one ' . $med['name'] . ' ' . $med['dosage'] . ' — ' . ucfirst($med['color'] ?? '') . ', ' . ucfirst($med['shape'] ?? '') . ' pill'); ?></textarea>
                    </div>
                    <button type="submit" name="save_recording" class="btn btn-sm btn-primary" style="width: auto;">
                        <?php echo $med['voice_reminder_path'] ? '🔄 Update' : '🎤 Save Recording'; ?>
                    </button>
                </form>

                <?php if ($med['voice_reminder_path']): ?>
                <div class="mt-8" style="background: var(--bg-primary); padding: 8px 12px; border-radius: 8px; border: 2px solid var(--border-color);">
                    <p class="text-sm" style="margin-bottom: 0;">🔊 Family Recording:</p>
                    <p style="font-style: italic; margin-top: 4px;">"<?php echo htmlspecialchars($med['voice_reminder_path']); ?>"</p>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
