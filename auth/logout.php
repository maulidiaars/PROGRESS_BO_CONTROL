<?php
session_start();
// Clear all session data
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login
header("Location: ../views/login.php");
exit();
?>