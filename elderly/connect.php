<?php
require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'elderly') {
        header('Location: dashboard.php');
    } else {
        header('Location: ../caregiver/dashboard.php');
    }
    exit;
}

$error = '';
$code = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = preg_replace('/\D/', '', $_POST['link_code'] ?? '');

    if (strlen($code) !== 4) {
        $error = 'Please enter the 4-digit code from your caregiver.';
    } else {
        $stmt = $pdo->prepare("
            SELECT dl.id AS device_link_id, u.*
            FROM device_links dl
            JOIN users u ON u.id = dl.elderly_id
            WHERE dl.link_code = ?
              AND dl.status IN ('active', 'used')
              AND dl.expires_at > NOW()
              AND u.role = 'elderly'
            LIMIT 1
        ");
        $stmt->execute([$code]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $pdo->prepare("UPDATE device_links SET status = 'used' WHERE id = ?");
            $stmt->execute([$user['device_link_id']]);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            header('Location: dashboard.php');
            exit;
        }

        $error = 'Invalid or expired code. Ask your caregiver for a new code.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect Device - CareTrack</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%3E%3Crect%20width='64'%20height='64'%20rx='16'%20fill='%232d8d89'/%3E%3Ccircle%20cx='32'%20cy='32'%20r='22'%20fill='none'%20stroke='%23d6c38f'%20stroke-width='6'/%3E%3Cg%20transform='translate(18%2018)'%3E%3Crect%20x='0'%20y='0'%20width='28'%20height='28'%20rx='14'%20fill='%235eb6a0'/%3E%3Cpath%20d='M8%208l12%2012'%20stroke='%23ffffff'%20stroke-width='5'%20stroke-linecap='round'/%3E%3Cpath%20d='M20%208l-12%2012'%20stroke='%23d6c38f'%20stroke-width='5'%20stroke-linecap='round'/%3E%3C/g%3E%3C/svg%3E" type="image/svg+xml">
    <link rel="shortcut icon" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%3E%3Crect%20width='64'%20height='64'%20rx='16'%20fill='%232d8d89'/%3E%3Ccircle%20cx='32'%20cy='32'%20r='22'%20fill='none'%20stroke='%23d6c38f'%20stroke-width='6'/%3E%3Cg%20transform='translate(18%2018)'%3E%3Crect%20x='0'%20y='0'%20width='28'%20height='28'%20rx='14'%20fill='%235eb6a0'/%3E%3Cpath%20d='M8%208l12%2012'%20stroke='%23ffffff'%20stroke-width='5'%20stroke-linecap='round'/%3E%3Cpath%20d='M20%208l-12%2012'%20stroke='%23d6c38f'%20stroke-width='5'%20stroke-linecap='round'/%3E%3C/g%3E%3C/svg%3E" type="image/svg+xml">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-logo">
        <span class="logo-icon">CT</span>
        <h1>CareTrack</h1>
        <p>Enter the 4-digit caregiver code</p>
    </div>

    <div class="auth-card">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <p class="text-sm" style="margin-bottom: 16px; color: var(--text-secondary);">No email or password is needed. Use the code shared by your caregiver.</p>

        <form method="POST" action="">
            <div class="form-group">
                <label for="link_code">Device Code</label>
                <input
                    type="text"
                    id="link_code"
                    name="link_code"
                    class="form-input code-input"
                    value="<?php echo htmlspecialchars($code); ?>"
                    placeholder="0000"
                    inputmode="numeric"
                    pattern="[0-9]{4}"
                    maxlength="4"
                    autocomplete="one-time-code"
                    required>
            </div>

            <button type="submit" class="btn btn-primary">Connect Device</button>
        </form>

        <div class="auth-link">
            <a href="../index.php">Back to role selection</a>
        </div>
    </div>
</div>
</body>
</html>
