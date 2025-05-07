<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/authentication.php';
require_once 'includes/functions.php';

ensureLoggedIn();

$user_id = $_SESSION['user_id'];

// Check filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$valid_statuses = ['all', 'pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];
if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = 'all';
}

$bookings_query = "SELECT b.*, r.room_number, r.room_type, r.price_per_night, r.image_url 
                  FROM bookings b
                  JOIN rooms r ON b.room_id = r.room_id
                  WHERE b.user_id = $user_id";

if ($status_filter !== 'all') {
    $bookings_query .= " AND b.booking_status = '$status_filter'";
}

$bookings_query .= " ORDER BY b.created_at DESC";
$bookings_result = $conn->query($bookings_query);
$bookings = [];

if ($bookings_result->num_rows > 0) {
    while ($row = $bookings_result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">My Bookings</h1>
    
    <!-- Filter -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="flex flex-wrap -mb-px">
            <a href="bookings.php?status=all" 
               class="mr-8 py-4 px-1 border-b-2 font-medium text-sm leading-5 <?= $status_filter === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                All Bookings
            </a>
            <a href="bookings.php?status=pending" 
               class="mr-8 py-4 px-1 border-b-2 font-medium text-sm leading-5 <?= $status_filter === 'pending' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                Pending
            </a>
            <a href="bookings.php?status=confirmed" 
               class="mr-8 py-4 px-1 border-b-2 font-medium text-sm leading-5 <?= $status_filter === 'confirmed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                Confirmed
            </a>
            <a href="bookings.php?status=checked_in" 
               class="mr-8 py-4 px-1 border-b-2 font-medium text-sm leading-5 <?= $status_filter === 'checked_in' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                Checked In
            </a>
            <a href="bookings.php?status=checked_out" 
               class="mr-8 py-4 px-1 border-b-2 font-medium text-sm leading-5 <?= $status_filter === 'checked_out' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                Checked Out
            </a>
            <a href="bookings.php?status=cancelled" 
               class="py-4 px-1 border-b-2 font-medium text-sm leading-5 <?= $status_filter === 'cancelled' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                Cancelled
            </a>
        </nav>
    </div>
    
    <?php if (empty($bookings)): ?>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-600 mb-4">You don't have any <?= $status_filter !== 'all' ? $status_filter : '' ?> bookings.</p>
            <a href="rooms.php" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded">
                Browse Rooms
            </a>
        </div>
    <?php else: ?>
        <!-- Bookings List -->
        <div class="grid grid-cols-1 gap-6">
            <?php foreach ($bookings as $booking): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="md:flex">
                        <div class="md:w-1/4">
                            <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="Room" class="w-full h-full object-cover">
                        </div>
                        <div class="p-6 md:w-3/4">
                            <div class="flex flex-wrap justify-between items-start mb-4">
                                <div>
                                    <h2 class="text-xl font-semibold"><?= htmlspecialchars($booking['room_type']) ?> Room</h2>
                                    <p class="text-gray-600">Room #<?= htmlspecialchars($booking['room_number']) ?></p>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= getBookingStatusInfo($booking['booking_status'])['class'] ?>">
                                    <?= getStatusLabel($booking['booking_status']) ?>
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                <div>
                                    <span class="block text-sm text-gray-600">Booking Reference</span>
                                    <span class="font-medium">#<?= $booking['booking_id'] ?></span>
                                </div>
                                <div>
                                    <span class="block text-sm text-gray-600">Booking Date</span>
                                    <span class="font-medium"><?= date('M j, Y', strtotime($booking['created_at'])) ?></span>
                                </div>
                                <div>
                                    <span class="block text-sm text-gray-600">Check-in</span>
                                    <span class="font-medium"><?= date('M j, Y', strtotime($booking['check_in_date'])) ?></span>
                                </div>
                                <div>
                                    <span class="block text-sm text-gray-600">Check-out</span>
                                    <span class="font-medium"><?= date('M j, Y', strtotime($booking['check_out_date'])) ?></span>
                                </div>
                                <div>
                                    <span class="block text-sm text-gray-600">Guests</span>
                                    <span class="font-medium"><?= $booking['adults'] ?> Adults, <?= $booking['children'] ?> Children</span>
                                </div>
                                <div>
                                    <span class="block text-sm text-gray-600">Nights</span>
                                    <span class="font-medium"><?= calculateNights($booking['check_in_date'], $booking['check_out_date']) ?></span>
                                </div>
                                <div>
                                    <span class="block text-sm text-gray-600">Price per Night</span>
                                    <span class="font-medium"><?= formatCurrency($booking['price_per_night'], 2) ?></span>
                                </div>
                                <div>
                                    <span class="block text-sm text-gray-600">Total Price</span>
                                    <span class="font-bold"><?= formatCurrency($booking['total_price'], 2) ?></span>
                                </div>
                            </div>
                            
                            <!-- Action buttons -->
                            <div class="flex flex-wrap items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    <?= getBookingMessage($booking['booking_status'], $booking['check_in_date']) ?>
                                </div>
                                <div class="mt-4 md:mt-0">
                                    <?php if ($booking['booking_status'] === 'pending'): ?>
                                        <a href="payment.php?booking_id=<?= $booking['booking_id'] ?>" class="bg-green-500 hover:bg-green-600 text-white text-sm font-medium py-2 px-4 rounded">
                                            Complete Payment
                                        </a>
                                        <a href="cancel-booking.php?id=<?= $booking['booking_id'] ?>&cancel=1" class="ml-2 text-red-500 hover:text-red-700" 
                                           onclick="return confirm('Are you sure you want to cancel this booking?');">
                                            Cancel
                                        </a>
                                    <?php elseif ($booking['booking_status'] === 'confirmed'): ?>
                                        <a href="booking-details.php?id=<?= $booking['booking_id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-2 px-4 rounded">
                                            View Details
                                        </a>
                                        <?php if (isWithinCancellationPeriod($booking['check_in_date'])): ?>
                                            <a href="cancel-booking.php?id=<?= $booking['booking_id'] ?>&cancel=1" class="ml-2 text-red-500 hover:text-red-700" 
                                               onclick="return confirm('Are you sure you want to cancel this booking?');">
                                                Cancel
                                            </a>
                                        <?php endif; ?>
                                    <?php elseif ($booking['booking_status'] === 'checked_in'): ?>
                                        <a href="booking-details.php?id=<?= $booking['booking_id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-2 px-4 rounded">
                                            View Details
                                        </a>
                                    <?php elseif ($booking['booking_status'] === 'checked_out' || $booking['booking_status'] === 'cancelled'): ?>
                                        <a href="booking-details.php?id=<?= $booking['booking_id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-2 px-4 rounded">
                                            View Details
                                        </a>
                                        <a href="rooms.php" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium py-2 px-4 rounded">
                                            Book Again
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php

function getStatusLabel($status) {
    switch($status) {
        case 'pending':
            return 'Pending Payment';
        case 'confirmed':
            return 'Confirmed';
        case 'checked_in':
            return 'Checked In';
        case 'checked_out':
            return 'Checked Out';
        case 'cancelled':
            return 'Cancelled';
        default:
            return ucfirst($status);
    }
}

function isWithinCancellationPeriod($checkInDate) {
    $now = new DateTime();
    $checkIn = new DateTime($checkInDate);
    $interval = $now->diff($checkIn);
    
    return ($interval->days > 2 || ($interval->days == 2 && $interval->h > 0));
}

function getBookingMessage($status, $checkInDate) {
    switch($status) {
        case 'pending':
            return 'Your booking is awaiting payment. Please complete payment to confirm your reservation.';
        case 'confirmed':
            $daysUntil = daysUntilCheckIn($checkInDate);
            if ($daysUntil > 0) {
                return "$daysUntil days until your stay. We're looking forward to your arrival!";
            } else {
                return "Your check-in is today! We're ready to welcome you.";
            }
        case 'checked_in':
            return 'You are currently checked in. Enjoy your stay!';
        case 'checked_out':
            return 'Thank you for staying with us. We hope you enjoyed your visit.';
        case 'cancelled':
            return 'This booking has been cancelled.';
        default:
            return '';
    }
}

function daysUntilCheckIn($checkInDate) {
    $now = new DateTime();
    $checkIn = new DateTime($checkInDate);
    $interval = $now->diff($checkIn);
    return $interval->days;
}
?>

<?php include 'includes/footer.php'; ?>