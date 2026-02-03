<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - Borewell System</title>
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
                    <li><a href="services.php" class="active"><i class="fas fa-tools"></i> Services</a></li>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                        <li><a href="order.php"><i class="fas fa-shopping-cart"></i> Order</a></li>
                        <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                        <li><a href="../admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <li><a href="order.php"><i class="fas fa-shopping-cart"></i> Order</a></li>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2 class="section-title">Our Services</h2>

        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-digging"></i>
                </div>
                <h3>Borewell Drilling</h3>
                <p>Complete borewell drilling solutions using advanced hydraulic rigs capable of drilling up to 1000 ft
                    depth. We survey the land and recommend the best spot for drilling.</p>
                <ul class="service-features" style="list-style: none; text-align: left; padding: 20px 0;">
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> Groundwater Survey
                    </li>
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> 4.5" to 6.5" Drilling
                    </li>
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> Casing Pipe
                        Installation</li>
                </ul>
                <p class="price">Starting from Rp 5.000.000</p>
                <a href="order.php" class="btn btn-primary">Book Now</a>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-wrench"></i>
                </div>
                <h3>Maintenance & Cleaning</h3>
                <p>Regular maintenance ensures longevity. We offer high-pressure air compressor cleaning to remove silt
                    and mud, restoring your borewell's yield.</p>
                <ul class="service-features" style="list-style: none; text-align: left; padding: 20px 0;">
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> Flushing & Cleaning
                    </li>
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> Yield Testing</li>
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> Chemical Treatment
                    </li>
                </ul>
                <p class="price">Starting from Rp 1.500.000</p>
                <a href="order.php" class="btn btn-primary">Book Now</a>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <h3>Pump Repair & Installation</h3>
                <p>Expert installation and repair of submersible pumps, jet pumps, and motors. We ensure proper fitting
                    for maximum efficiency and water flow.</p>
                <ul class="service-features" style="list-style: none; text-align: left; padding: 20px 0;">
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> Pump Extraction &
                        Erection</li>
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> Motor Rewinding</li>
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> Panel Board Service
                    </li>
                </ul>
                <p class="price">Starting from Rp 2.000.000</p>
                <a href="order.php" class="btn btn-primary">Book Now</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Borewell System</h3>
                    <p>Professional water drilling and maintenance services since 2005.</p>
                </div>
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-phone"></i> +62 21 1234 5678</p>
                    <p><i class="fas fa-envelope"></i> aryafauzan123@gmail.com</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="services.php">Services</a></li>
                        <li><a href="order.php">Book Service</a></li>
                        <li><a href="login.php">Login</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 23552011321_Arya Fauzan_23B_UASWEB1.</p>
            </div>
        </div>
    </footer>

</body>

</html>