<?php
session_start();

// Security checks
if (!isset($_SESSION['admin_id']) || !$_SESSION['admin_id']) {
    header("Location: Admin_login.php");
    exit;
}

require_once '../Config/db_connect.php';

// Utility function for safe redirects
function redirectWithMessage($type, $message) {
    $_SESSION[$type] = $message;
    header("Location: manage_users.php");
    exit;
}

// Validate user ID
function validateUserId($user_id) {
    return filter_var($user_id, FILTER_VALIDATE_INT) !== false && $user_id > 0;
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
    
    if (!validateUserId($user_id)) {
        redirectWithMessage('error', 'Invalid user ID provided.');
    }

    try {
        // Check for active bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND booking_status = 'confirmed'");
        $stmt->execute([$user_id]);
        $hasActiveBookings = $stmt->fetchColumn() > 0;

        if ($hasActiveBookings) {
            redirectWithMessage('error', 'Cannot delete user with active bookings.');
        }

        // Begin transaction for safe deletion
        $pdo->beginTransaction();
        
        // Delete related records in proper order using prepared statements
        $tables = ['reviews', 'payments', 'bookings', 'login_history', 'users'];
        $queries = [
            'reviews' => "DELETE FROM reviews WHERE user_id = ?",
            'payments' => "DELETE FROM payments WHERE booking_id IN (SELECT booking_id FROM bookings WHERE user_id = ?)",
            'bookings' => "DELETE FROM bookings WHERE user_id = ?",
            'login_history' => "DELETE FROM login_history WHERE user_id = ?",
            'users' => "DELETE FROM users WHERE user_id = ?"
        ];

        foreach ($queries as $table => $query) {
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user_id]);
        }
        
        $pdo->commit();
        redirectWithMessage('success', 'User and all related records successfully deleted.');
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting user: " . $e->getMessage());
        redirectWithMessage('error', 'An error occurred while deleting the user. Please try again.');
    }
}

// Handle status toggle
if (isset($_POST['toggle_status'])) {
    $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
    $new_status = filter_var($_POST['new_status'], FILTER_SANITIZE_STRING);
    
    if (!validateUserId($user_id)) {
        redirectWithMessage('error', 'Invalid user ID provided.');
    }

    if (!in_array($new_status, ['active', 'inactive'])) {
        redirectWithMessage('error', 'Invalid status provided.');
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        $stmt->execute([$new_status, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            redirectWithMessage('success', 'User status successfully updated.');
        } else {
            redirectWithMessage('error', 'User not found or no changes made.');
        }
    } catch(PDOException $e) {
        error_log("Error updating user status: " . $e->getMessage());
        redirectWithMessage('error', 'An error occurred while updating user status.');
    }
}

// Handle user search with improved security
$search = filter_var($_GET['search'] ?? '', FILTER_SANITIZE_STRING);
$searchCondition = '';
$searchParams = [];

if (!empty($search)) {
    $searchCondition = "AND (u.nik LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR u.phone_number LIKE ?)";
    $searchTerm = "%{$search}%";
    $searchParams = array_fill(0, 4, $searchTerm);
}

// Base query with security improvements
$baseQuery = "SELECT u.*, 
    (SELECT r.room_number 
     FROM bookings b 
     JOIN rooms r ON b.room_id = r.room_id 
     WHERE b.user_id = u.user_id 
     AND b.booking_status = 'confirmed' 
     LIMIT 1) as current_room,
    (SELECT COUNT(*) FROM bookings WHERE user_id = u.user_id) as total_bookings,
    (SELECT COUNT(*) FROM reviews WHERE user_id = u.user_id) as total_reviews
    FROM users u 
    WHERE 1=1 " . $searchCondition . "
    ORDER BY u.created_at DESC";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-Content-Type-Security-Policy" content="default-src 'self'">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;">
    <title>Manage Users - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .table-responsive { overflow-x: auto; }
        .action-buttons { 
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }
        .search-box { max-width: 300px; }
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

    <div class="mb-3">
            <a href="Admin_Dashboard.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

    <div class="container my-4">
        <!-- Alert Messages -->
        <?php foreach (['success', 'error'] as $type): ?>
            <?php if (isset($_SESSION[$type])): ?>
                <div class="alert alert-<?= $type === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION[$type]); unset($_SESSION[$type]); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col">
                <h2>User Management</h2>
            </div>
            <div class="col-auto">
                <form class="search-box" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search users..." 
                               value="<?= htmlspecialchars($search) ?>"
                               maxlength="100">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
                        <tbody>
                            <?php
                            try {
                                $stmt = $pdo->prepare($baseQuery);
                                $stmt->execute($searchParams);

                                while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['nik']) ?></td>
                                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['phone_number']) ?></td>
                                        <td><?= $user['current_room'] ? "Room " . htmlspecialchars($user['current_room']) : "-" ?></td>
                                        <td>
                                            <span class="badge bg-<?= $user['status'] == 'active' ? 'success' : 'danger' ?>">
                                                <?= ucfirst(htmlspecialchars($user['status'])) ?>
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
                                                <form method="POST" class="d-inline" name="delete_form">
                                                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-danger btn-sm">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>

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
                                                                        <h3>
                                                                            <span class="badge bg-<?= $user['status'] == 'active' ? 'success' : 'danger' ?>">
                                                                                <?= ucfirst(htmlspecialchars($user['status'])) ?>
                                                                            </span>
                                                                        </h3>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- User Details Table -->
                                                            <table class="modal-table w-100">
                                                                <?php
                                                                $details = [
                                                                    'NIK' => 'nik',
                                                                    'Full Name' => 'full_name',
                                                                    'Email' => 'email',
                                                                    'Phone Number' => 'phone_number',
                                                                    'Gender' => ['field' => 'gender', 'transform' => function($v) { return $v == 'L' ? 'Male' : 'Female'; }],
                                                                    'Address' => 'address',
                                                                    'Emergency Contact' => 'emergency_contact',
                                                                    'Occupation' => 'occupation',
                                                                    'Current Room' => ['field' => 'current_room', 'transform' => function($v) { return $v ? "Room $v" : "Not assigned"; }],
                                                                    'Registered Date' => ['field' => 'created_at', 'transform' => function($v) { return date('d/m/Y H:i', strtotime($v)); }],
                                                                    'Last Updated' => ['field' => 'updated_at', 'transform' => function($v) { return date('d/m/Y H:i', strtotime($v)); }]
                                                                ];

                                                                foreach ($details as $label => $field) {
                                                                    $value = is_array($field) 
                                                                        ? $field['transform']($user[$field['field']] ?? '-')
                                                                        : ($user[$field] ?? '-');
                                                                    ?>
                                                                    <tr>
                                                                        <th><?= htmlspecialchars($label) ?></th>
                                                                        <td><?= htmlspecialchars($value) ?></td>
                                                                    </tr>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } catch(PDOException $e) {
                                error_log("Database error: " . $e->getMessage());
                                echo "<tr><td colspan='7' class='text-center text-danger'>An error occurred while fetching users. Please try again later.</td></tr>";
                            }
                            ?>
                        </tbody>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirm delete action
        document.querySelectorAll('form[name="delete_form"]').forEach(form => {
            form.onsubmit = function(e) {
                return confirm('Are you sure you want to delete this user? This action cannot be undone.');
            }
        });
    </script>
</body>
</html>