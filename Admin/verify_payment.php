<?php
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}
require_once '../Config/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
   header("Location: Admin_login.php");
   exit;
}

if(isset($_GET['id'])) {
   $booking_id = $_GET['id'];
   
   try {
       $pdo->beginTransaction();
       
       // Update status pembayaran di tabel bookings
       $stmt = $pdo->prepare("UPDATE bookings 
                         SET payment_status = 'paid' 
                         WHERE booking_id = ?");
       $stmt->execute([$booking_id]);
       
       // Update status kamar
       $stmt = $pdo->prepare("UPDATE rooms r 
                        JOIN bookings b ON r.room_id = b.room_id 
                        SET r.status = 'occupied' 
                        WHERE b.booking_id = ?");
       $stmt->execute([$booking_id]);
       
       $pdo->commit();
       
       header("Location: manage_bookings.php?success=payment_verified");
       exit;
   } catch(PDOException $e) {
       $pdo->rollBack();
       header("Location: manage_bookings.php?error=" . urlencode($e->getMessage()));
       exit;
   }
}

header("Location: manage_bookings.php");
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Verifikasi Pembayaran - Admin</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
   <link href="../Style/verify_payment.css" rel="stylesheet">
   <link rel="icon" type="png" href="../Image/Logo Alfins Kost.png">
</head>
<body class="bg-light">
   <?php include 'Admin_header.php'; ?>

   <div class="container mt-4">
       <div class="row justify-content-center">
           <div class="col-md-8">
               <?php if(isset($error)): ?>
                   <div class="alert alert-danger">
                       <?php echo $error; ?>
                   </div>
               <?php endif; ?>
               
               <?php if(isset($_GET['success'])): ?>
                   <div class="alert alert-success">
                       Pembayaran berhasil diverifikasi!
                   </div>
               <?php endif; ?>

               <div class="card">
                   <div class="card-header">
                       <h4 class="mb-0">Verifikasi Pembayaran</h4>
                   </div>
                   <div class="card-body">
                       <p>Apakah Anda yakin ingin memverifikasi pembayaran ini?</p>
                       <div class="mt-3">
                           <a href="verify_payment.php?id=<?php echo $booking_id; ?>" class="btn btn-success">Ya, Verifikasi</a>
                           <a href="manage_bookings.php" class="btn btn-secondary">Kembali</a>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>