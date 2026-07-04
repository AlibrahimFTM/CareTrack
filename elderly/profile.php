<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'elderly') {
    header('Location: ../caregiver/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'My Profile');

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM elderly_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT u.full_name, u.phone 
    FROM family_links fl 
    JOIN users u ON u.id = fl.caregiver_id 
    WHERE fl.elderly_id = ? AND fl.status = 'active'
    LIMIT 1
");
$stmt->execute([$userId]);
$caregiver = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM medications WHERE user_id = ? AND status = 'active'");
$stmt->execute([$userId]);
$medCount = $stmt->fetch()['total'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">👤 My Profile</div>

    <div style="text-align: center; margin-bottom: 20px;">
        <div class="menu-avatar" style="margin: 0 auto 12px; width: 80px; height: 80px; font-size: 36px;">
            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
        </div>
        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
    </div>

    <div class="profile-field">
        <span class="field-label">Age</span>
        <span class="field-value">
            <?php 
            if ($user['date_of_birth']) {
                $dob = new DateTime($user['date_of_birth']);
                $now = new DateTime();
                echo $dob->diff($now)->y . ' years';
            } else {
                echo 'Not set';
            }
            ?>
        </span>
    </div>

    <div class="profile-field">
        <span class="field-label">Gender</span>
        <span class="field-value"><?php echo htmlspecialchars($user['gender'] ?? 'Not set'); ?></span>
    </div>

    <div class="profile-field">
        <span class="field-label">Email</span>
        <span class="field-value"><?php echo htmlspecialchars($user['email']); ?></span>
    </div>

    <?php if ($profile): ?>
    <div class="profile-field">
        <span class="field-label">Allergies</span>
        <span class="field-value"><?php echo htmlspecialchars($profile['allergies'] ?? 'None'); ?></span>
    </div>

    <div class="profile-field">
        <span class="field-label">Condition</span>
        <span class="field-value"><?php echo htmlspecialchars($profile['chronic_diseases'] ?? 'None'); ?></span>
    </div>

    <?php if ($profile['special_conditions']): ?>
    <div class="profile-field">
        <span class="field-label">Special Needs</span>
        <span class="field-value"><?php echo htmlspecialchars($profile['special_conditions']); ?></span>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <div class="profile-field">
        <span class="field-label">Medications</span>
        <span class="field-value"><?php echo $medCount; ?> Active</span>
    </div>

    <?php if ($caregiver): ?>
    <div class="profile-field" style="border-bottom: none;">
        <span class="field-label">Linked with</span>
        <span class="field-value">
            <strong><?php echo htmlspecialchars($caregiver['full_name']); ?></strong>
            <?php if ($caregiver['phone']): ?>
            · <a href="tel:<?php echo htmlspecialchars($caregiver['phone']); ?>"><?php echo htmlspecialchars($caregiver['phone']); ?></a>
            <?php endif; ?>
        </span>
    </div>
    <?php endif; ?>
</div>

<a href="../includes/accessibility.php" class="btn btn-primary">♿ Settings & Accessibility</a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
