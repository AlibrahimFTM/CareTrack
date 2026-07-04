<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'elderly') {
    header('Location: ../caregiver/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'Need Help?');

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT u.full_name, u.phone 
    FROM family_links fl 
    JOIN users u ON u.id = fl.caregiver_id 
    WHERE fl.elderly_id = ? AND fl.status = 'active'
    LIMIT 1
");
$stmt->execute([$userId]);
$caregiver = $stmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card" style="text-align: center;">
    <p style="font-size: 48px; margin-bottom: 12px;">🆘</p>
    <h2>Do you need help?</h2>
    <p class="text-sm" style="color: var(--text-secondary); margin-bottom: 24px;">
        Press the button below to alert your family immediately.
    </p>

    <button class="emergency-btn" onclick="sendEmergency()">
        <span style="font-size: 48px;">🆘</span>
        Alert Family Now
    </button>

    <?php if ($caregiver): ?>
    <div class="card mt-16" style="background: var(--bg-primary);">
        <h3>Emergency Contact</h3>
        <p style="font-size: var(--font-size-lg); font-weight: 800;">
            <?php echo htmlspecialchars($caregiver['full_name']); ?>
        </p>
        <?php if ($caregiver['phone']): ?>
        <a href="tel:<?php echo htmlspecialchars($caregiver['phone']); ?>" class="btn btn-accent mt-8">
            📞 <?php echo htmlspecialchars($caregiver['phone']); ?>
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <p class="text-sm" style="color: var(--text-secondary); margin-top: 16px;">
        No emergency contact set up. Please contact your caregiver.
    </p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
