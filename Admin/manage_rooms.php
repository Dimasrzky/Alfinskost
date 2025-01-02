<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../Config/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit;
}

// Delete room
if(isset($_POST['delete'])) {
    $room_id = $_POST['room_id'];
    try {
        // Get type_id first
        $stmt = $pdo->prepare("SELECT type_id FROM rooms WHERE room_id = ?");
        $stmt->execute([$room_id]);
        $type_id = $stmt->fetch()['type_id'];

        // Delete from rooms first (child table)
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
        $stmt->execute([$room_id]);

        // Then delete from room_types (parent table)
        $stmt = $pdo->prepare("DELETE FROM room_types WHERE type_id = ?");
        $stmt->execute([$type_id]);

        header("Location: manage_rooms.php?success=deleted");
        exit;
    } catch(PDOException $e) {
        $error = $e->getMessage();
    }
}

// Get all rooms
$query = "SELECT r.*, rt.type_name, rt.price_monthly, rt.facilities, rp.photo_url 
        FROM rooms r 
        JOIN room_types rt ON r.type_id = rt.type_id 
        LEFT JOIN room_photos rp ON r.room_id = rp.room_id AND rp.is_primary = 1";
$rooms = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Kelola Kamar - Admin</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
   <?php include 'Admin_header.php'; ?>

   <div class="container mt-4">
       <div class="d-flex justify-content-between align-items-center mb-4">
           <h2>Kelola Kamar</h2>
           <a href="Add_room.php" class="btn btn-primary">Tambah Kamar</a>
       </div>

       <div class="row">
           <?php foreach($rooms as $room): ?>
           <div class="col-md-4 mb-4">
               <div class="card h-100">
                   <img src="../<?php echo $room['photo_url'] ?? 'uploads/rooms/default.jpg'; ?>" 
                        class="card-img-top" alt="Kamar <?php echo $room['room_number']; ?>">
                   <div class="card-body">
                       <h5 class="card-title">Kamar <?php echo htmlspecialchars($room['room_number']); ?></h5>
                       <p>Harga: Rp <?php echo number_format($room['price_monthly']); ?>/bulan</p>
                       <p>Status: <?php echo $room['status']; ?></p>
                       <p>Fasilitas: <?php echo htmlspecialchars($room['facilities']); ?></p>
                   </div>
                   <div class="card-footer">
                       <a href="edit_room.php?id=<?php echo $room['room_id']; ?>" 
                          class="btn btn-warning">Edit</a>
                       <form action="" method="POST" class="d-inline" 
                             onsubmit="return confirm('Yakin ingin menghapus?')">
                           <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                           <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
                       </form>
                   </div>
               </div>
           </div>
           <?php endforeach; ?>
       </div>
   </div>
</body>
</html>