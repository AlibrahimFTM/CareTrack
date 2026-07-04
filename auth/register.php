<?php
require_once __DIR__ . '/../config/database.php';

define('PAGE_TITLE', 'Create Account');
$error = '';
$success = '';
$selected_role = $_GET['role'] ?? ($_POST['role'] ?? '');
$selected_role = in_array($selected_role, ['caregiver', 'elderly'], true) ? $selected_role : '';
$role_label = $selected_role === 'caregiver' ? 'Caregiver' : ($selected_role === 'elderly' ? 'Elderly' : '');
$is_elderly_only_flow = $selected_role === 'elderly';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'caregiver';
    $role = $role === 'caregiver' ? 'caregiver' : '';
    $phone = trim($_POST['phone'] ?? '');
    $terms = isset($_POST['terms']);

    if (empty($full_name) || empty($email) || empty($password) || empty($role)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!$terms) {
        $error = 'You must agree to the Terms & Privacy Policy.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "INSERT INTO users (full_name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$full_name, $email, $hash, $role, $phone]);
            $userId = $pdo->lastInsertId();

            // Create default accessibility settings
            $stmt = $pdo->prepare(
                "INSERT INTO accessibility_settings (user_id) VALUES (?)"
            );
            $stmt->execute([$userId]);

            $success = 'Account created successfully! You can now log in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - CareTrack</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%3E%3Crect%20width='64'%20height='64'%20rx='16'%20fill='%232d8d89'/%3E%3Ccircle%20cx='32'%20cy='32'%20r='22'%20fill='none'%20stroke='%23d6c38f'%20stroke-width='6'/%3E%3Cg%20transform='translate(18%2018)'%3E%3Crect%20x='0'%20y='0'%20width='28'%20height='28'%20rx='14'%20fill='%235eb6a0'/%3E%3Cpath%20d='M8%208l12%2012'%20stroke='%23ffffff'%20stroke-width='5'%20stroke-linecap='round'/%3E%3Cpath%20d='M20%208l-12%2012'%20stroke='%23d6c38f'%20stroke-width='5'%20stroke-linecap='round'/%3E%3C/g%3E%3C/svg%3E" type="image/svg+xml">
    <link rel="shortcut icon" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%3E%3Crect%20width='64'%20height='64'%20rx='16'%20fill='%232d8d89'/%3E%3Ccircle%20cx='32'%20cy='32'%20r='22'%20fill='none'%20stroke='%23d6c38f'%20stroke-width='6'/%3E%3Cg%20transform='translate(18%2018)'%3E%3Crect%20x='0'%20y='0'%20width='28'%20height='28'%20rx='14'%20fill='%235eb6a0'/%3E%3Cpath%20d='M8%208l12%2012'%20stroke='%23ffffff'%20stroke-width='5'%20stroke-linecap='round'/%3E%3Cpath%20d='M20%208l-12%2012'%20stroke='%23d6c38f'%20stroke-width='5'%20stroke-linecap='round'/%3E%3C/g%3E%3C/svg%3E" type="image/svg+xml">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-logo">
        <span class="logo-icon">💊</span>
        <h1>CareTrack</h1>
        <p><?php echo $role_label ? $role_label . ' sign up' : 'Create your account'; ?></p>
    </div>

    <div class="auth-card">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <a href="login.php<?php echo $selected_role ? '?role=' . urlencode($selected_role) : ''; ?>" class="btn btn-primary mt-16">Sign In</a>
        <?php elseif ($is_elderly_only_flow): ?>
            <div class="alert alert-info">Elderly users should use the caregiver device code. No email or password is needed.</div>
            <a href="../elderly/connect.php" class="btn btn-primary mt-16">Enter Device Code</a>
        <?php else: ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-input" placeholder="Enter your full name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number (optional)</label>
                <input type="tel" id="phone" name="phone" class="form-input" placeholder="e.g. 0501234567">
            </div>
            <?php if ($selected_role): ?>
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($selected_role); ?>">
                <div class="selected-role">
                    <span><?php echo htmlspecialchars($role_label); ?></span>
                    <a href="../index.php">Change Role</a>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label for="role">I am a...</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="">Select your role</option>
                        <option value="caregiver">Caregiver / Family Member</option>
                    </select>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="At least 6 characters" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Repeat your password" required>
            </div>
            <div class="form-group">
                <label class="flex items-center gap-12" style="font-size: var(--font-size-base); cursor: pointer;">
                    <input type="checkbox" name="terms" style="width: 24px; height: 24px;" required>
                    I agree to <a href="#" style="font-size: var(--font-size-base);">Terms & Privacy Policy</a>
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Sign Up</button>
        </form>
        <?php if (!$is_elderly_only_flow): ?>
        <div class="auth-link">
            Already have an account? <a href="login.php<?php echo $selected_role ? '?role=' . urlencode($selected_role) : ''; ?>">Sign In</a>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
