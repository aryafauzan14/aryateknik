<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$alertClass = '';

// Pre-fill user data
$user_id = $_SESSION['user_id'];

// Load Settings
$settingsFile = '../config/settings.json';
$settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : [];
$taxRate = isset($settings['taxRate']) ? floatval($settings['taxRate']) : 10;

$sql_user = "SELECT name, email, phone, address FROM users WHERE id = $user_id";
$result_user = $conn->query($sql_user);
$user_data = $result_user->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customerName = $conn->real_escape_string($_POST['customerName']);
    $customerEmail = $conn->real_escape_string($_POST['customerEmail']);
    $customerPhone = $conn->real_escape_string($_POST['customerPhone']);
    $serviceType = $conn->real_escape_string($_POST['serviceType']);
    $location = $conn->real_escape_string($_POST['location']);
    $date = $conn->real_escape_string($_POST['date']);
    $notes = $conn->real_escape_string($_POST['notes']);

    // Calculate price (Normally fetch from DB, but using hardcoded logic for simplicity to match frontend JS)
    $prices = [
        'drilling' => 5000000,
        'maintenance' => 1500000,
        'repair' => 2000000,
        'installation' => 3000000
    ];

    $price = isset($prices[$serviceType]) ? $prices[$serviceType] : 0;

    // Special Rule: If tax rate is 1%, tax is flat 100,000
    if ($taxRate == 1) {
        $tax = 100000;
    } else {
        $tax = $price * ($taxRate / 100);
    }

    $total_price = $price + $tax;

    // Generate Order ID
    $orderNumber = 'BW' . date('Ymd') . rand(100, 999);

    $sql = "INSERT INTO orders (order_number, user_id, customer_name, customer_email, customer_phone, service_type, location, preferred_date, notes, total_price, status)
            VALUES ('$orderNumber', '$user_id', '$customerName', '$customerEmail', '$customerPhone', '$serviceType', '$location', '$date', '$notes', '$total_price', 'pending')";

    if ($conn->query($sql) === TRUE) {
        $alertClass = "alert-success";
        $message = "Order placed successfully! Order ID: <strong>$orderNumber</strong>";
    } else {
        $alertClass = "alert-error";
        $message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Service - Borewell System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-water"></i>
                <h1>Borewell<span>System</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="services.php"><i class="fas fa-tools"></i> Services</a></li>
                    <li><a href="order.php" class="active"><i class="fas fa-shopping-cart"></i> Order</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-calendar-check"></i> Book a Service</h2>
                <p>Fill in the details below to schedule your service</p>
            </div>

            <?php if ($message != ''): ?>
                <div id="orderMessage" class="alert <?php echo $alertClass; ?>" style="display: block;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form id="orderForm" action="order.php" method="POST">
                <div class="form-group">
                    <label for="customerName"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" id="customerName" name="customerName" class="form-control"
                        placeholder="Enter your full name" value="<?php echo htmlspecialchars($user_data['name']); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="customerEmail"><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" id="customerEmail" name="customerEmail" class="form-control"
                        placeholder="Enter your email" value="<?php echo htmlspecialchars($user_data['email']); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="customerPhone"><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="tel" id="customerPhone" name="customerPhone" class="form-control"
                        placeholder="Enter your phone number"
                        value="<?php echo htmlspecialchars($user_data['phone']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="serviceType"><i class="fas fa-concierge-bell"></i> Select Service</label>
                    <select id="serviceType" name="serviceType" class="form-control" required>
                        <option value="">-- Choose a Service --</option>
                        <option value="drilling">Borewell Drilling - Rp 5,000,000</option>
                        <option value="maintenance">Maintenance - Rp 1,500,000</option>
                        <option value="repair">Emergency Repair - Rp 2,000,000</option>
                        <option value="installation">Pump Installation - Rp 3,000,000</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="location"><i class="fas fa-map-marker-alt"></i> Service Location</label>
                    <textarea id="location" name="location" class="form-control" rows="3"
                        placeholder="Enter complete address including landmarks"
                        required><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="date"><i class="fas fa-calendar-alt"></i> Preferred Date</label>
                    <input type="date" id="date" name="date" class="form-control" min="<?php echo date('Y-m-d'); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="notes"><i class="fas fa-sticky-note"></i> Additional Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"
                        placeholder="Any special requirements or instructions"></textarea>
                </div>

                <div class="price-summary"
                    style="margin: 20px 0; padding: 15px; background: #e8f4fc; border-radius: 5px;">
                    <h4>Price Summary:</h4>
                    <p>Service Fee: <span id="priceDisplay">Rp 0</span></p>
                    <p>Tax (<?php echo $taxRate; ?>%): <span id="taxDisplay">Rp 0</span></p>
                    <p><strong>Total: <span id="totalDisplay">Rp 0</span></strong></p>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check"></i> Confirm Booking
                </button>
            </form>
        </div>
    </div>

    <script>
        // Service prices
        const servicePrices = {
            'drilling': 5000000,
            'maintenance': 1500000,
            'repair': 2000000,
            'installation': 3000000
        };

        // Tax Rate from PHP
        const taxRateData = <?php echo $taxRate; ?>;

        // Update price when service changes
        document.getElementById('serviceType').addEventListener('change', function () {
            updatePrice();
        });

        function updatePrice() {
            const serviceType = document.getElementById('serviceType').value;
            const price = servicePrices[serviceType] || 0;

            let tax = 0;
            if (taxRateData == 1) {
                tax = 100000;
            } else {
                tax = price * (taxRateData / 100);
            }

            const total = price + tax;

            document.getElementById('priceDisplay').textContent = formatCurrency(price);
            document.getElementById('taxDisplay').textContent = formatCurrency(tax);
            document.getElementById('totalDisplay').textContent = formatCurrency(total);
        }

        function formatCurrency(amount) {
            return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    </script>
    <footer
        style="text-align: center; padding: 20px; background: #fff; color: #666; margin-top: 40px; border-top: 1px solid #ddd;">
        <p>&copy; 23552011321_Arya Fauzan_23B_UASWEB1.</p>
    </footer>
</body>

</html>