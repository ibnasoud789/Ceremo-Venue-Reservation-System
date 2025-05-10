<?php
session_start();
include 'db.php';  // defines $conn

// Check if the user is logged in and is a venue manager
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'venue') {
  header('Location: login.php');
  exit;
}

$venue_id = $_SESSION['user_id'];  // Assuming venue ID is stored in session

// Fetch venue name from the database
$stmt = $conn->prepare("SELECT name FROM venues WHERE id = ?");
$stmt->bind_param("i", $venue_id);
$stmt->execute();
$stmt->bind_result($venue_name);
$stmt->fetch();
$stmt->close();

// Today's Bookings Query
$sql_today_bookings = "SELECT COUNT(*) AS total FROM bookings WHERE venue_id = ? AND DATE(created_at) = CURDATE() AND status = 'Active'";
$stmt = $conn->prepare($sql_today_bookings);
$stmt->bind_param('i', $venue_id);
$stmt->execute();
$result_today_bookings = $stmt->get_result()->fetch_assoc();
$total_today_bookings = $result_today_bookings['total'] ?? 0;

// Upcoming Events Query
$sql_upcoming_events = "SELECT COUNT(*) AS total FROM bookings WHERE venue_id = ? AND booking_date > CURDATE() AND status = 'Active'";
$stmt = $conn->prepare($sql_upcoming_events);
$stmt->bind_param('i', $venue_id);
$stmt->execute();
$result_upcoming_events = $stmt->get_result()->fetch_assoc();
$total_upcoming_events = $result_upcoming_events['total'] ?? 0;

// Fetch the pending cancellation requests
$sql_cancellation_request = "SELECT b.id, c.name AS client_name, b.booking_date, b.timeslot, b.package_name, b.cancellationReason 
                            FROM bookings b 
                            JOIN customers c ON b.customer_id = c.id 
                            WHERE b.venue_id = ? AND b.cancellationStatus = 'Pending' LIMIT 1";
$stmt = $conn->prepare($sql_cancellation_request);
$stmt->bind_param('i', $venue_id);
$stmt->execute();
$result_cancellation = $stmt->get_result();
$hasCancellationRequest = $result_cancellation->num_rows > 0;
$cancellationData = $result_cancellation->fetch_assoc();
$stmt->close();
// Cancellations Query
$sql_cancellations = "SELECT COUNT(*) AS total FROM bookings WHERE venue_id = ? AND status = 'Cancelled'";
$stmt = $conn->prepare($sql_cancellations);
$stmt->bind_param('i', $venue_id);
$stmt->execute();
$result_cancellations = $stmt->get_result()->fetch_assoc();
$total_cancellations = $result_cancellations['total'] ?? 0;

// Timewise Reservations Query (for the current day)
$sql_timewise_reservations = "SELECT booking_date, timeslot, b.id AS booking_id, c.name AS client_name, b.status, guests, package_name
                              FROM bookings b
                              JOIN customers c ON b.customer_id = c.id
                              WHERE b.venue_id = ?";
$stmt = $conn->prepare($sql_timewise_reservations);
$stmt->bind_param('i', $venue_id);
$stmt->execute();
$result_timewise_reservations = $stmt->get_result();
$timewise_reservations = $result_timewise_reservations->fetch_all(MYSQLI_ASSOC);

// Monthly Booking Trend Data (for the past 6 months)
$sql_monthly_bookings = "SELECT MONTH(booking_date) AS month, COUNT(*) AS total
                         FROM bookings
                         WHERE venue_id = ? AND booking_date > DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                         GROUP BY MONTH(booking_date)
                         ORDER BY MONTH(booking_date)";
