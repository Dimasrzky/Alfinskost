<?php
session_start();

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
    <title>Daftar Penghuni - Alfins Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .table {
            margin-bottom: 0;
            vertical-align: middle;
        }
        .table > thead {
            background-color: #f8f9fa;
        }
        .table > tbody > tr > td {
            vertical-align: middle;
            padding: 0.75rem;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
        }
        .modal-table td, .modal-table th {
            padding: 0.5rem;
            border-bottom: 1px solid #dee2e6;
        }
        .modal-table th {
            width: 30%;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'Admin_header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Daftar Penghuni</h2>
            <div class="search-box">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari penghuni...">
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>NIK</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>No. Telepon</th>
                                <th>Kamar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $query = "SELECT u.*, 
                                         (SELECT r.room_number 
                                          FROM bookings b 
                                          JOIN rooms r ON b.room_id = r.room_id 
                                          WHERE b.user_id = u.user_id 
                                          AND b.booking_status = 'confirmed' 
                                          AND b.payment_status = 'paid'
                                          LIMIT 1) as current_room
                                         FROM users u 
                                         WHERE u.role = 'user'
                                         ORDER BY u.created_at DESC";
                                $stmt = $pdo->query($query);
                                
                                if ($stmt->rowCount() > 0) {
                                    while ($user = $stmt->fetch()) {
                                        ?>
                                        <tr>
                                            <td><?= $user['nik'] ?></td>
                                            <td><?= $user['full_name'] ?></td>
                                            <td><?= $user['email'] ?></td>
                                            <td><?= $user['phone_number'] ?></td>
                                            <td><?= $user['current_room'] ? "Kamar " . $user['current_room'] : "-" ?></td>
                                            <td>
                                                <span class="badge bg-<?= $user['status'] == 'active' ? 'success' : 'danger' ?>">
                                                    <?= ucfirst($user['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-info text-white" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#detailModal<?= $user['user_id'] ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                        <input type="hidden" name="new_status" 
                                                               value="<?= $user['status'] == 'active' ? 'inactive' : 'active' ?>">
                                                        <button type="submit" name="toggle_status" 
                                                                class="btn btn-<?= $user['status'] == 'active' ? 'warning' : 'success' ?>">
                                                            <i class="bi bi-<?= $user['status'] == 'active' ? 'pause-fill' : 'play-fill' ?>"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="d-inline" 
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus penghuni ini?');">
                                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Detail Modal -->
                                        <div class="modal fade" id="detailModal<?= $user['user_id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detail Penghuni</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table class="modal-table w-100">
                                                            <tr>
                                                                <th>NIK</th>
                                                                <td><?= $user['nik'] ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Nama Lengkap</th>
                                                                <td><?= $user['full_name'] ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Email</th>
                                                                <td><?= $user['email'] ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>No. Telepon</th>
                                                                <td><?= $user['phone_number'] ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Gender</th>
                                                                <td><?= $user['gender'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Alamat</th>
                                                                <td><?= $user['address'] ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Kontak Darurat</th>
                                                                <td><?= $user['emergency_contact'] ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Pekerjaan</th>
                                                                <td><?= $user['occupation'] ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Terdaftar</th>
                                                                <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="7" class="text-center">Belum ada data penghuni</td></tr>';
                                }
                            } catch(PDOException $e) {
                                echo "<tr><td colspan='7' class='text-center text-danger'>Error: " . $e->getMessage() . "</td></tr>";
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