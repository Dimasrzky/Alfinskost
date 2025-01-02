<?php
require_once '../Config/db_connect.php';
require_once '../Controller/functions.php';

if (isLoggedIn()) {
    header("Location: Dashboard.php");
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $messageType = 'error';
        $message = 'Mohon isi email dan password';
    } else {
        $result = loginUser($email, $password);
        $messageType = $result['status'];
        $message = $result['message'];
        
        if ($result['status'] == 'success') {
            header("Location: dashboard.php");
            exit;
        }
    }
}

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    
    // Catat login history
    $stmt = $pdo->prepare("INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
    $stmt->execute([
        $user['user_id'],
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
    
    header("Location: Dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Kost Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : 'danger'; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <div class="mb-3">
                                <label>Email:</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Password:</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                            
                            <p class="text-center mt-3">Belum punya akun? <a href="register.php">Register di sini</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>