<?php
require_once __DIR__ . '/../config/database.php';

define('PAGE_TITLE', 'Forgot Password');
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            // In production, send email. For prototype, show success.
            $success = 'If this email is registered, you will receive reset instructions shortly.';
        } else {
            // Don't reveal if email exists
            $success = 'If this email is registered, you will receive reset instructions shortly.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CareTrack</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-logo">
        <span class="logo-icon">💊</span>
        <h1>CareTrack</h1>
        <p>Reset your password</p>
    </div>

    <div class="auth-card">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <a href="login.php" class="btn btn-primary mt-16">Back to Login</a>
        <?php else: ?>
        <p class="text-sm" style="margin-bottom: 20px; color: var(--text-secondary);">
            No worries, we'll send you reset instructions.
        </p>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
        <div class="auth-link">
            <a href="login.php">Back to Login</a>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
