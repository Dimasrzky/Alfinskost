<?php
require_once '../Config/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit;
}

if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $booking_id = $_GET['id'];
    
    try {
        if($action === 'confirm') {
            $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'confirmed' WHERE booking_id = ?");
            $success_msg = "Pemesanan berhasil dikonfirmasi";
        } elseif($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = ?");
            // Update room status back to available
            $stmt2 = $pdo->prepare("UPDATE rooms r 
                                  JOIN bookings b ON r.room_id = b.room_id 
                                  SET r.status = 'available' 
                                  WHERE b.booking_id = ?");
            $stmt2->execute([$booking_id]);
            $success_msg = "Pemesanan telah ditolak";
        }
        $stmt->execute([$booking_id]);
        
        header("Location: manage_bookings.php?success=".urlencode($success_msg));
        exit;
    } catch(PDOException $e) {
        header("Location: manage_bookings.php?error=".urlencode($e->getMessage()));
        exit;
    }
}

header("Location: manage_bookings.php");
exit;
?>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        Pemesanan berhasil diupdate!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        Error: <?php echo htmlspecialchars($_GET['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>