<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

// 1. Monthly Revenue (Last 6 Months)
$revenue_data = [];
for ($i = 5; $i >= 0; $i--) {
    $dateObj = new DateTime("-$i months");
    $month_key = $dateObj->format('Y-m');
    $month_name = $dateObj->format('M');

    $sql = "SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month_key' AND status != 'cancelled'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $revenue_data[] = ['month' => $month_name, 'revenue' => (float) $row['total']];
}

// 2. Service Distribution
$service_counts = [];
$colors = [
    'drilling' => '#3498db',
    'maintenance' => '#2ecc71',
    'repair' => '#e74c3c',
    'installation' => '#f39c12',
    'other' => '#9b59b6'
];
$total_orders = 0;

$sql = "SELECT service_type, COUNT(*) as count FROM orders GROUP BY service_type";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $service_counts[] = [
        'service' => ucfirst($row['service_type']),
        'count' => (int) $row['count'],
        'color' => isset($colors[$row['service_type']]) ? $colors[$row['service_type']] : $colors['other']
    ];
    $total_orders += $row['count'];
}

// Calculate percentages
foreach ($service_counts as &$item) {
    $item['percentage'] = $total_orders > 0 ? round(($item['count'] / $total_orders) * 100, 1) : 0;
}
unset($item);

// 3. Recent Activities (Union of New Orders and New Users)
$activities = [];

// Recent Orders
$sql_orders = "SELECT created_at, CONCAT('New order #', order_number, ' placed') as activity, customer_name as user FROM orders ORDER BY created_at DESC LIMIT 5";
$res_orders = $conn->query($sql_orders);
while ($row = $res_orders->fetch_assoc()) {
    $activities[] = $row;
}

// Recent Users
$sql_users = "SELECT created_at, 'New customer registered' as activity, name as user FROM users WHERE role='customer' ORDER BY created_at DESC LIMIT 5";
$res_users = $conn->query($sql_users);
while ($row = $res_users->fetch_assoc()) {
    $activities[] = $row;
}

