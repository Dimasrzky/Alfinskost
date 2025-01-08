<?php
// admin_logout.php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: Admin_login.php");
exit;
?>