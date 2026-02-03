<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Borewell System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Admin Header -->
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-tachometer-alt"></i>
                <h1>Admin<span>Dashboard</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="manage_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="manage_customers.php"><i class="fas fa-users"></i> Customers</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../public/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <!-- Welcome Message -->
        <div class="welcome-section"
            style="margin: 30px 0; padding: 20px; background: white; border-radius: var(--border-radius);">
            <h2>Welcome,
                <?php echo htmlspecialchars($_SESSION['name']); ?>!
            </h2>
            <p>You are logged in as Administrator.</p>
        </div>

        <!-- Stats Cards -->
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Total Orders</h3>
                <p class="stat-number">1,247</p>
                <p><i class="fas fa-chart-line"></i> +12% from last month</p>
            </div>

            <div class="dashboard-card">
                <h3>Active Customers</h3>
                <p class="stat-number">542</p>
                <p><i class="fas fa-users"></i> Total registered users</p>
            </div>

            <div class="dashboard-card">
                <h3>Revenue This Month</h3>
                <p class="stat-number">Rp 45.2 Jt</p>
                <p><i class="fas fa-money-bill-wave"></i> +8% from last month</p>
            </div>

            <div class="dashboard-card">
                <h3>Pending Orders</h3>
                <p class="stat-number">23</p>
                <p><i class="fas fa-clock"></i> Need attention</p>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="table-container">
            <h3><i class="fas fa-history"></i> Recent Orders</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>BW2024-001</td>
                        <td>Budi Santoso</td>
                        <td>Borewell Drilling</td>
                        <td>2024-01-15</td>
                        <td><span class="badge badge-success">Completed</span></td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="viewOrder('BW2024-001')">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>BW2024-002</td>
                        <td>Siti Nurbaya</td>
                        <td>Pump Installation</td>
                        <td>2024-01-16</td>
                        <td><span class="badge badge-warning">In Progress</span></td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="viewOrder('BW2024-002')">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>BW2024-003</td>
                        <td>Ahmad Rizki</td>
                        <td>Maintenance</td>
                        <td>2024-01-17</td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="viewOrder('BW2024-003')">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions" style="margin: 40px 0; display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="manage_orders.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Add New Order
            </a>
            <a href="reports.php" class="btn btn-secondary">
                <i class="fas fa-download"></i> Generate Report
            </a>
            <a href="manage_customers.php" class="btn btn-success">
                <i class="fas fa-user-plus"></i> Add Customer
            </a>
        </div>
    </div>

    <script>
        function viewOrder(orderId) {
            alert('Viewing order: ' + orderId + '\n\nIn a real application, this would open order details.');
        }
    </script>
    <footer
        style="text-align: center; padding: 20px; background: #fff; color: #666; margin-top: 40px; border-top: 1px solid #ddd;">
        <p>&copy; 23552011321_Arya Fauzan_23B_UASWEB1.</p>
    </footer>
</body>

</html>