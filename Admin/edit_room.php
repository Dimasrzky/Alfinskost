<?php
require_once '../Config/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit;
}

$room_id = $_GET['id'];
$room = $pdo->query("SELECT r.*, rt.* FROM rooms r 
                     JOIN room_types rt ON r.type_id = rt.type_id 
                     WHERE r.room_id = $room_id")->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE rooms SET room_number = ?, floor = ?, status = ? WHERE room_id = ?");
        $stmt->execute([
            $_POST['room_number'],
            $_POST['floor'],
            $_POST['status'],
            $room_id
        ]);

        $stmt = $pdo->prepare("UPDATE room_types SET price_monthly = ?, facilities = ? WHERE type_id = ?");
        $stmt->execute([
            $_POST['price'],
            $_POST['facilities'], 
            $room['type_id']
        ]);

        header("Location: manage_rooms.php?success=updated");
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
    <title>Edit Kamar - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'Admin_header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Kamar</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Nomor Kamar</label>
                                <input type="text" name="room_number" class="form-control" 
                                       value="<?php echo $room['room_number']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Lantai</label>
                                <input type="number" name="floor" class="form-control" 
                                       value="<?php echo $room['floor']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label>Harga per Bulan</label>
                                <input type="number" name="price" class="form-control" 
                                       value="<?php echo $room['price_monthly']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="Tersedia" <?php echo $room['status'] == 'available' ? 'selected' : ''; ?>>
                                        Tersedia
                                    </option>
                                    <option value="Terisi" <?php echo $room['status'] == 'occupied' ? 'selected' : ''; ?>>
                                        Terisi
                                    </option>
                                    <option value="Perbaikan" <?php echo $room['status'] == 'maintenance' ? 'selected' : ''; ?>>
                                        Perbaikan
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Fasilitas</label>
                                <textarea name="facilities" class="form-control" rows="3"><?php echo $room['facilities']; ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="manage_rooms.php" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>