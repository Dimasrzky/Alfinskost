<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_Login.php");
    exit;
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
    <?php include 'admin_header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Tambah Kamar Baru</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
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