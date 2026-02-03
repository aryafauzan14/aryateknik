<?php
session_start();
require_once '../config/db.php';

$message = '';
$alertClass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['regName']);
    $email = $conn->real_escape_string($_POST['regEmail']);
    $phone = $conn->real_escape_string($_POST['regPhone']);
    $username = $conn->real_escape_string($_POST['regUsername']);
    $password = $_POST['regPassword'];
    $confirmPassword = $_POST['regConfirmPassword'];

    if ($password !== $confirmPassword) {
        $message = "Passwords do not match!";
        $alertClass = "alert-error";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters!";
        $alertClass = "alert-error";
    } else {
        // Check if username or email exists
        $check = $conn->query("SELECT id FROM users WHERE username='$username' OR email='$email'");
        if ($check->num_rows > 0) {
            $message = "Username or Email already exists!";
            $alertClass = "alert-error";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer';

            $sql = "INSERT INTO users (name, email, phone, username, password, role) 
                    VALUES ('$name', '$email', '$phone', '$username', '$hashed_password', '$role')";

            if ($conn->query($sql) === TRUE) {
                $alertClass = "alert-success";
                $message = "Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $alertClass = "alert-error";
                $message = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Borewell System</title>
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
                    <li><a href="order.php"><i class="fas fa-shopping-cart"></i> Order</a></li>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-user-plus"></i> Create Account</h2>
                <p>Join our borewell service community</p>
            </div>

            <?php if ($message != ''): ?>
                <div id="registerMessage" class="alert <?php echo $alertClass; ?>" style="display: block;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form id="registerForm" action="register.php" method="POST">
                <div class="form-group">
                    <label for="regName"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" id="regName" name="regName" class="form-control"
                        placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="regEmail"><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" id="regEmail" name="regEmail" class="form-control"
                        placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="regPhone"><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="tel" id="regPhone" name="regPhone" class="form-control"
                        placeholder="Enter your phone number" required>
                </div>

                <div class="form-group">
                    <label for="regUsername"><i class="fas fa-user-circle"></i> Username</label>
                    <input type="text" id="regUsername" name="regUsername" class="form-control"
                        placeholder="Choose a username" required>
                </div>

                <div class="form-group">
                    <label for="regPassword"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="regPassword" name="regPassword" class="form-control"
                        placeholder="Enter password (min. 6 characters)" minlength="6" required>
                </div>

                <div class="form-group">
                    <label for="regConfirmPassword"><i class="fas fa-lock"></i> Confirm Password</label>
                    <input type="password" id="regConfirmPassword" name="regConfirmPassword" class="form-control"
                        placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>

            <div class="form-footer" style="text-align: center; margin-top: 20px;">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
    <footer
        style="text-align: center; padding: 20px; background: #fff; color: #666; margin-top: 40px; border-top: 1px solid #ddd;">
        <p>&copy; 23552011321_Arya Fauzan_23B_UASWEB1.</p>
    </footer>
</body>

</html>