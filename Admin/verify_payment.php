<?php
require_once '../Config/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit;
}

if(isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Update payment status
        $stmt = $pdo->prepare("UPDATE payments SET 
                             payment_status = 'paid',
                             confirmed_by = ?,
                             confirmed_at = NOW()
                             WHERE booking_id = ?");
        $stmt->execute([$_SESSION['admin_id'], $booking_id]);
        
        // Update booking payment status
        $stmt = $pdo->prepare("UPDATE bookings SET payment_status = 'paid' WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        
        // Update room status to occupied
        $stmt = $pdo->prepare("UPDATE rooms r 
                             JOIN bookings b ON r.room_id = b.room_id 
                             SET r.status = 'occupied' 
                             WHERE b.booking_id = ?");
        $stmt->execute([$booking_id]);
        
        $pdo->commit();
        
        header("Location: manage_bookings.php?success=payment_verified");
        exit;
        
    } catch(Exception $e) {
        $pdo->rollBack();
        header("Location: manage_bookings.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}

header("Location: manage_bookings.php");
exit;
?>