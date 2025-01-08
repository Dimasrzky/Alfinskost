<?php
require_once '../Config/db_connect.php';
require_once '../Controller/functions.php';

if (!isLoggedIn()) {
    header("Location: Login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = $_POST['nama'];
    $gender = $_POST['gender'];
    $phone_number = $_POST['nomorhp'];
    $review_text = $_POST['ulasan'];
    $rating = $_POST['rating'];

    try {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, full_name, gender, phone_number, review_text, rating) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $full_name, $gender, $phone_number, $review_text, $rating]);
        
        header("Location: Dashboard.php#ulasan");
        exit;
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
