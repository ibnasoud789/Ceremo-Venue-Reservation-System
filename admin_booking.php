<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Bookings Management</title>
  <!-- Animate.css for smooth animations -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <!-- Font Awesome for icons -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <!-- Shared Dashboard CSS -->
  <link rel="stylesheet" href="dashboard.css" />
  <!-- Custom CSS for Bookings Page -->
  <link rel="stylesheet" href="admin_booking.css" />
</head>

<body>
  <!-- Sidebar Navigation (same as dashboard) -->
  <!-- Sidebar Navigation (Static) -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <h2><i class="fas fa-crown"></i> Admin Panel</h2>
    </div>
    <ul class="sidebar-menu">
      <li>
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
      <li class="active">
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
    <!-- Top Navigation -->
    <header>
      <div class="search-wrapper">
        <i class="fas fa-search"></i>
        <input type="search" placeholder="Search bookings..." />
      </div>
      <div class="user-wrapper">
        <i class="fas fa-bell"></i>
        <div class="admin-profile">
          <img src="images/admin.jpg" alt="Admin" />
          <div>
            <h4>Admin Name</h4>
            <small>King Ibna Soud</small>
          </div>
        </div>
      </div>
    </header>

    <h2 class="page-title">Bookings Management</h2>

    <!-- Bookings Table -->
    <section
      class="bookings-table-section animate__animated animate__fadeInUp">
      <table class="bookings-table">
        <thead>
          <tr>
            <th>Booking ID</th>
            <th>Venue</th>
            <th>User</th>
            <th>Date</th>
            <th>Package</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sample Data Rows (replace with dynamic content) -->
          <tr>
            <td>#1234</td>
            <td>The Grand Hall</td>
            <td>John Doe</td>
            <td>2025-03-10</td>
            <td>Set-1</td>
            <td><span class="status status-success">Confirmed</span></td>
            <td>
              <button class="btn-view"><i class="fas fa-eye"></i></button>
              <button class="btn-update"><i class="fas fa-edit"></i></button>
              <button class="btn-delete"><i class="fas fa-trash"></i></button>
            </td>
          </tr>
          <tr>
            <td>#1235</td>
            <td>Lake View</td>
            <td>Jane Smith</td>
            <td>2025-03-11</td>
            <td>Set-2</td>
            <td><span class="status status-pending">Pending</span></td>
            <td>
              <button class="btn-view"><i class="fas fa-eye"></i></button>
              <button class="btn-update"><i class="fas fa-edit"></i></button>
              <button class="btn-delete"><i class="fas fa-trash"></i></button>
            </td>
          </tr>
          <!-- More rows as needed -->
        </tbody>
      </table>
    </section>
  </div>
</body>

</html>