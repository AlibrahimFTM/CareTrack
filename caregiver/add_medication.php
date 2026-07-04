<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'caregiver') {
    header('Location: ../elderly/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'Add Medication');

$userId = $_SESSION['user_id'];

// Get linked elderly
$stmt = $pdo->prepare("
    SELECT u.id, u.full_name 
    FROM family_links fl 
    JOIN users u ON u.id = fl.elderly_id 
    WHERE fl.caregiver_id = ? AND fl.status = 'active'
");
$stmt->execute([$userId]);
$linkedElderly = $stmt->fetchAll();

$selectedElderly = $_GET['elderly_id'] ?? ($_POST['elderly_id'] ?? ($linkedElderly[0]['id'] ?? 0));
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $dosage = trim($_POST['dosage'] ?? '');
    $time = $_POST['time'] ?? '';
    $color = $_POST['color'] ?? '';
    $shape = $_POST['shape'] ?? '';
    $frequency = $_POST['frequency'] ?? 'daily';
    $elderly_id = $_POST['elderly_id'] ?? 0;

    if (empty($name) || empty($dosage) || empty($time) || empty($elderly_id)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO medications (user_id, name, dosage, color, shape, time, frequency)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$elderly_id, $name, $dosage, $color, $shape, $time, $frequency]);
        $medId = $pdo->lastInsertId();

        // Create a dose_log entry for today if time has passed
        $today = date('Y-m-d');
        $currentTime = date('H:i:s');
        if ($time <= $currentTime) {
            $stmt = $pdo->prepare("
                INSERT INTO dose_logs (medication_id, user_id, scheduled_date, scheduled_time, status)
                VALUES (?, ?, ?, ?, 'missed')
            ");
            $stmt->execute([$medId, $elderly_id, $today, $time]);
        }

        $msg = 'Medication added successfully!';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">➕ Add Medication</div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?> ✔</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (empty($linkedElderly)): ?>
        <p style="text-align: center; color: var(--text-secondary);">
            You need to <a href="add_elderly.php">add an elderly profile</a> first.
        </p>
    <?php else: ?>
    <form method="POST" action="">
        <?php if (count($linkedElderly) > 1): ?>
        <div class="form-group">
            <label for="elderly_id">For</label>
            <select id="elderly_id" name="elderly_id" class="form-select">
                <?php foreach ($linkedElderly as $el): ?>
                <option value="<?php echo $el['id']; ?>" <?php echo $selectedElderly == $el['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($el['full_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php else: ?>
            <input type="hidden" name="elderly_id" value="<?php echo $linkedElderly[0]['id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="name">Medication Name</label>
            <input type="text" id="name" name="name" class="form-input" placeholder="e.g. Aspirin" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="dosage">Dosage (mg)</label>
                <input type="text" id="dosage" name="dosage" class="form-input" placeholder="e.g. 100 mg" required>
            </div>
            <div class="form-group">
                <label for="time">Time</label>
                <input type="time" id="time" name="time" class="form-input" required>
            </div>
        </div>

        <div class="form-group">
            <label>Color</label>
            <div class="pill-selector" id="colorSelector">
                <?php 
                $colors = [
                    '#e74c3c' => 'Red', '#3498db' => 'Blue', '#2ecc71' => 'Green', 
                    '#f1c40f' => 'Yellow', '#9b59b6' => 'Purple', '#e67e22' => 'Orange',
                    '#ffffff' => 'White', '#1a1a2e' => 'Dark'
                ];
                foreach ($colors as $hex => $name): ?>
                <div class="pill-option" style="background: <?php echo $hex; ?>; border-color: <?php echo $hex === '#ffffff' ? '#000' : 'var(--border-color)'; ?>;" 
                     data-value="<?php echo $name; ?>" onclick="selectPill(this, 'color')">
                    <?php echo $hex === '#ffffff' ? 'W' : ''; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" id="color" name="color" value="">
        </div>

        <div class="form-group">
            <label>Shape</label>
            <div class="shape-selector" id="shapeSelector">
                <div class="shape-option" data-value="round" onclick="selectShape(this, 'shape')">⬤</div>
                <div class="shape-option" data-value="oval" onclick="selectShape(this, 'shape')">⬮</div>
                <div class="shape-option" data-value="capsule" onclick="selectShape(this, 'shape')">💊</div>
                <div class="shape-option" data-value="square" onclick="selectShape(this, 'shape')">⬛</div>
            </div>
            <input type="hidden" id="shape" name="shape" value="">
        </div>

        <div class="form-group">
            <label for="frequency">Frequency</label>
            <select id="frequency" name="frequency" class="form-select">
                <option value="daily">Daily</option>
                <option value="twice_daily">Twice Daily</option>
                <option value="weekly">Weekly</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Continue to Reminder Setup</button>
    </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
