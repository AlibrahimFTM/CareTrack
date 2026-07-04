<?php
/**
 * CareTrack Missed Dose Checker
 * 
 * Run this script every minute via cron to detect missed doses
 * and send alerts to caregivers.
 * 
 * Cron: * * * * * php /path/to/cron/check_missed_doses.php
 */

require_once __DIR__ . '/../config/database.php';

$now = date('Y-m-d H:i:s');
$currentTime = date('H:i:s');
$today = date('Y-m-d');
$fifteenMinutesAgo = date('H:i:s', strtotime('-15 minutes'));

// Find medications scheduled between 15-20 minutes ago that haven't been taken
$stmt = $pdo->prepare("
    SELECT m.id as medication_id, m.name, m.dosage, m.time, m.user_id as elderly_id,
           m.name as med_name
    FROM medications m
    WHERE m.status = 'active'
      AND m.time BETWEEN ? AND ?
      AND m.id NOT IN (
          SELECT medication_id FROM dose_logs 
          WHERE scheduled_date = ? AND status = 'taken'
      )
");
$stmt->execute([$fifteenMinutesAgo, $currentTime, $today]);
$missedMeds = $stmt->fetchAll();

foreach ($missedMeds as $med) {
    // Mark as missed in dose_logs
    $stmt = $pdo->prepare("
        INSERT INTO dose_logs (medication_id, user_id, scheduled_date, scheduled_time, status)
        VALUES (?, ?, ?, ?, 'missed')
        ON DUPLICATE KEY UPDATE status = 'missed'
    ");
    $stmt->execute([$med['medication_id'], $med['elderly_id'], $today, $med['time']]);

    // Find linked caregivers
    $stmt2 = $pdo->prepare("
        SELECT caregiver_id FROM family_links 
        WHERE elderly_id = ? AND status = 'active'
    ");
    $stmt2->execute([$med['elderly_id']]);
    $caregivers = $stmt2->fetchAll();

    foreach ($caregivers as $cg) {
        // Create alert (if not already pending)
        $stmt3 = $pdo->prepare("
            SELECT id FROM missed_dose_alerts 
            WHERE medication_id = ? AND elderly_id = ? AND caregiver_id = ? 
            AND status = 'pending' AND DATE(alert_time) = ?
            LIMIT 1
        ");
        $stmt3->execute([$med['medication_id'], $med['elderly_id'], $cg['caregiver_id'], $today]);
        if (!$stmt3->fetch()) {
            $stmt4 = $pdo->prepare("
                INSERT INTO missed_dose_alerts (medication_id, elderly_id, caregiver_id, alert_time, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt4->execute([$med['medication_id'], $med['elderly_id'], $cg['caregiver_id'], $now]);
        }
    }
}

echo "[" . $now . "] Checked doses. Found " . count($missedMeds) . " missed.\n";
