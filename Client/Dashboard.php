<?php
require_once '../Config/db_connect.php';
require_once '../Controller/functions.php';

if (!isLoggedIn()) {
    header("Location: Login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Alfins Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../Style/dashboard.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="header-logo">
                <img src="../Image/Logo Alfins Kost.png" alt="Logo" class="site-logo">
                <h1>Alfins Kost</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="#beranda">Beranda</a></li>
                    <li><a href="#lokasi">Lokasi</a></li>
                    <li><a href="#tentang">Tentang</a></li>
                    <li><a href="#kamar">Kamar</a></li>
                    <li><a href="#ulasan">Ulasan</a></li>
                    <li><a href="Logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="main-content">
            <!-- Profile Section -->
            <section class="profile-section">
                <div class="profile-header">
                    <img src="<?php 
                        if (!empty($_SESSION['profile_photo'])) {
                            echo '../' . $_SESSION['profile_photo'];
                        } else {
                            echo '../Uploads/default-profile.png';
                        }
                    ?>" alt="Profile" class="profile-image">
                    <div class="profile-info">
                        <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
                        <p>Status: Active Member</p>
                    </div>
                </div>
                <div class="profile-actions">
                    <a href="#" class="btn btn-primary">Edit Profile</a>
                    <a href="#" class="btn btn-outline-primary">Riwayat Booking</a>
                </div>
            </section>

            <section id="beranda">
                <div class="welcome-section">
                    <h2>Selamat Datang di Alfins Kost</h2>
                    <p>Temukan kenyamanan dan ketenangan di Alfins Kost, pilihan terbaik untuk hunian Anda.</p>
                </div>
            </section>

            <section id="lokasi">
                <div class="location-info">
                    <h2>Lokasi Kami</h2>
                    <p>Kami berlokasi di Isekai City, dekat dengan berbagai fasilitas umum.</p>
                </div>
            </section>

            <section id="tentang">
                <div class="container mt-3">
                    <h2>Tentang Kost</h2>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fasilitas</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Kamar</td>
                                <td>Nyaman dan bersih</td>
                            </tr>
                            <tr>
                                <td>Wifi</td>
                                <td>Kecepatan tinggi</td>
                            </tr>
                            <tr>
                                <td>Dapur</td>
                                <td>Bersama</td>
                            </tr>
                            <tr>
                                <td>Parkir</td>
                                <td>Luas & aman</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="image-gallery">
                    <div class="image-container">
                        <img src="../Image/kamar.jpg" alt="Kamar">
                    </div>
                    <div class="image-container">
                        <img src="../Image/gambar dapur minimalis.jpg" alt="Dapur">
                    </div>
                    <div class="image-container">
                        <img src="../Image/parkir.jpeg" alt="Parkir">
                    </div>
                    <div class="image-container">
                        <img src="../Image/white-wifi-router-modern-free-photo.jpg" alt="WiFi">
                    </div>
                </div>
            </section>
            
            <section id="kamar" class="my-5">
                <div class="container">
                    <h2>Cari kamar</h2>
                    <!-- Search and Filter -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Search -->
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="searchRoom" placeholder="Cari kamar...">
                                </div>
                                <!-- Filters -->
                                <div class="col-md-3">
                                    <select class="form-select" id="priceSort">
                                        <option value="">Urutkan Harga</option>
                                        <option value="low">Termurah</option>
                                        <option value="high">Termahal</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="availabilityFilter">
                                        <option value="">Status Kamar</option>
                                        <option value="available">Tersedia</option>
                                        <option value="occupied">Terisi</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rooms Grid -->
                    <div class="row" id="roomsContainer">
                        <?php
                        $query = "SELECT r.*, rt.price_monthly, rt.facilities 
                                FROM rooms r 
                                JOIN room_types rt ON r.type_id = rt.type_id";
                        $stmt = $pdo->query($query);
                        while($room = $stmt->fetch()) {
                        ?>
                        <div class="col-md-4 mb-4 room-card" 
                                data-price="<?php echo $room['price_monthly']; ?>"
                                data-status="<?php echo $room['status']; ?>">
                            <div class="card h-100">
                                <img src="../uploads/rooms/<?php echo $room['room_photo'] ?? 'default.jpg'; ?>" 
                                        class="card-img-top" alt="Room Image">
                                <div class="card-body">
                                    <h5 class="card-title">Kamar <?php echo $room['room_number']; ?></h5>
                                    <p class="card-text">
                                        <strong>Harga:</strong> Rp <?php echo number_format($room['price_monthly']); ?>/bulan<br>
                                        <strong>Status:</strong> <?php echo $room['status'] == 'available' ? 
                                            '<span class="text-success">Tersedia</span>' : 
                                            '<span class="text-danger">Terisi</span>'; ?>
                                    </p>
                                    <p class="card-text small"><?php echo $room['facilities']; ?></p>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary w-100" <?php echo $room['status'] != 'available' ? 'disabled' : ''; ?>>
                                        Pesan Kamar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </section>

            <section id="ulasan">
                <div class="review-section">
                    <div class="form-container my-5">
                        <div class="row">
                            <div class="col-md-6">
                                <h2>Beri Ulasan</h2>
                                <form action="process_review.php" method="post">
                                    <div class="form-group mb-3">
                                        <label for="nama">Nama:</label>
                                        <input type="text" id="nama" name="nama" class="form-control" 
                                               value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>" readonly>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Gender:</label>
                                        <div class="gender-options">
                                            <div class="gender-option">
                                                <input type="radio" id="laki-laki" name="gender" value="L" required>
                                                <label for="laki-laki">Laki-laki</label>
                                            </div>
                                            <div class="gender-option">
                                                <input type="radio" id="perempuan" name="gender" value="P" required>
                                                <label for="perempuan">Perempuan</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="nomorhp">Nomor HP:</label>
                                        <input type="tel" id="nomorhp" name="nomorhp" class="form-control" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="ulasan">Ulasan:</label>
                                        <textarea id="ulasan" name="ulasan" class="form-control" rows="4" required></textarea>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Kirim Ulasan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Alfins Kost. Hak cipta dilindungi.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchRoom');
            const priceSort = document.getElementById('priceSort');
            const availabilityFilter = document.getElementById('availabilityFilter');
            const roomsContainer = document.getElementById('roomsContainer');

            function filterRooms() {
                const searchText = searchInput.value.toLowerCase();
                const sortValue = priceSort.value;
                const statusFilter = availabilityFilter.value;
                
                let rooms = Array.from(document.querySelectorAll('.room-card'));
                
                rooms.forEach(room => {
                    const title = room.querySelector('.card-title').textContent.toLowerCase();
                    const status = room.dataset.status;
                    const matches = title.includes(searchText) && 
                                    (!statusFilter || status === statusFilter);
                    room.style.display = matches ? '' : 'none';
                });

                if(sortValue) {
                    rooms.sort((a, b) => {
                        const priceA = parseFloat(a.dataset.price);
                        const priceB = parseFloat(b.dataset.price);
                        return sortValue === 'low' ? priceA - priceB : priceB - priceA;
                    });
                    rooms.forEach(room => roomsContainer.appendChild(room));
                }
            }

            searchInput.addEventListener('input', filterRooms);
            priceSort.addEventListener('change', filterRooms);
            availabilityFilter.addEventListener('change', filterRooms);
        });
   </script>
</body>
</html>