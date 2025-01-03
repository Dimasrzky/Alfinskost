<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../Config/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit;
}

if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $booking_id = $_GET['id'];
    
    try {
        if($action === 'confirm') {
            // Update booking status
            $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'confirmed' WHERE booking_id = ?");
            $stmt->execute([$booking_id]);
            
            // Update room status
            $stmt = $pdo->prepare("UPDATE rooms r 
                                 JOIN bookings b ON r.room_id = b.room_id 
                                 SET r.status = 'occupied' 
                                 WHERE b.booking_id = ?");
            $stmt->execute([$booking_id]);
            
        } elseif($action === 'cancel') {
            // Update booking status only
            $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = ?");
            $stmt->execute([$booking_id]);
        }
        
        header("Location: manage_bookings.php?success=1");
        exit;
        
    } catch(PDOException $e) {
        header("Location: manage_bookings.php?error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: manage_bookings.php");
    exit;
}
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