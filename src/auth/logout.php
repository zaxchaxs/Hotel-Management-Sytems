<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the home page or login page
header("Location: /hotel/src/index.php");
exit;
