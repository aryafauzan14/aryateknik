<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

$message = '';
$alertClass = '';
$activeTab = 'general';

$settingsFile = '../config/settings.json';
$currentSettings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : [];

// Function to save settings
function saveSettings($newSettings, $file)
{
    global $currentSettings;
    $updatedSettings = array_merge($currentSettings, $newSettings);
    if (file_put_contents($file, json_encode($updatedSettings, JSON_PRETTY_PRINT))) {
        return true;
    }
    return false;
}

// Handle Form Submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_general'])) {
        $generalSettings = [
            'companyName' => $_POST['companyName'],
            'companyAddress' => $_POST['companyAddress'],
            'contactEmail' => $_POST['contactEmail'],
            'contactPhone' => $_POST['contactPhone'],
            'websiteUrl' => $_POST['websiteUrl']
        ];
        if (saveSettings($generalSettings, $settingsFile)) {
            $message = "General settings saved successfully!";
            $alertClass = "alert-success";
        }
        $activeTab = 'general';
    } elseif (isset($_POST['update_services'])) {
        $serviceSettings = [
            'serviceDuration' => $_POST['serviceDuration'],
            'warrantyPeriod' => $_POST['warrantyPeriod'],
            'taxRate' => $_POST['taxRate'],
            'currency' => $_POST['currency']
        ];
        if (saveSettings($serviceSettings, $settingsFile)) {
            $message = "Service settings saved successfully!";
            $alertClass = "alert-success";
        }
        $activeTab = 'services';
    } elseif (isset($_POST['update_notifications'])) {
        $notificationSettings = [
            'emailNotifications' => isset($_POST['emailNotifications']),
            'smsNotifications' => isset($_POST['smsNotifications']),
            'notificationEmail' => $_POST['notificationEmail'],
            'orderConfirmation' => $_POST['orderConfirmation']
        ];
        if (saveSettings($notificationSettings, $settingsFile)) {
            $message = "Notification settings saved successfully!";
            $alertClass = "alert-success";
        }
        $activeTab = 'notifications';
    } elseif (isset($_POST['update_security'])) {
        // Handle Password Change
        if (!empty($_POST['newPassword'])) {
            $newPassword = $_POST['newPassword'];
            $confirmPassword = $_POST['confirmPassword'];
            $userId = $_SESSION['user_id'];

            if ($newPassword === $confirmPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password='$hashedPassword' WHERE id='$userId'";
                if ($conn->query($sql) === TRUE) {
                    $message = "Password updated successfully! ";
                    $alertClass = "alert-success";
                } else {
                    $message = "Error updating password: " . $conn->error;
                    $alertClass = "alert-error";
                }
            } else {
                $message = "Passwords do not match!";
                $alertClass = "alert-error";
            }
        }

        // Save other security settings
        $securitySettings = [
            'sessionTimeout' => $_POST['sessionTimeout'],
            'maxLoginAttempts' => $_POST['maxLoginAttempts'],
            'passwordExpiry' => $_POST['passwordExpiry'],
            'forceSSL' => isset($_POST['forceSSL']),
            'twoFactorAuth' => isset($_POST['twoFactorAuth'])
        ];
        saveSettings($securitySettings, $settingsFile);

        if (empty($message)) {
            $message = "Security settings saved!";
            $alertClass = "alert-success";
        }
        $activeTab = 'security';
    }

    // Refresh settings
    $currentSettings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : [];
}

