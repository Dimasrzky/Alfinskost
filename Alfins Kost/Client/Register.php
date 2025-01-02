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
    // Validate input
    $errors = [];
    
    if (strlen($_POST['nik']) != 16) {
        $errors[] = "NIK harus 16 digit";
    }
    
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (strlen($_POST['password']) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }
    
    if (empty($errors)) {
        $result = registerUser($_POST, $_FILES['profile_photo'] ?? null);
        $messageType = $result['status'];
        $message = $result['message'];
    } else {
        $messageType = 'error';
        $message = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Kost Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Register</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : 'danger'; ?>">
                                <?php echo $message; ?>
                                <?php if ($messageType == 'success'): ?>
                                    <br>
                                    <a href="login.php">Silakan login</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label>NIK:</label>
                                <input type="text" name="nik" class="form-control" required maxlength="16">
                            </div>
                            
                            <div class="mb-3">
                                <label>Nama Lengkap:</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Email:</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Password:</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>No. Telepon:</label>
                                <input type="text" name="phone_number" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Jenis Kelamin:</label>
                                <select name="gender" class="form-control" required>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label>Alamat:</label>
                                <textarea name="address" class="form-control" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label>Kontak Darurat:</label>
                                <input type="text" name="emergency_contact" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label>Pekerjaan:</label>
                                <input type="text" name="occupation" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label>Foto Profil:</label>
                                <input type="file" name="profile_photo" class="form-control" accept="image/*">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                            
                            <p class="text-center mt-3">Sudah punya akun? <a href="login.php">Login di sini</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>