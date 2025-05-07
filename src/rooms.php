<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get filter parameters
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$room_type = $_GET['room_type'] ?? '';
$capacity = $_GET['capacity'] ?? '';

// query on filters
$query = "SELECT * FROM rooms WHERE status = 'available'";

if (!empty($room_type)) {
    $query .= " AND room_type = '$room_type'";
}

if (!empty($capacity)) {
    $query .= " AND capacity >= $capacity";
}

if (!empty($check_in) && !empty($check_out)) {
    $query .= " AND room_id NOT IN (
        SELECT room_id FROM bookings 
        WHERE booking_status IN ('confirmed', 'checked_in') 
        AND (
            (check_in_date <= '$check_in' AND check_out_date >= '$check_in') OR
            (check_in_date <= '$check_out' AND check_out_date >= '$check_out') OR
            (check_in_date >= '$check_in' AND check_out_date <= '$check_out')
        )
    )";
}

$result = $conn->query($query);
$rooms = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Available Rooms</h1>
    
    <!-- Ini search form -->
    <form class="bg-gray-100 p-4 rounded-lg mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Check-in Date</label>
                <input type="date" name="check_in" value="<?= $check_in ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Check-out Date</label>
                <input type="date" name="check_out" value="<?= $check_out ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Room Type</label>
                <select name="room_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">All Types</option>
                    <option value="Standard" <?= $room_type == 'Standard' ? 'selected' : '' ?>>Standard</option>
                    <option value="Deluxe" <?= $room_type == 'Deluxe' ? 'selected' : '' ?>>Deluxe</option>
                    <option value="Suite" <?= $room_type == 'Suite' ? 'selected' : '' ?>>Suite</option>
                    <option value="Executive" <?= $room_type == 'Executive' ? 'selected' : '' ?>>Executive</option>
                    <option value="Penthouse" <?= $room_type == 'penthouse' ? 'selected' : '' ?>>Penthouse</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Guests</label>
                <select name="capacity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Any</option>
                    <option value="1" <?= $capacity == '1' ? 'selected' : '' ?>>1</option>
                    <option value="2" <?= $capacity == '2' ? 'selected' : '' ?>>2</option>
                    <option value="3" <?= $capacity == '3' ? 'selected' : '' ?>>3+</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded">
                Search Rooms
            </button>
        </div>
    </form>
    
    <!-- Looping rooms -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($rooms as $room): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <img src="<?= htmlspecialchars($room['image_url']) ?>" alt="Room <?= htmlspecialchars($room['room_number']) ?>" class="w-full h-48 object-cover">
            <div class="p-4">
                <h2 class="text-xl font-semibold"><?= htmlspecialchars($room['room_type']) ?> Room</h2>
                <p class="text-gray-600">Room #<?= htmlspecialchars($room['room_number']) ?></p>
                <p class="mt-2"><?= htmlspecialchars($room['description']) ?></p>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xl font-bold"><?= formatCurrency($room['price_per_night']) ?><span class="text-sm font-normal"> / night</span></span>
                    <a href="booking.php?room_id=<?= $room['room_id'] ?><?= !empty($check_in) ? '&check_in='.$check_in : '' ?><?= !empty($check_out) ? '&check_out='.$check_out : '' ?>" 
                       class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded">
                        Book Now
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($rooms)): ?>
        <div class="col-span-full text-center py-8">
            <p class="text-xl text-gray-600">Tidak ada kamar tersedia yang sesuai dengan kriteria Anda.</p>
            <p class="mt-2">Coba sesuaikan penelusuran atau tanggal Anda.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>