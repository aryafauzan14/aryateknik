<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borewell Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Navigation -->
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-water"></i>
                <h1>Borewell<span>System</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="services.php"><i class="fas fa-tools"></i> Services</a></li>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                        <li><a href="order.php"><i class="fas fa-shopping-cart"></i> Order</a></li>
                        <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                        <li><a href="../admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <!-- <li><a href="order.php"><i class="fas fa-shopping-cart"></i> Order</a></li> -->
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>Professional Water Drilling Solutions</h2>
                <p>Providing reliable borewell services with 20+ years of experience</p>
                <a href="order.php" class="btn btn-primary">Book Service Now</a>

            </div>
        </div>
    </section>

    <!-- Services Preview -->
    <section class="services-preview">
        <div class="container">
            <h2 class="section-title">Our Services</h2>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-digging"></i>
                    </div>
                    <h3>Borewell Drilling</h3>
                    <p>Professional drilling services for residential and commercial needs</p>
                    <p class="price">Starting from Rp 5.000.000</p>
                    <a href="order.php" class="btn btn-primary">Book Now</a>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-wrench"></i>
                    </div>
                    <h3>Maintenance</h3>
                    <p>Regular maintenance and cleaning services</p>
                    <p class="price">Starting from Rp 1.500.000</p>
                    <a href="order.php" class="btn btn-primary">Book Now</a>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3>Repair Services</h3>
                    <p>24/7 emergency repair services</p>
                    <p class="price">Starting from Rp 2.000.000</p>
                    <a href="order.php" class="btn btn-primary">Book Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>500+</h3>
                    <p>Projects Completed</p>
                </div>
                <div class="stat-item">
                    <h3>15+</h3>
                    <p>Years Experience</p>
                </div>
                <div class="stat-item">
                    <h3>98%</h3>
                    <p>Customer Satisfaction</p>
                </div>
                <div class="stat-item">
                    <h3>24/7</h3>
                    <p>Support Available</p>
                </div>
            </div>
        </div>
    </section>

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
            <p>&copy; 23552011321_Arya Fauzan_23B_UASWEB1.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>

</html>