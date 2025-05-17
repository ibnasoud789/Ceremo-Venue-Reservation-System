<?php
session_start();
include 'customerAuthorization.php';
include "db.php";
include 'navbar.php';

// Initialize
$success = false;
$venue = null;

// --- Handle data from booking page and store in session ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['venue_id'], $_POST['bookingDate'], $_POST['timeSlot'], $_POST['guests'], $_POST['package_name'])) {
  // Collect selected add-ons
  $selected_addons = [];
  foreach ($_POST as $key => $val) {
    if (strpos($key, 'addon_') === 0 && !empty($val)) {
      $addon_id = (int)$val;
      $stmt = $conn->prepare("SELECT ao.option_name, ao.price, ac.name AS category_name FROM add_on_options ao JOIN add_on_categories ac ON ao.category_id = ac.id WHERE ao.id = ?");
      $stmt->bind_param("i", $addon_id);
      $stmt->execute();
      $result = $stmt->get_result()->fetch_assoc();
      if ($result) {
        $selected_addons[] = [
          'category' => $result['category_name'],
          'name' => $result['option_name'],
          'price' => (float)$result['price']
        ];
      }
    }
  }

  // Save in session
  $_SESSION['pending_booking'] = [
    'venue_id' => $_POST['venue_id'],
    'bookingDate' => $_POST['bookingDate'],
    'timeSlot' => $_POST['timeSlot'],
    'guests' => $_POST['guests'],
    'package_name' => $_POST['package_name'],
    'add_ons' => $selected_addons
  ];

  header("Location: bookingConfirmation.php");
  exit();
}

// --- Process final confirmation ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking']) && isset($_SESSION['pending_booking'])) {
  $data = $_SESSION['pending_booking'];
  $venue_id = $data['venue_id'];
  $customer_id = $_SESSION['customer_id'] ?? 1;
  $bookingDate = $data['bookingDate'];
  $timeSlot = $data['timeSlot'];
  $guests = $data['guests'];
  $package_name = $data['package_name'];
  $status = "Active";
  $revenue = $guests * 30;
  $newBookingCheck = 'No';

  $stmt = $conn->prepare("INSERT INTO bookings (venue_id, customer_id, booking_date, timeslot, guests, package_name, status, revenue, newBookingCheck) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)");
  $stmt->bind_param("iisssssis", $venue_id, $customer_id, $bookingDate, $timeSlot, $guests, $package_name, $status, $revenue, $newBookingCheck);
  $stmt->execute();

  $success = true;
  unset($_SESSION['pending_booking']);
}

// --- Display data ---
$data = $_SESSION['pending_booking'] ?? null;

if (!$data && !$success) {
  header("Location: index.php");
  exit();
}

