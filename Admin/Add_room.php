<?php
session_start();
require_once '../Config/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_Login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $room_number = $_POST['room_number'];
        $floor = $_POST['floor'];
        $status = $_POST['status'];
        $facilities = $_POST['facilities'];
        
        // Insert room data
        $stmt = $pdo->prepare("INSERT INTO rooms (room_number, floor, status, facilities) VALUES (?, ?, ?, ?)");
        
        if($stmt->execute([$room_number, $floor, $status, $facilities])) {
            $room_id = $pdo->lastInsertId();
            
            // Handle photo upload
            if(isset($_FILES['room_photos'])) {
                $upload_dir = '../uploads/rooms/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                foreach($_FILES['room_photos']['tmp_name'] as $key => $tmp_name) {
                    $file_name = uniqid() . '_' . $_FILES['room_photos']['name'][$key];
                    $upload_path = $upload_dir . $file_name;
                    
                    if(move_uploaded_file($tmp_name, $upload_path)) {
                        // Save photo reference to database if needed
                        $stmt = $pdo->prepare("UPDATE rooms SET room_photo = ? WHERE room_id = ?");
                        $stmt->execute([$file_name, $room_id]);
                    }
                }
            }
            echo "<script>alert('Kamar berhasil ditambahkan!'); window.location.href='Admin_Dashboard.php';</script>";
        }
    } catch(PDOException $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
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