<?php
session_start();

// Cek jika user bukan admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit;
}

// Tidak perlu koneksi database karena belum ada tabel
$totalRooms = 0;
$availableRooms = 0;
$totalUsers = 2;
$pendingBookings = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Alfins Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'Admin_header.php'; ?>

    <div class="container mt-4">
        <h2>Selamat Datang, Administrator!</h2>
        <p class="text-muted">Dashboard Admin Alfins Kost</p>

        <!-- Quick Actions -->
        <div class="mb-4">
            <a href="add_room.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Kamar
            </a>
            <a href="manage_bookings.php" class="btn btn-success">
                <i class="bi bi-calendar-check"></i> Kelola Pemesanan
            </a>
            <a href="manage_users.php" class="btn btn-info text-white">
                <i class="bi bi-people"></i> Kelola Penghuni
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card bg-primary text-white">
                    <i class="bi bi-house-door stat-icon"></i>
                    <div class="stat-value"><?php echo $totalRooms; ?></div>
                    <div class="stat-label">Total Kamar</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-success text-white">
                    <i class="bi bi-check-circle stat-icon"></i>
                    <div class="stat-value"><?php echo $availableRooms; ?></div>
                    <div class="stat-label">Kamar Tersedia</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-info text-white">
                    <i class="bi bi-people stat-icon"></i>
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Total Penghuni</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-warning text-dark">
                    <i class="bi bi-clock-history stat-icon"></i>
                    <div class="stat-value"><?php echo $pendingBookings; ?></div>
                    <div class="stat-label">Pemesanan Pending</div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings Table -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Pemesanan Terbaru</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Belum ada data pemesanan.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>