if (!$success) {
  // Venue
  $stmt = $conn->prepare("SELECT * FROM venues WHERE id = ?");
  $stmt->bind_param("i", $data['venue_id']);
  $stmt->execute();
  $venue = $stmt->get_result()->fetch_assoc();

  // Package price
  $pstmt = $conn->prepare("SELECT promotional_price FROM food_packages WHERE venue_id = ? AND package_name = ?");
  $pstmt->bind_param("is", $data['venue_id'], $data['package_name']);
  $pstmt->execute();
  $res = $pstmt->get_result()->fetch_assoc();
  $price = $res['promotional_price'] ?? 0;

  $total = $data['guests'] * $price;
  $revenue = $data['guests'] * 30;

  $add_on_total = 0;
  foreach ($data['add_ons'] ?? [] as $a) {
    $add_on_total += $a['price'];
  }

  $grand = $total + $revenue + $add_on_total;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Booking Confirmation</title>
  <style>
    .confirmation-wrapper {
      max-width: 1200px;
      margin: auto;
    }

    .confirmation-container {
      display: flex;
      flex-wrap: wrap;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
      overflow: hidden;
    }

    .booking-details,
    .price-summary {
      flex: 1 1 50%;
      padding: 40px;
      box-sizing: border-box;
    }

    .booking-details {
      background: #faf7ff;
      border-right: 1px solid #e0dff5;
    }

    .booking-details h2 {
      color: #6a0dad;
      margin-bottom: 25px;
      font-size: 1.8rem;
    }

    .booking-details p {
      margin: 12px 0;
      font-size: 1.05rem;
      color: #222;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .booking-details .icon {
      font-size: 1.1rem;
      color: #6a0dad;
    }

    .add-on-block {
      background: #f5f0ff;
      padding: 25px;
      border-left: 4px solid #6a0dad;
      border-radius: 10px;
      margin-top: 30px;
    }

    .add-on-block h4 {
      font-size: 1.25rem;
      color: #6a0dad;
      margin-bottom: 15px;
    }

    .add-on-block ul {
      list-style: none;
      padding-left: 0;
      margin: 0;
    }

    .add-on-block li {
      margin-bottom: 10px;
      font-size: 1rem;
      position: relative;
      padding-left: 20px;
    }

    .add-on-block li::before {
      content: "✔";
      color: #6a0dad;
      position: absolute;
      left: 0;
    }

    .price-summary h3 {
      font-size: 1.5rem;
      margin-bottom: 25px;
      color: #2a0076;
    }

    .price-summary .row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
      font-size: 1.05rem;
    }

    .grand-total {
      background: linear-gradient(90deg, #f9f9fb, #f1f1f8);
      padding: 18px 24px;
      border-left: 5px solid #6a0dad;
      border-radius: 10px;
      margin: 30px 0 20px;
      font-size: 1.2rem;
      font-weight: 600;
      color: #2a0076;
      box-shadow: 0 4px 12px rgba(106, 13, 173, 0.07);
    }

    .advance-box {
      background: #fff7f7;
      border: 1px solid #f5d0d0;
      border-radius: 10px;
      padding: 20px;
      margin-top: 20px;
    }

    .advance-box h4 {
      margin-bottom: 10px;
      font-size: 1.1rem;
      color: #8b0000;
    }

    .advance-box .row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
      font-size: 1.05rem;
    }

    .advance-box .note {
      font-size: 0.9rem;
      color: #555;
      margin-top: 8px;
      font-style: italic;
    }

    .btn {
      background: linear-gradient(to right, #6a0dad, #8a2be2);
      color: #fff;
      padding: 14px 28px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 500;
      margin-top: 30px;
      width: 100%;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background: linear-gradient(to right, #53179f, #731dc4);
    }

    .success {
      background: #e4ffe4;
      border: 1px solid #2ecc71;
      color: #239d3a;
      padding: 18px;
      font-weight: 600;
      border-radius: 10px;
      text-align: center;
      font-size: 1.1rem;
      margin-bottom: 30px;
    }

    .booking-summary {
      background: #f5f0ff;
      padding: 25px;
      border-left: 4px solid #6a0dad;
      border-radius: 10px;
      margin-top: 30px;
    }
  </style>
</head>

<body>
  <div class="confirmation-wrapper">
    <?php if ($success): ?>
      <div class="success">✅ Booking successful! Redirecting to homepage...</div>
      <script>
        setTimeout(() => {
          window.location.href = 'index.php';
        }, 3000);
      </script>
    <?php else: ?>
      <div class="confirmation-container">
        <!-- Left: Booking Details -->
        <div class="booking-details">
          <h2>🎉 Booking Summary</h2>
          <div class="booking-summary">
            <p><span class="icon">📍</span><strong>Venue:</strong> <?= htmlspecialchars($venue['name']) ?></p>
            <p><span class="icon">📅</span><strong>Date:</strong> <?= htmlspecialchars(string: $data['bookingDate']) ?></p>
            <p><span class="icon">🕒</span><strong>Time Slot:</strong> <?= htmlspecialchars($data['timeSlot']) ?></p>
            <p><span class="icon">👥</span><strong>Guests:</strong> <?= htmlspecialchars($data['guests']) ?></p>
            <p><span class="icon">🍽️</span><strong>Food Package:</strong> <?= htmlspecialchars($data['package_name']) ?></p>
          </div>


          <?php if (!empty($data['add_ons'])): ?>
            <div class="add-on-block">
              <h4>Selected Add-Ons:</h4>
              <ul>
                <?php foreach ($data['add_ons'] as $addon): ?>
                  <li><strong><?= htmlspecialchars($addon['category']) ?>:</strong> <?= htmlspecialchars($addon['name']) ?> (৳<?= number_format($addon['price']) ?>)</li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>

        <!-- Right: Cost Summary + Advance -->
        <div class="price-summary">
          <h3>💰 Cost Summary</h3>
          <div class="row"><span>Total Food Cost:</span><strong>৳<?= number_format($total) ?></strong></div>
          <div class="row"><span>Service Charge:</span><strong>৳<?= number_format($revenue) ?></strong></div>
          <?php if ($add_on_total): ?>
            <div class="row"><span>Add-On Cost:</span><strong>৳<?= number_format($add_on_total) ?></strong></div>
          <?php endif; ?>
          <div class="grand-total">Grand Total: ৳<?= number_format($grand) ?></div>

          <?php $advanceAmount = ceil($grand * 0.40); ?>
          <div class="advance-box">
            <h4>Advance Payment</h4>
            <div class="row"><span>40% Payable Now:</span><strong>৳<?= number_format($advanceAmount) ?></strong></div>
            <p class="note">This must be paid to confirm booking. The rest can be paid at the venue.</p>
          </div>

          <form method="POST" action="dummyPaymentGateway.php">
            <input type="hidden" name="total" value="<?= $grand ?>">
            <input type="hidden" name="advance" value="<?= $advanceAmount ?>">
            <button type="submit" class="btn">💳 Proceed to Payment</button>
          </form>

        </div>
      </div>
    <?php endif; ?>
  </div>
</body>


</html>