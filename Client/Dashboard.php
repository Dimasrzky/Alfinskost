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
    <link rel="icon" type="png" href="../Image/Logo Alfins Kost.png">
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
                    <a href="booking_history.php" class="btn btn-outline-primary">Riwayat Booking</a>
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
                            try {
                            $query = "SELECT r.*, rt.type_name, rt.price_monthly, rt.facilities, rp.photo_url 
                                FROM rooms r 
                                JOIN room_types rt ON r.type_id = rt.type_id 
                                LEFT JOIN room_photos rp ON r.room_id = rp.room_id AND rp.is_primary = 1";
                            $stmt = $pdo->query($query);
                            
                            while($room = $stmt->fetch()) {
                                $statusClass = $room['status'] === 'available' ? 'success' : 'danger';
                                $statusText = $room['status'] === 'available' ? 'Tersedia' : 'Terisi';
                            ?>
                            <div class="col-md-4 mb-4 room-card" 
                                data-price="<?php echo $room['price_monthly']; ?>"
                                data-status="<?php echo $room['status']; ?>">
                            <div class="card h-100">
                            <img src="../<?php echo $room['photo_url'] ?? 'uploads/rooms/default.jpg'; ?>" 
                                class="card-img-top" alt="Foto Kamar <?php echo $room['room_number']; ?>">
                                <div class="card-body">
                                    <h5 class="card-title">Kamar <?php echo htmlspecialchars($room['room_number']); ?></h5>
                                    <p class="room-price mb-2">
                                        Rp <?php echo number_format($room['price_monthly'], 0, ',', '.'); ?>/bulan
                                    </p>
                                    <p class="text-<?php echo $statusClass; ?> mb-2">
                                        <strong>Status:</strong> <?php echo $statusText; ?>
                                    </p>
                                    <p class="facilities mb-3">
                                        <strong>Fasilitas:</strong> <?php echo htmlspecialchars($room['facilities']); ?>
                                    </p>
                                    <?php if($room['status'] === 'available'): ?>
                                        <button onclick="location.href='booking.php?room_id=<?php echo $room['room_id']; ?>'" 
                                            class="btn btn-primary w-100">
                                            Pesan Kamar
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100" disabled>
                                            Tidak Tersedia
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                            }
                        } catch(PDOException $e) {
                        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
                        }
                        ?>
                    </div>
                </div>
            </section>

            <section id="ulasan">
                <div class="review-section container">
                    <div class="row">
                        <!-- Review Form Column -->
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
                                    <label>Rating:</label>
                                    <div class="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                            <label for="star<?php echo $i; ?>"><?php echo $i; ?>⭐</label>
                                        <?php endfor; ?>
                                    </div>
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

                        <!-- Reviews Display Column -->
                        <div class="col-md-6">
                            <h2>Ulasan Penghuni</h2>
                            <div class="reviews-container">
                                <?php
                                $stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
                                while($review = $stmt->fetch()) {
                                    $genderIcon = $review['gender'] === 'L' ? '👨' : '👩';
                                    $ratingStars = str_repeat('⭐', $review['rating']);
                                ?>
                                <div class="review-card mb-3">
                                    <div class="review-header">
                                        <span class="user-icon"><?php echo $genderIcon; ?></span>
                                        <span class="user-name"><?php echo htmlspecialchars($review['full_name']); ?></span>
                                        <div class="rating-display"><?php echo $ratingStars; ?></div>
                                    </div>
                                    <div class="review-content">
                                        <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                    </div>
                                    <div class="review-footer">
                                        <small class="text-muted">
                                            <?php echo date('d M Y H:i', strtotime($review['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <?php
                                }
                                ?>
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