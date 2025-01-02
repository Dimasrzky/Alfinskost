<?php
require_once '../Config/db_connect.php';
session_start();

// Cek jika user bukan admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit;
}

// Mengambil statistik untuk dashboard
try {
    // Total kamar
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rooms");
    $totalRooms = $stmt->fetch()['total'];

    // Kamar tersedia
    $stmt = $pdo->query("SELECT COUNT(*) as available FROM rooms WHERE status = 'available'");
    $availableRooms = $stmt->fetch()['available'];

    // Total penghuni
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
    $totalUsers = $stmt->fetch()['total'];

    // Pemesanan baru (pending)
    $stmt = $pdo->query("SELECT COUNT(*) as pending FROM bookings WHERE booking_status = 'pending'");
    $pendingBookings = $stmt->fetch()['pending'];

    // Pemesanan terbaru
    $stmt = $pdo->query("SELECT b.*, u.full_name, r.room_number 
                        FROM bookings b 
                        JOIN users u ON b.user_id = u.user_id 
                        JOIN rooms r ON b.room_id = r.room_id 
                        ORDER BY b.booking_date DESC LIMIT 5");
    $recentBookings = $stmt->fetchAll();

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
        .quick-actions {
            margin: 20px 0;
        }
        .action-button {
            padding: 10px 20px;
            margin: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'admin_header.php'; ?>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col">
                <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h2>
                <p class="text-muted">Dashboard Admin Alfins Kost</p>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="quick-actions">
            <button class="btn btn-primary action-button" onclick="location.href='add_room.php'">
                <i class="bi bi-plus-circle"></i> Tambah Kamar
            </button>
            <button class="btn btn-success action-button" onclick="location.href='bookings.php'">
                <i class="bi bi-calendar-check"></i> Kelola Pemesanan
            </button>
            <button class="btn btn-info action-button text-white" onclick="location.href='users.php'">
                <i class="bi bi-people"></i> Kelola Penghuni
            </button>
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
        <div class="row mt-4">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Pemesanan Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama Pemesan</th>
                                        <th>Nomor Kamar</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentBookings as $booking): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['room_number']); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch($booking['booking_status']) {
                                                case 'pending':
                                                    $statusClass = 'warning';
                                                    break;
                                                case 'confirmed':
                                                    $statusClass = 'success';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <?php echo ucfirst($booking['booking_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="view_booking.php?id=<?php echo $booking['booking_id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>