$stmt = $conn->prepare($sql_monthly_bookings);
$stmt->bind_param('i', $venue_id);
$stmt->execute();
$result_monthly_bookings = $stmt->get_result();
$monthly_bookings = [];
while ($row = $result_monthly_bookings->fetch_assoc()) {
  $monthly_bookings[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Venue Dashboard</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="dashboard.css" />
</head>

<body>
  <!-- Side Navigation -->
  <aside class="sidebar animate__animated animate__fadeInLeft">
    <div class="sidebar-brand">
      <h2><i class="fas fa-user-tie"></i> Venue</h2>
    </div>
    <ul class="sidebar-menu">
      <li class="active">
        <a href="#">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="#">
          <i class="fas fa-calendar-check"></i>
          <span>Reservations</span>
        </a>
      </li>
      <li>
        <a href="#">
          <i class="fas fa-clock"></i>
          <span>Timewise Schedule</span>
        </a>
      </li>
      <li>
        <a href="#">
          <i class="fas fa-chart-line"></i>
          <span>Reports</span>
        </a>
      </li>
      <li>
        <a href="#">
          <i class="fas fa-cog"></i>
          <span>Settings</span>
        </a>
      </li>
      <li>
        <a href="logout.php" class="logout-btn">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
        </a>
      </li>
    </ul>
  </aside>

  <!-- Main Content -->
  <div class="main-content animate__animated animate__fadeInUp">
    <header>
      <div class="welcome-wrapper">
        <h2>Welcome, <span id="venueName"><?php echo htmlspecialchars($venue_name); ?>!</span></h2>
      </div>
    </header>
    <!-- Check for Pending Cancellation Request -->
    <?php if ($hasCancellationRequest): ?>
      <!-- Show Modal -->
      <div id="cancellationModal" class="modal">
        <div class="modal-content">
          <h3>Ops! You got a cancellation request!</h3>
          <div class="booking-details">
            <p><strong>Client Name:</strong> <?= htmlspecialchars($cancellationData['client_name']) ?></p>
            <p><strong>Booking Date:</strong> <?= htmlspecialchars($cancellationData['booking_date']) ?></p>
            <p><strong>Timeslot:</strong> <?= htmlspecialchars($cancellationData['timeslot']) ?></p>
            <p><strong>Package Name:</strong> <?= htmlspecialchars($cancellationData['package_name']) ?></p>
            <p><strong>Cancellation Reason:</strong> <?= htmlspecialchars($cancellationData['cancellationReason']) ?></p>
          </div>
          <button id="approveCancellation" data-booking-id="<?= $cancellationData['id'] ?>">Approve</button>
          <button id="rejectCancellation" data-booking-id="<?= $cancellationData['id'] ?>" class="reject-btn">Reject</button>
        </div>
      </div>
    <?php endif; ?>

    <!-- Dashboard Cards -->
    <section class="cards">
      <div class="card-single animate__animated animate__zoomIn">
        <div>
          <h1><?= $total_today_bookings ?></h1>
          <span>Today's Bookings</span>
        </div>
        <div>
          <i class="fas fa-calendar-check"></i>
        </div>
      </div>

      <div class="card-single animate__animated animate__zoomIn">
        <div>
          <h1><?= $total_upcoming_events ?></h1>
          <span>Upcoming Events</span>
        </div>
        <div>
          <i class="fas fa-hourglass-half"></i>
        </div>
      </div>

      <div class="card-single animate__animated animate__zoomIn">
        <div>
          <h1><?= $total_cancellations ?></h1>
          <span>Cancellations</span>
        </div>
        <div>
          <i class="fas fa-times-circle"></i>
        </div>
      </div>
    </section>

    <!-- Manager Charts -->
    <section class="charts">
      <div class="chart-card animate__animated animate__fadeIn">
        <h3>Monthly Booking Trend</h3>
        <canvas id="managerBookingsChart" height="400" width="400"></canvas>
      </div>
    </section>

    <!-- Timewise Schedule Section -->
    <section class="timewise-schedule animate__animated animate__fadeIn">
      <h3>Reservations (Timewise)</h3>
      <table>
        <thead>
          <tr>
            <th>Booking ID</th>
            <th>Client Name</th>
            <th>Event Date</th>
            <th>Time Slot</th>

            <th>Guests</th>
            <th>Package Name</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($timewise_reservations as $reservation): ?>
            <tr>

              <td><?= htmlspecialchars($reservation['booking_id']) ?></td>
              <td><?= htmlspecialchars($reservation['client_name']) ?></td>
              <td><?= htmlspecialchars($reservation['booking_date']) ?></td>
              <td><?= htmlspecialchars($reservation['timeslot']) ?></td>

              <td><?= htmlspecialchars($reservation['guests']) ?></td>
              <td><?= htmlspecialchars($reservation['package_name']) ?></td>
              <td><span class="status status-success"><?= htmlspecialchars($reservation['status']) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </div>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx = document.getElementById('managerBookingsChart').getContext('2d');
    const managerBookingsChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [<?php echo implode(',', array_column($monthly_bookings, 'month')); ?>],
        datasets: [{
          label: 'Bookings',
          data: [<?php echo implode(',', array_column($monthly_bookings, 'total')); ?>],
          borderColor: '#4CAF50',
          fill: false,
        }]
      },
      options: {
        responsive: false,
        plugins: {
          legend: {
            position: 'top',
          },
          tooltip: {
            mode: 'index',
            intersect: false,
          },
        },
      }
    });
  </script>
  <script>
    // Show the modal if there's a cancellation request
    function showModal() {
      const modal = document.getElementById('cancellationModal');
      modal.style.display = 'flex';
    }

    // Close the modal
    function closeModal() {
      const modal = document.getElementById('cancellationModal');
      modal.style.display = 'none';
    }

    // Automatically show modal if there's a cancellation
    <?php if ($hasCancellationRequest): ?>
      showModal();
    <?php endif; ?>
    // Approve the cancellation
    $('#approveCancellation').click(function() {
      const bookingId = $(this).data('booking-id');
      $.ajax({
        url: 'handleCancellationRequest.php',
        type: 'POST',
        data: {
          booking_id: bookingId,
          action: 'approve'
        },
        success: function(response) {
          alert(response.message);
          closeModal();
        },
        error: function() {
          alert('Error occurred while approving the cancellation.');
        }
      });
    });

    // Reject the cancellation
    $('#rejectCancellation').click(function() {
      const bookingId = $(this).data('booking-id');
      $.ajax({
        url: 'handleCancellationRequest.php',
        type: 'POST',
        data: {
          booking_id: bookingId,
          action: 'reject'
        },
        success: function(response) {
          alert(response.message);
          closeModal();
        },
        error: function() {
          alert('Error occurred while rejecting the cancellation.');
        }
      });
    });
  </script>
</body>

</html>