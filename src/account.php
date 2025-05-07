<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/authentication.php';
require_once 'includes/functions.php';

ensureLoggedIn();

$user_id = $_SESSION['user_id'];

$user = getUserById($user_id);

$bookings = getUserBookings($user_id);
echo "<script>console.log(" . json_encode($bookings) . ");</script>";

$profile_updated = false;
$profile_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($full_name)) {
        $profile_errors[] = "Full name is required";
    }
    
    if (empty($email)) {
        $profile_errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profile_errors[] = "Invalid email format";
    }
    
    // Check if email is already used by another user
    if (!empty($email) && $email !== $user['email']) {
        $email_check_query = "SELECT * FROM users WHERE email = '$email' AND user_id != $user_id";
        $email_check_result = $conn->query($email_check_query);
        if ($email_check_result->num_rows > 0) {
            $profile_errors[] = "Email is already in use";
        }
    }
    
    // Password change (optional)
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $profile_errors[] = "Current password is required to set a new password";
        } else if (!password_verify($current_password, $user['password'])) {
            $profile_errors[] = "Current password is incorrect";
        }
        
        // Validate new password
        if (strlen($new_password) < 8) {
            $profile_errors[] = "New password must be at least 8 characters";
        }
        
        // Confirm new password
        if ($new_password !== $confirm_password) {
            $profile_errors[] = "New passwords do not match";
        }
    }
    
    if (empty($profile_errors)) {
        $update_query = "UPDATE users SET full_name = '$full_name', email = '$email'";
        
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query .= ", password = '$hashed_password'";
        }
        
        $update_query .= " WHERE user_id = $user_id";
        
        if ($conn->query($update_query)) {
            $profile_updated = true;
            $user = getUserById($user_id);
        } else {
            $profile_errors[] = "Failed to update profile: " . $conn->error;
        }
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">My Account</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="p-6">
                    <div class="text-center mb-4">
                        <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center text-2xl font-bold text-gray-500">
                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                        </div>
                        <h2 class="mt-2 text-xl font-semibold"><?= htmlspecialchars($user['full_name']) ?></h2>
                        <p class="text-gray-600"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                    
                    <div class="mt-6">
                        <button id="edit-profile-btn" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded mb-3">
                            Edit Profile
                        </button>
                        <a href="auth/logout.php" class="block w-full text-center border border-gray-300 hover:bg-gray-100 text-gray-700 font-medium py-2 px-4 rounded">
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="md:col-span-2">
            <div id="profile-form" class="bg-white rounded-lg shadow-md overflow-hidden mb-6" style="display: none;">
                <div class="bg-gray-100 px-4 py-2">
                    <h2 class="font-semibold">Edit Profile</h2>
                </div>
                <div class="p-6">
                    <?php if ($profile_updated): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <p>Profile updated successfully!</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($profile_errors)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside">
                                <?php foreach ($profile_errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        </div>
                        
                        <h3 class="font-medium text-gray-700 mt-6 mb-2">Change Password (Optional)</h3>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" name="current_password" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="new_password" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="confirm_password" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="button" id="cancel-edit" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded mr-2">
                                Cancel
                            </button>
                            <button type="submit" name="update_profile" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gray-100 px-4 py-2">
                    <h2 class="font-semibold">My Bookings</h2>
                </div>
                
                <?php if (empty($bookings)): ?>
                    <div class="p-6 text-center text-gray-600">
                        <p>You don't have any bookings yet.</p>
                        <a href="rooms.php" class="text-blue-500 hover:underline mt-2 inline-block">Browse Rooms</a>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="p-4">
                                <div class="flex flex-wrap md:flex-nowrap">
                                    <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="Room" class="w-full md:w-32 h-24 object-cover rounded mb-3 md:mb-0">
                                    <div class="w-full md:pl-4">
                                        <div class="flex flex-wrap justify-between mb-2">
                                            <h3 class="font-medium"><?= htmlspecialchars($booking['room_type']) ?> Room</h3>
                                            <span class="text-sm <?= getBookingStatusInfo($booking['booking_status'])['class'] ?>">
                                                <?= ucfirst(htmlspecialchars($booking['booking_status'])) ?>
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600">Room #<?= htmlspecialchars($booking['room_number']) ?></p>
                                        <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                            <div>
                                                <span class="text-gray-600">Check-in:</span>
                                                <span class="block font-medium"><?= date('M j, Y', strtotime($booking['check_in_date'])) ?></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Check-out:</span>
                                                <span class="block font-medium"><?= date('M j, Y', strtotime($booking['check_out_date'])) ?></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Guests:</span>
                                                <span class="block font-medium"><?= $booking['adults'] ?> Adults, <?= $booking['children'] ?> Children</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Total:</span>
                                                <span class="block font-medium"><?= formatCurrency($booking['total_price'], 2) ?></span>
                                            </div>
                                        </div>
                                        
                                        <?php if ($booking['booking_status'] === 'pending'): ?>
                                            <div class="mt-3">
                                                <a href="payment.php?booking_id=<?= $booking['booking_id'] ?>" class="bg-green-500 hover:bg-green-600 text-white text-sm font-medium py-1 px-3 rounded">
                                                    Complete Payment
                                                </a>
                                            </div>
                                        <?php elseif ($booking['booking_status'] === 'confirmed'): ?>
                                            <div class="mt-3">
                                                <a href="cancel-booking.php?id=<?= $booking['booking_id'] ?>" class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-1 px-3 rounded">
                                                    Cancel Booking
                                                </a>
                                                <a href="booking-details.php?id=<?= $booking['booking_id'] ?>" class="ml-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-1 px-3 rounded">
                                                    View Details
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profile-form');
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const cancelEditBtn = document.getElementById('cancel-edit');
    
    editProfileBtn.addEventListener('click', function() {
        profileForm.style.display = 'block';
    });
    
    cancelEditBtn.addEventListener('click', function() {
        profileForm.style.display = 'none';
    });
    
    // Show profile form if there are errors or if it was just updated
    <?php if (!empty($profile_errors) || $profile_updated): ?>
        profileForm.style.display = 'block';
    <?php endif; ?>
});
</script>

<?php include 'includes/footer.php'; ?>