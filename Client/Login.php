<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Client/Login.php
require_once '../Config/db_connect.php';
require_once '../Controller/functions.php';

// Jika sudah login, redirect ke dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: Dashboard.php");
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Validasi input
        if (empty($email) || empty($password)) {
            throw new Exception("Email dan password harus diisi!");
        }

        // Query untuk mencari user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['profile_photo'] = $user['profile_photo'];
            
            // Catat login history
            $stmt = $pdo->prepare("INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
            $stmt->execute([
                $user['user_id'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            ]);

            header("Location: Dashboard.php");
            exit;
        } else {
            throw new Exception("Email atau password salah!");
        }
    } catch(Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Alfins Kost</title>
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
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-logo img {
            max-width: 150px;
        }
        .form-control {
            padding: 10px 15px;
            border-radius: 5px;
        }
        .btn-primary {
            padding: 10px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <img src="../assets/images/logo.png" alt="Alfins Kost Logo">
            </div>
            <h2 class="text-center mb-4">Login</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
                
                <p class="text-center mb-0">
                    Belum punya akun? <a href="Register.php" class="text-decoration-none">Register di sini</a>
                </p>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>