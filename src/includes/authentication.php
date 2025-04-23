<?php require_once 'config.php'; ?>
<?php
/**
 * Authentication functions for Hotel Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is logged in
 * 
 * @return boolean True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Ensure that a user is logged in, redirect to login if not
 * 
 * @param string $redirect URL to redirect to after login
 * @return void
 */
function ensureLoggedIn($redirect = '') {
    if (!isLoggedIn()) {
        // Store the current URL for redirection after login
        $_SESSION['redirect_after_login'] = $redirect ?: $_SERVER['REQUEST_URI'];
        header('Location: ' . ROOT_NAME . 'auth/login.php');
        exit;
    }
}

/**
 * Check if the current user is an admin
 * 
 * @return boolean True if user is an admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff');
}

/**
 * Ensure that a user is an admin, redirect to home if not
 * 
 * @return void
 */
function ensureAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        setFlashMessage('error', 'You do not have permission to access that page.');
        header('Location: /index.php');
        exit;
    }
}

/**
 * Authenticate a user with username/email and password
 * 
 * @param string $username_or_email Username or email
 * @param string $password Password
 * @return array|false User data if authenticated, false otherwise
 */
function authenticateUser($username_or_email, $password) {
    global $conn;
    
    // Prepare query to check if username or email exists
    $query = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    
    return false;
}

/**
 * Log in a user and set session variables
 * 
 * @param array $user User data
 * @return void
 */
function loginUser($user) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    
    // Update last login time
    global $conn;
    $user_id = $user['user_id'];
    $conn->query("UPDATE users SET last_login = NOW() WHERE user_id = $user_id");
}

/**
 * Log out a user by destroying the session
 * 
 * @return void
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
}

/**
 * Set a flash message to be displayed once
 * 
 * @param string $type Type of message (success, error, info, warning)
 * @param string $message Message content
 * @return void
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear the flash message
 * 
 * @return array|null Flash message data or null if none exists
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message if one exists
 * 
 * @return void
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $type = $flash['type'];
        $message = $flash['message'];
        
        $bg_color = 'bg-blue-100 border-blue-400 text-blue-700'; // Default info style
        
        if ($type === 'success') {
            $bg_color = 'bg-green-100 border-green-400 text-green-700';
        } elseif ($type === 'error') {
            $bg_color = 'bg-red-100 border-red-400 text-red-700';
        } elseif ($type === 'warning') {
            $bg_color = 'bg-yellow-100 border-yellow-400 text-yellow-700';
        }
        
        echo "<div class=\"$bg_color px-4 py-3 rounded relative border mb-4\" role=\"alert\">
                <span class=\"block sm:inline\">$message</span>
              </div>";
    }
}
?>