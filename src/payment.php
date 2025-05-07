<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/authentication.php';

ensureLoggedIn();

$errors = [];
$success = false;

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if ($booking_id <= 0) {
    header('Location: account.php');
    exit;
}

$booking_query = "SELECT b.*, r.room_number, r.room_type, r.price_per_night, r.image_url 
                 FROM bookings b
                 JOIN rooms r ON b.room_id = r.room_id
                 WHERE b.booking_id = $booking_id AND b.user_id = {$_SESSION['user_id']}";
$booking_result = $conn->query($booking_query);

if ($booking_result->num_rows == 0) {
    header('Location: account.php');
    exit;
}

$booking = $booking_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    $card_number = $_POST['card_number'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $cardholder_name = $_POST['cardholder_name'] ?? '';

    if (empty($payment_method)) {
        $errors[] = "Payment method is required";
    }

    if ($payment_method === 'credit_card') {
        if (empty($card_number)) {
            $errors[] = "Card number is required";
        } elseif (!preg_match('/^\d{16}$/', str_replace(' ', '', $card_number))) {
            $errors[] = "Invalid card number";
        }

        if (empty($expiry_date)) {
            $errors[] = "Expiry date is required";
        } elseif (!preg_match('/^\d{2}\/\d{2}$/', $expiry_date)) {
            $errors[] = "Invalid expiry date format (MM/YY)";
        }

        if (empty($cvv)) {
            $errors[] = "CVV is required";
        } elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
            $errors[] = "Invalid CVV";
        }

        if (empty($cardholder_name)) {
            $errors[] = "Cardholder name is required";
        }
    }

    if (empty($errors)) {
        $transaction_id = 'TXN' . time() . rand(100, 999);

        // Logic for payment, but for now just show succesful payment

        $payment_query = "INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status) 
                         VALUES ($booking_id, {$booking['total_price']}, '$payment_method', '$transaction_id', 'completed')";

        if ($conn->query($payment_query)) {
            // Update booking status
            $update_booking_query = "UPDATE bookings SET booking_status = 'confirmed' WHERE booking_id = $booking_id";
            $conn->query($update_booking_query);

            $success = true;
        } else {
            $errors[] = "Failed to process payment: " . $conn->error;
        }
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Payment</h1>

    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <p class="font-bold">Payment Successful!</p>
            <p>Your booking has been confirmed. Thank you for choosing our hotel.</p>
            <p class="mt-4">
                <a href="account.php" class="text-blue-500 hover:underline">View your bookings</a>
            </p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="bg-gray-100 px-4 py-2">
                <h2 class="font-semibold">Booking Summary</h2>
            </div>
            <div class="p-4">
                <div class="flex items-center">
                    <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="Room" class="w-20 h-20 object-cover rounded">
                    <div class="ml-4">
                        <h3 class="font-medium"><?= htmlspecialchars($booking['room_type']) ?> Room</h3>
                        <p class="text-sm text-gray-600">Room #<?= htmlspecialchars($booking['room_number']) ?></p>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
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

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">Payment Details</h2>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="payment_method" value="credit_card" class="form-radio" checked>
                                <span class="ml-2">Credit Card</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="payment_method" value="paypal" class="form-radio">
                                <span class="ml-2">PayPal</span>
                            </label>
                        </div>
                    </div>

                    <div id="credit-card-form">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                            <input type="text" name="card_number" placeholder="1234 5678 9012 3456"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md" maxlength="19">
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                                <input type="text" name="expiry_date" placeholder="MM/YY"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md" maxlength="5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                                <input type="text" name="cvv" placeholder="123"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md" maxlength="4">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cardholder Name</label>
                            <input type="text" name="cardholder_name" placeholder="John Doe"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div id="paypal-form" class="hidden">
                        <p class="mb-4">You will be redirected to PayPal to complete your payment.</p>
                    </div>

                    <div class="mt-6 text-right">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded">
                            Pay $<?= number_format($booking['total_price'], 2) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethodInputs = document.querySelectorAll('input[name="payment_method"]');
        const creditCardForm = document.getElementById('credit-card-form');
        const paypalForm = document.getElementById('paypal-form');

        function togglePaymentForm() {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;

            if (selectedMethod === 'credit_card') {
                creditCardForm.classList.remove('hidden');
                paypalForm.classList.add('hidden');
            } else if (selectedMethod === 'paypal') {
                creditCardForm.classList.add('hidden');
                paypalForm.classList.remove('hidden');
            }
        }

        paymentMethodInputs.forEach(input => {
            input.addEventListener('change', togglePaymentForm);
        });

        // Format credit card number with spaces
        const cardNumberInput = document.querySelector('input[name="card_number"]');
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = '';

            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }

            e.target.value = formattedValue;
        });

        // Format expiry date
        const expiryDateInput = document.querySelector('input[name="expiry_date"]');
        expiryDateInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');

            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }

            e.target.value = value;
        });
    });
</script>

<?php include 'includes/footer.php'; ?>