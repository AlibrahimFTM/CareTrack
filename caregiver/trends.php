<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SESSION['role'] !== 'caregiver') {
    header('Location: ../elderly/dashboard.php');
    exit;
}

define('PAGE_TITLE', 'Medication Trends');

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT u.id, u.full_name 
    FROM family_links fl 
    JOIN users u ON u.id = fl.elderly_id 
    WHERE fl.caregiver_id = ? AND fl.status = 'active'
");
$stmt->execute([$userId]);
$linkedElderly = $stmt->fetchAll();

$selectedElderly = $_GET['elderly_id'] ?? ($linkedElderly[0]['id'] ?? 0);

// Get weekly stats
$weekAgo = date('Y-m-d', strtotime('-7 days'));

$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'taken' THEN 1 ELSE 0 END) as taken,
        SUM(CASE WHEN status = 'missed' THEN 1 ELSE 0 END) as missed
    FROM dose_logs 
    WHERE user_id = ? AND scheduled_date >= ? AND scheduled_date <= CURDATE()
");
$stmt->execute([$selectedElderly, $weekAgo]);
$weeklyStats = $stmt->fetch();

// Daily breakdown
$stmt = $pdo->prepare("
    SELECT scheduled_date,
        SUM(CASE WHEN status = 'taken' THEN 1 ELSE 0 END) as taken,
        SUM(CASE WHEN status = 'missed' THEN 1 ELSE 0 END) as missed
    FROM dose_logs 
    WHERE user_id = ? AND scheduled_date >= ?
    GROUP BY scheduled_date
    ORDER BY scheduled_date
");
$stmt->execute([$selectedElderly, $weekAgo]);
$dailyLogs = $stmt->fetchAll();

// Most missed time
$stmt = $pdo->prepare("
    SELECT HOUR(m.time) as hour, COUNT(*) as missed_count
    FROM dose_logs dl
    JOIN medications m ON m.id = dl.medication_id
    WHERE dl.user_id = ? AND dl.status = 'missed'
    GROUP BY HOUR(m.time)
    ORDER BY missed_count DESC
    LIMIT 1
");
$stmt->execute([$selectedElderly]);
$mostMissedTime = $stmt->fetch();

// Most taken time
$stmt = $pdo->prepare("
    SELECT HOUR(m.time) as hour, COUNT(*) as taken_count
    FROM dose_logs dl
    JOIN medications m ON m.id = dl.medication_id
    WHERE dl.user_id = ? AND dl.status = 'taken'
    GROUP BY HOUR(m.time)
    ORDER BY taken_count DESC
    LIMIT 1
");
$stmt->execute([$selectedElderly]);
$mostTakenTime = $stmt->fetch();

$total = $weeklyStats['total'] ?? 0;
$taken = $weeklyStats['taken'] ?? 0;
$missed = $weeklyStats['missed'] ?? 0;
$rate = $total > 0 ? round(($taken / $total) * 100) : 0;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">📊 Medication Trends</div>

    <?php if (empty($linkedElderly)): ?>
        <p style="text-align: center; color: var(--text-secondary);">
            <a href="add_elderly.php">Add an elderly profile</a> to see medication trends.
        </p>
    <?php else: ?>
        <?php if (count($linkedElderly) > 1): ?>
        <form method="GET" class="mb-16">
            <select name="elderly_id" class="form-select" onchange="this.form.submit()">
                <?php foreach ($linkedElderly as $el): ?>
                <option value="<?php echo $el['id']; ?>" <?php echo $selectedElderly == $el['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($el['full_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php endif; ?>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="number text-taken"><?php echo $rate; ?>%</div>
                <div class="label">Adherence</div>
            </div>
            <div class="summary-card">
                <div class="number text-missed"><?php echo $missed; ?></div>
                <div class="label">Missed This Week</div>
            </div>
            <div class="summary-card">
                <div class="number" style="color: var(--color-upcoming);"><?php echo $total; ?></div>
                <div class="label">Total Doses</div>
            </div>
        </div>

        <?php if ($mostMissedTime): 
            $hours = ['12 AM', '1 AM', '2 AM', '3 AM', '4 AM', '5 AM', '6 AM', '7 AM', '8 AM', '9 AM', '10 AM', '11 AM',
                      '12 PM', '1 PM', '2 PM', '3 PM', '4 PM', '5 PM', '6 PM', '7 PM', '8 PM', '9 PM', '10 PM', '11 PM'];
        ?>
        <div class="card" style="box-shadow: var(--shadow-sm);">
            <h3>⏰ Missed Dose Patterns</h3>
            <p class="text-sm">Most missed time: <strong><?php echo $hours[$mostMissedTime['hour']] ?? 'N/A'; ?></strong> (<?php echo $mostMissedTime['missed_count']; ?> times)</p>
            <p class="text-sm">Most taken time: <strong><?php echo $hours[$mostTakenTime['hour']] ?? 'N/A'; ?></strong> (<?php echo $mostTakenTime['taken_count']; ?> times)</p>
        </div>
        <?php endif; ?>

        <h3 style="margin-top: 20px;">Weekly Summary</h3>
        <div class="trend-container">
            <?php foreach ($dailyLogs as $day): 
                $dayTotal = $day['taken'] + $day['missed'];
                $dayRate = $dayTotal > 0 ? round(($day['taken'] / $dayTotal) * 100) : 0;
                $dayName = date('D', strtotime($day['scheduled_date']));
            ?>
            <div class="trend-bar">
                <span class="trend-label"><?php echo $dayName; ?></span>
                <div class="trend-track">
                    <div class="trend-fill" style="width: <?php echo $dayRate; ?>%; background: <?php 
                        echo $dayRate >= 80 ? 'var(--color-taken)' : ($dayRate >= 50 ? 'var(--color-upcoming)' : 'var(--color-missed)'); ?>;">
                    </div>
                </div>
                <span class="trend-pct"><?php echo $dayRate; ?>%</span>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
