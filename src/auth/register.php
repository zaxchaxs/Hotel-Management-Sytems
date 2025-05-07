<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    
    // Validate form data
    if (empty($username)) {
        $errors[] = "Username is required";
    } else if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors[] = "Username must be 3-20 characters and can only contain letters, numbers, and underscores";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } else if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    // Check if username or email already exists
    if (empty($errors)) {
        $check_query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['username'] === $username) {
                $errors[] = "Username already exists";
            }
            if ($user['email'] === $email) {
                $errors[] = "Email already exists";
            }
        }
    }
    
    // If no errors, create user account
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user into database
        $insert_query = "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'customer')";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Failed to create account: " . $conn->error;
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-blue-500 text-white">
            <h2 class="text-xl font-bold">Create an Account</h2>
        </div>
        <div class="p-6">
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <p class="font-bold">Registration Successful!</p>
                    <p>Your account has been created. You can now <a href="login.php" class="text-blue-500 hover:underline">login</a>.</p>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="password" name="password" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded">
                            Register
                        </button>
                        <a href="login.php" class="text-blue-500 hover:underline">Already have an account?</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>