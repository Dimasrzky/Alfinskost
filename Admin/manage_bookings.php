<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../Config/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit;
}

// Get all bookings with user and room details
$query = "SELECT b.*, u.full_name, r.room_number, b.payment_status 
          FROM bookings b
          JOIN users u ON b.user_id = u.user_id
          JOIN rooms r ON b.room_id = r.room_id
          ORDER BY b.booking_date DESC";
$bookings = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pemesanan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="../Style/manage_booking.css" rel="stylesheet">
    <link rel="icon" type="png" href="../Image/Logo Alfins Kost.png">
</head>
<body class="bg-light">
    <?php include 'Admin_header.php'; ?>

    <div class="container mt-4">
        <div class="mb-3">
            <a href="Admin_Dashboard.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Daftar Pemesanan</h2>
            <select class="form-select w-auto" id="statusFilter">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID Booking</th>
                                <th>Tanggal Booking</th>
                                <th>Nama Pemesan</th>
                                <th>Nomor Kamar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($bookings)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data pemesanan</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo $booking['booking_id']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                        <td>Kamar <?php echo htmlspecialchars($booking['room_number']); ?></td>
                                        <td>
                                            <?php 
                                            $statusClass = '';
                                            $statusText = '';
                                            
                                            if($booking['payment_status'] == 'paid') {
                                                $statusClass = 'success';
                                                $statusText = 'Lunas';
                                            } else if($booking['payment_status'] == 'pending') {
                                                $statusClass = 'info';
                                                $statusText = 'Menunggu Verifikasi';
                                            } else {
                                                $statusClass = 'warning';
                                                $statusText = 'Belum Bayar';
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php if($booking['booking_status'] == 'pending'): ?>
                                                <div class="btn-group">
                                                    <a href="process_booking.php?action=confirm&id=<?php echo $booking['booking_id']; ?>" 
                                                    class="btn btn-success btn-sm">Konfirmasi</a>
                                                    <a href="process_booking.php?action=reject&id=<?php echo $booking['booking_id']; ?>" 
                                                    class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menolak pesanan ini?')">Tolak</a>
                                                </div>
                                            <?php 
                                                if($booking['booking_status'] == 'confirmed') {
                                                if($booking['payment_status'] == 'pending') {
                                                    echo '<a href="verify_payment.php?id='.$booking['booking_id'].'" 
                                                    class="btn btn-success btn-sm">Verifikasi Pembayaran</a>';
                                                } elseif($booking['payment_status'] == 'paid') {
                                                    echo '<span class="badge bg-success">Pembayaran Terverifikasi</span>';
                                                }
                                            }?>
                                            <?php endif; ?>
                                            <a href="booking_detail.php?id=<?php echo $booking['booking_id']; ?>" 
                                            class="btn btn-info btn-sm text-white">Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if(isset($_GET['success']) && $_GET['success'] == 'payment_verified'): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    Pembayaran berhasil diverifikasi! Penghuni baru telah ditambahkan.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmBooking(bookingId) {
        if(confirm('Konfirmasi pemesanan ini?')) {
            window.location.href = `process_booking.php?action=confirm&id=${bookingId}`;
        }
    }

    function cancelBooking(bookingId) {
        if(confirm('Tolak pemesanan ini?')) {
            window.location.href = `process_booking.php?action=cancel&id=${bookingId}`;
        }
    }

    function viewDetails(bookingId) {
        window.location.href = `booking_details.php?id=${bookingId}`;
    }

    // Filter by status
    document.getElementById('statusFilter').addEventListener('change', function() {
        const status = this.value;
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const statusCell = row.querySelector('td:nth-child(5)');
            if(!statusCell) return;
            
            if(!status || statusCell.textContent.toLowerCase().includes(status)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>