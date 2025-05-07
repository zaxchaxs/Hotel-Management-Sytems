<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pageTitle = 'Manage Rooms';
$currentPage = 'rooms';

$errors = [];
$success = '';

// Handle room deletion
if (isset($_POST['delete_room']) && isset($_POST['room_id'])) {
    $room_id = intval($_POST['room_id']);
    
    // Check if the room is booked
    $check_bookings_query = "SELECT COUNT(*) as count FROM bookings 
                           WHERE room_id = $room_id 
                           AND booking_status IN ('confirmed', 'checked_in')";
    $result = $conn->query($check_bookings_query);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $errors[] = "Cannot delete room #$room_id because it has active bookings.";
    } else {
        $delete_query = "DELETE FROM rooms WHERE room_id = $room_id";
        if ($conn->query($delete_query)) {
            $success = "Room #$room_id has been deleted successfully.";
        } else {
            $errors[] = "Failed to delete room: " . $conn->error;
        }
    }
}

// Get rooms list
$rooms_query = "SELECT * FROM rooms ORDER BY room_number";
$rooms_result = $conn->query($rooms_query);
$rooms = [];

if ($rooms_result) {
    while ($row = $rooms_result->fetch_assoc()) {
        $rooms[] = $row;
    }
}

include 'includes/header.php';
?>

<div class="mb-4 flex justify-between items-center">
    <h3 class="text-lg leading-6 font-medium text-gray-900">All Rooms</h3>
    <a href="add_room.php" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded">
        Add New Room
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc list-inside">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($rooms)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No rooms found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($room['room_number']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($room['room_type']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $room['capacity'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= formatCurrency($room['price_per_night'], 2) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php 
                                switch ($room['status']) {
                                    case 'available':
                                        echo 'bg-green-100 text-green-800';
                                        break;
                                    case 'occupied':
                                        echo 'bg-blue-100 text-blue-800';
                                        break;
                                    case 'maintenance':
                                        echo 'bg-red-100 text-red-800';
                                        break;
                                    default:
                                        echo 'bg-gray-100 text-gray-800';
                                }
                            ?>">
                                <?= ucfirst($room['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="view_room.php?id=<?= $room['room_id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                            <a href="edit_room.php?id=<?= $room['room_id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this room?');">
                                <input type="hidden" name="room_id" value="<?= $room['room_id'] ?>">
                                <button type="submit" name="delete_room" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>