<?php
// admin_login.php
session_start();

// Email dan password statis untuk admin
define('ADMIN_EMAIL', 'admin@alfinskost.com');
define('ADMIN_PASSWORD', 'admin123');

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validasi login dengan kredensial statis
    if ($email === "Alfinskost@gmail.com" && $password === "alfins123") {
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_name'] = 'Administrator';
        header("Location: Admin_Dashboard.php");
        exit;
    } else {
        $message = "Email atau password salah!";
        $messageType = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Alfins Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .login-box h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-logo img {
            max-width: 150px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <img src="../assets/images/logo.png" alt="Alfins Kost Logo">
            </div>
            <h2>Admin Login</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="Alfinskost@gmail.com" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>