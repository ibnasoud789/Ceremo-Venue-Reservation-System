<?php
session_start();
include "db.php"; // Include the database connection file

// Fetch bookings today count
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM bookings WHERE DATE(booking_date) = ?");
$stmt->bind_param("s", $today);
$stmt->execute();
$bookings_today = $stmt->get_result()->fetch_assoc()['count'];

// Fetch active venues count
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM venues WHERE status = 'active'");
$stmt->execute();
$active_venues = $stmt->get_result()->fetch_assoc()['count'];

// Fetch total users count
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM customers");
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['count'];

// Fetch total revenue for the week
$start_date = date('Y-m-d', strtotime('monday this week'));
$end_date = date('Y-m-d', strtotime('sunday this week'));
$stmt = $conn->prepare("SELECT SUM(revenue) AS total FROM bookings WHERE booking_date BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$revenue_this_week = $stmt->get_result()->fetch_assoc()['total'];

// Fetch recent bookings (last 5 bookings)
$stmt = $conn->prepare("SELECT b.id, c.name AS user_name, v.name AS venue_name, b.booking_date, b.package_name, b.guests 
                        FROM bookings b
                        JOIN customers c ON b.customer_id = c.id
                        JOIN venues v ON b.venue_id = v.id
                        ORDER BY b.booking_date DESC LIMIT 5");
$stmt->execute();
$recent_bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch bookings over time for chart
$stmt = $conn->prepare("SELECT DATE(booking_date) AS date, COUNT(*) AS count FROM bookings 
                        GROUP BY DATE(booking_date) ORDER BY date ASC");
$stmt->execute();
$bookings_over_time = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch revenue over time for chart
$stmt = $conn->prepare("SELECT DATE(booking_date) AS date, SUM(revenue) AS total_revenue FROM bookings 
                        GROUP BY DATE(booking_date) ORDER BY date ASC");
$stmt->execute();
$revenue_over_time = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();  // Close the connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="dashboard.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
</head>

<body>
  <!-- Sidebar -->
  <aside class="sidebar animate__animated animate__fadeInLeft">
    <div class="sidebar-brand">
      <h2><i class="fas fa-crown"></i> Admin Panel</h2>
    </div>
    <ul class="sidebar-menu">
      <li class="active">
        <a href="admin_dashboard.php">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="admin_venues.php">
          <i class="fas fa-building"></i>
          <span>Venues</span>
        </a>
      </li>
      <li>
        <a href="admin_booking.php">
          <i class="fas fa-calendar-check"></i>
          <span>Bookings</span>
        </a>
      </li>
      <li>
        <a href="#">
          <i class="fas fa-users"></i>
          <span>Users</span>
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
    </ul>
  </aside>

  <!-- Main Content -->
  <div class="main-content animate__animated animate__fadeInUp">
    <!-- Dashboard Cards -->
    <section class="cards">
      <div class="card-single animate__animated animate__zoomIn">
        <div>
          <h1><?= $bookings_today ?></h1>
          <span>Bookings Today</span>
        </div>
        <div><i class="fas fa-calendar-check"></i></div>
      </div>
      <div class="card-single animate__animated animate__zoomIn">
        <div>
          <h1><?= $active_venues ?></h1>
          <span>Active Venues</span>
        </div>
        <div><i class="fas fa-building"></i></div>
      </div>
      <div class="card-single animate__animated animate__zoomIn">
        <div>
          <h1><?= $total_users ?></h1>
          <span>Total Users</span>
        </div>
        <div><i class="fas fa-users"></i></div>
      </div>
      <div class="card-single animate__animated animate__zoomIn">
        <div>
          <h1>$<?= number_format($revenue_this_week, 2) ?></h1>
          <span>Revenue This Week</span>
        </div>
        <div><i class="fas fa-dollar-sign"></i></div>
      </div>
    </section>

    <!-- Charts Section -->
    <section class="charts">
      <div class="chart-card animate__animated animate__fadeIn">
        <h3>Bookings Over Time</h3>
        <div class="chart-container">
          <canvas id="bookingsChart"></canvas>
        </div>
      </div>
      <div class="chart-card animate__animated animate__fadeIn">
        <h3>Revenue Over Time</h3>
        <div class="chart-container">
          <canvas id="revenueChart"></canvas>
        </div>
      </div>
    </section>

    <!-- Recent Activity Table -->
    <section class="recent-activity animate__animated animate__fadeIn">
      <div class="table-card">
        <h3>Recent Bookings</h3>
        <table>
          <thead>
            <tr>
              <th>Booking ID</th>
              <th>User</th>
              <th>Venue</th>
              <th>Date</th>
              <th>Total Guests</th>
              <th>Package</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent_bookings as $booking): ?>
              <tr>
                <td><?= $booking['id'] ?></td>
                <td><?= $booking['user_name'] ?></td>
                <td><?= $booking['venue_name'] ?></td>
                <td><?= $booking['booking_date'] ?></td>
                <td><?= $booking['guests'] ?></td>
                <td><?= $booking['package_name'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Bookings Chart
    const bookingsChart = document.getElementById('bookingsChart').getContext('2d');
    new Chart(bookingsChart, {
      type: 'line',
      data: {
        labels: <?= json_encode(array_column($bookings_over_time, 'date')) ?>,
        datasets: [{
          label: 'Bookings Count',
          data: <?= json_encode(array_column($bookings_over_time, 'count')) ?>,
          borderColor: '#1f0758',
          fill: false,
          tension: 0.1
        }]
      }
    });

    // Revenue Chart
    const revenueChart = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueChart, {
      type: 'line',
      data: {
        labels: <?= json_encode(array_column($revenue_over_time, 'date')) ?>,
        datasets: [{
          label: 'Revenue ($)',
          data: <?= json_encode(array_column($revenue_over_time, 'total_revenue')) ?>,
          borderColor: '#28a745',
          fill: false,
          tension: 0.1
        }]
      }
    });
  </script>
</body>

</html>