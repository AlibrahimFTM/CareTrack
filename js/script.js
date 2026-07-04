// CareTrack - Main JavaScript

// Toggle side menu
function toggleMenu() {
    const menu = document.getElementById('sideMenu');
    menu.classList.toggle('open');
    let overlay = document.querySelector('.menu-overlay');
    if (overlay) {
        overlay.classList.toggle('show');
    } else {
        overlay = document.createElement('div');
        overlay.className = 'menu-overlay';
        overlay.onclick = toggleMenu;
        document.body.appendChild(overlay);
        setTimeout(() => overlay.classList.add('show'), 10);
    }
}

// Confirm dose
function confirmDose(medicationId) {
    if (!confirm('Have you taken this medication?')) return;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../api/confirm_dose.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
                showAlert('Dose confirmed successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(res.error || 'Failed to confirm dose.', 'danger');
            }
        }
    };
    xhr.send('medication_id=' + medicationId + '&action=confirm');
}

// Send emergency alert
function sendEmergency() {
    if (!confirm('Are you sure you want to alert your family? This is an emergency!')) return;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../api/emergency.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
                showAlert('Emergency alert sent to your family!', 'danger');
            } else {
                showAlert(res.error || 'Failed to send alert.', 'danger');
            }
        }
    };
    xhr.send('action=alert');
}

// Resolve emergency
function resolveEmergency(alertId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../api/emergency.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
                showAlert('Emergency resolved.', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        }
    };
    xhr.send('action=resolve&alert_id=' + alertId);
}

// Acknowledge missed dose alert
function acknowledgeAlert(alertId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../api/acknowledge_alert.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
                showAlert('Alert acknowledged.', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        }
    };
    xhr.send('alert_id=' + alertId);
}

// Pill/color selector
function selectPill(element, inputId) {
    document.querySelectorAll('.pill-option').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    document.getElementById(inputId).value = element.dataset.value;
}

// Shape selector
function selectShape(element, inputId) {
    document.querySelectorAll('.shape-option').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    document.getElementById(inputId).value = element.dataset.value;
}

// Generate link code (for caregiver linking)
function generateLinkCode() {
    const btn = document.querySelector('.generate-code-btn');
    if (btn) btn.disabled = true;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../api/generate_code.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
                document.querySelector('.link-code-display .code').textContent = res.code;
                document.querySelector('.link-code-display .expiry').textContent =
                    'Expires in 60 seconds';
                if (btn) btn.disabled = false;
                startCodeTimer();
            } else {
                showAlert(res.error || 'Failed to generate code.', 'danger');
                if (btn) btn.disabled = false;
            }
        }
    };
    xhr.send('action=generate');
}

let codeTimer = null;

function startCodeTimer() {
    if (codeTimer) clearInterval(codeTimer);
    let seconds = 60;
    const expiryEl = document.querySelector('.link-code-display .expiry');
    codeTimer = setInterval(function () {
        seconds--;
        if (expiryEl) expiryEl.textContent = 'Expires in ' + seconds + ' seconds';
        if (seconds <= 0) {
            clearInterval(codeTimer);
            if (expiryEl) expiryEl.textContent = 'Code expired. Generate a new one.';
            document.querySelector('.link-code-display .code').textContent = '----';
        }
    }, 1000);
}

// Connect device (elderly enters code)
function connectDevice() {
    const code = document.getElementById('link_code').value;
    if (code.length !== 4) {
        showAlert('Please enter a 4-digit code.', 'warning');
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../api/connect_device.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
                showAlert('Device linked successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(res.error || 'Invalid or expired code.', 'danger');
            }
        }
    };
    xhr.send('code=' + code);
}

// Show alert message
function showAlert(message, type) {
    const container = document.querySelector('.main-content');
    const alert = document.createElement('div');
    alert.className = 'alert alert-' + type;
    alert.textContent = message;
    container.insertBefore(alert, container.firstChild);
    setTimeout(() => alert.remove(), 4000);
}

// Delete medication
function deleteMedication(medId) {
    if (!confirm('Delete this medication? This action cannot be undone.')) return;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../api/delete_medication.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
                showAlert('Medication deleted.', 'success');
                document.getElementById('med-' + medId)?.remove();
            } else {
                showAlert(res.error || 'Failed to delete.', 'danger');
            }
        }
    };
    xhr.send('medication_id=' + medId);
}

// Copy code to clipboard
function copyCode() {
    const codeEl = document.querySelector('.link-code-display .code');
    if (codeEl && codeEl.textContent !== '----') {
        navigator.clipboard.writeText(codeEl.textContent).then(function () {
            showAlert('Code copied!', 'success');
        });
    }
}

// Init on DOM ready
document.addEventListener('DOMContentLoaded', function () {
    // Close menu on outside click
    document.addEventListener('click', function (e) {
        const menu = document.getElementById('sideMenu');
        const toggle = document.querySelector('.nav-toggle');
        if (menu && menu.classList.contains('open') &&
            !menu.contains(e.target) &&
            toggle && !toggle.contains(e.target)) {
            toggleMenu();
        }
    });
});
