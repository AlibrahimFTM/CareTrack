<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'caregiver') {
    header('Location: ../elderly/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'Add Elderly Profile');

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $chronic_diseases = $_POST['chronic_diseases'] ?? '';
    $allergies = $_POST['allergies'] ?? '';
    $special_conditions = trim($_POST['special_conditions'] ?? '');

    if (empty($full_name) || empty($email)) {
        $error = 'Please fill in name and email.';
    } else {
        // Create elderly account
        $tempPassword = password_hash('caretrack123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, password, role, phone, date_of_birth, gender)
            VALUES (?, ?, ?, 'elderly', ?, ?, ?)
        ");
        $stmt->execute([$full_name, $email, $tempPassword, $phone, $date_of_birth, $gender]);
        $elderlyId = $pdo->lastInsertId();

        // Create default accessibility settings
        $stmt = $pdo->prepare("INSERT INTO accessibility_settings (user_id) VALUES (?)");
        $stmt->execute([$elderlyId]);

        // Create elderly profile with health info
        $stmt = $pdo->prepare("
            INSERT INTO elderly_profiles (user_id, chronic_diseases, allergies, special_conditions)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$elderlyId, $chronic_diseases, $allergies, $special_conditions]);

        // Create family link
        $linkCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("
            INSERT INTO family_links (caregiver_id, elderly_id, link_code, status)
            VALUES (?, ?, ?, 'active')
        ");
        $stmt->execute([$userId, $elderlyId, $linkCode]);

        // Create device link for future pairing
        $stmt = $pdo->prepare("
            INSERT INTO device_links (link_code, caregiver_id, elderly_id, expires_at, status)
            VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), 'active')
        ");
        $stmt->execute([$linkCode, $userId, $elderlyId]);

        $success = "Profile created successfully! Link Code: <strong>$linkCode</strong>. Share this with the elderly user to connect.";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">👤 Add Elderly Profile</div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
    <?php else: ?>
    <form method="POST" action="">
        <h3>Personal Information</h3>
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" class="form-input" placeholder="Enter full name" required>
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-input" placeholder="e.g. ahmed@email.com" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" class="form-input" placeholder="e.g. 0501234567">
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="form-input">
            </div>
        </div>
        <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender" class="form-select">
                <option value="">Select</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>

        <h3 style="margin-top: 24px;">Health Information</h3>
        <div class="form-group">
            <label for="chronic_diseases">Chronic Diseases (Select all that apply)</label>
            <select id="chronic_diseases" name="chronic_diseases" class="form-select" multiple size="4">
                <option value="Diabetes">Diabetes</option>
                <option value="Hypertension">Hypertension</option>
                <option value="Heart Disease">Heart Disease</option>
                <option value="Arthritis">Arthritis</option>
                <option value="Asthma">Asthma</option>
            </select>
            <p class="text-xs" style="color: var(--text-secondary); margin-top: 4px;">Hold Ctrl/Cmd to select multiple</p>
        </div>
        <div class="form-group">
            <label for="allergies">Allergies (Select all that apply)</label>
            <select id="allergies" name="allergies" class="form-select" multiple size="3">
                <option value="No allergies">No allergies</option>
                <option value="Penicillin">Penicillin</option>
                <option value="Food allergy">Food Allergy</option>
                <option value="Latex">Latex</option>
            </select>
        </div>
        <div class="form-group">
            <label for="special_conditions">Special Conditions (Optional)</label>
            <textarea id="special_conditions" name="special_conditions" class="form-textarea" placeholder="e.g. Vision problems, Memory issues"></textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-16">Create Profile</button>
    </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
