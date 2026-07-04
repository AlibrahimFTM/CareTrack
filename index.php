<?php
require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'caregiver') {
        header('Location: caregiver/dashboard.php');
    } else {
        header('Location: elderly/dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - CareTrack</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%3E%3Crect%20width='64'%20height='64'%20rx='16'%20fill='%232d8d89'/%3E%3Ccircle%20cx='32'%20cy='32'%20r='22'%20fill='none'%20stroke='%23d6c38f'%20stroke-width='6'/%3E%3Cg%20transform='translate(18%2018)'%3E%3Crect%20x='0'%20y='0'%20width='28'%20height='28'%20rx='14'%20fill='%235eb6a0'/%3E%3Cpath%20d='M8%208l12%2012'%20stroke='%23ffffff'%20stroke-width='5'%20stroke-linecap='round'/%3E%3Cpath%20d='M20%208l-12%2012'%20stroke='%23d6c38f'%20stroke-width='5'%20stroke-linecap='round'/%3E%3C/g%3E%3C/svg%3E" type="image/svg+xml">
    <link rel="shortcut icon" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%3E%3Crect%20width='64'%20height='64'%20rx='16'%20fill='%232d8d89'/%3E%3Ccircle%20cx='32'%20cy='32'%20r='22'%20fill='none'%20stroke='%23d6c38f'%20stroke-width='6'/%3E%3Cg%20transform='translate(18%2018)'%3E%3Crect%20x='0'%20y='0'%20width='28'%20height='28'%20rx='14'%20fill='%235eb6a0'/%3E%3Cpath%20d='M8%208l12%2012'%20stroke='%23ffffff'%20stroke-width='5'%20stroke-linecap='round'/%3E%3Cpath%20d='M20%208l-12%2012'%20stroke='%23d6c38f'%20stroke-width='5'%20stroke-linecap='round'/%3E%3C/g%3E%3C/svg%3E" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page start-page">
    <div class="auth-logo">
        <span class="logo-icon">CT</span>
        <h1>CareTrack</h1>
        <p>Choose your role</p>
    </div>

    <div class="role-grid">
        <section class="role-card">
            <div class="role-icon" aria-hidden="true">C</div>
            <h2>Caregiver</h2>
            <p class="text-sm">Manage medications, reminders, alerts, and elderly profiles.</p>
            <div class="role-actions">
                <a href="auth/login.php?role=caregiver" class="btn btn-primary">Caregiver Sign In</a>
                <a href="auth/register.php?role=caregiver" class="btn">Caregiver Sign Up</a>
            </div>
        </section>

        <section class="role-card">
            <div class="role-icon" aria-hidden="true">E</div>
            <h2>Elderly</h2>
            <p class="text-sm">No sign-up or login is needed. Use the 4-digit code from your caregiver to open your CareTrack dashboard.</p>
            <div class="role-actions">
                <a href="elderly/connect.php" class="btn btn-primary">Enter Device Code</a>
            </div>
        </section>
    </div>
</div>
</body>
</html>