// Sort combined activities by date
usort($activities, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$activities = array_slice($activities, 0, 10);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Export Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>

<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-chart-bar"></i>
                <h1>System<span>Reports</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="manage_customers.php"><i class="fas fa-users"></i> Customers</a></li>
                    <li><a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../public/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container" id="reportContent">
        <h2>System Reports & Analytics</h2>

        <!-- Report Controls -->
        <div class="report-controls"
            style="background: white; padding: 20px; border-radius: var(--border-radius); margin: 30px 0;">
            <div
                style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
                <p>Data shown for the last 6 months.</p>
                <div class="export-buttons" style="display: flex; gap: 10px;">
                    <button class="btn btn-primary" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                    <button class="btn btn-success" onclick="exportToExcel()"
                        style="background-color: #28a745; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="chart-container"
            style="background: white; padding: 30px; border-radius: var(--border-radius); margin-bottom: 30px;">
            <h3><i class="fas fa-money-bill-wave"></i> Monthly Revenue (Last 6 Months)</h3>
            <div id="revenueChart"
                style="height: 300px; margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                <!-- Chart will be generated here -->
            </div>
        </div>

        <!-- Service Distribution -->
        <div class="chart-container"
            style="background: white; padding: 30px; border-radius: var(--border-radius); margin-bottom: 30px;">
            <h3><i class="fas fa-chart-pie"></i> Service Distribution</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 20px;">
                <div id="serviceDistribution">
                    <!-- Distribution will be generated here -->
                </div>

                <div style="display: flex; flex-direction: column; justify-content: center;">
                    <div id="pieChart"
                        style="width: 200px; height: 200px; margin: 0 auto; <?php if (empty($service_counts))
                            echo 'background: #eee; border-radius:50%; display:flex; align-items:center; justify-content:center;'; ?>">
                        <?php if (empty($service_counts))
                            echo 'No data'; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="table-container">
            <h3><i class="fas fa-history"></i> Recent Activities</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Activity</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody id="activityLog">
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td><?php echo date('Y-m-d H:i', strtotime($activity['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                                <td><?php echo htmlspecialchars($activity['user']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">No recent activity.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Data from PHP
        const monthlyRevenue = <?php echo json_encode($revenue_data); ?>;
        const serviceDistribution = <?php echo json_encode($service_counts); ?>;

        // Generate revenue chart
        function generateRevenueChart() {
            const chartDiv = document.getElementById('revenueChart');
            chartDiv.innerHTML = '';

            // Calculate max revenue to scale bars
            const maxRevenue = Math.max(...monthlyRevenue.map(m => m.revenue)) || 1;

            monthlyRevenue.forEach(item => {
                const barHeight = (item.revenue / maxRevenue) * 100;

                const barContainer = document.createElement('div');
                barContainer.style.cssText = 'display: flex; align-items: center; margin-bottom: 15px;';

                barContainer.innerHTML = `
                    <div style="width: 50px;">${item.month}</div>
                    <div style="flex: 1; background: #e8f4fc; height: 30px; border-radius: 3px; position: relative;">
                        <div style="background: var(--secondary-color); height: 100%; width: ${barHeight}%; border-radius: 3px; min-width: 0px;"></div>
                        <span style="position: absolute; right: 10px; top: 5px; font-weight: bold; color: #333;">
                            Rp ${(item.revenue / 1000000).toFixed(1)}M
                        </span>
                    </div>
                `;

                chartDiv.appendChild(barContainer);
            });
        }

        // Generate service distribution
        function generateServiceDistribution() {
            const distributionDiv = document.getElementById('serviceDistribution');
            const pieChartDiv = document.getElementById('pieChart');

            if (serviceDistribution.length === 0) return;

            distributionDiv.innerHTML = '';
            pieChartDiv.innerHTML = '';

            // Generate distribution bars
            serviceDistribution.forEach(item => {
                const barContainer = document.createElement('div');
                barContainer.style.cssText = 'margin-bottom: 15px;';

                barContainer.innerHTML = `
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>${item.service}</span>
                        <span>${item.percentage}%</span>
                    </div>
                    <div style="background: #f0f0f0; height: 10px; border-radius: 5px;">
                        <div style="background: ${item.color}; height: 100%; width: ${item.percentage}%; border-radius: 5px;"></div>
                    </div>
                `;

                distributionDiv.appendChild(barContainer);
            });

            // Generate pie chart (simplified version)
            // Create conic gradient for pie chart
            let gradient = '';
            let accumulated = 0;

            serviceDistribution.forEach((item, index) => {
                const start = accumulated;
                const end = accumulated + item.percentage;
                // Important: Conic gradient needs colors and stops
                gradient += `${item.color} ${start}% ${end}%`;
                if (index < serviceDistribution.length - 1) gradient += ', ';
                accumulated = end;
            });

            pieChartDiv.innerHTML = `<div style="width: 200px; height: 200px; border-radius: 50%; background: conic-gradient(${gradient});"></div>`;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            generateRevenueChart();
            generateServiceDistribution();
        });

        // Export to PDF
        function exportToPDF() {
            const element = document.getElementById('reportContent');
            const opt = {
                margin: 0.5,
                filename: 'Borewell_Reports_' + new Date().toISOString().slice(0, 10) + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            };

            // Clone container to modify for PDF only (e.g. hide buttons)
            const clone = element.cloneNode(true);
            clone.querySelector('.page-header')?.remove(); // Optional: remove header if causing issues
            clone.querySelector('.report-controls').style.display = 'none'; // Hide controls in PDF

            html2pdf().set(opt).from(clone).save();
        }

        // Export to Excel
        function exportToExcel() {
            // 1. Prepare Data
            const revenueSheet = monthlyRevenue.map(item => ({
                "Month": item.month,
                "Revenue": item.revenue
            }));

            const serviceSheet = serviceDistribution.map(item => ({
                "Service": item.service,
                "Count": item.count,
                "Percentage": item.percentage + "%"
            }));

            // Use 'activities' from PHP if available globally, or scrape table
            // Since we passed PHP arrays for charts, let's use the table data for activity
            const activityRows = [];
            document.querySelectorAll('#activityLog tr').forEach(tr => {
                const tds = tr.querySelectorAll('td');
                if (tds.length === 3) {
                    activityRows.push({
                        "Date Time": tds[0].innerText,
                        "Activity": tds[1].innerText,
                        "User": tds[2].innerText
                    });
                }
            });

            // 2. Create Workbook
            const wb = XLSX.utils.book_new();

            const wsRevenue = XLSX.utils.json_to_sheet(revenueSheet);
            XLSX.utils.book_append_sheet(wb, wsRevenue, "Revenue");

            const wsServices = XLSX.utils.json_to_sheet(serviceSheet);
            XLSX.utils.book_append_sheet(wb, wsServices, "Services");

            const wsActivity = XLSX.utils.json_to_sheet(activityRows);
            XLSX.utils.book_append_sheet(wb, wsActivity, "Activity Log");

            // 3. Download
            XLSX.writeFile(wb, 'Borewell_Reports_' + new Date().toISOString().slice(0, 10) + '.xlsx');
        }
    </script>
    <footer
        style="text-align: center; padding: 20px; background: #fff; color: #666; margin-top: 40px; border-top: 1px solid #ddd;">
        <p>&copy; 23552011321_Arya Fauzan_23B_UASWEB1.</p>
    </footer>
</body>

</html>