<?php
// submit_cancellation.php

session_start();
include 'db.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
  echo json_encode(['message' => 'Unauthorized access.']);
  exit;
}

$booking_id = $_POST['booking_id'] ?? null;
$cancellation_explanation = $_POST['cancellation_explanation'] ?? null;

if (!$booking_id || !$cancellation_explanation) {
  echo json_encode(['message' => 'Invalid input.']);
  exit;
}

// Update the booking with the cancellation request
$stmt = $conn->prepare("
    UPDATE bookings 
    SET  cancellationStatus = 'Pending' ,cancellationReason=?
    WHERE id = ? AND customer_id = ?
");
$stmt->bind_param('sii', $cancellation_explanation, $booking_id, $_SESSION['user_id']);
$stmt->execute();

if ($stmt->affected_rows > 0) {
  // Set the message in the session to display it in the modal
  $_SESSION['cancellation_message'] = 'Cancellation request submitted successfully. Kindly wait for confirmation!';
  header('Location: customerBooking.php');  // Redirect back to the bookings page
} else {
  echo json_encode(['message' => 'Failed to submit cancellation request.']);
}

$stmt->close();
$conn->close();
