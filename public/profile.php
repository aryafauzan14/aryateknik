<?php
session_start();
require_once '../config/db.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$alertClass = '';

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);

    // Check if email is taken by another user
    $check = $conn->query("SELECT id FROM users WHERE email='$email' AND id != $user_id");
    if ($check->num_rows > 0) {
        $message = "Email already in use by another account.";
        $alertClass = "alert-error";
    } else {
        $sql = "UPDATE users SET name='$name', phone='$phone', email='$email', address='$address' WHERE id=$user_id";
        if ($conn->query($sql) === TRUE) {
            $message = "Profile updated successfully!";
            $alertClass = "alert-success";
            $_SESSION['user_name'] = $name; // Update session
        } else {
            $message = "Error updating profile: " . $conn->error;
            $alertClass = "alert-error";
        }
    }
}

// Fetch User Data
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Fetch Order History
$orders = [];
$order_sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$order_result = $conn->query($order_sql);
while ($row = $order_result->fetch_assoc()) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Borewell System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
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
                    <li><a href="order.php"><i class="fas fa-shopping-cart"></i> Order</a></li>
                    <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <!-- Messages -->
        <?php if ($message != ''): ?>
            <div class="alert <?php echo $alertClass; ?>" style="margin: 20px 0;">
                <i class="fas fa-info-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Profile Content -->
        <div id="profileContent">
            <!-- User Info -->
            <div class="profile-card" style="margin-bottom: 30px;">
                <div class="profile-header"
                    style="display: flex; align-items: center; gap: 20px; padding: 30px; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow);">
                    <div class="avatar"
                        style="width: 100px; height: 100px; background: var(--secondary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($user['address'] ?? 'No address set'); ?></p>
                        <p><i class="fas fa-user-tag"></i> <?php echo ucfirst($user['role']); ?></p>
                        <p><i class="fas fa-calendar-alt"></i> Member since:
                            <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div style="margin: 30px 0; display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="order.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Book New Service
                </a>
                <button class="btn btn-secondary" onclick="openEditModal()">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
                <button class="btn btn-success" onclick="contactSupport()">
                    <i class="fas fa-headset"></i> Contact Support
                </button>
            </div>

            <!-- My Orders -->
            <div class="table-container">
                <h3><i class="fas fa-history"></i> My Orders</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $order): ?>
                                <?php
                                $statusClass = 'badge-warning';
                                if ($order['status'] == 'completed')
                                    $statusClass = 'badge-success';
                                elseif ($order['status'] == 'cancelled')
                                    $statusClass = 'badge-danger';
                                elseif ($order['status'] == 'in_progress')
                                    $statusClass = 'badge-primary';
                                ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($order['service_type']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                    <td>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">You have no orders yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Profile</h2>
            <form method="POST" action="profile.php">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control"
                        value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control"
                        value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control"
                        value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Default Address</label>
                    <textarea name="address" class="form-control"
                        rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal() {
            document.getElementById('editProfileModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editProfileModal').style.display = 'none';
        }

        function contactSupport() {
            window.location.href = "mailto:support@borewellsystem.com";
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            if (event.target == document.getElementById('editProfileModal')) {
                closeEditModal();
            }
        }
    </script>
    <footer
        style="text-align: center; padding: 20px; background: #fff; color: #666; margin-top: 40px; border-top: 1px solid #ddd;">
        <p>&copy; 23552011321_Arya Fauzan_23B_UASWEB1.</p>
    </footer>
</body>

</html>