<?php
require_once '../Config/db_connect.php';
require_once '../Controller/functions.php';

if (!isLoggedIn()) {
    header("Location: Login.php");
    exit;
}

// Di file payment.php atau proses pembayaran
$stmt = $pdo->prepare("UPDATE bookings SET payment_status = 'pending' WHERE booking_id = ?");
$stmt->execute([$booking_id]);

if (!$booking) {
    header("Location: booking_history.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
            $upload_dir = '../uploads/payments/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = uniqid() . '_' . $_FILES['payment_proof']['name'];
            move_uploaded_file($_FILES['payment_proof']['tmp_name'], $upload_dir . $filename);

            // Insert into payments table
            $stmt = $pdo->prepare("INSERT INTO payments (booking_id, amount, payment_date, payment_method, 
                                 payment_proof, payment_status, notes) 
                                 VALUES (?, ?, NOW(), ?, ?, 'pending', ?)");
            $stmt->execute([
                $booking_id,
                $booking['total_price'],
                $_POST['payment_method'],
                $filename,
                $_POST['notes'] ?? null
            ]);

            // Update booking payment status
            $stmt = $pdo->prepare("UPDATE bookings SET payment_status = 'pending' WHERE booking_id = ?");
            $stmt->execute([$booking_id]);

            header("Location: booking_history.php?success=payment_uploaded");
            exit;
        }
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
    <title>Pembayaran - Alfins Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../Style/dashboard.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="mb-3">
            <a href="booking_history.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Detail Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Nomor Kamar:</strong> <?php echo $booking['room_number']; ?></p>
                        <p><strong>Total Pembayaran:</strong> Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></p>
                        <p><strong>Durasi:</strong> <?php echo $booking['duration']; ?> bulan</p>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label>Metode Pembayaran</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="cash">Cash</option>
                                    <option value="ewallet">E-Wallet</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Bukti Pembayaran</label>
                                <input type="file" name="payment_proof" class="form-control" required 
                                       accept="image/*">
                                <small class="text-muted">Upload bukti transfer/pembayaran</small>
                            </div>

                            <div class="mb-3">
                                <label>Catatan (opsional)</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Upload Pembayaran</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Informasi Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <h6>Transfer Bank:</h6>
                        <p>Bank ABC<br>
                        No. Rekening: 1234-5678-9012<br>
                        a.n. Alfins Kost</p>
                        
                        <div class="alert alert-info">
                            <small>
                                * Setelah melakukan pembayaran, upload bukti pembayaran untuk konfirmasi<br>
                                * Admin akan memverifikasi pembayaran Anda<br>
                                * Status pembayaran akan diupdate setelah verifikasi
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>