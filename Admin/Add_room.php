<?php
session_start();
require_once '../Config/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_Login.php");
    exit;
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Insert room type first
        $stmt = $pdo->prepare("INSERT INTO room_types (type_name, description, price_monthly, price_yearly, facilities) 
                             VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            'Standard', // default type
            'Standard Room',
            $_POST['price'],
            $_POST['price'] * 12, // yearly price
            $_POST['facilities']
        ]);
        $type_id = $pdo->lastInsertId();
 
        // Then insert room
        $stmt = $pdo->prepare("INSERT INTO rooms (room_number, type_id, floor, status, description) 
                             VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['room_number'],
            $type_id,
            $_POST['floor'],
            $_POST['status'], 
            'Kamar ' . $_POST['room_number']
        ]);
        $room_id = $pdo->lastInsertId();
 
        // Handle photo upload
        if(!empty($_FILES['room_photos']['name'][0])) {
            $upload_dir = '../uploads/rooms/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
 
            foreach($_FILES['room_photos']['tmp_name'] as $key => $tmp_name) {
                $filename = uniqid() . '.jpg';
                move_uploaded_file($tmp_name, $upload_dir . $filename);
                
                $stmt = $pdo->prepare("INSERT INTO room_photos (room_id, photo_url, is_primary) 
                                     VALUES (?, ?, ?)");
                $stmt->execute([
                    $room_id,
                    'uploads/rooms/' . $filename,
                    $key === 0 ? 1 : 0
                ]);
            }
        }
 
        header("Location: Admin_Dashboard.php?success=1");
        exit;
 
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
 }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kamar - Admin Alfins Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'Admin_header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Tambah Kamar Baru</h4>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Nomor Kamar</label>
                                <input type="text" class="form-control" name="room_number" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Lantai</label>
                                <input type="number" class="form-control" name="floor" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Harga per Bulan</label>
                                <input type="number" class="form-control" name="price" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Fasilitas</label>
                                <textarea class="form-control" name="facilities" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="available">Tersedia</option>
                                    <option value="occupied">Terisi</option>
                                    <option value="maintenance">Perbaikan</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Foto Kamar</label>
                                <input type="file" class="form-control" name="room_photos[]" multiple accept="image/*">
                            </div>

                            <button type="submit" class="btn btn-primary">Tambah Kamar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>