<?php
session_start();
include "db.php";
require 'vendor/autoload.php'; // for dompdf

use Dompdf\Dompdf;

if (!isset($_SESSION['pending_booking'])) {
  header("Location: index.php");
  exit();
}

$data = $_SESSION['pending_booking'];
$venue_id = $data['venue_id'];
$customer_id = $_SESSION['customer_id'] ?? 1;
$bookingDate = $data['bookingDate'];
$timeSlot = $data['timeSlot'];
$guests = $data['guests'];
$package_name = $data['package_name'];
$status = "Active";
$revenue = $guests * 30;
$paid = $_POST['paid'] ?? 0;

// Calculate food + addon total

$pstmt = $conn->prepare("SELECT promotional_price FROM food_packages WHERE venue_id = ? AND package_name = ?");
$pstmt->bind_param("is", $venue_id, $package_name);
$pstmt->execute();
$res = $pstmt->get_result()->fetch_assoc();
$price = $res['promotional_price'] ?? 0;
$food_total = $guests * $price;
$addon_total = 0;

foreach ($data['add_ons'] ?? [] as $a) {
  $addon_total += $a['price'];
}
$grand = $food_total + $revenue + $addon_total;

// Save booking
$stmt = $conn->prepare("INSERT INTO bookings (venue_id, customer_id, booking_date, timeslot, guests, package_name, status, revenue) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisssssi", $venue_id, $customer_id, $bookingDate, $timeSlot, $guests, $package_name, $status, $revenue);
$stmt->execute();

unset($_SESSION['pending_booking']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Payment Successful</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      padding: 40px;
      background: #f7f8fc;
      text-align: center;
    }

    .success-box {
      background: #fff;
      border-radius: 12px;
      padding: 40px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      max-width: 600px;
      margin: auto;
    }

    h2 {
      color: #2a0076;
      margin-bottom: 20px;
    }

    .details {
      text-align: left;
      margin-top: 30px;
      font-size: 1rem;
    }

    .btn {
      background: #6a0dad;
      color: white;
      padding: 12px 25px;
      border: none;
      border-radius: 8px;
      margin-top: 30px;
      cursor: pointer;
    }

    .btn:hover {
      background: #4c0082;
    }
  </style>
</head>

<body>
  <div class="success-box">
    <h2>🎉 Payment Successful!</h2>
    <p>Advance Paid: <strong>৳<?= number_format($paid) ?></strong></p>
    <div class="details">
      <p><strong>Date:</strong> <?= htmlspecialchars($bookingDate) ?></p>
      <p><strong>Time Slot:</strong> <?= htmlspecialchars($timeSlot) ?></p>
      <p><strong>Guests:</strong> <?= htmlspecialchars($guests) ?></p>
      <p><strong>Food Package:</strong> <?= htmlspecialchars($package_name) ?></p>
      <p><strong>Food Cost:</strong> ৳<?= number_format($food_total) ?></p>
      <p><strong>Service Charge:</strong> ৳<?= number_format($revenue) ?></p>
      <p><strong>Add-Ons:</strong> ৳<?= number_format($addon_total) ?></p>
      <p><strong>Total:</strong> ৳<?= number_format($grand) ?></p>
    </div>

    <form action="exportPdf.php" method="POST">
      <input type="hidden" name="date" value="<?= $bookingDate ?>">
      <input type="hidden" name="slot" value="<?= $timeSlot ?>">
      <input type="hidden" name="guests" value="<?= $guests ?>">
      <input type="hidden" name="package" value="<?= $package_name ?>">
      <input type="hidden" name="food_total" value="<?= $food_total ?>">
      <input type="hidden" name="service" value="<?= $revenue ?>">
      <input type="hidden" name="addons" value="<?= $addon_total ?>">
      <input type="hidden" name="grand" value="<?= $grand ?>">
      <input type="hidden" name="paid" value="<?= $paid ?>">
      <button type="submit" class="btn">📄 Download Invoice (PDF)</button>
    </form>
  </div>
</body>

</html>