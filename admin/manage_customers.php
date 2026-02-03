<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

$message = '';
$alertClass = '';

// Handle Add Customer
if (isset($_POST['add_customer'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    // Generate username from email or random
    $username = explode('@', $email)[0] . rand(100, 999);
    $password = password_hash('password123', PASSWORD_DEFAULT); // Default password

    // Check if email exists
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $message = "Email already exists!";
        $alertClass = "alert-error";
    } else {
        $sql = "INSERT INTO users (name, email, phone, username, password, role) 
                VALUES ('$name', '$email', '$phone', '$username', '$password', 'customer')";

        if ($conn->query($sql) === TRUE) {
            $message = "Customer added successfully! Default password: password123";
            $alertClass = "alert-success";
        } else {
            $message = "Error adding customer: " . $conn->error;
            $alertClass = "alert-error";
        }
    }
}

// Handle Update Customer
if (isset($_POST['update_customer'])) {
    $id = $conn->real_escape_string($_POST['customer_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);

    $sql = "UPDATE users SET name='$name', email='$email', phone='$phone' WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        $message = "Customer updated successfully!";
        $alertClass = "alert-success";
    } else {
        $message = "Error updating customer: " . $conn->error;
        $alertClass = "alert-error";
    }
}

// Handle Delete (optional, usually we don't delete customers with orders)
// Skipping delete for now to preserve data integrity, or implement soft delete.

// Fetch Customers with Stats
$sql = "SELECT u.*, 
       COUNT(o.id) as total_orders, 
       COALESCE(SUM(o.total_price), 0) as total_spent 
       FROM users u 
       LEFT JOIN orders o ON u.id = o.user_id 
       WHERE u.role = 'customer' 
       GROUP BY u.id
       ORDER BY u.created_at DESC";
$customers = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-users"></i>
                <h1>Manage<span>Customers</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="manage_customers.php" class="active"><i class="fas fa-users"></i> Customers</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../public/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2>Customer Management</h2>

        <?php if ($message != ''): ?>
            <div class="alert <?php echo $alertClass; ?>" style="display: block; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Customers Grid -->
        <div class="customers-grid"
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; margin-top: 40px;">
            <?php if ($customers->num_rows > 0): ?>
                <?php while ($row = $customers->fetch_assoc()): ?>
                    <div class="customer-card"
                        style="background: white; padding: 25px; border-radius: var(--border-radius); box-shadow: var(--shadow);">
                        <div class="customer-header"
                            style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                            <div class="customer-avatar"
                                style="width: 60px; height: 60px; background: var(--secondary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h3>
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </h3>
                                <p style="color: #666;">ID: #
                                    <?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?>
                                </p>
                            </div>
                        </div>

                        <div class="customer-info" style="margin-bottom: 20px;">
                            <p><i class="fas fa-envelope"></i>
                                <?php echo htmlspecialchars($row['email']); ?>
                            </p>
                            <p><i class="fas fa-phone"></i>
                                <?php echo htmlspecialchars($row['phone']); ?>
                            </p>
                            <p><i class="fas fa-calendar"></i> Joined:
                                <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                            </p>
                        </div>

                        <div class="customer-stats"
                            style="display: flex; justify-content: space-between; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                            <div style="text-align: center;">
                                <h4 style="color: var(--secondary-color);">
                                    <?php echo $row['total_orders']; ?>
                                </h4>
                                <p style="font-size: 0.9rem;">Orders</p>
                            </div>
                            <div style="text-align: center;">
                                <h4 style="color: var(--success-color);">Rp
                                    <?php echo number_format($row['total_spent'], 0, ',', '.'); ?>
                                </h4>
                                <p style="font-size: 0.9rem;">Total Spent</p>
                            </div>
                        </div>

                        <div class="customer-actions" style="display: flex; gap: 10px;">
                            <button class="btn btn-primary btn-sm"
                                onclick="editCustomer('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars(addslashes($row['name'])); ?>', '<?php echo htmlspecialchars(addslashes($row['email'])); ?>', '<?php echo htmlspecialchars(addslashes($row['phone'])); ?>')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-comment"></i> Contact
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No customers found.</p>
            <?php endif; ?>
        </div>

        <!-- Add Customer Button -->
        <div style="text-align: center; margin: 40px 0;">
            <button class="btn btn-primary" onclick="addCustomer()">
                <i class="fas fa-user-plus"></i> Add New Customer
            </button>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div id="customerModal" class="modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1001; align-items: center; justify-content: center;">
        <div class="modal-content"
            style="background: white; padding: 30px; border-radius: var(--border-radius); width: 90%; max-width: 500px;">
            <h3><i class="fas fa-user-plus"></i> Add New Customer</h3>
            <form method="POST" action="manage_customers.php">
                <input type="hidden" name="add_customer" value="1">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" class="form-control" name="phone" required>
                </div>
                <p class="text-muted" style="font-size: 0.9em; margin-top: 10px;">Default password will be
                    <strong>password123</strong>
                </p>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('customerModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div id="editCustomerModal" class="modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1001; align-items: center; justify-content: center;">
        <div class="modal-content"
            style="background: white; padding: 30px; border-radius: var(--border-radius); width: 90%; max-width: 500px;">
            <h3><i class="fas fa-edit"></i> Edit Customer</h3>
            <form method="POST" action="manage_customers.php">
                <input type="hidden" name="update_customer" value="1">
                <input type="hidden" id="edit_customer_id" name="customer_id">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-control" id="edit_name" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" id="edit_email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Update Customer</button>
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('editCustomerModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function addCustomer() {
            document.getElementById('customerModal').style.display = 'flex';
        }

        function editCustomer(id, name, email, phone) {
            document.getElementById('edit_customer_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('editCustomerModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        }
    </script>
    <footer
        style="text-align: center; padding: 20px; background: #fff; color: #666; margin-top: 40px; border-top: 1px solid #ddd;">
        <p>&copy; 23552011321_Arya Fauzan_23B_UASWEB1.</p>
    </footer>
</body>

</html>