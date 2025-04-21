<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/authentication.php';

// Ensure user is logged in and is an admin
ensureLoggedIn();
ensureAdmin();

$errors = [];
$success_message = '';

// Process user action requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete user
    if (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        
        // Check if user has any bookings
        $booking_check_query = "SELECT COUNT(*) AS booking_count FROM bookings WHERE user_id = $user_id";
        $booking_check_result = $conn->query($booking_check_query);
        $booking_count = $booking_check_result->fetch_assoc()['booking_count'];
        
        if ($booking_count > 0) {
            $errors[] = "Cannot delete user with existing bookings. Please delete or reassign their bookings first.";
        } else {
            $delete_query = "DELETE FROM users WHERE user_id = $user_id";
            if ($conn->query($delete_query)) {
                $success_message = "User deleted successfully.";
            } else {
                $errors[] = "Failed to delete user: " . $conn->error;
            }
        }
    }
    
    // Update user role
    if (isset($_POST['update_role'])) {
        $user_id = intval($_POST['user_id']);
        $new_role = $_POST['role'];
        
        if (!in_array($new_role, ['admin', 'staff', 'customer'])) {
            $errors[] = "Invalid role selected.";
        } else {
            $update_query = "UPDATE users SET role = '$new_role' WHERE user_id = $user_id";
            if ($conn->query($update_query)) {
                $success_message = "User role updated successfully.";
            } else {
                $errors[] = "Failed to update user role: " . $conn->error;
            }
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $search_condition = "WHERE username LIKE '%$search%' OR email LIKE '%$search%' OR full_name LIKE '%$search%'";
}

// Get total users count for pagination
$count_query = "SELECT COUNT(*) as total FROM users $search_condition";
$count_result = $conn->query($count_query);
$total_users = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $per_page);

// Get users
$users_query = "SELECT * FROM users $search_condition ORDER BY user_id DESC LIMIT $offset, $per_page";
$users_result = $conn->query($users_query);
$users = [];

if ($users_result) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Include admin header
include '../includes/admin_header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">User Management</h1>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Back to Dashboard
        </a>
    </div>
    
    <!-- Display messages -->
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <p><?= htmlspecialchars($success_message) ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Search form -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="p-4">
            <form method="GET" class="flex">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Search users..." 
                       class="flex-grow px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-r-md">
                    Search
                </button>
            </form>
        </div>
    </div>
    
    <!-- Users table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No users found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['user_id']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['full_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php
                                    switch ($user['role']) {
                                        case 'admin':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        case 'staff':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        default:
                                            echo 'bg-green-100 text-green-800';
                                    }
                                    ?>">
                                    <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= date('M j, Y', strtotime($user['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex space-x-2">
                                    <!-- Update Role Form -->
                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                        <select name="role" class="text-sm border border-gray-300 rounded py-1 px-2 mr-1">
                                            <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                            <option value="staff" <?= $user['role'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <button type="submit" name="update_role" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded text-xs">
                                            Update
                                        </button>
                                    </form>
                                    
                                    <!-- Delete User Form -->
                                    <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                        <button type="submit" name="delete_user" class="bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded text-xs">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="flex justify-center mt-6">
            <nav class="inline-flex rounded-md shadow">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium rounded-l-md hover:bg-gray-50">
                        Previous
                    </a>
                <?php else: ?>
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-gray-100 text-sm font-medium rounded-l-md text-gray-400 cursor-not-allowed">
                        Previous
                    </span>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">
                            <?= $i ?>
                        </span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium rounded-r-md hover:bg-gray-50">
                        Next
                    </a>
                <?php else: ?>
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-gray-100 text-sm font-medium rounded-r-md text-gray-400 cursor-not-allowed">
                        Next
                    </span>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/admin_footer.php'; ?>