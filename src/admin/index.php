<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

// Get stats for dashboard
$stats = [
    'rooms' => 0,
    'bookings' => 0,
    'users' => 0,
    'revenue' => 0
];

// Count rooms
$rooms_query = "SELECT COUNT(*) as count FROM rooms";
$result = $conn->query($rooms_query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['rooms'] = $row['count'];
}

// Count bookings
$bookings_query = "SELECT COUNT(*) as count FROM bookings";
$result = $conn->query($bookings_query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['bookings'] = $row['count'];
}

// Count users
$users_query = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($users_query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['users'] = $row['count'];
}

// Calculate revenue
$revenue_query = "SELECT SUM(amount) as total FROM payments WHERE status = 'completed'";
$result = $conn->query($revenue_query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['revenue'] = $row['total'] ?? 0;
}

// Get recent bookings
$recent_bookings_query = "SELECT b.*, u.username, r.room_number, r.room_type 
                         FROM bookings b
                         JOIN users u ON b.user_id = u.user_id
                         JOIN rooms r ON b.room_id = r.room_id
                         ORDER BY b.created_at DESC LIMIT 5";
$recent_bookings_result = $conn->query($recent_bookings_query);
$recent_bookings = [];

if ($recent_bookings_result) {
    while ($row = $recent_bookings_result->fetch_assoc()) {
        $recent_bookings[] = $row;
    }
}

include 'includes/header.php'?>
<div class="bg-gray-100">

</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Rooms</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900"><?= $stats['rooms'] ?></dd>
            </dl>
        </div>
    </div>
    
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Bookings</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900"><?= $stats['bookings'] ?></dd>
            </dl>
        </div>
    </div>
    
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900"><?= $stats['users'] ?></dd>
            </dl>
        </div>
    </div>
    
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">$<?= number_format($stats['revenue'], 2) ?></dd>
            </dl>
        </div>
    </div>
</div>

<div class="mt-8">
    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Bookings</h3>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($recent_bookings)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No bookings found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_bookings as $booking): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#<?= $booking['booking_id'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($booking['username']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($booking['room_type']) ?> (<?= htmlspecialchars($booking['room_number']) ?>)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M j, Y', strtotime($booking['check_in_date'])) ?> - 
                                <?= date('M j, Y', strtotime($booking['check_out_date'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php 
                                    switch ($booking['booking_status']) {
                                        case 'confirmed':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'pending':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'cancelled':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                ?>">
                                    <?= ucfirst($booking['booking_status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                $<?= number_format($booking['total_price'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>