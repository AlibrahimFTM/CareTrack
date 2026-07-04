<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth_check.php';

define('PAGE_TITLE', 'Accessibility Settings');

$userId = $_SESSION['user_id'];

// Fetch current settings
$stmt = $pdo->prepare("SELECT * FROM accessibility_settings WHERE user_id = ?");
$stmt->execute([$userId]);
$settings = $stmt->fetch();

if (!$settings) {
    // Create default settings
    $stmt = $pdo->prepare("INSERT INTO accessibility_settings (user_id) VALUES (?)");
    $stmt->execute([$userId]);
    $stmt = $pdo->prepare("SELECT * FROM accessibility_settings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $settings = $stmt->fetch();
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text_size = $_POST['text_size'] ?? 'large';
    $bold_colors = isset($_POST['bold_colors']) ? 1 : 0;
    $high_contrast = 0;
    $loud_alarm = isset($_POST['loud_alarm']) ? 1 : 0;
    $vibrate = isset($_POST['vibrate']) ? 1 : 0;
    $voice_reminders = isset($_POST['voice_reminders']) ? 1 : 0;
    $play_family_recording = isset($_POST['play_family_recording']) ? 1 : 0;

    $stmt = $pdo->prepare(
        "UPDATE accessibility_settings SET 
         text_size = ?, bold_colors = ?, high_contrast = ?, 
         loud_alarm = ?, vibrate = ?, voice_reminders = ?, 
         play_family_recording = ?
         WHERE user_id = ?"
    );
    $stmt->execute([
        $text_size, $bold_colors, $high_contrast,
        $loud_alarm, $vibrate, $voice_reminders,
        $play_family_recording, $userId
    ]);

    $msg = 'Settings saved successfully!';

    // Re-fetch
    $stmt = $pdo->prepare("SELECT * FROM accessibility_settings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $settings = $stmt->fetch();
}

require_once __DIR__ . '/header.php';
?>

<div class="card">
    <div class="card-header">♿ Accessibility Settings</div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <p class="text-sm" style="margin-bottom: 16px; color: var(--text-secondary);">
        Customize your CareTrack experience. Changes apply immediately.
    </p>

    <form method="POST" action="">
        <h3 style="margin-top: 16px;">Display</h3>

        <div class="form-group">
            <label>Text Size</label>
            <div class="btn-group">
                <label class="btn btn-sm <?php echo $settings['text_size'] === 'small' ? 'btn-primary' : ''; ?>">
                    <input type="radio" name="text_size" value="small" 
                        <?php echo $settings['text_size'] === 'small' ? 'checked' : ''; ?>
                        onchange="this.closest('form').submit()" style="display:none;">
                    A
                </label>
                <label class="btn btn-sm <?php echo $settings['text_size'] === 'medium' ? 'btn-primary' : ''; ?>">
                    <input type="radio" name="text_size" value="medium" 
                        <?php echo $settings['text_size'] === 'medium' ? 'checked' : ''; ?>
                        onchange="this.closest('form').submit()" style="display:none;">
                    A<span style="font-size:1.3em">A</span>
                </label>
                <label class="btn btn-sm <?php echo $settings['text_size'] === 'large' ? 'btn-primary' : ''; ?>">
                    <input type="radio" name="text_size" value="large" 
                        <?php echo $settings['text_size'] === 'large' ? 'checked' : ''; ?>
                        onchange="this.closest('form').submit()" style="display:none;">
                    A<span style="font-size:1.5em">A</span>
                </label>
            </div>
        </div>

        <h3 style="margin-top: 20px;">Colors & Contrast</h3>

        <div class="toggle-wrap">
            <span class="toggle-label">Bolder Colors</span>
            <label class="toggle-switch">
                <input type="checkbox" name="bold_colors" value="1" 
                    <?php echo $settings['bold_colors'] ? 'checked' : ''; ?>
                    onchange="this.closest('form').submit()">
                <span class="toggle-slider"></span>
            </label>
        </div>

        <h3 style="margin-top: 20px;">Sound & Alerts</h3>

        <div class="toggle-wrap">
            <span class="toggle-label">Loud Alarm Sound</span>
            <label class="toggle-switch">
                <input type="checkbox" name="loud_alarm" value="1" 
                    <?php echo $settings['loud_alarm'] ? 'checked' : ''; ?>
                    onchange="this.closest('form').submit()">
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="toggle-wrap">
            <span class="toggle-label">Vibrate on Reminder</span>
            <label class="toggle-switch">
                <input type="checkbox" name="vibrate" value="1" 
                    <?php echo $settings['vibrate'] ? 'checked' : ''; ?>
                    onchange="this.closest('form').submit()">
                <span class="toggle-slider"></span>
            </label>
        </div>

        <h3 style="margin-top: 20px;">Voice Reminders</h3>

        <div class="toggle-wrap">
            <span class="toggle-label">Voice Reminders</span>
            <label class="toggle-switch">
                <input type="checkbox" name="voice_reminders" value="1" 
                    <?php echo $settings['voice_reminders'] ? 'checked' : ''; ?>
                    onchange="this.closest('form').submit()">
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="toggle-wrap">
            <span class="toggle-label">Play Family Recording</span>
            <label class="toggle-switch">
                <input type="checkbox" name="play_family_recording" value="1" 
                    <?php echo $settings['play_family_recording'] ? 'checked' : ''; ?>
                    onchange="this.closest('form').submit()">
                <span class="toggle-slider"></span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary mt-24">Save All Settings</button>
    </form>
</div>

<div class="card" style="background: var(--color-primary); color: #fff;">
    <div class="card-header" style="color: #fff; border-color: #fff;">🔍 Preview</div>
    <p style="color: #fff;">This is how text will appear with your current settings.</p>
    <p style="font-size: var(--font-size-lg); font-weight: 800; color: #fff;">Large & Bold Headings</p>
    <button class="btn" style="background: #fff; color: var(--color-primary); margin-top: 12px;">Sample Button</button>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
