<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/authentication.php';
require_once '../includes/functions.php';

ensureLoggedIn();

$user_id = $_SESSION['user_id'];
$errors = [];
$booking = null;


$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id <= 0) {
    header('Location: account.php');
    exit;
}

// Get booking details with joins to get room information
$booking_query = "SELECT b.*, 
                 r.room_number, r.room_type, r.price_per_night, r.capacity, r.amenities, r.description, r.image_url,
                 p.payment_id, p.payment_method, p.transaction_id, p.payment_date
                 FROM bookings b
                 JOIN rooms r ON b.room_id = r.room_id
                 LEFT JOIN payments p ON b.booking_id = p.booking_id AND p.status = 'completed'
                 WHERE b.booking_id = $booking_id AND b.user_id = $user_id";

$result = $conn->query($booking_query);

if ($result->num_rows === 0) {
    // Booking not found or doesn't belong to this user
    header('Location: account.php');
    exit;
}

$booking = $result->fetch_assoc();

// Calculate number of nights
$check_in = new DateTime($booking['check_in_date']);
$check_out = new DateTime($booking['check_out_date']);
$interval = $check_in->diff($check_out);
$nights = $interval->days;

// Process cancellation request
if (isset($_POST['cancel_booking']) && $booking['booking_status'] === 'confirmed') {
    $cancel_query = "UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = $booking_id AND user_id = $user_id";
    
    if ($conn->query($cancel_query)) {
        // Refresh booking data
        $result = $conn->query($booking_query);
        $booking = $result->fetch_assoc();
        
        // Add success message
        $cancellation_success = true;
    } else {
        $errors[] = "Failed to cancel booking. Please try again.";
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold">Booking Details</h1>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (isset($cancellation_success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <p>Your booking has been successfully cancelled.</p>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="bg-gray-100 px-4 py-2 flex justify-between items-center">
            <h2 class="font-semibold">Booking #<?= $booking['booking_id'] ?></h2>
            <span class="px-2 py-1 rounded text-sm font-semibold <?= getStatusBadgeClass($booking['booking_status']) ?>">
                <?= ucfirst(str_replace('_', ' ', $booking['booking_status'])) ?>
            </span>
        </div>
        <div class="p-6">
            <div class="md:flex">
                <div class="md:w-1/3 mb-6 md:mb-0">
                    <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="Room <?= htmlspecialchars($booking['room_number']) ?>" class="w-full h-48 object-cover rounded">
                    <h3 class="mt-4 text-xl font-semibold"><?= htmlspecialchars($booking['room_type']) ?> Room</h3>
                    <p class="text-gray-600">Room #<?= htmlspecialchars($booking['room_number']) ?></p>
                    <p class="mt-2 text-sm"><?= htmlspecialchars($booking['description']) ?></p>
                    
                    <div class="mt-4">
                        <h4 class="font-medium">Amenities</h4>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($booking['amenities']) ?></p>
                    </div>
                </div>
                
                <div class="md:w-2/3 md:pl-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium mb-2">Stay Information</h4>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-600">Check-in:</span>
                                    <span class="block font-medium"><?= date('D, M j, Y', strtotime($booking['check_in_date'])) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Check-out:</span>
                                    <span class="block font-medium"><?= date('D, M j, Y', strtotime($booking['check_out_date'])) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Nights:</span>
                                    <span class="block font-medium"><?= $nights ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Guests:</span>
                                    <span class="block font-medium"><?= $booking['adults'] ?> Adults, <?= $booking['children'] ?> Children</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium mb-2">Pricing Details</h4>
                            <div class="text-sm">
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">Room Rate:</span>
                                    <span><?= formatCurrency($booking['price_per_night'], 2) ?> / night</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">Stay Duration:</span>
                                    <span><?= $nights ?> nights</span>
                                </div>
                                <div class="flex justify-between font-medium text-base pt-2 border-t border-gray-300 mt-2">
                                    <span>Total:</span>
                                    <span><?= formatCurrency($booking['total_price'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($booking['payment_id'])): ?>
                    <div class="bg-gray-50 p-4 rounded mb-6">
                        <h4 class="font-medium mb-2">Payment Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Payment Method:</span>
                                <span class="block font-medium"><?= ucfirst(str_replace('_', ' ', $booking['payment_method'])) ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Transaction ID:</span>
                                <span class="block font-medium"><?= htmlspecialchars($booking['transaction_id']) ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Payment Date:</span>
                                <span class="block font-medium"><?= date('M j, Y H:i', strtotime($booking['payment_date'])) ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Amount Paid:</span>
                                <span class="block font-medium">$<?= number_format($booking['total_price'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="bg-gray-50 p-4 rounded">
                        <h4 class="font-medium mb-2">Booking Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Booking ID:</span>
                                <span class="block font-medium"><?= $booking['booking_id'] ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Booking Date:</span>
                                <span class="block font-medium"><?= date('M j, Y H:i', strtotime($booking['created_at'])) ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Status:</span>
                                <span class="block font-medium <?= getBookingStatusInfo($booking['booking_status']) ?>">
                                    <?= ucfirst(str_replace('_', ' ', $booking['booking_status'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function for status badge classes
function getStatusBadgeClass($status) {
    switch($status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'confirmed':
            return 'bg-green-100 text-green-800';
        case 'checked_in':
            return 'bg-blue-100 text-blue-800';
        case 'checked_out':
            return 'bg-gray-100 text-gray-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

include 'includes/footer.php';
?>