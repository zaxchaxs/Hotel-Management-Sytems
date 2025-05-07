<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/authentication.php';

ensureLoggedIn();

$errors = [];
$success = false;
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

if ($booking_id <= 0) {
    header('Location: account.php');
    exit;
}

$booking_query = "SELECT b.*, r.room_id, r.room_number, r.room_type, r.capacity, r.price_per_night, r.image_url, r.status AS room_status
                 FROM bookings b
                 JOIN rooms r ON b.room_id = r.room_id
                 WHERE b.booking_id = $booking_id AND b.user_id = $user_id";
$booking_result = $conn->query($booking_query);

if ($booking_result->num_rows == 0) {
    header('Location: account.php');
    exit;
}

$booking = $booking_result->fetch_assoc();

if ($booking['booking_status'] !== 'confirmed') {
    $_SESSION['error_message'] = "Only confirmed bookings can be modified.";
    header('Location: account.php');
    exit;
}

$check_in_date = new DateTime($booking['check_in_date']);
$check_out_date = new DateTime($booking['check_out_date']);
$nights = $check_in_date->diff($check_out_date)->days;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_check_in = $_POST['check_in'] ?? '';
    $new_check_out = $_POST['check_out'] ?? '';
    $new_adults = intval($_POST['adults'] ?? 1);
    $new_children = intval($_POST['children'] ?? 0);
    
    if (empty($new_check_in)) {
        $errors[] = "Check-in date is required";
    }
    
    if (empty($new_check_out)) {
        $errors[] = "Check-out date is required";
    }
    
    if (!empty($new_check_in) && !empty($new_check_out)) {
        $new_check_in_obj = new DateTime($new_check_in);
        $new_check_out_obj = new DateTime($new_check_out);
        
        if ($new_check_out_obj <= $new_check_in_obj) {
            $errors[] = "Check-out date must be after check-in date";
        }
        
        $now = new DateTime();
        if ($new_check_in_obj < $now) {
            $errors[] = "Check-in date must be in the future";
        }
        
        $new_nights = $new_check_in_obj->diff($new_check_out_obj)->days;
        
        $new_total_price = $booking['price_per_night'] * $new_nights;
    }
    
    if ($new_adults + $new_children > $booking['capacity']) {
        $errors[] = "Total guests exceeds room capacity of " . $booking['capacity'];
    }
    
    if (empty($errors)) {
        $availability_query = "SELECT * FROM bookings 
                             WHERE room_id = {$booking['room_id']} 
                             AND booking_id != $booking_id
                             AND booking_status IN ('confirmed', 'checked_in') 
                             AND (
                                 (check_in_date <= '$new_check_in' AND check_out_date >= '$new_check_in') OR
                                 (check_in_date <= '$new_check_out' AND check_out_date >= '$new_check_out') OR
                                 (check_in_date >= '$new_check_in' AND check_out_date <= '$new_check_out')
                             )";
        $availability_result = $conn->query($availability_query);
        
        if ($availability_result->num_rows > 0) {
            $errors[] = "Room is not available for the selected dates";
        }
    }
    
    if (empty($errors)) {
        $price_difference = $new_total_price - $booking['total_price'];
        
        $update_query = "UPDATE bookings SET 
                        check_in_date = '$new_check_in', 
                        check_out_date = '$new_check_out', 
                        adults = $new_adults, 
                        children = $new_children, 
                        total_price = $new_total_price
                        WHERE booking_id = $booking_id";
        
        if ($conn->query($update_query)) {
            $success = true;
            
            if ($price_difference != 0) {
                $transaction_id = 'ADJ' . time() . rand(100, 999);
                $adjustment_status = $price_difference > 0 ? 'pending' : 'completed';
                
                $adjustment_query = "INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status, notes) 
                                   VALUES ($booking_id, ABS($price_difference), 
                                   'adjustment', '$transaction_id', '$adjustment_status', 
                                   'Booking modification adjustment: " . ($price_difference > 0 ? "Additional charge" : "Refund") . "')";
                $conn->query($adjustment_query);
            }
            
            if ($price_difference > 0) {
                header("Location: payment.php?booking_id=$booking_id&adjustment=1");
                exit;
            }
            
            $booking_result = $conn->query($booking_query);
            $booking = $booking_result->fetch_assoc();
        } else {
            $errors[] = "Failed to update booking: " . $conn->error;
        }
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Modify Booking</h1>
    
    <?php if ($success && $price_difference <= 0): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <p class="font-bold">Booking Modified Successfully!</p>
            <p>Your booking details have been updated.</p>
            <?php if ($price_difference < 0): ?>
                <p class="mt-2">A refund of $<?= number_format(abs($price_difference), 2) ?> has been processed.</p>
            <?php endif; ?>
            <p class="mt-4">
                <a href="account.php" class="text-blue-500 hover:underline">Return to your account</a>
            </p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="bg-gray-100 px-4 py-2">
            <h2 class="font-semibold">Current Booking Details</h2>
        </div>
        <div class="p-4">
            <div class="flex items-center">
                <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="Room" class="w-20 h-20 object-cover rounded">
                <div class="ml-4">
                    <h3 class="font-medium"><?= htmlspecialchars($booking['room_type']) ?> Room</h3>
                    <p class="text-sm text-gray-600">Room #<?= htmlspecialchars($booking['room_number']) ?></p>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-2">
                <div>
                    <p class="text-sm text-gray-600">Check-in</p>
                    <p class="font-medium"><?= date('M j, Y', strtotime($booking['check_in_date'])) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Check-out</p>
                    <p class="font-medium"><?= date('M j, Y', strtotime($booking['check_out_date'])) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Guests</p>
                    <p class="font-medium"><?= $booking['adults'] ?> Adults, <?= $booking['children'] ?> Children</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Price</p>
                    <p class="font-bold">$<?= number_format($booking['total_price'], 2) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modification Form -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-100 px-4 py-2">
            <h2 class="font-semibold">Modify Booking</h2>
        </div>
        <div class="p-6">
            <form method="POST" id="modify-form">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Check-in Date</label>
                        <input type="date" name="check_in" value="<?= isset($_POST['check_in']) ? htmlspecialchars($_POST['check_in']) : htmlspecialchars($booking['check_in_date']) ?>" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Check-out Date</label>
                        <input type="date" name="check_out" value="<?= isset($_POST['check_out']) ? htmlspecialchars($_POST['check_out']) : htmlspecialchars($booking['check_out_date']) ?>" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Adults</label>
                        <select name="adults" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <?php for ($i = 1; $i <= $booking['capacity']; $i++): ?>
                                <option value="<?= $i ?>" <?= (isset($_POST['adults']) ? $_POST['adults'] : $booking['adults']) == $i ? 'selected' : '' ?>>
                                    <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Children</label>
                        <select name="children" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <?php for ($i = 0; $i <= 3; $i++): ?>
                                <option value="<?= $i ?>" <?= (isset($_POST['children']) ? $_POST['children'] : $booking['children']) == $i ? 'selected' : '' ?>>
                                    <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mt-6 bg-gray-50 p-4 rounded border border-gray-200">
                    <h3 class="font-medium text-gray-700 mb-2">Price Calculation</h3>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <span>Current price:</span>
                            <span class="font-semibold">$<?= number_format($booking['total_price'], 2) ?></span>
                        </div>
                        <div>
                            <span>Price per night:</span>
                            <span class="font-semibold">$<?= number_format($booking['price_per_night'], 2) ?></span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span>New price estimate: </span>
                        <span id="nights-count" class="font-semibold">0</span> nights × 
                        <span class="font-semibold">$<?= number_format($booking['price_per_night'], 2) ?></span> = 
                        <span id="total-price" class="font-bold">$0.00</span>
                    </div>
                    <div class="mt-1">
                        <span>Price difference: </span>
                        <span id="price-difference" class="font-bold">$0.00</span>
                        <span id="payment-note" class="text-sm text-gray-600 ml-2"></span>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center justify-between">
                    <a href="account.php" class="text-gray-600 hover:underline">Cancel and return to account</a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="mt-6 bg-yellow-50 p-4 rounded border border-yellow-200">
        <h3 class="font-medium text-yellow-800 mb-2">Modification Policy</h3>
        <ul class="list-disc list-inside text-sm text-yellow-800">
            <li>Changes to your booking are subject to room availability</li>
            <li>If the new booking total is higher, you will need to pay the difference</li>
            <li>If the new booking total is lower, the difference will be refunded to your original payment method</li>
            <li>Modifications cannot be made to checked-in, checked-out, or cancelled bookings</li>
            <li>For any assistance, please contact our support team</li>
        </ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pricePerNight = <?= $booking['price_per_night'] ?>;
    const originalTotal = <?= $booking['total_price'] ?>;
    const checkInInput = document.querySelector('input[name="check_in"]');
    const checkOutInput = document.querySelector('input[name="check_out"]');
    const nightsCountElement = document.getElementById('nights-count');
    const totalPriceElement = document.getElementById('total-price');
    const priceDifferenceElement = document.getElementById('price-difference');
    const paymentNoteElement = document.getElementById('payment-note');
    
    function calculatePrice() {
        const checkIn = new Date(checkInInput.value);
        const checkOut = new Date(checkOutInput.value);
        
        if (checkInInput.value && checkOutInput.value && checkOut > checkIn) {
            const diffTime = checkOut.getTime() - checkIn.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const totalPrice = diffDays * pricePerNight;
            const priceDifference = totalPrice - originalTotal;
            
            nightsCountElement.textContent = diffDays;
            totalPriceElement.textContent = '$' + totalPrice.toFixed(2);
            
            if (priceDifference > 0) {
                priceDifferenceElement.textContent = '+$' + priceDifference.toFixed(2);
                priceDifferenceElement.className = 'font-bold text-red-600';
                paymentNoteElement.textContent = '(Additional payment required)';
            } else if (priceDifference < 0) {
                priceDifferenceElement.textContent = '-$' + Math.abs(priceDifference).toFixed(2);
                priceDifferenceElement.className = 'font-bold text-green-600';
                paymentNoteElement.textContent = '(Will be refunded)';
            } else {
                priceDifferenceElement.textContent = '$0.00';
                priceDifferenceElement.className = 'font-bold';
                paymentNoteElement.textContent = '';
            }
        } else {
            nightsCountElement.textContent = '0';
            totalPriceElement.textContent = '$0.00';
            priceDifferenceElement.textContent = '$0.00';
            paymentNoteElement.textContent = '';
        }
    }
    
    checkInInput.addEventListener('change', calculatePrice);
    checkOutInput.addEventListener('change', calculatePrice);
    
    // Calculate initial price
    calculatePrice();
});
</script>

<?php include 'includes/footer.php'; ?>