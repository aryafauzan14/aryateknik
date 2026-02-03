<?php
session_start();
require_once '../config/db.php';

$message = '';
$alertClass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, name, username, password, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Password correct, start session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $message = "Invalid password!";
            $alertClass = "alert-error";
        }
    } else {
        $message = "Username not found!";
        $alertClass = "alert-error";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Borewell System</title>
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
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="services.php"><i class="fas fa-tools"></i> Services</a></li>
                    <!-- <li><a href="order.php"><i class="fas fa-shopping-cart"></i> Order</a></li> -->
                    <li><a href="login.php" class="active"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="login-container">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
                <p>Access your account</p>
            </div>

            <?php if ($message != ''): ?>
                <div id="loginMessage" class="alert <?php echo $alertClass; ?>" style="display: block;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" action="login.php" method="POST">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" class="form-control"
                        placeholder="Enter your username" required>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="demo-credentials">
                <h4>Default Credentials:</h4>
                <ul>
                    <li><strong>Admin:</strong> admin / admin123</li>
                    <li><strong>User:</strong> user / user123</li>

                </ul>
            </div>

            <div class="form-footer" style="text-align: center; margin-top: 20px;">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
    <footer
        style="text-align: center; padding: 20px; background: #fff; color: #666; margin-top: 40px; border-top: 1px solid #ddd;">
        <p>&copy; 23552011321_Arya Fauzan_23B_UASWEB1.</p>
    </footer>
</body>

</html>