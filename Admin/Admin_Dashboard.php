<?php
session_start();

// Cek jika user bukan admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit;
}

require_once '../Config/db_connect.php';

// Mengambil statistik
try {
    // Total pengunjung yang login
    $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as total_visitors FROM login_history");
    $totalVisitors = $stmt->fetch()['total_visitors'];

    // Pengunjung hari ini
    $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as today_visitors FROM login_history 
                         WHERE DATE(login_time) = CURDATE()");
    $todayVisitors = $stmt->fetch()['today_visitors'];

    // Menghitung statistik lainnya seperti sebelumnya
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rooms");
    $totalRooms = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as available FROM rooms WHERE status = 'available'");
    $availableRooms = $stmt->fetch()['available'];
    
    $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as total FROM bookings WHERE booking_status = 'confirmed'");
    $totalUsers = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as pending FROM bookings WHERE booking_status = 'pending'");
    $pendingBookings = $stmt->fetch()['pending'];
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Alfins Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="../Style/Admin_Dashboard.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'Admin_header.php'; ?>

    <div class="container mt-4">
        <h2>Selamat Datang, Administrator!</h2>

        <div class="mb-4">
            <a href="Add_room.php" class="btn btn-primary">
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
            <div class="col-md-6">
                <div class="stat-card bg-purple text-white">
                    <i class="bi bi-eye stat-icon"></i>
                    <div class="stat-value"><?php echo $totalVisitors; ?></div>
                    <div class="stat-label">Total Pengunjung</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card bg-orange text-white">
                    <i class="bi bi-person-check stat-icon"></i>
                    <div class="stat-value"><?php echo $todayVisitors; ?></div>
                    <div class="stat-label">Pengunjung Hari Ini</div>
                </div>
            </div>
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