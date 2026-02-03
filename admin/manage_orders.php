<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

$message = '';
$alertClass = '';

// Handle Status Update
if (isset($_POST['update_status'])) {
    $order_id = $conn->real_escape_string($_POST['order_id']);
    $new_status = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE orders SET status='$new_status' WHERE id='$order_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "Order status updated successfully!";
        $alertClass = "alert-success";
    } else {
        $message = "Error updating status: " . $conn->error;
        $alertClass = "alert-error";
    }
}

// Handle Add Order (Admin)
if (isset($_POST['add_order'])) {
    $customerName = $conn->real_escape_string($_POST['customerName']);
    $serviceType = $conn->real_escape_string($_POST['serviceType']);
    $price = $conn->real_escape_string($_POST['price']);
    $status = $conn->real_escape_string($_POST['status']);

    // Admin created orders might not have a user_id if we didn't implement customer selection. 
    // For now, assign to current admin or a default user/NULL if allowed. 
    // START FIX: Assign to Admin ID for now or create a mechanism to select user. 
    // Schema has user_id NOT NULL. So we must use a valid user_id.
    $user_id = $_SESSION['user_id'];

    $orderNumber = 'BW' . date('Ymd') . rand(100, 999);
    $date = date('Y-m-d');

    $sql = "INSERT INTO orders (order_number, user_id, customer_name, customer_email, customer_phone, service_type, location, preferred_date, total_price, status)
            VALUES ('$orderNumber', '$user_id', '$customerName', 'admin-entry@example.com', '0000000000', '$serviceType', 'Admin Entry', '$date', '$price', '$status')";

    if ($conn->query($sql) === TRUE) {
        $message = "Order added successfully!";
        $alertClass = "alert-success";
    } else {
        $message = "Error adding order: " . $conn->error;
        $alertClass = "alert-error";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $conn->real_escape_string($_GET['delete']);
    $conn->query("DELETE FROM orders WHERE id='$id'");
    header("Location: manage_orders.php"); // Redirect to clear query param
    exit();
}

// Fetch Stats
$stats = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM orders")->fetch_assoc();

// Fetch Orders
$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");

// Fetch Services for Dropdown
$services_result = $conn->query("SELECT * FROM services ORDER BY name ASC");
$services_options = [];
if ($services_result->num_rows > 0) {
    while ($srv = $services_result->fetch_assoc()) {
        $services_options[] = $srv;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-shopping-cart"></i>
                <h1>Manage<span>Orders</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage_orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="manage_customers.php"><i class="fas fa-users"></i> Customers</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../public/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header"
            style="display: flex; justify-content: space-between; align-items: center; margin: 30px 0;">
            <h2>Order Management</h2>
            <button class="btn btn-primary" onclick="addNewOrder()">
                <i class="fas fa-plus-circle"></i> Add New Order
            </button>
        </div>

        <?php if ($message != ''): ?>
            <div class="alert <?php echo $alertClass; ?>" style="display: block; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Orders Table -->
        <div class="table-container">
            <table class="table" id="ordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders->num_rows > 0): ?>
                        <?php while ($row = $orders->fetch_assoc()): ?>
                            <?php
                            $badgeClass = 'badge-warning';
                            if ($row['status'] == 'completed')
                                $badgeClass = 'badge-success';
                            if ($row['status'] == 'cancelled')
                                $badgeClass = 'badge-danger';
                            if ($row['status'] == 'in_progress')
                                $badgeClass = 'badge-info'; // Assuming you might add badge-info style or map to warning
                            ?>
                            <tr>
                                <td><strong>
                                        <?php echo $row['order_number']; ?>
                                    </strong></td>
                                <td>
                                    <?php echo htmlspecialchars($row['customer_name']); ?>
                                </td>
                                <td>
                                    <?php echo ucfirst($row['service_type']); ?>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($row['preferred_date'])); ?>
                                </td>
                                <td>Rp
                                    <?php echo number_format($row['total_price'], 0, ',', '.'); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo strtoupper(str_replace('_', ' ', $row['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons" style="display: flex; gap: 5px;">
                                        <button class="btn btn-primary btn-sm"
                                            onclick="viewOrder('<?php echo $row['order_number']; ?>', '<?php echo $row['customer_name']; ?>', '<?php echo $row['service_type']; ?>', '<?php echo $row['status']; ?>', '<?php echo htmlspecialchars(addslashes(str_replace(array("\r", "\n"), ' ', $row['location']))); ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-secondary btn-sm"
                                            onclick="editOrder('<?php echo $row['id']; ?>', '<?php echo $row['status']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="manage_orders.php?delete=<?php echo $row['id']; ?>"
                                            class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Order Summary -->
        <div class="summary-cards"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 40px;">
            <div class="summary-card" style="background: #e8f4fc; padding: 20px; border-radius: var(--border-radius);">
                <h4>Total Orders</h4>
                <p class="stat-number">
                    <?php echo $stats['total']; ?>
                </p>
            </div>
            <div class="summary-card" style="background: #d4edda; padding: 20px; border-radius: var(--border-radius);">
                <h4>Completed</h4>
                <p class="stat-number">
                    <?php echo $stats['completed']; ?>
                </p>
            </div>
            <div class="summary-card" style="background: #fff3cd; padding: 20px; border-radius: var(--border-radius);">
                <h4>Pending</h4>
                <p class="stat-number">
                    <?php echo $stats['pending']; ?>
                </p>
            </div>
            <div class="summary-card" style="background: #f8d7da; padding: 20px; border-radius: var(--border-radius);">
                <h4>Cancelled</h4>
                <p class="stat-number">
                    <?php echo $stats['cancelled']; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Add Order Modal -->
    <div id="addOrderModal" class="modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1001; align-items: center; justify-content: center;">
        <div class="modal-content"
            style="background: white; padding: 30px; border-radius: var(--border-radius); width: 90%; max-width: 500px;">
            <h3><i class="fas fa-plus-circle"></i> Add New Order</h3>
            <form method="POST" action="manage_orders.php">
                <input type="hidden" name="add_order" value="1">
                <div class="form-group">
                    <label>Customer Name</label>
                    <input type="text" class="form-control" name="customerName" required>
                </div>
                <div class="form-group">
                    <label>Service Type</label>
                    <select class="form-control" name="serviceType" required>
                        <option value="">Select Service</option>
                        <?php foreach ($services_options as $srv): ?>
                            <option value="<?php echo htmlspecialchars($srv['slug']); ?>"
                                data-price="<?php echo htmlspecialchars($srv['price']); ?>">
                                <?php echo htmlspecialchars($srv['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Amount (Rp)</label>
                    <input type="number" class="form-control" name="price" id="orderPrice" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Save Order</button>
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('addOrderModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Status Modal -->
    <div id="editStatusModal" class="modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1001; align-items: center; justify-content: center;">
        <div class="modal-content"
            style="background: white; padding: 30px; border-radius: var(--border-radius); width: 90%; max-width: 400px;">
            <h3><i class="fas fa-edit"></i> Update Status</h3>
            <form method="POST" action="manage_orders.php">
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" id="editOrderId" name="order_id" value="">

                <div class="form-group">
                    <label>New Status</label>
                    <select class="form-control" id="editOrderStatus" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('editStatusModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function addNewOrder() {
            document.getElementById('addOrderModal').style.display = 'flex';
        }

        function editOrder(id, status) {
            document.getElementById('editOrderId').value = id;
            document.getElementById('editOrderStatus').value = status;
            document.getElementById('editStatusModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function viewOrder(number, name, service, status, location) {
            alert(`Order Details:\nID: ${number}\nCustomer: ${name}\nService: ${service}\nLocation: ${location}\nStatus: ${status}`);
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        }

        // Auto-fill price based on service selection
        document.querySelector('select[name="serviceType"]').addEventListener('change', function () {
            var selectedOption = this.options[this.selectedIndex];
            var price = selectedOption.getAttribute('data-price');
            if (price) {
                document.getElementById('orderPrice').value = price;
            } else {
                document.getElementById('orderPrice').value = '';
            }
        });
    </script>
    <footer
        style="text-align: center; padding: 20px; background: #fff; color: #666; margin-top: 40px; border-top: 1px solid #ddd;">
        <p>&copy; 23552011321_Arya Fauzan_23B_UASWEB1.</p>
    </footer>
</body>

</html>