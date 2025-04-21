<?php
/**
 * Configuration settings for Hotel Management System
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Uncomment when using HTTPS

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_management_system');

// Site configuration
define('SITE_NAME', 'Hotel Management System');
define('SITE_URL', 'http://localhost'); // Change to your domain

// Timezone
date_default_timezone_set('UTC');

// Email configuration
define('EMAIL_FROM', 'noreply@yourdomain.com');
define('EMAIL_NAME', 'Hotel Management System');

// Payment configuration
define('PAYMENT_CURRENCY', 'USD');
define('TAX_RATE', 0.1); // 10%

// Upload configuration
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// API configuration
define('API_URL', 'http://localhost:3000');
define('API_KEY', 'your_api_key_here');

// Security configuration
define('BCRYPT_COST', 12); // Cost parameter for password_hash

// Pagination
define('ITEMS_PER_PAGE', 10);

// Include paths
define('INCLUDE_PATH', dirname(__FILE__) . '/');
define('TEMPLATE_PATH', dirname(__DIR__) . '/templates/');

// root page
define('ROOT_NAME', '/hotel/src/');

// Navbar path
$navbarLinks = [
    'home' => [
        'label' => 'Home',
        'path' => '/hotel/src/index.php',
        'filename' => 'index.php'
    ],
    'rooms' => [
        'label' => 'Rooms',
        'path' => '/hotel/src/rooms.php',
        'filename' => 'rooms.php'
    ],
    'about' => [
        'label' => 'About',
        'path' => '/hotel/src/about.php',
        'filename' => 'about.php'
    ],
    'contact' => [
        'label' => 'Contact',
        'path' => '/hotel/src/contact.php',
        'filename' => 'contact.php'
    ],
]
?>