<?php
// my_bookings.php

session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
  $here = $_SERVER['REQUEST_URI'];
  header('Location: login.php?redirect=' . urlencode($here));
  exit;
}

include 'db.php';  // defines $conn

$customer_id = $_SESSION['user_id'];

// Get current date
$today = date('Y-m-d');

// Fetch bookings and group them into pending and completed
$sql = "
  SELECT 
    b.id,
    v.name AS venue_name,
    CONCAT(v.area, ', ', v.city) AS venue_location,
    b.booking_date,
    b.timeslot,
    b.guests,
    b.package_name,
    b.status,
    b.revenue,
    b.cancellationStatus
  FROM bookings b
  JOIN venues v ON b.venue_id = v.id
  WHERE b.customer_id = ?
  ORDER BY b.booking_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);

// Separate bookings into pending and completed
$pendingBookings = [];
$completedBookings = [];
foreach ($bookings as $booking) {
  if ($booking['booking_date'] >= $today && $booking['status'] == 'Active') {
    $pendingBookings[] = $booking;
  } else {
    $completedBookings[] = $booking;
  }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>My Bookings — Ceremo</title>
  <link rel="stylesheet" href="customer.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
  <style>
    /* Bookings table styling */
    .bookings-container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 1rem;
    }

    .bookings-container h2 {
      font-size: 2rem;
      color: var(--text-dark);
      margin-bottom: 1.5rem;
    }

    .table-responsive {
      overflow-x: auto;
    }

    table.bookings-table {
      width: 100%;
      border-collapse: collapse;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
    }

    table.bookings-table thead {
      background: var(--pin-color);
    }

    table.bookings-table thead th {
      color: #fff;
      text-align: left;
      padding: 1rem;
      font-weight: 600;
    }

    table.bookings-table tbody tr:nth-child(even) {
      background: var(--bg-light);
    }

    table.bookings-table td {
      padding: 0.75rem 1rem;
      color: var(--text-dark);
      border-bottom: 1px solid #e5e7eb;
    }

    table.bookings-table td.status-Active {
      color: #2563eb;
      font-weight: 500;
    }

    table.bookings-table td.status-Completed {
      color: #10b981;
      font-weight: 500;
    }

    /* Modal Styling */
    .modal {
      display: none;
      position: fixed;
      z-index: 1001;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      max-width: 500px;
      width: 90%;
    }

    .modal-content button {
      background-color: var(--pin-color);
      color: white;
      padding: 10px;
      border-radius: 5px;
      cursor: pointer;
    }

    .modal-content button:hover {
      background-color: #45a049;
    }

    .modal-content .close {
      position: absolute;
      top: 5px;
      right: 10px;
      font-size: 1.5rem;
      cursor: pointer;
    }

    .request-cancellation-btn {
      color: red;
    }

    /* Updated button for Cancellation Requested */
    .cancellation-requested-btn {
      background-color: red;
      color: white;
      padding: 10px 20px;
      border: none;
      cursor: not-allowed;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>
  <main class="bookings-container">
    <h2>My Bookings</h2>

    <!-- Success message for cancellation request -->
    <?php if (isset($_SESSION['cancellation_message'])): ?>
      <div class="alert-success">
        <p><?= $_SESSION['cancellation_message']; ?></p>
      </div>
      <?php unset($_SESSION['cancellation_message']); ?>
    <?php endif; ?>

    <h3>Pending Bookings</h3>
    <div class="table-responsive">
      <table class="bookings-table display" id="pendingBookingsTable">
        <thead>
          <tr>
            <th>Venue</th>
            <th>Location</th>
            <th>Date</th>
            <th>Time</th>
            <th>Guests</th>
            <th>Package</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendingBookings as $b): ?>
            <tr>
              <td><?= htmlspecialchars($b['venue_name']) ?></td>
              <td><?= htmlspecialchars($b['venue_location']) ?></td>
              <td><?= htmlspecialchars($b['booking_date']) ?></td>
              <td><?= htmlspecialchars($b['timeslot']) ?></td>
              <td><?= htmlspecialchars($b['guests']) ?></td>
              <td><?= htmlspecialchars($b['package_name']) ?></td>
              <td>
                <?php if ($b['cancellationStatus'] == 'Pending'): ?>
                  <button class="cancellation-requested-btn" disabled>Cancellation Requested</button>
                <?php else: ?>
                  <button class="request-cancellation-btn" data-id="<?= $b['id'] ?>">Request Cancellation</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Completed Bookings Table -->
    <h3>Completed Bookings</h3>
    <div class="table-responsive">
      <table class="bookings-table display" id="completedBookingsTable">
        <thead>
          <tr>
            <th>Venue</th>
            <th>Location</th>
            <th>Date</th>
            <th>Time</th>
            <th>Guests</th>
            <th>Package</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($completedBookings as $b): ?>
            <tr>
              <td><?= htmlspecialchars($b['venue_name']) ?></td>
              <td><?= htmlspecialchars($b['venue_location']) ?></td>
              <td><?= htmlspecialchars($b['booking_date']) ?></td>
              <td><?= htmlspecialchars($b['timeslot']) ?></td>
              <td><?= htmlspecialchars($b['guests']) ?></td>
              <td><?= htmlspecialchars($b['package_name']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Modal for Cancellation Request -->
  <div id="cancellationModal" class="modal">
    <div class="modal-content">
      <span class="close" id="closeModal">&times;</span>
      <h2>Request Cancellation</h2>
      <form action="submit_cancellation.php" method="POST">
        <p>Are you sure you want to cancel your booking? A 20% cancellation fee will be applied.</p>
        <textarea name="cancellation_explanation" placeholder="Provide an explanation (Optional)" rows="4" style="width:100%;"></textarea>
        <input type="hidden" name="booking_id" id="modalBookingId">
        <button type="submit">Submit Request</button>
      </form>
    </div>
  </div>

  <script>
    // Modal functionality
    const modal = document.getElementById('cancellationModal');
    const closeModal = document.getElementById('closeModal');
    const requestBtns = document.querySelectorAll('.request-cancellation-btn');
    let currentBookingId = null;

    requestBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        currentBookingId = btn.getAttribute('data-id');
        modal.style.display = 'flex';
        document.getElementById('modalBookingId').value = currentBookingId;
      });
    });

    closeModal.addEventListener('click', () => {
      modal.style.display = 'none';
    });
  </script>
</body>

</html>