<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/authentication.php';

// Ensure user is logged in and is an admin or staff
ensureLoggedIn();
isAdmin();

$errors = [];
$success_message = '';

// Process booking action requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update booking status
    if (isset($_POST['update_status'])) {
        $booking_id = intval($_POST['booking_id']);
        $new_status = $_POST['status'];
        
        if (!in_array($new_status, ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'])) {
            $errors[] = "Invalid status selected.";
        } else {
            $update_query = "UPDATE bookings SET booking_status = ? WHERE booking_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $new_status, $booking_id);
            
            if ($stmt->execute()) {
                $success_message = "Booking status updated successfully.";
                
                // If status is changed to cancelled, update room status
                if ($new_status === 'cancelled') {
                    $room_query = "UPDATE rooms SET status = 'available' WHERE room_id = (SELECT room_id FROM bookings WHERE booking_id = ?)";
                    $stmt = $conn->prepare($room_query);
                    $stmt->bind_param("i", $booking_id);
                    $stmt->execute();
                }
            } else {
                $errors[] = "Failed to update booking status: " . $conn->error;
            }
        }
    }
    
    // Delete booking
    if (isset($_POST['delete_booking'])) {
        $booking_id = intval($_POST['booking_id']);
        
        // Check if there are any payments associated with this booking
        $payment_check_query = "SELECT COUNT(*) AS payment_count FROM payments WHERE booking_id = $booking_id";
        $payment_check_result = $conn->query($payment_check_query);
        $payment_count = $payment_check_result->fetch_assoc()['payment_count'];
        
        // If there are payments, delete them first
        if ($payment_count > 0) {
            $delete_payments_query = "DELETE FROM payments WHERE booking_id = $booking_id";
            $conn->query($delete_payments_query);
        }
        
        // Now delete the booking
        $delete_query = "DELETE FROM bookings WHERE booking_id = $booking_id";
        if ($conn->query($delete_query)) {
            $success_message = "Booking deleted successfully.";
        } else {
            $errors[] = "Failed to delete booking: " . $conn->error;
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query conditions
$conditions = [];

if (!empty($status_filter)) {
    $status_filter = $conn->real_escape_string($status_filter);
    $conditions[] = "b.booking_status = '$status_filter'";
}

if (!empty($date_filter)) {
    switch ($date_filter) {
        case 'today':
            $conditions[] = "DATE(b.check_in_date) = CURDATE()";
            break;
        case 'tomorrow':
            $conditions[] = "DATE(b.check_in_date) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'this_week':
            $conditions[] = "YEARWEEK(b.check_in_date, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'next_week':
            $conditions[] = "YEARWEEK(b.check_in_date, 1) = YEARWEEK(DATE_ADD(CURDATE(), INTERVAL 1 WEEK), 1)";
            break;
    }
}

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $conditions[] = "(u.username LIKE '%$search%' OR u.full_name LIKE '%$search%' OR r.room_number LIKE '%$search%')";
}

$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Get total bookings count for pagination
$count_query = "SELECT COUNT(*) as total FROM bookings b
                LEFT JOIN users u ON b.user_id = u.user_id
                LEFT JOIN rooms r ON b.room_id = r.room_id
                $where_clause";
$count_result = $conn->query($count_query);
$total_bookings = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_bookings / $per_page);

// Get bookings
$bookings_query = "SELECT b.*, u.username, u.full_name, r.room_number, r.room_type 
                  FROM bookings b
                  LEFT JOIN users u ON b.user_id = u.user_id
                  LEFT JOIN rooms r ON b.room_id = r.room_id
                  $where_clause
                  ORDER BY b.check_in_date DESC
                  LIMIT $offset, $per_page";
$bookings_result = $conn->query($bookings_query);
$bookings = [];

if ($bookings_result) {
    while ($row = $bookings_result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// Include admin header
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Booking Management</h1>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Back to Dashboard
        </a>
    </div>
    
    <!-- Display messages -->
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <p><?= htmlspecialchars($success_message) ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Filter form -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="checked_in" <?= $status_filter === 'checked_in' ? 'selected' : '' ?>>Checked In</option>
                        <option value="checked_out" <?= $status_filter === 'checked_out' ? 'selected' : '' ?>>Checked Out</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Filter</label>
                    <select name="date_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">All Dates</option>
                        <option value="today" <?= $date_filter === 'today' ? 'selected' : '' ?>>Today</option>
                        <option value="tomorrow" <?= $date_filter === 'tomorrow' ? 'selected' : '' ?>>Tomorrow</option>
                        <option value="this_week" <?= $date_filter === 'this_week' ? 'selected' : '' ?>>This Week</option>
                        <option value="next_week" <?= $date_filter === 'next_week' ? 'selected' : '' ?>>Next Week</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Search by guest name or room number" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded mr-2">
                        Filter
                    </button>
                    <a href="bookings.php" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bookings table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="bg-gray-50">
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">No bookings found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($booking['booking_id']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($booking['full_name']) ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($booking['username']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($booking['room_type']) ?> Room</div>
                                <div class="text-sm text-gray-500">Room #<?= htmlspecialchars($booking['room_number']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?= date('M j, Y', strtotime($booking['check_in_date'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?= date('M j, Y', strtotime($booking['check_out_date'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                $<?= number_format($booking['total_price'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php
                                    switch ($booking['booking_status']) {
                                        case 'confirmed':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'checked_in':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'checked_out':
                                            echo 'bg-purple-100 text-purple-800';
                                            break;
                                        case 'cancelled':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            echo 'bg-yellow-100 text-yellow-800';
                                    }
                                    ?>">
                                    <?= ucfirst(str_replace('_', ' ', htmlspecialchars($booking['booking_status']))) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M j, Y', strtotime($booking['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex space-x-2">
                                    <!-- Update Status Form -->
                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                        <select name="status" class="text-sm border border-gray-300 rounded py-1 px-2 mr-1">
                                            <option value="pending" <?= $booking['booking_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="confirmed" <?= $booking['booking_status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                            <option value="checked_in" <?= $booking['booking_status'] === 'checked_in' ? 'selected' : '' ?>>Checked In</option>
                                            <option value="checked_out" <?= $booking['booking_status'] === 'checked_out' ? 'selected' : '' ?>>Checked Out</option>
                                            <option value="cancelled" <?= $booking['booking_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded text-xs">
                                            Update
                                        </button>
                                    </form>
                                    
                                    <!-- View Details Link -->
                                    <a href="booking-details.php?id=<?= $booking['booking_id'] ?>" class="bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded text-xs">
                                        Details
                                    </a>
                                    
                                    <!-- Delete Booking Form -->
                                    <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                        <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                        <button type="submit" name="delete_booking" class="bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded text-xs">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="flex justify-center mt-6">
            <nav class="inline-flex rounded-md shadow">
                <?php 
                // Build the query string for pagination links
                $query_params = [];
                if (!empty($status_filter)) $query_params[] = "status=" . urlencode($status_filter);
                if (!empty($date_filter)) $query_params[] = "date_filter=" . urlencode($date_filter);
                if (!empty($search)) $query_params[] = "search=" . urlencode($search);
                $query_string = !empty($query_params) ? "&" . implode("&", $query_params) : "";
                ?>
                
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 . $query_string ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium rounded-l-md hover:bg-gray-50">
                        Previous
                    </a>
                <?php else: ?>
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-gray-100 text-sm font-medium rounded-l-md text-gray-400 cursor-not-allowed">
                        Previous
                    </span>
                <?php endif; ?>
                
                <?php
                // Calculate pagination range
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                // Show first page if not in range
                if ($start_page > 1) {
                    echo '<a href="?page=1' . $query_string . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50">1</a>';
                    if ($start_page > 2) {
                        echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium">...</span>';
                    }
                }
                
                // Display page links
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">
                            <?= $i ?>
                        </span>
                    <?php else: ?>
                        <a href="?page=<?= $i . $query_string ?>"
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php
                // Show last page if not in range
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium">...</span>';
                    }
                    echo '<a href="?page=' . $total_pages . $query_string . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50">' . $total_pages . '</a>';
                }
                ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 . $query_string ?>"
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium rounded-r-md hover:bg-gray-50">
                        Next
                    </a>
                <?php else: ?>
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-gray-100 text-sm font-medium rounded-r-md text-gray-400 cursor-not-allowed">
                        Next
                    </span>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
    
    <!-- Quick Stats -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        <?php
        // Get some statistics
        $stats_query = "SELECT 
                         COUNT(*) AS total_bookings,
                         SUM(CASE WHEN booking_status = 'pending' THEN 1 ELSE 0 END) AS pending_bookings,
                         SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_bookings,
                         SUM(CASE WHEN booking_status = 'checked_in' THEN 1 ELSE 0 END) AS checked_in_bookings,
                         SUM(CASE WHEN booking_status = 'checked_out' THEN 1 ELSE 0 END) AS checked_out_bookings,
                         SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_bookings,
                         SUM(CASE WHEN DATE(check_in_date) = CURDATE() THEN 1 ELSE 0 END) AS today_arrivals,
                         SUM(CASE WHEN DATE(check_out_date) = CURDATE() THEN 1 ELSE 0 END) AS today_departures
                       FROM bookings";
        $stats_result = $conn->query($stats_query);
        $stats = $stats_result->fetch_assoc();
        ?>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4">
                <h3 class="text-lg font-medium text-gray-900">Total Bookings</h3>
                <p class="mt-1 text-3xl font-semibold"><?= $stats['total_bookings'] ?></p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4">
                <h3 class="text-lg font-medium text-gray-900">Today's Arrivals</h3>
                <p class="mt-1 text-3xl font-semibold"><?= $stats['today_arrivals'] ?></p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4">
                <h3 class="text-lg font-medium text-gray-900">Today's Departures</h3>
                <p class="mt-1 text-3xl font-semibold"><?= $stats['today_departures'] ?></p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4">
                <h3 class="text-lg font-medium text-gray-900">Active Bookings</h3>
                <p class="mt-1 text-3xl font-semibold"><?= $stats['confirmed_bookings'] + $stats['checked_in_bookings'] ?></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>