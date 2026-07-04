<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('PAGE_TITLE') ? PAGE_TITLE . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%3E%3Crect%20width='64'%20height='64'%20rx='16'%20fill='%232d8d89'/%3E%3Ccircle%20cx='32'%20cy='32'%20r='22'%20fill='none'%20stroke='%23d6c38f'%20stroke-width='6'/%3E%3Cg%20transform='translate(18%2018)'%3E%3Crect%20x='0'%20y='0'%20width='28'%20height='28'%20rx='14'%20fill='%235eb6a0'/%3E%3Cpath%20d='M8%208l12%2012'%20stroke='%23ffffff'%20stroke-width='5'%20stroke-linecap='round'/%3E%3Cpath%20d='M20%208l-12%2012'%20stroke='%23d6c38f'%20stroke-width='5'%20stroke-linecap='round'/%3E%3C/g%3E%3C/svg%3E" type="image/svg+xml">
    <link rel="shortcut icon" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%3E%3Crect%20width='64'%20height='64'%20rx='16'%20fill='%232d8d89'/%3E%3Ccircle%20cx='32'%20cy='32'%20r='22'%20fill='none'%20stroke='%23d6c38f'%20stroke-width='6'/%3E%3Cg%20transform='translate(18%2018)'%3E%3Crect%20x='0'%20y='0'%20width='28'%20height='28'%20rx='14'%20fill='%235eb6a0'/%3E%3Cpath%20d='M8%208l12%2012'%20stroke='%23ffffff'%20stroke-width='5'%20stroke-linecap='round'/%3E%3Cpath%20d='M20%208l-12%2012'%20stroke='%23d6c38f'%20stroke-width='5'%20stroke-linecap='round'/%3E%3C/g%3E%3C/svg%3E" type="image/svg+xml">
    <link rel="stylesheet" href="../css/style.css">
    <?php if (isset($_SESSION['user_id'])): ?>
    <?php
    // Load user's accessibility settings
    $stmt = $pdo->prepare("SELECT * FROM accessibility_settings WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $access = $stmt->fetch();
    ?>
    <style>
        <?php if ($access && $access['text_size'] === 'large'): ?>
        :root { --font-size-base: 20px; --font-size-lg: 28px; --font-size-xl: 36px; --font-size-sm: 16px; }
        <?php elseif ($access && $access['text_size'] === 'medium'): ?>
        :root { --font-size-base: 18px; --font-size-lg: 24px; --font-size-xl: 30px; --font-size-sm: 14px; }
        <?php else: ?>
        :root { --font-size-base: 16px; --font-size-lg: 22px; --font-size-xl: 28px; --font-size-sm: 13px; }
        <?php endif; ?>
        <?php if ($access && $access['bold_colors']): ?>
        .btn, .card, .nav-link, .pill-icon { font-weight: 800 !important; }
        <?php endif; ?>
    </style>
    <?php endif; ?>
</head>
<body>
<div class="app-container">
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="top-nav">
        <button class="nav-toggle" onclick="toggleMenu()" aria-label="Toggle menu">☰</button>
        <div class="nav-brand"><?php echo SITE_NAME; ?></div>
        <div class="nav-user"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </nav>
    <div class="side-menu" id="sideMenu">
        <button class="close-menu" onclick="toggleMenu()">&times;</button>
        <div class="menu-header">
            <div class="menu-avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
            <div class="menu-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
            <div class="menu-role"><?php echo ucfirst($_SESSION['role']); ?></div>
        </div>
        <ul class="menu-items">
            <?php if ($_SESSION['role'] === 'caregiver'): ?>
            <li><a href="../caregiver/dashboard.php">🏠 Home</a></li>
            <li><a href="../caregiver/medications.php">💊 My Medications</a></li>
            <li><a href="../caregiver/medication_log.php">📋 Medication Log</a></li>
            <li><a href="../caregiver/trends.php">📊 View Trends</a></li>
            <li><a href="../caregiver/add_medication.php">➕ Add Medication</a></li>
            <li><a href="../caregiver/voice_reminders.php">🎤 Voice Reminders</a></li>
            <li><a href="../caregiver/link_device.php">🔗 Link Device</a></li>
            <li><a href="../caregiver/add_elderly.php">👤 Add Elderly Profile</a></li>
            <li><a href="../caregiver/alerts.php">🔔 Missed Dose Alerts</a></li>
            <?php else: ?>
            <li><a href="../elderly/dashboard.php">🏠 Home</a></li>
            <li><a href="../elderly/medications.php">💊 My Medications</a></li>
            <li><a href="../elderly/history.php">📋 My History</a></li>
            <li><a href="../elderly/profile.php">👤 My Profile</a></li>
            <li><a href="../elderly/link_device.php">🔗 Link Device</a></li>
            <li><a href="../elderly/emergency.php">🆘 Need Help?</a></li>
            <?php endif; ?>
            <li><a href="../includes/accessibility.php">♿ Accessibility Settings</a></li>
            <li><a href="../auth/logout.php" class="logout-link">🚪 Logout</a></li>
        </ul>
    </div>
    <?php endif; ?>
    <div class="main-content">
