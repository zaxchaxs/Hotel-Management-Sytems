<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/authentication.php';
require_once 'includes/functions.php';

// Ensure user is logged in
ensureLoggedIn();

$errors = [];
$success = false;

// Get room_id from query string
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;

// Get check-in and check-out dates from query string
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';

// If room_id is invalid, redirect to rooms page
if ($room_id <= 0) {
    header('Location: rooms.php');
    exit;
}

// Get room details
$room_query = "SELECT * FROM rooms WHERE room_id = $room_id AND status = 'available'";
$room_result = $conn->query($room_query);

if ($room_result->num_rows == 0) {
    // Room not found or not available
    header('Location: rooms.php');
    exit;
}

$room = $room_result->fetch_assoc();

// Process booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $check_in_date = $_POST['check_in'] ?? '';
    $check_out_date = $_POST['check_out'] ?? '';
    $adults = intval($_POST['adults'] ?? 1);
    $children = intval($_POST['children'] ?? 0);
    
    // Validate form data
    if (empty($check_in_date)) {
        $errors[] = "Check-in date is required";
    }
    
    if (empty($check_out_date)) {
        $errors[] = "Check-out date is required";
    }
    
    // Ensure check-out date is after check-in date
    if (!empty($check_in_date) && !empty($check_out_date) && strtotime($check_out_date) <= strtotime($check_in_date)) {
        $errors[] = "Check-out date must be after check-in date";
    }
    
    // Calculate number of nights
    $nights = 0;
    if (!empty($check_in_date) && !empty($check_out_date)) {
        $check_in_obj = new DateTime($check_in_date);
        $check_out_obj = new DateTime($check_out_date);
        $interval = $check_in_obj->diff($check_out_obj);
        $nights = $interval->days;
    }
    
    // Calculate total price
    $total_price = $room['price_per_night'] * $nights;
    
    // Check if room is available for selected dates
    if (empty($errors)) {
        $availability_query = "SELECT * FROM bookings 
                             WHERE room_id = $room_id 
                             AND booking_status IN ('confirmed', 'checked_in') 
                             AND (
                                 (check_in_date <= '$check_in_date' AND check_out_date >= '$check_in_date') OR
                                 (check_in_date <= '$check_out_date' AND check_out_date >= '$check_out_date') OR
                                 (check_in_date >= '$check_in_date' AND check_out_date <= '$check_out_date')
                             )";
        $availability_result = $conn->query($availability_query);
        
        if ($availability_result->num_rows > 0) {
            $errors[] = "Room is not available for the selected dates";
        }
    }
    
    // If no errors, create booking
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        
        $booking_query = "INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date, adults, children, total_price, booking_status) 
                         VALUES ($user_id, $room_id, '$check_in_date', '$check_out_date', $adults, $children, $total_price, 'pending')";
        
        if ($conn->query($booking_query)) {
            $booking_id = $conn->insert_id;
            // Redirect to payment page
            header("Location: payment.php?booking_id=$booking_id");
            exit;
        } else {
            $errors[] = "Failed to create booking: " . $conn->error;
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Book Room</h1>
    
    <!-- Room Details -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="md:flex">
            <div class="md:w-1/3">
                <img src="<?= htmlspecialchars($room['image_url']) ?>" alt="Room <?= htmlspecialchars($room['room_number']) ?>" class="w-full h-full object-cover">
            </div>
            <div class="p-6 md:w-2/3">
                <h2 class="text-2xl font-semibold"><?= htmlspecialchars($room['room_type']) ?> Room</h2>
                <p class="text-gray-600">Room #<?= htmlspecialchars($room['room_number']) ?></p>
                <p class="mt-2"><?= htmlspecialchars($room['description']) ?></p>
                <div class="mt-4">
                    <h3 class="font-semibold">Amenities:</h3>
                    <p><?= htmlspecialchars($room['amenities']) ?></p>
                </div>
                <div class="mt-4">
                    <span class="text-2xl font-bold"><?= toRupiah($room['price_per_night']) ?></span>
                    <span class="text-gray-600"> / night</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Booking Form -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-4">Booking Details</h2>
            
            <!-- Display errors if any -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="booking-form">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Check-in Date</label>
                        <input type="date" name="check_in" value="<?= htmlspecialchars($check_in) ?>" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Check-out Date</label>
                        <input type="date" name="check_out" value="<?= htmlspecialchars($check_out) ?>" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Adults</label>
                        <select name="adults" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <?php for ($i = 1; $i <= $room['capacity']; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Children</label>
                        <select name="children" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <?php for ($i = 0; $i <= 3; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mt-6 text-right">
                    <div class="inline-block mr-6">
                        <span class="text-lg">Total: </span>
                        <span id="total-nights" class="font-semibold">0</span> nights × 
                        <span class="font-semibold">$<?= htmlspecialchars($room['price_per_night']) ?></span> = 
                        <span id="total-price" class="text-xl font-bold">$0</span>
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded">
                        Proceed to Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pricePerNight = <?= $room['price_per_night'] ?>;
    const checkInInput = document.querySelector('input[name="check_in"]');
    const checkOutInput = document.querySelector('input[name="check_out"]');
    const totalNightsElement = document.getElementById('total-nights');
    const totalPriceElement = document.getElementById('total-price');
    
    function calculateTotal() {
        const checkIn = new Date(checkInInput.value);
        const checkOut = new Date(checkOutInput.value);
        
        if (checkInInput.value && checkOutInput.value && checkOut > checkIn) {
            const diffTime = checkOut.getTime() - checkIn.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const totalPrice = diffDays * pricePerNight;
            
            totalNightsElement.textContent = diffDays;
            totalPriceElement.textContent = '$' + totalPrice.toFixed(2);
        } else {
            totalNightsElement.textContent = '0';
            totalPriceElement.textContent = '$0';
        }
    }
    
    checkInInput.addEventListener('change', calculateTotal);
    checkOutInput.addEventListener('change', calculateTotal);
    
    // Calculate initial total if dates are pre-filled
    calculateTotal();
});
</script>

<?php include 'includes/footer.php'; ?>