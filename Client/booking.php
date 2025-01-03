<?php
// booking.php
session_start();
require_once '../Config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

$room_id = $_GET['room_id'];

// Get room details
$stmt = $pdo->prepare("SELECT r.*, rt.price_monthly, rt.facilities 
                       FROM rooms r 
                       JOIN room_types rt ON r.type_id = rt.type_id 
                       WHERE r.room_id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $check_in_date = $_POST['check_in_date'];
        $duration = $_POST['duration']; // dalam bulan
        $total_price = $room['price_monthly'] * $duration;
        
        // Hitung check_out_date
        $check_out_date = date('Y-m-d', strtotime($check_in_date . ' + ' . $duration . ' months'));
        
        // Insert booking dengan struktur tabel yang baru
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, booking_date, check_in_date, check_out_date, 
                              duration, total_price, booking_status, payment_status, notes) 
                              VALUES (?, ?, NOW(), ?, ?, ?, ?, 'pending', 'unpaid', ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $room_id,
            $check_in_date,
            $check_out_date,
            $duration,
            $total_price,
            $_POST['notes'] ?? null
        ]);

        header("Location: Dashboard.php?success=booking");
        exit;
    } catch(PDOException $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Kamar - Alfins Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'Header.php'; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-6">
                <!-- Room Details -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h4>Detail Kamar</h4>
                        <p><strong>Nomor Kamar:</strong> <?php echo $room['room_number']; ?></p>
                        <p><strong>Harga per Bulan:</strong> Rp <?php echo number_format($room['price_monthly']); ?></p>
                        <p><strong>Fasilitas:</strong> <?php echo $room['facilities']; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Booking Form -->
                <div class="card">
                    <div class="card-body">
                        <h4>Form Pemesanan</h4>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Check-in</label>
                                <input type="date" name="check_in_date" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Durasi Sewa (Bulan)</label>
                                <select name="duration" class="form-select" required>
                                    <option value="1">1 Bulan</option>
                                    <option value="3">3 Bulan</option>
                                    <option value="6">6 Bulan</option>
                                    <option value="12">12 Bulan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan Tambahan</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika ada..."></textarea>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary w-100">Pesan Sekarang</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>