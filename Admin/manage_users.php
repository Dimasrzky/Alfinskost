<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit;
}

require_once '../Config/db_connect.php';

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    try {
        // First check if user has any active bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND booking_status = 'confirmed'");
        $stmt->execute([$user_id]);
        $hasActiveBookings = $stmt->fetchColumn() > 0;

        if ($hasActiveBookings) {
            $_SESSION['error'] = "Tidak dapat menghapus penghuni dengan pemesanan aktif.";
        } else {
            // Delete user's bookings first
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Then delete user's login history
            $stmt = $pdo->prepare("DELETE FROM login_history WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Finally delete the user
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $_SESSION['success'] = "Penghuni berhasil dihapus.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header("Location: manage_users.php");
    exit;
}

// Handle user status toggle
if (isset($_POST['toggle_status'])) {
    $user_id = $_POST['user_id'];
    $new_status = $_POST['new_status'];
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        $stmt->execute([$new_status, $user_id]);
        $_SESSION['success'] = "Status penghuni berhasil diperbarui.";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header("Location: manage_users.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penghuni - Alfins Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="../Style/Admin_Dashboard.css" rel="stylesheet">
    <link rel="icon" type="png" href="../Image/Logo Alfins Kost.png">
</head>
<body class="bg-light">
    <?php include 'Admin_header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola Penghuni</h2>
            <div class="search-box">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari penghuni...">
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>NIK</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>No. Telepon</th>
                                <th>Gender</th>
                                <th>Kontak Darurat</th>
                                <th>Pekerjaan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $query = "SELECT * FROM users ORDER BY created_at DESC";
                                $stmt = $pdo->query($query);
                                
                                while ($user = $stmt->fetch()) {
                                    echo "<tr>
                                            <td>{$user['user_id']}</td>
                                            <td>{$user['nik']}</td>
                                            <td>{$user['full_name']}</td>
                                            <td>{$user['email']}</td>
                                            <td>{$user['phone_number']}</td>
                                            <td>" . ($user['gender'] == 'L' ? 'Laki-laki' : 'Perempuan') . "</td>
                                            <td>{$user['emergency_contact']}</td>
                                            <td>{$user['occupation']}</td>
                                            <td>";
                                    if ($user['status'] == 'active') {
                                        echo "<span class='badge bg-success'>Active</span>";
                                    } else {
                                        echo "<span class='badge bg-danger'>Inactive</span>";
                                    }
                                    echo "</td>
                                            <td>
                                                <div class='btn-group'>
                                                    <button type='button' class='btn btn-info btn-sm me-2' 
                                                            data-bs-toggle='modal' 
                                                            data-bs-target='#detailModal{$user['user_id']}'>
                                                        <i class='bi bi-eye'></i>
                                                    </button>
                                                    <form method='POST' class='me-2'>
                                                        <input type='hidden' name='user_id' value='{$user['user_id']}'>
                                                        <input type='hidden' name='new_status' value='" . ($user['status'] == 'active' ? 'inactive' : 'active') . "'>
                                                        <button type='submit' name='toggle_status' class='btn btn-" . ($user['status'] == 'active' ? 'warning' : 'success') . " btn-sm'>
                                                            <i class='bi bi-" . ($user['status'] == 'active' ? 'pause-fill' : 'play-fill') . "'></i>
                                                        </button>
                                                    </form>
                                                    <form method='POST' onsubmit='return confirm(\"Apakah Anda yakin ingin menghapus penghuni ini?\");'>
                                                        <input type='hidden' name='user_id' value='{$user['user_id']}'>
                                                        <button type='submit' name='delete_user' class='btn btn-danger btn-sm'>
                                                            <i class='bi bi-trash'></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>";

                                    // Detail Modal for each user
                                    echo "<div class='modal fade' id='detailModal{$user['user_id']}' tabindex='-1'>
                                            <div class='modal-dialog modal-lg'>
                                                <div class='modal-content'>
                                                    <div class='modal-header'>
                                                        <h5 class='modal-title'>Detail Penghuni</h5>
                                                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                                    </div>
                                                    <div class='modal-body'>
                                                        <div class='row'>
                                                            <div class='col-md-4'>
                                                                <img src='" . ($user['profile_photo'] ? "../uploads/profiles/{$user['profile_photo']}" : "../Image/default-profile.png") . "' 
                                                                     class='img-fluid rounded' alt='Profile Photo'>
                                                            </div>
                                                            <div class='col-md-8'>
                                                                <table class='table'>
                                                                    <tr><th>NIK</th><td>{$user['nik']}</td></tr>
                                                                    <tr><th>Nama Lengkap</th><td>{$user['full_name']}</td></tr>
                                                                    <tr><th>Email</th><td>{$user['email']}</td></tr>
                                                                    <tr><th>No. Telepon</th><td>{$user['phone_number']}</td></tr>
                                                                    <tr><th>Gender</th><td>" . ($user['gender'] == 'L' ? 'Laki-laki' : 'Perempuan') . "</td></tr>
                                                                    <tr><th>Alamat</th><td>{$user['address']}</td></tr>
                                                                    <tr><th>Kontak Darurat</th><td>{$user['emergency_contact']}</td></tr>
                                                                    <tr><th>Pekerjaan</th><td>{$user['occupation']}</td></tr>
                                                                    <tr><th>Terdaftar</th><td>" . date('d/m/Y H:i', strtotime($user['created_at'])) . "</td></tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>";
                                }
                            } catch(PDOException $e) {
                                echo "<tr><td colspan='10' class='text-center text-danger'>Error: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    </script>
</body>
</html>