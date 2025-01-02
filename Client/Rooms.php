<!-- rooms.php -->
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
    <title>Daftar Kamar - Alfins Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../Style/Rooms.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include_once __DIR__ . '/Header.php'; ?>

    <div class="container py-5">
        <h2 class="mb-4">Daftar Kamar Tersedia</h2>

        <!-- Search and Filters -->
        <div class="search-filters">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="searchRoom" placeholder="Cari kamar (nomor, tipe, fasilitas)...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="priceSort">
                        <option value="">Urutkan Harga</option>
                        <option value="low">Termurah</option>
                        <option value="high">Termahal</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="availabilityFilter">
                        <option value="">Semua Status</option>
                        <option value="available">Tersedia</option>
                        <option value="occupied">Terisi</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Rooms Grid -->
        <div class="row g-4" id="roomsContainer">
            <?php
            try {
                $query = "SELECT r.*, rt.type_name, rt.price_monthly, rt.facilities 
                         FROM rooms r 
                         JOIN room_types rt ON r.type_id = rt.type_id 
                         ORDER BY rt.price_monthly ASC";
                $stmt = $pdo->query($query);
                
                while($room = $stmt->fetch()) {
                    $statusClass = $room['status'] === 'available' ? 'success' : 'danger';
                    $statusText = $room['status'] === 'available' ? 'Tersedia' : 'Terisi';
            ?>
            <div class="col-md-4 room-card" 
                 data-price="<?php echo $room['price_monthly']; ?>"
                 data-status="<?php echo $room['status']; ?>">
                <div class="card">
                    <img src="../uploads/rooms/<?php echo $room['room_photo'] ?? 'default.jpg'; ?>" 
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
                            <?php echo htmlspecialchars($room['facilities']); ?>
                        </p>
                        <?php if($room['status'] === 'available'): ?>
                        <button onclick="bookRoom(<?php echo $room['room_id']; ?>)" 
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
                const cardContent = room.textContent.toLowerCase();
                const status = room.dataset.status;
                const matches = cardContent.includes(searchText) && 
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

    function bookRoom(roomId) {
        // Implementasi fungsi booking
        window.location.href = `booking.php?room_id=${roomId}`;
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>