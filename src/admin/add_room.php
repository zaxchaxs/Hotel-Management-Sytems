<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pageTitle = 'Add New Room';
$currentPage = 'rooms';
$roomId = generateRoomID();

$errors = [];
$success = false;

// Process form submission
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

    // Validate form data
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === UPLOAD_ERR_OK) {
        // Define upload directory
        $upload_dir = ROOT_NAME.'assets/images/rooms/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Get file info
        $file_name = $_FILES['room_image']['name'];
        $file_tmp = $_FILES['room_image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Check file extension
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_extensions)) {
            $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
        
        // Generate unique filename to prevent overwriting
        $new_file_name = uniqid('room_', true) . '.' . $file_ext;
        $file_path = $upload_dir . $new_file_name;
        
        // Move uploaded file
        if (empty($errors)) {
            if (move_uploaded_file($file_tmp, '../assets/images/rooms/'.$new_file_name)) {
                $image_url = $file_path; // stored to database
            } else {
                $errors[] = "Failed to upload the image.";
            }
        }
    } else if ($_FILES['room_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // If there was an error other than no file uploaded
        $upload_error_messages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        $errors[] = "Upload error: " . ($upload_error_messages[$_FILES['room_image']['error']] ?? 'Unknown error');
    };

    if (empty($room_number)) {
        $errors[] = "Room number is required";
    } else {
        // Check if room number already exists
        $check_query = "SELECT room_id FROM rooms WHERE room_number = '$room_number'";
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

    // If no errors, insert room
    if (empty($errors)) {
        $insert_query = "INSERT INTO rooms (room_number, room_type, capacity, price_per_night, description, amenities, image_url, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('ssiissss', $room_number, $room_type, $capacity, $price_per_night, $description, $amenities, $image_url, $status);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Failed to add room: " . $conn->error;
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
        <p>The room has been added successfully.</p>
        <p class="mt-2">
            <a href="rooms.php" class="text-green-700 font-semibold hover:underline">Return to rooms list</a> or
            <a href="add_room.php" class="text-green-700 font-semibold hover:underline">add another room</a>
        </p>
    </div>
<?php else: ?>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Add New Room</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Fill in the details below to add a new room.</p>
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
            <form method="POST" class="px-6 py-4" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Number *</label>
                        <input type="text" name="room_number" value="<?= $success ? '' : (isset($roomId) ? htmlspecialchars($roomId) : '') ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Type *</label>
                        <select name="room_type" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            <option value="">Select Room Type</option>
                            <option value="Standard" <?= isset($_POST['room_type']) && $_POST['room_type'] === 'Standard' ? 'selected' : '' ?>>Standard</option>
                            <option value="Deluxe" <?= isset($_POST['room_type']) && $_POST['room_type'] === 'Deluxe' ? 'selected' : '' ?>>Deluxe</option>
                            <option value="Executive" <?= isset($_POST['room_type']) && $_POST['room_type'] === 'Executive' ? 'selected' : '' ?>>Executive</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacity *</label>
                        <select name="capacity" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?= $i ?>" <?= isset($_POST['capacity']) && intval($_POST['capacity']) === $i ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price Per Night (Rp) *</label>
                        <input type="number" name="price_per_night" min="0" step="0.01" value="<?= $success ? '' : htmlspecialchars($_POST['price_per_night'] ?? '') ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?= $success ? '' : htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amenities</label>
                        <textarea name="amenities" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            placeholder="WiFi, TV, Mini Bar, etc."><?= $success ? '' : htmlspecialchars($_POST['amenities'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Image</label>
                        <input type="file" name="room_image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <?php if (isset($current_image) && !empty($current_image)): ?>
                            <div class="mt-2">
                                <p class="text-sm text-gray-600">Current image: <?= htmlspecialchars(basename($current_image)) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="available" <?= isset($_POST['status']) && $_POST['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                            <option value="occupied" <?= isset($_POST['status']) && $_POST['status'] === 'occupied' ? 'selected' : '' ?>>Occupied</option>
                            <option value="maintenance" <?= isset($_POST['status']) && $_POST['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 text-right">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded">
                        Add Room
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>