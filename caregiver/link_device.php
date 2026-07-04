<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'caregiver') {
    header('Location: ../elderly/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'Link Device');

$userId = $_SESSION['user_id'];

// Get linked elderly for code generation
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

<div class="card">
    <div class="card-header">🔗 Link Device</div>

    <p class="text-sm" style="color: var(--text-secondary); margin-bottom: 16px;">
        Generate a code for the elderly user to enter on their device to connect.
    </p>

    <?php if (empty($linkedElderly)): ?>
        <p style="text-align: center; color: var(--text-secondary);">
            You need to <a href="add_elderly.php">add an elderly profile</a> first before linking a device.
        </p>
    <?php else: ?>
    <div class="link-code-display">
        <div class="code" id="displayCode">----</div>
        <div class="expiry">Generate a code to connect</div>
    </div>

    <div class="form-group">
        <label for="elderly_id">Elderly Profile</label>
        <select id="elderly_id" class="form-select" required>
            <?php foreach ($linkedElderly as $elderly): ?>
                <option value="<?php echo (int) $elderly['id']; ?>">
                    <?php echo htmlspecialchars($elderly['full_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="btn-group">
        <button class="btn btn-primary generate-code-btn" onclick="generateLinkCode()">
            🔄 Generate New Code
        </button>
        <button class="btn" onclick="copyCode()">
            📋 Copy
        </button>
    </div>

    <div class="card mt-16" style="box-shadow: var(--shadow-sm);">
        <h3>How to connect:</h3>
        <ol style="padding-left: 20px; font-size: var(--font-size-base);">
            <li style="margin-bottom: 8px;">Generate a code above</li>
            <li style="margin-bottom: 8px;">Tell the elderly user to open CareTrack on their device</li>
            <li style="margin-bottom: 8px;">They enter the 4-digit code in the "Link Device" section</li>
            <li>Connection is established automatically!</li>
        </ol>
    </div>

    <h3 style="margin-top: 20px;">Connected Devices</h3>
    <?php
    $stmt = $pdo->prepare("
        SELECT u.full_name, dl.created_at 
        FROM device_links dl
        JOIN users u ON u.id = dl.elderly_id
        WHERE dl.caregiver_id = ? AND dl.status = 'used'
        ORDER BY dl.created_at DESC
    ");
    $stmt->execute([$userId]);
    $connected = $stmt->fetchAll();

    if (empty($connected)): ?>
        <p class="text-sm" style="color: var(--text-secondary);">No devices currently linked.</p>
    <?php else: ?>
        <?php foreach ($connected as $c): ?>
        <div class="medication-item">
            <span style="font-size: 24px;">📱</span>
            <div class="med-info">
                <div class="med-name"><?php echo htmlspecialchars($c['full_name']); ?></div>
                <div class="med-details">Connected <?php echo date('M j, Y', strtotime($c['created_at'])); ?></div>
            </div>
            <span class="badge badge-taken">Connected</span>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
