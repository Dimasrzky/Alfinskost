<?php
require_once '../Config/db_connect.php';
require_once '../Controller/functions.php';

if (!isLoggedIn()) {
    header("Location: Login.php");
    exit;
}

// Get user's booking history
$stmt = $pdo->prepare("SELECT b.*, r.room_number, rt.price_monthly 
                       FROM bookings b
                       JOIN rooms r ON b.room_id = r.room_id
                       JOIN room_types rt ON r.type_id = rt.type_id
                       WHERE b.user_id = ?
                       ORDER BY b.booking_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Booking - Alfins Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="../Style/booking_history.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="mb-3">
            <a href="Dashboard.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
        <h2>Riwayat Booking</h2>
        
        <?php if (empty($bookings)): ?>
            <div class="alert alert-info">Belum ada riwayat booking.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal Booking</th>
                            <th>Nomor Kamar</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Durasi</th>
                            <th>Total Harga</th>
                            <th>Status Booking</th>
                            <th>Status Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></td>
                                <td>Kamar <?php echo htmlspecialchars($booking['room_number']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($booking['check_in_date'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($booking['check_out_date'])); ?></td>
                                <td><?php echo $booking['duration']; ?> bulan</td>
                                <td>Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></td>
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
                                    <span class="badge bg-<?php echo $booking['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($booking['payment_status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>