<?php
require_once __DIR__ . '/../config/database.php';

define('PAGE_TITLE', 'Login');
$error = '';
$selected_role = $_GET['role'] ?? ($_POST['selected_role'] ?? '');
$selected_role = in_array($selected_role, ['caregiver', 'elderly'], true) ? $selected_role : '';
$role_label = $selected_role === 'caregiver' ? 'Caregiver' : ($selected_role === 'elderly' ? 'Elderly' : '');
$is_elderly_only_flow = $selected_role === 'elderly';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === 'elderly') {
                $error = 'Elderly users connect with the caregiver device code.';
            } else {
            if ($selected_role && $user['role'] !== $selected_role) {
                $error = 'This account is registered as ' . ucfirst($user['role']) . '. Please choose the correct role.';
            } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            // Redirect based on role
            if ($user['role'] === 'caregiver') {
                header('Location: ../caregiver/dashboard.php');
            } else {
                header('Location: ../elderly/dashboard.php');
            }
            exit;
            }
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CareTrack</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%3E%3Crect%20width='64'%20height='64'%20rx='16'%20fill='%232d8d89'/%3E%3Ccircle%20cx='32'%20cy='32'%20r='22'%20fill='none'%20stroke='%23d6c38f'%20stroke-width='6'/%3E%3Cg%20transform='translate(18%2018)'%3E%3Crect%20x='0'%20y='0'%20width='28'%20height='28'%20rx='14'%20fill='%235eb6a0'/%3E%3Cpath%20d='M8%208l12%2012'%20stroke='%23ffffff'%20stroke-width='5'%20stroke-linecap='round'/%3E%3Cpath%20d='M20%208l-12%2012'%20stroke='%23d6c38f'%20stroke-width='5'%20stroke-linecap='round'/%3E%3C/g%3E%3C/svg%3E" type="image/svg+xml">
    <link rel="shortcut icon" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%3E%3Crect%20width='64'%20height='64'%20rx='16'%20fill='%232d8d89'/%3E%3Ccircle%20cx='32'%20cy='32'%20r='22'%20fill='none'%20stroke='%23d6c38f'%20stroke-width='6'/%3E%3Cg%20transform='translate(18%2018)'%3E%3Crect%20x='0'%20y='0'%20width='28'%20height='28'%20rx='14'%20fill='%235eb6a0'/%3E%3Cpath%20d='M8%208l12%2012'%20stroke='%23ffffff'%20stroke-width='5'%20stroke-linecap='round'/%3E%3Cpath%20d='M20%208l-12%2012'%20stroke='%23d6c38f'%20stroke-width='5'%20stroke-linecap='round'/%3E%3C/g%3E%3C/svg%3E" type="image/svg+xml">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root { --font-size-base: 20px; --font-size-lg: 28px; --font-size-xl: 36px; --font-size-sm: 16px; }
    </style>
</head>
<body>
<div class="auth-page">
    <div class="auth-logo">
        <span class="logo-icon">💊</span>
        <h1>CareTrack</h1>
        <p><?php echo $role_label ? $role_label . ' sign in' : 'Welcome back'; ?></p>
    </div>

    <div class="auth-card">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($is_elderly_only_flow): ?>
            <div class="alert alert-info" style="margin-bottom: 16px;">No email or password is required. Use the caregiver device code only.</div>
            <a href="../elderly/connect.php" class="btn btn-primary">Enter Device Code</a>
        <?php else: ?>
        <form method="POST" action="">
            <input type="hidden" name="selected_role" value="<?php echo htmlspecialchars($selected_role); ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <div class="auth-link">
            <a href="forgot_password.php">Forgot Password?</a>
            <span style="display: inline-block; margin: 0 8px; color: var(--text-secondary);">|</span>
            <a href="../index.php">Change Role</a>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!$is_elderly_only_flow): ?>
    <div class="auth-divider">New to CareTrack?</div>
    <a href="register.php<?php echo $selected_role ? '?role=' . urlencode($selected_role) : ''; ?>" class="btn">Create an Account</a>
    <?php endif; ?>
</div>
</body>
</html>