// Helper to get setting value safely
function getSetting($key, $default = '')
{
    global $currentSettings;
    return isset($currentSettings[$key]) ? $currentSettings[$key] : $default;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tab-button {
            padding: 12px 30px;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            font-weight: 600;
            color: #666;
        }

        .tab-button.active {
            color: var(--secondary-color);
            border-bottom-color: var(--secondary-color);
            background: #f8f9fa;
        }

        .tab-content {
            box-shadow: var(--shadow);
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox input[type="checkbox"] {
            width: auto;
            transform: scale(1.2);
        }
    </style>
</head>

<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-cog"></i>
                <h1>System<span>Settings</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="manage_customers.php"><i class="fas fa-users"></i> Customers</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../public/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2>System Settings</h2>

        <?php if ($message != ''): ?>
            <div id="settingsMessage" class="alert <?php echo $alertClass; ?>" style="display: block;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="settings-tabs" style="margin: 30px 0;">
            <div style="display: flex; border-bottom: 2px solid #ddd; flex-wrap: wrap;">
                <button class="tab-button <?php echo $activeTab == 'general' ? 'active' : ''; ?>"
                    onclick="openTab(event, 'general')">General</button>
                <button class="tab-button <?php echo $activeTab == 'services' ? 'active' : ''; ?>"
                    onclick="openTab(event, 'services')">Services</button>
                <button class="tab-button <?php echo $activeTab == 'notifications' ? 'active' : ''; ?>"
                    onclick="openTab(event, 'notifications')">Notifications</button>
                <button class="tab-button <?php echo $activeTab == 'security' ? 'active' : ''; ?>"
                    onclick="openTab(event, 'security')">Security</button>
            </div>

            <!-- General Settings -->
            <div id="general" class="tab-content <?php echo $activeTab == 'general' ? 'active' : ''; ?>"
                style="background: white; padding: 30px; border-radius: 0 0 var(--border-radius) var(--border-radius); margin-top: -2px;">
                <form method="POST" action="settings.php">
                    <input type="hidden" name="update_general" value="1">
                    <div class="form-group">
                        <label>Company Name</label>
                        <input type="text" class="form-control" name="companyName"
                            value="<?php echo htmlspecialchars(getSetting('companyName', 'Borewell Solutions')); ?>">
                    </div>

                    <div class="form-group">
                        <label>Company Address</label>
                        <textarea class="form-control" name="companyAddress"
                            rows="3"><?php echo htmlspecialchars(getSetting('companyAddress', 'Jl. Industri Raya No. 123, Jakarta')); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Contact Email</label>
                        <input type="email" class="form-control" name="contactEmail"
                            value="<?php echo htmlspecialchars(getSetting('contactEmail', 'contact@borewell.com')); ?>">
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" class="form-control" name="contactPhone"
                            value="<?php echo htmlspecialchars(getSetting('contactPhone', '+62 21 1234 5678')); ?>">
                    </div>

                    <div class="form-group">
                        <label>Website URL</label>
                        <input type="url" class="form-control" name="websiteUrl"
                            value="<?php echo htmlspecialchars(getSetting('websiteUrl', 'https://borewellsystem.com')); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">Save General Settings</button>
                </form>
            </div>

            <!-- Services Settings -->
            <div id="services" class="tab-content <?php echo $activeTab == 'services' ? 'active' : ''; ?>"
                style="background: white; padding: 30px; border-radius: 0 0 var(--border-radius) var(--border-radius); margin-top: -2px;">
                <h4>Service Settings</h4>
                <form method="POST" action="settings.php">
                    <input type="hidden" name="update_services" value="1">
                    <div class="form-group">
                        <label>Default Service Duration (days)</label>
                        <input type="number" class="form-control" name="serviceDuration"
                            value="<?php echo htmlspecialchars(getSetting('serviceDuration', '3')); ?>" min="1">
                    </div>

                    <div class="form-group">
                        <label>Default Warranty Period (months)</label>
                        <input type="number" class="form-control" name="warrantyPeriod"
                            value="<?php echo htmlspecialchars(getSetting('warrantyPeriod', '12')); ?>" min="0">
                    </div>

                    <div class="form-group">
                        <label>Tax Rate (%)</label>
                        <input type="number" class="form-control" name="taxRate"
                            value="<?php echo htmlspecialchars(getSetting('taxRate', '10')); ?>" step="0.1" min="0"
                            max="100">
                    </div>

                    <div class="form-group">
                        <label>Currency</label>
                        <select class="form-control" name="currency">
                            <option value="IDR" <?php echo getSetting('currency') == 'IDR' ? 'selected' : ''; ?>>
                                Indonesian Rupiah (IDR)</option>
                            <option value="USD" <?php echo getSetting('currency') == 'USD' ? 'selected' : ''; ?>>US
                                Dollar (USD)</option>
                            <option value="EUR" <?php echo getSetting('currency') == 'EUR' ? 'selected' : ''; ?>>Euro
                                (EUR)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Service Settings</button>
                </form>
            </div>

            <!-- Notifications Settings -->
            <div id="notifications" class="tab-content <?php echo $activeTab == 'notifications' ? 'active' : ''; ?>"
                style="background: white; padding: 30px; border-radius: 0 0 var(--border-radius) var(--border-radius); margin-top: -2px;">
                <h4>Notification Settings</h4>
                <form method="POST" action="settings.php">
                    <input type="hidden" name="update_notifications" value="1">
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" id="emailNotifications" name="emailNotifications" <?php echo getSetting('emailNotifications', true) ? 'checked' : ''; ?>>
                            <label for="emailNotifications">Enable Email Notifications</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" id="smsNotifications" name="smsNotifications" <?php echo getSetting('smsNotifications', true) ? 'checked' : ''; ?>>
                            <label for="smsNotifications">Enable SMS Notifications</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notification Email</label>
                        <input type="email" class="form-control" name="notificationEmail"
                            value="<?php echo htmlspecialchars(getSetting('notificationEmail', 'notifications@borewell.com')); ?>">
                    </div>

                    <div class="form-group">
                        <label>Send Order Confirmation</label>
                        <select class="form-control" name="orderConfirmation">
                            <option value="immediate" <?php echo getSetting('orderConfirmation') == 'immediate' ? 'selected' : ''; ?>>Immediately</option>
                            <option value="daily" <?php echo getSetting('orderConfirmation') == 'daily' ? 'selected' : ''; ?>>Daily Summary</option>
                            <option value="weekly" <?php echo getSetting('orderConfirmation') == 'weekly' ? 'selected' : ''; ?>>Weekly Summary</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                </form>
            </div>

            <!-- Security Settings -->
            <div id="security" class="tab-content <?php echo $activeTab == 'security' ? 'active' : ''; ?>"
                style="background: white; padding: 30px; border-radius: 0 0 var(--border-radius) var(--border-radius); margin-top: -2px;">
                <h4>Security Settings</h4>
                <form method="POST" action="settings.php">
                    <input type="hidden" name="update_security" value="1">
                    <div class="form-group">
                        <label>Change Password</label>
                        <input type="password" class="form-control" name="newPassword" placeholder="New Password">
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" class="form-control" name="confirmPassword"
                            placeholder="Confirm New Password">
                    </div>
                    <hr>

                    <div class="form-group">
                        <label>Session Timeout (minutes)</label>
                        <input type="number" class="form-control" name="sessionTimeout"
                            value="<?php echo htmlspecialchars(getSetting('sessionTimeout', '30')); ?>" min="5">
                    </div>

                    <div class="form-group">
                        <label>Max Login Attempts</label>
                        <input type="number" class="form-control" name="maxLoginAttempts"
                            value="<?php echo htmlspecialchars(getSetting('maxLoginAttempts', '5')); ?>" min="1">
                    </div>

                    <div class="form-group">
                        <label>Password Expiry (days)</label>
                        <input type="number" class="form-control" name="passwordExpiry"
                            value="<?php echo htmlspecialchars(getSetting('passwordExpiry', '90')); ?>" min="1">
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" id="forceSSL" name="forceSSL" <?php echo getSetting('forceSSL', true) ? 'checked' : ''; ?>>
                            <label for="forceSSL">Force HTTPS (SSL)</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" id="twoFactorAuth" name="twoFactorAuth" <?php echo getSetting('twoFactorAuth', false) ? 'checked' : ''; ?>>
                            <label for="twoFactorAuth">Enable Two-Factor Authentication</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Security Settings</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Tab switching function
        function openTab(evt, tabName) {
            // Hide all tab contents
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].className = tabContents[i].className.replace(' active', '');
            }

            // Remove active class from all buttons
            const tabButtons = document.getElementsByClassName('tab-button');
            for (let i = 0; i < tabButtons.length; i++) {
                tabButtons[i].className = tabButtons[i].className.replace(' active', '');
            }

            // Show current tab and set button as active
            document.getElementById(tabName).className += ' active';
            evt.currentTarget.className += ' active';
        }
    </script>
    <footer
        style="text-align: center; padding: 20px; background: #fff; color: #666; margin-top: 40px; border-top: 1px solid #ddd;">
        <p>&copy; 23552011321_Arya Fauzan_23B_UASWEB1.</p>
    </footer>
</body>

</html>