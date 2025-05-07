<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/authentication.php';
require_once 'includes/functions.php';

ensureLoggedIn();

$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
$success = false;

if ($booking_id <= 0) {
    $error = "Invalid booking ID.";
} else {
    $booking_query = "SELECT b.*, r.room_number, r.room_type, p.payment_id 
                     FROM bookings b
                     JOIN rooms r ON b.room_id = r.room_id
                     LEFT JOIN payments p ON b.booking_id = p.booking_id AND p.status = 'completed'
                     WHERE b.booking_id = $booking_id AND b.user_id = $user_id";
    $booking_result = $conn->query($booking_query);
    
    if ($booking_result->num_rows == 0) {
        $error = "Booking not found or you don't have permission to cancel it.";
    } else {
        $booking = $booking_result->fetch_assoc();
        
        if ($booking['booking_status'] !== 'confirmed' && $booking['booking_status'] !== 'pending') {
            $error = "This booking cannot be cancelled. Only confirmed or pending bookings can be cancelled.";
        } else {
            $today = new DateTime();
            $check_in = new DateTime($booking['check_in_date']);
            $days_until_checkin = $today->diff($check_in)->days;
            
            $cancellation_fee = 0;
            $refund_amount = $booking['total_price'];
            $cancellation_policy = "";
            
            // Implement cancellation policy
            // Criteria
            // - Free cancel if 7 or more days before check-in
            // - 50% fee if cancelled between 3-7 days before check-in
            // - 100% fee if cancelled less than 3 days before check-in

            if ($days_until_checkin >= 7) {
                $cancellation_fee = 0;
                $refund_amount = $booking['total_price'];
                $cancellation_policy = "No cancellation fee applies as you're cancelling 7 or more days before check-in.";
            } elseif ($days_until_checkin >= 3) {
                $cancellation_fee = $booking['total_price'] * 0.5;
                $refund_amount = $booking['total_price'] - $cancellation_fee;
                $cancellation_policy = "A 50% cancellation fee applies as you're cancelling between 3-7 days before check-in.";
            } else {
                $cancellation_fee = $booking['total_price'];
                $refund_amount = 0;
                $cancellation_policy = "A 100% cancellation fee applies as you're cancelling less than 3 days before check-in.";
            }
            
            // cancell if form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    $update_booking_query = "UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = $booking_id";
                    $conn->query($update_booking_query);
                    
                    // If there was a payment, process refund
                    if (!empty($booking['payment_id']) && $refund_amount > 0) {

                        // for now this example, maybe using payment gateway in production
                        $refund_transaction_id = 'REF' . time() . rand(100, 999);
                        
                        $refund_query = "INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status) 
                                        VALUES ($booking_id, -$refund_amount, 'refund', '$refund_transaction_id', 'completed')";
                        $conn->query($refund_query);
                    }
                    
                    $update_room_query = "UPDATE rooms SET status = 'available' WHERE room_id = {$booking['room_id']}";
                    $conn->query($update_room_query);
                    
                    $conn->commit();
                    $success = true;
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Failed to cancel booking: " . $e->getMessage();
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Cancel Booking</h1>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
        <div class="mt-4">
            <a href="account.php" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded">
                Back to My Account
            </a>
        </div>
    <?php elseif ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <p class="font-bold">Booking Cancelled Successfully</p>
            <p>Your booking has been cancelled and your room is now available for other guests.</p>
            <?php if ($refund_amount > 0): ?>
                <p class="mt-2">A refund of <?= formatCurrency($refund_amount, 2) ?> has been processed and will be credited to your original payment method within 5-7 business days.</p>
            <?php endif; ?>
        </div>
        <div class="mt-4">
            <a href="account.php" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded">
                Back to My Account
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="bg-gray-100 px-4 py-2">
                <h2 class="font-semibold">Booking Details</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-600">Booking ID</p>
                        <p class="font-medium">#<?= $booking_id ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Room</p>
                        <p class="font-medium"><?= htmlspecialchars($booking['room_type']) ?> Room (<?= htmlspecialchars($booking['room_number']) ?>)</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Check-in Date</p>
                        <p class="font-medium"><?= date('M j, Y', strtotime($booking['check_in_date'])) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Check-out Date</p>
                        <p class="font-medium"><?= date('M j, Y', strtotime($booking['check_out_date'])) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Price</p>
                        <p class="font-medium"><?= formatCurrency($booking['total_price'], 2) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="font-medium"><?= ucfirst(htmlspecialchars($booking['booking_status'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="bg-gray-100 px-4 py-2">
                <h2 class="font-semibold">Cancellation Policy</h2>
            </div>
            <div class="p-6">
                <p class="mb-4"><?= $cancellation_policy ?></p>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Please note that this action cannot be undone. Once cancelled, you'll need to make a new booking if you change your mind.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h3 class="font-medium mb-2">Cancellation Summary:</h3>
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div class="text-sm text-gray-600">Total Paid:</div>
                        <div class="text-sm font-medium"><?= formatCurrency($booking['total_price'], 2) ?></div>
                        
                        <div class="text-sm text-gray-600">Cancellation Fee:</div>
                        <div class="text-sm font-medium"><?= formatCurrency($cancellation_fee, 2) ?></div>
                        
                        <div class="text-sm text-gray-600">Refund Amount:</div>
                        <div class="text-sm font-medium"><?= formatCurrency($refund_amount, 2) ?></div>
                    </div>
                </div>
                
                <form method="POST" class="mt-6">
                    <div class="flex items-center mb-4">
                        <input type="checkbox" id="confirm-cancel" class="mr-2" required>
                        <label for="confirm-cancel" class="text-sm">I understand the cancellation policy and want to cancel this booking.</label>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <a href="account.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded">
                            Go Back
                        </a>
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded">
                            Cancel Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>