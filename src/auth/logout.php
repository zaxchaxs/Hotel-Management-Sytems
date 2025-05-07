<?php
session_start();

// Clear all session variables
$_SESSION = array();

session_destroy();

header("Location: /hotel/src/index.php");
exit;
