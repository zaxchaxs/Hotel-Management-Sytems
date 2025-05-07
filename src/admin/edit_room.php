<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$pageTitle = 'Edit Room';
$currentPage = 'rooms';

$errors = [];
$success = false;
$room = null;

// Get room ID from query string
$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If room_id is invalid, redirect to rooms page
if ($room_id <= 0) {
    header('Location: rooms.php');
    exit;
}

// Get room details
$room_query = "SELECT * FROM rooms WHERE room_id = $room_id";
$room_result = $conn->query($room_query);

if ($room_result->num_rows == 0) {
    // Room not found
    header('Location: rooms.php');
    exit;
}

$room = $room_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $room_number = $_POST['room_number'] ?? '';
    $room_type = $_POST['room_type'] ?? '';
    $capacity = intval($_POST['capacity'] ?? 1);
    $price_per_night = floatval($_POST['price_per_night'] ?? 0);
    $description = $_POST['description'] ?? '';
    $amenities = $_POST['amenities'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    $status = $_POST['status'] ?? 'available';
    
    if (empty($room_number)) {
        $errors[] = "Room number is required";
    } else {
        $check_query = "SELECT room_id FROM rooms WHERE room_number = '$room_number' AND room_id != $room_id";
        $result = $conn->query($check_query);
        if ($result->num_rows > 0) {
            $errors[] = "Room number already exists";
        }
    }
    
    if (empty($room_type)) {
        $errors[] = "Room type is required";
    }
    
    if ($capacity <= 0) {
        $errors[] = "Capacity must be greater than 0";
    }
    
    if ($price_per_night <= 0) {
        $errors[] = "Price must be greater than 0";
    }
    
    // If no errors, update room
    if (empty($errors)) {
        $update_query = "UPDATE rooms SET 
                        room_number = ?, 
                        room_type = ?, 
                        capacity = ?, 
                        price_per_night = ?, 
                        description = ?, 
                        amenities = ?, 
                        image_url = ?, 
                        status = ? 
                        WHERE room_id = ?";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('ssiissssi', $room_number, $room_type, $capacity, $price_per_night, $description, $amenities, $image_url, $status, $room_id);
        
        if ($stmt->execute()) {
            $success = true;
            // Refresh room data
            $room_result = $conn->query($room_query);
            $room = $room_result->fetch_assoc();
        } else {
            $errors[] = "Failed to update room: " . $conn->error;
        }
        
        $stmt->close();
    }
}

include 'includes/header.php';
?>

<div class="mb-4">
    <a href="rooms.php" class="text-blue-500 hover:underline">← Back to Rooms</a>
</div>

<?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <p class="font-bold">Success!</p>
        <p>The room has been updated successfully.</p>
    </div>
<?php endif; ?>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Room #<?= htmlspecialchars($room['room_number']) ?></h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Update the room details below.</p>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mx-6 mb-4">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="border-t border-gray-200">
        <form method="POST" class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Room Number *</label>
                    <input type="text" name="room_number" value="<?= htmlspecialchars($room['room_number']) ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Room Type *</label>
                    <select name="room_type" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        <option value="">Select Room Type</option>
                        <option value="Standard" <?= $room['room_type'] === 'Standard' ? 'selected' : '' ?>>Standard</option>
                        <option value="Deluxe" <?= $room['room_type'] === 'Deluxe' ? 'selected' : '' ?>>Deluxe</option>
                        <option value="Suite" <?= $room['room_type'] === 'Suite' ? 'selected' : '' ?>>Suite</option>
                        <option value="Executive" <?= $room['room_type'] === 'Executive' ? 'selected' : '' ?>>Executive</option>
                        <option value="Penthouse" <?= $room['room_type'] === 'Penthouse' ? 'selected' : '' ?>>Penthouse</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacity *</label>
                    <select name="capacity" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?= $i ?>" <?= intval($room['capacity']) === $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price Per Night ($) *</label>
                    <input type="number" name="price_per_night" min="0" step="0.01" value="<?= htmlspecialchars($room['price_per_night']) ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?= htmlspecialchars($room['description']) ?></textarea>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amenities</label>
                    <textarea name="amenities" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md" 
                             placeholder="WiFi, TV, Mini Bar, etc."><?= htmlspecialchars($room['amenities']) ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                    <input type="text" name="image_url" value="<?= htmlspecialchars($room['image_url']) ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="available" <?= $room['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="occupied" <?= $room['status'] === 'occupied' ? 'selected' : '' ?>>Occupied</option>
                        <option value="maintenance" <?= $room['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-6 text-right">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded">
                    Update Room
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>