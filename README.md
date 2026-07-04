# CareTrack 💊

**CareTrack** is a medication tracking web application designed specifically for elderly individuals. It features a highly accessible interface with large buttons, high contrast colors, and clear text to help elderly users safely take their medications on schedule.

## Features

### For Elderly Users
- **Dashboard** - Daily medication overview with taken/missed counts
- **Dose Confirmation** - One-tap confirm button when medication is taken
- **Voice Reminders** - Familiar family-recorded voice messages for each medication
- **Medication History** - View past medication logs by date
- **Personal Profile** - View health info and linked family members
- **Emergency Alert** - One-button alert to notify family immediately

### For Caregivers / Family Members
- **Multi-Elderly Management** - Manage medications for multiple family members
- **Add Medications** - Set name, dosage, time, color, and shape of pills
- **Voice Reminder Setup** - Record personalized voice messages for each medication
- **Medication Log** - View detailed logs of taken/missed doses
- **Trends & Analytics** - Adherence rates, weekly summaries, missed dose patterns
- **Missed Dose Alerts** - Real-time alerts when a dose is not confirmed within 15 minutes
- **Device Linking** - 4-digit code system to connect elderly devices
- **Emergency Monitoring** - Receive and respond to emergency alerts

## Tech Stack

- **Frontend**: HTML5, CSS3 (Cartoon Brutalist design), JavaScript (Vanilla)
- **Backend**: PHP 8+ (PDO)
- **Database**: MySQL / MariaDB
- **Design**: Accessible, High Contrast, Large Touch Targets

## Installation

### Requirements
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache / Nginx)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/caretrack.git
   cd caretrack
   ```

2. **Create the database**
   ```bash
   mysql -u root -p < sql/schema.sql
   ```

3. **Configure database connection**
   
   Edit `config/database.php` and update:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'caretrack');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   ```

4. **Set up web server**
   
   Point your web server document root to the `caretrack` directory.

   For Apache, create a virtual host:
   ```apache
   <VirtualHost *:80>
       DocumentRoot "/path/to/caretrack"
       ServerName caretrack.local
   </VirtualHost>
   ```

5. **Set up cron job** (for missed dose detection)
   
   Add to crontab:
   ```bash
   * * * * * php /path/to/caretrack/cron/check_missed_doses.php
   ```

6. **Access the application**
   
   Open http://localhost/caretrack in your browser.

### Default Credentials
After setup, register a new account. The first user can register as a **Caregiver** or **Elderly**.

## Project Structure

```
caretrack/
├── index.php                 # Entry point / redirect
├── config/
│   └── database.php          # Database connection & config
├── sql/
│   └── schema.sql            # MySQL schema
├── includes/
│   ├── auth_check.php        # Authentication middleware
│   ├── header.php            # HTML header with nav
│   ├── footer.php            # HTML footer
│   └── accessibility.php     # Accessibility settings page
├── css/
│   └── style.css             # Main stylesheet (accessible design)
├── js/
│   └── script.js             # JavaScript (AJAX, UI interactions)
├── auth/
│   ├── login.php             # Login page
│   ├── register.php          # Registration page
│   ├── forgot_password.php   # Password reset
│   └── logout.php            # Logout handler
├── caregiver/
│   ├── dashboard.php         # Caregiver dashboard
│   ├── medications.php       # List medications
│   ├── add_medication.php    # Add new medication
│   ├── medication_log.php    # Medication log viewer
│   ├── trends.php            # Adherence trends
│   ├── alerts.php            # Missed dose alerts
│   ├── add_elderly.php       # Add elderly profile
│   ├── link_device.php       # Device linking
│   └── voice_reminders.php   # Voice reminder setup
├── elderly/
│   ├── dashboard.php         # Elderly home dashboard
│   ├── medications.php       # Elderly medication list
│   ├── history.php           # Medication history
│   ├── profile.php           # User profile
│   ├── emergency.php         # Emergency alert
│   └── link_device.php       # Connect to caregiver
├── api/
│   ├── confirm_dose.php      # Dose confirmation AJAX
│   ├── emergency.php         # Emergency alert AJAX
│   ├── generate_code.php     # Device code generation
│   ├── connect_device.php    # Device pairing
│   ├── acknowledge_alert.php # Alert acknowledgment
│   └── delete_medication.php # Medication deletion
└── cron/
    └── check_missed_doses.php # Missed dose detection
```

## Design Features

- **Cartoon Brutalist Design** - Bold outlines, flat colors, chunky shadows
- **Large Touch Targets** - All buttons minimum 56px height
- **High Contrast Mode** - Optional dark mode with high contrast
- **Text Size Options** - Small, Medium, Large
- **Bolder Colors Mode** - Enhanced color visibility
- **Voice Reminders** - Family-recorded medication instructions
- **Mobile-First** - Optimized for 480px width (phone-like experience)

## License

This project was developed for academic purposes (CIS422 - Human Computer Interaction).

## Team

- Juman Sultan (Leader)
- Fatimah Alibrahim
- Reemas Sultan
- Fatimah Aljaffal
- Shahad Alsufyani
- Layan Alasmri
