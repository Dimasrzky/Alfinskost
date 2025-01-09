<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit;
}

require_once '../Config/db_connect.php';

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    try {
        // Check for active bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND booking_status = 'confirmed'");
        $stmt->execute([$user_id]);
        $hasActiveBookings = $stmt->fetchColumn() > 0;

        if ($hasActiveBookings) {
            $_SESSION['error'] = "Cannot delete user with active bookings.";
        } else {
            // Begin transaction for safe deletion
            $pdo->beginTransaction();
            
            // Delete all related records in proper order
            // Delete reviews
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Delete payments
            $stmt = $pdo->prepare("DELETE FROM payments WHERE booking_id IN (SELECT booking_id FROM bookings WHERE user_id = ?)");
            $stmt->execute([$user_id]);
            
            // Delete bookings
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Delete login history
            $stmt = $pdo->prepare("DELETE FROM login_history WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Finally delete the user
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $pdo->commit();
            $_SESSION['success'] = "User and all related records successfully deleted.";
        }
    } catch(PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header("Location: manage_users.php");
    exit;
}

// Handle status toggle
if (isset($_POST['toggle_status'])) {
    $user_id = $_POST['user_id'];
    $new_status = $_POST['new_status'];
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        $stmt->execute([$new_status, $user_id]);
        $_SESSION['success'] = "User status successfully updated.";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header("Location: manage_users.php");
    exit;
}

// Handle user search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
$searchParams = [];

if (!empty($search)) {
    $searchCondition = "AND (u.nik LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR u.phone_number LIKE ?)";
    $searchTerm = "%$search%";
    $searchParams = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }
        .search-box {
            max-width: 300px;
        }
        .modal-table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        .modal-table th {
            width: 35%;
            font-weight: bold;
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        .stats-card {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'Admin_header.php'; ?>

    <div class="container my-4">
        <div class="row mb-4">
            <div class="col">
                <h2>User Management</h2>
            </div>
            <div class="col-auto">
                <form class="search-box" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search users..." 
                               value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>NIK</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Current Room</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $query = "SELECT u.*, 
                                        (SELECT r.room_number 
                                        FROM bookings b 
                                        JOIN rooms r ON b.room_id = r.room_id 
                                        WHERE b.user_id = u.user_id 
                                        AND b.booking_status = 'confirmed' 
                                        LIMIT 1) as current_room,
                                        (SELECT COUNT(*) FROM bookings WHERE user_id = u.user_id) as total_bookings,
                                        (SELECT COUNT(*) FROM reviews WHERE user_id = u.user_id) as total_reviews
                                        FROM users u 
                                        WHERE u.role = 'user' 
                                        $searchCondition
                                        ORDER BY u.created_at DESC";
                                
                                $stmt = $pdo->prepare($query);
                                if (!empty($searchParams)) {
                                    $stmt->execute($searchParams);
                                } else {
                                    $stmt->execute();
                                }

                                while ($user = $stmt->fetch()) {
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['nik']) ?></td>
                                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['phone_number']) ?></td>
                                        <td><?= $user['current_room'] ? "Room " . htmlspecialchars($user['current_room']) : "-" ?></td>
                                        <td>
                                            <span class="badge bg-<?= $user['status'] == 'active' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($user['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" class="btn btn-info btn-sm text-white" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#userModal<?= $user['user_id'] ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                    <input type="hidden" name="new_status" 
                                                           value="<?= $user['status'] == 'active' ? 'inactive' : 'active' ?>">
                                                    <button type="submit" name="toggle_status" class="btn btn-warning btn-sm">
                                                        <i class="bi bi-toggle-<?= $user['status'] == 'active' ? 'on' : 'off' ?>"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this user? This will also delete all related bookings, payments, reviews, and login history.');">
                                                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-danger btn-sm">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- User Detail Modal -->
                                    <div class="modal fade" id="userModal<?= $user['user_id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">User Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- User Stats -->
                                                    <div class="row mb-4">
                                                        <div class="col-md-4">
                                                            <div class="stats-card text-center">
                                                                <h6>Total Bookings</h6>
                                                                <h3><?= $user['total_bookings'] ?></h3>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="stats-card text-center">
                                                                <h6>Reviews Given</h6>
                                                                <h3><?= $user['total_reviews'] ?></h3>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="stats-card text-center">
                                                                <h6>Account Status</h6>
                                                                <h3><span class="badge bg-<?= $user['status'] == 'active' ? 'success' : 'danger' ?>">
                                                                    <?= ucfirst($user['status']) ?>
                                                                </span></h3>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- User Details Table -->
                                                    <table class="modal-table w-100">
                                                        <tr>
                                                            <th>NIK</th>
                                                            <td><?= htmlspecialchars($user['nik']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Full Name</th>
                                                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Email</th>
                                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Phone Number</th>
                                                            <td><?= htmlspecialchars($user['phone_number']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Gender</th>
                                                            <td><?= $user['gender'] == 'L' ? 'Male' : 'Female' ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Address</th>
                                                            <td><?= htmlspecialchars($user['address']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Emergency Contact</th>
                                                            <td><?= htmlspecialchars($user['emergency_contact'] ?? '-') ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Occupation</th>
                                                            <td><?= htmlspecialchars($user['occupation'] ?? '-') ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Current Room</th>
                                                            <td><?= $user['current_room'] ? "Room " . htmlspecialchars($user['current_room']) : "Not assigned" ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Registered Date</th>
                                                            <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Last Updated</th>
                                                            <td><?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } catch(PDOException $e) {
                                echo "<tr><td colspan='7' class='text-center text-danger'>Error: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>