<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'elderly') {
    header('Location: ../caregiver/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'Link Device');

$userId = $_SESSION['user_id'];

// Check if already linked
$stmt = $pdo->prepare("
    SELECT u.full_name 
    FROM family_links fl 
    JOIN users u ON u.id = fl.caregiver_id 
    WHERE fl.elderly_id = ? AND fl.status = 'active'
    LIMIT 1
");
$stmt->execute([$userId]);
$existingLink = $stmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">🔗 Link Device</div>

    <?php if ($existingLink): ?>
        <div class="alert alert-success" style="text-align: center;">
            ✅ Connected to <strong><?php echo htmlspecialchars($existingLink['full_name']); ?></strong>
        </div>
        <p class="text-sm" style="color: var(--text-secondary); text-align: center;">
            Your device is already linked with your family member.
        </p>
    <?php else: ?>
        <p class="text-sm" style="color: var(--text-secondary); margin-bottom: 20px;">
            Enter the 4-digit code from your family member's device.
        </p>

        <form onsubmit="event.preventDefault(); connectDevice();">
            <div class="code-dots">
                <input type="text" id="link_code" class="form-input" 
                    placeholder="____" maxlength="4" 
                    style="text-align: center; font-size: 36px; letter-spacing: 12px; font-weight: 900;"
                    autocomplete="off">
            </div>

            <p class="text-xs" style="color: var(--text-secondary); text-align: center; margin-bottom: 20px;">
                Ask your family for the code
            </p>

            <button type="submit" class="btn btn-primary">Connect</button>
        </form>

        <div class="card mt-16" style="background: var(--bg-primary);">
            <h3>How to connect:</h3>
            <ol style="padding-left: 20px;">
                <li style="margin-bottom: 8px;">Ask your family member to open CareTrack</li>
                <li style="margin-bottom: 8px;">They tap "Link Device" in the menu</li>
                <li style="margin-bottom: 8px;">They generate a 4-digit code</li>
                <li>Enter the code above to connect</li>
            </ol>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
