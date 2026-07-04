-- CareTrack Database Schema
-- MySQL

CREATE DATABASE IF NOT EXISTS caretrack;
USE caretrack;

-- Users table (both caregivers and elderly)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('caregiver', 'elderly') NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Link between caregiver and elderly
CREATE TABLE family_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    caregiver_id INT NOT NULL,
    elderly_id INT NOT NULL,
    link_code VARCHAR(6),
    status ENUM('pending', 'active') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (caregiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (elderly_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Elderly profile extra info
CREATE TABLE elderly_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    chronic_diseases TEXT,
    allergies TEXT,
    special_conditions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Medications
CREATE TABLE medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    color VARCHAR(50),
    shape VARCHAR(50),
    time TIME NOT NULL,
    frequency ENUM('daily', 'twice_daily', 'weekly', 'custom') DEFAULT 'daily',
    status ENUM('active', 'inactive') DEFAULT 'active',
    voice_reminder_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Dose log
CREATE TABLE dose_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medication_id INT NOT NULL,
    user_id INT NOT NULL,
    scheduled_date DATE NOT NULL,
    scheduled_time TIME NOT NULL,
    status ENUM('taken', 'missed', 'skipped') NOT NULL,
    taken_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medication_id) REFERENCES medications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Missed dose alerts
CREATE TABLE missed_dose_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medication_id INT NOT NULL,
    elderly_id INT NOT NULL,
    caregiver_id INT NOT NULL,
    alert_time DATETIME NOT NULL,
    status ENUM('pending', 'acknowledged', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medication_id) REFERENCES medications(id) ON DELETE CASCADE,
    FOREIGN KEY (elderly_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (caregiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Emergency alerts
CREATE TABLE emergency_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    elderly_id INT NOT NULL,
    caregiver_id INT NOT NULL,
    alert_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'resolved') DEFAULT 'active',
    resolved_at DATETIME,
    FOREIGN KEY (elderly_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (caregiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Accessibility settings
CREATE TABLE accessibility_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    text_size ENUM('small', 'medium', 'large') DEFAULT 'large',
    bold_colors BOOLEAN DEFAULT TRUE,
    high_contrast BOOLEAN DEFAULT FALSE,
    loud_alarm BOOLEAN DEFAULT TRUE,
    vibrate BOOLEAN DEFAULT TRUE,
    voice_reminders BOOLEAN DEFAULT TRUE,
    play_family_recording BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Device linking (4-digit codes)
CREATE TABLE device_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    link_code VARCHAR(4) NOT NULL,
    caregiver_id INT NOT NULL,
    elderly_id INT,
    expires_at DATETIME NOT NULL,
    status ENUM('active', 'used', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (caregiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (elderly_id) REFERENCES users(id) ON DELETE SET NULL
);
